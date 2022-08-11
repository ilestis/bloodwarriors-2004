<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Rage.php
+-----------------------------------------------------
|Description:	Sort Rage
+-----------------------------------------------------
|Date de cr�ation:				16.07.07
|Derni�re modification:			
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}
//Valeurs
$Craft_ID = '22'; 
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
{
	if(isset($_POST['go']))
	{//On l'a lanc�
		// D�j� ce sort en r�serve
		$sql = "SELECT `ID` FROM temp_sorts WHERE id_province = '".$_SESSION['id_province']."' AND `id_sort` = '".$Craft_ID."'";
		$req = sql_query($sql);

		if (mysql_num_rows($req) == 1) 
		{//D�j� le sort
			$Message =  bw_info("Vous avez d�j� un sortil�ge de ".$Nom." en r�serve.<br />");
		}
		else {
			//Verifie si on a assez de magie
			if(check_craft($_SESSION['id_province'], $Prix))
			{//On a pass�		
				$Temps = 3600*$Temps;
				
				//Bonnus Anges
				if($Joueur->race == 1)
				{
					$Temps *= $CONF['bonus_anges_1'];
				}
				$Temps_Insert = time()+$Temps;

				$ins = "INSERT INTO temp_sorts VALUES ('', '".$_SESSION['id_province']."', '".$Temps_Insert."', '".$Craft_ID."', '".$Cible."', '".$Variable."')";
				sql_query($ins);

				$Message = bw_info("La rage coule dans les veines de vos unit�s, les rendants plus fortes jusqu'au ".date($CONF['game_timeformat'], $Temps_Insert)."!<br />\n");

			}
			else {//Not enougth craft
				$Message = bw_error("Vous n'avez pas assez de magie pour lancer ce sortil�ge!<br />\n");
			}
		}
	}
	
	if(isset($Message)) echo $Message;

	//Demande de param�tres
	echo $Titre_Choix;
	echo "<form method=\"POST\" action=\"index.php?p=bsp_sort\">\n";
	echo "<input type=\"submit\" value=\"N'ayez pas crainte, vos seront enrag�es mais ne se retournerons pas contre vous! (".$Prix." magie).\" />\n";
	echo "<input type=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\" />\n";
	echo "<input type=\"hidden\" name=\"go\" value=\"".$Craft_ID."\" />\n";
	echo "</form>\n";
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le b�timent n�cessaire pour lancer ce sortill�ge!\n"));
} ?>