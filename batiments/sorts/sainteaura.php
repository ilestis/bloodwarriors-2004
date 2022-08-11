<?php
/*----------------------[TABLEAU]---------------------
|Nom:			SainteAura.php
+-----------------------------------------------------
|Description:	Sort Sainte-Aura
+-----------------------------------------------------
|Date de création:				16.07.07
|Dernière modification:			
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}
//Valeurs
$Craft_ID = '21'; 
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

if(sort_available($res->batint, $res->race, $Joueur->race))
{
	if(isset($_POST['go']))
	{//On l'a lancé
		//Verifie si on a assez de magie
		if(check_craft($_SESSION['id_province'], $Prix))
		{//On a passé
			// Prend la satisfaction pour ne pas être au dessus de 100
			$up = "UPDATE provinces SET `satisfaction` = (`satisfaction`+".$Variable.") WHERE id = '".$_SESSION['id_province']."';";
			sql_query($up);

			$upv = "UPDATE provinces SET `satisfaction` = 100 WHERE `satisfaction` > 100 AND id = '".$_SESSION['id_province']."';";
			sql_query($upv);
			$Message = bw_info("La Sainte-Aura redonne espoire en vos habitants!<br />\n");

		}
		else
		{//Not enougth craft
			$Message = bw_error("Vous n'avez pas assez de magie pour lancer ce sortilège!<br />\n");
		}
	}
	
	if(isset($Message)) echo $Message;

	//Demande de paramètres
	echo $Titre_Choix;
	echo "<form method=\"POST\" action=\"index.php?p=bsp_sort\">\n";
	echo "<input type=\"submit\" value=\"Benedictus Benedicat! (".$Prix." magie).\" />\n";
	echo "<input type=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\" />\n";
	echo "<input type=\"hidden\" name=\"go\" value=\"".$Craft_ID."\" />\n";
	echo "</form>\n";
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège!\n"));
} ?>