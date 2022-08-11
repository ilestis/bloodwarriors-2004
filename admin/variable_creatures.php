<?php
include ('admin/adminheader.php');

if($_SESSION['aut'][$adminpage['variable_newsadmin']] == 0) exit;

bw_tableau_start("Unités");

if(!isset($var))
{?>
	<table class="newsmalltable">
	<tr>
		<th>Nom</th>
		<th>Race</th>
		<th>Prix</th>
		<th>Bati</th>
		<th>Stats</th>
		<th>Entretien</th>
		<th>MAJ</th>
	</tr>
	<?php
	$sql = "SELECT * FROM `liste_invocations` ORDER BY ID ASC";
	$req = sql_query($sql);
	while ($res = mysql_fetch_array($req))
	{//Prend chaque unités
		//Variables
		$ID = $res['ID'];
		$Nom = $res['nom'];
		$Race = $res['royaume'];
		$Prix_gold = $res['cost_gold'];
		$Prix_food = $res['cost_food'];
		$Prix_craft = $res['cost_craft'];
		$Need_Bati = $res['need_bulding'];
		$Need_Bati_Lvl = $res['need_bulding_lvl'];
		$Need_Bati_Id = $res['need_bulding_id'];
		$Power_1 = $res['power_1'];
		$Power_2 = $res['power_2'];
		$Power_3 = $res['power_3'];
		$Power_4 = $res['power_4'];
		$Type = $res['type'];
		$Entretien = $res['entretient'];
		?>
		<tr>
			<form method="post" action="?p=admin_variable_crea&do=up&id=<?php echo $ID; ?>">
			<td>
				<INPUT TYPE="text" NAME="nom" value="<?php echo $Nom; ?>" size="8">
			</td>
			<td>
				<select name="race">
					<option value="0" <?php if($Race == 0) echo "selected"; ?>>Tous</option>
					<option value="1" <?php if($Race == 1) echo "selected"; ?>>Anges</option>
					<option value="2" <?php if($Race == 2) echo "selected"; ?>>Barbares</option>
					<option value="3" <?php if($Race == 3) echo "selected"; ?>>Demons</option>
					<option value="4" <?php if($Race == 4) echo "selected"; ?>>Elfes</option>
					<option value="5" <?php if($Race == 5) echo "selected"; ?>>Rebelles</option>
					<option value="6" <?php if($Race == 6) echo "selected"; ?>>Sorciers</option>
				</select>
			</td>
			<td>
				Or: <INPUT TYPE="text" NAME="cost_gold" value="<?php echo $Prix_gold; ?>" size="3"><br />
				Nour: <INPUT TYPE="text" NAME="cost_food" value="<?php echo $Prix_food ?>" size="3"><br />
				Magie: <INPUT TYPE="text" NAME="cost_craft" value="<?php echo $Prix_craft; ?>" size="3"><br />
			</td>
			<td>
				Besoin:<select name="bati_need"><option value="0" <?php if($Need_Bati == 0) echo "selected"; ?>>Non</option><option value="1" <?php if($Need_Bati == 1) echo "selected"; ?>>Oui</option></select><br />
				Niveau:<select name="bati_need_lvl">
					<option value="0" <?php if($Need_Bati_Lvl == 0) echo "selected"; ?>>0</option
					<option value="1" <?php if($Need_Bati_Lvl == 1) echo "selected"; ?>>1</option>
					<option value="2" <?php if($Need_Bati_Lvl == 2) echo "selected"; ?>>2</option>
					<option value="3" <?php if($Need_Bati_Lvl == 3) echo "selected"; ?>>3</option>
					<option value="4" <?php if($Need_Bati_Lvl == 4) echo "selected"; ?>>4</option>
				</select><br />
				ID:<select name="bati_need_id">
					<?php
					for ($x = 0; $x <= 15; $x++) 
					{
						echo "<option value=\"".$x."\"";
						if ($Need_Bati_Id == $x) echo " selected";
						echo ">".$x."</option>";
					}?>
				</select>
			</td>
			<td>
				<INPUT TYPE="text" NAME="power_1" size="2" value="<?php echo $Power_1; ?>">
				<INPUT TYPE="text" NAME="power_2" size="2" value="<?php echo $Power_2; ?>"><br />
				<INPUT TYPE="text" NAME="power_3" size="2" value="<?php echo $Power_3; ?>">
				<INPUT TYPE="text" NAME="power_4" size="2" value="<?php echo $Power_4; ?>"><br />
				Type:<INPUT TYPE="text" NAME="type" size="1" value="<?php echo $Type; ?>">
			</td>
			<td>
				<INPUT TYPE="text" NAME="entretien" value="<?php echo $Entretien; ?>" size="3">
			</td>
			<td>
				<INPUT TYPE="submit" value="MAJ">
			</td>
			</form>
		</tr>
	<?php
	}
}
else
{
	//on botte le tout

	if($id == 'add')
	{ //ajouter
		echo 'Ajout de la créature<br />';
		$sql = "INSERT INTO `invocation` VALUES('','".$_POST['royaume']."',0,'".$_POST['nom']."','".$_POST['cost_food']."','".$_POST['cost_gold']."','".$_POST['cost_magic']."','".$_POST['need_bulding']."','".$_POST['need_bulding_lvl']."','".$_POST['need_bulding_id']."','".$_POST['commentaire']."', '".$_POST['power_1']."','".$_POST['power_2']."','".$_POST['power_3']."','".$_POST['power_4']."','')";
		sql_query($sql);
		echo 'L\'unité '.$_POST['name'].' à bien été ajoutée!<br/>';
	}
	elseif($id == 'modi')
	{//mettre à jour
		echo 'Mettre à jours<br />';
		$sql = "UPDATE `invocation` SET
			`royaume` =			'".$_POST['royaume']."',
			`nom` =				'".$_POST['nom']."',
			`cost_food` =		'".$_POST['cost_food']."',
			`cost_gold` =		'".$_POST['cost_gold']."',
			`cost_magic` =		'".$_POST['cost_magic']."',
			`need_bulding` =	'".$_POST['need_bulding']."',
			`need_bulding_lvl` = '".$_POST['need_bulding_lvl']."',
			`need_bulding_id` =	'".$_POST['need_bulding_id']."',
			`commentaire` =		'".$_POST['commentaire']."', 
			`power_1` =			'".$_POST['power_1']."',
			`power_2` =			'".$_POST['power_2']."',
			`power_3` =			'".$_POST['power_3']."',
			`power_4` =			'".$_POST['power_4']."'
		WHERE `ID` =			'".$_GET['idc']."'";
		sql_query($sql);
		echo 'Unité bien mise à jour.<br />';

		
	}//fin modife	
	echo '<a href="index.php?p=admin_variable_crea">Retour</a>';
}//fin botter
bw_tableau_end();