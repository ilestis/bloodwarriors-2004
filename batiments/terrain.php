<?php
//Permet de changer le nom de la province

require ('./profil.php');

//Variables des couts

#Coûts_Ferme
echo "<table class=\"newtable\">\n";
	echo "	<tr>\n";
	echo "		<td class=\"newtitre\">Terrain</td>\n";
	echo "	</tr><tr>\n";
	echo "		<td class=\"newcontenu\"><fieldset><legend>Information</legend>Plus votre province devient importante, plus elle prendra de place. Malheureusement, vous êtes limités en place de construction. Mais grace à divers bâtiments, vous pouvez, pour une petite somme, transformer le terrain non-utilisable, en terrain adéquat pour la construction.</fieldset><br />\n";

	echo "		<fieldset>\n";
	echo "			<legend>Ferme</legend>\n";
	
if (bw_batiavailable('ferme', false)) {//Ok
	if (isset($_GET['do']) && ($_GET['do'] == 'work')) {//On travail
		$sql = "SELECT cases_notusuable, gold FROM provinces WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id = '".$_SESSION['id_province']."'";
		$req = sql_query($sql);
		$res = mysql_fetch_array($req);	


		//Variables
		$Cases_Terraform = (is_numeric($_POST['cases']) ? clean($_POST['cases']) : 0);
		$Cases_Available = $res['cases_notusuable'];
		$Cases_Newvalue = $Cases_Available - $Cases_Terraform;
		$Gold_Newvalue = $res['gold'] - ($Cases_Terraform*$CONF['bati_cout_ferme_or']);
		$Cost = ($Cases_Terraform*$CONF['bati_cout_ferme_or']);

		if (($Gold_Newvalue >= 0) && ($Cases_Newvalue >= 0) && ($Craft_Newvalue >= 0)) {//Ok
			$Cases_Up = "UPDATE provinces SET cases_notusuable = (cases_notusuable-".$Cases_Terraform."), cases_usuable = (cases_usuable+".$Cases_Terraform."), gold = '".$Gold_Newvalue."' WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id = '".$_SESSION['id_province']."'";
			sql_query($Cases_Up);

			echo "<span class=\"info\">Pour ".$Cost." or, vous avez fait terraformer ".$Cases_Terraform." case".pluriel($Cases_Terraform, 's')." inexploitable".pluriel($Cases_Terraform, 's')." en case".pluriel($Cases_Terraform, 's')." exploitable".pluriel($Cases_Terraform, 's').".</span><br />\n";

		} else {
			echo "<span class=\"avert\">Soit vous demandez de terraformer plus de cases que vous ne possédez, soit vous n'avez pas assez d'or.</span><br />\n";
		}

	}
	
	$sql = "SELECT cases_usuable, cases_notusuable, cases_total FROM provinces WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id = '".$_SESSION['id_province']."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);

	$Cases_Total_Usuable = $res['cases_total'] - $res['cases_notusuable'];

	echo "			Transformez des cases non-utilisables en cases utilisables.<br />\n";
	echo "			<table class=\"newsmalltable\">\n";
	echo "			<tr>\n";
	echo "				<th colspan=\"2\">Travaux possibles:</th>\n";
	echo "			</tr>\n";
	echo "			<tr>\n";
	echo "				<td width=\"50%\">Cases disponnibles: ".$res['cases_usuable']." sur ".$Cases_Total_Usuable."</td>\n";
	echo "				<td width=\"50%\">Cases non-utilisables: ".$res['cases_notusuable']."</td>\n";
	echo "			<tr>\n";
	echo "			<tr>\n";
	echo "				<td colspan=\"2\">&nbsp;</td>\n";
	echo "			</tr>\n";
	echo "			<tr>\n";
	echo "				<th colspan=\"2\">Effectuer des travaux:</th>\n";
	echo "			</tr>\n";
	echo "			<tr>\n";
	echo "				<td>Attention, pour chaque case que vous terraformez, cela vous coûtera ".$CONF['bati_cout_ferme_or']." or.</td>\n";
	echo "				<td>\n";
	echo "					<FORM METHOD=\"POST\" ACTION=\"?p=bsp_terrain&do=work\">\n";
	echo "					<select name=\"cases\">\n";
	for($i = 1; $i <= $res['cases_notusuable']; $i++)
		echo "						<option value=\"".$i."\">".$i."</option>\n";
	echo "					</select>&nbsp;\n";
	echo "					<INPUT TYPE=\"submit\" value=\"Terraformer\">\n";
	echo "					</FORM>\n";
	echo "				</td>\n";
	echo "			</tr>\n";
	echo "			</table>\n";
}//Fin - If Ferme == 1
else
{
	echo "		<span class=\"avert\">Il faut construire la Ferme pour accéder à ces pouvoir.</span>\n";
}

echo "		</fieldset><br />\n";	

echo "		<fieldset>\n";
echo "			<legend>Maçonnerie</legend>\n";

#Coûts_Maçonnerie
if (bw_batiavailable('maconnerie', false))
{//On a la maçonnerie

	echo "			<strong>Découvrez des cases jusqu'alors inconnues.</strong><br />\n";

	if(isset($_GET['do']) && ($_GET['do'] == 'terra')) 
	{//On terraforme	

		$Nombre = clean($_POST['cases']);
		$Prix_Gold = $CONF['bati_cout_maconnerie_or']*$Nombre;
		$Prix_Mat = $CONF['bati_cout_maconnerie_mat']*$Nombre;
		$New_Gold = $Res_Ressources['gold'] - $Prix_Gold;
		$New_Mat = $Res_Ressources['mat'] - $Prix_Mat;

		if(($New_Gold >= 0) && ($New_Stone >= 0))
		{//Il a assez
			$Up = "UPDATE provinces SET gold = '".$New_Gold."', mat = '".$New_Mat."', cases_usuable = (cases_usuable+'".$Nombre."'), cases_total = (cases_total+'".$Nombre."') WHERE id = '".$_SESSION['id_province']."'";
			sql_query($Up);

			echo $Nombre." cases ont été découvertes pour la somme de ".$Prix_Gold." or et ".$Prix_Mat." mat&eacute;riaux.<br />\n";
		}
		else
		{//Pas assez de ressource
			echo "<span class=\"avert\">Vous n'avez pas assez de ressources!</span><br />\n";
		}
	}
		

	echo "			<table class=\"newsmalltable\">\n";
	echo "			<tr>\n";
	echo "				<td>Pour la modique somme de ".$CONF['bati_cout_maconnerie_or']." or et ".$CONF['bati_cout_maconnerie_mat']." matériaux, vous pouvez explorer une nouvelle case (qui sera directement utilisable).</td>\n";
	echo "			</tr><tr>\n";
	echo "				<th>Effectuer des travaux</th>\n";
	echo "			</tr><tr>\n";
	echo "				<td>\n";
	echo "				<FORM METHOD=POST ACTION=\"?p=bsp_terrain&do=terra\">\n";
	echo "				Nombre de cases à exploiter: <INPUT TYPE=\"text\" NAME=\"cases\" maxlength=\"3\"> <INPUT TYPE=\"submit\" value=\"Exploiter\">\n";
	echo "				</FORM>\n";
	echo "				</td>\n";
	echo "			</tr>\n";
	echo "			</table>\n";
}
else
{
	echo "			<span class=\"avert\">Vous devez construire la maçonnerie!</span>";
}
echo "			</fieldset>\n";




echo "</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newfin\"><br /></td>\n";
echo "	</tr>\n";
echo "</table>\n";
echo "<br />\n";