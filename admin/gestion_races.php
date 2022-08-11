<?php
//header
include ('adminheader.php');


if($_SESSION['aut'][$adminpage['variable_races']] == 0)  breakpage();

if(isset($_POST['race']) && is_numeric($_POST['race']))
{//Update
	$Bonus_1 = (is_numeric($_POST['bonus_1']) ? clean($_POST['bonus_1']) : '0');
	$Bonus_2 = (is_numeric($_POST['bonus_2']) ? clean($_POST['bonus_2']) : '0');
	$Bonus_3 = (is_numeric($_POST['bonus_3']) ? clean($_POST['bonus_3']) : '0');
	$Bonus_4 = (is_numeric($_POST['bonus_4']) ? clean($_POST['bonus_4']) : '0');
	$Id = clean($_POST['race']);

	$Up = "UPDATE info_races SET "
	. "bonus_1 = '".$Bonus_1."', bonus_2 = '".$Bonus_2."', bonus_3 = '".$Bonus_3."', bonus_4 = '".$Bonus_4."'"
	. "WHERE id_race = '".$Id."'";

	sql_query($Up);

	echo "<strong>Bonus mis à jour!</strong><br />\n";
}

echo "	<table class=\"newtable\"><tr>\n";
echo "		<td class=\"newtitre\">Bonnus Races</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newcontenu\">\n";

echo "			<table class=\"newsmalltable\">\n";
echo "			<tr>\n";
echo "				<th>Nom</th>\n";
echo "				<th>Bonus 1</th>\n";
echo "				<th>Bonus 2</th>\n";
echo "				<th>Bonus 3</th>\n";
echo "				<th>Bonus 4</th>\n";
echo "				<th>&nbsp;</th>\n";
echo "			</tr>\n";

$sql = "SELECT * FROM info_races ORDER BY id_race ASC";
$req = sql_query($sql);
while($res = sql_object($req))
{
	echo "			<form method=\"post\" action=\"?p=admin_races\">\n";
	echo "			<tr>\n";
	echo "				<td>".return_guilde($res->id_race, $Joueur->lang)."</th>\n";
	echo "				<td><input type=\"text\" maxlengh=\"3\" size=\"4\" value=\"".$res->bonus_1."\" name=\"bonus_1\" /></td>\n";
	echo "				<td><input type=\"text\" maxlengh=\"3\" size=\"4\" value=\"".$res->bonus_2."\" name=\"bonus_2\" /></td>\n";
	echo "				<td><input type=\"text\" maxlengh=\"3\" size=\"4\" value=\"".$res->bonus_3."\" name=\"bonus_3\" /></td>\n";
	echo "				<td><input type=\"text\" maxlengh=\"3\" size=\"4\" value=\"".$res->bonus_4."\" name=\"bonus_4\" /></td>\n";
	echo "				<td><input type=\"submit\" value=\"Enregistrer\"></td>\n";
	echo "			</tr>\n";
	echo "			<input type=\"hidden\" name=\"race\" value=\"".$res->id_race."\" />\n";
	echo "			</form>\n";
}

echo "			</table>\n";
echo "		</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newfin\">&nbsp;</td>\n";
echo "	</tr><tr>\n";
echo "</table>\n";

echo "<a href=\"?p=admin_admin\">Retour à l'administration</a><br />\n";