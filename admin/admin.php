<?php
//header
include ('adminheader.php');

//fonction de véricication
function verif_niveau($mylvl, $link, $name, $text)
{
	$return = '&nbsp;';
	if($mylvl == 1) $return = "<a href=\"index.php?p=admin_".$link."\">".$name."</a>";
	if($text != '') $return .= "<br />".$text;
	return $return;
}



//Nombre d'entrées dans les tables
$inscriptions = $csql->numRows("SELECT id FROM inscriptions");
$messages = $csql->numRows("SELECT id_message FROM messages WHERE location = 'a_m'");
$forums = $csql->numRows("SELECT id_message FROM messages WHERE location = 'a_f'");
	

/* ------------------------------- */
bw_tableau_start("Index d'administration");
//le tableau admin
echo "<table class=\"newsmalltable\">\n";
echo "<tr>\n";
echo "	<th>Gestion des joueurs</th>\n";
echo "	<th>Gestion du jeu</th>\n";
echo "	<th>Autre</th>\n";
echo "</tr>\n";


echo "<tr>\n";
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['nouveauxcomptes']], 'new', 'Validation des comptes','Il y a '.$inscriptions.' compte'.pluriel($inscriptions,'s').' à valider')."</td>\n";
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['messagesaison']], 'messagesaison', 'Modifier le message d\'acceuil', '')."</td>\n";
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['niveaupouvoir']], 'niveaupouvoir', 'Arbre des accès', '')."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['messages']], 'administration&go=messages', 'Administrer les messages privés', 'Il y a '.$messages.' message'.pluriel($messages, 's').' à administrer')."</td>\n";
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['faq']], 'faq', 'FAQ<br />',''). verif_niveau($_SESSION['aut'][$adminpage['charte']], 'charte', 'La Charte', '')."</td>\n";
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['variable_dico']], 'dico', 'Dictionnaire', '')."</td>\n";

echo "</tr>\n";

echo "<tr>\n";
echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['forumally']], 'administration&go=forums', 'Administrer les forums d\'alliance', 'Il y a '.$forums.' contribution'.pluriel($forums, 's').' à administrer')."</td>\n";
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['charte']], 'regles', 'Les Règles', '')."</td>\n";
	echo "	<td>&nbsp;</td>\n";

echo "</tr><tr>\n";

	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['messagesprives']], 'messagesprives&go=sendall', 'Envoyer un message collectif', '')."</td>\n";
	echo "	<td>&nbsp;</td>\n";//Gestion des tours
	echo "	<td>&nbsp;</td>\n";

echo "</tr><tr>\n";

	echo "	<td>&nbsp;</td>\n"; //".verif_niveau($_SESSION['aut'][$adminpage['variable_player']], 'variable_player', 'Niveau des joueurs', '')."
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['variable_batiment']], 'variable_batiment', 'Config Bâtiments', '')."</td>\n";
	echo "	<td>&nbsp;</td>\n";

echo "</tr><tr>\n";

	echo "	<td>&nbsp;</td>\n";
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['variable_races']], 'races', 'Config Races', '')."</td>\n";
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['journal']], 'journal', 'Journal des évenements', '')."</td>\n";

echo "</tr><tr>\n";

	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['messagesprives']], 'messagesprives&go=select', 'Visionner les messages privés', '')."</td>\n";
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['variable_saison']], 'variable_saison', 'Config Partie', '')."</td>\n";
	echo '	<td class="in"> </td>';

echo "</tr><tr>\n";

	echo "	<td>&nbsp;</td>\n";
	//echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['variable_crea']], 'variable_crea', 'Config Unités', '')."</td>\n";	
	echo "	<td>&nbsp;</td>\n";
	echo "	<td>".verif_niveau($_SESSION['aut'][$adminpage['variable_newsadmin']], 'variable_newsadmin', 'News des admins', '')."</a></td>\n";
echo '</tr></TABLE>';

bw_tableau_end();