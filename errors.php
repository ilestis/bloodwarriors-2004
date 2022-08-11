<?php
require ('include/session_verif.php');

if(isset($_GET['do']))
{
	$do = clean($_GET['do']);

	if($do == 'add')
	{
		if(isset($_POST['message']))
		{
			$Mes = clean($_POST['message']);
			$ins = "INSERT INTO messages SET id_from = '".$_SESSION['id_joueur']."', time = '".time()."', location = 'bug', expiration = '1', message = '".$Mes."';";
			sql_query($ins);
		}
	} elseif($do == 'up0') {
		if($_SESSION['aut'][13] == 1) {
			$id = clean($_GET['id']);
			$up = "UPDATE messages SET expiration = '2' WHERE id_message = '".$id."';";
			sql_query($up);
		}
	} elseif($do == 'up1' || $do == 'up2') {
		if($_SESSION['aut'][13] == 1) {
			$id = clean($_GET['id']);
			$up = "UPDATE messages SET expiration = '0' WHERE id_message = '".$id."';";
			sql_query($up);
		}
	} elseif($do == 'del') {
		if($_SESSION['aut'][13] == 1) {
			$id = clean($_GET['id']);
			$up = "DELETE FROM messages WHERE id_message = '".$id."';";
			sql_query($up);
		}
	}
}


bw_tableau_start("BUGS");
	bw_f_start("Insérer un bug", "icons/btn_bug_add.png");
	echo "<form method=\"post\" action=\"?p=errors&do=add\">\n";
	echo "<textarea name=\"message\" rows=\"2\" style=\"width:100%;\"></textarea><br />Soyez précit et clair s'il-vous-plait, en indiquant le maximum d'information utile! ".bw_submit("Ajouter");
	echo "</form>\n";
	bw_f_end();

	bw_f_start("Liste des bugs");
	echo "<table class=\"newsmalltable\">\n";
	echo "<tr>\n";
	echo "	<th width=\"20px\">ST</th><th width=\"140px\">Quand</th><th>Qui</th><th>Bug</th>";
	if($_SESSION['aut'][13] == 1)
		echo "<th>Action</th>";
	echo "\n";
	echo "</tr>\n";

	$arrayNum = array('0', '1', '2');
	$arrayText = array('0' => "En Cours", '1' => "Nouveau", '2' => 'Corrigé');

	$sql = "SELECT id_message, id_from, message, time, expiration FROM messages WHERE location = 'bug' ORDER BY expiration ASC, `time` ASC;";
	$req = sql_query($sql);
	$oldloc = '';
	while($res = sql_object($req))
	{
		if($oldloc != $res->expiration) {
			echo "<tr><td colspan=\"".($_SESSION['aut'][13] == 1 ? '5' : '4')."\"><strong>".
				(in_array($res->expiration, $arrayNum) ? $arrayText[$res->expiration] : $arrayText['2']).
				"</strong></td></tr>\n";
			$oldloc = $res->expiration;
		}
		echo "<tr>\n";
		echo "	<td valign=\"top\">";
		switch($res->expiration)
		{
			case 1: $img = 'btn_bug_new.png'; $title = 'Nouveau'; break;
			case 0: $img = 'btn_bug_ow.png'; $title = 'En Cours'; break;
			default: $img = 'btn_bug_ok.png'; $title = 'Corrigé'; break;
		}
		echo bw_icon($img, $title)."</td>\n";

		echo "	<td valign=\"top\">".date($CONF['game_timeformat'], $res->time)."</td>\n";
		echo "	<td valign=\"top\">".joueur_name($res->id_from)."</td>\n";
		echo "	<td valign=\"top\">".nl2br($res->message)."</td>\n";
		if($_SESSION['aut'][13] == 1)
		{
			echo "<td valign=\"top\"><a href=\"?p=errors&do=up".$res->expiration."&id=".$res->id_message."\">".bw_icon("btn_bug_up.png", "Prochaine étape")."</a> <a href=\"?p=errors&do=del&id=".$res->id_message."\">".bw_icon("btn_delete2.png", "Supprimer")."</a></td>\n";
		}
		echo "</tr>\n";
	}
	echo "</table>\n";

	echo bw_icon('btn_bug_new.png').": Nouveau | ";
	echo bw_icon('btn_bug_ow.png').": En Cours | ";
	echo bw_icon('btn_bug_ok.png').": Terminé<br />\n";
	bw_f_end();
bw_tableau_end();
?>