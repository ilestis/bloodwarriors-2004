<?php
/*
+---------------------
|Nom: La messagerie
+---------------------
|Description: Cette page affiche nos messages reçus
+---------------------
|Date de création: Février 04
|Date du premier test: Février 04
|Dernière modification: 10 Fev 06
+-------------------*/
//verifie l'état de la session
require ('include/session_verif.php');

//inclu le profil
include ('profil.php'); 

//langue
$lang_text = lang_include($Joueur->lang,'lang_messagerie');


//permet d'envoyer des messages

echo "<form name=\"messagesend\" method=\"post\" action=\"?p=messmod&id=send\">\n";

bw_tableau_start($lang_text['TitleMess']);


//informe si la variable n'est pas vide
if(isset($information) && $information != '') bw_f_info($lang_text['info'], $information);

bw_f_start(bw_icon('btn_newmail.png').$lang_text['send_title']);
 
//".$_SESSION['id_joueur']."
echo "<strong>".$lang_text['to']."</strong>\n"; 
$sql = "SELECT `id`, `pseudo`, `aut` FROM joueurs WHERE id <> '".$_SESSION['id_joueur']."' ORDER BY pseudo ASC" ;
$req = sql_query($sql);

echo "		<select name=\"reciver\">\n";
echo "			<option value=\"\">--".$lang_text['select']."--</option>\n";
while($res = mysql_fetch_array($req))
{
	if($res['aut'][0] == 1) {
		$Extra = ($res['aut'][11] == 1 ? ' ('.$lang_over['hollydays'].')' : '');
		echo "			<option value=\"".$res['id']."\">".$res['pseudo'].$Extra."</option>\n";
	}
}
echo "		</select><br />\n";

//mise en forme
include ('include/mise_en_forme.inc.php');
bw_afficheToolbar($lang_text['btn_send']);

//echo "		<br /><textarea name=\"comment\" cols=\"50\" rows=\"7\" id=\"comment\"></textarea><br/>\n";
//echo "		<input type=\"submit\" name=\"Submit2\" value=\"".."\">\n";
bw_f_end();
echo "		</form><br />\n\n";
/*echo "	</td>\n";
echo "</tr><tr>\n";
echo "	<td class=\"newfin\">&nbsp;</td>\n";
echo "</tr></table>\n";


echo "<br />\n\n";*/

//*************************************************************
//les messages reçus

/*echo "<table class=\"newtable\"><tr>\n";
echo "	<td class=\"newtitre\">".$lang_text['recieved']."</td>\n";
echo "</tr><tr>\n";
echo "	<td class=\"newcontenu\">\n";*/
	

//Form
echo "		<form method=\"POST\" action=\"?p=messmod&id=delselection\">\n";

bw_f_start(bw_icon('btn_inbox.png').$lang_text['recieved']);
$Cpt = 0;

//Requête
$sql = "SELECT a.*, b.pseudo FROM `messages` AS a LEFT JOIN joueurs AS b ON b.id = a.id_from WHERE `id_to` = '".$_SESSION['id_joueur']."' AND `location` = 'mes' ORDER BY id_message DESC" ;
$req = sql_query($sql);
$nombre = mysql_num_rows($req);
if ($nombre == 0) echo $lang_text['no_messages'].".<br />\n";
while($res = mysql_fetch_array($req))
{//cherche tout les message privé
	//$hide = '';
	//if($res['nouveau'] == '2') { echo "lalala"; $hide = 'hide';}
	//echo $hide;
	$ID_Mess = $res['id_message'];
	$FROM = $res['id_from'];

	$hide = ($res['nouveau'] == '2' ? 'none' : '');
	
	//echo $res['nouveau'];
	?>
	<!-- Message -->
	<table class="newsmalltable">
	<tr>
		<th onclick="javascript:showHide('message_content_<?php echo $ID_Mess ; ?>');" valign="top" width="150px"><?php
			//On va chercher son pseudo
			//$Psql = "SELECT pseudo FROM joueurs WHERE id = '".$FROM."'";
			//$Preq = mysql_query($Psql);
			if($res['pseudo'] == '')
			{
				$bln_PeutRep = false;

				$Pseudo_Aff = (isset($lang_text['other_'.$FROM]) ? $lang_text['other_'.$FROM] : $lang_text['other_unfound']);
				echo $Pseudo_Aff;
			} else {
				$bln_PeutRep = true;
				//$Pres = mysql_fetch_array($Preq);
				$Pseudo_Aff = $res['pseudo'];
				echo "<a href=\"index.php?p=search2&joueurid=".$FROM."\">".$Pseudo_Aff."</a>";
			}
			echo "</td>\n"; 
			?>
		<th onclick="javascript:showHide('message_content_<?php echo $ID_Mess; ?>');" width="275px" align="left" style="text-align: left;"><?php 
		
			//Date
		echo "le ".date($CONF['game_timeformat'], $res['time'])."</th>\n";

		echo "		<td onclick=\"javascript:element.chk_del".$Cpt.".checked='checked';\" width=\"75px\" align=\"right\" style=\"text-align: right;\">\n";
		echo "			".($res['nouveau'] != '2' ? Forum_new(0, 1, false) : '')."\n";
		//Verifie si le joueur est vivant pour répondre
		if($bln_PeutRep == true) { //il existe
			echo "			<a href=\"index.php?p=messmod&id=rep&num=".$ID_Mess."\">";
			echo bw_icon('btn_mailreply.png', $lang_text['reply'])."</a>\n";
		}

		//Show
		//echo "<a href=\"javascript:showHide('message_content_".$res['numero_message_prive']."');\">Lalal</a> ";
		
		//Supprimer
		echo "				<a href=\"index.php?p=messmod&id=del&num=".$ID_Mess."\">".bw_icon("btn_maildelete.png", $lang_text['delete'])."</a>";

		$Cpt ++;
		echo "<input type=\"checkbox\" name=\"chk_del".$Cpt."\" value=\"".$ID_Mess."\">\n";
		echo "		</th>\n";
		echo "	</tr>\n\n";	
		echo "	<tr id=\"message_content_".$ID_Mess."\" style=\"display: ".$hide."\">\n";	
		echo "		<td colspan=\"3\" width=\"350px\">\n";
		echo nl2br(stripslashes(preg_replace("/([^\s]{100})/","$1 ",$res['message'])))."\n"; 
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	</table>\n";
		echo "	<br />\n\n";
}

if ($nombre > 0)
{
	echo "<table width=\"100%\"><tr><td align=\"right\">\n";
	echo "	<input type=\"submit\" value=\"".$lang_text['del_select']."\">\n";
	echo "	</form>\n\n";
	echo "</td><td align=\"left\">\n";
	echo "	<form method=\"post\" action=\"?p=messmod&id=delall\">\n";
	echo "	<input type=\"submit\" value=\"".$lang_text['delall']."\">\n";
	echo "	</form>\n\n";
	echo "</td></tr></table>\n";
}
echo "</fieldset>\n";

//Met à jour nos messages non-lu
$up = "UPDATE `messages` SET nouveau = '2' WHERE `id_to` = '".$_SESSION['id_joueur']."' AND nouveau <> '2' AND location = 'mes'";
sql_query($up);

bw_tableau_end();
?>