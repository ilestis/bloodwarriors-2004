<?php
/*----------------------[TABLEAU]---------------------
|Nom:			NouveauSouffle.php
+-----------------------------------------------------
|Description:	Sort Nouveau Souffle
+-----------------------------------------------------
|Date de cr�ation:				16.01.07
|Derni�re modification:			
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '18'; 
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
	{//On l'a lanc�
		//Verifie si on a assez de magie
		if(check_craft($_SESSION['id_province'], $Prix))
		{//On a pass�
			$up = "UPDATE batiments SET `life` = `life_total` WHERE id_province = '".$_SESSION['id_province']."' AND value = '1'";
			sql_query($up);
			$Message = bw_info("Le Nouveau Souffle redore vos b�timents!<br />\n");

		}
		{//Not enougth craft
			$Message = bw_error("Vous n'avez pas assez de magie pour lancer ce sortil�ge!<br />\n");
		}
	}
	
	if(isset($Message)) echo $Message;

	//Demande de param�tres
	echo $Titre_Choix;
	echo "<form method=\"POST\" action=\"index.php?p=bsp_sort\">\n";
	echo "<input type=\"submit\" value=\"Redonnez TOUTE la vie � vos b�timents pour ".$Prix." magie.\" />\n";
	echo "<input type=\"hidden\" name=\"idsort\" value=\"18\" />\n";
	echo "<input type=\"hidden\" name=\"go\" value=\"".$Craft_ID."\" />\n";
	echo "</form>\n";
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le b�timent n�cessaire pour lancer ce sortill�ge!\n"));
} ?>