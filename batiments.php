<?php 
/*
+---------------------
|Nom: Les b�timents
+---------------------
|Description: Montres les dif�rents b�timents
+---------------------
|Date de cr�ation: Avril 04
|Date du premier test: Avril 04
|Derni�re modification: 24 Mars 05 -> pour chaque niveau, on a un onglet
|						14 Aout 05 -> Multi Provinces / Cases 
+---------------------
*/
//include
include ('profil.php');

//�tat de notre ville
//Prend les variables de la province

$Niveau = $Res_Ressources['etat'];

bw_tableau_start("B�timents");

if(isset($Message)) bw_f_info("Construction commenc�e", $Message);

  //-----------------------------------------------------------------------------------------//
 //--------------------------------------------B�TIMENTS -----------------------------------//
//-----------------------------------------------------------------------------------------//

$showOnglet = false;
$Onglet = "<table width=\"100%\"><tr>\n	<th align=\"center\"><a href=\"index.php?p=const&lock=1\">Village</a> ";
if($Niveau > 1) {$showOnglet = true; $Onglet .= '&nbsp <a href="index.php?p=const&lock=2">Ville</a> '; }
if($Niveau > 2) $Onglet .= '&nbsp <a href="index.php?p=const&lock=3">Cit�</a> ';
if($Niveau > 3) $Onglet .= '&nbsp <a href="index.php?p=const&lock=4">M�tropole</a>';
$Onglet .= "</th></tr></table>\n";



if($showOnglet) bw_fieldset("Onglet de province", $Onglet);

echo "<fieldset>\n";
echo "<legend>".bw_icon("btn_build.png")."Constructions</legend>\n";
//verifie le get
if(!isset($_GET['lock'])) $lock = $Niveau;
elseif($_GET['lock'] == 1)	$lock = 1;
elseif($_GET['lock'] == 2)	$lock = 2;
elseif($_GET['lock'] == 3)	$lock = 3;
elseif($_GET['lock'] == 4)	$lock = 4;

//Pas de tricherie
$sql = "SELECT etat FROM provinces WHERE id = '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."'";
$req = sql_query($sql);
$res = mysql_fetch_array($req);
$Etat = $res['etat'];
if($lock > $Etat) $lock = $Etat;

//selection les b�timents
$req = "SELECT * FROM `liste_batiments` WHERE `niveau` = '".$lock."' ORDER BY `num_batiment` ASC";
$result = sql_query($req);
while($res = mysql_fetch_array($result))
{
	//Variables
	$IdBati = $res['id'];
	$BAT['time'] = $res['duree'];
	$nom = $res["code_nom"];
	$niveau = $res["niveau"];
	$Vie = $res['life'];

	//Batiment secondaire donc unique?
	$Affiche = true;
	if($res['bati_principal'] == 'N')
	{
		if($_SESSION['id_province'] != $_SESSION['id_main_province'])
		{//On est sur notre province principal, donc on l'affiche
			$Affiche = false;
		}
	}
	
	//Batiment need batiment construit?
	if($res['need_bati_id'] > 0)
	{//Besoin d'un b�timent, verifions
		$sql_needbati = "SELECT id FROM batiments WHERE id_province = '".$_SESSION['id_province']."' AND value = '1' AND id_batiment = '".$res['need_bati_id']."'";
		$req_needbati = sql_query($sql_needbati);
		if(sql_rows($req_needbati) == 0)
		{//On a pas trouv�
			$Affiche = false;
		}
	}

	if($Affiche)
	{
		echo "\n<table class=\"newsmalltable\">\n";

		//Prend le b�timent dans la base de donn�es
		$sqlb = "SELECT id, value, life, time, life_total FROM batiments WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND id_batiment = '".$IdBati."'";
		$reqb = sql_query($sqlb);
		$nbr = mysql_num_rows($reqb);
		
		//premi�re ligne avec le nom et le co�t en temps
		$Duree = $res['duree']*$CONF['vitesse_jeu'];
		if($Joueur->race == 5) {
			$Duree -= ($CONF['bonus_rebelles_2']/3600);
		}

		# En dessous de z�ro? Bug vitesse jeu
		if($Duree < 0)$Duree = 0;

		echo "<tr>"; 
		echo '<th width="600px" colspan="2">'.$res["nom"].' ['.$Duree .'Heures]</td>';
		echo "</tr>\n";
		//2�me ligne avec description, co�ts et images-"bouton" construire
		echo '<tr>';
		echo '<td width="500px">';
		//co�t	
		echo '<table width="100%" border="0"><tr>';
		//images

		if($Joueur->race == 4) {//on est un elfe donc on a des r�ductions de prix
			$materiaux = ceil($res['materiaux']*$CONF['bonus_elfes_2']);
		}

		else {//Nous sommes pas elfe donc les ressources sont normales
			$materiaux = $res['materiaux'];
		}

		echo '<td style="border:none; background:none"><img src="images/icons/sgold.png" alt="Or" title="Or"/>'.$res['or'].'</td>';
		echo '<td style="border:none; background:none"><img src="images/icons/sfood.png" alt="Nourriture" title="Nourriture"/>'.$res['champ'].'</td>';
		echo '<td style="border:none; background:none"><img src="images/icons/smat.png" alt="Materiaux" title="Materiaux"/>'.$materiaux.'</td>';
		echo '<td style="border:none; background:none"><img src="images/icons/speasant.png" alt="Paysans" title="Paysans Disponnibles"/>'.$res['paysan'].'</td>';
		echo '<td style="border:none; background:none"><img src="images/icons/scases.png" alt="Cases" title="Cases"/>'.$res['cases'].'</td>';
		echo '</tr></table>';
		echo $res["power"];
		echo '</td>';

		//construction
		echo "<td width=\"100px\"><center>\n";

		if ($nbr == 0) {//Pas encore construit
			echo "<a href=\"index.php?p=baticons&batid=".$IdBati."\">Construire:<br/><img src=\"images/icons/construire.png\" border=\"0\"/></a>\n";
		} else {//En construction / Construit
			$resb = mysql_fetch_array($reqb);

			if ($resb['value'] == 0) {//En construction
				echo "En construction<br />Termin� le ".date($CONF['game_timeformat'], $resb['time'])."\n";
			} elseif ($resb['value'] == 1) {//Construit
				echo "Construit!<br />\n(".$resb['life']."/".$Vie.")\n";
				
				//R�parration
				if($resb['life'] < $Vie)
				{
					echo "<strong>R�parations:</strong><br >\n";
					echo "<form method=POST action=\"?p=reparation&id=".$resb['id']."\">\n";
					echo "Paysans:<input type=\"text\" name=\"nbpaysans\" size=\"1\" maxlength=\"3\">\n";
					echo "<input type=\"submit\" value=\"R�parer\"\n";
					echo "</form>\n";
				}
			}
			elseif($resb['value'] == 3)
			{
				echo "<strong>En r�paration :</strong> (".$resb['life']."/".$Vie.")<br />\n";
				echo "Fin des travaux le ".date($CONF['game_timeformat'], $resb['time'])."<br />\n";
			} else {
				echo "Erreur! Contactez l'admin! ".$resb['value']."<br />\n";
			}
		}	
		echo "</td>\n";
		echo "</tr></table>\n";
	}//End if on peut l'afficher
}//End While

echo "</fieldset><br />\n";


echo "<fieldset>\n";
echo "<legend>�volution</legend>\n";
 //----------------------------------------------------------------//
 //-------------------------------LVL UP--------------------------//
//---------------------------------------------------------------//
//Nombre nombre de b�timents
$sql = "SELECT etat FROM provinces WHERE `id` = '".$_SESSION['id_province']."'";
$req = sql_query($sql);
$res = mysql_fetch_array($req);

//Nb de bon b�timents
$sql_b = "SELECT id FROM batiments WHERE id_province = '".$_SESSION['id_province']."' AND value = '1' and id_batiment < 500 and id_batiment > 0";
$req_b = sql_query($sql_b);
$Joueur_Bati = mysql_num_rows($req_b);

$Province_Etat = $res['etat'];

if($_SESSION['id_province'] != $_SESSION['id_main_province']) {
	$req = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '1' && bati_principal = 'O'");
	$req2 = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '2' && bati_principal = 'O'");
	$req3 = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '3' && bati_principal = 'O'");
} else {
	$req = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '1'");
	$req2 = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '2'");
	$req3 = sql_query("SELECT nom FROM liste_batiments WHERE niveau = '3'");
}

//compte le nombre de b�timents besoin
	//lvl 1
	$nombre1 = mysql_num_rows($req); //nombre d'entr�es

	//lvl 2
	$nombre2 = mysql_num_rows($req2)+$nombre1; //nombre d'entr�es

	//lvl 3
	$nombre3 = mysql_num_rows($req3)+$nombre2; //nombre d'entr�es

	//verifie d'apr�s notre niveau actuelle
	if($Province_Etat == 1)
	{//verifie notre nombre de b'atiments
		if($Joueur_Bati == $nombre1)
		{//ok
			echo '<FORM METHOD=POST ACTION="index.php?p=batilvlup">';
			echo '<INPUT TYPE="submit" VALUE="�voluer � une Ville">';
			echo '</FORM>';
		}
		else
		{
			echo bw_info("Vous n'avez pas encore tous les b�timents construits!<br />\n");
		}
	}
	elseif($Province_Etat == 2)
	{//verifie notre nombre de b'atiments
		if($Joueur_Bati == $nombre2) //$nombre2
		{//ok
			echo '<FORM METHOD=POST ACTION="index.php?p=batilvlup">';
			echo '<INPUT TYPE="submit" VALUE="�voluer � une Cit�">';
			echo '</FORM>';
		}
		else
		{
			echo bw_info("Vous n'avez pas encore tous les b�timents construits!<br />\n");
		}
	}
	elseif($Province_Etat == 3)
	{//verifie notre nombre de b'atiments
		if($Joueur_Bati == $nombre3)
		{//ok
			echo '<FORM METHOD=POST ACTION="index.php?p=batilvlup">';
			echo '<INPUT TYPE="submit" VALUE="�voluer � une M�tropole">';
			echo '</FORM>';
			//echo bw_info("La M�tropole n'est pas encore d�velopp�e.<br />\n");
		}
		else
		{
			echo bw_info("Vous n'avez pas encore tous les b�timents construits!<br />\n");
		}
	}
	elseif($Province_Etat == 4)
	{//verifie notre nombre de b'atiments
		if($Joueur_Bati == $nombre4)
		{//ok
			echo '<FORM METHOD=POST ACTION="index.php?p=batilvlup">';
			echo '<INPUT TYPE="submit" VALUE="�voluer � une Megalopole">';
			echo '</FORM>';
		}
		else
		{
			echo bw_info("Vous n'avez pas encore tous les b�timents construits!<br />\n");
		}
	}
//-------------------END LVL UP--------------------
echo "</fieldset>\n";

bw_tableau_end();
?>
