<?php
require('./profil.php');


$rows = ceil(sqrt($Res_Ressources['cases_total']));

$cases = array();
$cpt = 0;
$sql = "SELECT a.id_batiment, b.nom, b.cases FROM batiments AS a LEFT JOIN liste_batiments AS b ON b.id = a.id_batiment WHERE a.id_province = '".$_SESSION['id_province']."' AND id_batiment < '300';";
$req = sql_query($sql);
while($res = sql_object($req)) {
	for($i = 0; $i <= $res->cases; $i++) {
		$cases[$cpt] = $res->nom;
		$cpt++;
	}
}

echo "<table border=1>";
$cpt = 0;
for($r = 0; $r < $rows; $r++) {
	echo "<tr>";
	for($l = 0; $l < $rows; $l++) {
		echo "<td>".$cases[$cpt]."</td>";
		$cpt++;
	}
	echo "</tr>";
}
echo "</table>";

echo "<img src=\"plan_province_image.php\" />";