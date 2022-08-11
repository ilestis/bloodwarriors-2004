<?php
//verifie si la session est en cours
include ('adminheader.php');


$need_admin_lvl = 8; 
if($Joueur->acceslvl >= 8)
{
	?>
	<table class="newsmalltable">
	<tr>
		<th>Variables de la partie</th>
	</tr>
	<tr>
		<td>
			<table width="100%">
			<tr>
				<th>Nom de la partie</td>
				<th>Etat</td>
				<th>Changer</td>
			</tr>
			<tr>
				<form method="POST" action="index.php?p=admin_variable_saison&do=up">
					<td><input type="text" name="nompartie" value="<? echo $CONF['game_echo'];?>"></td>
					<td>
						<select name="etatpartie">
							<option value="0" <?php if ($CONF['game_status'] == 0) echo "selected"; ?>>Ouvert</option>
							<option value="1" <?php if ($CONF['game_status'] == 1) echo "selected"; ?>>Maintenance</option>
							<option value="2" <?php if ($CONF['game_status'] == 0) echo "selected"; ?>>Fermer</option>
						</select>
					</td>
					<td><input type="submit" value="Valider"></td>
				</form>
			</tr>
			</table>
		</td>
	</tr>
	<? //temps depuis le début de la saison
	$time = time();
	$ecroule = $time - $CONF['game_time_start'];
	$jours = floor($ecroule/(3600*24));
	$heures = floor($ecroule/3600);
	$minutes = floor($ecroule/60);
	while ($minutes > 60)
	{//corrige les minutes pour par avoir: 560 minutes
		$minutes -= 60;
	}
	while ($heures > 24)
	{//corrige les heures
		$heures -= 24;
	}
	?>
	<tr>
		<th>Temps Écoulé (arrondi à l'inférieur)</th>
	</tr>
	<tr>
		<td>
			<table width="100%">
			<tr>
				<th>Début</th>
				<th>Jours</th>
				<th>Heures</th>
				<th>Minutes</th>
			</tr>
			<tr>
				<td><?php echo $CONF['game_time_start'].'<br />'.$CONF['game_date_start']; ?></td>
				<td><?php echo $jours; ?></td>
				<td><?php echo $heures; ?></td>
				<td><?php echo $minutes; ?></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>

	<a href="?p=admin_admin">Retour à la page Admin</a>
	<?php
}