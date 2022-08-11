<?php
session_start();
/*----------------------[TABLEAU]---------------------
|Nom:			WarRunner.Php
+-----------------------------------------------------
|Description:	Le fichier qui calcule chaque guerre (unité par unité)
+-----------------------------------------------------
|Date de création:				12/02/05
|Date du premier test:			26/03/05
|Dernière modification[Auteur]: 27/11/06[Escape]
+-----------------------------------------------------
|Mise en forme:
| - Choisi chaque guerre et lui enlève 1 jours.
| -- Si une guerre passe à zéro:
| - Choisi les bâtiments ennemis
| - Selectionne chaque unités attaquantes
| - Met à jour les stats de la créature attaquantes + effet de la technique
| - Choisi une créature adverse la mieux placée pour défendre
| - Calcul le résultat, et met en place des variable de gain/perte, éventuellement tue l'une des 2 unités.
| - Calcul les gains/perte de ressources.
| - Calcul les dégats effectués aux bâtiments 
+---------------------------------------------------*/
//Début d'exécution
$Temp = explode(" ",microtime()); 
$MicroTime['debut'] = $Temp[1]+$Temp[0];

//Session
if(!isset($_SESSION['type'])) 
{
	$_SESSION['type'] = 0;
	$_SESSION['cpt'] = 0;
	$_SESSION['array_0'] = 0;
	$_SESSION['assoc_0'] = 0;
	$_SESSION['array_1'] = 0;
	$_SESSION['assoc_1'] = 0;
	$_SESSION['type'] = 0;
} elseif($_SESSION['cpt'] < 99 && $_SESSION['type'] < 2)
{
	$_SESSION['cpt']++;
}
elseif($_SESSION['type'] < 2)
{
	$_SESSION['cpt'] = 0;
	$_SESSION['type'] += 1;
}
else
{
	$_SESSION['cpt'] = 100;
	$_SESSION['type'] += 1;
}

//Includes
global $CONF;
require('../include/variables.inc.php');
require_once('../include/fonction.php'); //Les fonctions
require_once('../class/class.MySql.php');
$csql = new sql();

//Lanche connection
$csql->connection($GLOBALS['CONF']['game_DB_user'], $GLOBALS['CONF']['game_DB_psw'] , $alias='', $GLOBALS['CONF']['game_DB_name']);


//Fin d'exécution
$Temp = explode(" ",microtime()); 
$MicroTime['end'] = $Temp[1]+$Temp[0];
$MicroTime['final'] = $MicroTime['end'] - $MicroTime['debut'];

echo "Temps pour charger les librairies: ".round($MicroTime['final'], 5).".<br />\n";

$Select = "id, nom, power_1, power_2, power_3, power_4";
$Select = "*";

/*---------------------------------------------
                ARRAY
---------------------------------*/
//Début d'exécution
$Temp = explode(" ",microtime()); 
$MicroTime['debut'] = $Temp[1]+$Temp[0];


//On va chercher toutes les unités avec fetch_array
$sql = "SELECT ".$Select." FROM armees ORDER BY id ASC";
$req = sql_query($sql);
while($res = mysql_fetch_array($req))
{
	$tmp = $res['power_1'] + $res['power_2'];
}

//Fin d'exécution
$Temp = explode(" ",microtime()); 
$MicroTime['end'] = $Temp[1]+$Temp[0];
$MicroTime['final_array'] = $MicroTime['end'] - $MicroTime['debut'];

//echo "<br />Temps total (array):  ".round($MicroTime['final'], 5).".<br />";

/*---------------------------------------------
                ASSOC
---------------------------------*/
//Début d'exécution
$Temp = explode(" ",microtime()); 
$MicroTime['debut'] = $Temp[1]+$Temp[0];

//On va chercher toutes les unités avec fetch_array
$sql = "SELECT ".$Select." FROM armees ORDER BY id ASC";
$req = sql_query($sql);
while($res = mysql_fetch_assoc($req))
{
	$tmp = $res['power_1'] + $res['power_2'];
}

//Fin d'exécution
$Temp = explode(" ",microtime()); 
$MicroTime['end'] = $Temp[1]+$Temp[0];
$MicroTime['final_assoc'] = $MicroTime['end'] - $MicroTime['debut'];

if($MicroTime['final_assoc'] > $MicroTime['final_array']) $_SESSION['array_'.$_SESSION['type']] += 1;
else  $_SESSION['assoc_'.$_SESSION['type']] += 1;

//echo "<br />Temps total (assoc): ".round($MicroTime['final'], 5).".<br />";



if($_SESSION['cpt'] == 100 && $_SESSION['type'] = 1)
{
	echo "<p><strong>Type de requête:</strong> 6 champs sur 16<br />\n";
	echo "<strong>Array:</strong> ".$_SESSION['array_0']."<br />\n";
	echo "<strong>Assoc:</strong> ".$_SESSION['assoc_0']."</p><br />\n";

	
	echo "<p><strong>Type de requête:</strong> * (16 champs sur 16)<br />\n";
	echo "<strong>Array:</strong> ".$_SESSION['array_1']."<br />\n";
	echo "<strong>Assoc:</strong> ".$_SESSION['assoc_1']."</p><br />\n";


	die();

}

//redirection('test.php', 1);

?>