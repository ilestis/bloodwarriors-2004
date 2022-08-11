<?php
//inclu les fichiers importants
include '../include/fonction.php';			//Fonctions disponibles
include '../include/function_mef.php';		//Mise en forme
include '../include/variables.inc.php';		//Variables
include '../connect.php';

echo "Score du PingPong<br />";

$sql = "SELECT pseudo FROM joueurs ORDER BY pseudo ASC";
$req = sql_query($sql);
while($res = mysql_fetch_array($req))
{
	//Compte le nombre de poste dans le topic ping pong
	$sql2 = "SELECT message_id FROM forum_messages WHERE subject_id = '5' AND message_poster = '".$res['pseudo']."'";
	$req2 = sql_query($sql2);
	$nbr = mysql_num_rows($req2);
	
	if($nbr > 0)
	{
		echo "<strong>".$res['pseudo']."</strong>:".$nbr."<br />";
	}
}
?>