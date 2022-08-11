<?php
require('adminheader.php');

$Page = 'nouveauxcomptes';

if($_SESSION['aut'][$adminpage['nouveauxcomptes']] == 0) breakpage();

if(isset($_GET['id']))
{
	//Identifiant
	$JId = clean($_GET['id']);

	//Prend les informations
	$sql = "SELECT * FROM `inscriptions` WHERE `Id` = '".$JId."'";
	$req = sql_query($sql);
	$nbr = mysql_num_rows($req);
	if ($nbr == 0) { echo "Aucune demande correspond.<br />\n"; exit; }

	$res = mysql_fetch_array($req);
	$JPseudo	=	$res['Pseudo'];
	$JLogin		=	$res['Login'];
	$JPsw		=	$res['Password'];
	$JSurname	=	$res['Surname'];
	$JName		=	$res['Name'];
	$JEmail		=	$res['Email'];
	$JDisco		=	$res['Discovery'];
	$JRace		=	$res['Race'];
	$JIp		=	$res['Ip'];
	$Time		=	time();

	//email
	include 'include/function_email.php';

	if ($_GET['do'] == 'add') {//On l'ajoute

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
		$SqlJoueur = "INSERT INTO `joueurs`  VALUES ";
		$SqlJoueur .= "('', '".$JLogin."', '".$JPsw."', '".$JPseudo."', '', '".$JRace."', 0, 0, 1, '".$CONF['start_puissance']."', '".$CONF['default_css']."', '', 0, 0, 0, 'fr', 0, '".$JSurname."', '".$JName."', '".$JEmail."', '".time()."', '".$JDisco."', '100000000000001')";
		sql_query($SqlJoueur);

		$JoueurId = mysql_insert_id();

		//Crée la table de la province
		$SqlProvince = "INSERT INTO `provinces` VALUES ";
		$SqlProvince .= "('', '".$JoueurId."', 'NoName', '".$x."', '".$y."', '".$CONF['start_or']."', '".$CONF['start_champs']."', '".$CONF['start_pierre']."', '".$CONF['start_bois']."', '".$CONF['start_magie']."', '".$CONF['start_paysans']."', 1, 0, 0, '".$CONF['start_cases_1']."', '".$CONF['start_cases_2']."', '".$CONF['start_cases_tot']."')";
		sql_query($SqlProvince);

		$ProvinceId = mysql_insert_id();

		//Crée la table des paysans
		$sqlpaysans = "INSERT INTO `temp_paysans` VALUES('', '".$JoueurId."', 0, '".$CONF['start_paysans']."', '', '".$ProvinceId."')";
		sql_query($sqlpaysans) ; 
		
		//Vire l'inscription
		$sql = "DELETE FROM `inscriptions` WHERE `Id` = '".$JId."'" ;
		sql_query($sql) ;

		//créé le truc pour les forums non-lu
		$sql2 = "INSERT INTO `forum_last_visite`(`id_joueur`) VALUES ('".$JoueurId."')";
		sql_query($sql2);
		

		//Sujet
		$email_subject = "Bienvenue sur Blood Warriors!";
		//message
		$message = 'Bienvenue '.$JPseudo.' sur le jeu de Blood Warriors. 
		Votre compte à été activé!<br>
			Pseudo: '.$JPseudo.'<br> 
			Login: '.$JLogin.'<br><br>

		Conservez bien ces données, car sans elles vous ne pourrez plus vous connecter au jeu.<br><br>

		L\'équipe des admins vous souhaitent un bon jeu sur "http://bw.decemberescape.com".<br><br>

		Si vous avez un problème avec votre connexion, répondez simplement en postant sur le forum du jeu, ou à l\'adresse bloodwarriors@gmail.com <br><br>
		Merci.<br><br>
				
			Cordialement, Les Admins<br>';
		//rend joli joli
		$message = strip_tags(eregi_replace("<br>", "\n", $message));
		//envoit
		send_email($JEmail, $message, $email_subject);

		//message du journal:
		$action = "<img src=\"images/admin/ok.png\">Le joueur ".$JPseudo." a été activé.";
		journal_admin($Joueur->pseudo, $action);

		echo '<span class="avert">Le compte '.$JPseudo.' a été validé.</span><br />';

	} 

	elseif ($_GET['do'] == 'del') {//On le supprime
		$Del = "DELETE FROM `inscriptions` WHERE `Id` = '".$JId."'";
		sql_query($Del);

		//Sujet
		$subject = "Blood Warriors";
		//message
		$message = "Bonjour ".$JPseudo.".<br>
				Votre demande d'inscription a été examinée, mais malheureusement votre compte n'a pas été accepté. <br>
				Les raisons peuvent être multiples, passant pas un manque d'informations (nom, prenom, etc...), ou même car vous tentez de créer un compte alors que vous en avez déjà un.<br><br>
				
				Cordialement, Les Admins<br>";

		//rend joli joli
		$message = strip_tags(eregi_replace("<br>", "\n", $message));
		//envoit
		send_email($JEmail, $message, $email_subject);

		//message du journal:
		$action = "<img src=\"images/admin/no.png\">Le joueur ".$JPseudo." a été refusé.";
		journal_admin($Joueur->pseudo, $action);
	}

	//Page de nouveaux comptes
	include ('admin/comptes_nouveaux.php');
}
?>