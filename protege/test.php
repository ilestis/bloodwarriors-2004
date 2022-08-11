<?php
session_start();
/*----------------------[TABLEAU]---------------------
|Nom:			WarRunner.Php
+-----------------------------------------------------
|Description:	Le fichier qui calcule chaque guerre (unit� par unit�)
+-----------------------------------------------------
|Date de cr�ation:				12/02/05
|Date du premier test:			26/03/05
|Derni�re modification[Auteur]: 27/11/06[Escape]
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
//D�but d'ex�cution
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


//Fin d'ex�cution
$Temp = explode(" ",microtime()); 
$MicroTime['end'] = $Temp[1]+$Temp[0];
$MicroTime['final'] = $MicroTime['end'] - $MicroTime['debut'];

echo "Temps pour charger les librairies: ".round($MicroTime['final'], 5).".<br />\n";

$Select = "id, nom, power_1, power_2, power_3, power_4";
$Select = "*";

/*---------------------------------------------
                ARRAY
---------------------------------*/
//D�but d'ex�cution
$Temp = explode(" ",microtime()); 
$MicroTime['debut'] = $Temp[1]+$Temp[0];


//On va chercher toutes les unit�s avec fetch_array
$sql = "SELECT ".$Select." FROM armees ORDER BY id ASC";
$req = sql_query($sql);
while($res = mysql_fetch_array($req))
{
	$tmp = $res['power_1'] + $res['power_2'];
}

//Fin d'ex�cution
$Temp = explode(" ",microtime()); 
$MicroTime['end'] = $Temp[1]+$Temp[0];
$MicroTime['final_array'] = $MicroTime['end'] - $MicroTime['debut'];

//echo "<br />Temps total (array):  ".round($MicroTime['final'], 5).".<br />";

/*---------------------------------------------
                ASSOC
---------------------------------*/
//D�but d'ex�cution
$Temp = explode(" ",microtime()); 
$MicroTime['debut'] = $Temp[1]+$Temp[0];

//On va chercher toutes les unit�s avec fetch_array
$sql = "SELECT ".$Select." FROM armees ORDER BY id ASC";
$req = sql_query($sql);
while($res = mysql_fetch_assoc($req))
{
	$tmp = $res['power_1'] + $res['power_2'];
}

//Fin d'ex�cution
$Temp = explode(" ",microtime()); 
$MicroTime['end'] = $Temp[1]+$Temp[0];
$MicroTime['final_assoc'] = $MicroTime['end'] - $MicroTime['debut'];

if($MicroTime['final_assoc'] > $MicroTime['final_array']) $_SESSION['array_'.$_SESSION['type']] += 1;
else  $_SESSION['assoc_'.$_SESSION['type']] += 1;

//echo "<br />Temps total (assoc): ".round($MicroTime['final'], 5).".<br />";



if($_SESSION['cpt'] == 100 && $_SESSION['type'] = 1)
{
	echo "<p><strong>Type de requ�te:</strong> 6 champs sur 16<br />\n";
	echo "<strong>Array:</strong> ".$_SESSION['array_0']."<br />\n";
	echo "<strong>Assoc:</strong> ".$_SESSION['assoc_0']."</p><br />\n";

	
	echo "<p><strong>Type de requ�te:</strong> * (16 champs sur 16)<br />\n";
	echo "<strong>Array:</strong> ".$_SESSION['array_1']."<br />\n";
	echo "<strong>Assoc:</strong> ".$_SESSION['assoc_1']."</p><br />\n";


	die();

}

//redirection('test.php', 1);

?>