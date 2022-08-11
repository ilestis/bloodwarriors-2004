<?php
//CALCULE
$NbJoueurs = 0;
$NbJoueursActifs = 0;
$NbProvinces = 0;
$PuissanceMoyenne = 0;
$PuissanceTotal = 0;
$NbUnites = 0;
$NbUnitesGuerre = 0;

$NbPaysans = 0;
$NbBatiments = 0;
$NbOr = 0;
$NbNourriture = 0;
$NbPierre = 0;
$NbBois = 0;
$NbMagie = 0;
$NbCasesTotal = 0;

$NbGuerres = 0;
$NbGuerresCours = 0;

$NbAlliances = 0;
$NbVictoires = 0;
$NbPertes = 0;

$sql = "SELECT id, victoires, pertes, puissance FROM joueurs";
$req = sql_query($sql);
while($res = sql_object($req))
{
	$NbJoueurs++;
	$PuissanceTotal += $res->puissance;
	$NbVictoires += $res->victoires;
	$NbPertes += $res->pertes;

	//Provinces
	$sql2 = "SELECT peasant, buildings, cases_total, gold, food, mat, craft FROM provinces WHERE id_joueur = '".$res->id."'";
	$req2 = sql_query($sql2);
	while($res2 = sql_object($req2))
	{
		$NbProvinces ++;
		$NbPaysans += $res2->peasant;
		$NbBatiments += $res2->buildings;
		$NbCasesTotal += $res2->cases_total;


		$NbOr += $res2->gold;
		$NbNourriture += $res2->food;
		$NbMat += $res2->mat;
		$NbMagie += $res2->craft;
	}

	//Actife?
	$Temps = time()-(3600*24);
	$sql2 = "SELECT id FROM autres_ip WHERE id_joueur = '".$res->id."' AND time > '".$Temps."' LIMIT 0, 1";
	$req2 = sql_query($sql2);
	if (mysql_num_rows($req2) > 0) $NbJoueursActifs++;
}

//Unités
$sql = "SELECT id FROM armees";
$req = sql_query($sql);
$NbUnites = mysql_num_rows($req);
$sql = "SELECT id FROM armees WHERE dispo <> 1 ";
$req = sql_query($sql);
$NbUnitesGuerre = mysql_num_rows($req);

$sql = "SELECT id_guerre FROM guerres";
$req = sql_query($sql);
$NbGuerresCours = mysql_num_rows($req);

//Puissance
$PuissanceMoyenne = round($PuissanceTotal / $NbJoueurs, 2);

bw_tableau_start("Les Statistiques Générales");
bw_f_info("Information", "Dernière mise à jour: ".date($CONF['game_timeformat'], time()).".");
?>

			<div style="text-align: left; margin: 10px;">

			<strong>Nombre de joueurs:</strong> <?php echo $NbJoueurs; ?><br />
			<strong>Nombre de joueurs s'étant connecté en 24 heures: </strong><?php echo $NbJoueursActifs; ?><br />
			<strong>Nombre de provinces:</strong><?php echo $NbProvinces; ?><br />
			<strong>Puissance moyenne des joueurs: </strong> <?php echo $PuissanceMoyenne; ?><br /><br />
			
			<strong>Nombre d'unités:</strong><?php echo $NbUnites; ?><br />
			<strong>Nombre d'unités en guerre</strong> (<em>inclu unités rentrantes</em>)<strong>:</strong> <?php echo $NbUnitesGuerre; ?><br />
			
			<strong>Nombre de paysans:</strong> <?php echo $NbPaysans; ?><br />
			<strong>Nombre de bâtiments:</strong> <?php echo $NbBatiments; ?><br /><br />

			<strong>Nombre de Pièces d'Or:</strong> <?php echo $NbOr; ?><br />
			<strong>Nombre de Sacs de Nourriture:</strong> <?php echo $NbNourriture; ?><br />
			<strong>Nombre de Matériaux:</strong> <?php echo $NbMat; ?><br />
			<strong>Nombre de Fioles de Magie:</strong> <?php echo $NbMagie; ?><br /><br />
			
			<strong>Nombre de guerres en cours:</strong> <?php echo $NbGuerresCours; ?><br />
			</div>
<?php
bw_tableau_end();
?>