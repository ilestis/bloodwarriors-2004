<?php
session_start();
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
$csql->connection($GLOBALS['CONF']['game_DB_user'], $GLOBALS['CONF']['game_DB_psw'] , $alias='', $GLOBALS['CONF']['game_DB_name']);

$dos = "./images/provinces/";

$positions = array(
	"ecole" =>			array('x' => '180', 'y' => '190'),
	"murailles" =>		array('x' => '160', 'y' => '150'),
	"municipale" =>		array('x' => '160', 'y' => '200'),
	"fort" =>			array('x' => '150', 'y' => '240'),
	"marche" =>			array('x' => '170', 'y' => '220'),
	"sanctuaire" =>		array('x' => '140', 'y' => '180'),
	"mine" =>			array('x' => '180', 'y' => '360'),
	"foyer" =>			array('x' => '170', 'y' => '170'),
	"auberge" =>		array('x' => '230', 'y' => '170'),
	"forgerie" =>		array('x' => '180', 'y' => '140'),
	"ferme" =>			array('x' => '290', 'y' => '170'),
	"guildedereperage" =>		array('x' => '140', 'y' => '230'),

	"last" => array('x' => '', 'y' => '')
);

//Créé l'image à partir de l'image
$image = imagecreatefrompng($dos."plaine.png");

$plateau = imagecreate(400, 400);
$sql = "SELECT code_nom FROM liste_batiments WHERE niveau = '1'";
$req = sql_query($sql);
while($res = sql_object($req)) {
	$create = imagecreatefrompng($dos.$res->code_nom.".png");
	$l = imagesx($create);
	$h = imagesy($create);
	imagecopymerge($image, $create, $positions[$res->code_nom]['x'], $positions[$res->code_nom]['y'], 0, 0, $l, $h, 80);
}
// Création de l'image complète au format PNG
header("Content-type: image/png");
imagepng($image);
