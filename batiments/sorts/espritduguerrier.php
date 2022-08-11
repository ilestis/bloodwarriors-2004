<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Squelette.php
+-----------------------------------------------------
|Description:	Squelette d'un sort
+-----------------------------------------------------
|Date de création:				11.02.06
|Dernière modification:
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '9'; 
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
	//Traitement
	if(isset($_POST['parametre']))
	{
		//Verifie si on a assez de magie
		if(check_craft($_SESSION['id_province'], $Prix))
		{//On a passé

			//Verifie si on a un paysan disponnible
			$sql = "SELECT nombre FROM temp_paysans WHERE section = '0' AND id_province = '".$_SESSION['id_province']."'";
			$req = sql_query($sql);
			$res = sql_object($req);

			if($res->nombre > 0)
			{
				//On le transforme
				$up = "UPDATE temp_paysans SET nombre = (nombre-".$Variable.") WHERE section = '0' AND id_province = '".$_SESSION['id_province']."'";
				sql_query($up);
				
				$up = "UPDATE provinces SET peasant = (peasant-".$Variable.") WHERE id = '".$_SESSION['id_province']."'";
				sql_query($up);

				//Créé une unité équivalente dans les armées
				$ins = "INSERT INTO armees VALUES('', '".$_SESSION['id_joueur']."', '".$_SESSION['id_province']."', '999', 
				'Paysan', '1', '2', '1', '-1', '1', '0', '0', '0', '0', '1', 'P')";
				sql_query($ins);

				$Message = bw_info("Votre paysan a été engagé dans les forces militaire, il est à présent près pour le combat!<br />");
			}
			else
			{
				$Message = bw_error( "Vous n'avez pas assez de paysans diponnibles pour lancer ce sortilège.<br />\n");
			}
		}
		else
		{
			//Message
			$Message = bw_error("Vous n'avez pas assez de magie pour lancer ce sortilège!<br />\n");
		}
	}
	if(isset($Message)) echo $Message;

	//Demande de paramètres
	echo $Titre_Choix;
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";
	
	echo "<INPUT TYPE=\"submit\" value=\"Transformer ".$Variable." paysans pour ".$Prix." magie.\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"parametre\" value=\"1\"><br />\n\n";
	echo "</FORM>\n";	

} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège!\n"));
} ?>