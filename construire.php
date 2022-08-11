<?php
//verifie la session
require ('include/session_verif.php');
//verifie le bouton
if(isset($_GET['batid']))
{
	$IdBati = clean($_GET['batid']);

	//Les couts du bâtiment
	$sql = "SELECT * FROM `liste_batiments` where id = '".$IdBati."'";
	$req = sql_query($sql);
	if(sql_rows($req) == 1)
	{
		$res = mysql_fetch_array($req);
		$BAT['id']			= $res['id'];
		$BAT['gold']		= $res['or'];
		$BAT['food']		= $res['champ'];
		$BAT['mat']			= $res['materiaux'];
		$BAT['cases']		= $res['cases'];
		$BAT['people']		= $res['paysan'];
		$BAT['total_name']	= $res['nom'];
		$BAT['niveau']		= $res['niveau'];
		$BAT['life']		= $res['life'];
		$BAT['nom']			= $res['code_nom'];
		$temps				= ($res['duree']*$CONF['vitesse_jeu'])*3600;

		//Vitesse jeu
		//$Duree		*= $CONF['vitesse_jeu'];

		if($Joueur->race == 4)
		{//on est elfe
			$BAT['mat'] = ceil($BAT['mat']*$CONF['bonus_elfes_2']);
		}
		else if($Joueur->race == 5)
		{//Rebelle
			$Duree -= $CONF['bonus_rebelles_2'];
		}
		$Duree = time()+$Duree;

		# En dessous de 0 -> bug vitesse_jeu
		if($Duree < 0) $Duree = time()+1;

		//On va chercher les params de sa province
		$pro = "SELECT * FROM provinces WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id = '".$_SESSION['id_province']."'";
		$proreq = sql_query($pro);
		$res = mysql_fetch_array($proreq);
		$Niveau = $res['etat'];
		$Gold	= $res['gold'];
		$Food	= $res['food'];
		$Mat	= $res['mat'];
		$Cases	= $res['cases_usuable'];

		//Paysan disponnibles
		$sqlp = "SELECT nombre FROM `temp_paysans` WHERE `id_joueur` = '".$_SESSION['id_joueur']."' AND section = '0' AND id_province = '".$_SESSION['id_province']."' AND esclave = 'N'";
		$reqp = sql_query($sqlp);
		$resp = mysql_fetch_array($reqp);
		$pdispo = $resp['nombre'];

		if ($Niveau >= $BAT['niveau'])
		{//Ok
			//petite correction pour éviter le problème du $BAT['nom_valeur']
			$NewPDispo = $pdispo-$BAT['people'];

			//Verifie que le bâtiment n'est pas déjà construit
			$sql = "SELECT * FROM batiments WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND id_batiment = '".$BAT['id']."'";
			$req = sql_query($sql);
			$nbr = mysql_num_rows($req);

			if ($nbr == 1) 
			{//Non
				$Message = "Vous construisez déjà ce bâtiment!<br />\n";
			} 
			else 
			{//Ok
				//Verifie si on a assez de ressources
				if (($Gold >= $BAT['gold']) && ($Food >= $BAT['food']) && ($Mat >= $BAT['mat']) && ($Cases >= $BAT['cases']) && ($pdispo >= $BAT['people'])) 
				{//Ca passe
					$newgold	= ($Gold - $BAT['gold']) ;
					$newfood	= ($Food - $BAT['food']) ; 
					$newmat		= ($Mat - $BAT['mat']) ;
					$newcases	= ($Cases - $BAT['cases']) ;
					$newpdispo	= ($pdispo - $BAT['people']) ;

					//Update les ressources
					$sql = "UPDATE `provinces` 
						SET 
							`gold` = '".$newgold."',
							`food` = '".$newfood."',
							`mat` = '".$newmat."',
							`cases_usuable` = '".$newcases."'
						WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id = '".$_SESSION['id_province']."'";
					sql_query($sql) ;

					//Ajoute le batiment en constructions
					$insert = "INSERT INTO batiments VALUES ('', '".$_SESSION['id_joueur']."', '".$_SESSION['id_province']."', '".$BAT['id']."', '".$BAT['nom']."', '0', '".$Duree."', '".$BAT['life']."', '".$BAT['life']."')";
					sql_query($insert);

					$sql = "UPDATE temp_paysans SET nombre = '".$NewPDispo."' WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND section = '0' AND esclave = 'N'";
					sql_query($sql);

					$sql = "INSERT INTO temp_paysans VALUES ('', '".$_SESSION['id_joueur']."', '8', '".$BAT['people']."', '".$Duree."', '".$_SESSION['id_province']."','const_".$BAT['id']."', 'N')";
					sql_query($sql);

					$Message = "La construction de votre ".$BAT['total_name']." a bien été commencé(e). Le bâtiment ainsi que vos paysans seront prêt le ".date($CONF['game_timeformat'], $Duree).".<br />\n";

				}
				else 
				{
					$Message = "Vous n'avez pas assez de ressources, de cases ou de paysans disponnibles.<br />\n";
				}//End If on a assez de ressources
			}//End if on construit déjà
		}//End if on est du bon lvl
	}
}
include 'batiments.php';
?>