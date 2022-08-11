<?php
echo "<table class=\"newtable\">\n";

// Si on a pas de next en $_GET, on affiche la liste
if(!isset($_GET['id']))
{
	echo "<tr>\n";
	echo "	<td class=\"newtitre\">Annonces</td>\n";
	echo "</tr><tr>\n";
	echo "	<td class=\"newcontenu\">\n";

	//cherche les news ordre décroissant (miam des croissants ^^)
	$news = "SELECT a.*, b.pseudo FROM messages AS a LEFT JOIN joueurs AS b ON b.id = a.id_from WHERE a.location = 'ann' ORDER BY a.id_message DESC LIMIT 0, 4";
	//$news = "SELECT * FROM `admin_news` order by id desc limit 0, 4";
	$rews = sql_query($news);
	while ($art = mysql_fetch_array($rews))
	{
		echo "		<table class=\"newsmalltable\">\n";
		echo "		<tr>\n";
		echo "			<th>\n";
		echo "				<div style=\"float: left\">[".date($CONF['game_timeformat'],$art['time'])."] ".$art['titre']."</div>\n";
		echo "				<div style=\"float:right; text-align: right; font-size:11px;\">~ par ".$art['pseudo']."</div>\n";
		echo "			<br style=\"clear: both;\" />\n";
		echo "			</th>\n";
		echo "		</tr>\n";
		echo "		<tr>\n";
		echo "			<td>\n";
		echo "				".substr(affiche($art['message']), 0, 350)."...\n";
		echo "				<span style=\"float:right;\"><a href=\"?p=annonces&id=".$art['id_message']."\">Lire la suite</a></span><br />\n";			
		echo "			</td>\n";
		echo "		</tr>\n";
		echo "		</table>\n";
		echo "		<br /> 	\n";
	} 

} else {
	//On a un ID!
	$ID = clean($_GET['id']);

	//Check existance
	//$req = sql_query("SELECT * FROM `admin_news` WHERE id = '".$ID."'");
	$news = "SELECT a.*, b.pseudo FROM messages AS a LEFT JOIN joueurs AS b ON b.id = a.id_from WHERE a.location = 'ann' AND a.id_message = '".$ID."';";
	$req = sql_query($news);

	if(mysql_num_rows($req) == 1)
	{
		$res = mysql_fetch_array($req);
		echo "<tr>\n";
		echo "	<td class=\"newtitre\">".$res['titre']."</td>\n";
		echo "</tr><tr>\n";
		echo "	<td class=\"newcontenu\">\n";
		echo "		<table class=\"newsmalltable\"><tr>\n";
		echo "			<th>Posté par ".$res['pseudo']." le ".date($CONF['game_timeformat'],$res['time'])."</th>\n";
		echo "		</tr><tr>\n";
		echo "			<td>\n";
		echo "				".affiche($res['message'])."\n";
		echo "				<br />\n";
		echo "			</td>\n";
		echo "		</tr>\n";
		echo "		</table>\n";

	}
	else
	{//ID Non troubé
		echo "<tr>\n";
		echo "	<td class=\"newtitre\">Erreur</td>\n";
		echo "</tr><tr>\n";
		echo "	<td class=\"newcontenu\">\n";
		echo "		Cette Annonce n'existe pas!<br />\n";		
	}
		
	echo "		<p><h3><a href=\"?p=annonces\">Retour aux annonces</a></h3></p>\n";
}

echo "	</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "	<td class=\"newfin\">&nbsp;</td>\n";
echo "</tr>\n";
echo "</table>\n";
?>