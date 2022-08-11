<?php

//Les variables
$JPseudo	=	clean($_POST['pseudo']);
$JPrenom	=	clean($_POST['prenom']);
$JNom		=	clean($_POST['nom']);
$JEmail		=	clean($_POST['email']);
$JLogin		=	clean($_POST['logininsc']);
$JRace		=	clean($_POST['list_royaume']);
$JDisco		=	clean($_POST['decouverte']);
$JPsw		=	clean($_POST['mdp']);
$JPsw2		=	clean($_POST['mdp2']);
$JChk		=	clean($_POST['charte']);
$JImg		=	clean($_POST['sha']);
$Time		=	time();
$err = 0;


//Places disponnibles
$sql	= "SELECT id FROM joueurs WHERE `race` = '".$JRace."'";
$req	= sql_query($sql) ;
$Pris	= mysql_num_rows($req) ;
$Dispo	= $CONF['game_race_limit'] - $Pris;

//Cheak premier lot d'erreur
$sql = "SELECT id FROM joueurs WHERE pseudo = '".$JPseudo."' OR login = '".$JLogin."' OR email = '".$JEmail."'"; 
$req = sql_query($sql); 	
$NbTrouve = mysql_num_rows($req);

if ($NbTrouve == 0 && ($JRace > 0 || $JRace < 7) && $JPseudo != '' && $JNom != '' && $JPrenom != '' && $JPsw != '' && $JChk == 'on' && ($JPsw == $JPsw2)  && ($_SESSION['subsribe_img_code'] == $JImg))
{
	if ($Dispo > 0)
	{
		//l'adresse IP
		$Ip = $_SERVER['REMOTE_ADDR']; 

		//Connexion
		$SqlIp = "INSERT INTO `autres_ip` VALUES('','".$JPseudo."','".$Ip."','".$Time."')";
		sql_query($SqlIp) ;

		if($CONF['game_auto_activate'] == 1)
		{//On active directement
			//Choisi un x et un y aléatoirement
			$disponible = FALSE;
			while($disponible == False)
			{//tant que c'est pas disponibles
				$x = mt_rand(1, $CONF['game_case']);
				$y = mt_rand(1, $CONF['game_case']);
				$dispo = "SELECT id FROM provinces WHERE `x` = '".$x."' AND `y` = '".$y."'";
				$requi = sql_query($dispo);
				$repo = mysql_num_rows($requi);

				if ($repo == 0)
				{//oki!
					$disponible = True;
				}
			}


			//Crée la table du joueur
			$SqlJoueur = "INSERT INTO `joueurs` "
			. "SET login = '".$JLogin."', "
			. "password = '".md5($JPsw)."', "
			. "pseudo = '".$JPseudo."', "
			. "race = '".$JRace."', "
			. "aut = '".$CONF['start_auth']."', "
			. "theme = '".$CONF['default_css']."', "
			. "lang = 'fr', "
			. "prenom = '".$JPrenom."', "
			. "nom = '".$JNom."', "
			. "email = '".$JEmail."', "
			. "activationtime = '".$Time."', "
			. "decouverte = '".$JDisco."' ";
			sql_query($SqlJoueur);

			$JoueurId = mysql_insert_id();

			//Crée la table de la province
			$SqlProvince = "INSERT INTO `provinces` "
			. "SET id_joueur = '".$JoueurId."', "
			. "name = 'Nouvelle Province', "
			. "x = '".$x."', "
			. "y = '".$y."', "
			. "gold = '".$CONF['start_or']."', "
			. "food = '".$CONF['start_champs']."', "
			. "mat = '".$CONF['start_mat']."', "
			. "stone = '".$CONF['start_pierre']."', "
			. "wood = '".$CONF['start_bois']."', "
			. "craft = '".$CONF['start_magie']."', "
			. "peasant = '".$CONF['start_paysans']."', "
			. "etat = '1', "
			. "cases_usuable = '".$CONF['start_cases_1']."', "
			. "cases_notusuable = '".$CONF['start_cases_2']."', "
			. "cases_total = '".$CONF['start_cases_tot']."', "
			. "type_province = '0', "
			. "satisfaction = '100'; ";
			sql_query($SqlProvince);

			$ProvinceId = mysql_insert_id();

			//Crée la table des paysans
			$sqlpaysans = "INSERT INTO `temp_paysans`  SET "
			. "id_joueur = '".$JoueurId."',  "
			. "section = '0',  "
			. "nombre = '".$CONF['start_paysans']."',  "
			. "id_province = '".$ProvinceId."' , "
			. "esclave = 'N'";
			sql_query($sqlpaysans) ; 
			
			# Les esclaves
			$sqlpaysans = "INSERT INTO `temp_paysans`  SET "
			. "id_joueur = '".$JoueurId."', "
			. "section = '0', "
			. "nombre = '0', "
			. "id_province = '".$ProvinceId."',
			esclave = 'O'";
			sql_query($sqlpaysans) ; 

			//créé le truc pour les forums non-lu
			$sql2 = "INSERT INTO `forum_last_visite`(`id_joueur`) VALUES ('".$JoueurId."')";
			sql_query($sql2);

			//Message
			echo "Votre compte a été créé! Vous pouvez maintenant vous connecter au jeu!";
		} else {
			//L'inscription
			$SqlIns = "INSERT INTO `inscriptions` 	VALUES('','".$Pseudo."', '".$Login."', '".$Password."', '".$Surname."', '".$Name."', '".$Email."', '".$Disco."', '".$Race."', '".$Ip."', '".$Time."')";
			sql_query($SqlIns);

			echo "Votre demande d'inscription à Blood Warriors a été enregistrée.<br />Elle sera très prochainement examinée par les administrateurs. Rassurez-vous! L'examination ne prend pas rarement plus de 24 heures, donc surveillez votre boite e-mail!<br /><br />Que vous soyez accepter à Blood Warriors ou pas, vous recevrez de toute façon un email.<br />\n";
		}
	}
	else
	{
			$err = 1;
			$Err_Race = "Il n'y a plus de place dans cette race";
	}
}
else
{
	$err = 1;
	
	if(!isset($JPseudo) || empty($JPseudo)) $Err_Pse = 'Veuillez choisir un pseudo';
	if(!isset($JLogin) || empty($JLogin)) $Err_Log = 'Veuillez choisir un login';
	if(!isset($JRace) || empty($JRace)) $Err_Roy = 'Veuillez choisir un royaume';
	if(!isset($JNom) || empty($JNom)) $Err_Nom = 'Veuillez remplire votre Nom';
	if(!isset($JPrenom) || empty($JPrenom)) $Err_Prenom = 'Veuillez remplire votre Prénom';
	if(!isset($JPsw) || empty($JPsw)) $Err_Psw = 'Veuillez approuver la Charte';
	if(!isset($JChk) || $JChk != 'on') $Err_Charte = 'Veuillez approuver la Charte';
	if(!isset($JImg) || $JImg != $_SESSION['subsribe_img_code']) $Err_Img = 'Vous avez mal entré le code. Seriez-vous un bot?';
	if($JPsw != $JPsw2) $Err_Psw2 = 'Les mots de passes ne correspondent pas!';

	//Erreur de doublons
	/*$sql = "SELECT (select count(1) FROM joueurs WHERE id = '1' ), (select count(1) FROM joueurs WHERE pseudo = 'Escapee')";
	$req = mysql_query($sql) or printf(mysql_error());
	$res = mysql_fetch_array($req);
	
	echo "Joueurs ID 1: ".$res[0]."<br />";
	echo "Joueurs Pseudo Escapee: ".$res[1]."<br />";
	*/
	$sql = "SELECT id FROM joueurs WHERE pseudo = '".$JPseudo."'"; 
	$req = sql_query($sql); 	
	$PseudoTaken = mysql_num_rows($req);
	
	$sql = "SELECT id FROM joueurs WHERE login = '".$JLogin."'";  	
	$req = sql_query($sql); 	
	$LoginTaken = mysql_num_rows($req);		

	$sql = "SELECT id FROM joueurs WHERE email = '".$JEmail."'";  	
	$req = sql_query($sql); 	
	$EmailTaken = mysql_num_rows($req);	

	if($PseudoTaken > 0) $Err_Pse = 'Ce Pseudo est déjà utilisé';
	if($LoginTaken > 0) $Err_Log = 'Ce Login est déjà utilisé';
	if($EmailTaken > 0) $Err_Mail = 'Ce Mail est déjà utilisé';
		
}

if($err == 1)
{
	require('./inscription.php');
}	
?>