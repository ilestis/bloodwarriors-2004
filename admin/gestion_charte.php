<?php
require('adminheader.php');

$Page = 'faq';
if($_SESSION['aut'][$adminpage['charte']] == 0)  breakpage();
$Message = '';

if(isset($_POST['go']))
{
	$fichier = './admin/charte.txt'; //le fichier qu'on renomme
	$comment = stripslashes($_POST['charte']); //la variable comment
	$operation = fopen($fichier, 'w');

	if(is_writable($fichier)) 
	{ //verifie si le document est accessible en �criture

		if(!$operation) { //si on arrive pas a le r�duire a 0
			$Message = 'Impossible de r��crire sur le vieux fichier';
		}

		if(fwrite($operation, $comment) == FALSE && $Message == '') { //on arrive pas a �crire dans le fichier
			$Message = 'Impossible d\'�crire dans le fichier '.$fichier.'<br/>';
		}

		if($Message == '') {
			$Message = 'La r��criture de la charte a r�ussi!<br/>';
			fclose($operation);
		}
	}
	else
	{
		$Message = "Impossible d'�crire dans le fichier!<br />\n";
	}
}

//Affiche les rubriques dispo
echo $Message."<br />\n";
echo "<form method=\"post\" action=\"?p=admin_charte\">\n";
echo "	<table class=\"newtable\"><tr>\n";
echo "		<td class=\"newtitre\">Modifier la Charte</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newcontenu\">\n";
echo "			<textarea name=\"charte\" cols=\"70\" rows=\"20\">\n";
require('./admin/charte.txt');
echo "</textarea><br />\n";
echo "		<input type=\"submit\" value=\"Enregistrer\">\n";
echo "		<input type=\"hidden\" name=\"go\" value=\"1\" />\n";
echo "		</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newfin\">&nbsp;</td>\n";
echo "	</tr><tr>\n";
echo "</table>\n";
echo "</form>\n";

echo "<a href=\"?p=admin_admin\">Retour � l'administration</a><br />\n";
?>