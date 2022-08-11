<?php
//verifie si la session est en cours
if (session_is_registered("login") == false)
{
	include 'news.php';
	exit;
}
$need_admin_lvl = 8; 
if($Sorcier->acceslvl >= 8)
{
	if ($id == "etat")
	{
		$sql = "UPDATE `configuration` SET `game_status` = '".$etatchange."'";
		mysql_query($sql);
		if ($etatchange == 1) echo 'La partie est maintenant ouverte!<br/>';
		elseif ($etatchange == 0) echo 'La partie est maintenant en maintenance!<br/>';

		if($Config['game_status'] == 2 AND $etatchange == 0)
		{//une nouvelle partie
			$now = time();
			$sql = "UPDATE `configuration` SET `game_time_start` = '".$now."'";
			mysql_query($sql);
		}
		journal_admin($Sorcier->pseudo, 'État de la partie passé à '.$etatchange, date("d/m/Y G:i"), time());
	}

}