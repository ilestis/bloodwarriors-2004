<?
//Nb de bon b�timents
$sql_b = "SELECT id FROM batiments WHERE id_province = '".$_SESSION['id_province']."' AND value = '1' and id_batiment < 500 and id_batiment > 0";
$req_b = sql_query($sql_b);
$Joueur_Bati = sql_rows($req_b);

//Etat
$sql = "SELECT etat FROM provinces WHERE id = '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."'";
$req = sql_query($sql);
$res = sql_array($req);
$Province_Etat = $res['etat'];

//b�timents necessaire pour chaque niveau
//Nivea 1 2 et 3
if($_SESSION['id_province'] != $_SESSION['id_main_province']) {
	$req1 = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '1' && bati_principal = 'O'");
	$req2 = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '2' && bati_principal = 'O'");
	$req3 = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '3' && bati_principal = 'O'");
} else {
	$req1 = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '1'");
	$req2 = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '2'");
	$req3 = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '3'");
}
$nombre_1 = mysql_num_rows($req1); //nombre d'entr�es
$nombre_2 = mysql_num_rows($req2)+$nombre_1; //nombre d'entr�es
$nombre_3 = mysql_num_rows($req3)+$nombre_2; //nombre d'entr�es


//verifie notre niveau
if ($Province_Etat == 1)
{//�tat d'un village � une ville
	if($Joueur_Bati == $nombre_1)
	{//ok
		$sql = "UPDATE provinces SET etat = '2' WHERE id = '".$_SESSION['id_province']."'";
		sql_query($sql);
		$Message = "Apr�s une f�te qui a dur� des heures, votre village vient de passer au stade de Ville!";

		//Update les cases
		sql_query("UPDATE provinces SET cases_total = (cases_total+'25'), cases_usuable = (cases_usuable+'20'), cases_notusuable = (cases_notusuable+'5') WHERE id = '".$_SESSION['id_province']."'");

	}
}
if ($Province_Etat == 2)
{//ville -> cit�
	if($Joueur_Bati == $nombre_2)
	{//ok
		$sql = "UPDATE provinces SET etat = '3' WHERE id = '".$_SESSION['id_province']."'";
		sql_query($sql);
		$Message = "La f�te est finie, votre ville vient de passer au stade de Cit�!";

		//Update les cases
		sql_query("UPDATE provinces SET cases_total = (cases_total+'30'), cases_usuable = (cases_usuable+'23'), cases_notusuable = (cases_notusuable+'7') WHERE id = '".$_SESSION['id_province']."'");
	}
}

if ($Province_Etat == 3)
{//cit� -> m�tropole
	if($Joueur_Bati == $nombre_3)
	{//ok
		$sql = "UPDATE provinces SET etat = '4' WHERE id = '".$_SESSION['id_province']."'";
		sql_query($sql);
		$Message = "La f�te est finie, votre cit� vient de passer au stade de M�tropole!";
		
		//Update les cases
		sql_query("UPDATE provinces SET cases_total = (cases_total+'35'), cases_usuable = (cases_usuable+'25'), cases_notusuable = (cases_notusuable+'10') WHERE id = '".$_SESSION['id_province']."'");
	}
}

include("./batiments.php");
?>