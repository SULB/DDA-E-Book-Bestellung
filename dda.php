<?php

/************************************************************************
*																		*
*	Tool zur Anforderung von E-Books									*
*																		*
*	@Author: "Cornelius Leidinger" <c.leidinger@sulb.uni-saarland.de>	*
*	@Datum: 12.12.2017													*
*																		*
*************************************************************************/
/*																		*
*	Update: Dienst für Mitglieder der HTW 								*
*			(Benutzernummer startend mit 50) sperren.					*
*																		*
*	@Datum: 07.08.2018													*
*																		*
*	Zeile: 39 - 41														*
*																		*
*************************************************************************/
/*																		*
*	Update: Beschreibung 3-4 Werktage auf ca. 7 geändert				*
*																		*
*	@Datum: 25.04.2019													*
*																		*
*************************************************************************/
/*																		*
*	Update: Auslagerung aller einrichtungsbezogenen Daten in Variablen	*
*																		*
*	@Datum: 05.11.2020													*
*************************************************************************/

//Name der Einrichtung
$instName = "SULB";

//Name des Kontakts, der in der Mail genannt wird.
$contactName = "E-Book-Team";

//Telefonnummer für Rückfragen
$tel = "";

//Haupt-Mailadresse für Rückfragen und als Absender
$mainMail = "";
//$mainMail = "c.leidinger@sulb.uni-saarland.de";

//Empfänger-Mailadressen für Anfragen
//$mailTo = $mainMail.", email2@sulb.uni-saarland.de"; //mehrere Mails möglich
$mailTo = $mainMail;

//Absendeadresse
$mailFrom = "From: ".$mainMail;
//$mailFrom = "From:c.leidinger@sulb.uni-saarland.de";

//URL der Libero Authentifizierungsschnittstelle
$libero_url = "https://<libero-url>/csp/user/autoris.csp";
//URL dieses skripts
$tools_url = "https://<url-of-this-script->/dda.php";

//Daten aus aufgerufenem Link abfragen (Abfrage aus POST und GET um damit auch später das Abschicken des Formulars funktioniert)
$titel = isset($_REQUEST["titel"]) ? $_REQUEST["titel"] : "";
$autor = isset($_REQUEST["autor"]) ? $_REQUEST["autor"] : "";
$isbn = isset($_REQUEST["isbn"]) ? $_REQUEST["isbn"] : "";
$verlag = isset($_REQUEST["verlag"]) ? $_REQUEST["verlag"] : "";

//Url der ursprünglichen Seite mekren und später zurückkehren zu können
$referer = isset($_REQUEST["referer"]) ? $_REQUEST["referer"] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "");
$benutzer = isset($_REQUEST["benutzer"]) ? $_REQUEST["benutzer"] : "";

//URL der Liberoschnittstelle um die gültigkeit der Benutzernummer zu verifizieren
$nutzerschnittstelle = $libero_url."?DATA=SUB&BEN=".$benutzer;

//Submit nur aus POST nehmen, damit das Formular nicht doppelt geschickt werden kann
if(isset($_POST["submit"]) && $benutzer != ""){
	$error = false;
	//Bestimmte Nutzergruppen sperren. Hier alle die mit 50 beginnen
	if((substr($benutzer,0,2)== "50")){
		echo "<font color='red'>Dieser Dienst ist leider nur für Angehörige der UdS verfügbar</font>";
		$error = true;
	}elseif (($response_data = file_get_contents($nutzerschnittstelle))===false){ //Nutzernummer verifizieren
		//Bei fehlgeschlagener Verifizierung
		echo "<font color='red'>Fehler beim Aufruf der Benutzerüberprüfung, bitte versuchen Sie es später noch einmal. Wenn das Problem dauerhaft besteht wenden Sie sich bitte an einen Systemadministrator.</font>";
		$error = true;
	} else {
		//Bei erfolgreicher Verifizierung
		
		//Das Bestätigungstoken ist egal, wichtig ist nur, dass es eins gibt
			//$benutzerEinstufung = preg_match("/ADATA:(.*)/", $response_data, $treffer);
			//echo $treffer[1];
			
		if(!preg_match("/ADATA:[ ]*_|_[ ]*/", $response_data)){
			//Es gibt ein Bestätigungstoken
			//Mail verschicken
			if(false === mail($mailTo, "DDA Buchbestellung", "Sehr geehrtes ".$contactName.",\n\nvon ".$instName."-Benutzer ".$benutzer." wurde ein E-Book angefordert.\n Titel: ".$titel."\n Autor: ".$autor."\n ISBN: ".$isbn."\n Verlag: ".$verlag."\n\nMit freundlichen Grüßen\ndas ".$contactName." der ".$instName, $mailFrom)){
				echo "<font color='red'>Fehler beim versenden der Email, bitte versuchen Sie es später noch einmal. Wenn das Problem dauerhaft besteht wenden Sie sich bitte an einen Systemadministrator.</font>";
				$error = true;
			}else{
				//echo "Der Buchwunsch wurde erfolgreich versendet.</br>Vielen Dank für Ihr Interesse.";
				?>
Senden erfolgreich.</br>				
Nach erfolgter Freischaltung werden Sie benachrichtigt.</br>
</br>
Bei Fragen wenden Sie sich bitte an die Mitarbeiter der <?php echo $instName; ?>:</br>
</br>
Tel.: <?php echo $tel; ?> oder</br>
Mail: <?php echo $mainMail; ?></br>
</br>
				<?php
			}
		}else{
			//Es gibt kein Bestätigungstoken
			echo "<font color='red'>Die Benutzernummer ist nicht gültig! Bitte geben sie eine gülte Kennung an.</font>";
			$error = true;
		}
	}
	if($error == true){
		echo '</br>Sie werden in 5 Sekunden automatisch weitergeleitet, falls nicht <a href="'.$tools_url.'?titel='.$titel.'&autor='.$autor.'&isbn='.$isbn.'&verlag='.$verlag.'">klicken Sie hier</a>';
		echo '<meta http-equiv="refresh" content="5; URL='.$tools_url.'?titel='.$titel.'&autor='.$autor.'&isbn='.$isbn.'&verlag='.$verlag.'&referer='.$referer.'">';
	}else{
		echo '<a href="'.$referer.'">Zurück zur Ursprungsseite</a>';
	}
}else{
if(isset($_POST["submit"]) && $benutzer == ""){
	echo "<font color='red'>Bitte geben Sie eine Benutzernummer an</font></br></br>";
}

?>
<html>
<head>
<meta charset="utf-8">
<title>DDA-Projekt <?php echo $instName; ?></title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
    <div id="website">
      <div id="logos" role="banner">
        <div id="logo_left">
          <a href="/"><img src="logo.gif" width="180" height="50"   alt="Logo der <?php echo $instName; ?>"  border="0"></a>
          <!--<div class="clear"></div>-->
        </div>
      </div>
</br>
</br>
Dieses E-Book befindet sich noch nicht im Bestand der <?php echo $instName; ?>:</br>
</br>
<?php echo $autor?></br>
<?php echo $titel?></br>
<?php echo $isbn?></br>
<?php echo $verlag?></br>
</br>
</br>
Möchten Sie Zugriff erhalten? (Dauert ca. 7 Werktage, Nutzung nur innerhalb des Uninetzes möglich)</br>
Dann geben Sie bitte Ihre Benutzernummer ein:</br>

<form method="POST" action="<?php echo $tools_url; ?>">
	<input type="text" name="benutzer">
	<input type="hidden" name="titel" value="<?php echo $titel?>">
	<input type="hidden" name="autor" value="<?php echo $autor?>">
	<input type="hidden" name="isbn" value="<?php echo $isbn?>">
	<input type="hidden" name="verlag" value="<?php echo $verlag?>">
	<input type="hidden" name="referer" value="<?php echo $referer?>">
	<input type="submit" name="submit" value="Senden">
</form>

Durch Betätigen des "Senden"-Knopfes bestellen wir die Freischaltung für Sie.</br>
</br>
Falls nicht:</br>
<a href="<?php echo $referer?>">Zurück zur Ursprungsseite</a>
</body>
</html>

<?php
}
?>
