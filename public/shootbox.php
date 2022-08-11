<?php
//shootbox

//on ajoute si necessaire
if (isset($_POST['messageshootbox']) AND isset($_SESSION['id_joueur'])) {//Y'a quelque chose
	$Text = clean($_POST['messageshootbox']);

	$shootbox_insert = "INSERT INTO messages SET id_from = '".$_SESSION['id_joueur']."', message = '".$Text."', time = '".time()."', location = 'box'";
	sql_query($shootbox_insert);

}
if (isset($_GET['deleteid'])) {//On demande de supprimer
	if(isset($_SESSION['id_joueur'])) {//connecté
		if($_SESSION['aut'][2] == 1) {//on peut delete
			$delete = "DELETE FROM messages WHERE numero_message_prive = '".clean($_GET['deleteid'])."' AND location = 'box'";
			sql_query($delete);
		}
	}
}

//on sélectionne les 15 derniers messages et supprime les vieux

bw_tableau_start("Shootbox");

//bw_fieldset("Information", "La shootbox n'affiche que 15 messages au maximum.");

echo "<table class=\"shootbox\"><tr><td style=\"text-align: left;\">\n";
$compteur = 0;
$sql = "SELECT `id_message`, `message`, `id_from`, `time` FROM messages WHERE location = 'box' ORDER BY id_message DESC";
$req = sql_query($sql);
while ($res = mysql_fetch_array($req))
{
	$compteur++;
	
	if ($compteur > 15) {//Supprime
		$del = "DELETE FROM messages WHERE id_message = '".$res['id_message']."'";
		sql_query($del);
	}
	else {//On affiche
		if (isset($_SESSION['id_joueur'])) {//Connecté
			if ($Joueur->acceslvl >= 3) {//peut supprimer
				echo "<a href=\"?p=shootbox&deleteid=".$res['id_message']."\"><img src=\"images/delete.png\"></a> ";
			}
		}
		//Pseudo
		$sqlp = "SELECT pseudo FROM joueurs WHERE id = '".$res['id_from']."'";
		$reqp = mysql_query($sqlp);
		$resp = mysql_fetch_array($reqp);
		echo affiche("[".date("G:i:s", $res['time'])."] <strong>".$resp['pseudo']."</strong>: ".affiche($res['message']))."<br />\n";
		
	}
	
}
if ($compteur == 0) echo "<em>La shootbox est vide</em>";
echo "</td></tr></table><br />\n";

//Ajouter
if(isset($_SESSION['id_joueur'])) {//On peut ajouter
	if($_SESSION['aut'][14] == 1)
	{
		$Message = "	<form method=\"POST\" action=\"?p=shootbox\">\n";
		$Message .= "		<input type=\"TEXT\" name=\"messageshootbox\" size=\"70\" maxlength=\"150\" />\n";
		$Message .= "		<input type=\"SUBMIT\" value=\"Envoyer\" /><br />\n";
		$Message .= "		<em>Vous ne pouvez écrire plus de 150 caractères par contribution.</em><br />\n";
		$Message .= bw_info("Comme pour le forum, il faut soigner votre orthographe sur la shootbox et rester poli. Tout joueur ne respectant pas ces simples règles se vera puni.");
		$Message .= "	</form>\n";
	} else {
		$Message = bw_error("Vous n'avez pas les droits pour poster sur la shootbox");
	}
	bw_fieldset("Contribuer", $Message);
}

bw_tableau_end();
?>