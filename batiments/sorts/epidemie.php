<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Epidemie.php
+-----------------------------------------------------
|Description:	Sort Charme
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
$Craft_ID = '6'; 
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
	if(isset($_POST['parametre']) && $_POST['parametre'] != "")
	{
		$Cible	=	clean($_POST['parametre']);

		//Verifie l'existance de la province, et prend ses ressources
		$sql = "SELECT id, id_joueur, name, peasant FROM provinces WHERE id = '".$Cible."'";
		$req = sql_query($sql);

		if (mysql_num_rows($req) == 1) 
		{//La province existe

			//Verifie si on a assez de magie
			if(check_craft($_SESSION['id_province'], $Prix))
			{//On a passé

				$res = sql_object($req);

				//Une de nos provinces? Baka!
				if($res->id_joueur != $_SESSION['id_joueur'])
				{

					//Protections
					if(!bw_protections($res->id))
					{

						$Message = bw_error("Une protection entoure la province ennemie et annule l'effet de votre sort!<br />\n");

						$MessageS = "La province ".$_SESSION['name_province']." du joueur ".$Joueur->pseudo." vous a lancé un sortilège de Epidémie, mais une protection a entouré votre province ".$res->name." et a protegé vos paysans.";
					}
					else
					{
						//Calcule la perte de paysan 
						$Epidemie = ceil($res->peasant*0.9);
						if($Epidemie < $CONF['paysans_min']) $Epidemie = $CONF['paysans_min'];

						//Update de la province ennemie
						$Up_Pro = "UPDATE province SET peasant = '".$Epidemie."' WHERE id = '".$Parametre."'";
						sql_query($Up_Pro);

						$Up_Pay = "UPDATE temp_paysans SET nombre = (nombre-".$Perte.") WHERE section = '0' AND  id_provinc = '".$Parametre."'";
						sql_query($Up_Pay);

						$Message = bw_info("Votre sort d'Épidémie a été réussis!<br />\n");

						$MessageS = "Le joueur ".$Joueur->pseudo." vous a lancé un sortilège de Epidémie sur votre province ".$res->name."!";
					}

					//Envoit le message
					send_message(999999995, $res->id_joueur, $MessageS, '1');
				}
				else
				{
					$Message = bw_error("Vous ne pouvez cibler une de vos provinces!<br />\n");
				}
			}
			else {
				$Message = bw_error("Vous n'avez pas assez de magie pour lancer ce sort!<br />\n");
			}
		}
		else {//Erreur
			$Message = bw_error("Cette province n'existe pas!<br />\n");
		}
	}
	if(isset($Message)) echo $Message;


	echo "Selectionnez une province chez qui déclancher une épidémie";
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";
	
	echo "<select name=\"parametre\">\n";
	echo "<option value=\"\">-Sélectionner dans la liste-</option>\n";

	//Puissance minimale 
	$Min_Puissance = min_attack_power($Joueur->puissance, $CONF[$Joueur->race.'_attaque_min'], $Joueur->race, $CONF['bonus_anges_2']);
	

	//prend ou la puissance est plus grand que la puissance minimal
	$sql2 = "SELECT id_joueur, name, id FROM provinces WHERE id_joueur <> '".$_SESSION['id_joueur']."' ORDER BY id_joueur ASC";
	$req2 = sql_query($sql2);
	while ($res2 = mysql_fetch_array($req2))
	{//Prend chaque province
		//Prend le pseudo et vérifie si on peut l'attaquer
		$sql3 = "SELECT pseudo, ally_id, acceslvl, vacances, puissance FROM joueurs WHERE id = '".$res2['id_joueur']."'";
		$req3 = sql_query($sql3);
		$res3 = mysql_fetch_array($req3);


		//Verifie divers truc
		if($Joueur->ally_id == 0) $MonAlly == '---235ggb32--dolardolardolarlolmdrhe';
		else $MonAlly = $Joueur->ally_id;
		if (($res3['acceslvl'] >= 0) AND ($res3['vacances'] == 0) AND ($res3['ally_id'] != $MonAlly) AND ($res3['puissance'] >= $Min_Puissance)) 
		{//On peut attaquer
			echo "<option value=\"".$res2['id']."\">".$res3['pseudo'].": ".$res2['name']."</option>\n";
		}
	}		
	echo "</select><br /><br />\n";
	echo "<INPUT TYPE=\"submit\" value=\"Déclancher une épidémie chez sur la province du héros ciblé pour ".$Prix." magie\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\">\n";

	
	echo bw_info("L'épidémie tue 10% de la population de la province ciblée.");
	echo "</FORM>\n";	
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège!\n"));
} 
?>