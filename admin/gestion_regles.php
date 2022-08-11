<?php
require('adminheader.php');
$t_regles = 'autres_regles';

bw_tableau_start("Gestion des Règles");

if(isset($_POST['action']) && $_POST['action'] == 'new')
{//Enregistre
	//Clean
	$Section = clean($_POST['section']);
	$Article = clean($_POST['article']);

	$ins = "INSERT INTO ".$t_regles." SET section = '".$Section."', valeur = '".$Article."'";
	sql_query($ins);
} elseif(isset($_POST['action']) && $_POST['action'] == 'update') {
	//Update
	$Id = (is_numeric($_POST['id']) ? clean($_POST['id']) : '0');
	$Section = clean($_POST['section']);
	$Article = clean($_POST['article']);
	$up = "UPDATE ".$t_regles." SET section = '".$Section."', valeur = '".$Article."' WHERE id = '".$Id."'";
	sql_query($up);
}


if(!isset($_GET['id']))
{
	

	$Titre = "Ajouter un article";
	$Message = "<form method=\"post\" action=\"?p=admin_regles\">\n"
	. "<strong>Section:</strong><input type=\"text\" name=\"section\" maxlength=\"30\" /><br />\n"
	. "<textarea name=\"article\" rows=\"10\" cols=\"70\">Article...</textarea><br />\n"
	. "<input type=\"submit\" value=\"Enregistrer\" /><input type=\"hidden\" name=\"action\" value=\"new\" /></form>\n";
	bw_fieldset($Titre, $Message);

	echo "<br /><fieldset><legend>Liste des articles</legend>\n";
	$sql = "SELECT * FROM ".$t_regles." ORDER BY section ASC";
	$req = sql_query($sql);
	$oldSection = '';
	while($res = sql_array($req))
	{
		if($oldSection != $res['section'])
		{
			$oldSection = $res['section'];
			echo "<strong>".$oldSection."</strong><br />\n";
		}
		echo "<a href=\"?p=admin_regles&id=".$res['id']."\">".substr($res['valeur'], 0, 80)."...</a><br />\n";
	}
	echo "</fieldset>\n";

	echo "<a href=\"?p=admin_admin\">Retour à l'administration</a><br />\n";

} else {//Modifier un article
	//Verifie si l'id existe
	$id = (isset($_GET['id']) && is_numeric($_GET['id']) ? clean($_GET['id']) : 0);
	$sql = "SELECT * FROM ".$t_regles." WHERE id = '".$id."'";
	$req = sql_query($sql);
	if(sql_rows($req) == 1)
	{
		$res = sql_array($req);

		$Titre = "Modifier l'article n°".$res['id'];
		$Message = "<form method=\"post\" action=\"?p=admin_regles\">\n"
		. "<strong>Section:</strong><input type=\"text\" name=\"section\" maxlength=\"30\" value=\"".$res['section']."\" /><br />\n"
		. "<textarea name=\"article\" rows=\"10\" cols=\"70\">".$res['valeur']."</textarea><br />\n"
		. "<input type=\"submit\" value=\"Enregistrer\" /><input type=\"hidden\" name=\"id\" value=\"".$res['id']."\" /><input type=\"hidden\" name=\"action\" value=\"update\" /></form>\n";
		bw_fieldset($Titre, $Message);

	}

	echo "<a href=\"?p=admin_regles\">Retour à la liste des Articles</a><br />\n";


}



bw_tableau_end();