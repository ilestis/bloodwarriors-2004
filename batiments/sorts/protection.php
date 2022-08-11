<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Protection.php
+-----------------------------------------------------
|Description:	Sort Protection
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
$id_sort = 4;
$sql = "SELECT * FROM `liste_sorts` WHERE id = '".$id_sort."'";
$req = sql_query($sql);
$res = mysql_fetch_array($req);

//Variables
$Prix	= $res['cout'];
$Nom	= $res['nom'];
$Variable = $res['variable'];

if(sort_available($res->batint, $res->race, $Joueur->race)) {

	//Variables de max
	$Max_Protections = $CONF['sort_protections_max'];

	//Verifie l'existance de la cathedrale
	if(bw_batiavailable(36))
		$Max_Protections += $CONF['bati_capa_cathedrale'];

	// Bonus de protections sorciers
	if($Joueur->race == 6) $Max_Protections *= floor($CONF['bonus_sorciers_2']);

	// Bonus alliance
	if(func_RaceMajorite($Joueur->ally_id) == 6) $Max_Protections *= floor($CONF['bonus_sorciers_2']);

	echo "<fieldset><legend>".$Nom."</legend>\n";

	if (isset($_POST['nombre'])) {//On a ajouté
		//Variable
		$Nombre	=	clean($_POST['nombre']);
		$My_Craft = $Res_Ressources['craft'];

		//On va chercher nos protections
		$sql = "SELECT id FROM temp_sorts WHERE id_province = '".$_SESSION['id_province']."' AND id_sort = '".$id_sort."'";
		$req = sql_query($sql);
		$My_Protec = sql_rows($req);
		$Cout = $Prix*$Nombre;

		//On regarde le nombre qu'on a
		if (($My_Protec + $Nombre) > $Max_Protections) {//Trop
			$Message = bw_info("Vous demandez d'ajouter plus de bouclier que le nombre maximal autorisé ".$Max_Protections.").<br />\n");
		}
		else
		{//Ok
			if(check_craft($_SESSION['id_province'], $Prix))
			{
				for($i = 1; $i <= $Nombre; $i++) {
					//Met a jour
					$ins = "INSERT INTO temp_sorts VALUES('', '".$_SESSION['id_province']."', '".time()."', '".$id_sort."', '10', '1')";
					sql_query($ins);
				}

				$Message = bw_info("Vos protections ont été mises en réserve.<br />\n");
			}
			else {//Pas assez de magie
				$Message = bw_error("Vos efforts sont en vain car vous n'avez pas assez de magie!<br />\n");
			}
		}
	}
	if(isset($Message)) echo $Message;

	echo "Choisissez le nombre de protection à ajouter:<br />\n";

	$sql = "SELECT id FROM temp_sorts WHERE id_province = '".$_SESSION['id_province']."' AND id_sort = '".$id_sort."'";
	$req = sql_query($sql);
	$My_Protec = sql_rows($req);

	if ($My_Protec < $Max_Protections) {
		//On peut en ajouter
		echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";

		$Max = $Max_Protections-$My_Protec;
		$Min = 1;

		echo "<select name=\"nombre\">\n";
		for ($x = $Min; $x <= $Max; $x++) {
			echo "<option value=\"".$x."\">".$x."</option>\n";
		}
		echo "</select><br />\n";

		echo "<INPUT TYPE=\"submit\" value=\"Ajouter x protections au prix de ".$Prix." magie par protection.\">\n";
		echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"4\">\n";

		echo "</FORM>\n";
	}
	else {
		echo "Vous avez déjà le nombre maximal de protections autorisés!<br />\n";
	}

	echo "<span class=\"info\">En stock: ".$My_Protec."/".$Max_Protections."</span><br />\n";
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège, ou vous êtes de la mauvaise Race!\n"));
}
?>