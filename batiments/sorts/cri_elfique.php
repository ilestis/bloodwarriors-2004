<?php
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '37'; 
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
$Temps			= $res->temps;
$BatInt			= $res->batint;
$Race			= $res->race;
$Description	= stripslashes($res->description);

echo "<fieldset><legend>".strtoupper($Nom)."</legend>\n";

if(sort_available($res->batint, $res->race, $Joueur->race))
{
	if (isset($_POST['parametre'])) 
	{//On lance le sort
		$Parametre = clean($_POST['parametre']);
		
		//Verifie l'existance de la province
		$sql = "SELECT a.id, a.id_joueur, a.name, b.pseudo, b.ally_id FROM provinces AS a LEFT JOIN joueurs AS b ON b.id = a.id_joueur WHERE a.id = '".$Parametre."'";
		$req = sql_query($sql);

		if(sql_rows($req) == 1)
		{//La province existe
			$res = sql_array($req);
			// Verifie a nous ou ally
			if ((($Joueur->ally_id == 0) || ($Joueur->ally_id != $res['ally_id'])) && $res['id'] != $_SESSION['id_joueur']) 
			{

				//Verifie si on pas d�j� le sort en r�serve
				$sql = "SELECT `ID` FROM temp_sorts WHERE id_province = '".$Parametre."' AND `id_sort` = '".$Craft_ID."'";
				$req = sql_query($sql);

				if (sql_rows($req) == 1) 
				{//D�j� le sort
					$Message = bw_error("Cette province poss�de d�j� un sortil�ge de ".$Nom." en r�serve!<br />");
				}
				else 
				{	
					if(check_craft($_SESSION['id_province'], $Prix))
					{//On a le test de magie

						if(bw_protections($res->id))
						{//Verifie les protections de la province ennemie
						
							//Bonnus Anges
							if($Joueur->race == 1)
							{
								$Temps *= $CONF['bonus_anges_1'];
							}
							$Temps_Insert = time()+($Temps*3600);

							$ins = "INSERT INTO temp_sorts VALUES ('', '".$Parametre."', '".$Temps_Insert."', '".$Craft_ID."', '".$Cible."', '".$Variable."')";
							sql_query($ins);

							$Message = bw_info("Le sortill�ge de ".$Nom." accompagne � pr�sent les unit�s de la province ".$res['name']." pour une dur�e de ".$Temps." heures!<br />\n");
							$MessageS = "Le joueur ".$Joueur->pseudo." vous a lanc� un sort de ".$Nom." sur votre province ".$res['name']."!";
						} else {
							// Echec
							$Message = bw_info("Votre sortil�ge est lanc�, mais une protection entour la province ennemie et d�joue votre offensive!<br />\n");
							$MessageS = "La province ".$_SESSION['name_province']." du joueur ".$Joueur->pseudo." vous a lanc� un sortil�ge de ".$Nom.", mais une protection a entour� votre province ".$res['name'].".";
						}
						//Envoit le message
						send_message(999999995, $res['id_joueur'], $MessageS, '1');
					}
					else
					{
						$Message = bw_error("Vous n'avez pas assez de magie!<br />\n");
					}
				}
			} else {
				$Message = bw_error("Vous ne pouvez pas cibler cette province!<br />\n");
			}
		} else {
			$Message = bw_error("Cette province n'existe pas!<br />\n");
		}
	}

	//Demande de param�tres
	echo "<form method=\"post\" action=\"?p=bsp_sort\">
	<table width=\"100%\" style=\"border:0px;\">
	<tr>
		<td width=\"80px\" valign=\"top\">
			<img src=\"./images/sorts/".$Craft_ID.".png\" alt=\"$Titre_Choix\" title=\"$Titre_Choix\" />
		</td>
		<td valign=\"top\" style=\"text-align:left;\">
			$Description <br /><br />
			".(!empty($Message) ? $Message : null)."
			<select name=\"parametre\">
			".liste_provinces_ennemies($Joueur)."
			</select>


			<INPUT TYPE=\"submit\" value=\"Lancer un ".$Nom." pour ".$Prix." magie.\">
			<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\">
		</td>
	</tr>
	</table>
	</form>\n";
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le b�timent n�cessaire pour lancer ce sortill�ge, ou vous �tes de la mauvaise Race!\n"));
} 
?>