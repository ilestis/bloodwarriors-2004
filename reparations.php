<?php

//Verifie si le b�timent existe et est chez nous
$sql = "SELECT * FROM batiments WHERE id = '".clean($_GET['id'])."' AND id_province = '".$_SESSION['id_province']."' AND value = '1'";
$req = sql_query($sql);
$res = mysql_fetch_array($req);

$Bati['vie'] = $res['life'];
$Bati['ID'] = clean($_GET['id']);
$Bati['ID2'] = $res['id_batiment'];

//Ressources
$sql = "SELECT mat FROM provinces WHERE id = '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."'";
$req = sql_query($sql);
$Res_Ressources = sql_array($req);

//Prend le b�timent dans la BDD
$sql = "SELECT life FROM `liste_batiments` WHERE id = '".$Bati['ID2']."'";
$req = sql_query($sql);
$res = mysql_fetch_array($req);
$Bati['total_life'] = $res['life'];

if($Bati['vie'] < $Bati['total_life'])
{//Le batiment peut �tre r�par�
	
	//Donn�es
	$Nombre = clean($_POST['nbpaysans']);
	$Temps = time()+(($Nombre*$CONF['bati_repar_time'])*$CONF['vitesse_jeu']);
	$Bati['new_life'] = $Bati['vie']+$Nombre;

	if($Bati['new_life'] <= $Bati['total_life'])
	{
		//Verifie si on a assez de ressources
		$New_Mat	= $Res_Ressources['mat'] - $Nombre;

		//Paysans
		$sql = "SELECT nombre FROM temp_paysans WHERE section = '0' AND id_province = '".$_SESSION['id_province']."' AND esclave = 'N'";
		$req = sql_query($sql);
		$res = mysql_fetch_array($req);

		$New_Paysans = $res['nombre'] - $Nombre;

		
		if(($New_Mat >= 0) && ($New_Paysans >= 0))
		{
			//Assez de ressources 9".$Bati['ID2']."
			$INS = "INSERT INTO temp_paysans VALUES ('','".$_SESSION['id_joueur']."', '8', '".$Nombre."', '".$Temps."', '".$_SESSION['id_province']."', 'repar_".$Bati['ID2']."', 'N')";
			sql_query($INS);

			//Update
			$Up = "UPDATE provinces SET mat = '".$New_Mat."' WHERE id = '".$_SESSION['id_province']."'";
			sql_query($Up);

			$Up2 = "UPDATE temp_paysans SET nombre = '".$New_Paysans."' WHERE section = '0' AND id_province = '".$_SESSION['id_province']."' AND esclave = 'N'";
			sql_query($Up2);

			$Up3 = "UPDATE batiments SET value = 3, time = '".$Temps."' WHERE id = '".$Bati['ID']."' AND id_province = '".$_SESSION['id_province']."'";
			sql_query($Up3);

			$Message = "Les r�parations de votre b�timent ont bien �t� commenc�es. Votre b�timent ainsi que vos paysans seront pr�t pour le ".date($CONF['game_timeformat'], $Temps).".<br />\n";

		}
		else
		{
			$Message = bw_error("Soit vous n'avez pas assez de ressources, soit pas assez de paysans disponnibles.<br />Les r�paration vous couteraient:<br /> Paysans disponnibles: ".$Nombre.". Bois n�cessaire:".$Nombre.". Pierre n�cessaire:".$Nombre."<br />\n");
		}

	}
	else
	{//Trop de r�parations
		$Message = bw_error("Vous ne pouvez effectuer autant de r�parations!<br />\n");
	}
}
else
{
	$Message = bw_info("Ce b�timent poss�de d�j� toute sa vie!");
}

include('./batiments.php');
?>