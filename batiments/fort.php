<?php 

//Tentes
$sql = "SELECT id FROM batiments WHERE id_province = '".$_SESSION['id_province']."' AND value = '1' AND id_batiment = '".$CONF['bati_tente_id']."'";
$req = sql_query($sql);
$nb_tentes = mysql_num_rows($req);
$nb_max_unites = $nb_tentes*$CONF['war_tente_capa'];
if($Joueur->race == 2) $nb_max_unites = $nb_tentes*($CONF['war_tente_capa']+$CONF['bonus_barbares_2']);

$sql = "SELECT id FROM armees WHERE id_province = '".$_SESSION['id_province']."'";
$req = sql_query($sql);
$nb_unites = mysql_num_rows($req);

$nb_libre = $nb_max_unites - $nb_unites;

# Multiplicateur co�ts en nourriture
$Multiplicateur_Food = 1;

$Valeur_Sort = check_spell($_SESSION['id_province'], '36');
if($Valeur_Sort > 0) {
	$Multiplicateur_Food *= $Valeur_Sort;
}
$Valeur_Sort = check_spell($_SESSION['id_province'], '37');
if($Valeur_Sort > 0) {
	$Multiplicateur_Food *= $Valeur_Sort; 
}

# ON ENGAGE DES UNIT�S ---------------------------
if (isset($_GET['id']))
{
	$Sacrifice = 0;
	$messageFortTMP = "";
	$nbrTMP = 0;

	//Nombre de place dispo apr�s le choix du nombre
	//$nb_libre_second = $nb_libre - clean($_POST['nombre']);

	if (isset($_POST['sacrifice']) && $_POST['sacrifice'] == "on") $Sacrifice = 1;
	if($_GET['id'] == 'all')
	{//on prend une invocation g�n�rale
		$sql = "SELECT * FROM `liste_invocations` WHERE `ID` = '".clean($_POST['choix_invocation'])."'";
		$req = sql_query($sql);
		$ident = mysql_num_rows($req);
		if(($ident == 1))
		{//la cr�ature existe	
			$res = mysql_fetch_array($req);
			$Nom = $res['nom'];
			$Type = $res['type'];
			$Cost_Food = $res['cost_food'];

			if ($Joueur->race == 3)
			{
				$Cost_Food = ceil($Cost_Food * $CONF['bonus_demons_2']);
			}
			$Cost_Food = ceil($Multiplicateur_Food*$Cost_Food);

			//nos ressource 
			$sqlM = "SELECT gold, food, craft FROM provinces WHERE id = '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."'";
			$reqM = sql_query($sqlM);
			$resM = mysql_fetch_array($reqM);
			$PLAY['gold'] = $resM['gold'];
			$PLAY['food'] = $resM['food'];
			$PLAY['craft'] = $resM['craft'];

			//on regarde si l'unit�s selectionne � besoin d'un b�timents
			$OkRoyaume = 1;
			$OkBuilding = 1;
			$OkTentes = 1;
			$OkRessources = 1;

			if($res['need_bulding'] == 1)
			{//oui
				//selectionne le nom du b�timents
				$ba = "SELECT code_nom  FROM liste_batiments WHERE `niveau` = '".$res['need_bulding_lvl']."' AND `num_batiment` = '".$res['need_bulding_id']."'";
				$req2 = sql_query($ba);
				$res2 = mysql_fetch_array($req2);

				//selectionne l'�tat du batiments ( 0=no, 1 = en constru, 2 = construit)
				$ch = "SELECT life, life_total FROM batiments WHERE codename = '".$res2['code_nom']."' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND value = '1'";
				$chq = sql_query($ch);
				$chnbr = mysql_num_rows($chq);
				$chr = mysql_fetch_array($chq);

				$Seuil_Life = floor($chr['life_total']*$CONF['bati_min_life']);

				if($chnbr == 1)
				{//il est construit donc ok!
					if($chr['life'] >= $Seuil_Life)
					{
						$OkBuilding = 1;
					}
					else
					{
						$OkBuilding = 2;
					}
				}
				else
				{//il n'a pas le b�timents
					$OkBuilding = 0;
				}
			}//fin if batiments
			$OkRoyaume = 1;
			if($res['race'] != 0)
			{//on a besoin d'�tre d'un royaume sp�cial!
				if($res['race'] == $Joueur->race) 
				{//c'est le bon royaume
					$OkRoyaume = 1;
				}
				else
				{
					$OkRoyaume = 0;
				}
			}//fin besoin du royaume

			//on s'occupe de chaque unit�
			for($i=1;$i<=clean($_POST['nombre']);$i++)
			{
				//R�duit le nombre de place libres
				$nb_libre --;

				//Verifie donc si on en a encore
				if ($nb_libre >= 0)
				{
					$OkTentes = 1;
				}
				else $OkTentes = 0;

	/*	WTF???	$Cost_Food = $res['cost_food'];
				if ($Joueur->royaume == 3)
				{
					$Cost_Food = ceil($Cost_Food * $CONF['bonus_demons_2']);
				}*/
				//ressources
				$PLAY['food']	-=	$Cost_Food;
				$PLAY['gold']	-=	$res['cost_gold'];
				$PLAY['craft']	-=	$res['cost_craft'];

				//Teste ressources
				if(($PLAY['food'] < 0) || ($PLAY['gold'] < 0) || ($PLAY['craft'] < 0))
				{ // Pas assez, buuug, et on remet � la bonne valeure merci
					$OkRessources = 0;
					$PLAY['food']	+=	$Cost_Food;
					$PLAY['gold']	+=	$res['cost_gold'];
					$PLAY['craft']	+=	$res['cost_craft'];
				}
	
				//verifie les ressource
				if(($OkRessources == 1) && ($OkBuilding == 1) && ($OkRoyaume == 1) && ($OkTentes == 1))
				{//ok
					//on ajoute l'unit�s
					$arm = "INSERT INTO `armees` ";
					$arm .= "VALUES ('', '".$_SESSION['id_joueur']."', '".$_SESSION['id_province']."',  '".$res['ID']."','".$res['nom']."', '".$res['power_1']."', '".$res['power_2']."', '".$res['power_3']."', '".$res['power_4']."', '1', '', '', '".$Sacrifice."', '".$res['entretient']."', '".$res['puissance']."', '".$Type."')";
					sql_query($arm);
					//echo "<p>Debug Kaio:<br />$arm</p>\n";

					$nbrTMP++;
				}
				else
				{//pas assez de ressources
					if($OkBuilding == 0) $messageFortTMP .= "<span class=\"info\">Vous n'avez pas le b�timent n�cessaire pour engager cette unit�!</span><br />\n";
					if($OkRoyaume == 0) $messageFortTMP .= "<span class=\"info\">Vous n'�tes pas de la bonne race pour engager cette unit�!</span><br />\n";
					if($OkRoyaume == 2) $messageFortTMP .= "<span class=\"info\">Le b�timent n�cessaire pour engager cette unit� est en trop mauvais �tat!</span><br />\n";
					if($OkTentes == 0) $messageFortTMP .= "<span class=\"info\">Vous n'avez plus de place dans vos tentes!</span><br />\n";
					if($OkRessources == 0) $messageFortTMP .= "<span class=\"info\">Vous n'avez pas assez de ressources!</span><br />\n";

					break;
				}
			}
			// Met � jour nos ressources
			
			//update nos stats
			$ress = "UPDATE provinces SET `gold` = '".$PLAY['gold']."', food = '".$PLAY['food']."', craft = '".$PLAY['craft']."' WHERE id_joueur = '".$_SESSION['id_joueur']."' AND `id` = '".$_SESSION['id_province']."'";
			sql_query($ress);
						
			$messageFortTMP .= "<span class=\"info\">".$nbrTMP." ".$Nom." ont rejoint votre arm�e!</span><br />\n";
		}
		else
		{//cr�ature existe pas
			$messageFortTMP .= "<span class=\"info\">Cette unit�s n'existe pas!</span><br />\n";
		}
	}
}
/* ------------------------------------- */

require('./profil.php');

echo "<table class=\"newtable\">\n";
echo "	<tr>\n";
echo "		<td class=\"newtitre\">Engager des unit�s de guerre</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newcontenu\">\n";

$text = "Vous poss�dez actuellement <span class=\"info\">".$nb_unites."</span> unit�".pluriel($nb_unites, 's').".<br />\n";
if(isset($messageFortTMP)) { $text .= $messageFortTMP."<br />\n"; $messageFortTMP = ''; };

//Verifie les tentes
if($nb_libre == 0)
{
	$text .= bw_info("Vous n'avez plus de places libres pour engager des unit�s! Achetez d'abord des tentes sous [G�rer vos unit�s]!<br />\n");

	bw_f_info("Informations", $text);
	
	$_SESSION['message'] = '';
}
else
{
	bw_f_info("Informations", $text);	
	?><br />


	<!-- UNITES POUR TOUT LE MONDE -->
	<form name="form1" method="post" action="index.php?p=bsp_fort&id=all">
	<fieldset>
		<legend>Unit�s G�n�rales (engageable par tous les H�ros)</legend>
		<table class="newsmalltable">
		<tr>
			<th>Nom</th>
			<th>Statistique</th>
			<th>Co�t</th>
			<!--<th>Entretient</th>-->
			<th>Selecteur</th>
		</tr>
	<?php
	//selectionne les cr�atures � toutes provinces
	$sql = "SELECT * FROM `liste_invocations` WHERE `race` = '0' ORDER BY puissance ASC";
	$req = sql_query($sql);
	while($Invo = mysql_fetch_array($req))
	{
		//on prend le tout, et on test pour les b'atiments
		if ($Invo['need_bulding'] == 1)
		{//on a besoin d'un b�timents
			//selectionne le nom du b�timents
			$ba = "SELECT code_nom FROM liste_batiments WHERE `niveau` = '".$Invo['need_bulding_lvl']."' AND `num_batiment` = '".$Invo['need_bulding_id']."'";
			$req2 = sql_query($ba);
			$res = mysql_fetch_array($req2);

			$ech = 0;

			if(bw_batiavailable($res['code_nom'], false))
			{
				$ech = 1;
			}

			/*if($cheak['value'] == 1)
			{//il est construit donc ok!
				//Verifie les vies...
				$Seuil_Life = floor($cheak['life_total']*$CONF['bati_min_life']);
				if($cheak['life'] >= $Seuil_Life)
				{
					$ech = 1;
				}
			}*/
		}
		else
		{//on a pas besoin de b�timents
			$ech = 1;
		}//fin selecteur batiments
		if($ech == 1)
		{
			$Cost_Food = $Invo['cost_food'];
			if ($Joueur->race == 3)
			{
				$Cost_Food = ceil($Cost_Food * $CONF['bonus_demons_2']);
			}
			$Cost_Food = ceil($Multiplicateur_Food*$Cost_Food);

			echo '<tr><td>'.$Invo['nom'].'</TD>';
			//echo '<td>'.$Invo['type'].'</td>';
			echo '<td>['.$Invo['power_1'].'] ['.$Invo['power_2'].']['.$Invo['power_3'].']['.$Invo['power_4'].']</TD>';
			echo '<td>[Or:'.$Invo['cost_gold'].'][Nour.:'.$Cost_Food.'] [Magie:'.$Invo['cost_craft'].']</td>';
			//echo '<td>'.$Invo['entretient'].'</td>';
			echo '<td><INPUT TYPE="radio" NAME="choix_invocation" VALUE="'.$Invo['ID'].'"></TD></TR>';
		}
	}//fin while normal
?>
	<tr>
		<th colspan="2">
			Nombre d'engagements: <select name="nombre">
			<?php
			for($i=1;$i<=49;$i++)
			{
				echo '<option value="'.$i.'">'.$i.'</option>';
			}	
			?>
			</select>
		</th>
		<th>
			Sacrifice: <INPUT TYPE="checkbox" NAME="sacrifice">
		</th>
		<th><INPUT TYPE="submit" value="Engager"></th>
	</tr>
	</table>
	</fieldset>
	</form><br />

	<?php
	if(bw_batiavailable('forgerie', false))
	{//on peut invoquer les unit�s de notre race
		?>
		<fieldset>
			<legend>Unit�s des <?php echo return_guilde($Joueur->race, $Joueur->lang).'(r�serv�es aux '.return_guilde($Joueur->race, $Joueur->lang).')'; ?></legend>

			<table class="newsmalltable">
			<tr>
				<th>Nom</th>
				<th>Statistique</th>
				<th>Co�t</th>
				<!--<th>Entretient</th>-->
				<th>Selecteur</th>
			</tr>

			<form name="form1" method="post" action="index.php?p=bsp_fort&id=all">

		<?php
		//selectionne les cr�atures de notre province
		$sql = "SELECT * FROM `liste_invocations` WHERE `race` = '".$Joueur->race."'";
		$req = sql_query($sql);
		while($Invo = mysql_fetch_array($req))
		{
			//on prend le tout, et on test pour les b'atiments
			if ($Invo['need_bulding'] == 1)
			{//on a besoin d'un b�timents
				//selectionne le nom du b�timents
				$ba = "SELECT code_nom FROM liste_batiments WHERE `niveau` = '".$Invo['need_bulding_lvl']."' AND `num_batiment` = '".$Invo['need_bulding_id']."'";
				$req2 = sql_query($ba);
				$res = mysql_fetch_array($req2);
				//selectionne l'�tat du batiments ( 0=no, 1 = en constru, 2 = construit)
				$ech = 0;

				if(bw_batiavailable($res['code_nom'], false))
				{
					$ech = 1;
				}
			}
			else
			{//on a pas besoin de b�timents
				$ech = 1;
			}//fin selecteur batiments
			if($ech == 1)
			{
				$Cost_Food = $Invo['cost_food'];
				if ($Joueur->race == 3)
				{
					$Cost_Food = ceil($Cost_Food * $CONF['bonus_demons_2']);
				}
				$Cost_Food = ceil($Multiplicateur_Food*$Cost_Food);

				echo '<tr><td>'.$Invo['nom'].'</TD>';
				//echo '<td>'.$Invo['type'].'</td>';
				echo '<td>['.$Invo['power_1'].']['.$Invo['power_2'].']['.$Invo['power_3'].']['.$Invo['power_4'].']</TD>';
				echo '<td>[Or:'.$Invo['cost_gold'].'] [Nou.:'.$Cost_Food.'] [Magie:'.$Invo['cost_craft'].']</td>';
				//echo '<td>'.$Invo['entretient'].'</td>';
				echo '<td><INPUT TYPE="radio" NAME="choix_invocation" VALUE="'.$Invo['ID'].'"></td></tr>';
			}
		}//fin while normal
	?>
			<tr>
				<th colspan="2">
					Nombre d'engagements: <select name="nombre">
					<?php
					for($i=1;$i<=49;$i++)
					{
						echo '<option value="'.$i.'">'.$i.'</option>';
					}	
					?>
					</select>
				</th>
				<th>Sacrifice: <INPUT TYPE="checkbox" NAME="sacrifice"></th>
				<th><INPUT TYPE="submit" value="Engager"></th>
			</tr>
			</form>
			</table>
		</fieldset><br />
	<?php
	}//end if race
}
?>
		<br />
		</fieldset>
			
		</td>
	</tr><tr>
		<td class="newfin">&nbsp;</td>
	</tr>
</table>