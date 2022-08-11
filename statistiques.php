<?php
/*
+---------------------
|Nom: Statistiques
+---------------------
|Description: Petite page de stats de notre joueur/province
+---------------------
|Date de création: Février 2006
|Date du premier test: 
|Dernière modification: Mai 2007
+---------------------
*/

//verifie l'état de la session
require ('./include/session_verif.php');

bw_tableau_start("Statistiques");

//bw_fieldset("Information", bw_info("En Construction!"));
//echo "<br />\n";

$sql = "SELECT 
	a.*, 
	b.valeur, 
	COUNT(c.id) AS NumUnites 
FROM 
	provinces AS a 
LEFT JOIN 
	info_cartes AS b ON b.x = a.x AND b.y = a.y 
LEFT JOIN 
	armees AS c ON c.id_province = a.id 
WHERE 
	a.id_joueur = '".$_SESSION['id_joueur']."' 
GROUP BY a.id";
//echo $sql;
$req = sql_query($sql);
while($res = sql_object($req))
{
	echo "<fieldset>\n";
	echo "<legend>Province".$res->name." [".$res->x.", ".$res->y."]</legend>\n";
	?>
	<table class="newsmalltable">
		<tr>
			<th colspan="2">Ressources & Info</th>
		</tr>
		<tr>
			<td valign="top">
				<strong>Nom</strong>: <?php echo $res->name; ?><br />
				<strong>Puissance</strong>: <?php echo $res->puissance ; ?><br />
				<strong>Victoires</strong>: <?php echo $res->victoires ; ?><br />
				<strong>Défaites</strong>: <?php echo $res->pertes ; ?><br />
				<strong>Unités</strong>: <?php 
					echo $res->NumUnites; 	?>
				<br />

				<strong>Type de terrain</strong>: <?php
					//Bonus Terrain
					echo bw_terrain_type($res->valeur);
				?>
				<br />

			</td>
			<td valign="top">
				<strong>Or</strong>:			<?php echo $res->gold; ?><br />
				<strong>Nourriture</strong>:	<?php echo $res->food; ?><br />
				<strong>Bois</strong>:			<?php echo $res->wood; ?><br />
				<strong>Pierre</strong>:		<?php echo $res->stone; ?><br />
				<strong>Magie</strong>:			<?php echo $res->craft; ?><br />
				<strong>Cases</strong>:			<?php echo $res->cases_usuable."/".$res->cases_notusuable."/".$res->cases_total; ?><br />
			</td>
		
		</tr>

		<tr>
			<th colspan="2">Guerres, Unités & Sorts</th>
		</tr>
		<tr>
			<td valign="top">
				<strong>Attaques en court</strong>: 
				<?php 
				//Notre nombre de guerres où on attaque
				$sql_war = "SELECT id_guerre FROM guerres WHERE att_pro_id = '".$res->id."'";
				$req_war = sql_query($sql_war);
				echo mysql_num_rows($req_war);
				?><br />

				<strong>Attaques subies en court</strong>: 
				<?php 
				//Notre nombre de guerres où on attaque
				$sql_war = "SELECT id_guerre FROM guerres WHERE def_pro_id = '".$res->id."'";
				$req_war = sql_query($sql_war);
				echo mysql_num_rows($req_war);
				?><br />
			</td>
			<td valign="top">
				<strong>Sorts en réserve</strong>:<br />

				<?php
				$sql_sort = "SELECT a.id_sort, a.`time`, b.nom FROM temp_sorts AS a LEFT JOIN liste_sorts AS b ON b.id = a.id_sort WHERE id_province = '".$res->id."' AND a.boost_id != 10 ORDER BY a.time";
				$req_sort = sql_query($sql_sort);
				while($res_sort = sql_object($req_sort))
				{
					echo "<strong>".bw_popup($res_sort->nom, 'sort', $res_sort->id_sort)."</strong>: Durée jusqu'au ".date($CONF['game_timeformat'], $res_sort->time).".<br />";
				}

				?>
			</td>
		
		</tr>
		<tr>
			<th colspan="2">Paysans & Bâtiments</th>
		</tr>
		<tr>
			<td valign="top">
			
				<strong>Paysans totaux</strong>:		<?php echo $res->peasant; ?><br />

				<strong>Paysans disponnibles</strong>: 
				<?php 
				//Paysans disponnibles
				$sql_dis = "SELECT nombre FROM temp_paysans WHERE section = '0' AND id_province = '".$res->id."'";
				$req_dis = sql_query($sql_dis);
				$res_dis = sql_object($req_dis);
				echo $res_dis->nombre;
				?><br />

				<strong>Paysans en construction</strong>: 
				<?php 
				//Paysans en coonstructions
				$cpt = 0;
				$sql_dis = "SELECT SUM(nombre) as NombreTot FROM temp_paysans WHERE section = '8' AND id_province = '".$res->id."' GROUP BY section";
				$req_dis = sql_query($sql_dis);
				$res_dis = sql_object($req_dis);
				/*while($res_dis = sql_object($req_dis))
				{ $cpt += $res_dis->nombre; }*/
				echo $res_dis->NombreTot;
				?><br />					

			</td>
			<td>
				<strong>Bâtiments totaux</strong>: <?php echo $res->buildings ; ?><br />

				<strong>Bâtiments en construction</strong>: 
				<?php
				$sql_bat = "SELECT id FROM batiments WHERE id_province = '".$res->id."' AND value = '0'";
				$req_bat = sql_query($sql_bat);
				echo mysql_num_rows($req_bat);
				?><br />

				<strong>Bâtiments endommagés</strong>:
				<?php
				$cpt = 0;
				$sql_bat2 = "SELECT id FROM batiments WHERE id_province = '".$res->id."' AND `value` = '1' AND life < life_total";
				$req_bat2 = sql_query($sql_bat2);
				echo mysql_num_rows($req_bat2);
				?>
				<br />
			</td>
		
		</tr>
	</table>
	</fieldset>
	<br />
<?php
}

bw_tableau_end();

echo "<h2>Nouveau</h2>\n";

bw_tableau_start("Statistiques");

echo "<table class=\"newsmalltable\">
<tr>
	<th>Province</th>
	<th>Ressources</th>
	<th>Paysans</th>
	<th>Unités</th>
	<th>Guerres</th>
	<th>Sorts</th>
	<th>Cases</th>
	<th>V/D</th>
</tr>";
//bw_fieldset("Information", bw_info("En Construction!"));
//echo "<br />\n";

$sql = "SELECT 
	a.*, 
	b.nombre,
	COUNT(c.id) AS NumUnites 
FROM 
	provinces AS a 
LEFT JOIN
	temp_paysans AS b ON b.id_province = b.id
LEFT JOIN 
	armees AS c ON c.id_province = a.id 
WHERE 
	a.id_joueur = '".$_SESSION['id_joueur']."'
	AND b.section = '0'
GROUP BY a.id
";
//echo $sql;
$req = sql_query($sql);
while($res = sql_object($req))
{
	echo "<tr>
	<td>".$res->name."</td>
	<td>
		Or: ".$res->gold."<br />
		Nou.: ".$res->food."<br />
		Pierre: ".$res->stone."<br />
		Bois: ".$res->wood."<br />
		Magie: ".$res->craft."
	</td>
	<td>".$res->nombre."/".$res->peasant."</td>";
	// Unités
	$s_uni = "SELECT id FROM armees WHERE id_province = '".$res->id."' AND dispo = '1'";
	$q_uni = sql_query($s_uni);
	echo "
	<td>".sql_rows($q_uni)."/".$res->NumUnites."</td>";

	// Guerres
	$s_war = "SELECT id_guerre FROM guerres WHERE att_pro_id = '".$res->id."'";
	$q_war = sql_query($s_war);
	echo "
	<td>".sql_rows($q_war);
	$s_war = "SELECT id_guerre FROM guerres WHERE def_pro_id = '".$res->id."'";
	$q_war = sql_query($s_war);
	echo "/".sql_rows($q_war)."</td>";

	// Sorts
	echo "
	<td><br /></td>
	<td>".$res->cases_usuable."/".$res->cases_notusuable."/".$res->cases_total."</td>
	<td>".$res->victoires."/".$res->pertes."</td


</tr>";
}
echo "
</table>";
bw_tableau_end();
?>