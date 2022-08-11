<?php
/*----------------------[TABLEAU]---------------------
|Nom:			FuiteDeMagie.php
+-----------------------------------------------------
|Description:	Sort Fuite de magie
+-----------------------------------------------------
|Date de création:				16.01.07
|Dernière modification:			
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '13'; 
$Allowed = FALSE;


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
$BatInt			= $res->batint;

echo "<fieldset><legend>".$Nom."</legend>\n";

if(sort_available($res->batint, $res->race, $Joueur->race))
{
	//Traitement
	if(isset($_POST['parametre']))
	{
		//Verifie si on a assez de magie
		if(check_craft($_SESSION['id_province'], $Prix))
		{//On a passé
			//Paramètre du sort
			$Parametre = clean($_POST['parametre']);

			//Verifie l'existance de la province
			$sql = "SELECT id, id_joueur, name, protections FROM provinces WHERE id = '".$Parametre."'";
			$req = sql_query($sql);

			if(mysql_num_rows($req) == 1)
			{//La province existe
			
				$res = sql_object($req);

				if(bw_protections($res->id))
				//Verifie les protections de la province ennemie
				{
					$NewMagie = $res['craft']*(100-$Pourcent);
					
					$Up_His_stats = "UPDATE provinces SET `craft` = ".$NewMagie." WHERE `id` = '".$Parametre."'";
					sql_query($Up_His_stats);

					//Message
					$MessageS = "La province ".$_SESSION['name_province']." du joueur ".$Joueur->pseudo." a réduit vos réserves de magie de ".$Pourcent."% grâce sort de ".$Nom.", votre province ".$res['name']." se trouve dérobée!";

					$Message = bw_info("Vous avez réduit les réserves de magie de ".$Pourcent."% de la province ".$res['name']."!<br />\n");

				}
				else
				{//Echoué
					//Message
					$Message = bw_info("Votre sortilège est lancé, mais une protection entour la province ennemie et déjoue votre offensive!<br />\n");
					$MessageS = "Le joueur ".$Joueur->pseudo." vous a lancé un sortilège de PickPocket, mais une protection a entouré votre province ".$res['name']." et a protegé vos ressources.";
				}

				//Envoit le message
				send_message(999999995, $res->id_joueur, $MessageS, '1');
			}
			else
			{//Province inexistante
				$Message = bw_error("Cette province n'existe pas!<br />\n");
			}
		}
		else
		{
			//Message
			$Message = bw_error("Vous n'avez pas assez de magie pour lancer ce sortilège!<br />\n");
		}
	}

	if(isset($Message)) echo $Message;

	//Demande de paramètres
	echo $Titre_Choix;
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";
	
	echo "<select name=\"parametre\">\n";

	//Sélectionne des provinces ciblable
	$MinPower = floor($Joueur->puissance/100)*$CONF['relation_attack_power'];

	$sql = "SELECT ally_id, id, pseudo FROM joueurs WHERE puissance >= '".$MinPower."' AND pseudo <> '".$Joueur->pseudo."'";
	$req = sql_query($sql);
	while ($res = mysql_fetch_array($req))
	{//Prend chaque héros assez fort
		if (($Joueur->ally_id == 0) || ($Joueur->ally_id != $res['ally_id'])) 
		{
			//On peut le cibler alors on va chercher ses provinces
			$sqlp = "SELECT name FROM provinces WHERE id_joueur = '".$res['id']."'";
			$reqp = sql_query($sqlp);
			while($resp = sql_object($reqp))
			{
				echo "<option value=\"".$resp->id."\">".$res['pseudo']."[".$resp->name."]</option>\n";

			}
		}
	}
	echo "</select>\n\n";

	echo "<br />\n\n";
	
	echo "<INPUT TYPE=\"submit\" value=\"Réduire de ".$Variable."% la magie pour ".$Prix." magie.\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\">\n\n";

	echo "</FORM>\n\n";
	
	echo bw_info("Attention, en vous volez ne volez que de l'or, de la nourriture, du bois et de la pierre, avec un maximum de ".$Variable." pièces de chaque. Si l'Héros ciblé n'a que un or, vous n'en volerez que un.\n");
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège!\n"));
}
?>