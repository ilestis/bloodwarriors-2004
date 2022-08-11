<?php
/*--------------------
|Nom: La rubrique
+---------------------
|Description: Definit l'include de la pseudo-frame
+---------------------
|Date de création: Août 04
|Date du premier test: Août 04
|Dernière modification: 10 Fev 06
+-------------------*/
//pour les paysans
if(!isset($_GET['p'])) $p = 'index';
else $p = clean($_GET['p']);

switch ($p)
{
	//------------------------------------------
	//            NO LOG
	case 'inscrip':
		include 'inscription.php';
		break;
	case 'inscription_next':
		include 'inscription_verifie.php';
		break;
	case 'parte':
		include 'partenaria.php';
		break;
	case 'merci':
		include 'nolog_merci.php';
		break;
	case 'download':
		include 'nolog_download.php';
		break;
	case 'regles':
		include 'regles.php';
		break;
	case 'faq':
		include 'faq.php';
		break;
	case 'index':
		include 'profilium.php';
		break;
	case 'lostpassword':
		include 'lostpassword.php';
		break;
	case 'carte':
		include 'mapdujeu.php';
		break;
	//      /NO LOG
	//------------------------------------------


	//------------------------------------------
	//            CONNECTE
	case 'mess':
		include ('./messagerie.php');
		break;
	case 'messmod':
		$id = $_GET['id'];
		include ('./messagemod.php');
		break;
	case 'search2':
		include ('./heros.php');
		break;
	case 'statistiques':
		include ('./statistiques.php');
		break;
	case 'paysans':
		include ('./paysans.php');
		break;
	case 'map':
		include ('./mapdujeu.php');
		break;
	case 'const':
		include 'batiments.php';
		break;
	case 'reparation':
		include 'reparations.php';
		break;
	case 'baticons':
		include 'construire.php';
		break;
	case 'batilvlup':
		include 'batimentlvlup.php';	
		break;
	case 'province':
		include 'province.php';
		break;
	case 'plan_province':
		include 'plan_province.php';
		break;
	case 'unites':
		include 'gestion_unites.php';
		break;
	case 'war':
		include 'guerre.php';
		break;
	case 'warchoice':
		include 'guerre_choix.php';
		break;
	case 'scores':
		include 'scores.php';
		break;
	case 'forumgen':
		include 'public/index.php';
		break;
	case 'forum2':
		include 'public/forum.php';
		break;
	case 'forummod2':
		include 'public/forummod.php';
		break;
	case 'topic2':
		include 'public/topique.php';
		break;
	case 'topicmod2':
		include 'public/topiquemod.php';
		break;
	case 'shootbox':
		include 'public/shootbox.php';
		break;
	case 'para':
		include 'parametres.php';
		break;
	case 'parachange':
		include 'paramod.php';
		break;
	case 'memo':
		include 'memo.php';
		break;
	case 'deco':
		include 'loggout.php';
		break;
	case 'annonces':
		include 'annonces.php';
		break;	
	case 'errors':
		include 'errors.php';
		break;	
	//      /CONNECTE
	//------------------------------------------


	//------------------------------------------
	//            ADMINISTRATION
	case 'admin_admin':
		include 'admin/admin.php';
		break;
	case 'admin_new':
		include 'admin/comptes_nouveaux.php';
		break;
	case 'admin_validnew':
		include 'admin/comptes_activation.php';
		break;
	case 'admin_journal':
		include 'admin/journal.php';
		break;
	case 'admin_administration':
		include 'admin/administration.php';
		break;
	case 'admin_search':
		include 'admin/recherche.php';
		break;
	case 'admin_warning':
		include 'admin/warning.php';
		break;
	case 'admin_messagesaison':
		include 'admin/messagesaison.php';
		break;
	case 'admin_supprimationcompte':
		include 'admin/compte_supprimation.php';
		break;
	case 'admin_niveaupouvoir':
		include 'admin/niveaupouvoir.php';
		break;
	case 'admin_variable_guilde':
		include 'admin/variable_guilde.php';
		break;
	case 'admin_dico':
		include 'admin/variable_dico.php';
		break;
	case 'admin_variable_batiment':
		include 'admin/variable_batiment.php';
		break;
	case 'admin_variable_saison':
		include 'admin/variable_saison.php';
		break;
	case 'admin_variable_player':
		include 'admin/variable_player.php';
		break;
	case 'admin_variable_newsadmin':
		include 'admin/variable_admin_news.php';
		break;
	case 'admin_variable_change':
		include 'admin/variable_change.php';
		break;
	case 'admin_variable_configuration':
		include 'admin/variable_configuration.php';
		break;
	case 'admin_variable_crea':
		include 'admin/variable_creatures.php';
		break;
	case 'admin_email':
		include 'admin/email.php';
		break;
	case 'admin_messagesprives':
		require('admin/messagesprives.php');
		break;
	case 'admin_paysans':
		require('admin/player_paysans.php');
		break;
	case 'admin_faq':
		require('admin/gestion_faq.php');
		break;
	case 'admin_modifaut':
		require('admin/comptes_aut.php');
		break;
	case 'admin_charte':
		require('admin/gestion_charte.php');
		break;
	case 'admin_races':
		require('admin/gestion_races.php');
		break;
	case 'admin_regles':
		require('./admin/gestion_regles.php');
		break;
	//      /ADMINISTRATION
	//------------------------------------------


	//------------------------------------------
	//            BATIMENTS SPECIAUX
		case 'batispe':
			include 'batiments/index.php';
			break;
		case 'bsp_marche':
			include 'batiments/marche.php';
			break;
		//fort
		case 'bsp_fort':
			include 'batiments/fort.php';
			break;
		case 'bsp_sort':
			include 'batiments/sorcellerie.php';
			break;
		case 'bsp_terrain':
			include 'batiments/terrain.php';
			break;
		case 'bsp_murailles':
			include './batiments/murailles.php';
			break;
	//            /BATIMENTS SPECIAUX
	//------------------------------------------



	//------------------------------------------
	//            ALLIANCE
	case 'ally_news':
		include 'alliances/alliancenews.php';
		break;
	case 'ally_admin':
		include 'alliances/alliancemod.php';
		break;
	case 'ally_none':
		include 'alliances/recherche.php';
		break;
	case 'ally_forum':
		include 'alliances/forum.php';
		break;
	case 'ally_topic':
		include 'alliances/topique.php';
		break;
	case 'ally_topicmod':
		include 'alliances/topiquemod.php';
		break;
	case 'ally_journal':
		include 'alliances/journal.php';
		break;
	//            /ALLIANCE
	//------------------------------------------

}
echo "<br />\n";
?>