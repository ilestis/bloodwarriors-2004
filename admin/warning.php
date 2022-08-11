<?php
/*
//verifie si la session est en cours
if (session_is_registered("login") == false)
{
	include 'news.php';
	exit;
}

//date & time
$jour = date("d/m/Y") ;
$heure = date("G:i");
$true_date = $jour.'-'.$heure;
$time = time();


$Page = 'nouveauxcomptes';
if ($Joueur->acceslvl < $adminpage[$Page])  exit;
{//si on est de niveau 3 et plus
	
	if($id == 'del')
	{//on a demander de supprimé un warn
		$del = "DELETE FROM `warnings` where `id` = '".$num."'";
		mysql_query($del);
		echo 'Warning bien supprimé.<br/>';
		$news = "INSERT INTO `admin_journal` VALUES('','".$pseudo."','".$true_date."','<img src=\'images/admin/warn.png\'>Le warning \'".addslashes($warning)."\' à été retiré de ".$joueur.".','".$time."')";
		mysql_query($news);
	}
	elseif($id == 'add')
	{//on ajoute un warning
		$add = "INSERT INTO `warnings` VALUES('','".$joueur."','".addslashes($warning)."')";
		mysql_query($add);
		echo 'Le warning '.$warning.' à bien été ajouté a '.$joueur.'.<br/>';
		$news = "INSERT INTO `admin_journal` VALUES('','".$pseudo."','".$true_date."','<img src=\'images/admin/warn.png\'>Le warning \'".addslashes($warning)."\' à été ajouté à ".$joueur.".','".$time."')";
		mysql_query($news);
	}
	//le bouton pour chercher d'autre joueurs
	echo '<center><table width=60% border=0 bgcolor=#000000>';
	echo '<tr><td class="entete">';
	echo '<center><form name=form1 method=post action="index.php?p=admin_search">';
	echo 'Rechercher: <select name=joueur>';
	$sql = "select `pseudo` from stats order by pseudo ASC" ;
	$result = mysql_query($sql);
	while($res = mysql_fetch_array($result))
	{
		echo '<option value=\''.$res['pseudo'].'\'>'.$res['pseudo'].'</option>' ;
	}
	echo '</select><input type=submit name=Submit2 value="Rechercher"></td></tr></table></form>';

	echo '<a href="index.php?p=admin_admin">Retour a la page admin</a><br/>';

}*/
?>