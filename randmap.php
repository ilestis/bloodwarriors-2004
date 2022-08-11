<?php
/*----------------------[TABLEAU]---------------------
|Nom:			NomDuFichier.Extention
+-----------------------------------------------------
|Description:	Description du fichier
+-----------------------------------------------------
|Date de création:				jj/mm/aa
|Dernière modification[Auteur]: jj/mm/aa[Pseudo]
+---------------------------------------------------*/
include 'include/fonction.php'; //Les fonctions
$CONF = global_variables('include/variables.inc.php'); //Variables global de configuration
include 'connect.php'; //Connextion BDD

//vide 
echo "Vidage de la map:<br />";
$sql = "TRUNCATE TABLE `info_cartes`";
sql_query($sql);
echo "Table vidée...<br />\n";

echo "Création d'une nouvelle table:<br />";
echo "<ul>Nombre de case (à la racine): ".$CONF['game_case']." cases.</ul><br /><br />";
//créé une map
for($y = 1; $y <= $CONF['game_case_y']; $y++)
{
	for ($x = 1; $x <= $CONF['game_case_x']; $x++)
	{
		$num = mt_rand(1, 6);

		if ($num == 4) $num = 1;
		elseif ($num == 5) $num = 1;
		elseif ($num == 6) $num = 2;
		$upi = "INSERT INTO info_cartes VALUES('".$x."', '".$y."', '".$num."')";
		sql_query($upi);
		echo "<img src=\"images/map/".$num.".jpg\" border=\"0\"\ style=\"margin: 0px; padding: 0px;\">\n";	
	}
	echo "<br />";
}

$max = ($y-1)*($x-1);
echo "Nombre de case au total: ".$max."<br /><br />";

echo "Création terminée.<br />";

?>