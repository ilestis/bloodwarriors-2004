<?php
//On verifie si on vient des sorts
if(!defined('IN_SORT'))
{
	exit;
}

//Valeurs
$Craft_ID = '12'; 
$Allowed = FALSE;


//On selectionne les valeurs du sort
$sql = "SELECT * FROM `liste_sorts` WHERE id = '".$Craft_ID."'";
$req = sql_query($sql);
$res = sql_object($req);

//Variables
$Prix			= $res->cout;
$Nom			= $res->nom;
$Variable		= $res->variable;
$Cible			= $res->cible;
$Titre_Choix	= $res->titre_choix;
$BatInt			= $res->batint;

echo "<fieldset><legend>".$Nom."</legend>\n";

if(sort_available($res->batint, $res->race, $Joueur->race))
{
	//Traitement
	if(isset($_POST['parametre']))
	{
		//Verifie si on a assez de magie
		if(check_craft($_SESSION['id_province'], $Prix))
		{//On a passé
			$Ress = mt_rand(1,4);
			switch ($Ress) {
				case 1: $Ress = 'food'; break;
				case 2: $Ress = 'gold'; break;
				case 3: $Ress = 'mat'; break;
				case 4: $Ress = 'craft';
			}
			$Array = array('food' => 'Nourriture', 'gold' => 'Or', 'mat' => 'Matériaux', 'craft' => 'Magie');

			$up = "UPDATE provinces SET `".$Ress."` = (`".$Ress."`+".$Variable.") WHERE id = '".$_SESSION['id_province']."'";
			sql_query($up);

			$Message = bw_info("Vous avez gagné ".$Variable." ressources de ".$Array[$Ress]."!<br />\n");
		}
		else
		{
			//Message
			$Message = bw_error("Vous n'avez pas assez de magie pour lancer ce sortilège!<br />\n");
		}
	}
	if(isset($Message)) echo $Message;

	echo $Titre_Choix;
	echo "<FORM METHOD=\"POST\" ACTION=\"index.php?p=bsp_sort\">\n";
	echo "<INPUT TYPE=\"submit\" value=\"Hocus Pocus!\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"idsort\" value=\"".$Craft_ID."\">\n";
	echo "<INPUT TYPE=\"hidden\" name=\"parametre\" value=\"1\">\n";

	echo "</FORM>\n";
	echo bw_info("Le gain magique vous fait gagner ".$Variable." ressources d'un type<br />(or, nourriture, bois, pierre, magie) au hasard pour ".$Prix." magie.<br />");
		

} else {
	bw_fieldset("Information", bw_error("Vous n'avez pas le bâtiment nécessaire pour lancer ce sortillège!\n"));
}
?>