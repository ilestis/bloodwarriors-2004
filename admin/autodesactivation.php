<?php
/*----------------------[TABLEAU]---------------------
|Nom:			AutoD�sactivation.php
+-----------------------------------------------------
|Description:	Supprime les joueurs inactifes
+-----------------------------------------------------
|Date de cr�ation:				14/02/05
|Derni�re modification[Auteur]: jj/mm/aa[Pseudo]
+---------------------------------------------------*/
//verifie si la session est en cours
if (session_is_registered("login") == false)
{
	include 'news.php';
	exit;
}

//selectionne chaque jours
$sql = "SELECT `login`,`pseudo`,`email` FROM `stats` WHERE `acceslvl` < '3' ORDER BY `id` ASC";
$req = mysql_query($sql);
while($res = mysql_fetch_array($req))
{
	//pour chaque joueur on prend la derni�re connexion
	$ipa = "SELECT time FROM `ip` WHERE `pseudo` = '".$res['pseudo']."' ORDER BY `time` DESC LIMIT 0,1";
	$ipb = mysql_query($ipa);
	$ip = mysql_fetch_array($ipb);
	$inactive = (3600*24)*5; //1h fois 24 * 5 jours
	$status = time() - $ip['time'];
	if($status > $inactife)
	{///proot basta^^
		//supprime

		//email
		include 'include/function_email.php';
		$subject = "Blood Warriors - D�sactivation de votre compte";

		$message = 'Bonjour '.$res['pseudo'].'.<br>
		Votre compte � �t� d�sactiv� pour inactivit�!<br> 
		Cependant, vous pouvez vous r�inscrire, mais vous recommencerez � z�ro.<br><br>
		L\'�quipe des admins vous souhaitent un bon jeu sur http://www.place3.org/kaio .<br><br>
		Cordialement, Les Admins<br>';

		//met en format de email
		$message = strip_tags(eregi_replace("<br>", "\n", $message));

		//envoit
		send_email($res['email'], $message, $email_subject);
	}
}
?>