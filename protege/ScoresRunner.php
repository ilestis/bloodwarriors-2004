<html>
<head>
	<title>Yeah</title>
</head>
<body>
<?php
/*----------------------[TABLEAU]---------------------
|Nom:			ScoresRunner.Php
+-----------------------------------------------------
|Description:	Le runner qui calcul les scores des joueurs, alliances et races, 
|				... et la magie des sorciers.
+-----------------------------------------------------
|Date de création:				10.02.06
|Date du premier test:			10.02.06
|Dernière modification[Auteur]: 24/04/07[Escape] -> Score batiment = % de pv restant
+-----------------------------------------------------*/
//verifie qu'on a activé le GET password
if(!isset($_GET['pyjama']) || htmlentities($_GET['pyjama']) != 'dz542km')
{//pas ok
	exit;
}//arrête le code

//Includes
global $CONF;
require('../include/variables.inc.php');
require_once('../include/fonction.php'); //Les fonctions
require_once('../class/class.MySql.php');
$csql = new sql();

//Lanche connection
if(!$csql->connection($GLOBALS['CONF']['game_DB_user'], $GLOBALS['CONF']['game_DB_psw'] ,  $GLOBALS['CONF']['game_DB_server'], $GLOBALS['CONF']['game_DB_name'])) {
	echo $csql->error();
	die("Impossible de se connecter au serveur!");
}


//Message dans le journal des admins
//$action = "<img src=\"images/admin/ok.png\">Scores Runner Lancé.";
//journal_admin('CronTab', $action);

//--------------------------VACANCES PEOPLE----------------------//
echo "<br /><strong>Mise à jour des vacances...</strong><br />\n";
$upvacances = sql_query("UPDATE joueurs SET vacances = (vacances-1) WHERE vacances > '0'");
$upvacances = sql_query("UPDATE joueurs SET acceslvl = '1' WHERE vacances = '0' AND acceslvl = '-1'");

//----------------------------MAGIE SORCIERS--------------------------//
//si matin
if (date("G") == 5)
{//ok
	//Verifie si on l'a déjà fait ajourd'hui
	$file = "./scores_time.txt";
	require $file;

	if (time() > ($time + 9000))
	{//Ok

		//met à jour le fichier
		$content = '<?php $time = '.time().'; ?>';
		

		$operation = fopen($file, 'w');
		fwrite($operation, $content);
		fclose($operation);

		//------// Ajoute 10% de magie à tous les sorciers 
		echo "<h2>Runner de la magie</h2>\n";
		
		$sql = "SELECT id, pseudo FROM joueurs WHERE race = '6' ORDER BY id ASC";
		$req = sql_query($sql);
		while($res = mysql_fetch_array($req))
		{//Passe chaque sorcier
			
			echo "<strong>Pseudo</strong>: ".$res['pseudo']." :: ";
			
			//On prend chacune de ses provinces
			$sqlp = "SELECT craft, id FROM provinces WHERE id_joueur = '".$res['id']."'";
			$reqp = sql_query($sqlp);
			while($resp = mysql_fetch_array($reqp))
			{
				//Ressource max de base
				$TourDeMana = false;
				$Ressources_max = $CONF['province_max_ressources_craft'];

				//Calcul la nouvelle magie et met à jour
				$Magie = min($resp['craft']+150,ceil($resp['craft']*1.1));

				//Bonnus entrepot & tourdemana & 
				if(bw_batiavailable2('entrepot', $resp['id'], false)) $Ressources_max += $CONF['bati_capa_entrepot'];
				if(bw_batiavailable2('tourdemana', $resp['id'], false)) $Ressources_max += $CONF['bati_capa_tourdemana'];
				if(bw_batiavailable2('grandentrepot', $resp['id'], false)) $Ressources_max += $CONF['bati_capa_grandentrepot'];

				//Empêche le dépassement
				if($Magie > $Ressources_max)
				{
					$Magie = $Ressources_max;
				}

				echo "Magie ".$resp['craft']." -> ".$Magie."<br />\n";
				$up = "UPDATE provinces SET craft = '".$Magie."' WHERE id = '".$resp['id']."'";
				sql_query($up);
			}
		}
	}
}
//------------------------END MAGIE SORCIERS--------------------------//


//---------------------------------------------------------
//--------------- CODE DES SCORES ------------------------
	//---------------------------------------------------


echo "<h2>Runner des scores</h2>\n";

//Les provinces
echo "<strong>Provinces</strong<br />\n";

$sql = "SELECT * FROM provinces";
$req = sql_query($sql);
while($resp = sql_array($req))
{
	$Score = 0;
	$ID = $resp['id'];

	//On ajoute les ressources etc... au score

	$Score += $resp['victoires']*$CONF['score_victoires'];
	$Score -= $resp['pertes']*$CONF['score_defaites'];

	$Score += ($resp['gold'] + $resp['food'] + $resp['mat'] + $resp['craft']) * $CONF['score_ressources'];

	$Score += $resp['peasant'] * $CONF['score_paysans'];

	//Score cases: le nombre qu'on a moins le nombre inexploité (en gros tout terrain fertil, vierge ou construit)
	$Score += ($resp['cases_total']-$resp['cases_notusuable'])*$CONF['score_cases'];
	
	//On prend tous les bâtiments
	$reqb = sql_query("SELECT 
	a.`id_batiment`, a.`life`, a.`life_total` , b.puissance
	FROM 
	batiments AS a
	LEFT JOIN
	liste_batiments AS b ON b.id = a.id_batiment
	WHERE 
	a.id_province = '".$ID."' AND a.value = '1'");
	while($resb = sql_array($reqb))
	{
		if ($resb['life'] > 0) 
		{
			//Calcul le pourcentage de la vie du bâtiment
			if($resb['life'] == 0 || $resb['life_total'] == 0) { 
				$PourcentScoreBati = 0;
			}
			else {
				$PourcentScoreBati = round($resb['life']/$resb['life_total'], 2);
			}
			$Score += ($PourcentScoreBati*$resb['puissance']) * $CONF['score_batiments'];
		}
	}

	//On prend toutes les unités
	$requ = sql_query("SELECT a.ID_creature, b.puissance FROM armees AS a LEFT JOIN liste_invocations AS b ON b.ID = a.ID_creature WHERE a.id_province = '".$ID."'");
	while($resu = sql_array($requ))
	{
		$Score += ($resu['puissance'] * $CONF['score_unites']);
	}

	//Met a jours dans la bdd
	$sql_up = "UPDATE provinces SET puissance = '".$Score."' WHERE id = '".$ID."'";
	sql_query($sql_up);
}

//Cache du score
ob_start();
bw_tableau_start("Les 50 meilleures Provinces");

bw_f_info("Informations", "Contemplez les 50 plus puissantes Provinces des terres de Blood Warriors!");

?>
			<table class="newsmalltable"><tr>
				<th>N°</th><th>Nom</th><th>Héro</th><th>Puissance</th><th>V/D</th>
			</tr>		
			<?php
//constantes
$rank = 0;

//requête
$sql = "SELECT a.id, a.puissance, a.victoires, a.pertes, a.id_joueur, a.name, b.pseudo FROM provinces AS a LEFT JOIN joueurs AS b ON b.id = a.id_joueur ORDER BY a.puissance DESC LIMIT 0, 50";
$req = sql_query($sql);
while($res = sql_array($req))
{
	//incrémentation
	$rank ++;
	?>
			<tr>
			<td><?php echo $rank; ?></td>

			<td>
				<a href="index.php?p=search2&joueurid=<?php echo $res['id_joueur']; ?>&provinceid=<?php echo $res['id']."&view=1"; ?>"><?php echo $res['name']; ?>
				</a>

			</td>

			<td>
				<a href="index.php?p=search2&joueurid=<?php echo $res['id_joueur']."&view=1"; ?>"><?php echo $res['pseudo']; ?>
				</a>

			</td>

			<td><?php echo $res['puissance']; ?></td>
			<td><?php echo $res['victoires']."/".$res['pertes']; ?></td>
		</tr>
<?php
}

echo "			</table>\n";
echo "		<a href=\"?p=scores\">Retour au scores</a>\n";
bw_tableau_end();

$cache=ob_get_contents();
ob_end_clean();

//Ici : la partie pour la cache de la page web
$nom = 'ScoresProvinces.html';
$lieu = '../public/'.$nom;

//On définit quelle fonctions utiliser
$version = explode('.',phpversion());

if($version[0] == 5) {
	file_put_contents($lieu,$cache );
}
else
{
	$operation = fopen($lieu, 'w');
	fwrite($operation, $cache);
	fclose($operation);
}




//JOUEURS
$sql = "SELECT pseudo, id, victoires, pertes FROM joueurs"; 
$result = sql_query($sql);
while($res = sql_array($result))
{ //Prend les id
	$Joueur_id = $res['id'];
	$Score = 0;

	//Victoires et pertes
	$Victoires = 0;
	$Pertes = 0;

	//On prend les provinces
	$sqlp = "SELECT victoires, pertes, puissance FROM provinces WHERE id_joueur = '".$Joueur_id."'";
	$reqp = sql_query($sqlp);
	while($resp = sql_array($reqp))
	{	
		//Incrémentation
		$Pertes += $resp['pertes'];
		$Victoires += $resp['victoires'];
		$Score += $resp['puissance'];
	}
	
	$Up = "UPDATE joueurs SET puissance = '".round($Score)."', victoires = '".$Victoires."', pertes = '".$Pertes."' WHERE `id` = '".$res['id']."'";
	sql_query($Up);
}



//Cache du score
ob_start();

bw_tableau_start("Les Scores par Héros");
bw_f_info("Information", "Il est toujour bon de savoir où en sont vos alliés, ainsi que vos ennemis. Ces scores vous permettent d'avoir un rapport général, mis à jour toutes les heures, de l'état des Héros de Blood Warriors.");

?>
			<table class="newsmalltable"><tr>
				<th>N°</th><th>Pseudo [Alliance]</th><th>Accréditation</th><th>Race</th><th>Puissance</th><th>V/D</th>
			</tr>		
			<?php
//constantes
$rank = 0;

//requête
$sql = "SELECT 
	a.ally_id, a.ally_lvl, a.pseudo, a.id, a.puissance, a.vacances, a.victoires, a.pertes, a.race, 
	b.name 
FROM 
	joueurs AS a
LEFT JOIN
	alliances AS b ON b.ally_id = a.ally_id	
WHERE 
	acceslvl > '-2' 
ORDER BY puissance DESC";
$req = sql_query($sql);
while($res = sql_array($req))
{
	//incrémentation
	$rank ++;
	?>
			<tr>
				<td><?php echo $rank; if($res['vacances'] < 0) echo " (<a href=\"#\" title=\"En vacances\">V</a>)"; ?></td>
				<td><a href="index.php?p=search2&joueurid=<?php echo $res['id']; ?>&view=1"><?php echo $res['pseudo']; ?></a>
	<?php
	if($res['ally_id'] > 0) echo '[<a href="index.php?p=scores&do=allyview&id='.$res['ally_id'].'">'.$res['name'].'</a>]';

	//if($res['acceslvl'] == -1) echo "[<a href=\"#\" title=\"En vacances\">V</a>]\n";
	?>
			</td>
			<td>
			<?php if($res['ally_lvl'] == 5) echo "<img src=\"images/president.png\" title=\"Président d'alliance\" /> ";
			//if($res['acceslvl'] >= 3 AND $res['acceslvl'] < 7)  echo "<img src=\"images/moderateur.png\" title=\"Modérateur du jeu\" /> ";
			//if($res['acceslvl'] >= 8)  echo "<img src=\"images/administrateur.png\" title=\"Administrateur du jeu\" />";
			?>
			&nbsp;</td>
			<td><?php echo return_guilde($res['race'], 'fr'); ?></td>
			<td><?php echo $res['puissance']; ?></td>
			<td><?php echo $res['victoires']."/".$res['pertes']; ?></td>
		</tr>
<?php
}

echo "			</table>\n";
echo "		<a href=\"?p=scores\">Retour au scores</a>\n";
bw_tableau_end();
$cache=ob_get_contents();
ob_end_clean();

//Ici : la partie pour la cache de la page web
$nom = 'ScoresHeros.html';
$lieu = '../public/'.$nom;

//On définit quelle fonctions utiliser
$version = explode('.',phpversion());

if($version[0] == 5) {
	file_put_contents($lieu,$cache );
}
else
{
	$operation = fopen($lieu, 'w');
	fwrite($operation, $cache);
	fclose($operation);
}






// --------------------------------------------------------------------------------------- //
// ------------------------------------- END HEROS --------------------------------------- //
// --------------------------------------------------------------------------------------- //



//Calcul du score pour les Alliances
$sql = "SELECT * FROM alliances";
$req = sql_query($sql);
while ($res = sql_array($req))
{
	//variable
	$id = $res['ally_id'];
	$Score = 0;
	$Race = array();
	$Race[1] = 0;
	$Race[2] = 0;
	$Race[3] = 0;
	$Race[4] = 0;
	$Race[5] = 0;
	$Race[6] = 0;

	//prend chaque joueur
	$play = "SELECT puissance, race FROM joueurs WHERE ally_id = '".$id."' AND acceslvl > '-1' AND vacances = '0'"; 
	$reg = sql_query($play);
	while($pla = sql_array($reg))
	{
		$Score += $pla['puissance'];
		$Race[$pla['race']] += 1;
	}

	// Calcule la majorité
	$MajoriteNombre = 0;
	$MajoriteRace = 0;
	for($i = 1; $i < 7; $i++)
	{
		if($Race[$i] > $MajoriteNombre) {
			$MajoriteRace = $i;
			$MajoriteNombre = $Race[$i];
		}
	}
	$up = "UPDATE alliances SET power = '".$Score."', race_majorite = '".$MajoriteRace."' WHERE ally_id = '".$id."'";
	sql_query($up);
}

//Cache du score
ob_start();

bw_tableau_start("Scores des alliances");
bw_f_info("Information", "Votre alliance prépare une guerre? Où elle cherche simplement des alliés forts? Vous trouverez toutes les informations essentielles ici!");
?>
			<table class="newsmalltable"><tr>
				<th>N°</th>
				<th>Nom</th>
				<th>Membres</th>
				<th>Moyenne</th>
				<th>Puissance</th>
			</tr>
	<?php
	//constantes
	$rank = 0;

	$sql = "SELECT * FROM alliances ORDER BY power DESC";
	$req = mysql_query($sql);
	while($res = sql_array($req))
	{//prend chaque alliance classée par guilde
		$rank ++;

		echo "<tr>\n";
		echo "	<td>".$rank."</td>\n";
		//nombre de membres
		$blu = "SELECT pseudo FROM joueurs WHERE `ally_id` = '".$res['ally_id']."'";
		$ss = sql_query($blu);
		$nombres = mysql_num_rows($ss);
		echo "	<td><a href=\"index.php?p=scores&do=allyview&id=".$res['ally_id']."\">".$res['name']."</a></td>\n";
		echo "	<td>".$nombres."</td>\n";
		//Moyenne
		$Moyenne = round($res['power']/$nombres);
		echo "	<td>".$Moyenne."</td>\n";
		echo "	<td>".$res['power']."</td>\n";
		echo "</tr>\n";
	}
echo "			</table>\n";
echo "		<a href=\"?p=scores\">Retour au scores</a>\n";
bw_tableau_end();
$cache=ob_get_contents();
ob_end_clean();

//Ici : la partie pour la cache de la page web
$nom = 'ScoresAlliances.html';
$lieu = '../public/'.$nom;

//On définit quelle fonctions utiliser
$version = explode('.',phpversion());

if($version[0] == 5) {
	file_put_contents($lieu,$cache );
}
else
{
	$operation = fopen($lieu, 'w');
	fwrite($operation, $cache);
	fclose($operation);
}


// --------------------------------------------------------------------------------------- //
// ------------------------------------- END ALLY ---------------------------------------- //
// --------------------------------------------------------------------------------------- //




//calcul pour les races
$sql = "SELECT * FROM info_races";
$req = sql_query($sql);
while ($res = sql_array($req))
{
	//variables
	$id = $res['id_race'];
	echo "Id race: ".$id."<br />\n";
	$puissance = 0;
	$unites = 0;

	//prend chaque joueur
	$play = "SELECT puissance, id, aut FROM joueurs WHERE race = '".$id."' AND vacances = '0'";
	$reg = sql_query($play);
	while ($pla = sql_array($reg))
	{ 
		// Si activé
		if($pla['aut'][0] == 1) {
			$puissance += $pla['puissance']; 

			$unites += mysql_num_rows(sql_query("SELECT id FROM armees WHERE id_joueur = '".$pla['id']."'"));
			echo "&nbsp;&nbsp;&nbsp;".$pla['id'].":".$unites."<br />\n";
		}		
	}

	$up = "UPDATE info_races SET score = '".$puissance."', nbunites = '".$unites."' WHERE id_race = '".$id."'";
	sql_query($up);
}
//Cache du score
ob_start();
bw_tableau_start("Les Scores par Races");
bw_f_info("Information", "Portez la fierté, ou la honte de votre race!");

	echo "<table class=\"newsmalltable\"><tr><th>N°</th><th>Race</th><th>Nombre</th><th>Moyenne</th><th>Puissance</th><th>Nb Unités</th></tr>\n";
	
	$rank = 0;
	$sql = "SELECT * FROM info_races ORDER BY score DESC";
	$req = sql_query($sql);
	while ($res = sql_array($req))
	{//prend chaque race
		$rank ++;
		echo "<tr><td>".$rank."</td>\n";
		echo "<td>".return_guilde($res['id_race'], 'fr')."</td>\n";
		$nb = "SELECT id FROM joueurs WHERE race = '".$res['id_race']."'";

		$reu = sql_query($nb);
		$nbr = mysql_num_rows($reu);
		echo "<td>".$nbr."</td>\n";
		if ($nbr == 0) $nbr = 1;
		//Moyenne
		$Moyenne = floor($res['score']/$nbr);
		echo "<td>".$Moyenne."</td>\n";

		echo "<td>".$res['score']."</td>\n";

		echo "<td>".$res['nbunites']."</td>\n";

		echo "</tr>\n";

	}
echo "	</table>\n";
?>
			<br /><fieldset><legend>Bonnus :</legend>
			<table class="newsmalltable">
			<tr>
				<th>Race</th>
				<th>Bonnus Force</th>
				<th>Bonnus Endurance</th>
				<th>Bonnus Attaque</th>
				<th>Bonnus Défense</th>
			</tr>

			<?php
			$sql = "SELECT * FROM info_races ORDER BY id_race";
			$req = sql_query($sql);
			while($res = mysql_fetch_array($req))
			{?>
			<tr>
				<th><?php echo return_guilde($res['id_race'], 'fr'); ?></th>
				<td><?php echo $res['bonus_1']; ?></td>
				<td><?php echo $res['bonus_2']; ?></td>
				<td><?php echo $res['bonus_3']; ?></td>
				<td><?php echo $res['bonus_4']; ?></td>

			</tr>
			<?php
			}
echo "			</table></fieldset>\n";
echo "		<a href=\"?p=scores\">Retour au scores</a>\n";
bw_tableau_end();
$cache=ob_get_contents();
ob_end_clean();

//Ici : la partie pour la cache de la page web
$nom = 'ScoresRaces.html';
$lieu = '../public/'.$nom;

//On définit quelle fonctions utiliser
$version = explode('.',phpversion());

if($version[0] == 5) {
	file_put_contents($lieu,$cache );
}
else
{
	$operation = fopen($lieu, 'w');
	fwrite($operation, $cache);
	fclose($operation);
}

?>
</body>
</html>