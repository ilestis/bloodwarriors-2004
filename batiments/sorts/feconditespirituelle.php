<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Carapace.php
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
$Craft_ID = '17'; 
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
	if (isset($_POST['parametre'])) 
	{//On lance le sort

		
		if(check_craft($_SESSION['id_province'], $Prix))
		{//On a passé

			//Verifie si on a pas dépassé le nombre max de paysans
			$Max = func_MaxPop($_SESSION['id_joueur'], $_SESSION['id_province'], 1);

			//Pop actuelle

			if($Res_Ressources['peasant'] < $Max) 
			{
				$up = "UPDATE temp_paysans SET nombre = (nombre+'1') WHERE section = '0' AND id_province = '".$_SESSION['id_province']."'";
				sql_query($up);

				$up = "UPDATE provinces SET peasant = (peasant+'1') WHERE id = '".$_SESSION['id_province']."'";
				sql_query($up);

				$Message = bw_info("Votre nouveau paysans est à présent disponnible!<br />\n");
			}
			else
			{
				$Message = bw_error("Vous avez le nombre maximum de paysans autorisé pour votre province!<br />\n");
			}
		}
		else
		{
			$Message = bw_error("Vous n'avez pas assez de magie!<br />\n");
		}

	}
	if(isset($Message)) echo $Message;

	//Demande de paramètres
	echo $Titre_Choix;
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";
	
	echo "Fait apparaitre un paysan pour une modique somme de Magie... Attention cependant, ces paysans sont créé à partie de magie. Leur métabolisme n'est pas stable, et c'est contre les loies de Dieu.<br />\n";
	
	echo "<INPUT TYPE=\"submit\" value=\"Procréez un paysan pour ".$Prix." magie.\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"parametre\" value=\"1\">\n\n";

	echo "</FORM>\n";	

} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège!\n"));
} ?>