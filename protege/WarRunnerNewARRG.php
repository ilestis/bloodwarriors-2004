<?php
/*----------------------[TABLEAU]---------------------
|Nom:			WarRunner.Php
+-----------------------------------------------------
|Description:	Le fichier qui calcule chaque guerre (unité par unité)
+-----------------------------------------------------
|Date de création:				12/02/05
|Date du premier test:			26/03/05
|Dernière modification[Auteur]: 06/09/07[Escape]
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
$debug = '';// ARRG: ca peut valoir la peine d'utiliser les ob_start et faire des echo car 
			// Si ta string est méga longue ca devient lourd

//------- UNITÉ DE RETOUR
$debug .= "<H3>Retour des unités</H3>\n";
sql_query("UPDATE armees SET heureretour = '0', dispo = '1' WHERE dispo = '4' AND heureretour <= '".time()."'");
//-----------------------------------------------------

$debug .= "<strong>Runner des guerres.</strong><br />\n"; 

//on prend les guerres qui doivent se dérouler en ordre chronologique

// ARRG: compte du nombre de guerres à effectuer
$sqlCount = "SELECT id_guerre FROM guerres WHERE time_guerre < ".time()." AND etat = 1 AND type = 0";
$reqCount = sql_query($sqlCount);
$WarStackNum = sql_rows($reqCount);

// ARRG: On selectionne une guerre et on va l'executer
$Sql_War = "SELECT * FROM guerres WHERE `time_guerre` < '".time()."' AND etat = '1' AND type = '0' ORDER BY id_guerre ASC LIMIT 1";  // ARRG: ajouté LIMIT
$Req_War = sql_query($Sql_War);
while($res = sql_array($Req_War))
{ //Prend les données
	$WAR['id'] = $res['id_guerre'];
	
	//Met à jour l'état de la guerres, pour verifier les bugs de timeout
	$Update_War_Status = "Update guerres SET etat = '2' WHERE id_guerre = '".$WAR['id']."' LIMIT 1"; // ARRG: ajouté LIMIT
	sql_query($Update_War_Status);
	
	//Une guerre se déroule même si le mec est en vacance. On peut pas éviter une guerre engagée.
	
	// ARRG: The pies will soon be mine !
	
	//Récupère les valeurs de la table en tableau
	$ATT['province_id'] = $res['att_pro_id'];
	$ATT['id'] 			= $res['att_id'];
	$ATT['tech'] 		= $res['att_tech'];
	
	$DEF['province_id'] = $res['def_pro_id'];
	$DEF['id'] 			= $res['def_id'];
	$DEF['tech'] 		= $res['def_tech'];

	//Récupère les données des joueurs et des provinces
//Attaquant
	$att_p_sql = "SELECT a.*, b.pseudo, b.race, b.ally_id FROM 
		provinces AS a 
			LEFT JOIN joueurs AS b ON b.id = a.id_joueur 
		WHERE a.id = '".$ATT['province_id']."' LIMIT 1"; // ARRG: ajouté LIMIT, pas sur si c'est juste, à vérifier !
	$att_p_req = sql_query($att_p_sql);
	$att_p_res = sql_array($att_p_req);
	$ATT['gold']			= $att_p_res['gold'];
	$ATT['food']			= $att_p_res['food'];
	$ATT['mat']				= $att_p_res['mat'];
	$ATT['craft']			= $att_p_res['craft'];
	$ATT['x']				= $att_p_res['x'];
	$ATT['y']				= $att_p_res['y'];
	$ATT['province_name']	= $att_p_res['name'];
	$ATT['gold_gain']		= 0;
	$ATT['pseudo']			= $att_p_res['pseudo'];
	$ATT['race']			= $att_p_res['race'];
	$ATT['satisfaction']	= $att_p_res['satisfaction'];
	$ATT['esclaves']		= $att_p_res['esclaves'];
	$ATT['ally_id']			= $att_p_res['ally_id'];
	$ATT['ally_maj']		= func_RaceMajorite($ATT['ally_id']);

	//Nombre de provinces conquise par l'attaquant?
	$conqu_sql = "SELECT id FROM provinces WHERE id_joueur = '".$ATT['id']."'"; 
	$conqu_req = sql_query($conqu_sql);
	$NbProvincesAttaquant = sql_rows($conqu_req);
	
//Defenseur
	$def_p_sql = "SELECT 
		a.gold, a.food, a.mat, a.craft, a.x, a.y, a.name, a.peasant,
		a.muraille_normal, a.muraille_enchante, a.muraille_magie, a.type_province, a.satisfaction, a.esclaves,  
		b.pseudo, b.race, b.ally_id
	FROM 
		provinces AS a 
	LEFT JOIN 
		joueurs AS b ON b.id = a.id_joueur 
	WHERE a.id = '".$DEF['province_id']."' LIMIT 1"; // ARRG: pas sur si le LIMIT marche, à checker
	$def_p_req = sql_query($def_p_sql);
	$def_p_res = sql_array($def_p_req);
	$DEF['gold']			= $def_p_res['gold'];
	$DEF['food']			= $def_p_res['food'];
	$DEF['mat']				= $def_p_res['mat'];
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
	$DEF['esclaves']		= $def_p_res['esclaves'];
	$DEF['ally_id']			= $def_p_res['ally_id'];
	$DEF['ally_maj']		= func_RaceMajorite($DEF['ally_id']);

	// Nombre d'unité du défenseur
	$def_nb_uni = sql_rows(sql_query("SELECT id FROM armees WHERE id_province = '".$DEF['province_id']."' AND dispo = '1'"));

	// ---------- Batiments du defenseur
	
	// ARRG: recodé ce bout
	$Def_Bati_Barri = $def_p_res['muraille_normal'];
	$Def_Bati_Murai = $def_p_res['muraille_enchante'];
	$Def_Bati_Grani = $def_p_res['muraille_magie'];

	//On met chaque batiments dans un array
	$sql = "SELECT id, life, codename FROM batiments WHERE id_province = '".$DEF['province_id']."' AND life > 0 AND id_batiment < 65000";
	$req = sql_query($sql);
	$Def_Nb_Batiments = 0;
	while($resbati = sql_object($req))
	{
		$Def_BatiID[$Def_Nb_Batiments] = $resbati->id;
		$Def_BatiLife[$Def_Nb_Batiments] = $resbati->life;
		$Def_Nb_Batiments++;
	}
	$Def_Nb_Batiments_Vivants = $Def_Nb_Batiments;
	
	$debug .= "C'est parti !<br/>\n";
	$debug .= "GUERRE ".$ATT['pseudo']." VS ".$DEF['pseudo']."<br/>\n";

	//Temp de guerre
	$TempRetour = calc_WarTime($ATT['province_id'], $DEF['province_id'], $CONF['war_time'], $CONF['war_min'], $CONF['vitesse_jeu'], $ATT['ally_id']);
	
	//Initialisation des variables du runner
	$DEF['kills'] = 0; //nombre de créatures que le defenseur a tué
	$ATT['kills'] = 0; //...que l'attaquant a tué
	$DEF['batidef'] = 0; //defense bonus du defenseur avec les bâtiments
	$ATT['total'] = 0; //dégâts totaux causés pas l'attaquant
	$ATT['overallpowa'] = 0; //Puissance total de lattaquant
	$DEF['overallpowa'] = 0; //Puissance total du defenseur
	$ATT['nombrecrea'] = 0; //nombre d'unités qui attaquent
	$DEF['nombrecrea'] = 0; //nombre d'unités qui ont défendues

	// Compteur pour les dégâts infligués
	$ATT['degats_batiments'] = 0;
	
	// Bonnus de force sur les batiments
	$ATT['bonus_degats_batiments'] = 1;
	if($ATT['race'] == 2)
	{
		$ATT['bonus_degats_batiments'] *= $CONF['bonus_barbares_plus'];
	}

	//selectionne nos bonnus guilde...
	$bonusatt = "SELECT bonus_1, bonus_2, bonus_3 FROM `info_races` WHERE `id_race` = '".$ATT['race']."' LIMIT 1"; // ARRG: Ajouté LIMIT
	$bonusatta = sql_query($bonusatt);
	$bonusattaquant = sql_array($bonusatta);
	$ATT['bonus1'] = $bonusattaquant['bonus_1'];
	$ATT['bonus2'] = $bonusattaquant['bonus_2'];
	$ATT['bonus3'] = $bonusattaquant['bonus_3'];
	$ATT['bonus_force'] = $ATT['bonus1']+$ATT['bonus3'];
	$ATT['bonus_defense'] = $ATT['bonus2'];
	$debug .= "Bonus attaquant: ".$ATT['bonus_force']."/".$ATT['bonus_defense']."<br />\n";
	

	$bonusdef = "SELECT bonus_1, bonus_2, bonus_4 FROM `info_races` WHERE `id_race` = '".$DEF['race']."' LIMIT 1"; // ARRG: Limit
	$bonusdeff = sql_query($bonusdef);
	$bonusdefenseur = sql_array($bonusdeff);
	$DEF['bonus1'] = $bonusdefenseur['bonus_1'];
	$DEF['bonus2'] = $bonusdefenseur['bonus_2'];
	$DEF['bonus4'] = $bonusdefenseur['bonus_4'];
	$DEF['bonus_force'] = $DEF['bonus1'];
	$DEF['bonus_defense'] = $DEF['bonus2']+$DEF['bonus4'];
	$debug .= "Bonus defenseur: ".$DEF['bonus_force']."/".$DEF['bonus_defense']."<br />\n";

	// Bonnus ATT Anges/Démon ALLY
	if($ATT['ally_maj'] == '1' || $ATT['ally_maj'] == '3') {
		$bonusatt = "SELECT bonus_1, bonus_2, bonus_3 FROM `info_races` WHERE `id_race` = '".$ATT['ally_maj']."' LIMIT 1";
		$bonusatta = sql_query($bonusatt);
		$bonusattaquant = sql_array($bonusatta);
		$ATT['bonus1'] = $bonusattaquant['bonus_1'];
		$ATT['bonus2'] = $bonusattaquant['bonus_2'];
		$ATT['bonus3'] = $bonusattaquant['bonus_3'];
		$ATT['bonus_force'] = $ATT['bonus1']+$ATT['bonus3'];
		$ATT['bonus_defense'] = $ATT['bonus2'];
	}
	
	// Bonnus DEF Anges/Démon ALLY
	if($DEF['ally_maj'] == '1' || $DEF['ally_maj'] == '3') {
		$bonusdef = "SELECT bonus_1, bonus_2, bonus_4 FROM `info_races` WHERE `id_race` = '".$DEF['ally_maj']."' LIMIT 1";
		$bonusdeff = sql_query($bonusdef);
		$bonusdefenseur = sql_array($bonusdeff);
		$DEF['bonus1'] = $bonusdefenseur['bonus_1'];
		$DEF['bonus2'] = $bonusdefenseur['bonus_2'];
		$DEF['bonus4'] = $bonusdefenseur['bonus_4'];
		$DEF['bonus_force'] = $DEF['bonus1'];
		$DEF['bonus_defense'] = $DEF['bonus2']+$DEF['bonus4'];
	}

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
		elseif ($ATT_sort_res['boost_id'] == 5) {//Bonnus +x+x+x+x
			// Force
			if($ATT_sort_res['boost_value'][0] == '+') $ATT['bonus_force'] += $ATT_sort_res['boost_value'][1];
			elseif($ATT_sort_res['boost_value'][0] == '-') $ATT['bonus_force'] -= $ATT_sort_res['boost_value'][1];
			// Defense
			if($ATT_sort_res['boost_value'][2] == '+') $ATT['bonus_force'] += $ATT_sort_res['boost_value'][3];
			elseif($ATT_sort_res['boost_value'][2] == '-') $ATT['bonus_force'] -= $ATT_sort_res['boost_value'][3];
			// Attaque
			if($ATT_sort_res['boost_value'][4] == '+') $ATT['bonus_defense'] += $ATT_sort_res['boost_value'][5];
			elseif($ATT_sort_res['boost_value'][4] == '-') $ATT['bonus_defense'] -= $ATT_sort_res['boost_value'][5];
		}
		elseif ($ATT_sort_res['boost_id'] == 31) { // Dégâts aux bâtiments
			$ATT['bonus_degats_batiments'] *= $ATT_sort_res['boost_value'];
			// Barbare avec chant
			// 1.5 * 2 = force*3!! xD
			// Barbare avec cri
			// 1.5 * 0.5 = force*0.75
			// Autre avec cri
			// 1 * 0.5 = force*0.5
			// Autre avec cri + chant
			// 2 * 0.5 = force*1
			// Barbare avec cri + chant
			// 1.5 * 2 * 0.5 = 1.5
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
		elseif ($DEF_sort_res['boost_id'] == 5) {//Bonnus +x+x+x+x
			// Force
			if($DEF_sort_res['boost_value'][0] == '+') $DEF['bonus_force'] += $DEF_sort_res['boost_value'][1];
			elseif($DEF_sort_res['boost_value'][0] == '-') $DEF['bonus_force'] -= $DEF_sort_res['boost_value'][1];
			// Endurance
			if($DEF_sort_res['boost_value'][2] == '+') $DEF['bonus_force'] += $DEF_sort_res['boost_value'][3];
			elseif($DEF_sort_res['boost_value'][2] == '-') $DEF['bonus_force'] -= $DEF_sort_res['boost_value'][3];
			// Defense
			if($DEF_sort_res['boost_value'][6] == '+') $DEF['bonus_force'] += $DEF_sort_res['boost_value'][7];
			elseif($DEF_sort_res['boost_value'][6] == '-') $DEF['bonus_force'] -= $DEF_sort_res['boost_value'][7];
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

	// Array qui contient les ID des unités qui meurent
	$aDEATH = array();

	// On cherche le nombre d'unité chez le défenseur qui ont le sacrifice, pour la boucle d'attaque continue.
	$sat = "SELECT COUNT(id) FROM armees WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' AND sacrifice = '1'";
	$cha = sql_query($sat);
	$NbrSacrifice = mysql_num_rows($cha);

	//selectionne chaque créature attaquante par la plus forte // On a pas besoin de prendre l'id de la province, vu que l'id de la guerre est unique
	$creature = "SELECT id, power_1, power_2, power_3, nom FROM `armees` 
		WHERE `id_joueur` = '".$ATT['id']."' AND `dispo` = '2' AND id_guerre = '".$WAR['id']."' 
		ORDER BY `power_1` DESC"; //prend la plus forte
		// ARRG: en théorie tu devrais ordrer by "power1 + power3"

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


		//$debug .= '<B>Attaquant[Id:'.$ATTU['id'].']</B>Force: '.$ATTU['force'].' / Endurence: '.$ATTU['endurance'].'<br />';

		//On va chercher son nom...
		//$messageunite .= "<tr><td>".$unite['nom']." [".$ATTU['force']."/".$ATTU['endurance']."]</td>";

		//selectionne un adversaire de taille
		//besoin
		//$minimumatt = $ATT['force'] - $DEF['bonus1'];
		//$minimumdef = $ATT['endurence'] - ($DEF['bonus3']+$DEF['bonus4']);

		$hasdied = false; //la créature attaquante n'a pas été tuée ou utilisée
		//la variable hasdied permet de savoir si on continue a chercher un defenseur ou pas.

		//Essaye de trouver une unités capable de tuer l'attaquant sans mourir
		$ForceDemandee = $ATTU['endurance'] - $DEF['bonus_force'];
		$EnduranceDemandee = $ATTU['force'] - $DEF['bonus_defense'];

		// Si on a au moins une unité restante chez le def
		if($def_nb_uni > 0) 
		{

			if(!$hasdied) // si l'attaquant vit tjrs
			{
				//On cherche une créature capable de tuer l'attaquant et de résister
				$sat = "SELECT power_1, power_2, power_4, nom, id FROM armees 
					WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' 
					AND `power_1` >= '".$ForceDemandee."' AND (power_2+power_4) > '".$EnduranceDemandee."' 
					LIMIT 1";
				$cha = sql_query($sat);
				if(mysql_num_rows($cha) == 1) //On a trouvé une unité
				{
					$die = sql_array($cha);

					//Enregistre qu'on a tué l'unité
					$hasdied = true;

					//Message
					$DEFU_Force =  $die['power_1'] + $DEF['bonus_force'];
					$DEFU_Endur =  $die['power_2'] + $die['power_4'] + $DEF['bonus_defense'];
					//$messageunite .= "<td>".$die['nom'].": [".$DEFU_Force."/".$DEFU_Endur."]</td>";
					//$messageunite .= "<td>Attaquant tué!</td></tr>";
						
					//Overall Power
					$DEF['overallpowa'] += $DEFU_Force + $DEFU_Endur;

					//Tue l'attaquant
					array_push ($aDEATH, $ATTU['id']);
					

					//Incrémente les gains/kills
					$DEF['gold_gain'] += 2;
					$DEF['kills'] ++;
					$DEF['nombrecrea'] ++;

					//Update l'état de l'unité
					/* ARRG: est-ce que les unités devraient pas pourvoir défendre plus d'une fois ? 
					* C'est chiant à gérer si tu veux bien faire 
					* Mais tu peux faire une méthode bourrine: dire que si une unité a commencé à defendre, elle finit de defendre
					* Des nouvelles unités attaquantes jusqu'a sa mort. Faut juste aussi checker pas que ca tue des unités qui ont
					* Pas le sacrifice. */
					$DEFU_Dispo = "UPDATE armees SET dispo = '3' WHERE `id` = '".$die['id']."' LIMIT 1"; // ARRG: Limit
					sql_query($DEFU_Dispo);

					//Debug
					//$debug .= '<strong>Defenseur[Id:'.$die['id'].']</strong> Force: '.$DEFU_Force.' / Endurance: '.$DEFU_Endur.' : Attaquant tué.<br />';
				
				}//On a tué l'attaquant
			}

			if(!$hasdied)
			{
				//On cherche une unité capable de résister, mais sans tuer l'attaquant.
				$sat = "SELECT power_1, power_2, power_4, nom, id FROM armees 
					WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' AND (power_2+power_4) > '".$EnduranceDemandee."' 
					LIMIT 1";
				$cha = sql_query($sat);
				if(mysql_num_rows($cha) == 1)
				{//On a trouvé une unité
					$die = sql_array($cha);

					//Enregistre qu'on a tué l'unité ARRG: ou plutôt qu'elle a été bloquée quoi xD
					$hasdied = true;

					//Message
					$DEFU_Force =  $die['power_1'] + $DEF['bonus_force'];
					$DEFU_Endur =  $die['power_2'] + $die['power_4'] + $DEF['bonus_defense'];
					//$messageunite .= "<td>".$die['nom'].": [".$DEFU_Force."/".$DEFU_Endur."]</td>";
					//$messageunite .= "<td>Aucune unité ne meurt!</td></tr>";

					//Overall Power
					$DEF['overallpowa'] += $DEFU_Force + $DEFU_Endur;

					//Incrémente les gains/kills
					$DEF['nombrecrea'] ++;

					//Update l'état des unités
					$DEFU_Dispo = "UPDATE armees SET dispo = '3' WHERE `id` = '".$die['id']."' LIMIT 1"; // ARRG: Limit
					sql_query($DEFU_Dispo);

					// Décrémente le nombre d'unité dispo chez le defenseur
					$def_nb_uni--;

					//Debug
					//$debug .= '<strong>Defenseur[Id:'.$die['id'].']</strong> Force: '.$DEFU_Force.' / Endurance: '.$DEFU_Endur.' : Aucun mort.<br />';
				
				}//On a résiste
			}

			if(!$hasdied)
			{
				//On cherche une créature ou les 2 unités meurent
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
					//$messageunite .= "<td>".$die['nom'].": [".$DEFU_Force."/".$DEFU_Endur."]</td>";
					//$messageunite .= "<td>Les deux unités meurent!</td></tr>";

					//Overall Power
					$DEF['overallpowa'] += $DEFU_Force + $DEFU_Endur;

					//Tue les unités
					array_push ($aDEATH, $ATTU['id'], $die['id']);

					//Incrémente les gains/kills
					$ATT['gold_gain'] += 1;
					$DEF['gold_gain'] += 1;
					$ATT['kills'] ++;
					$DEF['kills'] ++;
					$DEF['nombrecrea'] ++;

					// Décrémente le nombre d'unité dispo chez le defenseur
					$def_nb_uni--;

					//Debug
					//$debug .= '<strong>Defenseur[Id:'.$die['id'].']</strong> Force: '.$DEFU_Force.' / Endurance: '.$DEFU_Endur.' : Double Kill.<br />';
				
				}//On a tué l'attaquant
			}

			if(!$hasdied && $NbrSacrifice > 0)
			{
				// Tant que notre unité à de la force et qu'il reste des noob
				$FoundUnite = true;
				//$messageunite .= "<td colspan=\"2\">";

				// On va chercher une unité
				$sat = "SELECT power_1, power_2, power_4, nom, id FROM armees WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' AND sacrifice = '1' ORDER BY puissance ASC";
				$cha = sql_query($sat);
				while(($ATTU['force'] > 0) && ($ATTU['endurance'] > 0) && ($NbrSacrifice > 0) && $FoundUnite && $die=sql_array($cha))
				{
					$DEFU_Force =  $die['power_1'] + $DEF['bonus_force'];
					$DEFU_Endur =  $die['power_2'] + $die['power_4'] + $DEF['bonus_defense'];

					$ATTU['force']		-= $DEFU_Endur;
					$ATTU['endurance']	-= $DEFU_Force;
					//$messageunite .= $die['nom'].": [".$DEFU_Force."/".$DEFU_Endur."] L'unité défendeuse meurt!<br />";

					// Kill l'unité
					array_push($aDEATH, $die['id']);

					// Diminue le nombre d'unité qui ont le sacrifice
					$NbrSacrifice--;
				
					//Overall Power
					$DEF['overallpowa'] += $DEFU_Force + $DEFU_Endur;

					//Incrémente les gains/kills
					$ATT['gold_gain'] += 2;
					$ATT['kills'] ++;
					$DEF['nombrecrea'] ++;

					// Décrémente le nombre d'unité dispo chez le defenseur
					$def_nb_uni--;
					$hasdied = true;
				}

				// Regarde si elle meurt, ou si elle est épuisée
				if($ATTU['endurance'] <= 0) { // DIE!
					array_push($aDEATH, $ATTU['id']);
					//$messageunite .= "L'attaquant meurt";
				} elseif($ATTU['force'] <= 0) {
					//$messageunite .= "<br />L'attaquant est épuisé";
				}

				//$messageunite .= "</td></tr>";
			}
		}
		
		if(!$hasdied)
		{
			//Plus d'unité, on s'attaque aux bâtiments

			$ATT['total'] += $ATTU['force']; //dégâts totaux
			$ATT['degats_batiments'] += $ATTU['force'];

			//$debug .= 'Les unités attaquantes attaquent la ville ---> ';

			//Message

			if($Def_Sort_Mur > 0) {
				//$debug .= ' Mur magic';
				//$messageunite .= '<td colspan=\"2\">Sort de protection Magique</td></tr>';
				$Def_Sort_Mur -= $ATTU['force'];
			}
			elseif($Def_Bati_Barri > 0 || $Def_Bati_Murai > 0 || $Def_Bati_Grani)
			{
				//$debug .= ' plaque';
				$Var_Etat_Defense = 0; //On se prend les murailles et cie
				//$messageunite .= "&nbsp;&nbsp;&nbsp;L'unité se fait bloquer par les bâtiments defensifes.\n";
				//On touche les defense
				if($Def_Bati_Barri > 0)
				{//on s'en prend à la barricade
					$Def_Bati_Barri -= $ATTU['force'];
					//$messageunite .= "<td>Muraille Normale</td><td>(".$Def_Bati_Barri.")</td></tr>"; // ARRG: ici et en dessous tu peux afficher le nombre de plaques, sexier 
				}
				elseif($Def_Bati_Murai > 0)
				{//On s'en prend à la muraille
					$Def_Bati_Murai -= $ATTU['force'];
					//$messageunite .= "<td>Muraille Enchantée</td><td>(".$Def_Bati_Murai.")</td></tr>";
				}
				elseif($Def_Bati_Grani > 0)
				{//On s'en prend à la granit
					$Def_Bati_Grani -= $ATTU['force'];
					//$messageunite .= "<td>Muraille d'Énergie</td><td>".$Def_Bati_Grani.")</td></tr>";
				}
			}
			else
			{//On endomage au hasard un batiment
				$Var_Etat_Defense = 2; //Enregistre qu'on a attaqué les batiments
				//$debug .= ' Bâtiment de province';

				$Bln_A_Endomage = false;

				if($Def_Nb_Batiments > 0 && $Def_Nb_Batiments_Vivants > 0)
				{
					$rand = rand(1, $Def_Nb_Batiments)-1;

					if($Def_BatiLife[$rand] > 0) // ARRG: BUG ! Certains vont taper dans le vide :/
					{
						//$messageunite .= "<td>Batiment de la ville</td><td><br /></td></tr>";
						//Si on est barbare
						$ATTU['force'] *= $ATT['bonus_degats_batiments'];
						$Def_BatiLife[$rand] -= $ATTU['force'];
						$Bln_A_Endomage = true;
						// ARRG: BUG! Si un batiment tombe à 0 tu dois faire $Def_Nb_Batiments-- sinon il y aura jamais pillage

						// Si le bâtiment tombe sous 0 pv, on s'en rappel
						if($Deb_BatiLife[$rand] <= 0) 
							$Def_Nb_Batiments_Vivants;
					}
				}

				if(!$Bln_A_Endomage) {
					//$messageunite .= "<td>Pillage général</td><td><br /></td></tr>";
					$PillageGeneral++;
				} else {
					//$messageunite .= "Bâtiment de la province";
				}
			}

			//$debug .= 'L\'unité attaque des batiments de la ville<br />';

			$hasdied = true;
		}//fin pas d'unités defense

	}//END WHILE CHAQUE ATTAQUANT
	//$messageunite .= "</table>";
	$messageunite = 'En tout, l\'attaquant a perdu '.$DEF['kills'].' unités, et en a tué '.$ATT['kills'].'. Les unités de l\'attaquant ont causé pour '.$ATT['degats_batiments'].' dégâts aux bâtiments et aux murailles de la province du défenseur. De plus, '.$PillageGeneral.' des unités attaquantes ont effectué un pillage général.';


	$debug .= "Fin des distributions des attaques<br />\n";
			

	//-------------------------------------------------------------
	//--------------------CALCUL LE RESULTAT-----------------------
	//messages du rapport
	$message = "Voici le résultat de la guerre de ".$ATT['pseudo'].":".$ATT['province_name']." qui attaquait ".$DEF['pseudo'].":".$DEF['province_name'].". \n \n";
	$message .= "L'attaquant ".$ATT['pseudo']." a attaqué avec un total de ".$ATT['nombrecrea']." unités. Sa puissance totale s'est élevée à ".$ATT['overallpowa']." \n \n";
	$message .= "Le defenseur ".$DEF['pseudo']." s'est défendu avec ".$DEF['nombrecrea']." unités. Sa puissance totale s'est élevée à ".$DEF['overallpowa']." \n ";
	$message .= "Voici le rapport détailé: \n ";
	$message .= $messageunite." \n ";


	if($Var_Etat_Defense == 2)
	{//l'attaquant a pénetré la défense du défenseur
		$debug .= '<B>Le defenseur c\'est fait envahir, il perd des ressources.</B><br />';

		//calcule les pertes des ressources du defenseur (or, nourriture, bois, et pierre)
		$gain = floor($ATT['total']/2.5); //arrondi au bas
		$DEF['loss_gold']	= $DEF['gold']	- $gain;
		$DEF['loss_food']	= $DEF['food']	- $gain;
		$DEF['loss_mat']	= $DEF['mat']	- $gain;

		//gain max de l'attaquant
		$gaingold	= $gain; 
		$gainfood	= $gain; 
		$gainmat	= $gain; 

		//verifie si on passe pas en négatif
		// ARRG: utilise des min() et max()
		if($DEF['loss_gold'] < 0) { $DEF['loss_gold'] = 0; $gaingold = $DEF['gold']; }
		if($DEF['loss_food'] < 0) { $DEF['loss_food'] = 0; $gainfood = $DEF['food']; }
		if($DEF['loss_mat'] < 0) { $DEF['loss_mat'] = 0; $gainwood = $DEF['mat']; }

		//vrai gains de l'attaquant
		$ATT['win_gold'] = $ATT['gold'] + $gaingold + $PillageGeneral;
		$ATT['win_food'] = $ATT['food'] + $gainfood + $PillageGeneral;
		$ATT['win_mat'] = $ATT['mat'] + $gainmat + $PillageGeneral;

		//On calcule la perte de paysans du defenseur
		$DEF['new_pesants'] = round($DEF['pesants']/$CONF['war_paysans_kill']);

		//Bonus Barbare
		if($ATT['race'] == 2)
		{
			$DEF['new_pesants'] = round($DEF['pesants']/($CONF['war_paysans_kill']*$CONF['bonus_barbares_plus']));
		}

		//Nombre réellement perdu
		$DEF['loss_pesants'] = $DEF['pesants']-$DEF['new_pesants'];

		// Esclaves? Barbares et Démons ne peuvent pas capturer
		if($ATT['race'] != 2 && $ATT['race'] != 3)
		{
			// Update la table temp_paysans
			$up_esclaves = "UPDATE temp_paysans SET nombre = (nombre+'".$DEF['loss_pesants']."') WHERE id_province = '".$ATT['province_id']."' AND section = '0'AND esclave = 'O'";
			sql_query($up_esclaves);
		}

		// On calcul les paysans qui meurent ...
		// On prend en premier sur les esclaves
		// 
		$PaysansTBLProvince = $DEF['pesants'];
		if($DEF['esclaves'] > 0) {
			$RetirerSurPaysans = $DEF['loss_pesants'];
			$DEF_TMP_Esclaves = $DEF['esclaves']-$DEF['loss_pesants'];
			if($DEF_TMP_Esclaves >= 0) {
				// On ne s'en prend qu'aux esclaves
				sql_query("UPDATE temp_paysans SET nombre = '".$DEF_TMP_Esclaves."' WHERE section = 0 AND id_province = '".$DEF['province_id']."' AND esclave = 'O' LIMIT 1");
			} else { // Reste, prendre sur paysans
				// On ne s'en prend qu'aux esclaves
				sql_query("UPDATE temp_paysans SET nombre = '0' WHERE section = 0 AND id_province = '".$DEF['province_id']."' AND esclave = 'O' LIMIT 1");

				// Retire des paysans normaux
				sql_query("UPDATE temp_paysans SET nombre = (nombre-".$DEF_TMP_Esclaves.") WHERE section = 0 AND id_province = '".$DEF['province_id']."' AND esclave = 'N' LIMIT 1"); // ARRG: Limit

				$PaysansTBLProvince += $DEF_TMP_Esclaves;
			}
		} else { // Que sur paysans
			//On verifie la somme
			if($DEF['new_pesants'] < $CONF['paysans_min']) $DEF['new_pesants'] = $CONF['paysans_min'];
			
			sql_query("UPDATE temp_paysans SET nombre = '".$DEF['new_pesants']."' WHERE section = 0 AND id_province = '".$DEF['province_id']."' AND esclave = 'O' LIMIT 1");

			$PaysansTBLProvince = $DEF['new_pesants'];
		}
		

		//update
		$ATTUPR = "UPDATE provinces SET 
			`gold` = '".$ATT['win_gold']."',
			`food` = '".$ATT['win_food']."',
			`mat` = '".$ATT['win_mat']."',
			`satisfaction` = (satisfaction+".$CONF['war_satisfaction']."),
			`victoires` = (victoires+1)
		WHERE `id` = '".$ATT['province_id']."' LIMIT 1"; // ARRG: limit
		sql_query($ATTUPR);

		// Défenseur
		$DEFUPR = "UPDATE provinces SET 
			`gold` = '".$DEF['loss_gold']."',
			`food` = '".$DEF['loss_food']."',
			`mat` = '".$DEF['loss_mat']."',
			`pertes` = (pertes+1),
			peasant = '".$PaysansTBLProvince."'
		WHERE `id` = '".$DEF['province_id']."' LIMIT 1"; // ARRG: limit
		sql_query($DEFUPR);

		sql_query("UPDATE temp_paysans SET nombre = (nombre-".$DEF['loss_pesants'].") WHERE section = 0 AND id_province = '".$DEF['province_id']."' AND esclave = 'N' LIMIT 1"); // ARRG: Limit

		//On calcule la perte des bâtiments
		for($x = 0; $x < $Def_Nb_Batiments; $x++)
		{
			//Met a jour le bâtiments
			if($Def_BatiLife[$x] < 0)
			{
				$Def_BatiLife[$x] = 0;
			}
			$sql = "UPDATE batiments SET life = '".$Def_BatiLife[$x]."' WHERE id = '".$Def_BatiID[$x]."' AND id_province = '".$DEF['province_id']."' LIMIT 1"; // ARRG: Limit
			sql_query($sql);
		}
		
		$wining = 'ATT'; //variable qui permet de savoir qui a gagné
	
		//messages
		$message .= "La guerre c'est terminée avec la victoire de l'attaquant ".$ATT['pseudo'].". \n";
		$message .= "Il vole en tout ".($gaingold+$PillageGeneral)." d'Or, ".($gainfood+$PillageGeneral)." de Nourriture, ".($gainmat+$PillageGeneral)." matériaux. \n";
		$message .= "En plus, le defenseur perd ".$DEF['loss_pesants']." paysans. \n ";

		// Conquête?
		if(($DEF['satisfaction'] - $CONF['war_satisfaction']) >= $CONF['war_sat_min'] && ($DEF['type_province'] > 0) && ($NbProvincesAttaquant > $CONF['province_max_nb'])) {
			// L'attaquant prend possession de la province!
			$debug .= "<strong>L'attaquant prend possession de la province!";
			$message ."<strong>Conquête!</strong>L'attaquant prend possession de la province ".$DEF['province_name']."!\n";

			// ARRG: Surement que tu peux mettre un LIMIT 1 à chacun de ces requêtes, flemme de le faire xD
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
			// ARRG: c'est nul de l'annuler, faudrait juste envoyer un message comme quoi la province a changé de contrôleur et annuler que si
			// C'est la même equipe ^^
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
		// ARRG: LIMIT 1 à ajouter ici aussi	
		sql_query("UPDATE provinces SET victoires = (victoires+1), `satisfaction` = (`satisfaction`+".$CONF['war_satisfaction'].") WHERE id = '".$DEF['province_id']."'");
		sql_query("UPDATE provinces SET pertes = (pertes+1), `satisfaction` = (`satisfaction`-".$CONF['war_satisfaction'].") WHERE id = '".$ATT['province_id']."'");

		$debug .= "<strong>Le defenseur gagne.</strong><br />\n";
		
		$message .= "Les défenses du defenseur ont submergées les unités de l'attaquant. Le defenseur a donc gagné. \n ";

		$wining = 'DEF';

		//Met a les plaques
		// ARRG: ? 

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

	// Bonus alliance
	if($ATT['ally_maj'] == 5) {
		$ATT['gold_gain'] *= $CONF['bonus_rebelles_1'];
	}
	if($DEF['ally_maj'] == 5) {
		$DEF['gold_gain'] *= $CONF['bonus_rebelles_1'];
	}

	$message .= " \n De plus, l'attaquant a gagné ".$ATT['gold_gain']." Or pour les unités qu'il a tué, et le defenseur en gagne ".$DEF['gold_gain'].". \n";
	//met les messages privés
	send_message(999999992, $ATT['id'], addslashes($message), 1);
	send_message(999999992, $DEF['id'], addslashes($message), 1);

	sql_query("UPDATE provinces SET gold = (gold+".$ATT['gold_gain'].") WHERE id = '".$ATT['province_id']."' LIMIT 1");
	sql_query("UPDATE provinces SET gold = (gold+".$DEF['gold_gain']."),
			`muraille_normal` = '".$Def_Bati_Barri."',
			`muraille_enchante` = '".$Def_Bati_Murai."',
			`muraille_magie` = '".$Def_Bati_Grani."' WHERE id = '".$DEF['province_id']."' LIMIT 1"); // ARRG: LIMIT

	$debug .= 'Messages envoyés<br />';

	// Supprime les créatures mortes
	if(count($aDEATH) > 0) {
		$s = implode(', ', $aDEATH);
		$deleteunites = "DELETE FROM armees WHERE ID IN (".$s.")";
		sql_query($deleteunites);
	}

	//update les créatures en mode dispo
	$unitesd = "UPDATE armees SET dispo = '1' WHERE `dispo` = '3' AND id_province = '".$DEF['province_id']."'";
	sql_query($unitesd);
	$unitesa = "UPDATE armees SET dispo = '4', `heureretour` = '".$TempRetour."' WHERE `id_guerre` = '".$WAR['id']."' AND id_province = '".$ATT['province_id']."'";
	sql_query($unitesa);
	$debug .= 'Update des créatures...<br />';

	//update les ressources
	//$debug .= '<B>Message</B>:<br />'.$message.'<br/>';

	//supprime la guerre
	$del = "DELETE FROM guerres WHERE id_guerre = '".$WAR['id']."' LIMIT 1"; // ARRG: LIMIT
	sql_query($del);
	$debug .= 'Supression.<br /><br />';

	if($Bln_Cree_HTML)
	{
		//Créé le fichier html
		ob_start();

		echo $debug; // Ca sert à rien d'utiliser ob si c'est pour faire ca xD

		$cache=ob_get_contents();
		ob_end_clean();

		//Ici : la partie pour la cache de la page web
		$nom = 'RapportTour'.time().'.html';
		$lieu = '../protege/rapports/'.$nom;

		//On définit quelle fonctions utiliser
		$version = explode('.',phpversion()); // ARRG: O_O versioning so enterprisey :p

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
		//echo $debug;
	}

	//On recharge la page pour relancer le temps d'exécution à zéro
	if($WarStackNum > 1) {	
		echo "<meta http-equiv=refresh content=0;URL=\"WarRunnerNew.php?pyjama=dz542km\">\n";
		exit();
	}
}//end selection des guerres
echo $debug;
?>