<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Pickpocket.php
+-----------------------------------------------------
|Description:	Sort pickpocket
+-----------------------------------------------------
|Date de création:				12.02.06
|Dernière modification:			20.02.06
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//On selectionne les valeurs du sort
$sql = "SELECT * FROM `liste_sorts` WHERE id = '10'";
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
	//Traitement
	if(isset($_POST['parametre']) && $_POST['parametre'] != "")
	{
		//Allons chercher nos paramètres de province
		$sql = "SELECT gold, food, mat, craft FROM provinces WHERE id = '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."'";
		$req = sql_query($sql);
		$resp = mysql_fetch_array($req);
		$My_Gold = $resp['gold'];
		$My_Food = $resp['food'];
		$My_Mat = $resp['mat'];
		$My_Craft = $resp['craft'];

		//Verifie si on a assez de magie
		if(check_craft($_SESSION['id_province'], $Prix))
		{
			$Parametre = clean($_POST['parametre']);

			//Verifion si la province existe, et si on peut cibler ce joueur.
			$sql = "SELECT id_joueur, name, gold, food, mat FROM provinces WHERE `id` = '".$Parametre."'";
			$req = sql_query($sql);
			
			if(mysql_num_rows($req) == 1)
			{//Existe
				$respro = mysql_fetch_array($req);

				//Verifie les protection
				if(!bw_protections($res->id)) {//Une protection se déclanche
					$Message = bw_error("Votre sortilège à été déjoué par une protection sur la province!<br />\n");

					$MessageS = "La province ".$_SESSION['name_province']." du joueur ".$Joueur->pseudo." vous a lancé un sortilège de ".$Nom.", mais une protection a entouré votre province ".$respro['name']." et a protegé vos ressources.";

				} else {
					//Ca passe
	//Calcule les gains
					//Joueur ciblé
					$His_Gold	= $respro['gold']		- $Variable;
					$His_Food	= $respro['food']		- $Variable;
					$His_Mat	= $respro['mat']		- $Variable;

					//Mes ressources
					$My_Gold	= $resp['gold'] + $Variable;
					$My_Food	= $resp['food'] + $Variable;
					$My_Mat		= $resp['mat'] + $Variable;

					if ($His_Gold	< 0) { $His_Gold = 0; $My_Gold = $resp['gold'] + $respro['gold']; }
					if ($His_Food	< 0) { $His_Food = 0; $My_Food = $resp['food'] + $respro['food']; }
					if ($His_Mat	< 0) { $His_Mat = 0; $My_Mat = $resp['mat']	+ $respro['mat']; }

					if(bw_batiavailable('entrepot', false))
						$Bonnus_Stack = $CONF['bati_capa_entrepot'];

					if($My_Gold > $CONF['province_max_ressources'])
						$My_Gold = $CONF['province_max_ressources'] * $Bonnus_Stack;
					if($My_Food > $CONF['province_max_ressources'])
						$My_Food = $CONF['province_max_ressources'] * $Bonnus_Stack;
					if($My_Mat > $CONF['province_max_ressources'])
						$My_Mat = $CONF['province_max_ressources'] * $Bonnus_Stack;
			
					
					//Update stats
					$Up_My_stats = "UPDATE provinces SET `gold` = ".$My_Gold.", food = ".$My_Food.", mat = ".$My_Mat." WHERE `id` = '".$_SESSION['id_province']."'";
					sql_query($Up_My_stats);
					
					$Up_His_stats = "UPDATE provinces SET gold = ".$His_Gold.", food = ".$His_Food.", mat = ".$His_Mat." WHERE `id` = '".$Parametre."'";
					sql_query($Up_His_stats);

					//Message
					$MessageS = "La province ".$_SESSION['name_province']." du joueur ".$Joueur->pseudo." vous a volé des ressources grâce au sort de ".$Nom.", votre province ".$respro['name']." se trouve dérobée!";

					$Message = bw_info("Vous avez volé des ressources de la province ".$respro['name']."!<br />\n");
				}
				send_message(999999995, $res['id_joueur'], $MessageS, 1);
			} else {
				$Message = bw_error("Cette province n'existe pas<br />\n");
			}
		} else {
			$Message = bw_error("Vous n'avez pas assez de magie.<br />\n");
		}

	}
	if(isset($Message)) echo $Message;

	//Demande de paramètres
	echo "Choisissez une province à voler:";
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";

	echo "<select name=\"parametre\">\n";
	echo "<option name=\"\">-Sélectionner dans la liste-</option>\n";


	//Puissance minimale 
	$Min_Puissance = min_attack_power($Joueur->puissance, $CONF[$Joueur->race.'_attaque_min'], $Joueur->race, $CONF['bonus_anges_2']);

	$sql = "SELECT ally_id, id, pseudo FROM joueurs WHERE puissance >= '".$Min_Puissance."' AND id <> '".$_SESSION['id_joueur']."'";
	$req = sql_query($sql);
	while ($resal = mysql_fetch_assoc($req))
	{//Prend chaque héros assez fort
		if (($Joueur->ally_id == 0) || ($Joueur->ally_id != $resal['ally_id'])) {
			//On peut le cibler alors on va chercher ses provinces
			$sqlp = "SELECT name, id FROM provinces WHERE id_joueur = '".$resal['id']."'";
			$reqp = mysql_query($sqlp);
			while($resp = mysql_fetch_array($reqp))
			{
				echo "<option value=\"".$resp['id']."\">".$resal['pseudo'].": ".$resp['name']."</option>\n";

			}
		}
	}
	?>
	</select>

	<br />

	<INPUT TYPE="submit" value="Voler <?php echo $Variable; ?> ressources pour <?php echo $Prix; ?> magie.">
	<INPUT TYPE="hidden" name="idsort" value="10">

	</FORM>

	<span class="info">Attention, en vous volez ne volez que de l'or, de la nourriture, du bois et de la pierre, avec un maximum de <?php echo $Variable; ?> pièces de chaque. Si l'Héros ciblé n'a que un or, vous n'en volerez que un.</span><br />
	<?php
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège!\n"));
}