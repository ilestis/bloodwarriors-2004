<?php 
//Sélectionne notre marché et notre place du marché



$MarcherOk = bw_batiavailable('marche', false);

//Profil
require ('./profil.php');
bw_tableau_start("Marché");

if(!$MarcherOk)
{
	bw_fieldset("Information", bw_error("Votre Marché n'est pas encore construit ou endommagé!"));
	bw_tableau_end();
	breakpage();
	exit;
}


function funcAcceptEchange($id_prov_fst, $id_prov_snd, $Conf_P_Min, $id_echange)
{
	$echange = "SELECT * FROM echanges WHERE `id` = '".$id_echange."'";
	$request = sql_query($echange);
	$ech = mysql_fetch_array($request);

	$MaxRessources_1 = get_ressource_limit($id_prov_fst, $CONF);
	$MaxRessources_2 = get_ressource_limit($id_prov_snd, $CONF);


	//calcule 
	$OkRe = 0;
	$OkSe = 0;

	//On prend nos ressources
	$sqla = "SELECT gold, food, mat, craft, peasant, id_joueur FROM provinces WHERE id = '".$id_prov_fst."'";
	$reqa = sql_query($sqla);
	$resa = mysql_fetch_array($reqa);
	$REC[1]	= $resa['gold'];
	$REC[2]	= $resa['food'];
	$REC[3]	= $resa['mat'];
	$REC[4]	= 0;
	$REC[5]	= $resa['craft'];
	$REC['peasant']	= $resa['peasant'];

	//Ressources de l'envoyeur

	$sqlb = "SELECT gold, food, mat, craft, peasant, id_joueur FROM provinces WHERE id = '".$id_prov_snd."'";
	$reqb = sql_query($sqlb);
	$resb = mysql_fetch_array($reqb);
	$SEND[1]	= $resb['gold'];
	$SEND[2]	= $resb['food'];
	$SEND[3]	= $resb['mat'];
	$SEND[4]	= 0;
	$SEND[5]	= $resb['craft'];
	$SEND['peasant']	= $resb['peasant'];

	//Nouvelles ressources [recepteur]
	$REC[1]	+= $ech['golde'] - $ech['goldr'];
	$REC[2]	+= $ech['foode'] - $ech['foodr'];
	$REC[3]	+= $ech['mate'] - $ech['matr'];
	$REC[4]	= 0;
	$REC[5]	+= $ech['crafte'] - $ech['craftr'];
	$REC['peasant']	+= $ech['peasante'] - $ech['peasantr'];


	$Modif_Rec = $ech['peasante'] - $ech['peasantr'];

	//[enyoeur]
	$SEND[1]	+= $ech['goldr'] - $ech['golde'];
	$SEND[2]	+= $ech['foodr'] - $ech['foode'];
	$SEND[3]	+= $ech['matr'] - $ech['mate'];
	$SEND[5]	+= $ech['craftr'] - $ech['crafte'];
	$SEND['peasant']+= $ech['peasantr'] - $ech['peasante'];

	//Verifie que ca dépasse pas le max (et oui, on les pawn!)
	for ($i = 1; $i < 6; $i++)
	{
		if ($REC[$i] > $MaxRessources_1[$i])
			$REC[$i] = $MaxRessources_1[$i];
	
		if ($SEND[$i] > $MaxRessources_2[$i])
			$SEND[$i] = $MaxRessources_2[$i];
	}

	$Modif_Send = $ech['peasantr'] - $ech['peasante'];

	## Partie pour les paysans
	$Ok_Peasants = true;
	if ($Place_Du_Marche_Ok)
	{//On a la palce du marché
		if(($REC['peasant'] >= $Conf_P_Min) && ($SEND['peasant'] >= $Conf_P_Min))
		{
			//Prend les paysans du envoyeur
			$Ok_Peasants = true;
		}
		else
			$Ok_Peasants = false;


	}

	//Verifie
	if(($SEND[1]>=0) AND ($SEND[2]>=0) AND ($SEND[3]>=0) AND  ($SEND[5]>=0)) $OkSe = 1;
	if(($REC[1]>=0) AND ($REC[2]>=0) AND ($REC[3]>=0) AND ($REC[5]>=0)) $OkRe = 1;
	if(($OkRe == 1) AND ($OkSe == 1) && $Ok_Peasants)
	{//ok

		//update
		$upse = "UPDATE provinces SET
			`gold` = '".$SEND[1]."',
			`food` = '".$SEND[2]."',
			`mat` = '".$SEND[3]."',
			`craft` = '".$SEND[5]."',
			`peasant` = '".$SEND['peasant']."'
		WHERE id = '".$id_prov_snd."'";
		sql_query($upse);

		$upre = "UPDATE provinces SET
			`gold` = '".$REC[1]."',
			`food` = '".$REC[2]."',
			`mat` = '".$REC[3]."',
			`craft` = '".$REC[5]."',
			`peasant` = '".$REC['peasant']."'
		WHERE id = '".$id_prov_fst."'";
		sql_query($upre);

		#Paysans
		if($Ok_Peasants)
		{
			sql_query("UPDATE temp_paysans SET nombre = (nombre-'".$Modif_Send."') WHERE section = '0' AND id_province = '".$id_prov_fst."'");
			sql_query("UPDATE temp_paysans SET nombre = (nombre-'".$Modif_Rec."') WHERE section = '0' AND id_province = '".$id_prov_snd."'");
		}
		//supprime l'échange
		$del = "DELETE FROM echanges WHERE id = '".$id_echange."'";
		sql_query($del);
		//echo $debug;
		return "Echange réussit!<br />";
	}
	else
	{//pas ok
		return bw_error("L'une des 2 provinces n'a pas assez de ressources!");
	}


}


#Gestion du click
if( isset($_GET['do']))
{//on fait le blabla
	$Action = clean($_GET['do']);
	if($Action == 'send')
	{//on fait une demande
		//aVerifie qu'on ne se cible pas
		if(isset($_POST['demande']) AND ($_SESSION['id_province'] != clean($_POST['demande'])))
		{//ok
			//On va chercher l'id du joueur à qui appartiend la province
			$sql = "SELECT id_joueur, name FROM provinces WHERE id = '".clean($_POST['demande'])."'";
			$req = sql_query($sql);
			$nbr = mysql_num_rows($req);
			$res = mysql_fetch_array($req);

			//verifie qu'on a pas mis négative
			if(
				$nbr == 1 && ($_POST['givegold'] >= 0) AND ($_POST['givefood'] >= 0) AND ($_POST['givemat'] >= 0) AND  ($_POST['givecraft'] >= 0) AND ($_POST['askgold'] >= 0) AND ($_POST['askfood'] >= 0) AND ($_POST['askmat'] >= 0) AND ($_POST['askcraft'] >= 0)
			)
			{
				if(isset($_POST['givepeasant']) && ($_POST['askpeasant'] >= 0))
					$Give_Peasant = clean($_POST['givepeasant']);
				else
					$Give_Peasant = 0;

				if(isset($_POST['askpeasant']) && ($_POST['givepeasant'] >= 0))
					$Ask_Peasant = clean($_POST['askpeasant']);
				else
					$Ask_Peasant = 0;

				
				
				$insert = "INSERT INTO echanges VALUES('', '".$_SESSION['id_province']."', '".clean($_POST['demande'])."', '".clean($_POST['givegold'])."', '".clean($_POST['givefood'])."', '".clean($_POST['givemat'])."', '".clean($_POST['givecraft'])."', '".$Give_Peasant."', '".clean($_POST['askgold'])."', '".clean($_POST['askfood'])."', '".clean($_POST['askmat'])."',  '".clean($_POST['askcraft'])."', '".$Ask_Peasant."')";
				sql_query($insert);

				$id_echange = mysql_insert_id();
				

				//Si c'est pas à nous même
				if ($res['id_joueur'] != $_SESSION['id_joueur'])
				{

					//messages
					$Message = clean("Le joueur ".$Joueur->pseudo." à demandé un échange avec vous à votre province ".$res['name'].".");
					send_message('999999994', $res['id_joueur'], $Message, 1);
					
					//Son pseudo
					$resp = mysql_fetch_array(sql_query("SELECT pseudo FROM joueurs WHERE id = '".$res['id_joueur']."'"));

					$MessageInfo = "Votre demande d'échange à ".$resp['pseudo']." a bien été prise en compte!<br />";
				}
				else
				{//Fait directement
					funcAcceptEchange(clean($_POST['demande']), $_SESSION['id_province'], $CONF['paysans_min'], $id_echange);
				}
			}
			else 
				$MessageInfo = bw_error("Il faut mettre des nombres positifs!");
		}
		else
		{//on peut pas envoyer a soi-même
			$MessageInfo = bw_error("Vous devez choisir une cible valide!");
		}
	}
	elseif($Action == 'accept')
	{//on accept une offre
		$echange = "SELECT * FROM echanges WHERE `id` = '".clean($_GET['id'])."'";
		$request = sql_query($echange);
		$ech = mysql_fetch_array($request);
		if($ech['recepteur'] == $_SESSION['id_province'])
		{//ok
			$MessageInfo = funcAcceptEchange($_SESSION['id_province'], $ech['envoyeur'], $CONF['paysans_min'], $ech['id']);
		}
		else
		{//non
			$MessageInfo = bw_error("Vous n'êtes pas impliqué dans cet échange!");
		}
	}
	elseif($Action == 'delete')
	{//on annule/refuse l'offre
		$echange = "SELECT * FROM echanges WHERE `id` = '".clean($_GET['id'])."'";
		$request = sql_query($echange);
		$ech = mysql_fetch_array($request);
		if(($ech['envoyeur'] != $_SESSION['id_province']) AND ($ech['recepteur'] != $_SESSION['id_province']))
		{//on a rien a fair
			$MessageInfo = bw_error("Vous n'êtes pas impliqué dans cet échange!");
		}
		else
		{
			if($ech['envoyeur'] == $_SESSION['id_province'])
			{//c'est nous qui annulons
				$del = "DELETE FROM echanges WHERE `id` = '".$ech['id']."'";
				sql_query($del);
				$MessageInfo = bw_info("Demande d'échange annulée!");

				$Message = 'Le joueur '.$Joueur->pseudo.' a annulé son échange avec vous';
				send_message('999999994', $ech['recepteur'], $Message, 1);
			}
			elseif($ech['recepteur'] == $_SESSION['id_province'])
			{//c'est le repecteur qui annule
				$del = "DELETE FROM echanges WHERE `id` = '".$ech['id']."'";
				sql_query($del);
				//message
				$Message = 'Le joueur '.$Joueur->pseudo.' a refusé l\'échange avec vous';
				send_message('999999994', $ech['envoyeur'], addslashes($Message), 1);
				
				$MessageInfo = bw_info("Vous avez refusé la demande d'échange!");
			}
		}
	}
}

bw_f_info("Information", (isset($MessageInfo) ? $MessageInfo : "Le marché vous permet de faire des échanges de ressources avec d'autres Héros!"));

echo "<br />\n<form method=\"post\" action=\"index.php?p=bsp_marche&do=send\">\n";
echo "<fieldset>\n";
echo "	<legend>Faire un échange</legend>\n";
echo "	<table class=\"newsmalltable\"><tr><td>Choisissez un joueur à qui vous voulez faire l'échange :\n";
echo "	<select name=\"demande\">\n";
echo "		<option value=\"\">--Pseudo (Province)--</option>\n";

//liste des joueurs
$sql = "SELECT name, id FROM provinces WHERE id <> '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."' ";
$req = sql_query($sql);
while($res = mysql_fetch_array($req))
{//Nos provinces
	echo "		<option value=\"".$res['id']."\">Votre province: ".$res['name']."</option>\n";
}
$sql = "SELECT pseudo, id FROM joueurs WHERE id <> '".$_SESSION['id_joueur']."' ORDER BY pseudo ASC";
$req = sql_query($sql);
while($res = mysql_fetch_array($req))
{	
	$sql2 = "SELECT name, id FROM provinces WHERE id_joueur = '".$res['id']."'";
	$req2 = sql_query($sql2);
	while($res2 = mysql_fetch_array($req2))
	{
		echo "		<option value=\"".$res2['id']."\">".$res['pseudo']." (".$res2['name'].")</option>\n";
	}
}
echo "	</select><br /><br />\n";
echo "	<table class=\"newsmalltable\">\n";
echo "	<tr>\n";//entete
echo "		<th>Vous donnez</th><th>Vous demandez</th>\n";
echo "	</tr>\n";

//or
echo "		<tr><td><INPUT TYPE=\"text\" NAME=\"givegold\" size=\"10\" maxlength=\"10\" />Or </td>\n";
echo "		<td><INPUT TYPE=\"text\" NAME=\"askgold\" size=\"10\" maxlength=\"10\" />Or </td></tr>\n";
//nourriture
echo "		<tr><td><INPUT TYPE=\"text\" NAME=\"givefood\" size=\"10\" maxlength=\"10\" />Nourriture </td>\n";
echo "		<td><INPUT TYPE=\"text\" NAME=\"askfood\" size=\"10\" maxlength=\"10\" />Nourriture </td></tr>\n";
//matériaux
echo "		<tr><td><INPUT TYPE=\"text\" NAME=\"givemat\" size=\"10\" maxlength=\"10\" />Matériaux </td>\n";
echo "		<td><INPUT TYPE=\"text\" NAME=\"askmat\" size=\"10\" maxlength=\"10\" />Matériaux </td></tr>\n";
//magie
echo "		<tr><td><INPUT TYPE=\"text\" NAME=\"givecraft\" size=\"10\" maxlength=\"10\" />Magie </td>\n";
echo "		<td><INPUT TYPE=\"text\" NAME=\"askcraft\" size=\"10\" maxlength=\"10\" />Magie </td></tr>\n";

if($Place_Du_Marche_Ok)
{
	//Echange paysans
	echo "		<tr><td><INPUT TYPE=\"text\" NAME=\"givepeasant\" size=\"10\" maxlength=\"3\" />Paysans </td>\n";
	echo "		<td><INPUT TYPE=\"text\" NAME=\"askpeasant\" size=\"10\" maxlength=\"3\" />Paysans </td></tr>\n";	
}
echo "	</table>\n";

echo "	<INPUT TYPE=\"submit\" value=\"Confirmer\" /></td></tr></table>\n";
echo "</fieldset>\n";
echo "</form><br />\n\n";


echo "<fieldset>\n";
echo "	<legend>Vos échanges en cours</legend>\n";


echo "	<table class=\"newsmalltable\">\n";
echo "	<tr>\n		<th width=\"300px\" colspan=\"2\">Vous demandez en échange</th>\n	</tr>\n";
$multi = "SELECT * FROM echanges WHERE envoyeur = '".$_SESSION['id_province']."'";
$echange = sql_query($multi);
$nombre = mysql_num_rows($echange);
while($res = sql_array($echange))
{//ou on envoit
	//Cherche son pseudo et le nom de la province
	$sql = "SELECT name, id_joueur FROM provinces WHERE id = '".$res['recepteur']."'";
	$req = sql_query($sql);
	$res2 = mysql_fetch_array($req);

	$pse =  mysql_fetch_array(sql_query("SELECT pseudo FROM joueurs WHERE id = '".$res2['id_joueur']."'"));
	$pro = province_name($_SESSION['id_province']);


	echo '<tr><th>'.$Joueur->pseudo.' ('.$pro.')</th><th>'.$pse['pseudo'].' ('.$res2['name'].')</th></tr>';
	echo '<tr><td>Or: '.$res['golde'].'</td><td class="in">Or: '.$res['goldr'].'</td></tr>';
	echo '<tr><td>Nourriture: '.$res['foode'].'</td><td class="in">Nourriture: '.$res['foodr'].'</td></tr>';
	echo '<tr><td>Matériaux: '.$res['mate'].'</td><td class="in">Matériaux: '.$res['matr'].'</td></tr>';
	echo '<tr><td>Magie: '.$res['crafte'].'</td><td class="in">Magie: '.$res['craftr'].'</td></tr>';
	if($Place_Du_Marche_Ok)
	{
		echo '<tr><td>Paysans: '.$res['peasante'].'</td><td class="in">Paysans: '.$res['peasantr'].'</td></tr>';
	}
	echo '<FORM METHOD=POST ACTION="index.php?p=bsp_marche&do=delete&id='.$res['id'].'">';
	echo '<tr><th colspan="2"><INPUT TYPE="submit" value="Annuler"></th></tr></form>';
}
if ($nombre == 0) echo "	<tr>\n		<td>Vous demandez actuellement aucun échange.<br /></td>\n	</tr>\n";
echo "	</table>\n";

echo "	<br>\n\n";

echo "	<table class=\"newsmalltable\">\n";
echo "	<tr><th width=\"300px\" colspan=\"2\">Vous êtes demandé en échange</th></tr>\n";
$multi = "SELECT * FROM echanges WHERE recepteur = '".$_SESSION['id_province']."'";
$echange = sql_query($multi);
$nombre = mysql_num_rows($echange);
while($res = sql_array($echange))
{//on nous demande
	//Cherche son pseudo et le nom de la province
	$sql = "SELECT name, id_joueur FROM provinces WHERE id = '".$res['envoyeur']."'";
	$req = sql_query($sql);
	$res2 = mysql_fetch_array($req);

	$pse =  mysql_fetch_array(sql_query("SELECT pseudo FROM joueurs WHERE id = '".$res2['id_joueur']."'"));
	$pro = mysql_fetch_array(sql_query("SELECT name FROM provinces WHERE id = '".$_SESSION['id_province']."'"));


	echo "<tr>\n";
	echo "	<th>".$pse['pseudo'].' ('.$res2['name'].')</td><th>'.$Joueur->pseudo.' ('.$pro['name'].')</th></tr>';
	echo '<tr><td>Or: '.$res['golde'].'</td><td class="in">Or: '.$res['goldr'].'</td></tr>';
	echo '<tr><td>Nourriture: '.$res['foode'].'</td><td class="in">Nourriture: '.$res['foodr'].'</td></tr>';
	echo '<tr><td>Matériaux: '.$res['mate'].'</td><td class="in">Matériaux: '.$res['matr'].'</td></tr>';
	echo '<tr><td>Magie: '.$res['crafte'].'</td><td class="in">Magie: '.$res['craftr'].'</td></tr>';
	if($Place_Du_Marche_Ok)
	{
		echo '<tr><td>Paysans: '.$res['peasante'].'</td><td class="in">Paysans: '.$res['peasantr'].'</td></tr>';
	}
	echo '<tr><td><a href="index.php?p=bsp_marche&do=accept&id='.$res['id'].'">Accepter</a></td>';
	echo '<td><a href="index.php?p=bsp_marche&do=delete&id='.$res['id']."\">Refuser</a></td>\n";
	echo "</tr>\n";
}	
if ($nombre == 0) echo "	<tr>\n		<td>Personne ne vous demande en échange.<br /></td>\n	</tr>\n";
echo "	</table>\n";

echo "</fieldset><br />\n";

bw_tableau_end();
?>
