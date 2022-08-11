<?php
// Variables du jeu dans un array
$CONF = array (
	//Game
	'game_status'		=>	3,		//Status of actual game (0=closed);(1=maintainence);(2=lancement);(3=ok)
	'game_day_seconds'	=>	3600,	//Secondes in a day (to do quick turns)
	'game_case'			=>	60, 	//Nombre of x/y cases for the map.
	'game_case_x'		=>	60,
	'game_case_y'		=>	60,
	'game_case_view'	=>  20,		//Nombre de cases par ecran

	'game_min_holiday'	=>	3,		//Minimum time of $game_turn_time Holidays 	9
	'game_max_holiday'	=>	30,		//Maximum $game_turn a player can stay on holiday 	9
	'game_name' 		=>	1, 		//N° Season of actual game 	9
	'game_date_start'	=> 'Samedi 19 Août 2006 à 20h',	//Date when version started
	'game_time_start'	=> 1156022800,	//Time of game start
	'game_echo'			=>	'Version 4.0.0 beta',		//Name of actual game
	'game_version'		=>	'4.0.0', //Version
	'game_timeformat'	=> "d-m-Y \à G:i:s",
	
	
	'game_DB_user'		=> 'root',			//Database user
	'game_DB_psw'		=> '12pm23',				//Database password
	'game_DB_name'		=> 'decemberescapecom2',		//Name of Database table
	'game_DB_server'	=> 'localhost',
	/*
	'game_DB_user'		=> 'bwadmin',					//Database user
	'game_DB_psw'		=> 'bwbanane88',				//Database password
	'game_DB_name'		=> 'decemberescapecom2',		//Name of Database table
	'game_DB_server'	=> 'mysql.decemberescape.com',
*/ 
	
	'game_mess_exp'		=> 72,	//Heures avant expiration
	'game_admin_mail'	=> 'bloodwarriors@decemberescape.com',
	'game_address'		=> 'http://bw.decemberescape.com',


	'game_race_limit'	=> 50,		//Number of maximum players by race

	'game_auto_activate' => 1,		//Les inscrits sont automatiquement activés (0:non, 1:oui)

	'default_css'		=>	8,		//Css par defaut lors de l'inscription

	
	//Forum
	'forum_topics'  	=>	10,  	//Number of topics per page
	'forum_messages' 	=>	20,		//Number of posts by page

	//GUERRES
	'relation_attack_power'	=>	50,		//% of power between two players to attack 
	'war_time'				=>	60, 	//Durée de déplacement par case (en minutes
	'war_min'				=>	60,		//Durée minimum en minutes
	'war_paysans_kill'		=>	1.15,	//% de paysans tués lors qu'une guerre
	'1_attaque_min'			=> 0.5,		//Anges: Puissance minimum ataquable	
	'2_attaque_min'			=> 0.5,		//Barbares: Puissance minimum ataquable	
	'3_attaque_min'			=> 0.3,		//Démons: Puissance minimum ataquable	
	'4_attaque_min'			=> 0.5,		//Elfes: Puissance minimum ataquable	
	'5_attaque_min'			=> 0.5,		//Rebelles: Puissance minimum ataquable	
	'6_attaque_min'			=> 0.5,		//Sorciers: Puissance minimum ataquable	
	'war_tente_capa'		=> 10,		//Tente: capacité de place par tente
	'war_tente_prix_gold'	=> 25,		//Tente: cout en or d'une tente
	'war_tente_prix_mat'	=> 50,		//Tente: cout en matériaux d'une tente
	'war_tente_nb'			=> 10,		//Tente: Nombre de tentes max par carré
	'war_satisfaction'		=> 5,		//Valeur de changement de satisfaction
	'war_sat_min'			=> 15,		//Valeur minimal de satisfaction pour conquêrire
	'war_sat_max'			=> 150,		//Satisfaction maximale
	'war_conquest_satisfaction' => 30,	//Nouvelle valeur de satisfaction lors de conquête

	'allow_give_units_self' => true,			// Si on a le droit d'envoyer des unités sur nos autres provinces
	'allow_give_units_ally' => true,			// Si on a le droit d'envoyer des unités sur les provinces de nos alliés

	//JOUEUR
	'player_peasant_max'	=>	900,  	//Nombre Maximal de paysans
	'ressources_max'  	=>	9999,  	//Sorcier  	Ressources maximals de chaque type  
	'start_paysans' 	=>	30, 	//Sorcier 	Paysans au début de partie 	
	'start_or'			=>	20, 	//Sorcier 	Or au début de partie
	'start_champs'		=>	30, 	//Sorcier 	Nourriture au début de partie 
	'start_mat'			=>  45,		// Materiaux au début
	'start_pierre' 		=>	15, 	//Sorcier 	Pierre au début de partie
	'start_bois' 		=>	15, 	//Sorcier 	Bois au début de partie
	'start_magie' 		=>	10, 	//Sorcier 	Magie au début de partie
	'start_puissance'	=>	39,		//Sorcier 	Puissance lors de l'inscription
	'paysans_min'		=>	30, 	//Sorcier 	Nombre minimum de paysans
	'start_cases_1'		=>	30,		//Cases		utilisables
	'start_cases_2'		=>	15,		//Cases		non utilisable
	'start_cases_tot'	=>	45,		//Cases		total
	'start_auth'		=> '100000000000001',
	'joueur_avatar'		=> 100,		//Joueur	Taille en x et y max d'un avatar
	'start_satisfaction'=> 80,
	'bati_repar_time'	=> 300,		//Temp de réparation en secondes
	'bati_min_life'		=> 0.2,		//% de vie minimal pour qu'un batiment fonctionne

	'img_taille'		=> 15,
	'img_width'			=> 100,
	'img_height'		=> 150,

	#Provinces
	'province_gold'		=> 100,		//Province: prix en or
	'province_food'		=> 50,
	'province_mat'		=> 60,
	'province_craft'	=> 50,
	'province_pesants'	=> 50,
	'province_max_ressources'	=> 450, //Max de ressources par type
	'province_max_nb'	=> 10,		//Nombre maximum de provinces par joueur
	'province_max_nb_creche' => 2,	//Nombre de province construite avec la crêche max

	'province_max_ressources_gold'	=> 450, //Max de ressource Gold
	'province_max_ressources_food'	=> 450, //Max de ressource Food
	'province_max_ressources_wood'	=> 450, //Max de ressource Wood
	'province_max_ressources_stone'	=> 450, //Max de ressource Stone
	'province_max_ressources_mat'	=> 450, //Max de ressource Materiaux
	'province_max_ressources_craft'	=> 600, //Max de ressource Craft

	'1_paysans_max'		=> 1.0,		//Anges:	Taux de maternité
	'2_paysans_max'		=> 1.1,		//Barbares:	Taux de maternité
	'3_paysans_max'		=> 1.0,		//Démons:	Taux de maternité
	'4_paysans_max'		=> 1.0,		//Elfes:	Taux de maternité
	'5_paysans_max'		=> 1.0,		//Rebelless:Taux de maternité
	'6_paysans_max'		=> 1.0,		//Sorciers:	Taux de maternité

	#Batiments
	'bati_capa_entrepot'	=> 200,	//Valeur de l'entrepot
	'bati_capa_cathedrale'	=> 20,	//Boost de la cathédrale
	'bati_capa_tourdemana'	=> 2000,  //Boost valeur magie tour de mana
	'bati_capa_grandentrepot'	=> 1000,	//Valeur de l'entrepot

	'bati_cout_maconnerie_or' => 140,
	'bati_cout_maconnerie_mat' => 40,
	'bati_cout_ferme_or' => 60,

	//Paramètres Murailles
	'm_norm_price_mat'	=> 3,
	'm_norm_power'		=> 1,
	'm_norm_max'		=> 100,

	'm_ench_price_mat'	=> 2,
	'm_ench_price_craft'=> 2,
	'm_ench_power'		=> 1,
	'm_ench_max'		=> 200,

	'm_magi_price_craft'=> 5,
	'm_magi_power'		=> 1,
	'm_magi_max'		=> 300,


	//ALLIANCES
	'ally_gold_cost'	=>	25,		//Alliance	Coût en or
	'ally_craft_cost'	=>	25,		//Alliance	Coût en magie

	//Sorts
	'sort_protections_max'	=>	9,	//Nombre maximal de protection


	//BONNUS JEU
	//Bonus plus:	Deux paysans aux foyers = deux nouveaus paysans à la maternelle						OK
	'bonus_anges_1'		=> 1.5,		//Temps des sors voile magique et carapace fois plus longs			OK
	'bonus_anges_2'		=> 1.0,		//Attaquable minimum												OK <- mis a 1 car +0+2+0+2

	//Bonus plus:	Tuent x plus de paysans lors des guerres et abîment x plus les bâtiments			OK
	'bonus_barbares_plus'	=> 1.5,
	'bonus_barbares_1'	=> 1.1,		//MaxPop augmentée de x%											OK
	'bonus_barbares_2'	=> 1.5,		//Capacité supplémentaire par tentes								OK

	//Bonus plus:	Des l'acquisition du fort, peut avoir 2 guerres en même temps (puis 3 avec le QS)	OK
	'bonus_demons_1'	=> 0.7,		//Peuvent attaquer les joueurs à moins de 30% de leurs puissance -	OK
	'bonus_demons_2'	=> 0.5,		// ---> +2+0+2+0

	//Bonnus plus:	Peut directement construire une deuxième province							
	'bonus_elfes_vitesse_guerre'		=> 0.75,		//Vitesse de guerre/envoit fois la valeur		OK
	'bonus_elfes_2'		=> 0.6,		//Ressources matériaux d'un bâtiment en moins en construction		OK
	'bonus_elfes_mortalite'		=> 0.9,		// Taux de mortalité chez les esclaves						OK

	//Bonnus plus:	+1+1+1+0																			OK
	'bonus_rebelles_1'	=> 2,		//Or récupéré lors de tuer une unié * variable						OK
	'bonus_rebelles_2'	=> 0, //3600		//Tour une heure plus rapide

	//Bonus plus:	Certains sorts des le départ														OK
	'bonus_sorciers_1'	=> 1.1,		//Augmentation de la magie											OK
	'bonus_sorciers_2'	=> 1.5,		//Nombre de protections en plus										OK


	//SCORES (Valeur * score_variable)
	'score_ressources'	=> 0.1,
	'score_paysans'		=> 1,
	'score_batiments'	=> 1,
	'score_unites'		=> 0.5,
	'score_victoires'	=> 25,
	'score_defaites'	=> 25,
	'score_cases'		=> 15,
	
	// Esclaves
	'esclaves_mortalite'		=> 0.8,	// Taux de mortalité chez les esclaves aux retour des travaux
	'esclaves_mortalite_plus'	=> 5, // Nombre supplémentaire de paysans qui meurtent chaque tour


	//Spécial
	'bati_tente_id'		=> 65535,
	'popup_width'		=> 350,
	'popup_height'		=>  250,


	//Attention!
	'vitesse_jeu'		=> 1,		//* de la vitesse du jeu (- = plus rapide)
										//Tour d'environ 3 minutes: 0.085

	//Administrations temps
	'time_inactif_alert'	=> 5,	//Jours avant d'avertir le joueur pour inactivité
	'time_inactif_delete'	=> 7,	//Jours avant de supprimer le joueur pour inactivité
	'time_keep_ip'		=> 8, //Jours qu'on garde les IP

	'errcol' => '#f85',

	'end_of_file'		=> 0	//Doit toujours rester à la fin
); //Fin de l'array