<?php
/*++++++++++++++++++++++++
| DB_CLEANUP.PHP
|
+------------------------
| Nettoye la base de données, la purge, etc...
| 
| À prévoir: Backup?
+++++++++++++++++++++++++*/
//Includes
global $CONF;
require('../include/variables.inc.php');
require_once('../include/fonction.php'); //Les fonctions
require_once('../class/class.MySql.php');
$csql = new sql();

//Lanche connection
if(!$csql->connection($GLOBALS['CONF']['game_DB_user'], $GLOBALS['CONF']['game_DB_psw'] ,  $GLOBALS['CONF']['game_DB_server'], $GLOBALS['CONF']['game_DB_name'])) {
	echo $csql->error();
	die("Impossible de se connecter au serveur!");
}

//Clean les connections IPS
$DelTime = time() - (86400*$CONF['time_keep_ip']); //Sur x jours
sql_query("DELETE FROM `ip` WHERE `time` < '".$DelTime."'");

//Clean les messages journaux 
sql_query("DELETE FROM `messages` WHERE `expiration` > '0' AND `expiration` < '".time()."'");
?>