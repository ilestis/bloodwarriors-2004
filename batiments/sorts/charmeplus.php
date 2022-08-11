<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Charme.php
+-----------------------------------------------------
|Description:	Sort Charme
+-----------------------------------------------------
|Date de cr�ation:				12.06.06
|Derni�re modification:
+---------------------------------------------------*/
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '11'; 
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

if(sort_available($res->batint, $res->race, $Joueur->race)) {
{
	//Traitement
	if(isset($_POST['parametre']))
	{
		//Param�tre du sort
		$Parametre = clean($_POST['parametre']);

		//Verifie l'existance de la province, et prend ses ressources
		$sql = "SELECT id, id_joueur, name, peasant FROM provinces WHERE id = '".$Parametre."'";
		$req = sql_query($sql);

		if(mysql_num_rows($req) == 1)
		{//La province existe

			//Verifie si on a assez de magie
			if(check_craft($_SESSION['id_province'], $Prix))
			{//On a pass�

				$res = sql_object($req);

				//Verifie s'il a pas d�j� le minimum de paysans
				if($res->peasant > $CONF['paysans_min'])
				{
					//Verifie si on a pas d�j� trop de paysans...
					$sqlp = "SELECT peasant FROM provinces WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id = '".$_SESSION['id_province']."'";
					$reqp = sql_query($sqlp);
					$resp = sql_object($reqp);
					
					$Max = ceil(func_MaxPop($_SESSION['id_joueur'], $_SESSION['id_province'], $CONF[$Joueur->race.'_paysans_max'])*1.1);
					if($resp->peasant+$Variable <= $Max)
					{
						if(bw_protections($res->id))
						//Verifie les protections de la province ennemie
						{
							//Effet
							$Up_me_1 = "UPDATE provinces SET peasant = (peasant+".$Variable.") WHERE id = '".$_SESSION['id_province']."'";
							sql_query($Up_me_1);

							sql_query("UPDATE temp_paysans SET nombre = (nombre+".$Variable.") WHERE section = '0' AND id_province = '".$_SESSION['id_province']."'");

							//Update cibl�
							sql_query("UPDATE provinces SET peasant = (peasant-".$Variable.") WHERE `id` = '".$Parametre."'");

							sql_query("UPDATE temp_paysans SET nombre = (nombre-".$Variable.") WHERE section = '0' AND id_province = '".$Parametre."'");

							//Message
							$MessageS = "Le joueur ".$Joueur->pseudo." vous a vol� ".$Variable." paysans gr�ce au sort de ".$Nom.", votre province ".$res->name." se trouve d�rob�e!";
							$Message = bw_info("Vous avez vol� ".$Variable." paysans � la province ".$res->name.".");

						}
						else
						{
							//Message
							$Message = bw_info("Votre sortil�ge est lanc�, mais une protection entour la province ennemie et d�joue votre offensive!<br />\n");
							$MessageS = "La province ".$_SESSION['name_province']." du joueur ".$Joueur->pseudo." vous a lanc� un sortil�ge de Charme+, mais une protection a entour� votre province ".$res->name." et a proteg� vos ressources.";
						}

						//Envoit le message
						send_message(999999995, $res->id_joueur, $MessageS, '1');
					}
					else
					{//Trop de pop
						$Message = bw_error("Vous avez d�j� atteind votre limite de population!<br />\n");
					}
				}
				else
				{//Not enougth pop
					$Message = bw_error("Vous ne pouvez pas charmer cette province car elle ne poss�de pas assez de paysans.<br />\n");
				}
			}
			else
			{//cRAFt
				$Message = bw_error("Vous n'avez pas assez de magie pour lancer ce sortil�ge!<br />\n");
			}
		}
		else
		{//Province
			$Message = bw_error("Cette province n'existe pas.<br />\n");
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
			$sqlp = "SELECT id, name FROM provinces WHERE id_joueur = '".$res['id']."'";
			$reqp = sql_query($sqlp);
			while($resp = sql_object($reqp))
			{
				echo "<option value=\"".$resp->id."\" ".(isset($_POST['parametre']) && $_POST['parametre'] == $resp->id ? "selected=\"selected\"" : '').">".$res['pseudo']."[".$resp->name."]</option>\n";

			}
		}
	}
	echo "</select><br /><br />\n";
	echo "<INPUT TYPE=\"submit\" value=\"Voler ".$Variable." paysans pour ".$Prix." magie.\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\"><br />\n";

	
	echo bw_info("Attention, vous ne pouvez pas posseder plus de 10% suppl�mentaire que votre popmax en volant.");
	echo "</FORM>\n";	
} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le b�timent n�cessaire pour lancer ce sortill�ge!\n"));
} ?>