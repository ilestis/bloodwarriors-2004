<?php
//verifie si la session est en cours
require('adminheader.php');

//verifie notre niveau d'admin
$Page = 'journal';

bw_tableau_start("Journal des évenements");


echo "			<table class=\"newsmalltable\">\n";
$sql = "SELECT * FROM `messages` WHERE location = 'jou' ORDER BY `time` DESC";
$req = sql_query($sql);
$cpt = 0;
while ($res = mysql_fetch_array($req))
{ 
	echo "			<tr>\n";
	echo "				<td width=\"35%\">".date($CONF['game_timeformat'], $res['time'])." | <strong>".joueur_name($res['id_from'])."</strong></td>\n";
	echo "				<td width=\"65%\">".nl2br($res['message'])."</td>\n";
	echo "			</tr>\n";
	$cpt ++;
}
echo "			</table>\n";
if($cpt == 0) {
	echo bw_info("Aucune entrée!<br />\n");
}

echo "Les messages s'auto-détruisent au bout de ".$CONF['game_mess_exp']."jours. (En théorie... :))<br /><br />";
retour();

bw_tableau_end();
?>