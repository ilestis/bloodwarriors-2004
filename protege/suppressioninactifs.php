<?php
/*----------------------[TABLEAU]---------------------
|Nom:			SuppressionInactifs.Php
+-----------------------------------------------------
|Description:	Avertit par email les joueurs qui ne se sont pas ressemment connecté
| Et les supprime si besoin.
+-----------------------------------------------------
|Date de création:				03/06/05
|Dernière modification[Auteur]: 03/06/05[Kaio]
+---------------------------------------------------*/

//verifie si on essaye d'acceder depuis autre que le crontab.
if ($_GET['monchapeau'] != 'salutcmoicunlapin')
{
	exit;
}
//Includes
global $CONF;
require('../include/variables.inc.php');
require_once('../include/fonction.php'); //Les fonctions
require_once('../class/class.MySql.php');
$csql = new sql();

//Lanche connection
$csql->connection($GLOBALS['CONF']['game_DB_user'], $GLOBALS['CONF']['game_DB_psw'] , $alias='', $GLOBALS['CONF']['game_DB_name']);

$RealyDel = false;

//prend chaque pseudo
$sql = "SELECT * FROM joueurs WHERE acceslvl > '0' AND acceslvl < '3' ORDER BY pseudo ASC";
$req = sql_query($sql);
while($res = mysql_fetch_object($req)) // on prend tout les joueurs
{
	$Nom = $res->nom;
	$Prenom = $res->prenom;
	$Email = $res->email;
	$Pseudo = $res->pseudo;

	echo "<strong>Joueur</strong>: ".$Pseudo." <br />\n";

	//prend sa dernière connexion au jeux (la plus ressente et que une seul)
	$req_ip = "SELECT `time` FROM ip WHERE id_joueur= '".$res->id."' ORDER BY time DESC LIMIT 1 " ;
	// time entre `` car c'est une fonction sql et php donc faut indiquer que c'est un champs!
	$sql_ip = mysql_query($req_ip);
	if(sql_rows($sql_ip) == 1) //Cherche la date... Sinon; PanPan
	{
		$ip = mysql_fetch_array($sql_ip);
		//verifie la date
		$temps_d_inactivite = time() - $ip['time'];
		$cinqjours = 3600*24*$CONF['time_inactif_alert'];
		$cinqjourmax = $cinqjours+1800;
		$septjours = 3600*24*$CONF['time_inactif_delete'];

	}
	else
	{
		//On verifie sa date d'inscription
		$temps_d_inactivite = time() - $res->activationtime;
	}
	$echo_temps = ceil($temps_d_inactivite/3600); //heures
	echo "Temps d'inactivité: ".$echo_temps." heures <br />\n";

		

	//verifie
	if ($temps_d_inactivite >= $cinqjours)
	{//ça fait 6 jours
		if (($temps_d_inactivite >= $septjours) && $RealyDel)
		{//sept jour -> on supprime
			echo "<FONT COLOR=\"#FF0000\"<em>Delete!</em></FONT> <br /> \n";

			//Supprime les comptes
			$sqldel = "DELETE FROM `paysans` WHERE pseudo = '".$Pseudo."'" ;
			sql_query($sqldel) ;
			$sqldel = "DELETE FROM `stats` WHERE pseudo = '".$Pseudo."'" ;
			sql_query($sqldel) ;
			$sqldel = "DELETE FROM `batiments1` WHERE pseudo = '".$Pseudo."'" ;
			sql_query($sqldel) ;
			$sqldel = "DELETE FROM `batiments2` WHERE pseudo = '".$Pseudo."'" ;
			sql_query($sqldel) ;
			$sqldel = "DELETE FROM `batiments3` WHERE pseudo = '".$Pseudo."'" ;
			sql_query($sqldel) ;
			$sqldel = "DELETE FROM `batiments4` WHERE pseudo = '".$Pseudo."'" ;
			sql_query($sqldel) ;
			$sqldel = "DELETE FROM `armees` WHERE `Owner` = '".$Pseudo."'" ;
			sql_query($sqldel) ;
			$sqldel = "DELETE FROM `prive` WHERE pseudo = '".$Pseudo."'";     
			sql_query($sqldel) ;
			$sqldel = "DELETE FROM `forum_last_visite` WHERE pseudo= '".$Pseudo."'";
			sql_query($sqldel) ;
			$sqldel = "DELETE FROM `ip` WHERE `pseudo` = '".$Pseudo."'";
			sql_query($sqldel) ;
			$sqlwar = "DELETE FROM `warnings` WHERE `pseudo` = '".$Pseudo."'";
			sql_query($sqlwar) ;

			//journal
			journal_admin("CronTab", "<img src=\"images/admin/no.png\">Le joueur ".$Pseudo." à été supprimé!");

		}
		elseif($temps_d_inactivite <= $cinqjoursmax)
		{//seulement six jours
			//send un email

			//données du email
			$subject = "Blood Warriors: Inactivité!";
			$message = "Bonjour $Prenom $Nom, <br />Cela fait maintenant plus de 5 jours que vous vous n'êtes plus connecté a Blood Warriors! Il vous reste 24heures pour vous connecter, sinon votre héros sera supprimé. <br /><br />";
			$message .= "Nous espérons vous retrouver tout bientôt sur sur Blood Warriors! Si vous avez perdu l'adresse, là voici: ".$CONF['game_address']." <br />";
			$message .= "<br />L'équipe des admins";

			//change les tabs pour le format d'email
			$message = strip_tags(eregi_replace("<br />", "\n", $message));

			// on apelle la fonction prévu pour envoyer le mail
			if(send_email($Email, $message, $subject))
				echo "<em>Message envoyé! Il lui reste 24heures le pauvre!</em><br />\n";
		}//fin email
	}//si on le supprime
}//end chaque joueurs
mysql_close();
?>