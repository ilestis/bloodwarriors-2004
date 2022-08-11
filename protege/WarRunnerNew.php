<?php
/*----------------------[TABLEAU]---------------------
|Nom:			WarRunner.Php
+-----------------------------------------------------
|Description:	Le fichier qui calcule chaque guerre (unité par unité)
+-----------------------------------------------------
|Date de création:				12/02/05
|Date du premier test:			26/03/05
|Dernière modification[Auteur]: 05/05/07[Escape]
+-----------------------------------------------------
|Mise en forme:
| - Choisi chaque guerre qui arrive a therme.
| -- Si une guerre passe à zéro:
| - Choisi les bâtiments ennemis
| - Selectionne chaque unités attaquantes
| - Met à jour les stats de la créature attaquantes + effet de la technique
| - Choisi une créature adverse la mieux placée pour défendre/bâtiment défensive/bâtiment
| - Calcule le résultat, et met en place des variable de gain/perte, éventuellement tue l'une des 2 unités/abime les bâtis
| - Calcule les gains/perte de ressources.
| - Calcule les dégats effectués aux bâtiments 
| - Calcule la conquête
| - Rappel la page s'il reste des guerres à exécuter
+---------------------------------------------------*/
//verifie qu'on a activé le GET password
if(htmlentities($_GET['pyjama']) != 'dz542km')
{//pas ok
	exit;
}//arrête le code

//Includes
global $CONF;
require('../include/variables.inc.php');
require_once('../include/fonction.php'); //Les fonctions
require_once('../include/function_mef.php');
require_once('../class/class.MySql.php');

$csql = new sql();

//Lanche connection
if(!$csql->connection($GLOBALS['CONF']['game_DB_user'], $GLOBALS['CONF']['game_DB_psw'] ,  $GLOBALS['CONF']['game_DB_server'], $GLOBALS['CONF']['game_DB_name'])) {
	echo $csql->error();
	die("Impossible de se connecter au serveur!");
}

$Bln_Cree_HTML = false;						//Variable pour la création du rapport

//Message dans le journal des admins [viré car flood dans le journal x)
//$action = "<img src=\"images/admin/ok.png\">Guerres Runner Lancé.";
//journal_admin('CronTab', $action);

//message du débug
$debug = "<br /><strong>Runner des guerres.</strong><br />\n";

//on prend les guerres qui doivent se dérouler en ordre chronologique
$Sql_War = "SELECT * FROM guerres WHERE `time_guerre` < '".time()."' AND etat = '1' AND type = '0' ORDER BY id_guerre ASC"; 
$Req_War = sql_query($Sql_War);
$WarStackNum = sql_rows($Req_War); //Nombre de guerres;
while($res = sql_array($Req_War))
{ //Prend les données
	$WAR['id'] = $res['id_guerre'];
	
	//Met à jour l'état de la guerres, pour verifier les bugs de timeout
	$Update_War_Status = "Update guerres SET etat = '2' WHERE id_guerre = '".$WAR['id']."'";
	sql_query($Update_War_Status);
	
	//Une guerre se déroule même si le mec est en vacance. On peut pas éviter une guerre engagée.
	
	//Récupère les valeurs de la table en tableau
	$ATT['province_id'] = $res['att_pro_id'];
	$ATT['id'] 			= $res['att_id'];
	$ATT['tech'] 		= $res['att_tech'];
	
	$DEF['province_id'] = $res['def_pro_id'];
	$DEF['id'] 			= $res['def_id'];
	$DEF['tech'] 		= $res['def_tech'];

	//Récupère les données des joueurs et des provinces
//Attaquant
	$att_p_sql = "SELECT a.*, b.pseudo, b.race FROM provinces AS a LEFT JOIN joueurs AS b ON b.id = a.id_joueur WHERE a.id = '".$ATT['province_id']."'";
	$att_p_req = sql_query($att_p_sql);
	$att_p_res = sql_array($att_p_req);
	$ATT['gold']			= $att_p_res['gold'];
	$ATT['food']			= $att_p_res['food'];
	$ATT['wood']			= $att_p_res['wood'];
	$ATT['stone']			= $att_p_res['stone'];
	$ATT['craft']			= $att_p_res['craft'];
	$ATT['x']				= $att_p_res['x'];
	$ATT['y']				= $att_p_res['y'];
	$ATT['province_name']	= $att_p_res['name'];
	$ATT['gold_gain']		= 0;
	$ATT['pseudo']			= $att_p_res['pseudo'];
	$ATT['race']			= $att_p_res['race'];
	$ATT['satisfaction']	= $att_p_res['satisfaction'];

	//Nombre de provinces conquise par l'attaquant?
	$conqu_sql = "SELECT id FROM provinces WHERE id_joueur = '".$ATT['id']."'"; 
	$conqu_req = sql_query($conqu_sql);
	$NbProvincesAttaquant = sql_rows($conqu_req);
	
//Defenseur
	$def_p_sql = "SELECT 
		a.gold, a.food, a.wood, a.stone, a.craft, a.x, a.y, a.name, a.peasant, 
		a.muraille_bois, a.muraille_pierre, a.muraille_granit, a.type_province, a.satisfaction, 
		b.pseudo, b.race 
	FROM 
		provinces AS a 
	LEFT JOIN 
		joueurs AS b ON b.id = a.id_joueur 
	WHERE a.id = '".$DEF['province_id']."'";
	$def_p_req = sql_query($def_p_sql);
	$def_p_res = sql_array($def_p_req);
	$DEF['gold']			= $def_p_res['gold'];
	$DEF['food']			= $def_p_res['food'];
	$DEF['wood']			= $def_p_res['wood'];
	$DEF['stone']			= $def_p_res['stone'];
	$DEF['craft']			= $def_p_res['craft'];
	$DEF['x']				= $def_p_res['x'];
	$DEF['y']				= $def_p_res['y'];
	$DEF['province_name']	= $def_p_res['name'];
	$DEF['pesants']			= $def_p_res['peasant'];
	$DEF['gold_gain']		= 0;
	$DEF['pseudo']			= $def_p_res['pseudo'];
	$DEF['race']			= $def_p_res['race'];
	$DEF['type_province']	= $def_p_res['type_province'];
	$DEF['satisfaction']	= $def_p_res['satisfaction'];

	// Nombre d'unité du défenseur
	$def_nb_uni = sql_rows(sql_query("SELECT id FROM armees WHERE id_province = '".$DEF['province_id']."' AND dispo = '1'"));

	// ---------- Batiments du defenseur
	$Def_Bati_Barri = 0; // Bois
	$Def_Bati_Murai = 0; // Pierre
	$Def_Bati_Grani = 0; // Granit

	//Muraille bois & pierre
	if($def_p_res['muraille_bois'] > 0) {
		$Def_Bati_Barri	= $def_p_res['muraille_bois'];
	}
	if($def_p_res['muraille_pierre'] > 0) {
		$Def_Bati_Murai	= $def_p_res['muraille_pierre'];
	}
	if($def_p_res['muraille_granit'] > 0) {
		$Def_Bati_Grani	= $def_p_res['muraille_granit'];
	}

	//On met chaque batiments dans un array
	$sql = "SELECT id, life, codename  FROM batiments WHERE id_province = '".$DEF['province_id']."' AND life > 0";
	$req = sql_query($sql);
	$Def_Nb_Batiments = 0; //Compteur de batiments normaux
	while($resbati = sql_object($req))
	{
		$Def_BatiID[$Def_Nb_Batiments] = $resbati->id;
		$Def_BatiLife[$Def_Nb_Batiments] = $resbati->life;
		$Def_Nb_Batiments++;
	}
	
	$debug .= "C'est partit!<br />\n";
	$debug .= "GUERRE ".$ATT['pseudo']." VS ".$DEF['pseudo']."<br />\n";

	//Temp de guerre
	$TempRetour = calc_WarTime($ATT['province_id'], $DEF['province_id'], $CONF['war_time'], $CONF['war_min'], $CONF['vitesse_jeu']);
	
	//Initialisation des variables du runner
	$DEF['kills'] = 0; //nombre de créatures que le defenseur a tué
	$ATT['kills'] = 0; //...que l'attaquant a tué
	$DEF['batidef'] = 0; //defense bonus du defenseur avec les bâtiments
	$ATT['total'] = 0; //dégâts totaux causés pas l'attaquant
	$ATT['overallpowa'] = 0; //Puissance total de lattaquant
	$DEF['overallpowa'] = 0; //Puissance total du defenseur
	$ATT['nombrecrea'] = 0; //nombre d'unités qui attaquent
	$DEF['nombrecrea'] = 0; //nombre d'unités qui ont défendues

	//selectionne nos bonnus guilde...
	$bonusatt = "SELECT bonus_1, bonus_2, bonus_3 FROM `info_races` WHERE `id_race` = '".$ATT['race']."'";
	$bonusatta = sql_query($bonusatt);
	$bonusattaquant = sql_array($bonusatta);
	$ATT['bonus1'] = $bonusattaquant['bonus_1'];
	$ATT['bonus2'] = $bonusattaquant['bonus_2'];
	$ATT['bonus3'] = $bonusattaquant['bonus_3'];
	$ATT['bonus_force'] = $ATT['bonus1']+$ATT['bonus3'];
	$ATT['bonus_defense'] = $ATT['bonus2'];
	$debug .= "Bonus attaquant: ".$ATT['bonus_force']."/".$ATT['bonus_defense']."<br />\n";
	

	$bonusdef = "SELECT bonus_1, bonus_2, bonus_4 FROM `info_races` WHERE `id_race` = '".$DEF['race']."'";
	$bonusdeff = sql_query($bonusdef);
	$bonusdefenseur = sql_array($bonusdeff);
	$DEF['bonus1'] = $bonusdefenseur['bonus_1'];
	$DEF['bonus2'] = $bonusdefenseur['bonus_2'];
	$DEF['bonus4'] = $bonusdefenseur['bonus_4'];
	$DEF['bonus_force'] = $ATT['bonus1'];
	$DEF['bonus_defense'] = $ATT['bonus2']+$DEF['bonus4'];
	$debug .= "Bonus defenseur: ".$DEF['bonus_force']."/".$DEF['bonus_defense']."<br />\n";

	//prend les murailles, baricades du defenseur

	//Etat par défaut
	$Var_Etat_Defense = 1;
	

	//$DEF['overallpowa'] += $DEF['batidef'];

	//-------------------------------------------------------------------------------------
	//-----Prend les sorts de chaque joueur---------------------------------------
	$ATT_sort = "SELECT boost_id, boost_value FROM temp_sorts WHERE id_province = '".$ATT['province_id']."'";
	$ATT_sort_req = sql_query($ATT_sort);
	while ($ATT_sort_res = sql_array($ATT_sort_req))
	{//Prend chaque sort de l'attaquant
		if ($ATT_sort_res['boost_id'] == 1) {//Bonnus d'attaque
			$ATT['bonus_force'] += $ATT_sort_res['boost_value'];
		}
		elseif ($ATT_sort_res['boost_id'] == 2) {//Bonnus de defense
			$ATT['bonus_defense'] += $ATT_sort_res['boost_value'];
		}
		elseif ($ATT_sort_res['boost_id'] == 4) {//Bonnus +x+x+0+0
			$ATT['bonus_defense'] += $ATT_sort_res['boost_value'];
			$ATT['bonus_force'] += $ATT_sort_res['boost_value'];
		}
	}

	$DEF_sort = "SELECT boost_id, boost_value FROM temp_sorts WHERE id_province = '".$DEF['province_id']."'";
	$DEF_sort_req = sql_query($DEF_sort);
	$Def_Sort_Mur = 0;
	while ($DEF_sort_res = sql_array($DEF_sort_req))
	{//Prend chaque sort du défenseur
		if ($DEF_sort_res['boost_id'] == 1) {//Bonnus d'attaque
			$DEF['bonus_force'] += $DEF_sort_res['boost_value'];
		}
		elseif ($DEF_sort_res['boost_id'] == 2) {//Bonnus de defense
			$DEF['bonus_defense'] += $DEF_sort_res['boost_value'];
		}
		elseif ($DEF_sort_res['boost_id'] == 3) {//Bonnus defense ville
			//$DEF['batidef'] += $DEF_sort_res['boost_value'];	
			
			//On ajoute la valeur a la Barricade, car on part du principe 
			$Def_Sort_Mur += $DEF_sort_res['boost_value'];
		}
		elseif ($DEF_sort_res['boost_id'] == 4) {//Bonnus de +x+x+0+0
			$DEF['bonus_defense'] += $DEF_sort_res['boost_value'];
			$DEF['bonus_force'] += $DEF_sort_res['boost_value'];
		}
	}//-----------------------------------END SORT DE BOOST-------------------------
	//------------------------------------------------------------------------------


	//techniques
	$ATT['techbonus'] = 0;
	$DEF['techbonus'] = 0;
	switch ($ATT['tech'])
	{
		//Def:
		//1: Barricade
		//2: Pièges
		//3: Muraille
		//4: Convert
		//5: Anti-camouflage
		case 1:
			//l'attaque utilise l'attaque par front
			if ($DEF['tech'] == 1) $DEF['techbonus'] = 1;
			if ($DEF['tech'] == 5) $ATT['techbonus'] = 1;
			break;
		case 2:
			//l'attaque utilise l'attaque en Vagues
			if ($DEF['tech'] == 2) $DEF['techbonus'] = 1;
			if ($DEF['tech'] == 4) $ATT['techbonus'] = 1;
			break;
		case 3:
			//l'attaque utilise l'attaque en Cercle
			if ($DEF['tech'] == 3) $DEF['techbonus'] = 1;
			if ($DEF['tech'] == 1) $ATT['techbonus'] = 1;
			break;
		case 4:
			//l'attaque utilise l'attaque en Retrait
			if ($DEF['tech'] == 4) $DEF['techbonus'] = 1;
			if ($DEF['tech'] == 3) $ATT['techbonus'] = 1;
			break;
		case 5:
			//L'attaquant utilise l'attaque en camouflage
			if ($DEF['tech'] == 5) $DEF['techbonus'] = 1;
			if ($DEF['tech'] == 2) $ATT['techbonus'] = 1;
			break;
		default:
			$ATT['techbonus'] = 1;
	}//fin bonnus techniques
	$ATT['bonus_force'] += $ATT['techbonus'];
	$DEF['bonus_force'] += $DEF['techbonus'];

	// PILLAGE: Pour savoir si on a pillé ou pas la province
	$PillageGeneral = 0;

	//selectionne chaque créature attaquante par la plus forte // On a pas besoin de prendre l'id de la province, vu que l'id de la guerre est unique
	$creature = "SELECT id, power_1, power_2, power_3, nom FROM `armees` WHERE `id_joueur` = '".$ATT['id']."' AND `dispo` = '2' AND id_guerre = '".$WAR['id']."' ORDER BY `power_1` DESC"; //prend la plus forte

	//Tableau pour les unités
	$messageunite = '<table width="100%"><tr><th>Attquante</th><th>Défendeuse</th><th>Résultat</th></tr>';

	//$debug .= "Selection des unités de l'attaquant:<br />".$creature."<br />\n";
	$reply = sql_query($creature);
	while($unite = sql_array($reply))
	{//prend chaque créature ordrée par force
		//met ses stats (avec bonus)
		$ATT['nombrecrea'] ++; //incrémente d'un attaquant

		//Calcule la force total de l'unité
		$ATTU['force'] = ($unite['power_1'] + $unite['power_3']) + ($ATT['bonus_force']);
		//$ATTU['force'] = ($unite['power_1']+$ATT['bonus1']) + ($unite['power_3']) + $ATT['techbonus'];

		//Calcule l'endurance total de l'unité
		$ATTU['endurance'] = $unite['power_2'] + $ATT['bonus_defense'];

		//Id de l'unité
		$ATTU['id'] = $unite['id'];

		//Puissance Overall incrémentée
		$ATT['overallpowa'] += ($ATTU['force'] + $ATTU['endurance']);


		$debug .= '<B>Attaquant[Id:'.$ATTU['id'].']</B>Force: '.$ATTU['force'].' / Endurence: '.$ATTU['endurance'].'<br />';

		//On va chercher son nom...
		//$U_NomSql = "SELECT nom FROM invocation WHERE `ID` = '".$unite['ID_creature']."'";
		//$U_NomReq = sql_query($U_NomSql);
		//$U_NomRes = mysql_fetch_array($U_NomReq);
		$messageunite .= "<tr><td>".$unite['nom']." [".$ATTU['force']."/".$ATTU['endurance']."]</td>";

		//selectionne un adversaire de taille
		//besoin
		//$minimumatt = $ATT['force'] - $DEF['bonus1'];
		//$minimumdef = $ATT['endurence'] - ($DEF['bonus3']+$DEF['bonus4']);

		$hasdied = FALSE; //la créature attaquante n'a pas été tuée ou utilisée
		//la variable hasdied permet de savoir si on continue a chercher un defenseur ou pas.

		//Essaye de trouver une unités capable de tuer l'attaquant sans mourir
		$ForceDemandee = $ATTU['endurance'] - $DEF['bonus_force'];
		$EnduranceDemandee = $ATTU['force'] - $DEF['bonus_defense'];

		// Si on a au moins une unité
		if($def_nb_uni > 0) 
		{

			if(!$hasdied)
			{
				//On cherche une créature capable de tuer l'attaquant et de résister
				$sat = "SELECT power_1, power_2, power_4, nom, id FROM armees WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' AND `power_1` >= '".$ForceDemandee."' AND (power_2+power_4) > '".$EnduranceDemandee."' LIMIT 0, 1";
				$cha = sql_query($sat);
				if(mysql_num_rows($cha) == 1)
				{//On a trouvé une unité
					$die = sql_array($cha);

					//Enregistre qu'on a tué l'unité
					$hasdied = true;

					//Message
					$DEFU_Force =  $die['power_1'] + $DEF['bonus_force'];
					$DEFU_Endur =  $die['power_2'] + $die['power_4'] + $DEF['bonus_defense'];
					$messageunite .= "<td>".$die['nom'].": [".$DEFU_Force."/".$DEFU_Endur."]</td>";
					$messageunite .= "<td>Attaquant tué!</td></tr>";
						
					//Overall Power
					$DEF['overallpowa'] += $DEFU_Force + $DEFU_Endur;

					//Tue l'attaquant
					$ATTU_Die = "DELETE FROM armees WHERE `id` = '".$ATTU['id']."'";
					sql_query($ATTU_Die);

					//Incrémente les gains/kills
					$DEF['gold_gain']		+= 2;
					$DEF['kills'] ++;
					$DEF['nombrecrea'] ++;

					//Update l'état de l'unité
					$DEFU_Dispo = "UPDATE armees SET dispo = '3' WHERE `id` = '".$die['id']."'";
					sql_query($DEFU_Dispo);

					//Debug
					$debug .= '<strong>Defenseur[Id:'.$die['id'].']</strong> Force: '.$DEFU_Force.' / Endurance: '.$DEFU_Endur.' : Attaquant tué.<br />';
				
				}//On a tué l'attaquant
			}

			if(!$hasdied)
			{
				//On cherche une unité capable de résister, mais sans la tuer.
				$sat = "SELECT power_1, power_2, power_4, nom, id FROM armees WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' AND (power_2+power_4) > '".$EnduranceDemandee."' LIMIT 0, 1";
				$cha = sql_query($sat);
				if(mysql_num_rows($cha) == 1)
				{//On a trouvé une unité
					$die = sql_array($cha);

					//Enregistre qu'on a tué l'unité
					$hasdied = true;

					//Message
					$DEFU_Force =  $die['power_1'] + $DEF['bonus_force'];
					$DEFU_Endur =  $die['power_2'] + $die['power_4'] + $DEF['bonus_defense'];
					$messageunite .= "<td>".$die['nom'].": [".$DEFU_Force."/".$DEFU_Endur."]</td>";
					$messageunite .= "<td>Aucune unité ne meurt!</td></tr>";

					//Overall Power
					$DEF['overallpowa'] += $DEFU_Force + $DEFU_Endur;

					//Incrémente les gains/kills
					$DEF['nombrecrea'] ++;

					//Update l'état des unités
					$DEFU_Dispo = "UPDATE armees SET dispo = '3' WHERE `id` = '".$die['id']."'";
					sql_query($DEFU_Dispo);

					$ATTU_Dispo = "UPDATE armees SET dispo = '4', `heureretour` = '".$TempRetour."' WHERE `id` = '".$ATTU['id']."'";
					sql_query($ATTU_Dispo);

					// Décrémente le nombre d'unité dispo chez le defenseur
					$def_nb_uni--;

					//Debug
					$debug .= '<strong>Defenseur[Id:'.$die['id'].']</strong> Force: '.$DEFU_Force.' / Endurance: '.$DEFU_Endur.' : Aucun mort.<br />';
				
				}//On a résiste
			}

			if(!$hasdied)
			{
				//On cherche une créature ou les 2 unités meurts
				$sat = "SELECT power_1, power_2, power_4, nom, id FROM armees WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' AND `power_1` >= '".$ForceDemandee."' AND sacrifice = '1' LIMIT 0, 1";
				$cha = sql_query($sat);
				if(mysql_num_rows($cha) == 1)
				{//On a trouvé une unité
					$die = sql_array($cha);

					//Enregistre qu'on a tué l'unité
					$hasdied = true;

					//Message
					$DEFU_Force =  $die['power_1'] + $DEF['bonus_force'];
					$DEFU_Endur =  $die['power_2'] + $die['power_4'] + $DEF['bonus_defense'];
					$messageunite .= "<td>".$die['nom'].": [".$DEFU_Force."/".$DEFU_Endur."]</td>";
					$messageunite .= "<td>Les deux unités meurent!</td></tr>";

					//Overall Power
					$DEF['overallpowa'] += $DEFU_Force + $DEFU_Endur;

					//Tue les unités
					$ATTU_Die = "DELETE FROM armees WHERE `id` = '".$ATTU['id']."'";
					sql_query($ATTU_Die);
					$DEFU_Die = "DELETE FROM armees WHERE `id` = '".$die['id']."'";
					sql_query($DEFU_Die);

					//Incrémente les gains/kills
					$ATT['gold_gain'] += 1;
					$DEF['gold_gain'] += 1;
					$ATT['kills'] ++;
					$DEF['kills'] ++;
					$DEF['nombrecrea'] ++;

					// Décrémente le nombre d'unité dispo chez le defenseur
					$def_nb_uni--;

					//Debug
					$debug .= '<strong>Defenseur[Id:'.$die['id'].']</strong> Force: '.$DEFU_Force.' / Endurance: '.$DEFU_Endur.' : Double Kill.<br />';
				
				}//On a tué l'attaquant
			}

			if(!$hasdied)
			{
				//Cherche une unité avec le sacrifice, la plus faible du lot.
				$sat = "SELECT power_1, power_2, power_4, nom, id FROM armees WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' AND sacrifice = '1' ORDER BY puissance ASC LIMIT 0, 1";
				$cha = sql_query($sat);
				if(mysql_num_rows($cha) == 1)
				{//On a trouvé une unité
					$die = sql_array($cha);

					//Enregistre qu'on a tué l'unité
					$hasdied = true;

					//Message
					$DEFU_Force =  $die['power_1'] + $DEF['bonus_force'];
					$DEFU_Endur =  $die['power_2'] + $die['power_4'] + $DEF['bonus_defense'];
					$messageunite .= "<td>".$die['nom'].": [".$DEFU_Force."/".$DEFU_Endur."]</td>";
					$messageunite .= "<td>L'unité défendeuse meurt!</td></tr>";
					
					//Overall Power
					$DEF['overallpowa'] += $DEFU_Force + $DEFU_Endur;

					//Tue l'unité defense
					$DEFU_Die = "DELETE FROM armees WHERE `id` = '".$die['id']."'";
					sql_query($DEFU_Die);

					//Update unité attaquante
					$ATTU_Dispo = "UPDATE armees SET dispo = '4', `heureretour` = '".$TempRetour."' WHERE `id` = '".$ATTU['id']."'";
					sql_query($ATTU_Dispo);

					//Incrémente les gains/kills
					$ATT['gold_gain'] += 2;
					$ATT['kills'] ++;
					$DEF['nombrecrea'] ++;

					// Décrémente le nombre d'unité dispo chez le defenseur
					$def_nb_uni--;

					//Debug
					$debug .= '<strong>Defenseur[Id:'.$die['id'].']</strong> Force: '.$DEFU_Force.' / Endurance: '.$DEFU_Endur.' : Def Die!<br />';

				}//Sacrifice
			}
		}
		
		if(!$hasdied)
		{
			//Plus d'unité, on s'attaque aux bâtiments

			$ATT['total'] += $ATTU['force']; //dégâts totaux

			$debug .= 'Les unités attaquantes attaquent la ville ---> ';

			$ATTU_Dispo = "UPDATE armees SET dispo = '4', `heureretour` = '".$TempRetour."' WHERE `id` = '".$ATTU['id']."'";
			sql_query($ATTU_Dispo);

			//Message
			$messageunite .= "<td colspan=\"2\">";

			if($Def_Sort_Mur > 0) {
				$debug .= ' Mur magic';
				$messageunite .= 'Sort de protection Magique';
				$Def_Sort_Mur -= $ATTU['force'];;
			}
			elseif($Def_Bati_Barri > 0 || $Def_Bati_Murai > 0 || $Def_Bati_Grani)
			{
				$debug .= ' plaque';
				$Var_Etat_Defense = 0; //On se prend les murailles et cie
				//$messageunite .= "&nbsp;&nbsp;&nbsp;L'unité se fait bloquer par les bâtiments defensifes.\n";
				//On touche les defense
				if($Def_Bati_Barri > 0)
				{//on s'en prend à la barricade
					$Def_Bati_Barri -= $ATTU['force'];
					$messageunite .= "Plaques de Bois";
				}
				elseif($Def_Bati_Murai > 0)
				{//On s'en prend à la muraille
					$Def_Bati_Murai -= $ATTU['force'];
					$messageunite .= "Plaques de Pierres";
				}
				elseif($Def_Bati_Grani > 0)
				{//On s'en prend à la granit
					$Def_Bati_Grani -= $ATTU['force'];
					$messageunite .= "Plaques de Granit";
				}
			}
			else
			{//On endomage au hasard un batiment
				$Var_Etat_Defense = 2; //Enregistre qu'on a attaqué les batiments
				$debug .= ' Bâtiment de province';

				$Bln_A_Endomage = false;

				if($Def_Nb_Batiments > 0)
				{
					$rand = rand(1, $Def_Nb_Batiments)-1;

					if($Def_BatiLife[$rand] > 0)
					{
						//Si on est barbare
						if($ATT['race'] == 2)
						{
							$ATTU['force'] *= $CONF['bonus_barbares_plus'];
						}
						$Def_BatiLife[$rand] -= $ATTU['force'];
						$Bln_A_Endomage = true;
					}
				}

				if(!$Bln_A_Endomage) {
					$messageunite .= "Pillage général";
					$PillageGeneral++;
				} else {
					$messageunite .= "Bâtiment de la province";
				}
			}
			$messageunite .= "</td></tr>";

			//$debug .= 'L\'unité attaque des batiments de la ville<br />';

			$hasdied = TRUE;
		}//fin pas d'unités defense

	}//END WHILE CHAQUE ATTAQUANT
	$messageunite .= "</table>";

	$debug .= "Fin des distributions des attaques<br />\n";
			

	//-------------------------------------------------------------
	//--------------------CALCUL LE RESULTAT-----------------------
	//messages du rapport
	$message = "Voici le résultat de la guerre de ".$ATT['pseudo'].":".$ATT['province_name']." qui attaquait ".$DEF['pseudo'].":".$DEF['province_name'].". \n \n";
	$message .= "L'attaquant ".$ATT['pseudo']." a attaqué avec un total de ".$ATT['nombrecrea']." unités. Sa puissance totale s'est élevée à ".$ATT['overallpowa']." \n \n";
	$message .= "Le defenseur ".$DEF['pseudo']." s'est défendu avec ".$DEF['nombrecrea']." unités. Sa puissance totale s'est élevée à ".$DEF['overallpowa']." \n ";
	$message .= "Voici le rapport détailé: \n ";
	$message .= $messageunite." \n ";
	$message .= "L'attaquant a tué au total ".$ATT['kills']." unités, et le défenseur en a tué ".$DEF['kills']." \n "; 


	if($Var_Etat_Defense == 2)
	{//l'attaquant a pénetré la défense du défenseur
		$debug .= '<B>Le defenseur c\'est fait envahir, il perd des ressources.</B><br />';

		//calcule les pertes des ressources du defenseur (or, nourriture, bois, et pierre)
		$gain = floor($ATT['total']/2.5); //arrondi au bas
		$DEF['loss_gold']	= $DEF['gold']	- $gain;
		$DEF['loss_food']	= $DEF['food']	- $gain;
		$DEF['loss_wood']	= $DEF['wood']	- $gain;
		$DEF['loss_stone']	= $DEF['stone']	- $gain;

		//gain max de l'attaquant
		$gaingold	= $gain; 
		$gainfood	= $gain; 
		$gainwood	= $gain; 
		$gainstone	= $gain;

		//verifie si on passe pas en négatif
		if($DEF['loss_gold'] < 0) { $DEF['loss_gold'] = 0; $gaingold = $DEF['gold']; }
		if($DEF['loss_food'] < 0) { $DEF['loss_food'] = 0; $gainfood = $DEF['food']; }
		if($DEF['loss_wood'] < 0) { $DEF['loss_wood'] = 0; $gainwood = $DEF['wood']; }
		if($DEF['loss_stone'] < 0) { $DEF['loss_stone'] = 0; $gainstone = $DEF['stone']; }

		//vrai gains de l'attaquant
		$ATT['win_gold'] = $ATT['gold'] + $gaingold + $PillageGeneral;
		$ATT['win_food'] = $ATT['food'] + $gainfood + $PillageGeneral;
		$ATT['win_wood'] = $ATT['wood'] + $gainwood + $PillageGeneral;
		$ATT['win_stone'] = $ATT['stone'] + $gainstone + $PillageGeneral;

		//On calcule la perte de paysans du defenseur
		$DEF['new_pesants'] = round($DEF['pesants']/$CONF['war_paysans_kill']);

		//Bonnus Démons
		if($ATT['race'] == 2)
		{
			$DEF['new_pesants'] = round($DEF['pesants']/($CONF['war_paysans_kill']*$CONF['bonus_barbares_plus']));
		}

		//Nombre réellement perdu
		$DEF['loss_pesants'] = $DEF['pesants']-$DEF['new_pesants'];
		
		//On verifie la somme
		if($DEF['new_pesants'] < $CONF['paysans_min']) $DEF['new_pesants'] = $CONF['paysans_min'];

		//update
		$ATTUPR = "UPDATE provinces SET 
			`gold` = '".$ATT['win_gold']."',
			`food` = '".$ATT['win_food']."',
			`wood` = '".$ATT['win_wood']."',
			`stone` = '".$ATT['win_stone']."',
			`satisfaction` = (satisfaction+".$CONF['war_satisfaction']."),
			`victoires` = (victoires+1)
		WHERE `id` = '".$ATT['province_id']."'";
		sql_query($ATTUPR);

		// Défenseur
		$DEFUPR = "UPDATE provinces SET 
			`gold` = '".$DEF['loss_gold']."',
			`food` = '".$DEF['loss_food']."',
			`wood` = '".$DEF['loss_wood']."',
			`stone` = '".$DEF['loss_stone']."',
			`pertes` = (pertes+1),
			`muraille_bois` = '".$Def_Bati_Barri."',
			`muraille_pierre` = '".$Def_Bati_Murai."',
			`muraille_granit` = '".$Def_Bati_Grani."',
			peasant = '".$DEF['new_pesants']."'
		WHERE `id` = '".$DEF['province_id']."'";
		sql_query($DEFUPR);

		sql_query("UPDATE temp_paysans SET nombre = (nombre-".$DEF['loss_pesants'].") WHERE section = 0 AND id_province = '".$DEF['province_id']."'");

		//On calcule la perte des bâtiments
		for($x = 0; $x < $Def_Nb_Batiments; $x++)
		{
			//Met a jour le bâtiments
			if($Def_BatiLife[$x] < 0)
			{
				$Def_BatiLife[$x] = 0;
			}
			$sql = "UPDATE batiments SET life = '".$Def_BatiLife[$x]."' WHERE id = '".$Def_BatiID[$x]."' AND id_province = '".$DEF['province_id']."'";
			sql_query($sql);
		}
		
		$wining = 'ATT'; //variable qui permet de savoir qui a gagné
	
		//messages
		$message .= "La guerre c'est terminée avec la victoire de l'attaquant ".$ATT['pseudo'].". \n";
		$message .= "Il vole en tout ".$gaingold." d'Or, ".$gainfood." de Nourriture, ".$gainwood." de Bois et ".$gainstone." de Pierre. \n";
		$message .= "En plus, le defenseur perd ".$DEF['loss_pesants']." paysans. \n ";

		// Conquête?
		if(($DEF['satisfaction'] - $CONF['war_satisfaction']) >= $CONF['war_sat_min'] && ($DEF['type_province'] > 0) && ($NbProvincesAttaquant > $CONF['province_max_nb'])) {
			// L'attaquant prend possession de la province!
			$debug .= "<strong>L'attaquant prend possession de la province!";
			$message ."<strong>Conquête!</strong>L'attaquant prend possession de la province ".$DEF['province_name']."!\n";

			// Update
			$up_prov = "UPDATE provinces SET id_joueur = '".$ATT['id']."', type_province = '2', satisfaction = '".$CONF['war_conquest_satisfaction']."' WHERE id = '".$DEF['province_id']."';";
			sql_query($up_prov);

			//Unités
			$up_uni = "UPDATE armees SET id_joueur = '".$ATT['id']."' WHERE id_joueur = '".$DEF['id']."' AND id_province = '".$DEF['province_id']."';";
			sql_query($up_uni);

			//Batiments
			sql_query("UPDATE batiments SET id_joueur = '".$ATT['id']."' WHERE id_province = '".$DEF['province_id']."';");

			//Paysans
			sql_query("UPDATE temp_paysans SET id_joueur = '".$ATT['id']."' WHERE id_province = '".$DEF['province_id']."';");

			//Echanges
			sql_query("DELETE FROM echanges WHERE envoyeur = '".$DEF['province_id']."' OR recepteur = '".$DEF['province_id']."'");

			//Guerres
			$wars = "SELECT id_guerre FROM guerres WHERE def_pro_id = '".$DEF['province_id']."' AND id_guerre <> '".$WAR['id']."';";
			$warr = sql_query($wars);
			while($war_res = sql_array($warr)) {
				//Annule les guerres et envoit un message
				$message_war = "Notre guerre contre la province ".$DEF['province_name']."' du Héros '".$DEF['pseudo']."' a été annulée, car celle-ci a été conquérie par le Héros ".$ATT['pseudo']."!";
				send_message(999999992, $war_res['att_id'], addslashes($message_war), 1);

				//Update unité et guerre
				sql_query("UPDATE armees SET dispo = '1' WHERE id_guerre = '".$war_res['id_guerre']."'");
				sql_query("DELETE FROM guerres WHERE id_guerre = '".$war_res['id_guerre']."'");
			}
			unset($war_res);
		}

	}//attaquant gagne end
	elseif($Var_Etat_Defense == 1)
	{//défenseur gagne
		//mise à jour des level (scores)		
		sql_query("UPDATE provinces SET victoires = (victoires+1), `satisfaction` = (`satisfaction`+".$CONF['war_satisfaction'].") WHERE id = '".$DEF['province_id']."'");
		sql_query("UPDATE provinces SET pertes = (pertes+1), `satisfaction` = (`satisfaction`-".$CONF['war_satisfaction'].") WHERE id = '".$ATT['province_id']."'");

		$debug .= "<strong>Le defenseur gagne.</strong><br />\n";
		
		$message .= "Les défenses du defenseur ont submergées les unités de l'attaquant. Le defenseur a donc gagné. \n ";

		$wining = 'DEF';

		//Met a les plaques

	}
	elseif($Var_Etat_Defense == 0)
	{//égalité
		$debug .= '<strong>Égalité.</strong><br />';

		$wining = 'NONE';

		$message .= "La guerre s'est terminée par un matche nul. \n Les défenses du défenseurs ont encaissées le restant des dégâts causés par l'attaquant. \n ";
	}//end verification
	
	//Gain d'or
	$debug .= "Gain d'or<br />\n";
	
	if($ATT['race'] == 5)
		$ATT['gold_gain'] *= $CONF['bonus_rebelles_1'];
	if($DEF['race'] == 5)
		$DEF['gold_gain'] *= $CONF['bonus_rebelles_1'];

	$message .= " \n De plus, l'attaquant a gagné ".$ATT['gold_gain']." Or pour les unités qu'il a tué, et le defenseur en gagne ".$DEF['gold_gain'].". \n";
	//met les messages privés
	send_message(999999992, $ATT['id'], addslashes($message), 1);
	send_message(999999992, $DEF['id'], addslashes($message), 1);

	sql_query("UPDATE provinces SET gold = (gold+".$ATT['gold_gain'].") WHERE id = '".$ATT['province_id']."'");
	sql_query("UPDATE provinces SET gold = (gold+".$DEF['gold_gain'].") WHERE id = '".$DEF['province_id']."'");

	$debug .= 'Messages envoyés<br />';

	//update les créatures en mode dispo
	$unitesd = "UPDATE armees SET dispo = '1' WHERE `dispo` = '3' AND id_province = '".$DEF['province_id']."'";
	sql_query($unitesd);
	$unitesa = "UPDATE armees SET dispo = '4' WHERE `dispo` = '3' AND id_province = '".$ATT['province_id']."'";
	sql_query($unitesa);
	$debug .= 'Update des créatures...<br />';

	//update les ressources
	//$debug .= '<B>Message</B>:<br />'.$message.'<br/>';

	//supprime la guerre
	$del = "DELETE FROM guerres WHERE id_guerre = '".$WAR['id']."'";
	sql_query($del);
	$debug .= 'Supression.<br /><br />';

	//------- UNITÉ DE RETOUR
	$debug .= "<H3>Retour des unités</H3><br />\n";
	sql_query("UPDATE armees SET heureretour = '0', dispo = '1' WHERE dispo = '4' AND heureretour <= '".time()."'");
	//-----------------------------------------------------

	if($Bln_Cree_HTML)
	{
		//Créé le fichier html
		ob_start();

		echo $debug;

		$cache=ob_get_contents();
		ob_end_clean();

		//Ici : la partie pour la cache de la page web
		$nom = 'RapportTour'.time().'.html';
		$lieu = '../protege/rapports/'.$nom;

		//On définit quelle fonctions utiliser
		$version = explode('.',phpversion());

		if($version[0] == 5) {
			file_put_contents($lieu,$cache );
		}
		else
		{
			$operation = fopen($lieu, 'w');
			fwrite($operation, $cache);
			fclose($operation);
		}
	}
	else
	{
		echo $debug;
	}

	//On recharge la page pour relancer le temps d'exécution à zéro
	if($WarStackNum > 1) {	
		echo "<meta http-equiv=refresh content=0;URL=\"WarRunnerNew.php?pyjama=dz542km\">\n";
		exit();
	}
}//end selection des guerres
echo $debug;
?>