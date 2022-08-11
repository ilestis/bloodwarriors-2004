<?php
//verifie si la session est en cours
require ('include/session_verif.php');

if(!isset($_GET['do']))
{
	bw_tableau_start("Les Scores");

	$info = "Les scores sont automatiquement mis à jour toutes les heures.";
	bw_f_info("Information", $info);
	?>
	<fieldset>
		<legend>Liste de classements</legend>
	
		<a href="index.php?p=scores&do=players">Par Joueur</a><br />
		<a href="index.php?p=scores&do=provinces">Par Provinces</a><br />
		<a href="index.php?p=scores&do=ally">Par Alliance</a><br />
		<a href="index.php?p=scores&do=class">Par Race</a><br /><br />

		<a href="index.php?p=scores&do=stats">Stats Générales</a>
	</fieldset>
	<?php
	bw_tableau_end();
}
elseif($_GET['do'] == 'players')
{//voir les joueurs
	require('./public/ScoresHeros.html');
	
} 
elseif($_GET['do'] == 'provinces')
{///voir les alliances
	require('./public/ScoresProvinces.html');

} 
elseif($_GET['do'] == 'ally')
{///voir les alliances
	require('./public/ScoresAlliances.html');

}
elseif($_GET['do'] == 'class')
{//voir les classes
	require('./public/ScoresRaces.html');
}
elseif($_GET['do'] == 'stats')
{//voir les classes
	require('./public/Statistiques.php');
}
elseif($_GET['do'] == 'allyview')
{//voir une alliance
	//verifie si elle existe
	$sql = "SELECT * FROM alliances WHERE ally_id = '".clean($_GET['id'])."'";
	$req = mysql_query($sql);
	$resultat = mysql_num_rows($req);

	if($resultat == 0)
	{
		echo 'Cette alliance n\'existe pas!<br />';
		breakpage();
	}
	//si on est la c'est que c'est bon!
	$res = mysql_fetch_array($req);

	bw_tableau_start("L'alliance ".$res['name']);

	//nombre de membres
	$ss = sql_query("SELECT pseudo FROM joueurs WHERE `ally_id` = '".$res['ally_id']."'");
	$nombres = mysql_num_rows($ss);

	//tableau
	?>
	<img src="<?php echo $res['image']; ?>" />

	<table class="newsmalltable">
		<tr>
			<th colspan="2">Informations sur l'alliance <?php echo $res['name']; ?></th>
		</tr><tr>
			<td width="30%" valign="top">
			<strong>Nom de l'alliance:</strong><br />&nbsp;&nbsp; <?php echo affiche($res['name']); ?><br/>
			<strong>Nombre de membres:</strong><br />&nbsp;&nbsp;  <?php echo $nombres; ?><br />
			<strong>Chef(s) de l'alliance:</strong><br />
			<?php
				$chefs = "SELECT pseudo FROM joueurs WHERE ally_id = '".$res['ally_id']."' AND ally_lvl = '5'";
				$chefq = sql_query($chefs);
				while($chefr = mysql_fetch_array($chefq))
				{
					echo "&nbsp;&nbsp;".$chefr['pseudo']."<br />\n";
				}
			?>
			<td class="in"><?php echo affiche($res['description']); ?>&nbsp;</td>
		</tr>
	</table>


		<h3>Membres de l'alliance</h3>

		<table class="newsmalltable">
		<tr>
			<th>Pseudo</th>
			<th>Race</th>
			<th>Puissance</th>
		</tr>
		<?php
		$membres_sql = sql_query("SELECT pseudo, race, puissance, ally_lvl FROM joueurs WHERE ally_id = '".$res['ally_id']."' ORDER BY puissance DESC");
		while($res = mysql_fetch_array($membres_sql))
		{
			echo "<tr>\n";
			echo "<td class=\"in\">";
			if($res['ally_lvl'] == 5) echo "<img src=\"images/president.png\" title=\"Président d'alliance\" /> ";
			echo $res['pseudo']."</td>\n";
			echo "<td class=\"in\">".return_guilde($res['race'], $Joueur->lang)."</td>\n";
			echo "<td class=\"in\">".$res['puissance']."</td>\n";
			echo "</tr>\n";
		}
		echo "</table>";

	//bw_tableau_end();
	bw_tableau_end();
}
?>