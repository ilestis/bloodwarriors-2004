<?php
/*----------------------[TABLEAU]---------------------
|Nom:			WarRunner.Php
+-----------------------------------------------------
|Description:	Le fichier qui calcule chaque guerre (unit� par unit�)
+-----------------------------------------------------
|Date de cr�ation:				12/02/05
|Date du premier test:			26/03/05
|Derni�re modification[Auteur]: 12/10/06[Escape]
+-----------------------------------------------------
|Mise en forme:
| - Choisi chaque guerre et lui enl�ve 1 jours.
| -- Si une guerre passe � z�ro:
| - Choisi les b�timents ennemis
| - Selectionne chaque unit�s attaquantes
| - Met � jour les stats de la cr�ature attaquantes + effet de la technique
| - Choisi une cr�ature adverse la mieux plac�e pour d�fendre
| - Calcul le r�sultat, et met en place des variable de gain/perte, �ventuellement tue l'une des 2 unit�s.
| - Calcul les gains/perte de ressources.
| - Calcul les d�gats effectu�s aux b�timents 
+---------------------------------------------------*/

//inclu les fichiers importants
/* ... */


$Bln_Cree_HTML = false;						//Variable pour la cr�ation du rapport

//Message dans le journal des admins [vir� car flood dans le journal x)
//$action = "<img src=\"images/admin/ok.png\">Guerres Runner Lanc�.";
//journal_admin('CronTab', $action);

//message du d�bug
$debug = "<br /><strong>Runner des guerres.</strong><br />\n";

//on prend les guerres qui doivent se d�rouler en ordre chronologique
$Sql_War = "SELECT * FROM guerres WHERE `time_guerre` < '".time()."'ORDER BY id_guerre ASC"; 
$Req_War = sql_query($Sql_War);
while($res = mysql_fetch_array($Req_War))
{ //Prend les donn�es

	//On supprime la guerre
	$Delete_War = "DELETE FROM guerres WHERE id_guerre = '".$res['id_guerre']."'";
	sql_query($Delete_War);
	
	//Une guerre se d�roule m�me si le mec est en vacance. On peut pas �viter une guerre engag�e.
	$WAR['id'] = $res['id_guerre'];
	
	//R�cup�re les valeurs de la table en tableau
	$ATT['province_id'] = $res['att_pro_id'];
	$ATT['id'] 			= $res['att_id'];
	$ATT['tech'] 		= $res['att_tech'];
	
	$DEF['province_id'] = $res['def_pro_id'];
	$DEF['id'] 			= $res['def_id'];
	$DEF['tech'] 		= $res['def_tech'];

	//R�cup�re les donn�es des joueurs et des provinces
//Attaquant
	$att_sql = "SELECT `pseudo`, `race` FROM joueurs WHERE `id` = '".$ATT['id']."'";
	$att_req = sql_query($att_sql);
	$att_res = mysql_fetch_array($att_req);
	$ATT['pseudo']	= $att_res['pseudo'];
	$ATT['race']	= $att_res['race'];
	
	$att_p_sql = "SELECT * FROM provinces WHERE `id` = '".$ATT['province_id']."'";
	$att_p_req = sql_query($att_p_sql);
	$att_p_res = mysql_fetch_array($att_p_req);
	$ATT['gold']			= $att_p_res['gold'];
	$ATT['food']			= $att_p_res['food'];
	$ATT['wood']			= $att_p_res['wood'];
	$ATT['stone']			= $att_p_res['stone'];
	$ATT['craft']			= $att_p_res['craft'];
	$ATT['x']				= $att_p_res['x'];
	$ATT['y']				= $att_p_res['y'];
	$ATT['province_name']	= $att_p_res['name'];
	$ATT['gold_gain']		= 0;
	
//Defenseur
	$def_sql = "SELECT `pseudo`, `race` FROM joueurs WHERE `id` = '".$DEF['id']."'";
	$def_req = sql_query($def_sql);
	$def_res = mysql_fetch_array($def_req);
	$DEF['pseudo']	= $def_res['pseudo'];
	$DEF['race']	= $def_res['race'];
	
	$def_p_sql = "SELECT * FROM provinces WHERE `id` = '".$DEF['province_id']."'";
	$def_p_req = sql_query($def_p_sql);
	$def_p_res = mysql_fetch_array($def_p_req);
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

	// ---------- Batiments du defenseur
	//On met chaque batiments dans un array
	$sql = "SELECT id, life  FROM batiments WHERE id_province = '".$DEF['province_id']."' AND codename <> 'barricade' AND codename <> 'murailles' AND codename <> 'chateaufort' AND life > 0";
	$req = sql_query($sql);
	$x = 0;
	while($res = sql_object($req))
	{
		$Def_BatiID[$x] = $res->id;
		$Def_BatiLife[$x] = $res->life;
		$x++;
	}
	$Def_Nb_Batiments = $x;
	
	$debug .= "C'est partit!<br />\n";
	$debug .= "GUERRE ".$ATT['pseudo']." VS ".$DEF['pseudo']."<br />\n";

	//Temp de guerre
	$TempRetour = calc_WarTime($ATT['province_id'], $DEF['province_id'], $CONF['war_time'], $CONF['war_min'], $CONF['vitesse_jeu']);
	
	//Initialisation des variables du runner
	$DEF['kills'] = 0; //nombre de cr�atures que le defenseur a tu�
	$ATT['kills'] = 0; //...que l'attaquant a tu�
	$DEF['batidef'] = 0; //defense bonus du defenseur avec les b�timents
	$ATT['total'] = 0; //d�g�ts totaux caus�s pas l'attaquant
	$ATT['overallpowa'] = 0; //Puissance total de lattaquant
	$DEF['overallpowa'] = 0; //Puissance total du defenseur
	$ATT['nombrecrea'] = 0; //nombre d'unit�s qui attaquent
	$DEF['nombrecrea'] = 0; //nombre d'unit�s qui ont d�fendues

	//selectionne nos bonnus guilde...
	$bonusatt = "SELECT bonus_1, bonus_2, bonus_3 FROM `info_races` WHERE `id_race` = '".$ATT['race']."'";
	$bonusatta = sql_query($bonusatt);
	$bonusattaquant = mysql_fetch_array($bonusatta);
	$ATT['bonus1'] = 0+($bonusattaquant['bonus_1']+$bonusattaquant['bonus_3']);
	$ATT['bonus2'] = 0+($bonusattaquant['bonus_2']);
	$debug .= "Bonus attaquant: ".$ATT['bonus1']."/".$ATT['bonus2']."<br />\n";
	

	$bonusdef = "SELECT bonus_1, bonus_2, bonus_4 FROM `info_races` WHERE `id_race` = '".$DEF['race']."'";
	$bonusdeff = sql_query($bonusdef);
	$bonusdefenseur = mysql_fetch_array($bonusdeff);
	$DEF['bonus1'] = $bonusdefenseur['bonus_1'];
	$DEF['bonus2'] = $bonusdefenseur['bonus_2'];
	$DEF['bonus4'] = $bonusdefenseur['bonus_4'];
	$debug .= "Bonus defenseur: ".$DEF['bonus1']."/".$DEF['bonus2']."<br />\n";

	//prend les murailles, baricades du defenseur
	$Def_Bati_Barri = 0;
	$Def_Bati_Murai = 0;
	$Def_Bati_ChateauF = 0;
	$Def_Bati_Barri_Exist = false;
	$Def_Bati_Murai_Exist = false;
	$Def_Bati_ChateauF_Exist = false;

	$req_barri = sql_query("SELECT life FROM batiments WHERE id_province = '".$DEF['province_id']."' AND codename = 'barricade' AND value = '1' AND life > '0'");
	$req_mura = sql_query("SELECT life FROM batiments WHERE id_province = '".$DEF['province_id']."' AND codename = 'murailles' AND value = '1' AND life > '0'");
	$req_chat = sql_query("SELECT life FROM batiments WHERE id_province = '".$DEF['province_id']."' AND codename = 'chateaufort' AND value = '1' AND life > '0'");

	#Barricade
	if(mysql_num_rows($req_barri) == 1)
	{//La bvarricade existe
		$barricade = mysql_fetch_array($req_barri);
		$Def_Bati_Barri = $barricade['life'];
		$Def_Bati_Barri_Exist = true;
	}

	#Muraille
	if(mysql_num_rows($req_mura) == 1)
	{//Les murailles existe
		$murailles = mysql_fetch_array($req_mura);
		$Def_Bati_Murai = $murailles['life'];
		$Def_Bati_Murai_Exist = true;
	}
	
	#Chateau Fort
	if(mysql_num_rows($req_chat) == 1)
	{//Le chateau fort existe
		$chateauf = mysql_fetch_array($req_chat);
		$Def_Bati_ChateauF = $chateauf['life'];
		$Def_Bati_ChateauF_Exist = true;
	}

	//Etat par d�faut
	$Var_Etat_Defense = 1;
	

	//$DEF['overallpowa'] += $DEF['batidef'];

	//-------------------------------------------------------------------------------------
	//-----Prend les sorts de chaque joueur---------------------------------------
	$ATT_sort = "SELECT * FROM temp_sorts WHERE id_province = '".$ATT['province_id']."'";
	$ATT_sort_req = sql_query($ATT_sort);
	while ($ATT_sort_res = mysql_fetch_array($ATT_sort_req))
	{//Prend chaque sort de l'attaquant
		if ($ATT_sort_res['boost_id'] == 1) {//Bonnus d'attaque
			$ATT['bonus1'] += $ATT_sort_res['boost_value'];
		}
		elseif ($ATT_sort_res['boost_id'] == 2) {//Bonnus de defense
			$ATT['bonnus2'] += $ATT_sort_res['boost_value'];
		}
	}

	$DEF_sort = "SELECT * FROM temp_sorts WHERE id_province = '".$DEF['province_id']."'";
	$DEF_sort_req = sql_query($DEF_sort);
	while ($DEF_sort_res = mysql_fetch_array($DEF_sort_req))
	{//Prend chaque sort du d�fenseur
		if ($DEF_sort_res['boost_id'] == 1) {//Bonnus d'attaque
			$DEF['bonus1'] += $DEF_sort_res['boost_value'];
		}
		elseif ($DEF_sort_res['boost_id'] == 2) {//Bonnus de defense
			$DEF['bonnus2'] += $DEF_sort_res['boost_value'];
		}
		elseif ($DEF_sort_res['boost_id'] == 3) {//Bonnus defense ville
			//$DEF['batidef'] += $DEF_sort_res['boost_value'];	
			
			//On ajoute la valeur a la Barricade, car on part du principe 
			$Def_Bati_Barri += $DEF_sort_res['boost_value'];
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
		//2: Pi�ges
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
	}//fin bonnus techniques
	if ($DEF['tech'] == "") $ATT['techbonus'] = 1;


	//selectionne chaque cr�ature attaquante par la plus forte // On a pas besoin de prendre l'id de la province, vu que l'id de la guerre est unique
	$creature = "SELECT * FROM `armees` WHERE `id_joueur` = '".$ATT['id']."' AND `dispo` = '2' AND id_guerre = '".$WAR['id']."' ORDER BY `power_1` DESC"; //prend la plus forte

	//Tableau pour les unit�s
	$messageunite = '<table width="100%"><tr><th>Attquante</th><th>Defenseuse</th><th>R�sultat</th></tr>';

	//$debug .= "Selection des unit�s de l'attaquant:<br />".$creature."<br />\n";
	$reply = sql_query($creature);
	while($unite = mysql_fetch_array($reply))
	{//prend chaque cr�ature ordr�e par force
		//met ses stats (avec bonus)
		$ATT['nombrecrea'] ++; //incr�mente d'un attaquant
		$ATTU['force'] = ($unite['power_1']+$ATT['bonus1']) + ($unite['power_3']) + $ATT['techbonus'];
		$ATTU['endurence'] = ($unite['power_2']+$ATT['bonus2']);
		$ATTU['id'] = $unite['id'];
		$ATT['overallpowa'] += ($ATTU['force'] + $ATTU['endurence']);
		$debug .= '<B>Attaquant[Id:'.$ATTU['id'].']</B>Force: '.$ATTU['force'].' / Endurence: '.$ATTU['endurence'].'<br />';

		//On va chercher son nom...
		//$U_NomSql = "SELECT nom FROM invocation WHERE `ID` = '".$unite['ID_creature']."'";
		//$U_NomReq = sql_query($U_NomSql);
		//$U_NomRes = mysql_fetch_array($U_NomReq);
		$messageunite .= "<tr><td>".$unite['nom']." [".$ATTU['force']."/".$ATTU['endurence']."]</td>";

		//selectionne un adversaire de taille
		//besoin
		//$minimumatt = $ATT['force'] - $DEF['bonus1'];
		//$minimumdef = $ATT['endurence'] - ($DEF['bonus3']+$DEF['bonus4']);

		$hasdied = FALSE; //la cr�ature attaquante n'a pas �t� tu�e ou utilis�e
		//la variable hasdied permet de savoir si on continue a chercher un defenseur ou pas.


		if(!$hasdied)
		{
			//On cherche une cr�ature capable de tuer l'attaquant et de r�sister
			$sat = "SELECT * FROM armees WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' AND `power_1` >= '".($ATTU['endurence']-$DEF['bonus1'])."' AND (power_2+power_4) > '".($ATTU['force']-$DEF['bonus1'])."'";
			$cha = sql_query($sat);
			while(($die = mysql_fetch_array($cha)) AND ($hasdied == FALSE))
			{//tant que la cr�ature attaquante n'est pas morte, on continue a chercher un ennemi
				//les donn�es
				$DEFU['force'] = $die['power_1']+$DEF['bonus1'];
				$DEFU['endurence'] = ($die['power_2'] + $die['power_4'])+$DEF['bonus2']+$DEF['bonus4'];
				$DEFU['id'] = $die['id'] + $DEF['techbonus'];
				//on regarde si on peut le tuer et resister
				if(($DEFU['force'] >= $ATTU['endurence']) && ($DEFU['endurence'] > $ATTU['force']))
				{//ok!
					//Calcule la puissancede la b�te
					$DEF['overallpowa'] += ($DEFU['force'] + $DEFU['endurence']);
					$debug .= '<B>Defenseur[Id:'.$DEFU['id'].']</B> Force: '.$DEFU['force'].' / Endurence: '.$DEFU['endurence'].'<br />';

					//Cherche son nom
					//$U_NomSql = "SELECT nom FROM invocation WHERE `ID` = '".$DEFU['id']."'";
					//$U_NomReq = sql_query($U_NomSql);
					//$U_NomRes = mysql_fetch_array($U_NomReq);

					//Message
					$messageunite .= "<td>".$die['nom'].": [".$DEFU['force']."/".$DEFU['endurence']."]</td>";


					$messageunite .= "<td>Attaquant tu�!</td></tr>";

					//on tue la cr�atures attaquante
					$kill = "DELETE FROM armees WHERE `id` = '".$ATTU['id']."'";
					sql_query($kill);
					//update le status de l'unit�
					$disponible = "UPDATE armees SET dispo = '3' WHERE `id` = '".$die['id']."'";
					sql_query($disponible);

					//Gain de guerre pour le defenseur:
					$DEF['gold_gain']		+= 2;

					$debug .= 'Cr�ature attaquante tu�e!<br />';
					$DEF['kills'] ++;	//incr�mente un kill
					$DEF['nombrecrea'] ++; //incr�mente le nombre de d�fenseur

					//si on a r�ussit a tuer, en prend en charge la prochaine unit�s de l'attaquant
					$hasdied = TRUE;
				}
			}//SI ON GAGNE
		}


		if(!$hasdied)
		{
			//On cherche une unit�s capable de r�sister
			$sat = "SELECT * FROM armees WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' AND (power_2+power_4) > '".($ATTU['force']-$DEF['bonus1'])."'";
			$cha = sql_query($sat);
			while(($die = mysql_fetch_array($cha)) AND ($hasdied == FALSE))
			{//tant que la cr�ature attaquante n'est pas morte, on continue a chercher un ennemi
				//les donn�es
				$DEFU['force'] = $die['power_1']+$DEF['bonus1'];
				$DEFU['endurence'] = ($die['power_2'] + $die['power_4'])+$DEF['bonus2']+$DEF['techbonus'];
				$DEFU['id'] = $die['id'];
				if(($DEFU['endurence'] > $ATTU['force']) && ($DEFU['force'] < $ATTU['endurence']))
				{//personne ne meurt
					$debug .= 'Aucun mort<br />';

					//update le status des unit�s
					$DEF['overallpowa'] += ($DEFU['force'] + $DEFU['endurence']);
					$debug .= '<B>Defenseur[Id:'.$DEFU['id'].']</B> Force: '.$DEFU['force'].' / Endurence: '.$DEFU['endurence'].'<br />';

					//Message
					$messageunite .= "<td>".$die['nom'].": [".$DEFU['force']."/".$DEFU['endurence']."]</td>";


					$messageunite .= "<td>Aucun mort!</td></tr>";

					//Update unit�s 
					$disponible1 = "UPDATE armees SET dispo = '3' WHERE `id` = '".$DEFU['id']."'";
					sql_query($disponible1);

					$disponible2 = "UPDATE armees SET dispo = '4', `heureretour` = '".$TempRetour."' WHERE `id` = '".$ATTU['id']."'";
					sql_query($disponible2);

					$DEF['nombrecrea'] ++; //incr�mente le nombre de d�fenseur
					$hasdied = TRUE;
				}
			}//SI ON RESISTE
		}

		if(!$hasdied)
		{
			//On cherche une unit� capable de tuer m�me si elle cr�ve
			$sat = "SELECT * FROM armees WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' AND `power_1` >= '".($ATTU['endurence']-$DEF['bonus1'])."'";
			$cha = sql_query($sat);
			while(($die = mysql_fetch_array($cha)) AND ($hasdied == FALSE))
			{//tant que la cr�ature attaquante n'est pas morte, on continue a chercher un ennemi
				//les donn�es
				$DEFU['force'] = $die['power_1']+$DEF['bonus1'];
				$DEFU['endurence'] = ($die['power_2'] + $die['power_4'])+$DEF['bonus2']+$DEF['techbonus'];
				$DEFU['id'] = $die['id'];
			
				//si pas, si on peu au moins tuer
				if(($DEFU['force'] >= $ATTU['endurence']) && ($DEFU['endurence'] <= $ATTU['force']))
				{//on tue l'ennemi mais on cr�ve
					$DEF['overallpowa'] += ($DEFU['force'] + $DEFU['endurence']);
					$debug .= '<B>Defenseur[Id:'.$DEFU['id'].']</B> Force: '.$DEFU['force'].' / Endurence: '.$DEFU['endurence'].'<br />';

					//Message
					$messageunite .= "<td>".$die['nom'].": [".$DEFU['force']."/".$DEFU['endurence']."]</td>";


					$messageunite .= "<td>Les deux unit�s meurent!</td></tr>";

					//Compteur de kills
					$ATT['kills'] ++;
					$DEF['kills'] ++;

					//Gain en or
					$DEF['gold_gain'] += 1;
					$ATT['gold_gain'] += 1;

					$defdie = "DELETE FROM armees WHERE `id` = '".$DEFU['id']."'";
					sql_query($defdie);
					$defatt = "DELETE FROM armees WHERE `id` = '".$ATTU['id']."'";
					sql_query($defatt);
					$DEF['nombrecrea'] ++; //incr�mente le nombre de d�fenseur
					$hasdied = TRUE;
					$debug .= 'Les 2 unit�s sont mortes.<br />';
				}
			}//SI ON PEUT LA TUER
		}

		if(!$hasdied)
		{
			//sinon on prend la premi�re cr�ature la plus faible
			$sat = "SELECT * FROM armees WHERE `id_province` = '".$DEF['province_id']."' AND dispo = '1' AND sacrifice = '1' ORDER BY puissance ASC";
			$cha = sql_query($sat);
			while(($die = mysql_fetch_array($cha)) AND ($hasdied == FALSE))
			{//tant que la cr�ature attaquante n'est pas morte, on continue a chercher un ennemi
				//les donn�es
				$DEFU['force'] = $die['power_1']+$DEF['bonus1'];
				$DEFU['endurence'] = ($die['power_2'] + $die['power_4'])+$DEF['bonus2']+$DEF['techbonus'];
				$DEFU['id'] = $die['id'];
				$DEF['overallpowa'] += ($DEFU['force'] + $DEFU['endurence']);
				$debug .= '<B>Defenseur[Id:'.$DEFU['id'].']</B> Force: '.$DEFU['force'].' / Endurence: '.$DEFU['endurence'].'<br />';

				//Message
				$messageunite .= "<td>".$die['nom'].": [".$DEFU['force']."/".$DEFU['endurence']."]</td>";


				$messageunite .= "<td>Le defenseur meurt!</td></tr>";

				$ATT['kills'] ++;
				$defdie = "DELETE FROM armees WHERE `id` = '".$DEFU['id']."'";
				sql_query($defdie);
				$debug .= 'Le defenseur est mort.<br />';
				$DEF['nombrecrea'] ++; //incr�mente le nombre de d�fenseur

				//Gain de guerre pour le attaquant:
				$ATT['gold_gain']		+= 2;

				$disponible2 = "UPDATE armees SET dispo = '4', `heureretour` = '".$TempRetour."' WHERE `id` = '".$ATTU['id']."'";
				sql_query($disponible2);

				$hasdied = TRUE;
			}//END WHILE CREATURE
		}

		//maintenant, si y'a pas de defenseur on retire depuis la defense
		if(!$hasdied)
		{//on a pas tu� l'ennemi
			$DEF['batidef'] -= $ATTU['force'];// on diminue la force des d�fenses (a n�gatife, les attaquants passent dans la ville).
			$ATT['total'] += $ATTU['force']; //d�g�ts totaux

			$debug .= 'Les unit�s attaquantes attaquent la ville ---> ';

			$disponible2 = "UPDATE armees SET dispo = '4', `heureretour` = '".$TempRetour."' WHERE `id` = '".$ATTU['id']."'";
			//$debug .= '<br />'.$disponible2.'<br />';
			sql_query($disponible2);

			//Message
			$messageunite .= "<td colspan=\"2\">";

			if($Def_Bati_Barri > 0 || $Def_Bati_Murai > 0 || $Def_Bati_ChateauF > 0)
			{
				$Var_Etat_Defense = 0; //On se prend les murailles et cie
				//$messageunite .= "&nbsp;&nbsp;&nbsp;L'unit� se fait bloquer par les b�timents defensifes.\n";
				//On touche les defense
				if($Def_Bati_Barri > 0)
				{//on s'en prend � la barricade
					$Def_Bati_Barri -= $ATTU['force'];
					$messageunite .= "Barricade";
				}
				elseif($Def_Bati_Murai > 0)
				{//On s'en prend � la muraille
					$Def_Bati_Murai -= $ATTU['force'];
					$messageunite .= "Murailles";
				}
				elseif($Def_Bati_ChateauF > 0)
				{//Chateau Fort
					$Def_Bati_ChateauF -= $ATTU['force'];
					$messageunite .= "Chateau Fort";
				}
			}
			else
			{//On endomage au hasard un batiment
				$Var_Etat_Defense = 2; //Enregistre qu'on a attaqu� les batiments

				$messageunite .= "B�timent de la province";

				$Bln_A_Endomage = false;

				if($Def_Nb_Batiments > 0)
				{
					while(!$Bln_A_Endomage)
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
				}
			}
			$messageunite .= "</td></tr>";

			$debug .= 'L\'unit� attaque des batiments de la ville<br />';

			$hasdied = TRUE;
		}//fin pas d'unit�s defense

	}//END WHILE CHAQUE ATTAQUANT
	$messageunite .= "</table>";

	$debug .= "Fin des distributions des attaques<br />\n";
			

	//-------------------------------------------------------------
	//--------------------CALCUL LE RESULTAT-----------------------
	//messages du rapport
	$message = "Voici le r�sultat de la guerre de ".$ATT['pseudo'].":".$ATT['province_name']." qui attaquait ".$DEF['pseudo'].":".$DEF['province_name'].". \n \n";
	$message .= "L'attaquant ".$ATT['pseudo']." a attaqu� avec un total de ".$ATT['nombrecrea']." unit�s. Sa puissance totale c'est �lev�e � ".$ATT['overallpowa']." \n \n";
	$message .= "Le defenseur ".$DEF['pseudo']." c'est defendu avec ".$DEF['nombrecrea']." unit�s. Sa puissance totale c'est �lev�e � ".$DEF['overallpowa']." \n ";
	$message .= "Voici le rapport d�tail�: \n ";
	$message .= $messageunite." \n ";
	//$message .= 'L\'attaquant a attaqu� avec une puissance totale de '.$ATT['overallpowa'].', alors que le defenseur c\'est d�fendu avec une puissance total de '.$DEF['overallpowa'].'<br />';
	$message .= "L'attaquant a tu� au total ".$ATT['kills']." unit�s, et le d�fenseur en a tu� ".$DEF['kills']." \n "; 

	if($Var_Etat_Defense == 2)
	{//l'attaquant a p�netr� la d�fense du d�fenseur
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

		//verifie si on passe pas en n�gatif
		if($DEF['loss_gold'] < 0) { $DEF['loss_gold'] = 0; $gaingold = $DEF['gold']; }
		if($DEF['loss_food'] < 0) { $DEF['loss_food'] = 0; $gainfood = $DEF['food']; }
		if($DEF['loss_wood'] < 0) { $DEF['loss_wood'] = 0; $gainwood = $DEF['wood']; }
		if($DEF['loss_stone'] < 0) { $DEF['loss_stone'] = 0; $gainstone = $DEF['stone']; }

		//vrai gains de l'attaquant
		$ATT['win_gold'] = $ATT['gold'] + $gaingold;
		$ATT['win_food'] = $ATT['food'] + $gainfood;
		$ATT['win_wood'] = $ATT['wood'] + $gainwood;
		$ATT['win_stone'] = $ATT['stone'] + $gainstone;

		//update
		$ATTUPR = "UPDATE provinces SET 
			`gold` = '".$ATT['win_gold']."',
			`food` = '".$ATT['win_food']."',
			`wood` = '".$ATT['win_wood']."',
			`stone` = '".$ATT['win_stone']."'
		WHERE `id` = '".$ATT['province_id']."'";
		sql_query($ATTUPR);

		$DEFUPR = "UPDATE provinces SET 
			`gold` = '".$DEF['loss_gold']."',
			`food` = '".$DEF['loss_food']."',
			`wood` = '".$DEF['loss_wood']."',
			`stone` = '".$DEF['loss_stone']."'
		WHERE `id` = '".$DEF['province_id']."'";
		sql_query($DEFUPR);


		//On calcule la perte de paysans du defenseur
		$DEF['new_pesants'] = round($DEF['pesants']/$CONF['war_paysans_kill']);

		//Bonnus D�mons
		if($ATT['race'] == 2)
		{
			$DEF['new_pesants'] = round($DEF['pesants']/($CONF['war_paysans_kill']*$CONF['bonus_barbares_plus']));
		}

		//Nombre r�ellement perdu
		$DEF['loss_pesants'] = $DEF['pesants']-$DEF['new_pesants'];
		
		//On verifie la somme
		if($DEF['new_pesants'] < $CONF['paysans_min']) $DEF['new_pesants'] = $CONF['paysans_min'];

		//Update la province et le temp_paysans
		sql_query("UPDATE provinces SET peasant = '".$DEF['new_pesants']."' WHERE id = '".$DEF['province_id']."'");

		sql_query("UPDATE temp_paysans SET nombre = (nombre-".$DEF['loss_pesants'].") WHERE section = 0 AND id_province = '".$DEF['province_id']."'");


		//mise � jour des level (scores)
		sql_query("UPDATE provinces SET victoires = (victoires+1) WHERE id = '".$ATT['province_id']."'");
		sql_query("UPDATE provinces SET pertes = (pertes+1) WHERE id = '".$DEF['province_id']."'");

		//On calcule la perte des b�timents
		for($x = 0; $x < $Def_Nb_Batiments; $x++)
		{
			//Met a jour le b�timents
			if($Def_BatiLife[$x] < 0)
			{
				$Def_BatiLife[$x] = 0;
			}
			$sql = "UPDATE batiments SET life = '".$Def_BatiLife[$x]."' WHERE id = '".$Def_BatiID[$x]."' AND id_province = '".$DEF['province_id']."'";
			sql_query($sql);
		}

			//Met a jour la muraille et barricade
		if($Def_Bati_Barri_Exist)
		{//Met a jour barricade
			if($Def_Bati_Barri < 0)
			{
				$Def_Bati_Barri = 0;
			}
			sql_query("UPDATE batiments SET life = '".$Def_Bati_Barri."' WHERE id_province = '".$DEF['province_id']."' AND codename = 'barricade'");
		}
		if($Def_Bati_Murai_Exist)
		{//Met a jour barricade
			if($Def_Bati_Murai < 0)
			{
				$Def_Bati_Murai = 0;
			}
			sql_query("UPDATE batiments SET life = '".$Def_Bati_Murai."' WHERE id_province = '".$DEF['province_id']."' AND codename = 'murailles'");
		}

		if($Def_Bati_ChateauF_Exist)
		{//Met a jour barricade
			if($Def_Bati_ChateauF < 0)
			{
				$Def_Bati_ChateauF = 0;
			}
			sql_query("UPDATE batiments SET life = '".$Def_Bati_ChateauF."' WHERE id_province = '".$DEF['province_id']."' AND codename = 'chateaufort'");
		}
		#Def_Bati_Barri_Exist
		#$Def_BatiID[$x] = $res->id;
		#$Def_BatiLife[$x] = $res->life;

		
		$wining = 'ATT'; //variable qui permet de savoir qui a gagn�
	
		//messages
		$message .= "La guerre c'est termin�e avec la victoire de l'attaquant ".$ATT['pseudo'].". \n";
		$message .= "Il vole en tout ".$gaingold." d'Or, ".$gainfood." de Nourriture, ".$gainwood." de Bois et ".$gainstone." de Pierre. \n";
		$message .= "En plus, le defenseur perd ".$DEF['loss_pesants']." paysans. \n ";


	}//attaquant gagne end
	elseif($Var_Etat_Defense == 1)
	{//d�fenseur gagne
		//mise � jour des level (scores)		
		sql_query("UPDATE provinces SET victoires = (victoires+1) WHERE id = '".$DEF['province_id']."'");
		sql_query("UPDATE provinces SET pertes = (pertes+1) WHERE id = '".$ATT['province_id']."'");

		$debug .= "<strong>Le defenseur gagne.</strong><br />\n";
		
		$message .= "Les d�fenses du defenseur ont submerg�es les unit�s de l'attaquant. Le defenseur a donc gagn�. \n ";

		$wining = 'DEF';

		//Met a jour la muraille et barricade
		if($Def_Bati_Barri_Exist)
		{//Met a jour barricade
			if($Def_Bati_Barri < 0)
			{
				$Def_Bati_Barri = 0;
			}
			$sql = "UPDATE batiments SET life = '".$Def_Bati_Barri."' WHERE id_province = '".$DEF['province_id']."' AND codename = 'barricade'";
			sql_query($sql);
		}
		if($Def_Bati_Murai_Exist)
		{//Met a jour barricade
			if($Def_Bati_Murai < 0)
			{
				$Def_Bati_Murai = 0;
			}
			$sql = "UPDATE batiments SET life = '".$Def_Bati_Murai."' WHERE id_province = '".$DEF['province_id']."' AND codename = 'murailles'";
			sql_query($sql);
		}
		if($Def_Bati_ChateauF_Exist)
		{//Met a jour barricade
			if($Def_Bati_ChateauF < 0)
			{
				$Def_Bati_ChateauF = 0;
			}
			sql_query("UPDATE batiments SET life = '".$Def_Bati_ChateauF."' WHERE id_province = '".$DEF['province_id']."' AND codename = 'chateaufort'");
		}
	}
	elseif($Var_Etat_Defense == 0)
	{//�galit�
		$debug .= '<strong>�galit�.</strong><br />';

		$wining = 'NONE';

		$message .= "La guerre s'est termin�e par un matche nul. \n Les d�fenses du d�fenseurs ont encaiss�es le restant des d�g�ts caus�s par l'attaquant. \n ";
	}//end verification
	
	//Gain d'or
	$debug .= "Gain d'or<br />\n";
	
	if($ATT['race'] == 5)
		$ATT['gold_gain'] *= $CONF['bonus_rebelles_1'];
	if($DEF['race'] == 5)
		$DEF['gold_gain'] *= $CONF['bonus_rebelles_1'];

	$message .= " \n De plus, l'attaquant a gagn� ".$ATT['gold_gain']." Or pour les unit�s qu'il a tu�, et le defenseur en gagne ".$DEF['gold_gain'].". \n";
	//met les messages priv�s
	send_message(999999992, $ATT['id'], addslashes($message), 1);
	send_message(999999992, $DEF['id'], addslashes($message), 1);

	sql_query("UPDATE provinces SET gold = (gold+".$ATT['gold_gain'].") WHERE id = '".$ATT['province_id']."'");
	sql_query("UPDATE provinces SET gold = (gold+".$DEF['gold_gain'].") WHERE id = '".$DEF['province_id']."'");

	$debug .= 'Messages envoy�s<br />';

	//update les cr�atures en mode dispo
	$unitesd = "UPDATE armees SET dispo = '1' WHERE `dispo` = '3' AND id_province = '".$DEF['province_id']."'";
	sql_query($unitesd);
	$unitesa = "UPDATE armees SET dispo = '4' WHERE `dispo` = '3' AND id_province = '".$ATT['province_id']."'";
	sql_query($unitesa);
	$debug .= 'Update des cr�atures...<br />';

	//update les ressources
	//$debug .= '<B>Message</B>:<br />'.$message.'<br/>';

	//supprime la guerre
	//$del = "DELETE FROM guerres WHERE id_guerre = '".$WAR['id']."'";
	//sql_query($del);
	$debug .= 'Supression<br /><br />';
}//end selection des guerres





//------- UNIT� DE RETOUR
$debug .= "<H3>Retour des unit�s</H3><br />\n";
/*$sql = "SELECT `id` FROM armees WHERE dispo = '4'";
$req = sql_query($sql);
while ($res = mysql_fetch_array($req))
{
	if(time() >= $res['heureretour'])
	{//revient
		$up = "UPDATE armees SET heureretour = '0', dispo = '1' WHERE `id` = '".$res['id']."'";
		sql_query($up);
	}
}*/
sql_query("UPDATE armees SET heureretour = '0', dispo = '1' WHERE dispo = '4' AND heureretour <= '".time()."'");

//S'occupe des sorts en BDD qui sont finis--------------
$Del_Sorts = "DELETE FROM temp_sorts WHERE `time` < '".time()."'";
sql_query($Del_Sorts);
//-----------------------------------------------------



if($Bln_Cree_HTML)
{
	//Cr�� le fichier html
	ob_start();

	echo $debug;

	$cache=ob_get_contents();
	ob_end_clean();

	//Ici : la partie pour la cache de la page web
	$nom = 'RapportTour'.time().'.html';
	$lieu = '../protege/rapports/'.$nom;

	//On d�finit quelle fonctions utiliser
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

?>