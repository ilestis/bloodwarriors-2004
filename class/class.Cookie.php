<?php


class classCookie
{
	function SaveCookies($Login, $PSW)
	{
		//Get pwd

		$Perimation = time()+(3600*365);
		$Val = $Login.'.'.$PSW;
		
		(setcookie('sessid', $Val, $Perimation) or print("Impossible d'enregistrer votre cookies. Verifier que votre butineur le permet!"));
	}
	
	function GetCookies()
	{		
		if(isset($_COOKIE["sessid"]) && $_COOKIE["sessid"] <> "")
		{
			$tab = explode('.', $_COOKIE["sessid"]);
			$sql = "SELECT id, vacances, acceslvl FROM joueurs WHERE login = '".clean($tab[0])."' AND password = '".clean($tab[1])."'";
			$req = sql_query($sql);
			//echo $sql;
			if(sql_rows($req) == 1)
			{
				if(($GLOBALS['CONF']['game_status'] != 2))
				{
					return false;
				}
				$res = sql_array($req);

					
				//Verifie si on a le droit de se connecter
				if($res['vacances'] > 0) {//En vacances
					if($res['vacances'] <= time()) {//On update notre acceslvl
						$up = "UPDATE joueurs SET acceslvl = '1', vacances = '0' WHERE id = '".$res['id']."'";
						sql_query($up);
							return false;
					}
					else {//Encore en vacances
						return false;					
					}
				}
				elseif($res['acceslvl'] < 0) {//Pas encore activé
						return false;
				}
				
				//Prend l'id de notre première province
				$pro = "SELECT id FROM provinces WHERE id_joueur = '".$res['id']."' ORDER BY id ASC LIMIT 0, 1";
				$proq = sql_query($pro);
				$prov = sql_array($proq);
				$Province_Id = $prov['id'];

				//Met nos variables en Session
				$_SESSION['id_joueur'] = $res['id'];
				$_SESSION['login'] = $tab[0];
				$_SESSION['id_province'] = $Province_Id;
				$_SESSION['debug'] = FALSE;

				//Ajoute une connexion
				$sqlip = "INSERT INTO `ip` VALUES('','".$res['id']."','".$_SERVER['REMOTE_ADDR']."','".time()."')";
				sql_query($sqlip) ;
				?>
				<script language="JavaScript">compteur =setTimeout('window.location="index.php?p=index"',1)</script>
				<?php

			}
			else
			{//Destroy cookie
				//echo "suppression yaaa";
				setcookie('sessid');
			}
		}
		else
		{
			//echo "Pas de cookies.<br />\n";
		}
	}
	
	function Seshed()
	{
		if(!isset($_SESSION['sessid']))
			return false;
		return true;
	}



}
?>