<?php
/*----------------------[TABLEAU]---------------------
|Nom:			VariablesAdmins.Php
+-----------------------------------------------------
|Description:	Définit quel niveau est besoin pour les pages des admins
+-----------------------------------------------------
|Date de création:				06/05/05
+---------------------------------------------------*/

$admintext[0] = 'Activé';
$admintext[1] = 'Admin';

//Aut 2 Forum + dico + journal +
$Aut = 2;
$admintext[$Aut] = 'Modération Forum, Dictionnaire, Journal';
$adminpage['journal'] = $Aut;
$adminpage['forum'] = $Aut;
$adminpage['variable_dico'] = $Aut;
//$adminpage['forums'] = $Aut;

//Aut 3 Comptes + Messages
$Aut = 3;
$admintext[$Aut] = 'Nouveaux comptes, Messages, Suppression Comptes, Forums Alliances';
$adminpage['nouveauxcomptes'] = $Aut;
$adminpage['messages'] = $Aut;
$adminpage['suppression'] = $Aut;
$adminpage['forumally'] = $Aut;

//Aut 4 Niveau + Bati + Players + Races
$Aut = 4;
$admintext[$Aut] = 'Niveaux, Bâtiments, Joueurs, Unités';
$adminpage['niveaupouvoir'] = $Aut;
$adminpage['variable_batiment'] = $Aut;
$adminpage['variable_player'] = $Aut;
$adminpage['variable_crea'] = $Aut;
$adminpage['variable_races'] = $Aut;

//Aut 5
$Aut = 5;
$admintext[$Aut] = 'News Admin, Charte, Message Saison';
$adminpage['variable_newsadmin']  = $Aut;
$adminpage['charte'] = $Aut;
$adminpage['messagesaison'] = $Aut;

//Aut 6 Faq
$Aut = 6;
$admintext[$Aut] = 'FAQ';
$adminpage['faq'] = $Aut;

//Aut 10 Messages + Saison + Mail
$Aut = 10;
$admintext[$Aut] = 'Variables Saison, Config, Email, Messages, Select';
$adminpage['messagesprives'] = $Aut;
$adminpage['variable_saison'] = $Aut;
$adminpage['email'] = $Aut;
$adminpage['variable_configuration'] = $Aut;
$adminpage['select'] = $Aut;

//Aut 13 : Develope Team
$Aut = 13;
$admintext[$Aut] = 'Équipe de développement';

//Aut 14 Poster Forum + Shootbox
$Aut = 14;
$admintext[$Aut] = 'Poster Forum, Shootbox';
$admintext['post'] = $Aut;


//lvl 4
//$adminpage['blu'] = $Aut;

//lvl 5

//lvl 6


//lvl 7

//lvl 8

//lvl 9

//lvl 99

?>