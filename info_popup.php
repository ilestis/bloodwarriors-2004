<?php
session_start();

global $CONF;
require('include/variables.inc.php');
require_once('./include/fonction.php'); //Les fonctions
require_once('./include/function_mef.php');
require_once('./class/class.MySql.php');
$csql = new sql();
//Lanche connection
$csql->connection($GLOBALS['CONF']['game_DB_user'], $GLOBALS['CONF']['game_DB_psw'] , $alias='', $GLOBALS['CONF']['game_DB_name']);
$LANG = (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr');
$lang_over	= lang_include($LANG,'lang_overall');

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 4.01t//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
echo "<html>\n";
	
echo "<head>\n";
echo "	<link rel=\"SHORTCUT ICON\" type=\"image/x-icon\" href=\"images/logo_bw.png\" />\n";
echo "	<title>".$lang_over['page_title']."</title>\n";
echo "	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />\n";
echo "	<script language=\"javascript\" type=\"text/javascript\" src=\"./include/general.js\"></script>\n";
echo "</head>\n\n";

echo "<body>\n\n";

$DO = (isset($_GET['do']) ? clean($_GET['do']) : 'rien');
$ID = (isset($_GET['id']) ? clean($_GET['id']) : '0');

bw_f_start('Information');

if($DO == 'batiment') {
	$sql = "SELECT nom, niveau, power FROM liste_batiments WHERE id = '".$ID."';";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);

	echo "<strong>".$res['nom']." :</strong> (".bw_province_state($res['niveau']).")<br />\n";
	echo affiche($res['power']);
} elseif($DO == 'sort') {
	$sql = "SELECT nom, description, cout, race FROM liste_sorts WHERE id = '".$ID."';";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);

	echo "
	<div style=\"float:left; margin: 2px;\">
		<img src=\"./images/sorts/".$ID.".png\" alt=\"".$res['nom']."\" title=\"".$res['nom']."\" />
	</div>
	<strong>".$res['nom']." :</strong><br />";
	if($res['race'] != 0) {
		echo "
	Race : ".return_guilde($res['race'])."<br />";
	}
	
	echo "
	Coût: ".$res['cout']." magie.<br />\n";
	echo affiche($res['description']);
} else {
	echo bw_error("Erreur générale");
}

bw_f_end();