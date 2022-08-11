<?php
/*----------------------[TABLEAU]---------------------
|Nom:			PapDuJeu.Php
+-----------------------------------------------------
|Description:	Affiche la map du jeu
+-----------------------------------------------------
|Date de création:				06/06/05
|Dernière modification[Auteur]: 25/04/07[Escape]
+---------------------------------------------------*/

bw_tableau_start("Carte du monde");
if (isset($_SESSION['id_joueur'])) 
{//Connecté	

	bw_f_start("Calcul de durée");

	echo bw_info("Pour savoir combien de temps prendra votre attaque, entrez le nom de la province, ainsi que pseudo de la personne que vous souhaitez attaquer!");
	?>
			<FORM METHOD="POST" ACTION="index.php?p=carte">
			Quel province:Héros attaquer:<br />
			<select name="attaque"><option value="">-Choisissez (Pseudo: Province)</option>
	<?php
	$prov = "SELECT a.id_joueur, a.`name`, a.`id`, b.pseudo, b.vacances, b.aut, b.puissance FROM provinces AS a LEFT JOIN joueurs AS b ON b.id = a.id_joueur ORDER BY b.pseudo ASC, a.name ASC ";
	$provreq = sql_query($prov);
	while ($res = mysql_fetch_array($provreq)) {
		//Variables prises
		if($res['aut'][0] == '1') {
			$Prov_Id = $res['id'];
			$Prov_Name = $res['name'];
			$Prov_Player = $res['id_joueur'];

			/*//Selectionne les infos sur le joueur
			$sql = "SELECT pseudo, vacances, acceslvl, puissance FROM joueurs WHERE `id` = '".$Prov_Player."'";
			$req = sql_query($sql);
			$rep = mysql_fetch_array($req);*/

			//Variables
			$Joueur_Name = $res['pseudo'];
			$Joueur_Vac = $res['vacances'];
			$Joueur_Acc = $res['aut'];
			$Joueur_Pui = $res['puissance'];
			$Attaque_min = floor($Joueur->puissance/2);
			$Extra = "";
			if ($Joueur_Acc[11] == '1') $Extra = "(en vacances)";

			if ($Joueur_Name != $Joueur->pseudo) {//On peut l'attaquer
				echo "<option value=\"".$Prov_Id."\"";
				if($Joueur_Pui >= $Attaque_min) echo " style=\"font-weight: bold;\"";
				echo ">".$Joueur_Name.": ".$Prov_Name.$Extra."</option>\n";
			}
		}//End Aut
	}//End While
	echo "</select>\n";
	echo "<INPUT TYPE=\"submit\" value=\"Calculer!\">\n";
	echo "</FORM>";

	if(isset($_POST['attaque']) && !empty($_POST['attaque']))
	{//calcule l'algo
		$Joueur1 = $_SESSION['id_province'];
		$Prov_Id = clean($_POST['attaque']);

		//Nom de la province
		$prov = "SELECT a.`name`, b.pseudo, b.id FROM provinces AS a LEFT JOIN joueurs AS b ON b.id = a.id_joueur WHERE a.id = '".$Prov_Id."'";
		$provreq = sql_query($prov);
		$provres = sql_object($provreq);

		//Temp de guerre
		$ResultatTemp = calc_WarTime($_SESSION['id_province'], $Prov_Id, $CONF['war_time'], $CONF['war_min'], $CONF['vitesse_jeu'], $Joueur->ally_id);

		echo "La durée d'une attaque sur la province ".$provres->name." du joueur <a href=\"?p=search2&joueurid=".$provres->id."\">".$provres->pseudo."</a> se résoluerai le ".date($CONF['game_timeformat'], $ResultatTemp)." heures.<br>\n";
	}//End - if

	bw_f_end();

}//End - if (isset($_SESSION...

/*                       MAP                   */
//On gère l'axe x et y de la map
if(!isset($_GET['x'])) {//X vaut notre position x
	$Carte_X = 0;
}
else {//X vaut le GET
	$Carte_X = clean($_GET['x']);
}

if(!isset($_GET['y'])){//Y vaut notre position y
	$Carte_Y = 0;
}
else{//Y vaut le GET
	$Carte_Y = clean($_GET['y']);
}

//Debut X
$Debut_X = ($Carte_X*$CONF['game_case_view'])+1;
$Debut_Y = ($Carte_Y*$CONF['game_case_view'])+1;
$Fin_X = $Debut_X+$CONF['game_case_view']-1;
$Fin_Y = $Debut_Y+$CONF['game_case_view']-1;
$Limit = $CONF['game_case_view']*$CONF['game_case_view'];
?>
<table border="0" style="border-collapse:collapse; background-repeat: no-repeat;">
<tr>
	<td>&nbsp;</td>
	<td style="text-align: center; height:23px;">
	<?php
	if($Carte_Y > 0) {
		$Next = $Carte_Y-1;
		echo '<a href="?p=carte&x='.$Carte_X.'&y='.$Next.'"><img src="./images/map/up.png" /></a>';
	}
	else {
		echo "&nbsp;";
	}?>
		
	</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td width="23px;"> 
	<?php
	if($Carte_X > 0) {
		$Next = $Carte_X-1;
		echo '<a href="?p=carte&x='.$Next.'&y='.$Carte_Y.'"><img src="./images/map/gauche.png" /></a>';
	}
	else {
		echo "&nbsp;";
	}?>
	</td>
	<td>
	
		<table border="0" style="border-collapse:collapse; background-repeat: no-repeat; margin: 0px; padding: 0px;" background="./images/map/map.png">
		<tr>
			<td style="height: 23px; width:23px;">&nbsp;</td>
			<?php
				for($i = $Debut_X; $i <= $Fin_X; $i++)
				{
					echo "<td style=\"width:23px; height:23px;\">".$i."</td>";
				}
				
			$Cpt_Y = $Debut_Y;
			?>
		</tr>
		<tr>
			<td style="height: 23px; width:23px;"><?php echo $Cpt_Y; ?></td>

		<?php
		$passage = 0;
		$sql = "SELECT * FROM info_cartes WHERE x >= '".$Debut_X."' AND y >=  '".$Debut_Y."' AND x <= '".$Fin_X."' AND y <= '".$Fin_Y."' LIMIT 0, ".$Limit."";
		$req = sql_query($sql);
		while ($res = mysql_fetch_array($req))
		{//prend chaquw truc
			if ($passage == $CONF['game_case_view']) { echo "</tr><tr>\n"; $passage = 0;  $Cpt_Y++; echo "<td>".$Cpt_Y."</td>\n";}
			// width=\"20px\" height=\"20px\" 
			echo "<td style=\"width:23px; height:23px; text-align: center;background:url(images/map/".$res['valeur'].".png);\">";

			$sqlP = "SELECT name, id_joueur, id FROM provinces WHERE `x` = '".$res['x']."' AND `y` = '".$res['y']."'";
			$reqP = sql_query($sqlP);
			$nbrP = mysql_num_rows($reqP);
			if ($nbrP == 1) {//Il y a quelqun
				$resP = mysql_fetch_array($reqP);
				$Province = $resP['name'];

				$sqlJ = "SELECT pseudo, acceslvl, race FROM joueurs WHERE id = '".$resP['id_joueur']."'";
				$reqJ = sql_query($sqlJ);
				$resJ = mysql_fetch_array($reqJ);

				$Pseudo = $resJ['pseudo'];
				$Extra = "";
				if ($resJ['acceslvl'] == '-1') $Extra = "(V)";

				if ($resJ['acceslvl'] > '-2') {//On l'affiche	
					$Flag = $resJ['race'];
					echo "<a href=\"index.php?p=search2&provinceid=".$resP['id']."\">";
					echo "<img src=\"images/map/flag_".$Flag.".png\" title=\"".$resJ['pseudo'].$Extra.":".$Province."\" border=\"0\" style=\"margin:0px; padding=0px\"></a>";
				}//Il est visible
			}//Il y a quelqun
			else
			{
				echo "&nbsp;";
			}
			echo "</td>\n";

			$passage ++;
		}
		?>
		</tr>
		</table>

	</td>
	<td> 
	<?php
	$Calcul = ($CONF['game_case_x']/$CONF['game_case_view'])-1;
	if($Carte_X < $Calcul) {
		$Next = $Carte_X+1;
		echo '<a href="?p=carte&x='.$Next.'&y='.$Carte_Y.'"><img src="./images/map/droite.png" /></a>';
	}
	else {
		echo "&nbsp;";
	}?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td style="text-align: center; height:23px;">
	<?php
	$Calcul = ($CONF['game_case_y']/$CONF['game_case_view'])-1;
	if($Carte_Y < $Calcul) {
		$Next = $Carte_Y+1;
		echo '<a href="?p=carte&x='.$Carte_X.'&y='.$Next.'"><img src="./images/map/down.png" /></a>';
	}
	else {
		echo "&nbsp;";
	}?>
	</td>
	<td>&nbsp;</td>
</tr>
</table>
<?php
bw_tableau_end();
