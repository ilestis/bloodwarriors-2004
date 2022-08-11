<?php
//verifie si la session est en cours
require ('adminheader.php');

$Page = 'suppression';

if($_SESSION['aut'][$adminpage['suppression']] == 1)
{//si on a le niveau requis pour supprimer
	$sql = "SELECT id, pseudo FROM joueurs WHERE id = '".clean($_GET['joueurid'])."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);
	$Id_Cible = $res['id'];
	$Pseudo_Cible = $res['pseudo'];

	//On supprime
	sql_query("DELETE FROM armees WHERE id_joueur = '".$Id_Cible."'");
	sql_query("DELETE FROM batiments WHERE id_joueur = '".$Id_Cible."'");
	//sql_query("DELETE FROM echanges WHERE first_id = '".$Id_Cible."' OR second_id = '".$Id_Cible."'");
	sql_query("DELETE FROM forum_last_visite WHERE id_joueur = '".$Id_Cible."'");
	sql_query("DELETE FROM joueurs WHERE id = '".$Id_Cible."'");
	sql_query("DELETE FROM messages WHERE id_to = '".$Id_Cible."'");
	sql_query("DELETE FROM provinces WHERE id_joueur = '".$Id_Cible."'");
	sql_query("DELETE FROM temp_paysans WHERE id_joueur = '".$Id_Cible."'");
	sql_query("DELETE FROM warnings WHERE id_joueur = '".$Id_Cible."'");

	//journal
	journal_admin($Joueur->pseudo, "<img src=\"images/admin/no.png\">Le joueur ".$Pseudo_Cible." à été supprimé!");


	echo "Le joueur ".$Pseudo_Cible." a bien été supprimé.<br /><br />\n" ;

	echo "<a href=\"index.php?p=admin_admin\">Retour à la page admin</a><br />\n";
}