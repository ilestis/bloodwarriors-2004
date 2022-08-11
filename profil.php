<?php
/*
+---------------------
|Nom: Le Profil
+---------------------
|Description: Cette page incluse sur toutes les pages principales du jeu montre notre profil (nos stats)
+---------------------
|Date de création: Février 04
|Date du premier test: Février 04
|Dernière modification: 13 Aout 2005
+--------------------*/
//verifie l'état de la session
require ('include/session_verif.php');

//langague
$lang_text = lang_include($Joueur->lang,'lang_ressources');

//Prend les variables de la province
$sql = "SELECT * FROM provinces WHERE id = '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."'";
$req = sql_query($sql);
$nbr = mysql_num_rows($req);
if ($nbr == 0) { echo "erreur"; exit; }
else
{
	$Res_Ressources = mysql_fetch_array($req);

	//Province En Cours
	$_SESSION['nom_province'] = $Res_Ressources['name'];

	//définit notre niveau de ville
	if ($Res_Ressources['etat'] == 1) $etat = $lang_text['village'];
	if ($Res_Ressources['etat'] == 2) $etat = $lang_text['town'];
	if ($Res_Ressources['etat'] == 3) $etat = $lang_text['city'];
	if ($Res_Ressources['etat'] == 4) $etat = $lang_text['metropole'];

	//les paysans disponibles
	$sqlp = "SELECT nombre FROM `temp_paysans` WHERE `id_joueur` = '".$_SESSION['id_joueur']."' AND section = '0' AND id_province = '".$_SESSION['id_province']."'";
	$reqp = sql_query($sqlp);
	$resp = mysql_fetch_array($reqp);
	$pdispo = $resp['nombre'];

	echo "<center>\n";
	//le grand tableau
	echo "<!-- Ressoucres du joueur -->\n";
	//echo "".$Res_Ressources['name']."</strong>"; //$lang_over['province'].
	
	//On prend les autres provinces
	if (!isset($_GET['p'])) $PageEnCour = "index";
	else $PageEnCour = $_GET['p'];
	$cpt = 0;

	//Requête qui sort toutes nos provinces
	$reqProvinces = sql_query("SELECT `name`, `id`, `x`, `y` FROM provinces WHERE id_joueur = '".$_SESSION['id_joueur']."' ORDER BY `name` ASC");
	
	$cpt = 0;
	while($resProvinces = mysql_fetch_array($reqProvinces))
	{
		echo ($cpt > 0 ? ', &nbsp ' : null);
		
		if($resProvinces['id'] == $_SESSION['id_province']) { // Gras
			echo "<strong>".$resProvinces['name']."</strong>";
		} else {
			echo "<a href=\"?p=".$PageEnCour."&idprovince=".$resProvinces['id']."\">".$resProvinces['name']."</a>";
		}
		//(".$resProvinces['x'].";".$resProvinces['y'].")
		$cpt++;
	}

	echo "<br />\n";
	echo "<table width=\"550px\" border=\"0\">\n";

	echo "<tr>\n";//------------------------RESSOURCES---------------------------

	echo "	<td style=\"text-align:center\">";
	echo "<img src=\"images/icons/peasant.png\" alt=\"".$lang_over['r_peasant']."\" title=\"".$lang_over['r_peasant']."\"/><br />".$lang_over['r_peasant'].":<br />".$pdispo."/".$Res_Ressources['peasant']."</td>\n";

	$Array = array('gold', 'food', 'mat', 'craft', 'buildings');
	for($i = 0; $i < count($Array); $i++)
	{
		echo "	<td style=\"text-align:center\"><img src=\"images/icons/".$Array[$i].".png\" alt=\"".$lang_over['r_'.$Array[$i]]."\" title=\"".$lang_over['r_'.$Array[$i]]."\"/><br />";
		echo $lang_over['r_'.$Array[$i]].':<br />'.$Res_Ressources[$Array[$i]]."</td>\n";
	}

	echo "	<td style=\"text-align:center\">";
	echo "<img src=\"images/icons/cases.png\" alt=\"".$lang_over['r_cases']."\" title=\"".$lang_over['r_cases']."\"/><br />".$lang_over['r_cases'].":<br />".$Res_Ressources['cases_usuable']."/".$Res_Ressources['cases_notusuable']."/".$Res_Ressources['cases_total']."</td>\n";
	//---------------------END RESSOURCES--------------------------
	//echo '</tr>';

	//------------------------END TABLEAU----------------------
	echo "</tr></table>\n\n";
}
?>