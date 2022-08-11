<?php
//verifie la session
require ('include/session_verif.php');
//time
$date = time();

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
if(bw_batiavailable2('mine', $id_province, false)) $Bonus[1] = 1.1;
if(bw_batiavailable2('sanctuaire', $id_province, false)) $Bonus[5] = 1.1;
if(bw_batiavailable2(39, $id_province)) $Bonus[3] = 1.15;



//Nos ressouces
$sqlres = "SELECT * FROM provinces WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id = '".$_SESSION['id_province']."'";
$req = sql_query($sqlres);
$res = mysql_fetch_array($req);
$X			= $res['x'];
$Y			= $res['y'];
$R[1]		= $res['gold'];
$R[2]		= $res['food'];
$R[3]		= $res['mat'];
$R[4]		= 0;
$R[5]		= $res['craft'];
$R[6]		= 0;
$R[7]		= 0;
$P['dispo']	= 0;

//Bonus Terrain
$sql = "SELECT valeur FROM info_cartes WHERE x = '".$X."' AND y = '".$Y."'";
$req = sql_query($sql);
$res = mysql_fetch_array($req);
if($res['valeur'] == 1) //Champs
	$Bonus[2] = 1.1;
elseif($res['valeur'] == 2) //Forêt
	$Bonus[3] = 1.1;
elseif($res['valeur'] == 3) //Montagne
	$Bonus[1] = 1.1;

//Ressource max
$Ressources_max = get_ressource_limit($_SESSION['id_province']);
$Ressources_max[6] = 0;
$Ressources_max[7] = 0;
$nb_paysans_plus = 0;
$nb_esclaves_plus = 0;

for ($i = 1; $i < 8; $i++)
{
	//date des paysans
	$sql = "SELECT * FROM temp_paysans WHERE section = '".$i."' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND `time` <= '".time()."'";
	$req = sql_query($sql);
	while($resx = mysql_fetch_array($req))
	{//prenc chaque entrée
		/* C'est le temps donc on prend nos stats */
		//On incrémente la variable des ressources ressources
		$R[$i] += ceil($resx['nombre']*$Bonus[$i]);
		
		if($R[$i] > $Ressources_max[$i])
		{//Empêche de dépasser le max
			$R[$i] = $Ressources_max[$i];
		}

		//Supprime l'entrée
		$delete = "DELETE FROM temp_paysans WHERE id = '".$resx['id']."'";
		sql_query($delete);

		if($resx['esclave'] == 'O') {
			$nb_esclaves_plus += $resx['nombre'];
		} else {
			$nb_paysans_plus += $resx['nombre'];
		}
	}
}

if($nb_paysans_plus > 0)
{
	// Met à jour le nombre de paysans disponnibles
	$update2 = "UPDATE temp_paysans SET nombre = (nombre+'".$nb_paysans_plus."') WHERE section = '0' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND esclave = 'N'";
	sql_query($update2);	
}

# UPDATE esclaves
if($nb_esclaves_plus > 0) {
	$tot = floor($nb_esclaves_plus*($Joueur->race == 4 ? $CONF['bonus_elfes_mortalite'] : $CONF['esclaves_mortalite']))-$CONF['esclaves_mortalite_plus'];
	//print($nb_esclaves_plus." -> ".$tot);

	// Passage Min
	if($tot < 0) $tot = 0;

	// Met à jour le nombre d'esclaves disponnibles
	$update2 = "UPDATE temp_paysans SET nombre = (nombre+'".$tot."') WHERE section = '0' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND esclave = 'O'";
	sql_query($update2);	
}

// update ressources
$update = "UPDATE provinces SET `gold` = '".$R['1']."', `food` = '".$R['2']."', `mat` = '".$R['3']."', `craft` = '".$R['5']."' WHERE id = '".$_SESSION['id_province']."'";
sql_query($update);

?>