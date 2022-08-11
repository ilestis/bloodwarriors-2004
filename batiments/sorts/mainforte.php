<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Mainforte.php
+-----------------------------------------------------
|Description:	Sort Main Forte
+-----------------------------------------------------
|Date de création:				20.02.06
|Dernière modification:			
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//On selectionne les valeurs du sort
$sql = "SELECT * FROM `liste_sorts` WHERE id = '3'";
$req = sql_query($sql);
$res = sql_object($req);

//Variables
$Prix			= $res->cout;
$Nom			= $res->nom;
$Variable		= $res->variable;
$Cible			= $res->cible;
$Titre_Choix	= $res->titre_choix;
$Temps			= $res->temps;
$BatInt			= $res->batint;
$Race			= $res->race;
$Description	= stripslashes($res->description);

echo "<fieldset><legend>".$Nom."</legend>\n";

if(sort_available($res->batint, $res->race, $Joueur->race)) {
	if (isset($_POST['batiment']))
	{//on accélère
		$Bati = clean($_POST['batiment']);
		//verifie que le bâtiment est en un
		$sql = "SELECT value, id_batiment FROM batiments WHERE id = '".$Bati."' AND id_province = '".$_SESSION['id_province']."'";
		$req = sql_query($sql);
		$res = mysql_fetch_array($req);
		$nbr = mysql_num_rows($req);
		
		if ($nbr == 1 && $res['value'] == 0)
		{//en construction
			//Verifie si on a assez de magie
			if(check_craft($_SESSION['id_province'], $Prix))
			{
				$Temps = 3600*$Variable; //temps heures en secondes

				//Update le bâtiment et la magie
				sql_query("UPDATE batiments SET `time` = (`time`-".$Temps.") WHERE `id` = '".$Bati."' AND id_province = '".$_SESSION['id_province']."'");

				//Update paysans
				$up_paysans = "UPDATE temp_paysans SET `time` = (`time`-".$Temps.") WHERE section = '8' AND extra_info = 'const_".$res['id_batiment']."' AND id_province = '".$_SESSION['id_province']."'";
				sql_query($up_paysans);


				$Message = bw_info("Vos paysans sentent une énergie magique les entourers, et cela provoque une augmentation de la construction. Votre bâtiment sera construire six heures plus rapidement!<br />\n");
			} else {
				$Message = bw_error("Vous n'avez pas assez de magie!<br />\n");
			}
		} else {
			$Message = bw_error("Ce bâtiment n'est pas en construction!<br />\n");
		}
	}
	if(isset($Message)) echo $Message;

	echo "Choisissez un bâtiment à accélerer:<br />\n";

	$sql = "SELECT a.id, a.value, a.codename, a.id_batiment, b.nom FROM batiments AS a, liste_batiments AS b WHERE a.id_province = '".$_SESSION['id_province']."' AND a.value = '0' AND b.id = a.id_batiment";
	$req = sql_query($sql);
	if(sql_rows($req) > 0)
	{
		echo "<FORM METHOD=POST ACTION=\"index.php?p=bsp_sort\">\n";
		echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"3\">\n";

		echo "<select name=\"batiment\">\n";
		
		while ($res = sql_array($req))
		{//prend le batiments
			echo "<option value=\"".$res['id']."\">".$res['nom']."</option>\n"; 
				
		}
		echo "</select><br />\n";


		echo "<INPUT TYPE=\"submit\" value=\"Accelerer pour ".$Prix." magie\">\n";
		echo "</form>\n";
	} else {
		echo "<span class=\"info\">Il n'y a aucun bâtiment en construction!<span>\n";
	}
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège, ou vous êtes de la mauvaise Race!\n"));
}
?>