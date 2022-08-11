<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Tremblement.php
+-----------------------------------------------------
|Description:	Tromblement
+-----------------------------------------------------
|Date de cr�ation:				11.02.06
|Derni�re modification:
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '20'; 
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

echo "<fieldset><legend>".$Nom."</legend>\n";

if(sort_available($res->batint, $res->race, $Joueur->race))
{
	//Traitement
	if(isset($_POST['parametre']))
	{
		//Verifie si on a assez de magie
		if(check_craft($_SESSION['id_province'], $Prix))
		{//On a pass�
			//Param�tre du sort
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
					$UpProv = "UPDATE provinces SET muraille_pierre = (muraille_pierre-".$Variable."), muraille_bois = (muraille_bois-".$Variable.") WHERE id = '".$res->id."';";
					sql_query($UpProv);

					//Message
					$MessageS = "La province ".$_SESSION['name_province']." du joueur ".$Joueur->pseudo." vous a lanc� un Tremblement sur votre province ".$res->name."! Jusqu'� ".$Variable." Plaques ont �t� d�truites!";
					$Message = bw_info("La province ".$res->name." du joueur ".$res->pseudo." � suffit le Tremblement, et jusqu'� ".$Variable." Plaques ont �t� d�truites!<br />\n");
				}
				else
				{//Echou�
					//Message
					$Message = bw_info("Votre sortil�ge est lanc�, mais une protection entour la province ennemie et d�joue votre offensive!<br />\n");
					$MessageS = "Le joueur ".$Joueur->pseudo." vous a lanc� un sortil�ge de ".$Nom.", mais une protection a entour� votre province ".$res['name']."!";
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
			$Message = bw_error("Vous n'avez pas assez de magie pour lancer ce sortil�ge!<br />\n");
		}
	}
	if(isset($Message)) echo $Message;

	//Demande de param�tres
	echo $Titre_Choix;
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";
	
	echo "<select name=\"parametre\">\n";

	//S�lectionne des provinces ciblable
	$MinPower = floor($Joueur->puissance/100)*$CONF['relation_attack_power'];

	$sql = "SELECT ally_id, id, pseudo FROM joueurs WHERE puissance >= '".$MinPower."' AND pseudo <> '".$Joueur->pseudo."'";
	$req = sql_query($sql);
	while ($res = mysql_fetch_array($req))
	{//Prend chaque h�ros assez fort
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
	
	echo "<INPUT TYPE=\"submit\" value=\"Voler ".$Variable." ressources pour ".$Prix." magie.\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\">\n\n";

	echo "</FORM>\n\n";


} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le b�timent n�cessaire pour lancer ce sortill�ge!\n"));
}
?>