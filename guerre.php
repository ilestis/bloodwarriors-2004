<?php
/*
+---------------------
|Nom: Les Guerres
+---------------------
|Description: Permet d'attaque/se defendre/voir nos guerre en cours
+---------------------
|Date de création: Aaout 04
|Date du premier test: Aout 04
|Dernière modification: 13 Aout 05 (multi provinces)
|						14 Aout 05 (changements sql)
|						09 Février 06 (update v3)
|						11 Mars 06 (Annuler les guerres)
+---------------------
*/
include ('./profil.php');

//--------------
//$MISEAJOUT = FALSE;
//---- Les modifications ne sont pas prise en compte


bw_tableau_start("Guerres");
if (!bw_batiavailable('fort', false))
{
	bw_f_info("Information", bw_error("Vous avez besoin d'un fort pour attaquer des héros!"));

}

elseif(check_spell($_SESSION['id_province'], '42')) {
	bw_f_info("Information", "Vous êtes sous l'emprise d'une " . bw_popup('Paralysie', 'sort', '42')."! Vous ne pouvez pas déclarer de guerres en ce moment.");
}
else
{
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function select_switch()
	{
		var all_ck = document.form1.all_check;
		if(all_ck.checked == true)
		{
			for (i = 1; i < document.form1.length-5; i++)
			{
				document.form1.elements[i].checked = true;
			}
		} else {
			for (i = 1; i < document.form1.length-5; i++)
			{
				document.form1.elements[i].checked = false;
			}
		}
	}
	</SCRIPT>
	<?php
	//On update nos unités pour le retour
	sql_query("UPDATE armees SET dispo = '1' WHERE `heureretour` <= '".time()."' AND `dispo` = '4'");


	//verifie notre nombre de guerres
	$Sql_Guerres = "SELECT * FROM guerres WHERE att_id = '".$_SESSION['id_joueur']."' AND att_pro_id = '".$_SESSION['id_province']."'";
	$Req_Guerres = sql_query($Sql_Guerres) ;
	$Nb_Guerres = mysql_num_rows($Req_Guerres) ;   //nombre de guerrre
	
	//Nombre de guerres
	$Nb_Guerres_Max = 1;
	if(bw_batiavailable('quartierstrategique', false)) $Nb_Guerres_Max += 1;
	if($Joueur->race == 3) $Nb_Guerres_Max += 1;


	//nombre d'unité de guerre
	$sqlu = "SELECT `ID_creature` FROM `armees` WHERE id_province = '".$_SESSION['id_province']."'";  
	$requ = sql_query($sqlu);   
	$nbr = mysql_num_rows($requ) ;

	bw_f_info("Information", bw_info((isset($Message) ? $Message : "Vous possédez actuellement ".$nbr.' unité'.pluriel($nbr, 's'))));


	if ($Nb_Guerres < $Nb_Guerres_Max)
	{//aucune guerre en cour -> c'est ok
		//echo 'Page en création! Désolé du dérangement!<br />';

		?>

		<form name="form1" method="post" action="index.php?p=warchoice&id=2">
		<fieldset>
		
		<legend>Lancer une guerre</legend>
		<table class="newsmalltable"><tr>		
		<td style="text-align: center;">

		<strong>Choisissez un Héros à attaquer:</strong>
		<select name="province">
		<option value="">--Selectionnez (Pseudo: Province)</option>

		<?php
		//prend les différents joueurs
			//Puissance minimale 
		//$Min_Puissance =  * ;
		$Min_Puissance = min_attack_power($Joueur->puissance, $CONF[$Joueur->race.'_attaque_min'], $Joueur->race, $CONF['bonus_anges_2']);

		//prend ou la puissance est plus grand que la puissance minimal
		$sql2 = "SELECT id_joueur, name, id FROM provinces WHERE id_joueur <> '".$_SESSION['id_joueur']."' ORDER BY id_joueur ASC";
		$req2 = sql_query($sql2);
		while ($res2 = mysql_fetch_array($req2))
		{//Prend chaque province
			//Prend le pseudo et vérifie si on peut l'attaquer
			$sql3 = "SELECT pseudo, ally_id, aut, vacances, puissance, race FROM joueurs WHERE id = '".$res2['id_joueur']."'";
			$req3 = sql_query($sql3);
			$res3 = mysql_fetch_array($req3);

			//Bonus Anges
			if($res3['race'] == 1)
				$Min_Puissance *= $CONF['bonus_anges_2'];
			
			//Verifie divers truc
			if($Joueur->ally_id == 0) $MonAlly == '---235ggb32--dolardolardolarlolmdrhe';
			else $MonAlly = $Joueur->ally_id;
			if (($res3['aut'][0] == 1) AND ($res3['vacances'] == 0) AND ($res3['ally_id'] != $MonAlly) AND ($res3['puissance'] >= $Min_Puissance)) 
			{//On peut attaquer
				echo "<option value=\"".$res2['id']."\">".$res3['pseudo'].": ".$res2['name']."</option>\n";
			}
			

			if($res3['race'] == 1)
				$Min_Puissance /= $CONF['bonus_anges_2'];
		}
		?>
		</select><br />
		<span class="info">Si vous appartennez à une alliance, assurez-vous que vous n'êtes pas en PNA avec l'alliance du héros que vous attaquez!</span><br />

		<table width="100%" style="border: 0px; border-collapse: collapse;">
			 <!--<tr>
				<td class="in" colspan="3" style="text-align: right;">Tout selectionner<INPUT TYPE="checkbox" NAME="all_check" Onclick="select_switch();" value="yes"></td>
			</tr>-->
			<tr>
				<th width="40%">Nom</th>
				<th width="35%">Stats: Attaque / Defense (*)</th>
				<th width="25%">Selecteur</th>
			</tr>
		<?php
			//Bonnus de races
			$bon_s = "SELECT * FROM info_races WHERE id_race = '".$Joueur->race."'";
			$bon_q = sql_query($bon_s);
			$bon_r = sql_object($bon_q);

			$tt = "SELECT *, COUNT(ID_creature) AS TotCrea FROM `armees` WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND dispo = '1' GROUP BY ID_creature ORDER BY nom ASC";  
			$uu = sql_query($tt);
			while($vv = mysql_fetch_array($uu))
			{
				
				echo "<tr>\n";
				echo "	<td style=\"text-align: left;\">".$vv['nom']."</td>\n\n";
				
				echo "	<td>";
					//Calcul la puissance
					$Puissance_A = ($vv['power_1']+$bon_r->bonus_1) + ($vv['power_3'] + $bon_r->bonus_3);
					$Puissance_D = $vv['power_2'] + $bon_r->bonus_2;
					echo $Puissance_A."/".$Puissance_D."";
				echo "</td>\n\n";
				
				echo "	<td>
				<input type=\"text\" name=\"lst_".$vv['ID_creature']."\" maxlength=\"5\" size=\"5\" /> / ".$vv['TotCrea']."</td>\n";
				
				/*<select name=\"lst_".$vv['ID_creature']."\">\n";
				for($i = 0; $i <= $vv['TotCrea']; $i++)
				{
					echo "		<option value=\"".$i."\">".$i."</option>\n";
				}
				echo "	</select> / ".$vv['TotCrea']."</td>\n";*/
				//echo "<INPUT TYPE=\"checkbox\" name=\"unite".$vv['id']."\" id=\"".$vv['id']."\">\n";
				//echo "</td>\n";
				echo "</tr>\n";
			
			}
			?>
		<tr>
			<th colspan="3" style="text-align: center;">*: Force+Attaque, Inclu les bonnus de races, mais pas les sorts</td>
		</tr>
		</table>
		
		<br />
	
		<!-- CHOIX DE TECHNIQUE -->
		<span class="news">Veuillez choisir la technique de combat que vous utilisez:</span><br />
		<input type="radio" name="radiobutton" value="1"> Front<br />
		<input type="radio" name="radiobutton" value="2"> Vagues<br />
		<input type="radio" name="radiobutton" value="3"> Cercle<br />
		
		<?php
		//les techniques bonus
		if ($Joueur->puissance >= 10000) { 
			echo "<input type=\"radio\" name=\"radiobutton\" value=\"4\"> Retrait<br />\n"; 
		}
		if($Joueur->puissance >= 30000) {
			echo "<input type=\"radio\" name=\"radiobutton\" value=\"5\"> Camouflé<br />\n"; 
		}
		
		if($GLOBALS['CONF']['allow_give_units'] && false) { ?>
		<p>Action: <select name="type_action"><option value="0">Attaquer</option><option value="1">Envoyer</option></select> (Envoyé reviens à déposer les unités sur la province).</p>
		<?php } ?>

		<input type="submit" name="Submit" value="Attaquer">
		<br>
			
		<!--<span class="info">Attention!!!Une fois que vous aurez cliqué sur 'Attaquer!', vous ne pourez pas revenir en arrière et annuler votre action!</span><br/>-->
		
		</td></form>
		</tr>
		</table>

	<?php
	}
	else
	{//déjà une guerre en cours -> Pas ok
		bw_f_info("Lancer une guerre", bw_error("Vous avez déjà votre nombre maximal de guerre en cours!"));
	}
}
echo "	</fieldset><br />\n";

echo "	<fieldset>\n";
echo "	<legend>Guerres en cours</legend>\n";
echo "	<table class=\"newsmalltable\">\n";
echo "	<tr>\n";
echo "	<th colspan=\"4\">Vous êtes attaqué(e) par:</th>\n";
echo "	</tr>\n";

//prend nos guerres
$sql = "SELECT * FROM `guerres` WHERE def_id = '".$_SESSION['id_joueur']."' AND def_pro_id = '".$_SESSION['id_province']."' ORDER BY time_guerre ASC";
$req = sql_query($sql);
$nums1 = mysql_num_rows($req);
if($nums1 > 0)
{
	echo "	<tr>\n";
	echo "	<th>Héro:Province:</th>\n";
	echo "	<th>Durée de la guerre:</th>\n";
	echo "	<th>Votre technique:</th>\n";
	echo "	</tr>\n";

	while($res = mysql_fetch_array($req))
	{
		//Variables
		$Attaqueur = $res['att_id'];
		$Prov_Att = $res['att_pro_id'];
		//Prend les noms
		$Att = "SELECT joueurs.pseudo, provinces.name FROM joueurs, provinces WHERE joueurs.id = '".$Attaqueur."' AND provinces.id = '".$Prov_Att."'";
		$Attq = sql_query($Att);
		$Attr = mysql_fetch_array($Attq);
		$Attaqueur = $Attr['pseudo'];
		$Province = $Attr['name'];

		$Time = date($CONF['game_timeformat'], $res['time_guerre']);

		$Technique = $res['def_tech'];
		$War_Id = $res['id_guerre'];

		echo "	<TR>\n";
		echo "	<TD>".$Attaqueur.":".$Province."</TD>\n";
		echo "	<TD><?php echo $Time; ?></TD>\n";

		if ($Technique == '0' )
		{//changer de technique
			?>
			<FORM METHOD="POST" ACTION="index.php?p=warchoice&id=updef&id2=<?php echo $War_Id; ?>">
			<TD>
			<input type="radio" name="radiobutton" value="1"> Barricade<br />
			<input type="radio" name="radiobutton" value="2"> Pièges<br />
			<input type="radio" name="radiobutton" value="3"> Muraille<br />
			
			<?php //bonus
			if ($Joueur->puissance >= 1500) {
				echo '<input type=radio name=radiobutton value=4> Couvert<br/>'; 
			}
			if ($Joueur->puissance >= 3000) {
				echo '<input type=radio name=radiobutton value=5> Contre le camouflage<br/>'; 
			}

			//supplémentaire:
				//Illusion        -       Berzerker   //

			echo '<input type=submit name=Submit value="Changeons ma technique">';
			echo "</td>\n";
			echo '</FORM>';
		}
		else { //on a deja choisi une technique
			echo '<td>Vous avez déjà choisi votre technique!</td>';
		}
		echo "</TR>\n";
	}
} else {
	echo "<tr><td colspan=\"4\">".bw_info("Vous êtes attaqué par personne.")."</td></tr>\n";
}
echo "</table><br /><br />\n";


echo "	<table class=\"newsmalltable\">\n";
echo "	<tr>\n";
echo "	<th colspan=\"4\">Vous attaquez:</th>\n";
echo "	</tr>\n";

//requete
$sql = "SELECT * FROM guerres WHERE att_id = '".$_SESSION['id_joueur']."' AND att_pro_id = '".$_SESSION['id_province']."' order by time_guerre ASC" ;
$req = sql_query($sql);
$nums = mysql_num_rows($req);
if($nums > 0)
{
	echo "	<tr>\n";
	echo "	<th>Vous attaqué:</th>\n";
	echo "	<th>Durée de la guerre:</th>\n";
	echo "	<th>Votre technique:</th>\n";
	echo "	<th>Action:</th>\n";
	echo "	</tr>\n";

	while($res = mysql_fetch_array($req))
	{
		//Variable
		$Time = date($CONF['game_timeformat'], $res['time_guerre']);
		$War_Id = $res['id_guerre'];
		if($res["att_tech"] == 1) $technique="en Front";
		if($res["att_tech"] == 2) $technique="en Vagues";
		if($res["att_tech"] == 3) $technique="en Cercle";
		if($res["att_tech"] == 4) $technique="en Retrait";
		if($res["att_tech"] == 5) $technique="en Illusion";
		if($res["att_tech"] == 6) $technique="en Berzerker";

			//Prend les noms
		$NameS = "SELECT joueurs.pseudo, provinces.name FROM joueurs, provinces WHERE joueurs.id = '".$res['def_id']."' AND provinces.id = '".$res['def_pro_id']."'";
		$NameQ = sql_query($NameS);
		$NameS = mysql_fetch_array($NameQ);
		$Defenseur = $NameS['pseudo'];
		$Province = $NameS['name'];
		?>
		<TR>
		<TD><?php echo $Defenseur.':'.$Province; ?></TD>
		<TD><?php echo $Time; ?></TD>
		<TD>Vous attaquez <?php echo $technique; ?></TD>
		<TD><form method=POST action="?p=warchoice&id=3"><input type="hidden" name="id_guerre" value="<?php echo $res['id_guerre']; ?>"><input type="submit" value="Rappeller les troupes"></form></TD>
		</TR>
		<?php
	}
} else {
	echo "<tr><td colspan=\"4\">".bw_info("Vous n'attaquez personne!")."</td></tr>\n";
}	
echo "	</table></fieldset>\n";
bw_tableau_end();



# Met à jour les unités qui rentrent
$reqUp = "UPDATE armees SET dispo = '1', heureretour = '' WHERE heureretour < ".time()." AND id_province = '".$_SESSION['id_province']."' AND dispo IN(3, 4)";
sql_query($reqUp);
?>