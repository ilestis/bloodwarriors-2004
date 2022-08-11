<?php
/*
+---------------------
|Nom: Les fonctions
+---------------------
|Description: Les différentes fonctions du jeu
+---------------------
|Date de création: Octobre 04
|Dernière modification: 15.01.07
+-------------------*/

function return_guilde($numero, $lang = 'fr')
{
	if($lang == 'fr')
	{
		if ($numero == '0')	return 'Tous';
		if ($numero == '1')	return 'Anges';
		if ($numero == '2')	return 'Barbares';
		if ($numero == '3')	return 'Démons';
		if ($numero == '4')	return 'Elfes';
		if ($numero == '5')	return 'Rebelles';
		if ($numero == '6')	return 'Sorciers';
	}
	elseif($lang == 'en')
	{
		if ($numero == '0')	return 'All';
		if ($numero == '1')	return 'Angels';
		if ($numero == '2')	return 'Barbarians';
		if ($numero == '3')	return 'Demons';
		if ($numero == '4')	return 'Elves';
		if ($numero == '5')	return 'Rebles';
		if ($numero == '6')	return 'Wizards';
	}
}

function journal_admin($admin, $action)
{
	//mettre les actions dans le journal
	$sql = "INSERT into `messages` SET "
	. "id_from = '".$_SESSION['id_joueur']."', "
	. "message = '".$action."', "
	. "time = '".time()."', "
	. "location = 'jou';";
	sql_query($sql);
}

function ajout_warning($Pseudo_Admin, $JoueurId, $warning, $Type = 0)
{
	//mettre des warnings à des joueurs
	$sql = "INSERT into `warnings` VALUES('','".$JoueurId."','".$Type."', '".addslashes($warning)."')";
	sql_query($sql);
	
	//Pseudo
	$sql = sql_query("SELECT pseudo FROM joueurs WHERE id = '".$JoueurId."'");
	$res = mysql_fetch_array($sql);

	$T = ($Type == '1' ? 'Avertissement' : 'Point étoile');

	//Admin Journal
	journal_admin($Pseudo_Admin,'Le '.$T.' "'.addslashes($warning).'" a été ajouté à '.$res['pseudo']);
}

function retire_warning($admin,$warn_id, $joueur)
{
	//On cherche le warning et le pseudo du mec
	$sql = sql_query("SELECT warning FROM warnings WHERE id = '".$warn_id."'");
	$res = mysql_fetch_array($sql);
	$warning = addslashes($res['warning']);

	$sql = sql_query("SELECT pseudo FROM joueurs WHERE id = '".$joueur."'");
	$res = mysql_fetch_array($sql);

	//supprimer un warning de quelqun
	$sql = "DELETE FROM `warnings` WHERE `id` = '".$warn_id."'";
	sql_query($sql);
	journal_admin($admin,'Le warning \"'.$warning.'\" a été retiré de '.$res['pseudo']);
}

function pluriel($nombre,$accord)
{
	//deffini si y'a un s ou pas au mot
	if($nombre > 1) return $accord;
}


function sql_query($val)
{
	$ret = mysql_query($val) or print(bw_error("Erreur SQL !<br />".$val."<br />".mysql_error()));
	//if ($_SESSION['debug'] == TRUE) echo "<div class=\"requete\">".$val."</div>\n";
	return $ret;
}

function sql_object($val)
{//Retourn la même hose que mysql_fetch_object
	$ret = mysql_fetch_object($val);
	return $ret;
}

function sql_rows($val)
{
	$ret = mysql_num_rows($val);
	return $ret;
}

function sql_array($val)
{//Retourn la même hose que mysql_fetch_object
	$ret = mysql_fetch_assoc($val);
	return $ret;
}

//mettre a jour la date (time)
function update_last_forum_threads($theme)
{
	$sql = "UPDATE `forum_threads` SET `last_time` = '".time()."' WHERE `name` = '".$theme."'";
	sql_query($sql);
}

function lang_include($lang, $fichier)
{//définit quel fichier inclure pour la langue
	require 'lang/'.$lang.'/'.$fichier.'.php';

	foreach ($lang as $element => $valeur)
	{
		$current_lang_array[$element] = stripslashes($valeur);
	}
	unset($lang);
	return $current_lang_array;
}

function dernierepage($Subject_Id, $Config)
{//Calcule la dernière page d'un topic
	$sqlmessagesnbr = "SELECT message_id FROM forum_messages WHERE subject_id = '".$Subject_Id."'";
	$reqmessagesnbr = sql_query($sqlmessagesnbr);
	$nombredemessages = mysql_num_rows($reqmessagesnbr);
	$dernierepage = ceil($nombredemessages/$Config); //nomber de page

	return $dernierepage;
}

function global_variables($folder)
{//Global variables of the games
	require $folder;

	foreach ($CONF as $element => $valeur)
	{//Prend chaque ligne du forumat
		$var = $element = stripslashes($valeur);
		global $var;
	}
	//return $current_global_variable;
	return true;
}

function admin_badwords($Message, $Qui, $Destinataire)
{//Verifie si y'a des mots interdit dans le message
	$Passe = FALSE;
	$Time = time();
	$Sql = "SELECT `word` FROM `admin_dico`";
	$Req = mysql_query($Sql);
	while (($res = mysql_fetch_array($Req)) && ($Passe == FALSE))
	{
		if (strstr($Message, $res['word']) == true) 
		{
			$Insert = "INSERT INTO `messages` VALUES ('', '".$_SESSION['id_joueur']."', '".$Qui."', '".$Message."', '".$Time."','1', 'a_m', '')";
			sql_query($Insert);
			$Passe = TRUE;
		}
	}
}

function send_message($Me, $To, $Message, $Lvl=0, $Des='mes', $Titre='') {//Envoit de messages privés
	//L'utilité de cette fonction est en cas de changement de paramètres de la table "messagesprive", comme ça au moins ça change partout sans trop de problème.
	//Nétoie le message
	$Message = $Message;

	//Temp d'expiration du messages
	$expire = time()+3600*$GLOBALS['CONF']['game_mess_exp'];

	//insertion dans la base de donnée
	$Mes = "INSERT INTO messages VALUES('','".$Me."','".$To."', '".$Titre."', '".$Message."','".time()."', 0, '".$Des."', '".$expire."')";
	sql_query($Mes);

	//Fait  la verification de mot méchant
	if($Lvl == 0) admin_badwords($Message, $Me, $To);
}


function func_MaxPop($JoueurId, $ProvinceId, $Bonnus, $AllyID=0)
{//Calcule la population maximale
	//bonus max pop
	$MaxPop = 80;

	if(bw_batiavailable(18)) $MaxPop += 20; //Foyer
	if(bw_batiavailable('municipale', false)) $MaxPop += 45;
	if(bw_batiavailable('hoteldeville', false)) $MaxPop += 150;
	if(bw_batiavailable('eglise', false)) $MaxPop += 75;
	if(bw_batiavailable(19)) $MaxPop += 20; //Foyer 2
	if(bw_batiavailable('palais', false)) $MaxPop += 225;
	if(bw_batiavailable('temple', false)) $MaxPop += 310;

	//Calcul de bonnus de taux de maternité
	$PaysansMax = ceil($MaxPop*$Bonnus);

	// Bonnus alliance barbare?
	if(func_RaceMajorite($AllyID) == 2) {
		$PaysansMax = ceil($PaysansMax*$GLOBALS['CONF']['bonus_barbares_1']);
	}
	//le résultat
	return $PaysansMax;
}

function func_RaceMajorite($AllyID)
{
	if($AllyID == 0) return 0;

	$s = "SELECT race_majorite FROM alliances WHERE ally_id = '".$AllyID."'";
	$r = sql_query($s);
	$row = sql_array($r);
	return $row['race_majorite'];
}




function TitreEntete()
{//selectionne les nouveaux messages, et en même temps met un entête
	if(session_is_registered("login") == true)
	{//Connecté, check mail
		$sqlno = "select * from messages where nouveau < '2' AND `id_to` = '".$_SESSION['id_joueur']."' and location = 'mes'" ;
		$resultno = sql_query($sqlno) ;
		$no = mysql_num_rows($resultno) ;   //nombre de messages
		if ($no > 0) {//Nouveau message
			if($_SESSION['lang'] == 'en')
			{
				$Val = "<span class=\"info\">You have ".($no == 1 ? 'a ' : '')."<strong><a href=\"index.php?p=mess\"><FONT SIZE=\"\" 	COLOR=\"#FF0033\">".$no."</FONT></a></strong> new message".pluriel($no,'s')."</span></hr>\n";
			}
			else
			{
				$Val = "<span class=\"info\">Vous avez <strong><a href=\"index.php?p=mess\"><FONT SIZE=\"\" 	COLOR=\"#FF0033\">".$no."</FONT></strong> nouveau".pluriel($no,'x')." message".pluriel($no,'s')."</a></span></hr>\n";
			}
			return $Val;
		}
	}
	
	/*$lang_over	= lang_include($_SESSION['lang'],'lang_colone');
	$Rand = mt_rand(1, 20);
	if(isset($lang_over['m_'.$Rand])) $Val = "<strong>".$lang_over['m_'.$Rand]."</strong>\n";
	else
	{
		$Val = "<strong>Blood Warriors - ".$GLOBALS['CONF']['game_echo']."</strong> ~ ".date($GLOBALS['CONF']['game_timeformat'], time());
	}*/
	$Val = '';
	return $Val;
}

//Redirige
function redirection($page, $temps)
{
	echo "<script language='JavaScript'>compteur =setTimeout('window.location=\"".$page."\"',".$temps.")</script>\n";
}

//Verifie l'état de la magie
function check_craft($province_id, $cost)
{
	$sql = "SELECT craft FROM provinces WHERE id = '".$province_id."' LIMIT 1";
	$req = sql_query($sql);
	$res = sql_object($req);
	
	$cost = check_craft_cost($province_id, $cost);
	
	if($res->craft < $cost)
	{
		return false;
	}
	else
	{//Retir le bouclier
		
		$up = "UPDATE provinces SET craft = (craft-".$cost.") WHERE id = '".$province_id."'";
		sql_query($up);
		return true;
	}
}

function check_craft_cost($province_id, $cost)
{
	# Sort Chant/Cri de concentration?
	$chant = check_spell($province_id, 40);
	if(!empty($chant)) {
		$cost *= $chant;
	}

	$cri = check_spell($province_id, 41);
	if(!empty($cri)) {
		$cost *= $cri;
	}

	$cost = ceil($cost);

	return $cost;
}

# Verifie si la province possède ce sort en réserve
function check_spell($province_id, $spell_id) 
{
	$s = "SELECT boost_value FROM temp_sorts WHERE id_province = '".$province_id."' AND id_sort = '".$spell_id."'";
	$r = sql_query($s);
	if(sql_rows($r) >= 1) { // Oui
		$res = sql_array($r);
		return $res['boost_value'];
	}
	return 0;
}


function calc_WarTime($Province_1, $Province_2, $Conf_war_time = '', $Conf_war_min = '', $Conf_VitesseJeu = '', $Ally_Id = 0)
{//$Joueur1_X, $Joueur1_Y, $Joueur2_X, $Joueur2_Y)

	//Prend les axe de la province 1
	$sql = "SELECT x,y FROM provinces WHERE id = '".$Province_1."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);

	$Axe1_X = $res['x'];
	$Axe1_Y = $res['y'];

	//Prend les axe de la province 2
	$sql = "SELECT x,y FROM provinces WHERE id = '".$Province_2."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);

	$Axe2_X = $res['x'];
	$Axe2_Y = $res['y'];


	//Calcule le temps que ca prend en seconde
	$TempSecondes = 60 * floor(pow(pow($Axe1_X - $Axe2_X,2) + pow($Axe1_Y - $Axe2_Y,2), 0.5) * $GLOBALS['CONF']['war_time']) + $GLOBALS['CONF']['war_min'];

	// Bonus alliance elfes
	if(func_RaceMajorite($Ally_Id) == 4) {
		$TempSecondes = ceil($TempSecondes*$GLOBALS['CONF']['bonus_elfes_vitesse_guerre']);
	}

	//Vitesse du jeu en compte
	$TempSecondes *= $GLOBALS['CONF']['vitesse_jeu'];

	//As-t-on un sort de type 30?
	$sql_ailes = "SELECT ID, boost_value FROM temp_sorts WHERE id_province = '".$Province_1."' AND boost_id = '30'";
	$req_ailes = sql_query($sql_ailes);
	while($res_ailes = mysql_fetch_array($req_ailes)) {
		//Boost
		$TempSecondes *= $res_ailes['boost_value'];
	}

	//Calcul le moment que cela représente en tout
	$TempReel = time() + $TempSecondes;


	return $TempReel;
}

function min_attack_power($Player_Power, $Attaque_Min, $Player_Race, $Angel_Bonus)
{
	//Calculate the minimum power than a player has to attack

	$Min_Power = $Player_Power * $Attaque_Min;

	if($Player_Race == 1)
	{//Angel
		$Min_Power *= $Angel_Bonus;
	}

	return $Min_Power;
}

function province_name($ID)
{
	$sql = "SELECT name FROM provinces WHERE id = '".$ID."'";
	$req = sql_query($sql);
	$res = sql_object($req);

	return $res->name;
}
function joueur_name($ID)
{
	$sql = "SELECT pseudo FROM joueurs WHERE id = '".$ID."'";
	$req = sql_query($sql);
	$res = sql_object($req);

	return $res->pseudo;
}

function get_ressource_limit($Province)
{
	$MaxRessource[1]	= $GLOBALS['CONF']['province_max_ressources_gold'];
	$MaxRessource[2]	= $GLOBALS['CONF']['province_max_ressources_food'];
	$MaxRessource[3]	= $GLOBALS['CONF']['province_max_ressources_mat'];
	$MaxRessource[4]	= 0;
	$MaxRessource[5]	= $GLOBALS['CONF']['province_max_ressources_craft'];

	//Get information on buldings of the realm

	//Entrepôt
	if(bw_batiavailable2('entrepot', $Province, false))
	{
		for ($i = 1; $i < 6; $i++)
			$MaxRessource[$i] += $GLOBALS['CONF']['bati_capa_entrepot'];
	}

	//Tour de mana
	if(bw_batiavailable2('tourdemana', $Province, false))
	{
		$MaxRessource[5] += $GLOBALS['CONF']['bati_capa_tourdemana'];
	}
	
	//Grand Entrepôt
	if(bw_batiavailable2('grandentrepot', $Province, false))
	{
		for ($i = 1; $i < 6; $i++)
			$MaxRessource[$i] += $GLOBALS['CONF']['bati_capa_grandentrepot'];
	}

	// MAt * 2
	$MaxRessource[3] *= 2;
	return $MaxRessource;
}

function get_variable($variable)
{
	require('./include/variables.inc.php');
	foreach ($CONF as $element => $valeur)
	{//Prend chaque ligne du forumat
		if($element == $variable) return $valeur;
	}
	return 0;
}

function clean_form($table)
{
	foreach($table as $element => $valeur)
	{
		$current_array[$element] = clean($valeur);
	}
	unset($table);
	return $current_array;
}

function get_visiting($Id_sujet)
{
	$sql = "SELECT `time` FROM forum_visiting WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id_subject = '".$Id_sujet."'";
	$req = sql_query($sql);
	if(sql_rows($req) == 0) return 0;
	$res = sql_object($req);
	return $res->time;
}

function update_visiting($Id_sujet)
{
	//Verifie si on a une entrtée...
	$Nbr = "SELECT id FROM forum_visiting WHERE id_subject = '".$Id_sujet."' AND id_joueur = '".$_SESSION['id_joueur']."'";
	$Req = sql_query($Nbr);
	if(sql_rows($Req) == 0)
	{
		$Up_Last_Visit = "INSERT INTO forum_visiting VALUES('', '".$_SESSION['id_joueur']."', '".$Id_sujet."', '".time()."')";
	} else {
		$Up_Last_Visit = "UPDATE forum_visiting SET `time` = ".time()." WHERE id_subject = '".$Id_sujet."' AND id_joueur = '".$_SESSION['id_joueur']."'";
		
	}
	sql_query($Up_Last_Visit);
}

function breakpage()
{
	require ('./footer.php');
	exit;
}

function bw_protections($id_province)
{
	$pro_sql = "SELECT ID FROM temp_sorts WHERE id_province = '".$id_province."' AND id_sort = '4'";
	$pro_req = sql_query($pro_sql);
	
	if(sql_rows($pro_req) == 0) return true;

	//Sinon on en enlève une
	$pro_res = sql_array($pro_req);
	$pro_del = "DELETE FROM temp_sorts WHERE ID = '".$pro_res['ID']."' AND id_province = '".$id_province."'";
	sql_query($pro_del);
	return false;
}

function bw_batiavailable($Bat, $id = true)
{
	$sql_bativ = "SELECT life, life_total FROM batiments WHERE ".($id ? 'id_batiment' : 'codename')." = '".$Bat."' AND id_joueur = '".$_SESSION['id_joueur']."' AND id_province = '".$_SESSION['id_province']."' AND value >= '1'";
	$req_bativ = sql_query($sql_bativ);
	if(sql_rows($req_bativ) > 0)
	{
		$res_bativ = sql_array($req_bativ);
		//Verifie qu'on a les PV nécessaire
		$Min_Life = floor($res_bativ['life_total']*$GLOBALS['CONF']['bati_min_life']);
		if($res_bativ['life'] >= $Min_Life)
			return true;
	}
	return false;
}

function bw_batiavailable2($Bat, $prov_id, $id = true)
{
	$sql_bativ = "SELECT life, life_total FROM batiments WHERE ".($id ? 'id_batiment' : 'codename')." = '".$Bat."' AND id_province = '".$prov_id."'";
	$req_bativ = sql_query($sql_bativ);
	if(sql_rows($req_bativ) > 0)
	{
		$res_bativ = sql_array($req_bativ);
		//Verifie qu'on a les PV nécessaire
		$Min_Life = floor($res_bativ['life_total']*$GLOBALS['CONF']['bati_min_life']);
		if($res_bativ['life'] >= $Min_Life)
			return true;
		else
			return false;
	}
	else
		return false;
}

function bw_fieldset($title, $text, $align="center")
{
	echo "		<fieldset style=\"text-align:".$align.";\">\n";
	echo "			<legend>".$title."</legend>\n";
	echo "			".$text."\n";
	echo "		</fieldset><br />\n";
}
function bw_f_info($title, $text, $align="center")
{
	echo "		<fieldset style=\"text-align:".$align.";\">\n";
	echo "			<legend><img src=\"images/icons/btn_info.png\" alt=\"btn_info.png\" /> ".stripslashes($title)."</legend>\n";
	echo "			".stripslashes($text)."\n";
	echo "		</fieldset><br />\n";
}

function bw_tableau_start($title)
{
	echo "	<table class=\"newtable\"><tr>\n";
	echo "		<td class=\"newtitre\">".$title."</td>\n";
	echo "	</tr><tr>\n";
	echo "		<td class=\"newcontenu\">\n";
}

function bw_tableau_end()
{
	echo "		</td>\n";
	echo "	</tr><tr>\n";
	echo "		<td class=\"newfin\">&nbsp;</td>\n";
	echo "	</tr></table>\n";
}
function bw_f_start($Text, $Img = '', $align = '')
{
	echo '<fieldset '.($align != '' ? 'style="text-align:'.$align.';"' : '').">\n";
	echo "	<legend>".($Img != '' ? "<img src=\"images/".$Img."\" alt=\"".$Img."\" /> " : "").$Text."</legend>\n";
}
function bw_f_end()
{
	echo "</fieldset><br />\n";
}

function bw_error($text)
{
	$ret = "		<span class=\"avert\">".$text."</span>\n";
	return $ret;
}

function bw_info($text)
{
	$ret = "		<span class=\"info\">".$text."</span>\n";
	return $ret;	
}

function bw_new_message($nbr = 1)
{
	if($nbr == 1)
	{
		echo "<img src=\"images/forum_new.png\" alt=\"new\" />";
	} 
	elseif($nbr == 2)
	{
		echo "<img src=\"images/_forum_new.png\" alt=\"new\" />";
	}
}
function bw_old_message($nbr = 1)
{
	if($nbr == 1)
	{
		echo "<img src=\"images/forum_not_new.png\" alt=\"new\" />";
	} 
	elseif($nbr == 2)
	{
		echo "<img src=\"images/_forum_not_new.png\" alt=\"new\" />";
	}
}

function Forum_new($a,$b, $big = true)
{
	$extra = ($big ? '' : '_');
	
	//definit si il y a une nouvelle réponse ou pas
	if ($a < $b) 
		$forum_new = "<img src=\"images/".$extra."forum_new.png\" title=\"Nouveau\" alt=\"New\" />";
	else 
		$forum_new = "<img src=\"images/".$extra."forum_not_new.png\" title=\"Déjà lu\" alt=\"Old\" />";

	return $forum_new;
}
function bw_icon($Img, $Title = '')
{
	$ret = "<img src=\"images/icons/".$Img."\" alt=\"".$Img."\" ".($Title != '' ? "title=\"".$Title."\" " : '')."/> ";
	return $ret;
}
function bw_submit($Text)
{
	$ret = "	<input type=\"submit\" class=\"valide\" value=\"".$Text."\" />\n";
	return $ret;
}

function bw_province_state($ID)
{
	switch($ID)
	{
		case 1:
			return 'Village'; break;
		case 2:
			return 'Ville'; break;
		case 3:
			return 'Cité'; break;
		case 4:
			return 'Métropole';
	}
	return 'Error unknown ID';
}

function bw_terrain_type($ID)
{
	switch($ID)
	{
		case 1:
			return 'Plaine'; break;
		case 2:
			return 'Forêt'; break;
		case 3:
			return 'Montagne';
	}
	return 'Error unknown ID';
}

function bw_popup($text, $do, $id)
{
	$ret = "<a href=\"#\" title=\"Information\" target=\"info_popup\" onclick=\"window.open('info_popup.php?do=".$do."&id=".$id."','info_popup','height=".$GLOBALS['CONF']['popup_height']."px, width=".$GLOBALS['CONF']['popup_width']."px, resizable=yes');return false;\">".$text."</a>";
	return $ret;
}

function bw_type_province($type) {
	if($type == 0) return "Capitale";
	elseif($type == 1) return "Province";
	elseif($type == 2) return "Conquête";
	return "Erreur!";
}

# Créé une liste pour les provinces alliés et les notres
function liste_provinces_alliees($Joueur)
{
	$ret = "<option value=\"0\">Sélectionnez une province</option>\n";
	$sql = "SELECT ally_id, id, pseudo FROM joueurs";
	$req = sql_query($sql);
	while ($res = sql_array($req))
	{//Prend chaque héros assez fort
		if ((($Joueur->ally_id != 0) && ($Joueur->ally_id == $res['ally_id'])) || $res['id'] == $_SESSION['id_joueur']) 
		{
			//On peut le cibler alors on va chercher ses provinces
			$sqlp = "SELECT id, name FROM provinces WHERE id_joueur = '".$res['id']."'";
			$reqp = sql_query($sqlp);
			while($resp = sql_object($reqp))
			{
				$ret .= "<option value=\"".$resp->id."\">".$res['pseudo']." [".$resp->name."]</option>\n";

			}
		}
	}
	return $ret;
}

# Créé une liste pour les provinces ennemies et les notres
function liste_provinces_ennemies($Joueur)
{
	$MinPower = floor($Joueur->puissance/100)*$CONF['relation_attack_power'];
	$ret =  "<option value=\"0\">Sélectionnez une province</option>\n";
	$sql = "SELECT ally_id, id, pseudo FROM joueurs WHERE puissance >= '".$MinPower."' AND id != '".$_SESSION['id_joueur']."'";
	$req = sql_query($sql);
	while ($res = sql_array($req))
	{//Prend chaque héros assez fort
		if ($Joueur->ally_id == 0 || $Joueur->ally_id != $res['ally_id'])
		{
			//On peut le cibler alors on va chercher ses provinces
			$sqlp = "SELECT id, name FROM provinces WHERE id_joueur = '".$res['id']."'";
			$reqp = sql_query($sqlp);
			while($resp = sql_object($reqp))
			{
				$ret .=  "<option value=\"".$resp->id."\">".$res['pseudo']." [".$resp->name."]</option>\n";

			}
		}
	}
	return $ret;
}
?>