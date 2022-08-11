<?php
/*----------------------[TABLEAU]---------------------
|Nom:			AllianceMod.php
+-----------------------------------------------------
|Description:	Tout ce qui est gestion d'une alliance
+-----------------------------------------------------
|Date de création:				17/03/05
|Dernière modification:			07.02.06
+---------------------------------------------------*/
//verifie la session
require ('./include/session_verif.php');

bw_tableau_start("Administration de l'alliance");

//verifie si nous sommes chef
if($Joueur->ally_id == 0)
{	
	$info = bw_error("Il faut être faire partie d'une alliance et en être chef pour accéder à cette section!");
}
if($Joueur->ally_lvl < 4)
{//rien a faire ici
	$info = bw_error("Vous n'avez pas les accès suffisant pour vous trouver ici!");
}

if(isset($info))
{
	bw_fieldset("Erreur", $info);
	bw_tableau_end();
	breakpage();
	die();
}

if(isset($_GET['do']))
{
	switch (clean($_GET['do']))
	{
		case 'do_ask':
			//on accept ou suprime le nouveau mec
			//prend les valeurs
			$val = "SELECT a.*, b.name FROM `temp_alliances` AS a LEFT JOIN alliances AS b ON b.ally_id = a.ally_id WHERE a.id = '".clean($_GET['id'])."'";
			$req = sql_query($val);
			$res = mysql_fetch_array($req);

			if($res['ally_id'] == $Joueur->ally_id)
			{//pas de triche
				if(clean($_GET['to']) == 1)
				{//on accepte
					$up = "UPDATE joueurs SET ally_id = '".$Joueur->ally_id."', ally_lvl = '1' WHERE id = '".$res['joueur_id']."'";
					sql_query($up);
					
					$Message =  'Le joueur '.$res['joueur_p'].' à été accepté dans votre alliance!';

					//supprime toutes les demandes
					$del = "DELETE FROM `temp_alliances` WHERE id = '".$res['id']."'";
					sql_query($del);

					//Message comme quoi on a été accepté
					send_message('999999996', $res['joueur_id'], "Monseigneur,<br /><br />Nous avons reçu une réponse positive de la part de l\'alliance ".$res['name']."!<br />");
				}
				elseif(clean($_GET['to']) == 2)
				{//refuse
					$Message =  'Le joueur '.$res['joueur_p'].' à été refusé de votre alliance!';
					
					//suprime la demande pour notre alliance
					$del = "DELETE FROM `temp_alliances` WHERE `id` = '".$res['id']."'";
					sql_query($del);

					//Message comme quoi on a été refusé
					send_message('999999996', $res['joueur_id'], "Monseigneur,<br /><br />Nous avons reçu une réponse négative de la part de l\'alliance ".$res['name'].".<br />");
				}
			}
			else
			{
				$Message =  bw_error('Ce joueur ne s\'est pas proposé à votre alliance!');
			}
			break;

		case 'update':
			//met à jour l'images, le message de news et la déscription
			$news = clean(forumadd($_POST['news']));
			$description = clean(forumadd($_POST['description']));
			$image = clean($_POST['image_url']);

			//update
			$sql = "UPDATE `alliances` SET `description` = '".$description."', `news` = '".$news."', `image` = '".$image."' WHERE ally_id = '".$Joueur->ally_id."'";
			sql_query($sql);

			$Message = 'Données bien mises à jour!<br /> ';

			break;

		case 'memberkick':
			//virer un membre
			//verifie s'il est dans notre alliance
			$sql = "SELECT ally_id, ally_lvl, pseudo, id FROM `joueurs` WHERE id = '".clean($_GET['id'])."'";
			$req = sql_query($sql);
			$res = mysql_fetch_array($req);
			if($res['ally_id'] == $Joueur->ally_id && $res['ally_lvl'] < 5)
			{//ok
				//supprime
				$up = "UPDATE joueurs SET ally_id = '0', ally_lvl = '0' WHERE `id` = '".$res['id']."'";
				sql_query($up);

				$Message = 'Le joueur '.$res['pseudo'].' a été supprimé de l\'alliance!';
				
				send_message('999999996', $res['id'], "Monseigneur,<br /><br />Vous avez été renvoyé de l\'alliance!<br />");
			}
			else
			{
				$Message = 'Vous ne pouvez pas supprimer ce joueur! Soit il n\'est pas dans votre alliance, soit il est chef.';
			}
				
			break;

		case 'delete':
			bw_f_start("Supprimer l'alliance");
			echo "
			<form method=\"post\" action=\"?p=ally_admin&do=realydelete\">
				<p>Voulez-vous vraiment supprimer l'alliance?</p>
				<input type=\"submit\" value=\"Oui\" />
			</form>";
			bw_f_end();
		break;

		case 'realydelete':
			//supprime l'alliance
			//supprime les membres
			if($Joueur->ally_lvl == 5)
			{
				//supprime l'alliance, les membres et le journal
				$up = "UPDATE joueurs SET ally_lvl = '0', ally_id = '0' WHERE ally_id = '".$Joueur->ally_id."'";
				$del = "DELETE FROM alliances WHERE ally_id = '".$Joueur->ally_id."'";

				sql_query($up);
				sql_query($del);

				// Supprime les messages
				$ThemeID = '11660'.$Joueur->ally_id;
				$sql = "SELECT subject_id FROM forum_subjects WHERE theme_id = '".$ThemeID."'";
				$req = sql_query($sql);
				while($res = sql_array($req)) {
					$del = "DELETE FROM forum_messages WHERE id_subject = '".$res['subject_id']."'";
					sql_query($del);

					$del = "DELETE FROM forum_visiting WHERE subject_id = '".$res['subject_id']."'";
					sql_query($del);

					$del = "DELETE FROM forum_subjects WHERE subject_id = '".$res['subject_id']."'";
					sql_query($del);
				}

				echo "<h1>Votre alliance a bien été supprimée!</h1>";
				bw_tableau_end();
				require ('./footer.php');
			}
			break;

		case 'membres':
			//mettre à jour le niveau des joueurs
			//verifie s'il est dans notre alliance
			$sql = "SELECT ally_id, id, pseudo FROM joueurs WHERE id = '".clean($_GET['id'])."'";
			$req = sql_query($sql);
			$res = mysql_fetch_array($req);
			if($res['ally_id'] == $Joueur->ally_id)
			{//ok
				//Si on le met lvl 5, on n'est plus chef et passe lvl 4.
				if(($_POST['level'] == '5') && ($Joueur->ally_lvl == 5))
				{
					$UP = "UPDATE joueurs SET ally_lvl = '4' WHERE id = '".$Joueur->ally_id."'";
					sql_query($UP);
				}
				//Update
				$up = "UPDATE joueurs SET ally_lvl = '".clean($_POST['level'])."' WHERE id = '".$res['id']."'";
				sql_query($up);

				$Message = bw_info('Le niveau du joueur à bien été mis à jour.');
			}
			else
			{
				$Message = bw_error('Vous ne pouvez pas supprimer ce joueur! Soit il n\'est pas dans votre alliance, soit il est admin.');
			}
			break;
	}
}
if(isset($Message)) {
	bw_f_info("Information", $Message);
}
bw_f_start("Adhésions");
//demande le choix

//---------------------verifie s'il y a des demandes
$sql = "SELECT * FROM `temp_alliances` WHERE `ally_id` = '".$Joueur->ally_id."'";
$req = sql_query($sql);
$nombres = mysql_num_rows($req);

if($nombres > 0)
{//y'a de la demande in the air
	echo '<table class="newsmalltable">';
	while($res = mysql_fetch_array($req))
	{?>
		<tr>
			<th><?php echo $res['joueur_p'].'['.date($CONF['game_timeformat'], $res['time']).']'; ?></th>
			<td>
			<a href="index.php?p=ally_admin&do=do_ask&id=<?php echo $res['id']; ?>&to=1">Accepter</a> - <a href="index.php?p=ally_admin&do=do_ask&id=<?php echo $res['id']; ?>&to=2">Refuser</a></td>
		</tr>
		<tr>
			<td colspan="2"><?php echo affiche($res['comment']); ?></td>
		</tr>
	<?}
	echo '</table><br />';
}
else
{	
	echo bw_info('Il n\'y a aucune demande d\'adhésion.');	
}
echo "</fieldset><br />\n";


bw_f_start("Messages, Annonces, etc...");

//-----------------gestion
//sql
$sql = "SELECT * FROM `alliances` WHERE `ally_id` = '".$Joueur->ally_id."'";
$req = mysql_query($sql);
$alliance = mysql_fetch_array($req);

echo "<table class=\"newsmalltable\">\n";
echo '<tr><th colspan=2"">News</th></tr><tr>';
//changer le message des news et de descriptions, ainsi que l'url
echo '<FORM METHOD=POST ACTION="index.php?p=ally_admin&do=update">';
echo '<th>Adresse de l\'image</td>';
echo '<td><INPUT TYPE="text" size="67" NAME="image_url" value="'.$alliance['image'].'"></td></tr>'; //value="'..'"

echo '<tr><th>Déscriptions</th>';
echo '<td><TEXTAREA NAME="description" ROWS=7 COLS=50>'.reversemessage($alliance['description']).'</TEXTAREA></td></tr>';

echo '<tr><th>Message de news</th>';
echo '<td><TEXTAREA NAME="news" ROWS=7 COLS=50>'.reversemessage($alliance['news']).'</TEXTAREA></td></tr>';

echo '<tr><th colspan="2"><INPUT TYPE="submit" value="Mettre à jour"></th>';
echo '</FORM>';
echo '</tr>';
echo '</table>';
echo "</fieldset>\n";
echo "<br />";


echo "<fieldset><legend>Gestion des membres</legend>\n";
echo "<table class=\"newsmalltable\">\n";
echo '<tr><th>Pseudo</th><th>Accès</th><th>Action</th></tr>';
$ss = "SELECT pseudo, ally_lvl, id FROM joueurs WHERE ally_id = '".$Joueur->ally_id."' ORDER BY pseudo ASC";
$pp = sql_query($ss);
while($jur = mysql_fetch_array($pp))
{
	//pour chaque joueur, on fait un new tableau
	echo '	<tr>';
	echo '		<FORM METHOD=POST ACTION="index.php?p=ally_admin&do=membres&id='.$jur['id'].'">';
	echo '		<td>'.$jur['pseudo'].'</td>';
	//niveau d'acces
	echo '		<td>';
	
	$levelmax = 4;
	if($Joueur->ally_lvl == 5) $levelmax = 5;

	$ArrayGrade = array(1 => 'Membre', 4 => 'Sous-chef', 5 => 'Chef');
	echo '		<select name="level">';
	for($i = 1; $i <= $levelmax; $i++)
	{
		$i = ($i == 2 ? $i+2 : $i);
		echo '<option value="'.$i.'" ';
		if($jur['ally_lvl'] == $i) echo 'selected';
		echo '>'.$ArrayGrade[$i].'</option>' ;
	}
	echo '		</select>';
	echo '		<INPUT TYPE="submit" value="Mettre à jour"></td>';
	echo '		<td><a href="index.php?p=ally_admin&do=memberkick&id='.$jur['id'].'">Renvoyer</a></form></td>';
	echo '	</tr>';
}
echo '	</table>';
echo "</fieldset><br />\n";

if($Joueur->ally_lvl == 5) {
	echo "<fieldset><legend>Supprimer l'alliance</legend>\n";
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=ally_admin&do=delete\"><INPUT TYPE=\"submit\" value=\"Supprimer l'alliance! Action irréversible!\"></form></fieldset>\n";
}
bw_tableau_end();
?>