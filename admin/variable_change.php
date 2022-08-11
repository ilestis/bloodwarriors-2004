<?php
//verifie si la session est en cours
if (session_is_registered("login") == false)
{
	include 'news.php';
	exit;
}
//pour les mise en forme
include 'include/function_mef.php';


if($Joueur->acceslvl >= 9)
{
	switch($id)
	{
		case 'guilde':
			//on modifie les valeurs d'une guilde

			//verifie notre niveau
			if($Joueur->acceslvl >= 9)
			{//c'est ok
				switch($change)
				{
					case 'place':
					//on modifie les places
					$sql = "UPDATE `info_guilde` SET `place_libre` = '$valeur' WHERE royaume = '$guilde'";
					sql_query($sql);
					echo 'Données bien mises à jour!<br/>';
					break;

					case 'bonus':
					$sql = "UPDATE `info_guilde` SET `bonus_1` = '$b1', `bonus_2` = '$b2',`bonus_3e` = '$b3',`bonus_4` = '$b4' WHERE royaume = '$guilde'";
					sql_query($sql);
					echo 'Données bien mises à jour!<br/>';
					break;

					case 'politique':
					$sql = "UPDATE `info_guilde` SET `politique` = '$politique' WHERE royaume = '$guilde'";
					sql_query($sql);
					echo 'Données bien mises à jour!<br/>';
					break;

					case 'addnew':
					$sql = "INSERT INTO `info_guilde` VALUES('$name',0,0,0,0,0,1,'Variables des ".$name."',0)";
					sql_query($sql);
					echo 'Province bien ajoutée!<br/>';
					break;
				}
			include 'variable_guilde.php';
			}
			break;

		//modifier le niveau des joueurs
		case 'acceslvl':
			//verifie notre niveau
			if($Joueur->acceslvl >= 9)
			{//c'est ok
				$sql = "update `stats` set acceslvl = '".$_POST['lvl']."' where pseudo = '".$joueur."'";
				sql_query($sql);
				echo "Le niveau d'acces de $joueur est bien passé a $lvl </br>\n";
				include 'variable_player.php';
			}
			break;
		
		//modifier la partie
		case 'var-ouverture':
			//verifie notre niveau
			if($Joueur->acceslvl >= 8)
			{//c'est ok
				$sql = "UPDATE `info_partie` SET etat = '$etatchange'";
				sql_query($sql);
				if ($etatchange == 1) echo 'La partie est maintenant ouverte!<br/>';
				elseif ($etatchange == 0) echo 'La partie est maintenant en maintenance!<br/>';
				include 'variable_saison.php';
			}
			break;

		//modifier le dictionnaire
		case 'dico':
			//verifie notre niveau
			if($Joueur->acceslvl >= 5)
			{//c'est ok
				if($change == 'del')
				{//on supprime
					$sql = "DELETE FROM `admin_dico` WHERE `id` = ".$stat."";
					sql_query($sql);
					echo 'Le mot à bien été supprimé<br/>';
				}
				elseif($change == 'add') 
				{//on ajoute un message
					$sql = "INSERT INTO `admin_dico` VALUES('','$word')";
					sql_query($sql);
					echo 'Le mot '.$word.' à bien été ajouté à la base de donnée<br/>';
				}
			include 'variable_dico.php';
			}
			break;

		//les nouvelles de Kaïo
		case 'news_admin':
			//verifie notre niveau
			if($Joueur->acceslvl >= '99')
			{//c'est ok
			$comment = smiley(mef(admin($comment)));
				if($change == 'mod')
				{
					$sql = "UPDATE `news_admin` SET titre = '$titre', comment = '$comment' where id = '$stats'";
					sql_query($sql);
					echo 'Message bien modifier<br/>';
				}
				elseif ($change == 'del')
				{
					$sql = "DELETE FROM `news_admin` WHERE id = '$stat'";
					sql_query($sql);
					echo 'News bien supprimées<br/>';
				}
				elseif ($change == 'add')
				{
					$date = date("d-m-Y");
					$heure = date("G:i");
					$time = "$date $heure";
					$sql = "INSERT INTO `news_admin` VALUES('','".$time."','".$titre."','".$comment."')";
					sql_query($sql);
					echo 'News bien ajoutée<br/>';
				}
			include 'variable_admin_news.php';
			}
			else
			{ echo 'Mouahahaha pas bon'; }
			break;
	}
}
else
{
	echo 'Vous n\'avez pas les pouvoirs nessessaires pour acceder à cette page!<br/>';
}
?>