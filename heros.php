<?php
/*
+---------------------
|Nom: La recherche
+---------------------
|Description: Cette page affiche les statistiques des joueurs
+---------------------
|Date de création: Juillet 04
|Date du premier test: Juillet 04
|Dernière modification: 11 Jul 2007
+---------------------
*/
//inclu le profil
include ('./profil.php'); 
bw_tableau_start("Recherche");

//Cherche si on a la guilde de repérage
$GuildeReperage = bw_batiavailable('guildedereperage', false);

// Tableau de recherche
$Title = "Recherche direct";
$Message =	"<form method=\"post\" action=\"index.php?p=search2\">\n"
			. "Pseudo ou Province: <INPUT TYPE=\"TEXT\" NAME=\"cible\" size=\"30\">\n"
			. "	<INPUT TYPE=\"submit\" value=\"Rechercher!\">\n"
			. "</FORM><br />\n";
bw_fieldset($Title, $Message);

// Si on a définit quelque chose dans la recherche
if(isset($_POST['cible'])) {

	// Héro
	$sql = "SELECT id, pseudo FROM joueurs WHERE pseudo LIKE '%".clean($_POST['cible'])."%' AND aut > 100000000000000";
	$req = sql_query($sql);
	$Hero = mysql_num_rows($req);

	//Provinces
	$sql = "SELECT id, name FROM provinces WHERE name LIKE '%".clean($_POST['cible'])."%'";
	$req2 = sql_query($sql);
	$Province = mysql_num_rows($req2);

	//Qu'un seul? Redirection
	if($Hero == 1 && $Province == 0) { // Héro
		$res = sql_object($req);
		redirection("?p=search2&joueurid=".$res->id, 10);
	} else if($Hero == 0 && $Province == 1) { // Province
		$res = sql_object($req2);
		redirection("?p=search2&provinceid=".$res->id, 10);
	} else { // Affiche la liste

		// Héro
		if ($Hero > 0) {// Plusieurs pseudos
			echo "<strong>Résultat sur la liste des Héros:</strong><br />";
			while ($res = mysql_fetch_array($req)) {
				echo "<a href=\"index.php?p=search2&joueurid=".$res['id']."\">".$res['pseudo']."</a><br />\n";
				$Id = $res['id'];
			}
		} else {
			echo "Aucun joueur ne possède le nom saisit.<br />\n";
		}	
		echo "<br />\n";

		if ($Province > 0) {//Plusieurs pseudos
			echo "<strong>Résultat sur la liste des Provinces:</strong><br />";
			while ($res = mysql_fetch_array($req2)) {
				echo "<a href=\"index.php?p=search2&provinceid=".$res['id']."\">".$res['name']."</a><br />\n";
				$Id = $res['id'];
			}
		} else {
			echo "Aucune province ne possède le nom saisit.<br />\n";
		}
	}

}

//echo "hum hum ".$_GET['provinceid']."<br />";
if (isset($_GET['joueurid']) OR isset($_GET['provinceid'])) 
{
	if (isset($_GET['joueurid']) AND !isset($_GET['provinceid'])) 
	{//Il faut prendre ses paramètres et sa province
		$Vision_Id = clean($_GET['joueurid']);
		$sql = "SELECT a.*, b.id AS IdProvince, b.victoires AS Prov_victoires, b.pertes AS Prov_pertes FROM `joueurs` AS a LEFT JOIN provinces AS b ON b.id_joueur = a.id AND b.type_province = '0' WHERE a.`id` = '".$Vision_Id."'";
		$req = sql_query($sql);
		$res = mysql_fetch_array($req);
		$Vision_Id_Province = $res['IdProvince'];

	}

	if (isset($_GET['provinceid']) AND !isset($_GET['joueurid'])) 
	{
		$Vision_Id_Province = clean($_GET['provinceid']);
		$sql = "SELECT a.id AS IdProvince, a.victoires AS Prov_victoires, a.pertes AS Prov_pertes, b.* FROM provinces AS a LEFT JOIN joueurs AS b ON b.id = a.id_joueur WHERE a.`id` = '".$Vision_Id_Province."'";
		$req = sql_query($sql);
		$res = mysql_fetch_array($req);
		$Vision_Id = $res['id'];
	}
	elseif(isset($_GET['joueurid']) AND isset($_GET['provinceid']))
	{//On a les deux paramètres
		$Vision_Id = clean($_GET['joueurid']);
		$Vision_Id_Province = clean($_GET['provinceid']);

		$sql = "SELECT a.*, b.victoires AS Prov_victoires, b.pertes AS Prov_pertes  FROM joueurs AS a, provinces AS b WHERE a.`id` = '".$Vision_Id."' AND b.id = '".$Vision_Id_Province."'";
		$req = sql_query($sql);
		$res = mysql_fetch_array($req);
	}

	//Verifion si ce id existe...
	$sql_Verif = "SELECT `id` FROM joueurs WHERE `id` = '".$Vision_Id."'";
	$req_Verif = sql_query($sql_Verif);
	$nbr_Verif = mysql_num_rows($req_Verif);
	if($nbr_Verif == 0) {
		echo "<h2>Aucun Héros répértorié.</h2><br />\n";
	}
	else
	{
		bw_f_start("Information sur ".$res['pseudo']." ".($res['acceslvl'] == '-1' ? 'En Vacances' : ''));
		?>
		
				<table class="newsmalltable">
				<tr>
					<th valign="top" style="text-align: center;">
						Avatar:<br />
						<?php
						if(isset($res['avatar'])) {//ok
							$Fichier = "images/avatars/".$res['id']."_".$res['avatar'];
							//$Operation = @fopen($Fichier, 'r');
							if (!is_file($Fichier)) { //$Operation) {//N'existe pas
								echo "Aucun avatar\n";
							}
							else { //On l'ouvre
								echo "<img src=\"images/avatars/".$res["id"]."_".$res['avatar']."\" alt=\"".$res['pseudo']."\" title=\"".$res['pseudo']."\">\n";
							}
						}
						else {//aucune
							echo "Aucun avatar\n";
						}
						?>
					</td>
					<td valign="top">
		<?php								//données
		if ($res['ally_id'] == '' OR $res['ally_id'] == 0)	$Alliance = false;
		else { //il a une alliance
			$all = "SELECT name FROM alliances WHERE ally_id = '".$res['ally_id']."'";
			$ret = sql_query($all);
			$nu = mysql_fetch_array($ret);
			$Alliance = $nu['name'];
		}
		//Variables
		$Pseudo = $res['pseudo'];
		$Puissance = $res['puissance'];

		echo "<strong>Données:</strong><br />\n";
		echo "Pseudo: ".$Pseudo."<br />\n";
		echo "Classe: ".return_guilde($res['race'], $_SESSION['lang'])."<br />\n";
		echo "Alliance: ".($Alliance !== false ? "<a href=\"index.php?p=scores&do=allyview&id=".$res['ally_id']."\">".$Alliance."</a>" : "Aucune")."<br />\n";
		echo "Puissance: ".$Puissance."<br />\n";

		$swab = "SELECT name, id FROM provinces WHERE id_joueur = '".$res['id']."'";
		$swaq = sql_query($swab);
		$TmpProvLiens = ""; $ProNbr = 0;
		while ($swas = mysql_fetch_array($swaq)) {
			$TmpProvLiens .= "<a href=\"index.php?p=search2&provinceid=".$swas['id']."&joueurid=".$Vision_Id."\">".$swas['name']."</a>, ";
			$ProNbr+=1;
		}
		echo "Nombre de provinces: ".$ProNbr."<br />\n";
		echo $TmpProvLiens;
		
		echo "<br />\nVictoires/Défaites: ".$res['Prov_victoires']."/".$res['Prov_pertes']."</td>\n";
		
		echo "	<td valign=\"top\">\n";
		if($GuildeReperage == 1)
		{
			echo "<strong>Guilde de repérage:</strong><br />\n";

			if(bw_batiavailable('guildedereperage', false))
			{
				$ressources = mysql_fetch_array(sql_query("SELECT gold, food, mat, craft, muraille_normal, muraille_enchante, muraille_magie FROM provinces WHERE `id` = '".$Vision_Id_Province."'"));
				echo "Or: ".$ressources['gold']."<br />\n";
				echo "Nourriture: ".$ressources['food']."<br />\n";
				echo "Matériaux: ".$ressources['mat']."<br />\n";
				echo "Magie: ".$ressources['craft']."<br />\n";
				echo "Muraille Simple: ".$ressources['muraille_normal']."<br />\n";
				echo "Muraille Enchantée: ".$ressources['muraille_enchante']."<br />\n";
				echo "Muraille d'Énergie: ".$ressources['muraille_magie']."<br />\n";
			} else {
				echo bw_error("Votre Guilde de repérage<br /> est trop endommagée!");
			}
		} echo "<br />\n";
echo "						</td>\n";
echo "					</tr>\n";
echo "					</table>\n";
echo "				</fieldset>\n\n";
echo "				<br />\n";

		//listing des créatures
		//verifie si on a la tarverne
		$ta = "SELECT id FROM batiments WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND id_batiment = '7' AND value = '1'";
		$re = sql_query($ta);
		$tav = mysql_num_rows($re);
		$aex = "SELECT id FROM batiments WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND id_batiment = '42' AND value = '1'";
		$reae = sql_query($aex);
		$ae = mysql_num_rows($reae);
		if($tav == 1 || $ae == 1)
		{//ok	
			// Verifie si on a notre CdAE  en premier
			if(bw_batiavailable(42)) {
				
				echo "<fieldset><legend>Centre des Affaires Étrangères</legend>\n";
				
				echo "<table class=\"newsmalltable\">
					<tr>
						<th>Nom</th><th>Puissances</th><th>Nombre</th></tr>
					<tr>";
				$sql = "SELECT COUNT(a.id) AS TotGrp, a.* FROM armees AS a WHERE a.id_joueur = '".$Vision_Id."' AND a.id_province = '".$Vision_Id_Province."' GROUP BY a.ID_creature ORDER BY a.ID_creature";
				$req = sql_query($sql);
				while($res = sql_object($req)) {
					echo "<tr>
						<td>".$res->nom."</td>
						<td>[".($res->power_1+$bonusattaquant['bonus_1'])."/".($res->power_2+$bonusattaquant['bonus_21'])."/"
						.($res->power_3+$bonusattaquant['bonus_3'])."/".($res->power_4+$bonusattaquant['bonus_4'])."]</td>
						<td>".$res->TotGrp."</td>
					</tr>\n";
				}
				echo "</table>\n";

				echo "</fieldset><br />\n";

			} 
			
			// Sinon, regarde la tarverne
			else
			{
				if(bw_batiavailable(7))
				{
					$Pourcent = 50;
					echo "<fieldset><legend>Taverne: Listing des unités (".$Pourcent."%)</legend>\n";
					// Bonnus de race
					$bonusatt = "SELECT bonus_1, bonus_2, bonus_3, bonus_4 FROM `info_races` WHERE `id_race` = '".$Joueur->race."'";
					$bonusatta = sql_query($bonusatt);
					$bonusattaquant = sql_array($bonusatta);

					//nombre d'unités
					$sql = "SELECT id FROM armees WHERE id_joueur = '".$Vision_Id."' AND id_province = '".$Vision_Id_Province."'";
					$req = sql_query($sql);
					$tuple = mysql_num_rows($req);

					?>
					<table class="newsmalltable">
					<tr>
						<th colspan="2">Nom / [Puissances] </th></tr>
					<tr>
					<?php
					$start = 0;
					$end = ceil($tuple/2);
					$cpt = 2; //commence pair
					$sql = "SELECT * FROM armees WHERE id_joueur = '".$Vision_Id."' AND id_province = '".$Vision_Id_Province."' ORDER BY puissance LIMIT 0, ".$end."";
					$req = sql_query($sql);
					while($unite = mysql_fetch_array($req))
					{//prend chaque unités(50%)
						$cpt ++; //passe a impaire

						if ($cpt%2 == 1) echo "<tr>"; //impair donc nouvelle ligne

						echo "		<td class=\"newsmallcontenu\">".$unite['nom']." / ";
						echo "[".($unite['power_1']+$bonusattaquant['bonus_1'])."/".($unite['power_2']+$bonusattaquant['bonus_21'])."/";
						echo ($unite['power_3']+$bonusattaquant['bonus_3'])."/".($unite['power_4']+$bonusattaquant['bonus_4'])."]";
						echo "</td>";

						if ($cpt%2 == 0) echo "</tr>\n"; //pair donc fin de ligne
					}//While end
					echo "	</tr>\n	</table>\n";
				}//Fin Listing créature
				else
				{
					echo bw_error("Votre Guilde de repérage est trop endomagé pour être utilisé!");
				}
				echo "</fieldset><br />\n";
			}
		}

		//Centre diplomatique
		//A-t'on le centre diplomatique
		$sql = "SELECT id FROM batiments WHERE id_batiment = '17' AND id_province = '".$_SESSION['id_province']."' AND value = '1'";
		$req = sql_query($sql);
		$nbr = mysql_num_rows($req);
		
		if($nbr == 1)
		{
			$res = mysql_fetch_array($req);
			
			echo "<fieldset><legend>Centre Diplomatique</legend>\n";
			//Cherche la vie total du batiment
			if(bw_batiavailable(17))
			{
				echo bw_info("Voici une liste des bâtiments construit par l'ennemi, avec les points de vie restant à ceux-ci.");

				echo "<table class=\"newsmalltable\">\n";
				echo "<tr><th>Bâtiment</th><th>Niveau</th><th>État</th><th>Vie</th></tr>\n";
				$bsql = "SELECT a.*, b.nom, b.niveau FROM batiments AS a LEFT JOIN liste_batiments AS b ON b.id = a.id_batiment WHERE a.id_province = '".$Vision_Id_Province."' AND a.id_batiment < '300' ORDER BY a.time ASC";
				$breq = sql_query($bsql);
				while($bres = mysql_fetch_array($breq))
				{
					//Chaque bâtiment
					echo "<tr>\n";
					echo "<td>";
					
					echo bw_popup($bres['nom'], 'batiment', $bres['id_batiment']);
					//echo "<a href=\"info_popup.php\" title=\"Information sur le bâtiment\" target=\"info_popup\" onclick=\"window.open('info_popup.php?do=batiment&id=".$bres['id_batiment']."','info_popup','height=".$CONF['popup_height']."px, width=".$CONF['popup_width']."px, resizable=yes');return false;\">".$bres['nom']."</a>";
					echo "</td>\n";
					
					//Niveau
					echo "<td>".bw_province_state($bres['niveau'])."</td>\n";

					//Construit/en c
					echo "<td>";
					if($bres['value'] == 1) echo "Construit";
					elseif($bres['value'] == 0) echo "En construction";
					elseif($bres['value'] == 3) echo "En réparation";
					echo "</td>\n";

					//Vie
					echo "<td>";
					echo $bres['life'].'/'.$bres['life_total'];
					echo "</td>\n";


					echo "</tr>\n";
				}
				echo "</table><br />\n";
			}
			else
			{
				echo bw_error("Votre Centre Diplomatique est trop endomagé pour être utilisé!");
			}
			echo "</fieldset><br />\n";
		}

		//Tour d'observation
		$sql = "SELECT id FROM batiments WHERE id_batiment = '24' AND id_province = '".$_SESSION['id_province']."' AND value = '1'";
		$req = sql_query($sql);
		$nbr = mysql_num_rows($req);
		
		if($nbr == 1)
		{
			$res = mysql_fetch_array($req);
			
			echo "<fieldset><legend>Tour d'Observation</legend>\n";
			//Cherche la vie total du batiment
			if(bw_batiavailable(24))
			{
				echo bw_info("Voici une liste des sorts en réserve chez l'ennemi.");

				echo "<table class=\"newsmalltable\">\n";
				
				$sql_sort = "SELECT a.id_sort, a.`time`, b.nom FROM temp_sorts AS a LEFT JOIN liste_sorts AS b ON b.id = a.id_sort WHERE a.id_province = '".$Vision_Id_Province."' AND a.id_sort NOT IN ('4') ORDER BY a.`time` ASC";
				$req_sort = sql_query($sql_sort);
				while($res_sort = sql_object($req_sort))
				{
					echo "<tr><td><strong>".bw_popup($res_sort->nom, 'sort', $res_sort->id_sort)."</strong></td></tr>\n";
				}
				echo "</table>\n";
			}
			else {
				echo bw_error("Votre Tour d'Observation est trop endomagé pour être utilisée!");
			}
			echo "</fieldset><br />\n";
		}
	}
}

bw_tableau_end();
?>