<?php
require('adminheader.php');
$t_faq_qe = 'faq_questions';
$t_faq_ru = 'faq_rubriques';


$Page = 'faq';
if($_SESSION['aut'][$adminpage['faq']] == 0)  breakpage();

echo "<h2>Modification de la FAQ</h2>\n";


$flag = 0;
if(isset($_GET['do']))
{
	if($_GET['do'] == 'rubrique')
	{
		if($_GET['action'] == 'new')
		{
			//Nouvelle rubrique
			$Name = clean($_POST['name']);
			$Desc = '';

			$sql = "INSERT INTO ".$t_faq_ru." VALUES('', '".$Name."', '0', '')";
			sql_query($sql);
			$Message = "Rubrique ajoutée!";
		}
		elseif($_GET['action'] == 'delete')
		{
			//ID
			$ID = clean($_GET['rubid']);
			$del = "DELETE FROM ".$t_faq_ru." WHERE `id` = '".$ID."'";
			sql_query($del);

			//Supprime questions de la rubrique
			$del2 = "DELETE FROM ".$t_faq_qe." WHERE id_rubrique = '".$ID."'";
			sql_query($del2);

			$Message = "Rubrique bien supprimée.<br />\n";
		}
	}
	elseif($_GET['do'] == 'question')
	{
		if($_GET['action'] == 'new')
		{
			//Nouvelle question
			$Question = clean($_POST['question']);
			$Reponse = forummessage(clean($_POST['reponse']));
			$ID_Rub = clean($_POST['rubrique']);
			$MotsCles = clean($_POST['motscles']);

			$sql = "INSERT INTO ".$t_faq_qe." VALUES('', '".$ID_Rub."', '".$Question."', '".$Reponse."', '".time()."', '".$MotsCles."')";
			sql_query($sql);
			$Message = "Question ajoutée!";

		}
		elseif($_GET['action'] == 'delete')
		{
			//ID (Rubrique?...)
			$ID = clean($_GET['idquestion']);
			$del = "DELETE FROM ".$t_faq_qe." WHERE `id` = '".$ID."'";
			sql_query($del);

			$Message = "Question bien supprimée.<br />\n";

		}
		elseif($_GET['action'] == 'update')
		{
			//Update question
			$ID = clean($_POST['idquestion']);
			$Question = clean($_POST['question']);
			$Reponse = forummessage(clean($_POST['reponse']));
			$ID_Rub = clean($_POST['rubrique']);
			$MotsCles = clean($_POST['motscles']);

			$sql = "UPDATE ".$t_faq_qe." SET 
				`id_rubrique` = '".$ID_Rub."', 
				`question` = '".$Question."', 
				`reponse` = '".$Reponse."', 
				`date` = '".time()."',
				`motscles` = '".$MotsCles."'
				WHERE id = '".$ID."'";
			sql_query($sql);
			$Message = "Question mis à jour!";

		}
	}
}

//Affiche les rubriques dispo
echo $Message."<br />\n";
echo "	<table class=\"newtable\"><tr>\n";
echo "		<td class=\"newtitre\">Rubriques</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newcontenu\">\n";
$sql = "SELECT id, name FROM ".$t_faq_ru." ORDER BY name ASC";
$req = sql_query($sql);
while($res = sql_object($req))
{
	if(isset($_GET['id']) && is_numeric($_GET['id']) && ($res->id == $_GET['id']))
		echo "<strong><a href=\"?p=admin_faq&see=rubrique&id=".$res->id."\">".$res->name."</a></strong>[<a href=\"?p=admin_faq&do=rubrique&action=delete&rubid=".$res->id."\">x</a>]&nbsp; &nbsp;";
	else
		echo "<a href=\"?p=admin_faq&see=rubrique&id=".$res->id."\">".$res->name."</a>&nbsp; &nbsp;";
}
echo "			<br /><br />\n";

//Nouvelle Rubrique
echo "			<h3>Nouvelle Rubrique</h3>\n";
echo "			<form name=\"frm1\" method=\"post\" action=\"?p=admin_faq&do=rubrique&action=new\" />\n";
echo "				Nom: <input type=\"text\" name=\"name\" maxlength=\"255\" />\n";
echo "				<input type=\"submit\"  value=\"Créer\" /><br />\n";
echo "			</form>\n";
echo "		</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newfin\">&nbsp;</td>\n";
echo "	</tr><tr>\n";
echo "</table>\n";

echo "<br />\n";

if(isset($_GET['see']) && ($_GET['see'] == 'rubrique') && is_numeric($_GET['id']))
{
	$ID_R = clean($_GET['id']);
	echo "	<table class=\"newtable\"><tr>\n";
	echo "		<td class=\"newtitre\">Visionneuse inter rubrique</td>\n";
	echo "	</tr><tr>\n";
	echo "		<td class=\"newcontenu\">\n";
	echo "			<table class=\"newsmalltable\">\n";
	echo "			<tr><th>Question</th><th>Date</th><th>Supprimer</th></tr>\n";

	$sql = "SELECT * FROM ".$t_faq_qe." WHERE id_rubrique = '".$ID_R."' ORDER BY question ASC";
	$req = sql_query($sql);
	while($res = sql_object($req))
	{
		echo "			<tr>\n";
		echo "				<td><a href=\"?p=admin_faq&see=question&id=".$res->id."\">".$res->question."</a></td>\n";
		echo "				<td>".date($CONF['game_timeformat'], $res->date)."</td>\n";
		echo "				<td><a href=\"?p=admin_faq&do=question&action=delete&idquestion=".$res->id."&see=rubrique&id=".$ID_R."\">x</a></td>\n";
		echo "			</tr>\n";
	}
	echo "			</table>\n";

	//Ajouter une question sous cette rubrique
	echo "			<h3>Nouvelle Question</h3>\n";
	echo "			<form name=\"frm1\" method=\"post\" action=\"?p=admin_faq&do=question&action=new&see=rubrique&id=".$ID_R."\" />\n";
	echo "				Question: <input type=\"text\" name=\"question\" maxlength=\"255\" /><br />\n";
	echo "				Réponse:<br /><textarea name=\"reponse\" rows=\"7\" cols=\"60\">...</textarea><br />\n";
	echo "				Mots Clés:<input type=\"text\" name=\"motscles\" maxlength=\"255\" /> &nbsp;\n";
	echo "				<input type=\"hidden\" name=\"rubrique\" value=\"".$ID_R."\" /><input type=\"submit\" value=\"Valider\" /><br />\n";
	echo "			</form>\n";
	echo "		</td>\n";
	echo "	</tr><tr>\n";
	echo "		<td class=\"newfin\">&nbsp;</td>\n";
	echo "	</tr><tr>\n";
	echo "</table>\n";
}
elseif(($_GET['see'] == 'question') && is_numeric($_GET['id']))
{
	//Voir une question
	$ID_R = clean($_GET['id']);
	echo "	<table class=\"newtable\"><tr>\n";
	echo "		<td class=\"newtitre\">Question N°".$ID_R."</td>\n";
	echo "	</tr><tr>\n";
	echo "		<td class=\"newcontenu\">\n";

	$sql = "SELECT * FROM ".$t_faq_qe." WHERE id = '".$ID_R."'";
	$req = sql_query($sql);
	$res = sql_object($req);

	echo "			<form name=\"frm1\" method=\"post\" action=\"?p=admin_faq&do=question&action=update&see=rubrique&id=".$res->id_rubrique."\">\n";
	echo "				Rubrique: <select name=\"rubrique\">";
	
	//Liste rubriques
	$sqlr = "SELECT id, name FROM ".$t_faq_ru." ORDER BY name ASC";
	$reqr = sql_query($sqlr);
	while($resr = sql_object($reqr))
	{
		$selected = '';
		if($resr->id == $res->id_rubrique) $selected = " selected";
		echo "					<option value=\"".$resr->id."\" ".$selected.">".$resr->name."</option>\n";
	}
	echo "				</select><br />\n";

	echo "				Question: <input type=\"text\" name=\"question\" maxlength=\"255\" value=\"".$res->question."\" /><br />\n";
	echo "				Réponse:<br /><textarea name=\"reponse\" rows=\"7\" cols=\"60\">".$res->reponse."</textarea><br />\n";
	echo "				Mots Clés:<input type=\"text\" name=\"motscles\" maxlength=\"255\" value=\"".$res->motscles."\" /><br />\n";
	echo "				<input type=\"submit\" value=\"Valider\" /><input type=\"hidden\" name=\"idquestion\" value=\"".$res->id."\" /><br /> \n";
	echo "			</form>\n";
	echo "		</td>\n";
	echo "	</tr><tr>\n";
	echo "		<td class=\"newfin\">&nbsp;</td>\n";
	echo "	</tr><tr>\n";
	echo "</table>\n";

}

echo "<a href=\"?p=admin_admin\">Retour à l'administration</a><br />\n";