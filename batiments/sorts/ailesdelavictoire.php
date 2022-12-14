<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Ailes de la victoire.php
+-----------------------------------------------------
|Description:	Sort Carapace
+-----------------------------------------------------
|Date de cr?ation:				12.06.06
|Derni?re modification:
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '15'; 
$Allowed = FALSE;

//On selectionne les valeurs du sort
$sql = "SELECT * FROM `liste_sorts` WHERE id = '".$Craft_ID."'";
$req = sql_query($sql);
$res = sql_object($req);

//Variables
$Prix			= $res->cout;
$Nom			= $res->nom;
$Variable		= $res->variable;
$Cible			= $res->cible;
$Titre_Choix	= $res->titre_choix;
$Temps			= $res->temps;
$BatInt			= $res->batint;

echo "<fieldset><legend>".$Nom."</legend>\n";

if(sort_available($res->batint, $res->race, $Joueur->race)) {
	if (isset($_POST['parametre'])) 
	{//On lance le sort

		//Verifie si on pas d?j? le sort en r?serve
		$sql = "SELECT `ID` FROM temp_sorts WHERE id_province = '".$_SESSION['id_province']."' AND `id_sort` = '".$Craft_ID."'";
		$req = sql_query($sql);

		if (mysql_num_rows($req) == 1) 
		{//D?j? le sort
			$Message = bw_error("<span class=\"info\">Vous avez d?j? un sortil?ge des Ailes de la Victoire en r?serve.</span><br />\n");
		}
		else 
		{	
			if(check_craft($_SESSION['id_province'], $Prix))
			{//On a pass?
				//Bonnus Anges
				if($Joueur->race == 1)
				{
					$Temps *= $CONF['bonus_anges_1'];
				}
				$Duree = time() + ($Temps * 3600);

				$ins = "INSERT INTO temp_sorts VALUES ('', '".$_SESSION['id_province']."', '".$Duree."', '".$Craft_ID."', '".$Cible."', '".$Variable."')";
				sql_query($ins);

				$Message = bw_info("Les Ailes de la Victoire accompagne ? pr?sent vos unit?s pour une dur?e de ".$Temps." heures!<br />\n");
			}
			else
			{
				$Message = bw_error("Vous n'avez pas assez de magie!<br />\n");
			}
		}

	}

	if(isset($Message)) echo $Message;

	//Demande de param?tres
	echo $Titre_Choix;
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";
	
	echo "Invoque des Ailes de la Victoire sur vos unit?s, pour une dur?e de ".$Temps." heures.<br />\n";
	
	echo "<INPUT TYPE=\"submit\" value=\"Lancer les Ailes de la Victoire pour ".$Prix." magie.\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"parametre\" value=\"1\">\n\n";

	echo "</FORM>\n";
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le b?timent n?cessaire pour lancer ce sortill?ge!\n"));
} ?>