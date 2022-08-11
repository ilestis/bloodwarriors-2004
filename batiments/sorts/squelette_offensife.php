<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Squelette.php
+-----------------------------------------------------
|Description:	Squelette d'un sort
+-----------------------------------------------------
|Date de création:				11.02.06
|Dernière modification:
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '2'; 

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
$BatInt			= $res->batint;
$Description	= stripslashes($res->description);

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
			$sql = "SELECT a.id, a.id_joueur, a.name, b.pseudo FROM provinces AS a LEFT JOIN joueurs AS b ON b.id = a.id_joueur WHERE id = '".$Parametre."'";
			$req = sql_query($sql);

			if(mysql_num_rows($req) == 1)
			{//La province existe
			
				$res = sql_object($req);

				if(bw_protections($res->id))
				//Verifie les protections de la province ennemie
				{
					//Effet

					//Message
					$MessageS = "La province ".$_SESSION['name_province']." du joueur ".$Joueur->pseudo." vous a lancé un sort de ".$Nom.", votre province ".$res->name." se trouve dérobée!";
					$Message = bw_info("Vous avez pillé ... à la province ".$res->name.".<br />\n");

				}
				else
				{//Echoué
					//Message
					$Message = bw_info("Votre sortilège est lancé, mais une protection entour la province ennemie et déjoue votre offensive!<br />\n");
					$MessageS = "Le joueur ".$Joueur->pseudo." vous a lancé un sortilège de ".$Nom.", mais une protection a entouré votre province ".$res['name']."!";
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
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">
	<table width=\"100%\" style=\"border:0px;\">
	<tr>
		<td width=\"80px\" valign=\"top\"><img src=\"./images/sorts/".$Craft_ID.".png\" alt=\"$Titre_Choix\" title=\"$Titre_Choix\" /></td>
		<td valign=\"top\">
			$Description <br />
			<select name=\"parametre\">
			".liste_provinces_alliees($Joueur)."
			</select><br /><br />


			<INPUT TYPE=\"submit\" value=\"Lancer un ".$Nom." pour ".$Prix." magie.\">
			<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\">
		</td>
	</tr>
	</table>
	</FORM>\n";

} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège!\n"));
}
?>