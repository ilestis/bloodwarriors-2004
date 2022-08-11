<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Email.php
+-----------------------------------------------------
|Description:	Description du fichier
+-----------------------------------------------------
|Date de création:				05/02/05
|Date du premier test:			jj/mm/aa
|Dernière modification[Auteur]: jj/mm/aa[Pseudo]
+---------------------------------------------------*/
require('adminheader.php');


$Page = 'email';
if($_SESSION['aut'][$adminpage['email']] == 0)  breakpage();

//Sujet
$subject = "Proot";

//to
$a = 'kaio_swiss@hotmail.com';

//message
$message = 'Salut petit pété xD';
//rend joli joli
$message = strip_tags(eregi_replace("<br>", "\n", $message));

	//les headers
	$headers = "Date: ".date("l j F Y, G:i")."\n"; 
	$headers .= "MIME-Version: 1.0\n"; 
	$headers .= "From: Blood Warriors <kaio_swiss@hotmail.com>\n";
	$headers .= "Reply-To: Blood Warriors <kaio_swiss@hotmail.com>\n";
	$headers .= "X-Priority: 1\n";
	$headers .= "X-Mailer: Le jeu de Blood Warriors";
	$headers .= "Content-Type: text/html; charset=iso-8859-1\n";
	$headers .= "To: <".$a.">\r\n";

//envoit
mail($a, $subject, $message, $headers) or die('Impossible d\'envoyer le message!');

echo 'Email Envoyé!<br />';
?>