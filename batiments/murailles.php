<?php

if(!$WoodMuraille && !$StoneMuraille && !$GranitMuraille) {
	bw_fieldset("Erreur", "Vous devez construire la muraille!");
	breakpage();
}

//Ressources de la ville utilisé dans le script
$sqlM = "SELECT mat, craft, muraille_normal, muraille_enchante, muraille_magie FROM provinces "
. "WHERE id = '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."'";
$reqM = sql_query($sqlM);
$resM = mysql_fetch_array($reqM);

//On achete quelquechose?
$Message = '';
if(isset($_POST['type']))
{
	if($_POST['type'] == 'normal')
	{
		if($WoodMuraille) {//Ok
			$Nbr = clean($_POST['nombre']);
			$Cases = $Nbr + $resM['muraille_normal'];
			
			//Cases?
			if($resM['muraille_normal'] == $CONF['m_norm_max']) {
				$Message = bw_error("Votre Muraille Normale est déjà au complet!");
			} 
			else
			{
				if($Cases > $CONF['m_norm_max']) 
				{//Recalcule le nombre de cases
					$Moins = $Cases - $CONF['m_norm_max'];
					$Nbr -= $Moins;
				}
				$Prix = $Nbr * $CONF['m_norm_price_mat'];
				if($Prix <= $resM['mat'])
				{
					//Update province
					$Up = "UPDATE provinces SET muraille_normal = (muraille_normal+".$Nbr."), mat = (mat-".$Prix.") WHERE id = '".$_SESSION['id_province']."';";
					sql_query($Up);
					$Message = bw_info("Votre Muraille Normale a été améliorée!<br />");
				} else {
					$Message = bw_error("Vous n'avez pas assez de matériaux.");
				}
			}
		}

	}
	elseif($_POST['type'] == 'enchante')
	{
		if($StoneMuraille) {//Ok
			$Nbr = clean($_POST['nombre']);
			$Cases = $Nbr + $resM['muraille_enchante'];
			
			//Cases?
			if($resM['muraille_enchante'] == $CONF['m_ench_max']) {
				$Message = bw_error("Votre Muraille Enchantée est déjà au complet!");
			} 
			else
			{
				if($Cases > $CONF['m_ench_max']) 
				{//Recalcule le nombre de cases
					$Moins = $Cases - $CONF['m_ench_max'];
					$Nbr -= $Moins;
				}
				$Prix_Mat = $Nbr * $CONF['m_ench_price_mat'];
				$Prix_Craft = $Nbr * $CONF['m_ench_price_craft'];
				if($Prix_Mat <= $resM['mat'] && $Prix_Cradt <= $resM['craft'])
				{
					//Update province
					$Up = "UPDATE provinces SET muraille_enchante = (muraille_enchante+".$Nbr."), mat = (mat-".$Prix_Mat."), craft = (craft-".$Prix_Craft.") WHERE id = '".$_SESSION['id_province']."';";
					sql_query($Up);
					$Message = bw_info("Votre Muraille Enchantée a été améliorée!<br />");
				} else {
					$Message = bw_error("Vous n'avez pas assez de matériaux ou de magie.");
				}
			}
		}
	}
	elseif($_POST['type'] == 'magie')
	{
		if($StoneMuraille) {//Ok
			$Nbr = clean($_POST['nombre']);
			$Cases = $Nbr + $resM['muraille_magie'];
			
			//Cases?
			if($resM['muraille_magie'] == $CONF['m_magi_max']) {
				$Message = bw_error("Votre Murailel d'Énergie est déjà au complet!");
			} 
			else
			{
				if($Cases > $CONF['m_magi_max']) 
				{//Recalcule le nombre de cases
					$Moins = $Cases - $CONF['m_magi_max'];
					$Nbr -= $Moins;
				}
				$Prix = $Nbr * $CONF['m_granit_price_craft'];
				//die(  $Prix ."  <= ". $resM['stone'] ." && ".  $PrixCraft ." <= ".  $resM['craft']."<br />");
				if($Prix <= $resM['craft'])
				{
					//Update province
					$Up = "UPDATE provinces SET muraille_magie = (muraille_magie+".$Nbr."), craft = (craft-".$Prix.") WHERE id = '".$_SESSION['id_province']."';";
					sql_query($Up);
					$Message = bw_info("Votre Muraille d'Énergie a été améliorée!<br />");
				} else {
					$Message = bw_error("Vous n'avez pas assez de magie.");
				}
			}
		}
	}
	
	// On a traité un cas, on recharge les données sur les murailles.
	$sqlM = "SELECT muraille_normal, muraille_enchante, muraille_magie FROM provinces "
	. "WHERE id = '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."'";
	$reqM = sql_query($sqlM);
	$resM = mysql_fetch_array($reqM);	
}

require('./profil.php');
bw_tableau_start("Murailles");

$Message .= "Les murailles entourent votre province et encaisse les attaques des assaillants. Développez ces murailles pour protéger vos bâtiments, paysans et ressouces!";
if(isset($Message)) bw_f_info("Information", $Message);

//Murailles

if($WoodMuraille)
{
	bw_f_start("Muraille Normale");
	echo "<table class=\"newsmalltable\"><tr><td>\n";
	echo "Vous possédez actuellement <strong>".$resM['muraille_normal']."/".$CONF['m_norm_max']."</strong> plaques Normale.<br />\n";

	echo "Chaque &quot;Plaque&quot;Normale vous protège de ".$CONF['m_norm_power']." dégats et vous coute <strong>".$CONF['m_norm_price_mat']." en mat&eacute;riaux.</strong><br />\n";

	echo "<br /><form method=\"post\" action=\"?p=bsp_murailles\">\n";
	echo "<strong>Combien de plaques voulez-vous construire?</strong><br />\n";
	//echo "<select name=\"nombre\">\n";
	echo "<input type=\"text\" name=\"nombre\" maxlength=\"3\" /> ";
	echo bw_submit("Construire")."\n";
	echo "<input type=\"hidden\" name=\"type\" value=\"normal\" />\n";
	echo "</form><br />\n";
	echo "</td></tr></table>\n";
	bw_f_end();
}
if($StoneMuraille)
{
	bw_f_start("Muraille Enchantée");
	echo "<table class=\"newsmalltable\"><tr><td>\n";
	echo "Vous possédez actuellement <strong>".$resM['muraille_enchante']."/".$CONF['m_ench_max']."</strong> plaques Enchant&eacute;e.<br />\n";

	echo "Chaque &quot;Plaque&quot; Enchant&eacute;e vous protège de ".$CONF['m_ench_power']." dégats et vous coute <strong>".$CONF['m_ench_price_craft']." en magie et ".$CONF['m_ench_price_mat']." en mat&eacute;riaux.</strong><br />\n";

	echo "<br /><form method=\"post\" action=\"?p=bsp_murailles\">\n";
	echo "<strong>Combien de plaques voulez-vous construire?</strong><br />\n";
	//echo "<select name=\"nombre\">\n";
	echo "<input type=\"text\" name=\"nombre\" maxlength=\"3\" /> ";
	echo bw_submit("Construire")."\n";
	echo "<input type=\"hidden\" name=\"type\" value=\"enchante\" />\n";
	echo "</form><br />\n";
	echo "</td></tr></table>\n";
	bw_f_end();
}
if($GranitMuraille)
{
	bw_f_start("Muraille d'Énergie");
	echo "<table class=\"newsmalltable\"><tr><td>\n";
	echo "Vous possédez actuellement <strong>".$resM['muraille_magie']."/".$CONF['m_magi_max']."</strong> plaques d'&Eacute;nergie.<br />\n";

	echo "Chaque &quot;Plaque&quot; d'&Eacute;nergie vous protège de ".$CONF['m_magi_power']." dégats et vous coute <strong>".$CONF['m_magi_price_craft']." en magie.</strong><br />\n";

	echo "<br /><form method=\"post\" action=\"?p=bsp_murailles\">\n";
	echo "<strong>Combien de plaques voulez-vous construire?</strong><br />\n";
	//echo "<select name=\"nombre\">\n";
	echo "<input type=\"text\" name=\"nombre\" maxlength=\"3\" /> ";
	echo bw_submit("Construire")."\n";
	echo "<input type=\"hidden\" name=\"type\" value=\"magie\" />\n";
	echo "</form><br />\n";
	echo "</td></tr></table>\n";
	bw_f_end();
}
bw_tableau_end();
?>