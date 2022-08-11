<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Function_email.php
+-----------------------------------------------------
|Description:	Permet d'envoyer des emails
+-----------------------------------------------------
|Date de création:				27/01/05
|Date du premier test:			27/01/05
|Dernière modification[Auteur]: jj/mm/aa[Pseudo]
+----------------------------------------------------*/

function send_email($email_adresse, $email_message, $email_subject)
{
	//les headers
	$headers = "Date: ".date("l j F Y, G:i")."\n"; 
	$headers .= "MIME-Version: 1.0\n"; 
	$headers .= "From: Blood Warriors <".$CONF['game_admin_mail'].">\n";
	$headers .= "Reply-To: Blood Warriors <".$CONF['game_admin_mail'].">\n";
	$headers .= "X-Priority: 1\n";
	$headers .= "X-Mailer: Le jeu de Blood Warriors";
	$headers .= "Content-Type: text/html; charset=iso-8859-1\n";
	$headers .= "To: <".$email_adresse.">\r\n";

	//envoit
	if(@mail($email_adresse, $email_subject, $email_message, $headers))
	{
		return true;
	}
	echo "Impossible d'envoyer le mail à ".$email_adresse.". Verifier config SMTP.<br />\n";
	return false;

}

?>