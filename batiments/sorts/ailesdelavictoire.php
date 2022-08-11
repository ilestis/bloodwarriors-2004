<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Ailes de la victoire.php
+-----------------------------------------------------
|Description:	Sort Carapace
+-----------------------------------------------------
|Date de création:				12.06.06
|Dernière modification:
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

		//Verifie si on pas déjà le sort en réserve
		$sql = "SELECT `ID` FROM temp_sorts WHERE id_province = '".$_SESSION['id_province']."' AND `id_sort` = '".$Craft_ID."'";
		$req = sql_query($sql);

		if (mysql_num_rows($req) == 1) 
		{//Déjà le sort
			$Message = bw_error("<span class=\"info\">Vous avez déjà un sortilège des Ailes de la Victoire en réserve.</span><br />\n");
		}
		else 
		{	
			if(check_craft($_SESSION['id_province'], $Prix))
			{//On a passé
				//Bonnus Anges
				if($Joueur->race == 1)
				{
					$Temps *= $CONF['bonus_anges_1'];
				}
				$Duree = time() + ($Temps * 3600);

				$ins = "INSERT INTO temp_sorts VALUES ('', '".$_SESSION['id_province']."', '".$Duree."', '".$Craft_ID."', '".$Cible."', '".$Variable."')";
				sql_query($ins);

				$Message = bw_info("Les Ailes de la Victoire accompagne à présent vos unités pour une durée de ".$Temps." heures!<br />\n");
			}
			else
			{
				$Message = bw_error("Vous n'avez pas assez de magie!<br />\n");
			}
		}

	}

	if(isset($Message)) echo $Message;

	//Demande de paramètres
	echo $Titre_Choix;
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";
	
	echo "Invoque des Ailes de la Victoire sur vos unités, pour une durée de ".$Temps." heures.<br />\n";
	
	echo "<INPUT TYPE=\"submit\" value=\"Lancer les Ailes de la Victoire pour ".$Prix." magie.\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"parametre\" value=\"1\">\n\n";

	echo "</FORM>\n";
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège!\n"));
} ?>