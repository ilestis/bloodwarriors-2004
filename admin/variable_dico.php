<?php
//verifie si la session est en cours
include ('adminheader.php');

$Page = 'variable_dico';
if($_SESSION['aut'][$adminpage['variable_dico']] == 0)  breakpage();

echo "	<table class=\"newtable\">\n";
echo "	<tr>\n";
echo "		<td class=\"newtitre\">Modifier la Charte</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newcontenu\">\n\n";

if(isset($_POST['delid']) and is_numeric($_POST['delid']))
{
	//Supprime
	$del = "DELETE FROM `admin_dico` WHERE `id` = ".$stat."";
	sql_query($sql);
	echo 'Le mot à bien été supprimé<br/>';
}
elseif(isset($_POST['add']))
{//Ajouter


}

echo "			<table class=\"newsmalltable\">\n";
echo "			<tr>\n";
echo "				<th colspan=\"2\">Voir / Supprimer les mots déjà inscrit</th>\n";
echo "			</tr>\n";
echo "			<tr>\n";
echo "				<th width=\"50%\"><strong>Mot</strong></th>\n";
echo "				<th width=\"50%\"><strong>Supprimer</strong></th>\n";
echo "			</tr>\n";

$sql = "SELECT * FROM `admin_dico` order by word ASC";
$req = sql_query($sql);
while($res = mysql_fetch_array($req))
{
	echo "			<tr>\n";
	echo "				<form method=\"POST\" action=\"?p=admin_dico&id=dico&change=del&stat=".$res['id']."\">\n";
	echo "				<td class=\"in\">".$res['word']."</td>\n";
	echo "				<td class=\"in\"><input type=\"submit\" value=\"Supprimer\"></td>\n";
	echo "				</form>\n";
	echo "			</tr>\n";
}

echo "			<tr>\n";
echo "				<th colspan=\"2\">Ajouter un mot</th>\n";
echo "			</tr>\n";
echo "			<tr>\n";
echo "				<td colspan=\"2\">\n";

echo "					<form method=\"POST\" action=\"?p=admin_variable_change&id=dico&change=add\">\n";
echo "					Mot: <INPUT TYPE=\"text\" NAME=\"word\" maxlength=\"25\">\n"; 
echo "					<INPUT TYPE=\"submit\" value=\"Ajouter\">\n";
echo "				</td>\n";
echo "			</tr>\n";
echo "			</table>\n";

echo "		</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newfin\">&nbsp;</td>\n";
echo "	</tr><tr>\n";
echo "	</table>\n\n";

echo "	<a href=\"?p=admin_admin\">Retour à l'administration</a><br />\n";
?>