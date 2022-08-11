<?php
//Header d'admin (session cheacker)
include ('adminheader.php');

//mettre un warning
if(!isset($_GET['Warn_mod']) || !is_numeric($_GET['Warn_mod'])) $Warn_mod = 0;
else $Warn_mod = clean($_GET['Warn_mod']);
if(isset($_POST['JId']) && is_numeric($_POST['JId'])) $JId = clean($_POST['JId']);
elseif(isset($_GET['JId']) && is_numeric($_GET['JId'])) $JId = clean($_GET['JId']);

if ($Warn_mod == 1)
{//Ajoute warning
	ajout_warning($Joueur->pseudo, $JId, clean($_POST['warning']), clean($_POST['type']));
}
if($Warn_mod == 2)
{//Supprime warning
	retire_warning($Joueur->pseudo, clean($_GET['id']), $JId);
}

if(isset($_GET['autup']) && $_GET['autup'] == 1)
{
	$AllAut = '';
	for($i = 0; $i < 15; $i++)
	{
		$NewAut[$i] = (isset($_POST['aut_'.$i]) && $_POST['aut_'.$i] == 'on' ? 1 : 0);
		$AllAut .= $NewAut[$i];
	}
	$up = "UPDATE joueurs SET aut = '".$AllAut."' WHERE id = '".$JId."'";
	sql_query($up);
}

//Variables
$sql		= "SELECT * FROM `joueurs` WHERE `id` = '".$JId."'";
$req		= sql_query($sql);
$res		= sql_object($req);

$JLogin		= $res->login;
$JPseudo	= $res->pseudo;
$JAcces		= $res->acceslvl;
$JPrenom	= $res->prenom;
$JNom		= $res->nom;
$JActiv		= date($CONF['game_timeformat'], $res->activationtime);
$JDisco		= $res->decouverte;
$JEmail		= $res->email;
$JRace		= return_guilde($res->race, $Joueur->lang);
$JAut		= $res->aut;


//infos publiques
echo "<br />\n";

echo "<table class=\"newsmalltable\">\n";
echo "<tr>\n";
echo "	<th>ID:</th>\n";
echo "	<th>Pseudo:</th>\n";
echo "	<th>Login:</th>\n";
echo "	<th>Royaume:</th>\n";
echo "	<th>Prénom - Nom:</th>\n";
echo "	<th>Email:</th>\n";
echo "	<th>Inscription:</th>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "	<td>".$JId."</td>";
echo "	<td>".$JPseudo."</td>";
echo "	<td>".$JLogin."</td>\n";
echo "	<td>".$JRace."</td>\n";
echo "	<td>".$JNom." - ".$JPrenom."</td>\n";
echo "	<td>".$JEmail."</td>\n";
echo "	<td>".$JActiv."</td>\n";
echo "</tr>";
echo "</table>\n";

echo "<br />\n";

// ----------------------------
// visioner les warning
echo "<table class=\"newsmalltable\">\n";

$sql = "SELECT * FROM warnings WHERE `id_joueur` = '".$JId."'";
$req = sql_query($sql);
if(sql_rows($req) >= 1)
{
	echo "<tr>\n";
	echo "	<th width=\"70%\">Plus/Moins en comportement</th>\n";
	echo "	<th>Supprimer</th>\n";
	echo "</tr>\n";
	while ($warn = mysql_fetch_array($req))
	{
		echo "<tr>\n";

		echo "	<td>\n";
		if($warn['id_type'] == 1) echo "		".bw_icon("i_avertissement.png");
		elseif($warn['id_type'] == 2) echo "		".bw_icon("i_bon_comportement.png");
		else echo "&nbsp;";

		echo $warn['warning']."n";
		echo "	</td>\n";

		echo "	<td>\n";
		echo "	<FORM METHOD=\"POST\" ACTION=\"index.php?p=admin_search&Warn_mod=2&id=".$warn['id']."&JId=".$JId."\">";
		echo "<INPUT TYPE=\"submit\" value=\"Supprimer\" /></FORM>\n";								
		echo "	</td>\n";

		echo "</tr>\n";
	} 
}

echo "<tr>\n";
echo "	<th colspan=\"2\">Ajouter un Plus/Moins comportement</th>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "	<td colspan=\"2\">\n";
echo "	<FORM METHOD=POST ACTION=\"index.php?p=admin_search&Warn_mod=1&JId=".$JId."\">\n";
echo "	<select name=\"type\">\n";
echo "		<option value=\"1\">Warning</option>\n";
echo "		<option value=\"2\">Bon Comportement</option>\n";
echo "	</select>\n";
echo "	<INPUT TYPE=\"text\" NAME=\"warning\" maxlength=\"200\" size=\"50\">\n";
echo "	<INPUT TYPE=\"submit\" value=\"Ajouter\">\n";
echo "	</td>\n";
echo "</form>";
echo "</tr>\n";
echo "</table><br/>\n";

//Autorisation du joueur
echo "<form method=\"post\" action=\"?p=admin_search&autup=1&JId=".$JId."\">\n";
echo "<table  class=\"newsmalltable\">\n";
echo "<tr>\n";
echo "	<th colspan=\"2\">Autorisation</th>\n";
echo "</tr>\n";

$ArrayAut = array(0, 1, 2, 3, 4, 5, 6, 10, 13, 14);

foreach($ArrayAut as $Aut)
{
	$Checked = ($JAut[$Aut] == 1 ? "checked=\"checked\"" : "");
	echo "<tr>\n";
	echo "	<td align=\"left\">$Aut: ".$admintext[$Aut]."</td>\n";
	echo "	<td><input type=\"checkbox\" name=\"aut_".$Aut."\" ".$Checked." /></td>\n";
	echo "</tr>\n";
}

//echo "dsfd: ".$adminpage['niveaupouvoir']."-> ".$_SESSION['aut'][$adminpage['niveaupouvoir']]."<br />\n";
//On peut modifier?
if($_SESSION['aut'][$adminpage['niveaupouvoir']] == 1)
{
	echo "<tr>\n";
	echo "	<td colspan=\"2\" align=\"right\" style=\"text-align: right;\"><input type=\"submit\" value=\"Enregistrer\" /></td>\n";
	echo "</tr>\n";
}
echo "</table>\n";
echo "</form>\n";
echo "<br />\n";

//les infos sur le joueur
echo "<em>On voit les ips des gens que si l'ip est identique et la connexion a une intervale de 2 heures.</em><br />\n";

//table des connexions
echo "<table class=\"newsmalltable\">\n";
echo "<tr>\n";
echo "	<th>IP</th>\n";
echo "	<th width=\"25%\">Date</th>\n";
echo "	<th width=\"25%\">Ip identique</th>\n";
echo "</tr>\n";

echo "<tr>\n";

//selection les connexion
/*$req = "SELECT * FROM ip WHERE id_joueur = '".$JId."' ORDER BY id DESC" ;
$result = mysql_query($req);
while($res = mysql_fetch_array($result))
{
	echo "	<TD>".$res['ip']."</TD>\n";
	echo "	<TD>".date($CONF['game_timeformat'], $res['time'])."</TD>\n";
	echo "	<TD>\n";
		echo " ";
		$Id_Used = '';
		$Ip =		$res["ip"];
		$heure =	$res['time'];
		$T['min'] =		$heure - 1800*2;
		$T['max'] =		$heure + 1800*2;
		$sql2 = "SELECT id_joueur FROM `ip` WHERE `id_joueur` <> '".$JId."' AND (`ip` = '".$Ip."') AND (`time` < '".$T['max']."') AND  (`time` > '".$T['min']."') LIMIT 1";
		$reqip = sql_query($sql2);
		while($resip = mysql_fetch_array($reqip))
		{
			if ($Id_Used != $resip['id_joueur']) {
				$sqlpseudo = sql_query("SELECT pseudo FROM joueurs WHERE `id` = '".$resip['id_joueur']."'");
				$respseudo = mysql_fetch_array($sqlpseudo);
				echo '		<a href="index.php?p=admin_search&JId='.$resip['id_joueur'].'">'.$respseudo['pseudo']."</a><br/>\n";
				$Id_Used = $resip['id_joueur']; 
			}
		}
	echo "	</td>\n";
	echo "</tr>\n";
}//fin du while*/
echo "</table><br />\n";

//si on a un niveau admin assez puissant pour supprimé
if($Joueur->acceslvl > 4)
	{ ?>	
	<a href="index.php?p=admin_supprimationcompte&joueurid=<? echo $JId; ?>">Supprimer</a><br /><br />
	<?php }
retour();

