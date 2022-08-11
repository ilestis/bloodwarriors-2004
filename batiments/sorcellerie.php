<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Sorcellerie.php
+-----------------------------------------------------
|Description:	Permet de lancer des sorts
+-----------------------------------------------------
|Date de création:				29/03/05
|Dernière modification[Auteur]: 03/09/07[Escape]
+---------------------------------------------------*/
include ('./profil.php');
include ('./include/function_sorts.php');

bw_tableau_start("Sorcellerie");

//verifie si on a les bâtiments
if ($SortR != 1) { //Pas de sorcelerie
	bw_fieldset("Information", "Votre Sorcellerie n'est pas construite ou trop endommagée!");
	bw_tableau_end();
	breakpage();
	exit; 
}

// Hypnose?
if(check_spell($_SESSION['id_province'], '43')) {
	bw_fieldset("Information", "Vous êtes sous l'emprise d'une " . bw_popup('Hypnose', 'sort', '43')."! Vous ne pouvez pas lancer de sorts en ce moment.");
	bw_tableau_end();
	breakpage();
	exit; 
}

define('IN_SORT', true);

if(!isset($_POST['idsort']))
{
	/*---------------------------------------------------------------------------
	//------------------------------SELECTION DU SORT----------------------------
	//-------------------------------------------------------------------------*/
	?>
			<table class="newsmalltable">
			<tr>
				<th width="75px"><br /></th>
				<th width="175px">Nom</th>
				<th colspan="2">Description</th>				
			</tr>

			<?php
			//prend chaque sort dans la base de données
			$sort		= "SELECT * FROM `liste_sorts` WHERE race = '0' OR race = '".$Joueur->race."' ORDER BY nom ASC, batint ASC";
			$reqs		= sql_query($sort);
			while($ress = mysql_fetch_assoc($reqs))
			{
				//variables
				$Sort			= $ress['id'];
				$Nom			= $ress['nom'];
				$Description	= $ress['description'];
				$Cout			= $ress['cout'];
				$BatInt			= $ress['batint'];
				$Pourcentage	= $ress['pourcentage'];
				$OK = true;
				//$Cible			= $ress['cible'];

				if ($BatInt != 0)
				{//besoin d'un  bâtiment
					if (!bw_batiavailable($BatInt)) {//ok
						$OK = false;
					}
				}
				
				if($OK)
				{//pas besoin d'un bâtiment
					echo "
			<form method=\"post\" action=\"index.php?p=bsp_sort\" >
			<tr>
				<td><img src=\"images/sorts/".$Sort.".png\"/></td>
				<td>".$Nom."</td>
				<td>".affiche($Description)."</I></td>
				<td><input type=\"submit\" value=\"Lancer\" /></td>
			</tr>
			<input type=\"hidden\" name=\"idsort\" value=\"".$Sort."\" />
			</form>\n";
				}

			}
			
		echo "</table>\n";

		bw_tableau_end();
}
else
{
	//On a choisi un sort
	switch(clean($_POST['idsort']))
	{
		case 1:
			//Charme
			require ('sorts/charme.php');
			break;

		case 2:
			//Pickpocket
			require ('sorts/vol.php');
			break;

		case 3:
			//Main forte
			require ('sorts/mainforte.php');
			break;

		case 4:
			//Protection
			require ('sorts/protection.php');
			break;

		case 5:
			//Carapace
			require ('sorts/carapace.php');
			break;

		case 6:
			//Epidemie
			require ('sorts/epidemie.php');
			break;

		case 7: 
			//entropique
			require ('sorts/bouledemanaentropique.php');
			break;

		case 9: 
			//entropique
			require ('sorts/espritduguerrier.php');
			break;

		case 10:
			//Vol +
			require ('sorts/volplus.php');
			break;

		case 11:
			//Charme +
			require ('sorts/charmeplus.php');
			break;

		case 12:
			//Charme +
			require ('sorts/gainmagique.php');
			break;

		case 13:
			//Fuite de Magie
			require ('sorts/fuitedemagie.php');
			break;

		case 14:
			//Grande Pourriture
			require ('sorts/grandepourriture.php');
			break;

		case 15:
			//Ailes de la victoire
			require ('sorts/ailesdelavictoire.php');
			break;
			
		case 16:
			//Marteau D'Acier
			require ('sorts/marteaudacier.php');
			break;

		case 17:
			//Fécondité Spirituelle
			require ('sorts/feconditespirituelle.php');
			break;

		case 18:
			//Nouveau Souffle
			require ('sorts/nouveausouffle.php');
			break;

		case 19:
			//Grand Bouclier
			require ('sorts/grandbouclier.php');
			break;

		case 20:
			//Tremblement
			require ('sorts/tremblement.php');
			break;

		case 21:
			//Sainte-Aura
			require ('sorts/sainteaura.php');
			break;

		case 22:
			//Rage controlée
			require ('sorts/rage.php');
			break;

		case 23:
			//Bénédiction
			require ('sorts/benediction.php');
			break;
			
		case 24:
			//Annulation
			require ('sorts/annulation.php');
			break;
			
		case 25:
			//Force
			require ('sorts/force.php');
			break;
			
		case 26:
			//Resistance
			require ('sorts/resistance.php');
			break;
			
		case 27:
			//croissance_force
			require ('sorts/croissance_force.php');
			break;
			
		case 28:
			//croissance_resistance
			require ('sorts/croissance_resistance.php');
			break;
			
		case 29:
			//croissance_vitesse
			require ('sorts/croissance_vitesse.php');
			break;
			
	# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
	#			CHANTS/CRI
	# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
		case 30:
			require ('sorts/chant_angelique.php');
			break;
			
		case 31:
			require ('sorts/cri_angelique.php');
			break;
		
		case 32:
			require ('sorts/chant_desordre.php');
			break;
			
		case 33:
			require ('sorts/cri_desordre.php');
			break;

		case 34:
			require ('sorts/chant_demoniaque.php');
			break;
			
		case 35:
			require ('sorts/cri_demoniaque.php');
			break;

		case 36:
			require ('sorts/chant_elfique.php');
			break;
			
		case 37:
			require ('sorts/cri_elfique.php');
			break;

		case 38:
			require ('sorts/chant_rassemblement.php');
			break;
			
		case 39:
			require ('sorts/cri_rassemblement.php');
			break;

		case 40:
			require ('sorts/chant_concentration.php');
			break;
			
		case 41:
			require ('sorts/cri_concentration.php');
			break;

	# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
	#			PLUS
	# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
		case 42:
			require ('sorts/paralysie.php');
			break;
			
		case 43:
			require ('sorts/hypnose.php');
			break;

		default:
			echo "<h2>Pas encore disponnible.</h2><br />\n";
			break;

	}

	
	echo "</fieldset><br />\n";
	echo "<a href=\"index.php?p=bsp_sort\">Retour à la sorcelerie</a>\n";


}
bw_tableau_end();

