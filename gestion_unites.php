<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Gestion_unites.Php
+-----------------------------------------------------
|Description:	Voir nos unités pour les mettres a jour
+-----------------------------------------------------
|Date de création:				12/06/05
|Dernière modification[Auteur]: 06/11/05 [Jer] {3 tableaux d'unités + cocher unités pour sacrifice}
|								02.09.07 [Jer] {Tableau par disponibilité, gestion des tentes, pouvoir envoyer}
+---------------------------------------------------*/

//le profil
include ('profil.php');

bw_tableau_start("Gestion des unités");

$Message = '';
if(isset($_GET['do']) && $_GET['do'] == 'switch')
{//Échange
	foreach($_POST as $Element => $Valeur)
	{
		//echo $Element."-".substr($Element, 0, 10)."<br />";
		if(substr($Element, 0, 10) == 'sacrifice_')
		{
			// Recoit l'ID
			$s = explode("_", $Element);
			$ID_creature = clean($s[1]);
			$Nbr = clean($_POST[$Element]);

			// On verifie si on a bien ce nombre d'unité du type
			$sql = "SELECT id FROM armees WHERE id_province = '".$_SESSION['id_province']."' AND ID_creature = '".$ID_creature."'";
			$req = sql_query($sql);

			if(mysql_num_rows($req) >= $Nbr) {
				// C'est bon, on met à jour

				// En premier, on retire le sacrifice de nos unités de ce type
				$Up = "UPDATE armees SET sacrifice = '0' WHERE id_province = '".$_SESSION['id_province']."' AND ID_creature = '".$ID_creature."'";
				sql_query($Up);

				// Puis si le nombre est < 0, on le met
				if($Nbr > 0) {
					$Up = "UPDATE armees SET sacrifice = '1' WHERE id_province = '".$_SESSION['id_province']."' AND ID_creature = '".$ID_creature."' LIMIT ".$Nbr."";
					sql_query($Up);
				}
			}
			else
			{
				$Message .= bw_error("Vous ne possédez pas autant d'unité de ce type.<br />\n");
			}
		}
	}
}
elseif(isset($_GET['do']) && $_GET['do'] == 'buytent')
{//On achete une tente

	// Nombre qu'on a mis?
	$NBR = clean($_POST['nbr_tent']);
	$NbrAchetee = 0;
	$NbrFailCases = 0;
	$NbrFailRes = 0;
	$RessourceMat = $Res_Ressources['mat'];
	$RessourceGold = $Res_Ressources['gold'];

	// Boucle pour chaque tente, comme pour les unités
	for($i = 1; $i <= $NBR; $i++)
	{
		// Calcul le cout
		$PrixGold = $CONF['war_tente_prix_gold'];
		$PrixMat =  $CONF['war_tente_prix_mat'];
		
		// Bonnus elfes
		if($Joueur->race == 4) $PrixMat *= $CONF['bonus_elfes_2'];


		//Prend notre bois et or
		if(($RessourceMat >= $PrixMat) && ($RessourceGold >= $PrixGold))
		{
			//Calcule le nombre de tente qu'on a
			$sql = "SELECT id FROM batiments WHERE id_province = '".$_SESSION['id_province']."' AND id_batiment = '".$CONF['bati_tente_id']."'";
			$req = sql_query($sql);
			$nb = sql_rows($req);
		
			//Si on a zéro tente ou notre nombre est un multiple du nb de tente par cases
			$Ok = 1; $Up_Province = "";
			if(($nb == 0) || (($nb % $CONF['war_tente_nb']) == 1))
			{
				//Verifie si on a une case de terrain libre
				if($Res_Ressources['cases_usuable'] > 0)
				{
					//Met à jour notre nombre de cases utilisable
					$Up_Province = ", cases_usuable = (cases_usuable-1)";
				}
				else
				{
					$Ok = 0;
				}
			}

			if($Ok == 1)
			{
				$RessourceMat -= $CONF['war_tente_prix_mat'];
				$RessourceGold -= $CONF['war_tente_prix_gold'];

				$add = "INSERT INTO `batiments` (id_joueur, id_province, id_batiment, codename, value, life) VALUES('".$_SESSION['id_joueur']."', '".$_SESSION['id_province']."', '".$CONF['bati_tente_id']."', 'tente', '1', '10')";
				sql_query($add);

				$NbrAchetee ++;
			}
			else
			{
				$NbrFailCases ++;
				$Message = bw_error("Vous n'avez pas assez de cases libres pour placer des tentes!<br />\n");
			}
		}
		else
		{
			$NbrFailRes ++;
			$Message = bw_error("Vous n'avez pas assez de ressource pour acheter une tente!<br />\n");
		}
	}
	$Message = "<strong>Vous avez acheté ".$NbrAchetee." tentes!</strong><br />\n";

	// Update la province
	$up_ress = "UPDATE provinces SET mat = '".$RessourceMat."', gold = '".$RessourceGold."' WHERE id = '".$_SESSION['id_province']."'";
	sql_query($up_ress);
}
//Compte le nombre de place disponnibles
$id_tentearmee = $CONF['bati_tente_id'];

$sql = "SELECT id FROM batiments WHERE id_province = '".$_SESSION['id_province']."' AND value = '1' AND id_batiment = '".$CONF['bati_tente_id']."'";
$req = sql_query($sql);
$nb_tentes = sql_rows($req);
$nb_max_unites = $nb_tentes*$CONF['war_tente_capa'];

// Bonnus barbares
if($Joueur->race == 2) $nb_max_unites = $nb_tentes*($CONF['war_tente_capa']+$CONF['bonus_barbares_2']);

$sql = "SELECT id FROM armees WHERE id_province = '".$_SESSION['id_province']."'";
$req = sql_query($sql);
$nb_unites = sql_rows($req);

$nb_libre = $nb_max_unites - $nb_unites;

// Messages d'information.
$Message .= "Vous possédez actuellement ".$nb_tentes." tente".pluriel($nb_tentes, 's').", soit une place total de ".$nb_max_unites.".<br />\n".$nb_unites." places sont déjà occupées, il vous reste donc ".$nb_libre." place".pluriel($nb_libre, 's')." libre".pluriel($nb_libre, 's').".<br />\n";

// Prix en matériaux
$PrixMat = $CONF['war_tente_prix_mat'];
if($Joueur->race == 4) $PrixMat *= $CONF['bonus_elfes_2'];
$Message .= "<br />Acheter des nouvelles tentes vous coûte ".$CONF['war_tente_prix_gold']." or et ".$PrixMat." mat&eacute;iaux par tente.<br /><form method=\"post\" action=\"?p=unites&do=buytent\">Nombre à acheter: <input type=\"text\" name=\"nbr_tent\" maxlength=\"2\" size=\"5\" /> <input type=\"submit\" value=\"Acheter\" /></form><br />\n";


$Message .= "Attention: Chaque Case de votre territoire peut contenir jusqu'à ".$CONF['war_tente_nb']." tentes!<br />\n";
$Message .= "Les stats de vos unités sont calculées avec les bonus de votre race, mais pas avec vos sorts en réserve.";

bw_f_info("Information", $Message);

// Bonnus de race
$bonusatt = "SELECT bonus_1, bonus_2, bonus_3, bonus_4 FROM `info_races` WHERE `id_race` = '".$Joueur->race."'";
$bonusatta = sql_query($bonusatt);
$bonusattaquant = sql_array($bonusatta);




# ON CREE 3 tableau par disponnibilité
$Array_Message = array ('Disponnibles', 'En Guerre', 'Retour / Arrive');
$Array_SQL = array ('1', '2', '4, 9');


for($i = 0; $i < 3; $i++)
{
	
	echo "
	<form method=\"POST\" action=\"?p=unites&do=switch\">
	<fieldset>
		<legend>Type: ".$Array_Message[$i]."</legend>
		<table class=\"newsmalltable\">
		<tr>
			<th>Nom</th>
			<th>Statistiques</th>
			<th>Sacrifice / Total</th>
			<th>État</th>
		</tr>\n";
		

	//prend nos unités
	$Cpt = 0;
	//$sql = "SELECT id FROM invocation WHERE type = '".$Array_SQL[$i]."' ORDER BY nom ASC";
	$sql = "
SELECT 
	COUNT(a.id) as Dispo_Total,
	a.ID_creature, a.nom, a.power_1, a.power_2, a.power_3, a.power_4, 
	a.sacrifice, a.dispo 
FROM 
	armees AS a
WHERE 
	a.id_joueur = '".$_SESSION['id_joueur']."' AND 
	a.id_province = '".$_SESSION['id_province']."' AND 
	a.`dispo` IN(".$Array_SQL[$i].")
GROUP BY a.ID_creature
ORDER BY a.nom ASC";
	$req = sql_query($sql);
	while ($res = mysql_fetch_array($req))
	{
		//Variables
		$Cpt ++;
		$ID = $res['id'];
		$Nom = $res['nom'];
		$Stats = ($res['power_1']+$bonusattaquant['bonus_1']).'/'.($res['power_2']+$bonusattaquant['bonus_2']).'/'.($res['power_3']+$bonusattaquant['bonus_3']).'/'.($res['power_4']+$bonusattaquant['bonus_4']);

		switch ($res['dispo'])
		{//définit son etat de disponibilité
			case '0':
				$Disponible = 'Non disponnible';
				break;
			case '1':
				$Disponible = 'Disponible';
				break;
			case '2':
				$Disponible = 'En Guerre';
				break;
			case '3':
				$Disponible = 'Attaque!';
				break;
			case '4':
				$Disponible = 'Rentre <a title="Rentre le '.date($CONF['game_timeformat'], $res['heureretour']).'" href=""><strong>?</strong></a>';
				break;
			case '9':
				$Disponible = 'Arrive <a title="Arrive le '.date($CONF['game_timeformat'], $res['heureretour']).'" href=""><strong>?</strong></a>';
				break;
		}

		// Compte le nombre d'unité en sacrifice
		$sql_s = "SELECT 
			id
		FROM 
			armees 
		WHERE 
			id_joueur = '".$_SESSION['id_joueur']."' AND 
			id_province = '".$_SESSION['id_province']."' AND 
			`dispo` IN(".$Array_SQL[$i].") AND
			ID_creature = '".$res['ID_creature']."' AND
			sacrifice = '1'";
		$req_s = sql_query($sql_s);
		$nbrSacrifice = sql_rows($req_s);

		echo "<tr>\n";
		echo "<td>".$Nom."</td>\n";
		echo "<td>".$Stats."</td>\n";
		echo "<td><input type=\"text\" name=\"sacrifice_".$res['ID_creature']."\" size=\"5\" maxlength=\"".strlen($res['Dispo_Total'])."\" value=\"".$nbrSacrifice."\"> / ".$res['Dispo_Total']."</td>\n";
		echo "<td>".$Disponible."</td>\n";
		//echo "<td>x/".$res['Dispo_Total']."</td>\n";
		echo "</tr>\n";
	}
	if(mysql_num_rows($req) == 0) echo "<tr><td colspan=\"4\"><div style=\"text-align: center; width:100%\">Aucune unité.</div></td></tr>\n";
	
	if($i == 0) echo "		<tr><th colspan=\"3\"><br ></th><th><input type=\"submit\" value=\"Mettre &agrave; jour\"></th></tr>\n";
	echo "		</table>\n";
	echo "		</fieldset><br />\n";
	echo "		</form>\n";
}


// ENVOYER DES UNIT~ES
// Créé la liste des provinces possibles
$lst_Provinces = '';
if($CONF['allow_give_units_self'])  { // Si on a le droit de s'envoyer des unités à nos autres provinces
	$sql_e = sql_query("SELECT id, name FROM provinces WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id <> '".$_SESSION['id_province']."'");
	while($row = sql_array($sql_e)) {
		$lst_Provinces .= "
			<option value=\"".$row['id']."\">".$row['name']."</option>";
	}
}
if($CONF['allow_give_units_ally']) { // Si on a le droit d'envoyer des unités à nos alliés (membres de l'alliance)
	// Provinces de nos alliés
	if($Joueur->ally_id > 0) { //Cherche seulement si on fait partie d'une alliance...
		$sql_e = sql_query("SELECT a.id, a.name, b.pseudo FROM provinces AS a LEFT JOIN joueurs AS b ON b.id = a.id_joueur WHERE a.id_joueur <> '".$_SESSION['id_joueur']."' AND b.ally_id = '".$Joueur->ally_id."'");
		while($row = sql_array($sql_e)) {
			$lst_Provinces .= "
			<option value=\"".$row['id']."\">".$row['pseudo'].": ".$row['name']."</option>";

		}
	}
}

// Formulaire d'envoit d'unité; si on a une liste!
if(check_spell($_SESSION['id_province'], '42')) {
	bw_f_info("Information", "Vous êtes sous l'emprise d'une " . bw_popup('Paralysie', 'sort', '42')."! Vous ne pouvez pas envoyer d'unités en ce moment.");
}
elseif(!empty($lst_Provinces)) {
	echo "
	<form method=\"post\" action=\"index.php?p=warchoice&id=4\">
	<fieldset>
		<legend>Envoyer vos unités</legend>

		".(isset($MessageEnvoit) ? $MessageEnvoit : '')."
		<p>Vous pouvez envoyer vos unités à une de vos provinces. Elle prendront le même temps pour y aller que pour une guerre, et une fois sur place y resteront.</p>

		<p>Sélectionnez la province: 
		<select name=\"province\">
			".$lst_Provinces."
		</select>
		</p>

		Choix des unités:
		<table class=\"newsmalltable\">
		<tr>
			<th>Nom</th>
			<th>Nombre</th>
		</tr>\n";

		$sql_e = sql_query("SELECT nom, ID_creature, COUNT(ID_creature) AS TotCrea FROM `armees` WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND dispo = '1' GROUP BY ID_creature ORDER BY nom ASC");
		while($row = sql_array($sql_e)) {
			echo "
		<tr>
			<td>".$row['nom']."</td>
			<td>
				<input type=\"text\" name=\"lst_".$row['ID_creature']."\" size=\"5\" maxlength=\"7\" />";
			/*for($i = 0; $i <= $row['TotCrea']; $i++)
			{
				echo "
					<option value=\"".$i."\">".$i."</option>";
			}*/
			echo "
				/ ".$row['TotCrea']."
			</td>
		</tr>";
		}
		echo "
		</table><br />

		<input type=\"submit\" value=\"Envoyer\" />
	</fieldset>
	</form><br />";
}
bw_tableau_end();

?>