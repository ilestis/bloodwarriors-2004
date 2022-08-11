<?
//Nb de bon bâtiments
$sql_b = "SELECT id FROM batiments WHERE id_province = '".$_SESSION['id_province']."' AND value = '1' and id_batiment < 500 and id_batiment > 0";
$req_b = sql_query($sql_b);
$Joueur_Bati = sql_rows($req_b);

//Etat
$sql = "SELECT etat FROM provinces WHERE id = '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."'";
$req = sql_query($sql);
$res = sql_array($req);
$Province_Etat = $res['etat'];

//bâtiments necessaire pour chaque niveau
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
$nombre_1 = mysql_num_rows($req1); //nombre d'entrées
$nombre_2 = mysql_num_rows($req2)+$nombre_1; //nombre d'entrées
$nombre_3 = mysql_num_rows($req3)+$nombre_2; //nombre d'entrées


//verifie notre niveau
if ($Province_Etat == 1)
{//état d'un village à une ville
	if($Joueur_Bati == $nombre_1)
	{//ok
		$sql = "UPDATE provinces SET etat = '2' WHERE id = '".$_SESSION['id_province']."'";
		sql_query($sql);
		$Message = "Après une fête qui a duré des heures, votre village vient de passer au stade de Ville!";

		//Update les cases
		sql_query("UPDATE provinces SET cases_total = (cases_total+'25'), cases_usuable = (cases_usuable+'20'), cases_notusuable = (cases_notusuable+'5') WHERE id = '".$_SESSION['id_province']."'");

	}
}
if ($Province_Etat == 2)
{//ville -> cité
	if($Joueur_Bati == $nombre_2)
	{//ok
		$sql = "UPDATE provinces SET etat = '3' WHERE id = '".$_SESSION['id_province']."'";
		sql_query($sql);
		$Message = "La fête est finie, votre ville vient de passer au stade de Cité!";

		//Update les cases
		sql_query("UPDATE provinces SET cases_total = (cases_total+'30'), cases_usuable = (cases_usuable+'23'), cases_notusuable = (cases_notusuable+'7') WHERE id = '".$_SESSION['id_province']."'");
	}
}

if ($Province_Etat == 3)
{//cité -> métropole
	if($Joueur_Bati == $nombre_3)
	{//ok
		$sql = "UPDATE provinces SET etat = '4' WHERE id = '".$_SESSION['id_province']."'";
		sql_query($sql);
		$Message = "La fête est finie, votre cité vient de passer au stade de Métropole!";
		
		//Update les cases
		sql_query("UPDATE provinces SET cases_total = (cases_total+'35'), cases_usuable = (cases_usuable+'25'), cases_notusuable = (cases_notusuable+'10') WHERE id = '".$_SESSION['id_province']."'");
	}
}

include("./batiments.php");
?>