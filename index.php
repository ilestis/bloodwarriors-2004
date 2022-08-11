<?php
/* ------------------INDEX------------------*/
//session
@session_start();

//Début d'exécution
$Temp = explode(" ",microtime()); 
$MicroTime['debut'] = $Temp[1]+$Temp[0];

//includes
/*function global_variables2($folder)
{//Global variables of the games
	require $folder;

	foreach ($CONF as $element => $valeur)
	{//Prend chaque ligne du forumat
		$current_global_variable[$element] = stripslashes($valeur);
	}
	return $current_global_variable;
}*/
global $CONF;
require('include/variables.inc.php');

//$CONF = global_variables2('include/variables.inc.php'); //Variables global de configuration

require_once('./include/fonction.php'); //Les fonctions
require_once('./include/function_mef.php');
require_once('./class/class.MySql.php');
require_once('./class/class.Cookie.php');
$csql = new sql();
$cook = new classCookie();

//Lanche connection
if(!$csql->connection($GLOBALS['CONF']['game_DB_user'], $GLOBALS['CONF']['game_DB_psw'] ,  $GLOBALS['CONF']['game_DB_server'], $GLOBALS['CONF']['game_DB_name'])) {
	//echo $csql->error();
	die("Impossible de se connecter au serveur!");
}

//Langues
$LANG = (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr');
$lang_links = lang_include($LANG,'lang_colone');
$lang_over	= lang_include($LANG,'lang_overall');

//include 'connect.php'; //Connextion BDD

//Verifie si on est banni
/*$sql = "SELECT ip FROM autres_bannedip WHERE ip = '".$_SERVER['REMOTE_ADDR']."'";
$req = sql_query($sql);
$nombre = mysql_num_rows($req);
if ($nombre == 1) {
	//On est banni
	exit;
}*/

//Définit la méthode anti-hackage
define('IN_GAME', true);

//Cookie?
if(!isset($_SESSION['id_joueur']))  $cook->GetCookies();

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 4.01t//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
echo "
<html>
<head>
	<link rel=\"SHORTCUT ICON\" type=\"image/x-icon\" href=\"images/logo_bw.png\" />
	<title>".$lang_over['page_title']."</title>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />
	<script language=\"javascript\" type=\"text/javascript\" src=\"./include/general.js\"></script>\n";


if (session_is_registered("id_joueur") == true)
{//On est connecté pour les includes

	//Gère le changement de province
	if(isset($_GET['idprovince']))
	{//On change de province
		$ID =(is_numeric($_GET['idprovince']) ? clean($_GET['idprovince']) : $_SESSION['id_main_province']);

		//On verifie qu'elle est a nous
		$sql = "SELECT `id` FROM provinces WHERE id_joueur = '".$_SESSION['id_joueur']."' AND `id` = '".$ID."';";
		$req = sql_query($sql);
		$nbr = mysql_num_rows($req);
		if($nbr == 1)
		{
			$_SESSION['id_province'] = $ID;
		}
	}


	//object des données de la table stats
	$sql = "SELECT * FROM joueurs WHERE id = '".$_SESSION['id_joueur']."' ";
	$req = sql_query($sql);
	$supprime = mysql_num_rows($req);
	if ($supprime == 0) {
		echo $lang_over['no_account']; session_destroy(); exit; 	}
	$Joueur = mysql_fetch_object($req); //met tout dans un objet

	//Aut modifié?
	if($_SESSION['aut'] != $Joueur->aut) $_SESSION['aut'] = $Joueur->aut;

	/*if ($Joueur->acceslvl >= 99) {//On verifie s'il a activé le débug
		if (isset($_GET['deb'])) {
			if ($_GET['deb'] == 'on')		$_SESSION['debug']	=	TRUE;
			elseif ($_GET['deb'] == 'off')	$_SESSION['debug']	=	FALSE;
		}
	}*/

	//truc qui gere les paysans/batiments
	include 'autobat1.php';
	include 'autoresc1.php';

	$uptime = "UPDATE joueurs SET user_session = '".time()."' WHERE id = '".$_SESSION['id_joueur']."'";
	sql_query($uptime);

	echo "	<link rel=\"STYLESHEET\" type=\"text/css\" href=\"css".$Joueur->theme.".css\">\n";
}
else
{//pas connecté
	echo "	<link rel=\"STYLESHEET\" type=\"text/css\" href=\"css8.css\">\n"; 
}
//Meta-tags
echo "
	<META NAME=\"Author\" CONTENT=\"December Escape\">
	<META NAME=\"Keywords\" CONTENT=\"Blood Warriors, Jeu, PHP, Rôle, Jeu en ligne, Médiévale, Paysans, Héros\">
	<META NAME=\"Description\" CONTENT=\"Blood Warriors est un jeu gratuit sur internet. Incarnez un Héros de province pour gérer vos villes, paysans et armées!\">
</head>

<body>

<div class=\"container\">
	<div class=\"banner\">
		<a href=\"index.php\"><img src=\"images/css/v4/banner.png\" alt=\"Bannière\" title=\"Bannière\" /></a>
		</div>
	
		<div class=\"naviguation\">
			<a name=\"top\"></a>
			".bw_icon('btn_home.png')."<a href=\"?p=index\">".$lang_links['index']."</a> : ";

if(isset($_SESSION['id_joueur'])) {
	echo " ".bw_icon('btn_inbox.png')."<a href=\"?p=mess\">".$lang_links['inbox']."</a> : ";
	echo " ".bw_icon('btn_stats.png')."<a href=\"?p=statistiques\">Stats</a> : ";
} else {
	echo "  ".bw_icon('btn_subsribe.png')."<a href=\"?p=inscrip\">".$lang_links['inscriptions']."</a> : ";
	echo " ".bw_icon('btn_faq.png')."<a href=\"?p=faq\">".$lang_links['nocon_linkfaq']."</a>  : ";
}

echo "
 ".bw_icon('btn_news.png')."<a href=\"?p=annonces\">".$lang_links['news']."</a> :
  ".bw_icon('btn_forum.png')."<a href=\"?p=forumgen\">".$lang_links['forum']."</a> :
  ".bw_icon('btn_shootbox.png')."<a href=\"?p=shootbox\">".$lang_links['shootbox']."</a>   :";

//Connecté
$CinqMin = time() - 300; //5 minutes d'activités
$sql = "SELECT id FROM joueurs WHERE user_session >= '".$CinqMin."'";
$req = sql_query($sql); 
$Connectes = mysql_num_rows($req); 

echo "
  ".bw_icon('btn_online.png')."<a href=\"joueursconnectes.php\" title=\"".$lang_links['connected']."\" target=\"joueursconnectes\" onclick=\"window.open('joueursconnectes.php','joueursconnectes','height=200px, width=240px, resizable=yes');return false;\">".$lang_links['connected']."</a> ".$Connectes."

	</div>

	<div class=\"main\">

		<div class=\"colonne\">";
require('./colonne.php');
echo "
		</div>

		<div class=\"mainbox\">";

			if(session_is_registered("id_joueur") == true) 
				echo "			".TitreEntete()."\n";
			else 
				echo "			<strong>Blood Warriors - ".$GLOBALS['CONF']['game_echo']."</strong> ~ ".date($GLOBALS['CONF']['game_timeformat'], time())."\n";
//echo "			<hr>\n";

			//pseudo-frame droite
			require ('rubrique.php');

	//Fermeture des tableaux et pied de page
	require('./footer.php');
?>