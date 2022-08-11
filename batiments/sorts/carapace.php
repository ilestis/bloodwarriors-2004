<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Carapace.php
+-----------------------------------------------------
|Description:	Sort Carapace
+-----------------------------------------------------
|Date de création:				12.06.06
|Dernière modification:
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '5'; 

//On selectionne les valeurs du sort
$sql = "SELECT * FROM `liste_sorts` WHERE id = '".$Craft_ID."'";
$req = sql_query($sql);
$res = sql_object($req);

//Variables
$Prix			= $res->cout;
$Nom			= $res->nom;
$Variable		= $res->variable;
$Cible			= $res->cible;
$Titre_Choix	= $res->titre_choix;
$Temps			= $res->temps;

//Sort: Carapace
// Ajoute une valeur dans la table temp_sorts avec comme boost id : 3
//  1: attaque
//  2: defense
//  3: ville
//  4: ....
//---------

echo "<fieldset><legend>".$Nom."</legend>\n";

if(sort_available($res->batint, $res->race, $Joueur->race)) {
	if (isset($_POST['parametre'])) 
	{//On lance le sort

		//Verifie si on pas déjà le sort en réserve
		$sql = "SELECT `ID` FROM temp_sorts WHERE id_province = '".$_SESSION['id_province']."' AND `id_sort` = '".$Craft_ID."'";
		$req = sql_query($sql);

		if (mysql_num_rows($req) == 1) 
		{//Déjà le sort
			$Message =  bw_info("Vous avez déjà un sortilège de ".$Nom." en réserve.");
		}
		else 
		{	
			if(check_craft($_SESSION['id_province'], $Prix))
			{//On a passé
				$Temps = 3600*$Temps;

				//Bonnus Anges
				if($Joueur->race == 1)
				{
					$Temps *= $CONF['bonus_anges_1'];
				}

				$Temps_Insert = time()+$Temps;

				$ins = "INSERT INTO temp_sorts VALUES ('', '".$_SESSION['id_province']."', '".$Temps_Insert."', '".$Craft_ID."', '".$Cible."', '".$Variable."')";
				sql_query($ins);

				$Message =  bw_info("Une ".$Nom." entour votre province, la rendant ainsi plus solide face aux attaques ennemies jusqu'au ".date($CONF['game_timeformat'], $Temps_Insert)."!<br />\n");
			}
			else
			{
				$Message = bw_error("Vous n'avez pas assez de magie!");
			}
		}

	}

	if(isset($Message)) echo $Message;

	//Demande de paramètres
	echo $Titre_Choix;

	//Verifie si on pas déjà le sort en réserve
	$sql = "SELECT `ID` FROM temp_sorts WHERE id_province = '".$_SESSION['id_province']."' AND `id_sort` = '".$Craft_ID."'";
	$req = sql_query($sql);

	if (mysql_num_rows($req) == 1) 
	{//Déjà le sort
		echo bw_info("Vous avez déjà un sortilège de ".$Nom." en réserve.");
	} else {

		echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";
	?>
		<br />
		Invoque une <?php echo $Nom; ?> autour de votre province, d'une valeur de <?php echo $Variable; ?> points pour une durée de <?php echo $Temps; ?> heures.<br />


		
		<INPUT TYPE="submit" value="Lancer une carapace pour <?php echo $Prix; ?> magie.">
		<INPUT TYPE="hidden" name="idsort" value="<?php echo $Craft_ID; ?>">
		<input type="hidden" name="parametre" value="go">

		</FORM>
		
	<?php
	}
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège, ou vous êtes de la mauvaise Race!\n"));
}
	?>