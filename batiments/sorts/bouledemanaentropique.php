<?php
/*----------------------[TABLEAU]---------------------
|Nom:			BouleDeManaEntropique.php
+-----------------------------------------------------
|Description:	Sort Boule De Mana Entropique
+-----------------------------------------------------
|Date de création:				20.02.06
|Dernière modification:			
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '7'; 

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
$Description	= stripslashes($res->description);

echo "<fieldset><legend>".$Nom."</legend>\n";

if(sort_available($res->batint, $res->race, $Joueur->race)) {
	//Traitement
	if(isset($_POST['parametre']))
	{
		if(check_craft($_SESSION['id_province'], $Prix))
		{//On a passé

			//Variables
			$sql_b = "SELECT "
			.	"a.id, a.nom, a.power_2, a.power_4, a.id_province, a.id_joueur, "
			.	"b.pseudo, b.race, "
			.	"c.name as ProvNom, "
			.	"d.bonus_2, d.bonus_4 "
			. "FROM "
			.	"armees AS a "
			.	"LEFT JOIN joueurs AS b ON b.id = a.id_joueur "
			.	"LEFT JOIN provinces AS c ON c.id = a.id_province "
			.	"LEFT JOIN info_races AS d ON d.id_race = b.race "
			. "ORDER BY RAND() LIMIT 0, 1;";
			$req_b = sql_query($sql_b);
			$nbr_b = mysql_num_rows($req_b);
			$Die = true;

			if($nbr_b == 0)
			{
				$Message = bw_error("Il n'y a aucune unité à viser!<br />\n");
			} 
			else 
			{
				$res = mysql_fetch_array($req_b);
				
				$EnemiProvince = $res['ProvNom'];
				$MaProvince = $_SESSION['nom_province'];

				//On test si elle peut mourir?
				$DefCrea = $res['power_2'] + $res['power_4'] + $res['bonus_2'] + $res['bonus_4'];
				if($DefCrea > $Variable) {
					//Cherche les bonnus et le tralala

					$DEF_sort = "SELECT * FROM temp_sorts WHERE id_province = '".$res['id_province']."'";
					$DEF_sort_req = sql_query($DEF_sort);
					while ($DEF_sort_res = mysql_fetch_array($DEF_sort_req))
					{//Prend chaque sort du défenseur
						if ($DEF_sort_res['boost_id'] == 2) {//Bonnus de defense
							$DefCrea += $DEF_sort_res['boost_value'];
						}
					}

					//Toujours?
					if($DefCrea > $Variable) {
						//Elle survit!
						$Die = false;

					}
				}

				if($Die) {
					//Elle meurt
					$del = "DELETE FROM armees WHERE id = '".$res['id']."';";
					sql_query($del);

					$MessageS = "Un sort de Boule de Mana entropique de la province ".$MaProvince." du Héros ".$Joueur->pseudo." a tué votre unité ".$res['nom']." de votre province ".$EnemiProvince.".";
					$Message = bw_info("Vous avez tué l'unités ".$res['nom']." de la province ".$EnemiProvince." Héros ".$res['pseudo'].".<br />\n");
				} else {
					$MessageS = "Un sort de Boule de Mana entropique de la province ".$MaProvince." du Héros ".$Joueur->pseudo." a echoué de tuer votre unité ".$res['nom']." de votre province ".$EnemiProvince.".";
					$Message = bw_info("Vous n'avez pas tué l'unité ".$res['nom']." de la province ".$EnemiProvince." Héros ".$res['pseudo'].".<br />\n");
				}

				if($res['id_joueur'] != $_SESSION['id_joueur']) send_message(999999995, $res['id_joueur'], $MessageS, 0);
			}
		}
		else 
		{//Pas assez de magie
			$Message = bw_error("Vos efforts sont en vain car vous n'avez pas assez de magie!<br />\n");
		}

	} 
	if(isset($Message)) echo $Message;
	echo "Le boule de mana entropique peut viser n'importe quelle unité, même une en votre possession ou celle d'un membre de votre alliance, et inflige ".$Variable." dégâts à celle-ci.<br />";
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";
	echo "<br /><INPUT TYPE=\"submit\" value=\"Invoquer la ".$Nom." pour ".$Prix." magie.\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"7\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"parametre\" value=\"1\">\n";

	echo "</FORM>\n";
} else {
	
}