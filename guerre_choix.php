<?php
/*
+---------------------
|Nom: Modification de guerre
+---------------------
|Description: Modifie différente valeur des guerres (tech, envoy)
+---------------------
|Date de création: Aout 04
|Date du premier test: Aout 04
|Dernière modification: 13 Aout 05
|						09 Février 06 > V.3.0
+-------------------*/

//the profil
include ('./include/session_verif.php');
$Min_Puissance = $Min_Puissance = min_attack_power($Joueur->puissance, $CONF[$Joueur->race.'_attaque_min'], $Joueur->race, $CONF['bonus_anges_2']);

//Valeurs
$id = clean($_GET['id']);
if(!isset($_POST['radiobutton'])) $tech_def = 'NULL';


//Gère ce qu'on fait
switch($id)
{
	case 'updef':
		//on modifie notre technique de defense

		//cherche notre guerre et regarde si on a pas déjà choisi la technique
		$sql = "SELECT * FROM `guerres` WHERE `id_guerre` = '".clean($_GET['id2'])."' " ;
		$req = sql_query($sql);
		$tuple = mysql_fetch_array($req);
		$defenceur = $tuple['def_pro_id'];
		$techniqued = $tuple['def_tech'];
		if ($defenceur = $_SESSION['id_province'])
		{
			if($techniqued == 0)
			{//on a pas encore choisi sa technique
				$techd = clean($_POST['radiobutton']);
				$sql = "UPDATE `guerres` SET def_tech = ".$techd." WHERE `id_guerre` = '".clean($_GET['id2'])."'";    
				sql_query($sql) ;
				$Message = bw_info("Vos ordres ont été donnés à vos unités!");
			}
			else
			{
				$Message = bw_error("Vous avez déjà choisi votre technique!<br />");
			}
		}
		break;

	case 2:
		//Tableau

		//Verifie si on a pas deja notre nombre max de guerre.
		$Sql_Guerres = "SELECT * FROM guerres WHERE att_id = '".$_SESSION['id_joueur']."' AND att_pro_id = '".$_SESSION['id_province']."'";
		$Req_Guerres = sql_query($Sql_Guerres);
		$Nb_Guerres_EnCours = mysql_num_rows($Req_Guerres) ;   //nombre de guerrre
	
		//Nombre de guerres
		$Nb_Guerres_Max = 1;

		if(bw_batiavailable('quartierstrategique', false)) $Nb_Guerres_Max += 1;
		if($Joueur->race == 3) $Nb_Guerres_Max += 1;

		if ($Nb_Guerres_EnCours >= $Nb_Guerres_Max)
		{//aucune guerre en cour -> c'est ok
			$Message = bw_error("Vous avez déjà le nombre maximal de guerre autorisé!<br />");
			$err = 1;
			break;
		}
		//si on attaque personne, basta
		if($_POST['province'] == '') { 
			$Message = bw_error("Il faut choisir une province valide à attaquer!<br />");
			$err = 1;
			break;
		}

		// Paralysie
		if(check_spell($_SESSION['id_province'], '42')) {
			$Message = bw_error("Vous êtes sous l'emprise d'une " . bw_popup('Paralysie', 'sort', '42')."! Vous ne pouvez pas envoyer d'unités en ce moment.");
			$err = 1;
			break;
		}
		$Province = clean($_POST['province']);

		//Verification niveau 1
		$Ok = 0;

		//On verifie si on ne s'attaque pas et que la province existe...
		$sql = "SELECT id_joueur, `id`, `name` FROM provinces WHERE id = '".$Province."'";
		$req = sql_query($sql);
		if(mysql_num_rows($req) == 0) {
			$Message = bw_error("Province non trouvée!<br />");
			$err = 1;
			break;
		}
		//Réaliser la requête
		$res = mysql_fetch_array($req);
		$Def_Pro_Id = $res['id'];
		$Id_Ennemi = $res['id_joueur'];
		$Def_Pro_Name = $res['name'];

		// Définit si c'est un envoit ou non.
		$TypeAction = 0;
		if($_POST['type_action'] == 1) {
			$TypeAction = 1;
		}
	
		//verifie la technique technique
		switch(clean($_POST['radiobutton']))
		{
			case 1:
				$P['tech'] = 1;
				break;
			case 2:
				$P['tech'] = 2;
				break;
			case 3:
				$P['tech'] = 3;
				break;
			case 4:
				$P['tech'] = 4;
				break;
			default:
				$P['tech'] = 1;
		}

		$sql = "SELECT ally_id, aut, pseudo, puissance, vacances, race FROM joueurs WHERE id = '".$Id_Ennemi."'";
		$req = sql_query($sql);
		$res = mysql_fetch_array($req);
		$E_Ally_Id		= $res['ally_id'];
		$E_Pseudo		= $res['pseudo'];
		$E_Puissance	= $res['puissance'];
		$E_Race			= $res['race'];

		//Bonus Anges
		if($E_Race == 1)
			$Min_Puissance *= $CONF['bonus_anges_2'];

		//verifie qu'on s'attaque pas ou un mec de notre alliance
		if($Joueur->ally_id != 0) {//On a une alliance
			$Def_ally_id = $E_Ally_Id;
		}
		else {//Sinon on le met comme alliance -1 (la notre étant 0)
			$Def_ally_id = -1;
		}

		if($Joueur->ally_id == $Def_ally_id && $TypeAction == 0) {// pas ok
			$Message = bw_error("Vous ne pouvez pas attaquer quelqun de votre alliance!<br />");
			$err = 1;
			break;
		}
		elseif($res['vacances'] > 0) {//Joueur en vacances
			$Message = bw_error("Le Héros de cette province est en vacances!<br />");
			$err = 1;
			break;
		}
		elseif($E_Puissance < $Min_Puissance && $TypeAction == 0) {//Il est trop faible
			//echo "Debug spécial Gromph: Votre puissance: ".$Joueur->Puissance.", vous pouvez donc attaquer les provinces à plus de ".$Min_Puissance.". Or, la province ciblée à une puissance de ".$E_Puissance.". Merci.<br />\n";
			$Message = bw_error("Cette province est trop faible!<br />");
			$err = 1;
			break;
		}
		elseif ($res['aut'][0] == 0) {//Joueur pas activé
			$Message = bw_error("Cette province n'est pas attaquable!<br />");
			$err = 1;
			break;
		}

		//Unités attaquantes
		$NbCrea = 0;


		foreach($_POST as $key => $value)
		{
			if(substr($key, 0, 4) == 'lst_')
			{
				if($value != 0)
				{
					//Do we have this number?
					$exp = explode("_", $key);
					$csql = "SELECT id FROM armees WHERE id_province = '".$_SESSION['id_province']."' AND ID_creature = '".clean($exp[1])."' AND dispo = '1';";
					//echo $csql;
					$creq = sql_query($csql);
					if(sql_rows($creq) >= $value)
					{
						$cup = "UPDATE armees SET dispo = '2', id_guerre = '0' WHERE id_province = '".$_SESSION['id_province']."' AND dispo = '1' AND ID_creature = '".$exp[1]."' LIMIT ".$value.";";
						sql_query($cup);
						$NbCrea += $value;
					} else {
						$Message .= bw_error("Hacking error<br />");
					}
					//echo "Oui! ".$key." - ".$value."<br />\n";
				}
			}
		}

		if($NbCrea > 0) 
		{//On attaque avec au moins une créature...
			//Initialisation
			$time = time();

			//Temp de guerre
			$ResultatTemp = calc_WarTime($_SESSION['id_province'], $Province, $CONF['war_time'], $CONF['war_min'], $CONF['vitesse_jeu'], $Joueur->ally_id);


			//Ensuite, on ajouer à la BDD le tout
			$war = "INSERT INTO `guerres` VALUES('','".$_SESSION['id_joueur']."','".$_SESSION['id_province']."', '".$P['tech']."', '".$Id_Ennemi."', '".$Def_Pro_Id."', '0', '".$ResultatTemp."', '".time()."', '1', '".$TypeAction."')";
			sql_query($war);
			$debug = "Ajout table guerre<br />\n";

			//Prend l'id de la guerre
			$guerreid = mysql_insert_id();

			//update nos créature
			$upcr = "UPDATE armees SET id_guerre = '".$guerreid."', `heureretour` = '".$ResultatTemp."' WHERE id_joueur = '".$_SESSION['id_joueur']."' AND dispo = '2' AND id_province = '".$_SESSION['id_province']."' AND `id_guerre` = '0'";
			sql_query($upcr);

			if($TypeAction == 0) { // Guerre
				//Envoit des messages
				$SendMess = "Monseigneur, nous avons reçu les ordres!<br /><br /> Nous préparons nos rations et notre équipement, et partons tout de suite!<br /><br />Nous avons confiance en notre coisade contre la province <strong>".$Def_Pro_Name."</strong> du Héro ".$E_Pseudo."! Ils n'en verront que du feu, car nos ".$NbCrea." unités se battent pour une raison qu'elles considèrent juste: La votre!<br /><br />Nous arriverons à destination le ".date($CONF['game_timeformat'], $ResultatTemp)."!";
				send_message('999999991', $_SESSION['id_joueur'], addslashes($SendMess), 0, 'mes');

				$SendMess = "Monseigneur,<br /><br />Nous avons reçu de source sûre une information comme quoi l'armée de la province <strong>".province_name($_SESSION['id_province'])."</strong> du Héros ".$Joueur->pseudo." attaquent notre province ".$Def_Pro_Name."!<br /><br />D'après nos prévisions, elles arriveront au seuil de notre province le ".date($CONF['game_timeformat'], $ResultatTemp)."!<br /><br />Afin de mieux les combattres, nous avons besoin de vos instructions! Donnez-nous les ordres de défense sous la gestion des guerres, et nous protégerons votre province avec tous nos moyens possibles!";
				send_message('999999991', $Id_Ennemi, addslashes($SendMess), 0, 'mes');

				//fin
				$Message = bw_info("Votre guerre contre la province <strong>".$Def_Pro_Name."</strong> a bien été déclarée! Vos ".$NbCrea." unités sont en route et arriverons au seuil de la province ennemie le ".date($CONF['game_timeformat'], $ResultatTemp).".<br />");
			
			} else { // Envoit
				$SendMess = "Monseigneur, vos ".$NbCrea." unités ont été envoyée à la province <strong>".$Def_Pro_Name."</strong> du Héro ".$E_Pseudo.". Une fois vos unités arrivées à destination, c'est à dire le ".date($CONF['game_timeformat'], $ResultatTemp).", vos unités seront liée au Héro de la province.";
				send_message('999999991', $_SESSION['id_joueur'], addslashes($SendMess), 0, 'mes');

				$SendMess = "Monseigneur, des unités de la province <strong>".province_name($_SESSION['id_province'])."</strong> du Héros ".$Joueur->pseudo." ont été envoyée sur notre province ".$Def_Pro_Name.". Ces unités nous appartiendront à partir du ".date($CONF['game_timeformat'], $ResultatTemp).".";
				send_message('999999991', $Id_Ennemi, addslashes($SendMess), 0, 'mes');

				$Message = bw_info("Vos ".$NbCrea." unités ont été envoyées à la province <strong>".$Def_Pro_Name."</strong>, et arriveront le ".date($CONF['game_timeformat'], $ResultatTemp).".<br />");
			}
		} else {
			$Message = bw_error("Il faut sélectionner des unités à attaquer!");
		}
		bw_tableau_end();
		break;

	case 3: //Retire notre guerre		
		//Gère les valeurs
		$Id_Guerre = clean($_POST['id_guerre']);

		//Verifie si on est l'attaquant
		$Sql = "SELECT * FROM guerres WHERE id_guerre = '".$Id_Guerre."' AND att_pro_id = '".$_SESSION['id_province']."'";
		$Req = sql_query($Sql);
		$Nb	 = mysql_num_rows($Req);

		if($Nb == 0)
		{
			$Message = bw_error("Vous n'être pas impliqué dans cette guerre!");
		}
		else
		{//Ok
			$Res = mysql_fetch_array($Req);

			//Temp de début
			$TempDebut = $Res['time_debut_guerre'];

			//Temp passé, donc temps restant avant retour
			$TempPasse = time() + (time() - $TempDebut);

			//Met à jour les créatures
			$Requete = "UPDATE armees SET heureretour = '".$TempPasse."', dispo = '4', id_guerre = '0' WHERE id_guerre = '".$Res['id_guerre']."'";
			sql_query($Requete);

			//Supprime la guerre
			$Del = "DELETE FROM guerres WHERE id_guerre = '".$Id_Guerre."'";
			sql_query($Del);

			//Nom de la province et du joueur
			$sql = "SELECT name FROM provinces WHERE id = '".$Res['def_pro_id']."'";
			$req = sql_query($sql);
			$resP = mysql_fetch_array($req);
			$Nom = $resP['name'];

			$sql = "SELECT pseudo FROM joueurs WHERE id = '".$Res['def_id']."'";
			$req = sql_query($sql);
			$resJ = mysql_fetch_array($req);
			$Pseudo = $resJ['pseudo'];


			//Envois un message au joueur concerné
			$SendMess = "Le Héros ".$Joueur->pseudo." à rappelé ses troupes de son attaque sur votre province ".$Nom."!";
			send_message(999999991, $Res['def_id'], $SendMess, 1);


			//Affiche à l'écran
			$Message = bw_info("Votre guerre contre la province ".$Nom." du Héros ".$Pseudo." à été annulée. Vos unités rentreront le ".date($CONF['game_timeformat'], $TempPasse).".");
		}

		break;

	case 4: # Envoyer des unités
		/* Permet d'envoyer des unités à une de nos provinces ou un allié.
		 * Verifie si la province est valide (à nous ou a un allié).
		 * Verifie les unités.
		 * Met les unités en valeur 9, avec le temps calculé d'arrivée, et change le propriétaire.
		 * Envoit un message si différent.
		 *
		 * //Retour: On verifie les unités 
		*/ 
		
		# Verifie la province
		$ID = clean($_POST['province']);
		$MessageEnvoit = '';

		$sql = "SELECT a.id, a.id_joueur, b.ally_id, b.pseudo, b.race FROM provinces AS a LEFT JOIN joueurs AS b ON b.id = a.id_joueur WHERE a.id = '".$ID."'";
		$req = sql_query($sql);
		$res = sql_array($req);
		
		# Si c'est notre province mais pas celle courante
		#	OU
		# Si notre ally est pas zéro et que le processeur est pareil
		if(
			($res['id_joueur'] == $_SESSION['id_joueur'] && $res['id'] != $_SESSION['id_province']) 
			||
			($Joueur->ally_id > 0 && $Joueur->ally_id == $res['ally_id'])
		) {	# La province est valide
			
			# Temps pour la guerre
			$ResultatTemp = calc_WarTime($_SESSION['id_province'], $res['id'], $CONF['war_time'], $CONF['war_min'], $CONF['vitesse_jeu'], $Joueur->ally_id);
			$NbCrea = 0;

			# Calcul le nombre de tente
			$sql_u = "SELECT id FROM batiments WHERE id_province = '".$res['id']."' AND value = '1' AND id_batiment = '".$CONF['bati_tente_id']."'";
			$req_u = sql_query($sql_u);
			$nb_tentes = sql_rows($req_u);
			$nb_max_unites = $nb_tentes*$CONF['war_tente_capa'];
			if($res['race'] == 2) $nb_max_unites = $nb_tentes*($CONF['war_tente_capa']+$CONF['bonus_barbares_2']);

			$sql_u = "SELECT id FROM armees WHERE id_province = '".$res['id']."'";
			$req_u = sql_query($sql_u);
			$nb_unites = sql_rows($req_u);

			$nb_libre = $nb_max_unites - $nb_unites;

			# On passe les unités
			foreach($_POST as $key => $value)
			{
				if(substr($key, 0, 4) == 'lst_')
				{
					if($value != 0)
					{
						//Do we have this number?
						$exp = explode("_", $key);
						$csql = "SELECT id FROM armees WHERE id_province = '".$_SESSION['id_province']."' AND ID_creature = '".clean($exp[1])."' AND dispo = '1';";
						$creq = sql_query($csql);
						if(sql_rows($creq) >= $value)
						{
							if($nb_libre > 0) {
								$cup = "UPDATE armees SET dispo = '9', id_guerre = '0', heureretour = '".$ResultatTemp."', id_joueur = '".$res['id_joueur']."', id_province = '".$res['id']."' WHERE id_province = '".$_SESSION['id_province']."' AND dispo = '1' AND ID_creature = '".$exp[1]."' LIMIT ".$value.";";
								sql_query($cup);
								$NbCrea += $value;
								$nb_libre--;
							} else {
							}
						} else {
							$MessageEnvoit = bw_error("Hacking error<br />");
						}
					}
				}
			}

			if($NbCrea > 0) { // On a bien envoyé des unités
				// Informe le destinataire si différent

				if($_SESSION['id_joueur'] != $res['id_joueur']) {
					//Envois un message au joueur concerné
					$SendMess = "Le Héros ".$Joueur->pseudo." vous a envoyé des troupes sur votre province ".$Nom."! Elles arriveront le ".date($CONF['game_timeformat'], $ResultatTemp).".";
					send_message(999999991, $res['id_joueur'], $SendMess, 1);
				}
				
				//Affiche à l'écran
				$MessageEnvoit = bw_info("Vous avez bien envoyé vos unité à la province ".$res['nom']." du Héros ".$res['pseudo'].". Vos unités arriveront à destination le ".date($CONF['game_timeformat'], $ResultatTemp).".");
			} else {
				//Affiche à l'écran
				$MessageEnvoit = bw_info("Vos unités n'ont pas put être envoyées. Verifier que vous avez bien sélectionnez une province valide, ainsi que au moins une unité.");
			}

			if($nb_libre <= 0) {
				// Pas assez de place
				$MessageEnvoit .= bw_info("La province ciblée ne dispose pas assez de tente pour toutes les unités!");
			}

		}

		include("./gestion_unites.php");
		exit;

		break;
}
include("./guerre.php");