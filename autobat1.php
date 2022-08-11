<?php
//verifie la session
require ('include/session_verif.php');
//Selectionne nos bâtiments en construction

//MET à jour les batiments construit
$up = "UPDATE batiments SET value = '1' WHERE "
	. "value = '0' AND "
	. "id_joueur = '".$_SESSION['id_joueur']."' AND "
	. "id_province = '".$_SESSION['id_province']."' AND "
	. "time <= '".time()."';";
sql_query($up);

$nbr = mysql_affected_rows();
if($nbr > 0) {
	$up = "UPDATE provinces SET buildings = (buildings+".$nbr.") WHERE id_joueur = '".$_SESSION['id_joueur']."' AND `id` = '".$_SESSION['id_province']."'"; 
	sql_query($up);
}

//Pour les réparations & constructions (on se simplifie la vie...!)
$sql = "SELECT * FROM temp_paysans WHERE section = '8' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND `time` <= '".time()."'";
$req = sql_query($sql);
while($res2 = mysql_fetch_array($req))
{//prenc chaque entrée

	//Batiment
	$tmp = explode("_", $res2['extra_info']);
	$BAT['id'] = $tmp[1];


	//Si c'est repar, on update le batiment
	if($tmp[0] == 'repar')
	{
		//Update le bâtiment
		$Up = "UPDATE batiments SET value = '1', life = (life+'".$res2['nombre']."') WHERE id_batiment = '".$BAT['id']."' AND id_province = '".$_SESSION['id_province']."'";
		sql_query($Up);	
	}

	//Met à jour le nombre de paysans disponnible
	$update2 = "UPDATE temp_paysans SET nombre = (nombre+'".$res2['nombre']."') WHERE section = '0' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."'";
	sql_query($update2);	

	//Supprime l'entrée
	$delete = "DELETE FROM temp_paysans WHERE id = '".$res2['id']."'";
	sql_query($delete);
}
?>