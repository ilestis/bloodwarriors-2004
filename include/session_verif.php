<?php
//Fichier qui gère l'affichage si la session n'est pas en cours
//Affiche les news et la fin du code html.

if (session_is_registered("id_joueur") == false)
{//La session n'est pas active
	require ('./annonces.php');
	require ('./footer.php');
	exit;
}
elseif(($CONF['game_status'] == 1) && $_SESSION['aut'][13] != 1)
{
	echo "<H2>Blood Warriors est actuellement en maintenance.</H2>\n";
	require ('./footer.php');
	exit;
}
elseif(!defined('IN_GAME')) 
{
	echo "<H2>HACKING</H2>\n";
	require ('./footer.php');
	exit;

}
?>