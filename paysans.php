<?php
/*--------------------
|Nom: La gestion des paysans
+---------------------
|Description: Affiche les occupations des paysans
+---------------------
|Date de création: Février 04
|Date du premier test: Février 04
|Dernière modification: 11 Février 2006
+-------------------*/

//Le profil
require ("profil.php");

//langue
$lang_text = lang_include($Joueur->lang,'lang_paysans');


function Paysans_return($date)
{
	//verifie l'état des paysans
	if($date > 0) 
	return $lang_text['info1'].$date.$lang_text['info2'];
}

function view_section($numero)
{//retourne la section
	switch ($numero)
	{
		case 1:
			$var = "Mines";
			break;
		case 2:
			$var = "Champs";
			break;
		case 3:
			$var = "Matériaux";
			break;
		case 4:
			$var = "Matériaux";
			break;
		case 5:
			$var = "Sanctuaires";
			break;
		case 6:
			$var = "Foyers";
			break;
		case 7:
			$var = "Maternelles";
			break;
		case 8:
			$var = "Construction";
			break;
		case 9:
			$var = "Réparations";
			break;
	}
	return $var;
}
function paysans_nombre($log, $section, $esclave=false)
{
	$sqlgg = "SELECT nombre FROM temp_paysans WHERE section = '".$section."' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' ".($esclave ? "AND esclave = 'O'" : null)."";
	$reqgg = sql_query($sqlgg);
	$resgg = sql_array($reqgg);
	return $resgg['nombre'];
}

function type_paysans($esclave)
{
	if($esclave == 'O') return 'Esclaves';
	return 'Paysans';
}

//ptotal
$sql = "SELECT peasant FROM provinces WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id = '".$_SESSION['id_province']."'";
$req = sql_query($sql);
$res = sql_array($req);
$Ptotal = $res['peasant'];
$PDispo = $res['peasant'];

//prend chaque paysans en activité
$act = "SELECT nombre FROM temp_paysans WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."'";
$reh = sql_query($act);
while($res = sql_array($reh))
{
	//paysans utilisé
	$PDispo -= $res['nombre'];
}

$batiment =	$Ptotal - $PDispo;

//deffinit sur quel selecteur on se trouve
if(!isset($_GET['selecteur']) OR $_GET['selecteur'] > 2 OR $_GET['selecteur'] < 0)
	$selecteur = 0;
else
	$selecteur = $_GET['selecteur'];

switch($selecteur)
{//
	case 0:
		//vision des paysans

		//verifie les variables
		bw_tableau_start($lang_text['title2']);

		if(isset($_SESSION['message']) && $_SESSION['message'] != '') { 
			bw_fieldset("Information", $_SESSION['message']); 
			$_SESSION['message'] = ''; 
			echo "<br />\n"; 
		}

		?>
				<fieldset>
				<legend>Occupation des paysans</legend>
				<table class="newsmalltable">
				<tr>
					<th>Domaine</th>
					<th>Nombre</th>
					<th>Date de retour</th>
				</tr>
				<?php
				$blnEsclaves = false;
				//prend chaque paysans et l'emplacement
				$sql = "SELECT * FROM temp_paysans WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' ORDER BY id ASC";
				$req = sql_query($sql);
				while ($res = sql_array($req))
				{
					if($res['section'] == 0) {
						if($res['nombre'] == 0 && $res['esclave'] == 'O') null;
						else {
							echo "<tr><td>".type_paysans($res['esclave'])." disponibles</td><td>".$res['nombre']."</td><td >&nbsp;</td></tr>\n";
							if($res['esclave'] == 'O') $blnEsclaves = true;
						}
					}
					else
					{
						echo "<tr>\n";
						if($res['extra_info'] == '') echo "	<td>".type_paysans($res['esclave'])." aux ".view_section($res['section'][0])."</td>";
						else
						{
							echo "	<td>";
							//info spécial
							$ar = explode("_", $res['extra_info']);
							$batiname = "SELECT nom FROM liste_batiments WHERE id = '".$ar[1]."'";
							$batireq = sql_query($batiname);
							$batires = sql_object($batireq);

							if($ar[0] == 'repar') echo "Réparation: ".$batires->nom;
							elseif($ar[0] == 'const') echo "Construction: ".$batires->nom;
							else echo "Erreur (avertir admin)";
							echo "</td>\n";
						}
						echo "	<td>".$res['nombre']."</td>\n";
						echo "	<td>".date($CONF['game_timeformat'], $res['time'])."</td>\n";
						echo "</tr>\n";
						//echo "<td>".Paysans_return($res['date'])."</td></tr>";
					}
				}
				?>
				</tr>
				</table>
				</fieldset>

				<br />

				<form name="form1" method="post" action="index.php?p=paysans&selecteur=2">
				<fieldset>
				<legend>Occupation des paysans</legend>


				<table class="newsmalltable">
					<tr>
						<th width="250px"><?php echo $lang_text['title2']; ?></th>
						<th width="30px"><?php echo $pdispo; ?></th>
						<th width="250px"><?php echo $lang_text['recall'].func_MaxPop($_SESSION['id_joueur'], $_SESSION['id_province'], $CONF[$Joueur->race.'_paysans_max'], $Joueur->ally_id); ?></th>
					</tr>

					<tr>
						<td colspan="2"><?php echo $lang_text['mines']; ?></td>
						<td><input type="text" name="pmine" value="0"></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo $lang_text['fields']; ?></td>
						<td><input type="text" name="pchamps" value="0"></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo $lang_text['mats']; ?></td>
						<td><input type="text" name="pmat" value="0"></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo $lang_text['sanctuary']; ?></td>
						<td><input type="text" name="pmagie" value="0"></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo $lang_text['foyer']; ?></td>
						<td><input type="text" name="pfoyer" value="0"></td>
					</tr>
					<tr>
						<th align="right">
							<?php 
						if($blnEsclaves) { ?>
							<select name="esclaves">
								<option value="N">Paysans</option>
								<option value="O">Esclaves</option>
							</select>
						<?php } else { ?>
							<input type="hidden" name="esclaves" value="N" />
						<?php } ?>
						</th>
						<th><br /></th>
						<th align="left">
							<INPUT TYPE="submit" value="<?php echo $lang_text['btn2']; ?>">
						</th>
					</tr>
				</table>	
			</fieldset>	
			<img src="images/paysanvivi.gif" title="Merci Virginie pour ce fabuleux dessin!^^"/>
				
		<?php

		break;


	case 2:
		//le temps
		$Vintquatre = 3600*24*$CONF['vitesse_jeu']; //Vitesse de 24heures
		$ReturnTime = time()+$Vintquatre; 

		//Bonnus Rebelles tour plus rapide
		if($Joueur->race == 5) { $ReturnTime -= $CONF['bonus_rebelles_2'];
			//die($Vintquatre."<br />".time()."<br />".$ReturnTime."<br />".$CONF['bonus_rebelles_2']);
		}

		# Sélecteur paysans/esclaves
		if($_POST['esclaves'] == 'O') {
			$WherePlus = " AND esclave = 'O'";
			$Esclaves = true;
			$InputEsclave = 'O';
		}
		else {
			$WherePlus = " AND esclave = 'N'";
			$Esclaves = false;
			$InputEsclave = 'N';
		}

		$Message = '';

		//le bonus des bâtiments

		//école & collège
		$EcoleS = "SELECT value FROM batiments WHERE codename = 'ecole' AND  value= '1' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."'";
		$EcoleQ = sql_query($EcoleS);
		$EcoleR = mysql_num_rows($EcoleQ);
		if ($EcoleR == 1) $ReturnTime -= 7200;

		$CollegeS = "SELECT value FROM batiments WHERE codename = 'college' AND  value= '1' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."'";
		$CollegeQ = sql_query($CollegeS);
		$CollegeR = mysql_num_rows($CollegeQ);
		if ($CollegeR == 1) $ReturnTime -= 7200;

		//bonus max pop
		$max = func_MaxPop($_SESSION['id_joueur'], $_SESSION['id_province'], $CONF[$Joueur->race.'_paysans_max'], $Joueur->ally_id);
		//echo 'Max: '.$max.'<br />';

		//placement
		$PR['mine'] = $_POST['pmine'];
		$PR['champs'] = $_POST['pchamps'];
		$PR['mat'] = $_POST['pmat'];
		$PR['magie'] = $_POST['pmagie'];
		$PR['foyer'] = $_POST['pfoyer'];

		//met à jour le temps avec le bonheur
		//$time = $time+($b_time[$Joueur->niveau_bonheur]);
		//$tell = $tell+($bt_time[$Joueur->niveau_bonheur]);

		//recalcule paysans disponibles
		$P['dispo'] = paysans_nombre($_SESSION['login'], 0, $Esclaves);

		if($PR['mine'] >= 1 && is_numeric($PR['mine']))
		{//on regarde l'or
			$P['new_dispo'] = $P['dispo'] - $PR['mine'];
			//die($P['new_dispo'] ."=". $P['dispo'] ."-". $PR['mine']);
			if($P['new_dispo'] < 0) {//on place plus de paysans que ceux disponibles
				$ok = 0; }
			else {//tout semble ok
				$sqlmine = "INSERT INTO temp_paysans VALUES('','".$_SESSION['id_joueur']."','1','".$PR['mine']."','".$ReturnTime."', '".$_SESSION['id_province']."', '', '".$InputEsclave."')";
				sql_query($sqlmine);

				$sqlpaysans = "UPDATE temp_paysans SET nombre = '".$P['new_dispo']."' WHERE section = '0' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."'".$WherePlus."";
				
				sql_query($sqlpaysans);

				$Message .= "Vos paysans ont bien été placés aux mines.<br />\n"; //Ils seront de retour le <strong>".date($CONF['game_timeformat'], $ReturnTime)."</strong>.<br />\n";
				
				$ok = 1;
			}
		}

		//recalcule paysans disponibles
		$P['dispo'] = paysans_nombre($_SESSION['login'], 0, $Esclaves);

		if($PR['champs'] >= 1 && is_numeric($PR['champs']))
		{//verifie les champs
			$P['new_dispo'] = $P['dispo'] - $PR['champs'];
			if($P['new_dispo'] < 0) {//on place plus de paysans que ceux disponibles
				$ok = 0; }

			else {//tout semble ok
				$sqlmine = "INSERT INTO temp_paysans VALUES('','".$_SESSION['id_joueur']."','2','".$PR['champs']."','".$ReturnTime."', '".$_SESSION['id_province']."', '', '".$InputEsclave."')";
				sql_query($sqlmine);

				$sqlpaysans = "UPDATE temp_paysans SET nombre = '".$P['new_dispo']."' WHERE section = '0' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."'".$WherePlus."";
				sql_query($sqlpaysans);

				$ok = 1;
				$Message .= "Vos paysans ont bien été placés aux champs.<br />\n"; //Ils seront de retour le <strong>".date($CONF['game_timeformat'], $ReturnTime)."</strong>.<br />\n";
			}
		}

		//recalcule paysans disponibles
		$P['dispo'] = paysans_nombre($_SESSION['login'], 0, $Esclaves);

		if($PR['mat'] >= 1 && is_numeric($PR['mat']))
		{//verifie les pierres
			$P['new_dispo'] = $P['dispo'] - $PR['mat'];
			if($P['new_dispo'] < 0) {//on place plus de paysans que ceux disponibles
				$ok = 0; }

			else {//tout semble ok
				$sqlpierre = "INSERT INTO temp_paysans VALUES('','".$_SESSION['id_joueur']."','3','".$PR['mat']."','".$ReturnTime."', '".$_SESSION['id_province']."', '', '".$InputEsclave."')";
				sql_query($sqlpierre);

				$sqlpaysans = "UPDATE temp_paysans SET nombre = '".$P['new_dispo']."' WHERE section = '0' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."'".$WherePlus."";
				sql_query($sqlpaysans);

				$Message .= "Vos paysans ont bien été placés aux matériaux.<br />\n"; //Ils seront de retour le <strong>".date($CONF['game_timeformat'], $ReturnTime)."</strong>.<br />\n";
				$ok = 1;
			}
		}

		//recalcule paysans disponibles
		$P['dispo'] = paysans_nombre($_SESSION['login'], 0, $Esclaves);

		if($PR['magie'] >= 1 && is_numeric($PR['magie']))
		{//verifie la magie
			$P['new_dispo'] = $P['dispo'] - $PR['magie'];
			if($P['new_dispo'] < 0) {//on place plus de paysans que ceux disponibles
				$ok = 0; }

			else {//tout semble ok
				$sqlmine = "INSERT INTO temp_paysans VALUES('','".$_SESSION['id_joueur']."','5','".$PR['magie']."','".$ReturnTime."', '".$_SESSION['id_province']."', '', '".$InputEsclave."')";
				sql_query($sqlmine);

				$sqlpaysans = "UPDATE temp_paysans SET nombre = '".$P['new_dispo']."' WHERE section = '0' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."'".$WherePlus."";
				sql_query($sqlpaysans);

				$Message .= "Vos paysans ont bien été placés aux sanctuaires.<br />\n"; //Ils seront de retour le <strong>".date($CONF['game_timeformat'], $ReturnTime)."</strong>.<br />\n";
				$ok = 1;
			}
		}

		//recalcule paysans disponibles
		$P['dispo'] = paysans_nombre($_SESSION['login'], 0, $Esclaves);

		if($PR['foyer'] >= 2 && is_numeric($PR['foyer']))
		{//verifie le foyer
			//Si on est ange: on fait des jumeaux
			$pcreation=($PR['foyer'] / 2);
			if($Joueur->race == 1) {//Bonnus ange
				$pcreation = $PR['foyer']; }

			$P['ext'] = ($Ptotal + $pcreation);
			$P['new_dispo'] = $P['dispo'] - $PR['foyer'];
			if($Esclaves) { // On ne peut pas accoupler des esclaves
				$ok = 3;
			}
			elseif($P['new_dispo'] < 0 OR ($PR['foyer']%2) == 1 OR $P['ext'] > $max) 
			{ //on éfreint la limite
				$ok = 2; 
			} //message spécial foyer
			else {//tout semble ok
			
				$sqlfoyer = "INSERT INTO temp_paysans VALUES('','".$_SESSION['id_joueur']."','6','".$PR['foyer']."','".$ReturnTime."', '".$_SESSION['id_province']."', '', '')";
				sql_query($sqlfoyer);

				$sqlcrea = "INSERT INTO temp_paysans VALUES('','".$_SESSION['id_joueur']."','7','".$pcreation."','".$ReturnTime."', '".$_SESSION['id_province']."', '', '')";
				sql_query($sqlcrea);


				$sqlpaysans = "UPDATE temp_paysans SET nombre = '".$P['new_dispo']."' WHERE section = '0' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."'";
				sql_query($sqlpaysans);

				$sqltot = "UPDATE provinces SET peasant = '".$P['ext']."' WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id = '".$_SESSION['id_province']."'";
				sql_query($sqltot);

				$Message .= "Vos paysans ont bien été placés aux foyers.<br />\n"; //Ils seront de retour le <strong>".date($CONF['game_timeformat'], $ReturnTime)."</strong>.<br />\n";;
				$ok = 1;
			}
		}
		//débug
		//echo
		if($ok == 0)
		{
			$Message .= bw_error('Une <strong>Erreur</strong> est survenue lors de votre action.<br/>Verifiez que vous n\'avez pas designé un nombre de paysans supérieur que le nombre dont vous possedez.<br />Avez-vous sinon tout simplement indiqué aucun paysan à placer?<br />');
		}
		elseif($ok == 2)
		{
			$Message .=  bw_error("Une <strong>Erreur</strong> est survenue lors de votre action.<br/>Verifiez que vous avez assigné un nombre <strong>PAIR</strong> au foyer, ainsi que votre population plus le nombre de nouveaux paysans ne dépasse votre max de: ".$max.".<br/>\n");

			if($Joueur->race == 1) {//Bonus
				$Message .=  bw_error("De plus, n'oubliez pas que vos couples produisent des JUMEAUX (Bonnus des Anges, voir FAQ). Pour 2 paysans placés, vous avez 2 nouveaux paysans.<br />\n");
			}
		} elseif($ok == 3) {
			$Message .= bw_error("Une <strong>Erreur</strong> est survenue lors de votre action.<br/>Vous ne pouvez pas accoupler des esclaves<br />");
		}
		$_SESSION['message'] = $Message;
		unset($Message);

		//echo "<a href=\"index.php?p=paysans\">Retour au placement des paysans (redirection automatique)</a><br />\n";

		redirection('index.php?p=paysans', '1');

		break;
			

}

bw_tableau_end();
?>