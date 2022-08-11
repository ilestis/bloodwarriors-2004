<?php
//verifie qu'on a activé le GET password
if(htmlentities($_GET['pyjama']) != 'dz542kmu')
{//pas ok
	exit;
}//arrête le code

$Temp = explode(" ",microtime()); 
$MicroTime['debut'] = $Temp[1]+$Temp[0];

//Includes
global $CONF;
require('../include/variables.inc.php');
require_once('../include/fonction.php'); //Les fonctions
require_once('../class/class.MySql.php');
$csql = new sql();

//Lanche connection
if(!$csql->connection($GLOBALS['CONF']['game_DB_user'], $GLOBALS['CONF']['game_DB_psw'] ,  $GLOBALS['CONF']['game_DB_server'], $GLOBALS['CONF']['game_DB_name'])) {
	echo $csql->error();
	die("Impossible de se connecter au serveur!");
}

echo '<h2>Script d\'automatisation</h2>\n';


//Les sorts
sql_query("DELETE FROM temp_sorts WHERE `time` < '".time()."'");
echo 'Sorts.... Ok<br />';

// Unités envoit et retour de guerre
echo 'Unités: Envoit/Retour...';
sql_query("UPDATE armees SET dispo = '1' WHERE `heureretour` <= '".time()."' AND `dispo` = '4' OR `dispo` = '9'");
echo ' Ok<br />';

$sql = "SELECT a.x, a.id, a.y, a.gold, a.food, a.wood, a.stone, a.craft, b.valeur FROM provinces AS a LEFT JOIN info_cartes AS b ON b.x = a.x AND b.y = a.y ORDER BY id ASC";
$req = sql_query($sql);
while($res = sql_object($req))
{
	echo '<strong>Province -> '.$res->name.'</strong> ['.$res->x.'/'.$res->y."]<br />\n";
	$id_province = $res->id;
	$id_joueur = $res->id_joueur;
	$Time = time();

	//MET à jour les batiments construit
	echo 'Traite les batiments en construction: ';
	$up = "UPDATE batiments SET value = '1' WHERE "
		. "value = '0' AND "
		. "id_joueur = '".$id_joueur."' AND "
		. "id_province = '".$id_province."' AND "
		. "time <= '".$Time."';";
	sql_query($up);

	$nbr = mysql_affected_rows();
	if($nbr > 0) {
		$up = "UPDATE provinces SET buildings = (buildings+".$nbr.") WHERE id_joueur = '".$id_joueur."' AND `id` = '".$id_province."'"; 
		sql_query($up);
		echo '+'.$nbr;
	}
	echo '... OK<br />';

	//REPARATION
	echo 'Traite les réparations: ';

	$sql2 = "SELECT id, extra_info, nombre FROM temp_paysans WHERE section = '8' AND id_joueur = '".$id_joueur."' AND id_province = '".$id_province."' AND `time` <= '".$Time."'";
	$req2 = sql_query($sql2);
	$paysansplus = 0;
	while($res2 = mysql_fetch_array($req2))
	{//prenc chaque entrée
		//Batiment
		$tmp = explode("_", $res2['extra_info']);
		$BAT['id'] = $tmp[1];


		//Si c'est repar, on update le batiment
		if($tmp[0] == 'repar')
		{
			//Update le bâtiment
			$Up = "UPDATE batiments SET value = '1', life = (life+'".$res2['nombre']."') WHERE id_batiment = '".$BAT['id']."' AND id_province = '".$id_province."'";
			sql_query($Up);	
		}
		
		$paysansplus += $res2['nombre'];	

		//Supprime l'entrée
		$delete = "DELETE FROM temp_paysans WHERE id = '".$res2['id']."'";
		sql_query($delete);
	}
	if($paysansplus > 0) {
		//Met à jour le nombre de paysans disponnible
		$update2 = "UPDATE temp_paysans SET nombre = (nombre+'".$paysansplus."') WHERE section = '0' AND id_joueur = '".$id_joueur."' AND id_province = '".$id_province."'";
		sql_query($update2);
		echo "+".$paysansplus;
	}
	echo '... Ok<br />';

	//MET à jour les paysans au boulot
	echo 'Traite les paysans au boulot<br />';

	//Bonus
	$Bonus[1] = 1;
	$Bonus[2] = 1;
	$Bonus[3] = 1;
	$Bonus[4] = 1;
	$Bonus[5] = 1;
	$Bonus[6] = 1;
	$Bonus[7] = 1;
	$Bonus[8] = 1;

	//Bâtiments qui boostent la production?
	echo '[bonnus bâtiments]';
	if(bw_batiavailable2('mine', $id_province, false)) $Bonus[1] = 1.1;
	if(bw_batiavailable2('sanctuaire', $id_province, false)) $Bonus[5] = 1.1;
	if(bw_batiavailable2(39, $id_province)) $Bonus[3] = 1.15;
	
	//rRessources
	$X			= $res->x;
	$Y			= $res->y;
	$R[1]		= $res->gold;
	$R[2]		= $res->food;
	$R[3]		= $res->stone;
	$R[4]		= $res->wood;
	$R[5]		= $res->craft;
	$R[6]		= 0;
	$R[7]		= 0;
	$R[8]		= 0;
	$P['dispo']	= 0;

	//Bonus Terrain
	echo '[bonnus terrain: '.$res->valeur.']';
	if($res->valeur == 1) //Champs
		$Bonus[2] = 1.1;
	elseif($res->valeur == 2) //Forêt
		$Bonus[4] = 1.1;
	elseif($res->valeur == 3) //Montagne
		$Bonus[3] = 1.1;

	//Ressource max
	$Ressources_max = get_ressource_limit($id_province);
	$Ressources_max[6] = 0;
	$Ressources_max[7] = 0;
	$nb_paysans_plus = 0;

	for ($i = 1; $i < 8; $i++)
	{
		//date des paysans
		$sql2 = "SELECT nombre, id FROM temp_paysans WHERE section = '".$i."' AND id_province = '".$id_province."' AND `time` <= '".$Time."'";
		$req2 = sql_query($sql2);
		while($resx = mysql_fetch_array($req2))
		{//prenc chaque entrée
			/* C'est le temps donc on prend nos stats */
			//On incrémente la variable des ressources ressources
			$R[$i] += ceil($resx['nombre']*$Bonus[$i]);
			
			if($R[$i] > $Ressources_max[$i])
				$R[$i] = $Ressources_max[$i];
			sql_query("DELETE FROM temp_paysans WHERE id = '".$resx['id']."';");
			$nb_paysans_plus += $resx['nombre'];
		}
	}
	if($nb_paysans_plus > 0)
	{
		//Met à jour le nombre de paysans disponnible
		$update2 = "UPDATE temp_paysans SET nombre = (nombre+'".$nb_paysans_plus."') WHERE section = '0' AND id_province = '".$id_province."'";
		sql_query($update2);
		echo '['.$nb_paysans_plus.']';

		//update ressources
		$update = "UPDATE provinces SET `gold` = '".$R['1']."', `food` = '".$R['2']."', `stone` = '".$R['3']."', `wood` = '".$R['4']."', `craft` = '".$R['5']."' WHERE id = '".$id_province."'";
		sql_query($update);	
	}
	echo '... OK<br />';

	echo "<br />\n";
}

//Fin d'exécution
$Temp = explode(" ",microtime()); 
$MicroTime['end'] = $Temp[1]+$Temp[0];
$MicroTime['final'] = $MicroTime['end'] - $MicroTime['debut'];
echo 'exec: '.$MicroTime['final'];
?>