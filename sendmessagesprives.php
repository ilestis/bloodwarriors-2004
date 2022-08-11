<?php
/*----------------------[TABLEAU]---------------------
|Nom:			sendmessagesprives.php
+-----------------------------------------------------
|Description:	Permnet d'envoyer un message privé
+-----------------------------------------------------
|Date de création:				23/05/05
|Dernière modification[Auteur]: jj/mm/aa[Pseudo]
+---------------------------------------------------*/

//session
session_start();

//Définit la méthode anti-hackage
define('IN_GAME', true);


//includes
include 'include/fonction.php'; //Les fonctions
$CONF = global_variables('include/variables.inc.php'); //Variables global de configuration
require ('include/session_verif.php');
include 'connect.php'; //Connextion BDD
include 'include/function_mef.php';
	//object des données de la table stats
	$sql = "SELECT * FROM joueurs WHERE id = '".$_SESSION['id_joueur']."' ";
	$req = sql_query($sql);
	$Joueur = mysql_fetch_object($req); //met tout dans un dossier objet

//html
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 4.01t//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	
<head>
	<link rel="SHORTCUT ICON" type="image/x-icon" href="images/logo_bw.png">
	<title>Message Privé</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="STYLESHEET" type="text/css" href="css<?php echo $Joueur->theme; ?>.css" \>
</head>

<body>

<script language="JavaScript" type="text/javascript">
//pour les émoticons
function emoticon(text) {
	text = ' ' + text + ' ';
	if (document.post.comment.createTextRange && document.post.comment.caretPos) {
		var caretPos = document.post.comment.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		document.post.comment.focus();
	} else {
	document.post.comment.value  += text;
	document.post.comment.focus();
	}
}
</script>

<?php

echo "	<center>\n";

//verifie si le joueur existe
$sql = "SELECT pseudo, `id` FROM joueurs WHERE `id` = '".clean($_GET['id'])."'";
$req = sql_query($sql);
$Player = mysql_num_rows($req);
if(($Player == 1) &&(clean($_GET['id']) != $_SESSION['id_joueur']))
{

	if(isset($_POST['comment']))
	{
		$res = mysql_fetch_array($req);

		$comment = forummessage(clean($comment));

		//insertion dans la base de donnée
		send_message($_SESSION['id_joueur'], $res['id'], $comment, 0);


		
		echo "<span class=\"info\">Message envoyé à ".$res['pseudo'].".</span><br />\n";
		echo "<a href=\"#\" onclick=\"self.close();\">Fermer la fenêtre.</a>\n";
	}
	else
	{
		//Cherche le pseudo
		$resp = mysql_fetch_array(sql_query("SELECT pseudo FROM joueurs WHERE id = '".clean($_GET['id'])."'"));
		//ok
		echo '<table width="50%" border=0 bgcolor="#cccccc">';
		echo '<tr>';
		echo '<form name="post" method="post" action="sendmessagesprives.php?id='.clean($_GET['id']).'">';
		echo '<th>';
		echo "Envoyer un message privé à ".$resp['pseudo']."\n";
		echo "</th></tr><tr><td class=\"in\">\n";

		//les smileys
		$smi = "SELECT * FROM `autres_smileys` ORDER BY `id_smiley` ASC";
		$smip = sql_query($smi);
		while($smis = mysql_fetch_array($smip))
		{
			echo '<a href="javascript:emoticon(\''.$smis['code'].'\')"><img src="smiles/icon_'.$smis['url_adresse'].'.gif" border="0"/></a>&nbsp;';
		}

		echo "	<br/>\n";
		echo "	<textarea name=\"comment\" cols=\"50\" rows=\"7\" id=\"comment\"></textarea><br/>\n";
		echo '<input type="submit" name="Submit2" value="Envoyer">';
		echo "	</td>\n";
		echo "	</form>\n";
		echo "</tr>\n";
		echo "</table>\n";
	}
}
else
{
	echo "<span class=\"avert\">Il faut choisir un joueur valable!</spam><br />\n";
	echo "<span class=\"info\">Vous ne pouvez pas vous envoyer de message privé, ni à un joueur supprimé!</span><br />\n";
}
?>
	</center>
</body>