<?php
include ('admin/adminheader.php');

// Formulaire pour la saisie
include ('include/mise_en_forme.inc.php');

bw_tableau_start("News");

?>
<script language='JavaScript'>
function details(id) {
if (document.getElementById('t'+id).style.display == 'none') {
document.getElementById('t'+id).style.display = '';
document.getElementById('i'+id).src='images/moins.jpg';
} else {
document.getElementById('t'+id).style.display = 'none';
document.getElementById('i'+id).src='images/plus.jpg';
}}
</script>
<?php

if($_SESSION['aut'][$adminpage['variable_newsadmin']] == 0) exit;

if (isset($_GET['change']))
{//selecteur			
	if($_GET['change'] == 'mod')
	{
		$Titre = forummessage(clean($_POST['title']));
		$Commentaire = forummessage(clean($_POST['commentaire']));
		$ID = clean($_GET['id']);

		$sql = "UPDATE messages SET titre = '".$Titre."', message = '".$Commentaire."' WHERE id_message = '".$ID."' AND location = 'ann'";
		sql_query($sql);
		$infoMessage = "<img src=\"images/admin/edit.png\">News admin \'".$ID."\' modifiée.";
		journal_admin($Joueur->pseudo, $infoMessage);
	}
	elseif ($_GET['change'] == 'del')
	{	
		$id = clean($_GET['stat']);
		$sql = "DELETE FROM messages WHERE id_message = '".$id."' AND location = 'ann'";
		sql_query($sql);
		$infoMessage = "<img src=\"images/admin/no.png\">News admin \'".$ID."\' supprimée.";
		journal_admin($Joueur->pseudo, $infoMessage);
	}
	elseif ($_GET['change'] == 'add')
	{
		$Titre = forummessage(clean($_POST['title']));
		$Comment = forummessage(clean($_POST['commentaire']));

		send_message($_SESSION['id_joueur'], '', $Comment, 0, 'ann', $Titre);
		//$sql = "INSERT INTO `admin_news` VALUES('','".$Joueur->pseudo."','".time()."','".$Titre."', '".$Comment."')";
		//sql_query($sql);
		$infoMessage = "<img src=\"images/admin/ok.png\">News admin ajoutée.";
		journal_admin($Joueur->pseudo, $infoMessage);
	}

	if(!empty($infoMessage)) {
		bw_f_info($lang_text['info'], $infoMessage);
	}
}

if(isset($_GET['view']) and is_numeric($_GET['view']))
{
	$ID = clean($_GET['view']);

	echo "<table class=\"newsmalltable\">\n";
	echo "<tr>\n";
	echo "	<th>Modification de la news</th>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "	<td>\n";

	$sql = "SELECT * FROM messages WHERE id_message = '".$ID."' AND location = 'ann'";
	$req = sql_query($sql);
	if(sql_rows($req) == 1 || $ID == 0)
	{//Y'a donc on affiche, sinon c'est un nouveau
		$res = sql_array($req);

		
		echo "	<form method=\"POST\" action=\"index.php?p=admin_variable_newsadmin&change=mod&id=".$res['id_message']."\">\n";

		bw_afficheToolbar("Enregistrer", reversemessage($res['message']), true, $res['titre']);
		echo "	</form>\n";
	}
	else
	{
		echo "News introuvée!<br />\n";
	}
	echo "		<a href=\"?p=admin_variable_newsadmin\">Retour news</a><br />\n";
	echo "	</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
}
else
{//Liste des annonces + Nouvelles


	echo "<table class=\"newsmalltable\">\n";
	echo "<tr>\n";
	echo "	<th>Noms des articles</th><th>Auteur</th><th>Date</th><th>Supprimer</th>\n";
	echo "</tr>\n";

	$sql = "SELECT a.*, b.pseudo FROM messages AS a LEFT JOIN joueurs AS b ON b.id = a.id_from WHERE a.location = 'ann' ORDER BY a.time DESC";
	$req = sql_query($sql);
	while($res = mysql_fetch_array($req))
	{
		echo "	<tr>\n";
		echo "		<td><a href=\"?p=admin_variable_newsadmin&view=".$res['id_message']."\">".$res['titre']."</a></td>\n";
		echo "		<td>".$res['pseudo']."</td>\n";
		echo "		<td>".date($GLOBALS['CONF']['game_timeformat'], $res['time'])."</td>\n";
		echo "		<td><a href=\"?p=admin_variable_newsadmin&change=del&stat=".$res['id_message']."\">Del</a></td>\n";
		echo "	</tr>\n";
	}
	echo "</table>\n";

	echo "<p><strong><a href=\"?p=admin_variable_newsadmin&change=mod&id=0\">Créer une nouvelle news</a></strong></p>\n";
}

bw_tableau_end();