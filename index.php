<?php
session_start();
ob_start();
$autocomplete = "off"; // formulare doplnuje browser, off = vetsi security
$serverPOSTaddress = '/formular/'; // relativni cesta, kam se bude formular odesilat


// uvod formulare a start  HTML
echo '
<!doctype html>

<head>	
	<title>Formulář test</title>

	<meta charset="utf-8">
	<link rel="stylesheet" href="css/screen.css" />
</head>
<body>

<div id="container">
	<h1>Formulář</h1>
';

// formular odeslan, method form $_POST
if($_POST){
$ShopEmail = 'krsiak.daniel@gmail.com'; //$ShopEmail = 'krsiak.daniel@gmail.com'
$CustomerEmail =  $_POST['email']; // formularovy vyplneny email

// kontrola emailu
if(!eregi("^[a-z0-9]+([_\\.-][a-z0-9]+)*" ."@"."([a-z0-9]+([\.-][a-z0-9]+)*)+"."\\.[a-z]{2,}"."$",$_POST['email'] ))
  {
	$error ='<span class="errorform">Neplatná emailová adresa!</span>';
	$errors=1;
	}

// surname je pro roboty, nesmi byt vyplneno, $_POST['surname']!=""                                                                                                               
if (
$_POST['message']=="" || $_POST['name']=="" || $_POST['email']=="" || $_POST['url']=="" || $_POST['surname']!=""
) {$error= '<span class="errorform">Nejsou vyplněny povinné položky!</span>';  $errors=1;}


if($errors==1) 	{$msg= $error;} // nastavi se chybova hlaska a jinak se nic nedeje
else{
// neni zadna chyba - odesli 2 emaily

// 1. email - potvrzeni pro zakaznika
// predmet zpravy #1
$email_subject = "Potvrzení formuláře na webu Kyrra.cz";

// text zpravy #1
$email_content = "<p>Dobrý den,<br /><br />
tento email slouží jako potvrzení o úspěšném odeslání Vaší zprávy z formuláře webu <b>krsiak.cz</b><br />
Email je generován automaticky a proto na něj neodpovídejte.<br /><br /><br />

<b>Zpráva:</b> <br /> ".nl2br($_POST['message'])."<br /><br /><br />

S pozdravem<br />
Krsiak Daniel<br />
<a href=\"http://www.krsiak.cz/\">http://www.krsiak.cz/</a>
</p>
"; 

// UTF8 hlavicka emailu #1
 $header = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n";     
 $header .= "From: Daniel Krsiak <krsiak.daniel@gmail.com>\n"; // bez diakritiky
 $header .= "Reply-To: Daniel Krsiak <krsiak.daniel@gmail.com>\n"; // bez diakritiky
 $header .= "BCC: krsiak.daniel@gmail.com\n"; 
 
// command na odeslani emailu #1                
	 if (@mail($CustomerEmail, '=?UTF-8?B?'.base64_encode($email_subject).'?=', $email_content, $header)) {
			$tempstatus = 1; // email #1 ok
	} else {
     	$tempstatus = 0; // email #1 ERROR - dat vedet, ze neco neni v poradku
	}

// 2. email - pro prodejce
// predmet zpravy #2
$email_subject = "Nová zpráva z formuláře na webu Kyrra.cz";

// text zpravy #2
$email_content = "<p>
Jméno: ".$_POST['name']."<br />
Email: ".$_POST['email']."<br />
URL: ".$_POST['url']."<br /><br />
<b>Zpráva:</b> <br /> ".nl2br($_POST['message'])."<br /><br />
</p>
"; 
	
// UTF8 hlavicka emailu #2     
 $header = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n";     
 $header .= "From: Daniel Krsiak <krsiak.daniel@gmail.com>\n"; // bez diakritiky
 $header .= "Reply-To: Daniel Krsiak <krsiak.daniel@gmail.com>\n"; // bez diakritiky
 $header .= "BCC: krsiak.daniel@gmail.com\n"; 
 
// command na odeslani emailu #2               
	 if (@mail($ShopEmail, '=?UTF-8?B?'.base64_encode($email_subject).'?=', $email_content, $header)) {
	         
           // email 1 also OK
           if ($tempstatus==1) {$error= '<span class="okform">Formulář odeslán OK.</span>'; 
           // nastavime SESSION na OK, promena COMPLETE, dulezite pro nize v HTML FORMu
           $_SESSION['complete']='<span class="okform">Formulář odeslán OK.</span>'; 
           // presmeruj na stejnou adresu, kde jsi ted, aby se zamezilo odesilani formulare F5 F5 F5 jak u blbcu :]
           header('Location: '.$serverPOSTaddress); 
           exit(); // exit, aby neudelal akce nize, muze byt problem a neco se prepise v SESSION, nechceme, rovnou exit a redirect
                               }
           // email 1 nejede, napis standardni chybu                  
           else {		$error= '<span class="errorform">Došlo k neočekávané chybě. Zkuste to prosím později.</span>';}
	} else {
          	// email 2 nejede, standardni chybu
		$error= '<span class="errorform">Došlo k neočekávané chybě. Zkuste to prosím později.</span>';
	}
		
} // end Formular je vyplnen spravne

} // end $_POST
 
  
// HTML FORMULAR, zobrazi se pri $_POST i bez nej  
			echo '      
	<form id="form5" action="'.$serverPOSTaddress.'" autocomplete="'.$autocomplete.'" enctype="multipart/form-data" method="post">	
		<fieldset>
			<legend>Kontaktní formulář</legend>
			';
			// intro message jen pokud není SESSION COMPLETE = OK message
			if ($_SESSION['complete']=="") {
			// Intro message
			$IntroMessage = 'Všechny položky jsou povinné.';
			}
      // zobrazime OK hlasku, nebo error hlasku, vzdy bude jen jedna z nich $error nebo $_SESSION['complete'], proto . = spojovnik :]
      echo 
			'
	  	<div id="form-result">'.$IntroMessage.'<br />'.$error.$_SESSION['complete'].'</div>
			'
			;
			// vymazeme OK hlasku, po F5 uz nebude videt
			if ($_SESSION['complete']!="") {$_SESSION['complete'] = NULL;}
      // jednotlive casti formulare, value je vzdy hodnota POST, pokud posilame
			echo '
			
			<p class="first">					
				<label for="name">Jméno</label>				
				<input type="text" name="name" id="name" size="30" maxlength="100" value="';if ($_POST) {echo $_POST['name'];}  echo '"  />					
			</p>
			';
// jeden radek navic, lakadlo pro roboty, class hidden v css jako important <p class="important">
// vsechny requesty, ktere budou mit surname vyplnene, nezpracujeme			
			echo '
			<p class="first important">					
				<label for="name">Příjmení</label>				
				<input type="text" name="surname" id="surname" size="30" maxlength="100" />					
			</p>
			';
			
			echo 
      '
			<p>					
				<label for="email">Email</label>				
				<input type="text" name="email" id="email" size="30" maxlength="100" value="';if ($_POST) {echo $_POST['email'];}  echo '"  />				
			</p>
			<p>
				<label for="url">URL</label>				
				<input type="text" name="url" id="url" size="30" maxlength="100" value="';if ($_POST) {echo $_POST['url'];}  echo '" />										
			</p>
			<p>
				<label for="message">Zpráva</label>
				<textarea name="message" id="message" cols="30" rows="10">';if ($_POST) {echo $_POST['message'];}  
        echo '</textarea>
			</p>					
			<p class="submit"><button type="submit">Odeslat</button></p>	
		</fieldset>								
	</form>
';
// end HTML

echo '
</div>

</body>
</html>
';

?>
