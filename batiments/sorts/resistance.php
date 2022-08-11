<?php
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '26'; 
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
$Race			= $res->race;

echo "<fieldset><legend>".$Nom."</legend>\n";

if(sort_available($res->batint, $res->race, $Joueur->race)) {
{
	if (isset($_POST['parametre'])) 
	{//On lance le sort
		$Parametre = clean($_POST['parametre']);
		//Verifie l'existance de la province
		$sql = "SELECT a.id, a.id_joueur, a.name, b.pseudo, b.ally_id FROM provinces AS a LEFT JOIN joueurs AS b ON b.id = a.id_joueur WHERE a.id = '".$Parametre."'";
		$req = sql_query($sql);

		if(mysql_num_rows($req) == 1)
		{//La province existe
			$res = sql_array($req);
			// Verifie a nous ou ally
			if ((($Joueur->ally_id != 0) && ($Joueur->ally_id == $res['ally_id'])) || $res['id'] == $_SESSION['id_joueur']) 
			{

				//Verifie si on pas déjà le sort en réserve
				$sql = "SELECT `ID` FROM temp_sorts WHERE id_province = '".$Parametre."' AND `id_sort` = '".$Craft_ID."'";
				$req = sql_query($sql);

				if (mysql_num_rows($req) == 1) 
				{//Déjà le sort
					$Message = bw_error("Cette province possède déjà un sortilège de ".$Nom." en réserve!<br />");
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
						$Temps_Insert = time()+($Temps*3600);

						$ins = "INSERT INTO temp_sorts VALUES ('', '".$_SESSION['id_province']."', '".$Temps_Insert."', '".$Craft_ID."', '".$Cible."', '".$Variable."')";
						sql_query($ins);

						$Message = bw_info("La Résistance accompagne à présent les unités de la province ".$res['name']." pour une durée de ".$Temps." heures!<br />\n");
					}
					else
					{
						$Message = bw_error("Vous n'avez pas assez de magie!<br />\n");
					}
				}
			} else {
				$Message = bw_error("Vous ne pouvez pas cibler cette province!<br />\n");
			}
		} else {
			$Message = bw_error("Cette province n'existe pas!<br />\n");
		}
	}

	if(isset($Message)) echo $Message;

	//Demande de paramètres
	echo $Titre_Choix;
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";	
	echo "Invoque la Résistance sur les unités de la province ciblée, pour un bonus de ".$Variable." pour une durée de ".$Temps." heures.<br />\n";

	echo "<select name=\"parametre\">\n";
	echo "<option value=\"0\">Sélectionnez une province</option>\n";

	$sql = "SELECT ally_id, id, pseudo FROM joueurs";
	$req = sql_query($sql);
	while ($res = sql_array($req))
	{//Prend chaque héros assez fort
		if ((($Joueur->ally_id != 0) && ($Joueur->ally_id == $res['ally_id'])) || $res['id'] == $_SESSION['id_joueur']) 
		{
			//On peut le cibler alors on va chercher ses provinces
			$sqlp = "SELECT id, name FROM provinces WHERE id_joueur = '".$res['id']."'";
			$reqp = sql_query($sqlp);
			while($resp = sql_object($reqp))
			{
				echo "<option value=\"".$resp->id."\">".$res['pseudo']." [".$resp->name."]</option>\n";

			}
		}
	}
	echo "</select><br />\n\n";
		
	echo "<INPUT TYPE=\"submit\" value=\"Lancer une Résitance pour ".$Prix." magie.\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\">\n";

	echo "</FORM>\n";
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège, ou vous êtes de la mauvaise Race!\n"));
} ?>