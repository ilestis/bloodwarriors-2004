<?php
//header
include ('adminheader.php');

if(!isset($_POST['radio'])) $Select = '';
else $Select = clean($_POST['radio']);
if(!isset($_GET['go'])) $Go = 'nul';
else $Go = clean($_GET['go']);
$need_admin_lvl = 3; 


if($Go == "messages") //Selection les messages privés a administrer
{	
	echo "<H2>Administrer les messages privés</h2>\n";

	if (isset($_GET['id']) && is_numeric($_GET['id'])) {//On supprime un message
		//Verifie s'il existe
		$sql = "SELECT id_message FROM `messages` WHERE id_message = '".clean($_GET['id'])."' AND location = 'a_m'";
		$req = sql_query($sql);
		$nbr = mysql_num_rows($req);
		if ($nbr == 1) {//existe
			$res = mysql_fetch_array($req);
			//Variables
			$Warning = "Langague grossier";
			
			$Del = "DELETE FROM messages WHERE id_message = '".$res['id_message']."'";
			sql_query($Del);
			echo "Message supprimé.\n";

			if (clean($_GET['warn']) == 'yes') {//Warning
				ajout_warning($Joueur->pseudo, $res['id_from'], $Warning);
				echo " Warning ajouté!\n";
			}
			echo "<br />\n";
		} else {//Existe pas
			echo "Ce message n'a pas été trouvé dans la base de données.<br />\n";
		}
	}
	$sql = "SELECT * FROM messages WHERE location = 'a_m' ORDER BY id_message DESC" ;
	$req = sql_query($sql);
	$Nbr = mysql_num_rows($req);
	if ($Nbr == 0) echo "Il y a 0 message à administrer.<br />\n";
	else echo "Il y a ".$Nbr." message".pluriel($Nbr, 's')." à administrer.<br />\n";
	while($res = mysql_fetch_array($req))
	{//Cherche les différentes entrées
		echo "		<table class=\"newsmalltable\">\n";
		echo "		<tr>\n";
		echo "			<th width=\"35%\">De: ".joueur_name($res["id_from"])."</th>\n";
		echo "			<th width=\"35%\">À: ".joueur_name($res["id_to"])."</th>\n";
		echo "			<th width=\"30%\"><a href=\"index.php?p=admin_administration&go=messages&id=".$res['id_message']."\">Ok</a> / <a href=\"index.php?p=admin_administration&go=messages&id=".$res['id_message']."&warn=yes\">Warning</a></th>\n";
		echo "		</tr>\n";
		echo "			<td colspan=\"3\">\n";
		echo "			".date($CONF['game_timeformat'], $res['time']).":<br />".nl2br($res["message"])."\n";
		echo "			</td>\n";
		echo "		</tr>\n";
		echo "		</table>\n";
		echo "		<br />\n";
	}//fin while
}//fin if go


elseif($Go == "forums")  //Selection les messages des forums a administrer
{
	echo "<H2>Administrer les posts des forums</h2>\n";

	if (isset($_GET['id']) && is_numeric($_GET['id'])) {//On supprime un message
		//Verifie s'il existe
		$sql = "SELECT * FROM `messages` WHERE id_message = '".clean($_GET['id'])."'";
		$req = sql_query($sql);
		$nbr = mysql_num_rows($req);
		if ($nbr == 1) {//existe
			$res = mysql_fetch_array($req);
			//Variables
			$De = $res['id_from'];
			$Warning = "Langague grossier";
			$Mes_Id = $res['id_message'];
			
			$Del = "DELETE FROM messages WHERE id_message = '".$Mes_Id."'";
			sql_query($Del);
			echo "Message supprimé.\n";

			if ($_GET['warn'] == 'yes') {//Warning
				ajout_warning($Joueur->pseudo, $De, $Warning);
				echo " Warning ajouté!\n";
			}
			echo "<br />\n";
		} else {//Existe pas
			echo "Ce message n'a pas été trouvé dans la base de données.<br />\n";
		}
	}
	$sql = "SELECT * FROM `messages` WHERE location = 'a_f' ORDER BY id_message DESC" ;
	$req = sql_query($sql);
	$Nbr = mysql_num_rows($req);
	if ($Nbr == 0) echo "Il y a 0 message à administrer.<br />\n";
	else echo "Il y a ".$Nbr." messages".pluriel($Nbr, 's')." à administrer.<br />\n";
	while($res = mysql_fetch_array($req))
	{//Cherche les différentes entrées

		
		?>
		<table class="newsmalltable">
		<tr> 
			<th width="35%">De: <? echo joueur_name($res["id"]); ?></th>
			<th width="35%">Dans: Forum</th>
			<th width="30%"><a href="index.php?p=admin_administration&go=forums&id=<?php echo $res['id_message']; ?>">Ok</a> / <a href="index.php?p=admin_administration&go=forums&id=<?php echo $res['id_message']; ?>&warn=yes">Warning</a></th>
		</tr>
			<td valign="top">Date: <?php echo date($CONF['game_timeformat'], $res['time']); ?></td>
			<td colspan="2">
				<?php echo nl2br($res["message"]); ?>
			</td>
		</tr>
		</table>
		<br />
		<?php
	}//End While
}//fin if go

elseif ($Go == 'charte') {//on demande de visualisé la charte
	echo '<H2>Modifier la charte</H2><br/>';
	echo '<FORM METHOD=POST ACTION=index.php?p=admin_administration&go=upcharte>';
	echo '<TEXTAREA NAME=comment ROWS=7 COLS=40>';
	include 'charte.txt';
	echo '</TEXTAREA><br/><INPUT TYPE=submit value=Changer></FORM>';
}//fin if go charte

elseif ($Go == 'upcharte')
{ //on demande de mettre a jour la charte
	//remplace la charte par la nouvelle charte
	$fichier = 'charte.txt'; //le fichier qu'on renomme
	$comment = stripslashes($_POST['comment']); //la variable comment
	$operation = fopen($fichier, 'w');

	if(is_writable($fichier)) { //verifie si le document est accessible en écriture

		if(!$operation) { //si on arrive pas a le réduire a 0
			echo 'Impossible de réécrire sur le vieux fichier';
			exit;
		}

		if(fwrite($operation, $comment) == FALSE) { //on arrive pas a écrire dans le fichier
			echo 'Impossible d\'écrire dans le fichier '.$fichier.'<br/>';
			exit;
		}

		echo 'La réécriture de la charte a réussi!<br/>';

		fclose($operation);
	}
	else {
		echo 'Vous n\'avez pas le droit de réécrire sur ce fichier!<br/>';
	}
}//fin if go upcharte

elseif ($Go == 'selection') {//on demande de visualisé la FAQ/règles
	echo '<H2>Selectionnez le fichier à modifier:</H2><br />';
	echo '<FORM METHOD=POST ACTION=index.php?p=admin_administration>';
	echo '<INPUT TYPE=radio NAME=radio value=faq>La FAQ<br/>';
	echo '<INPUT TYPE=radio NAME=radio value=regle>Les Règles<br/>';
	echo '<INPUT TYPE=submit value=Visualiser>';
	echo '</FORM>';
}//fin if go selection

if ($Select == 'faq') {//on demande de visualisé la FAQ
	echo '<H2>Modifier la FAQ:</H2><br />';
	echo '<FORM METHOD=POST ACTION=index.php?p=admin_administration&go=upfaq>';
	echo '<TEXTAREA NAME=comment ROWS=30 COLS=90>';
	include 'admin/faq.txt';
	echo '</TEXTAREA><br/><INPUT TYPE=submit value=Mettre à jour></FORM>';
	}//fin if selection faq

elseif ($Select == 'regle') {//on demande de visualisé les règles
	echo '<H2>Modifier les Règles:</H2><br />';
	echo 'Pas encore en place!<br/>';
}//fin if selection règles

if ($Go == 'upfaq') {//on demande de mettre à jour la FAQ
	//remplace la faq par la nouvelle faq
	$fichier = 'admin/faq.txt'; //le fichier qu'on renomme
	$comment = stripslashes($_POST['comment']); //la variable comment
	$operation = fopen($fichier, 'w');

	if(is_writable($fichier)) { //verifie si le document est accessible en écriture

		if(!$operation) { //si on arrive pas a le réduire a 0
			echo 'Impossible de réécrire sur le vieux fichier';
			exit;
		}

		if(fwrite($operation, $comment) == FALSE) { //on arrive pas a écrire dans le fichier
			echo 'Impossible d\'écrire dans le fichier '.$fichier.'<br/>';
			exit;
		}

		echo 'La réécriture de la faq a réussi!<br/>';
		echo '<a href="index.php?p=admin_administration&go=selection">FAQ/Règles</a><br/>';

		fclose($operation);
	}
	else {
		echo 'Vous n\'avez pas le droit de réécrire sur ce fichier!<br/>';
	}
}//fin if go upfaq

/*if($Go == 'modmessfor')
{
	$sql = "DELETE FROM `admin_messages` WHERE id_message = '".$id_message."'" ;
	mysql_query($sql) ;
	echo 'Message bien supprimé!<br/><br>';
}*/