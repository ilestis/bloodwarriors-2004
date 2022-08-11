<?php
/*----------------------[TABLEAU]---------------------
|Nom:			AdminHeader.Php
+-----------------------------------------------------
|Description:	Header de la page des admins
+-----------------------------------------------------
|Date de création:				30/04/05
|Dernière modification[Auteur]: jj/mm/aa[Pseudo]
+---------------------------------------------------*/

//verifie si la session est en cours
require ('./include/session_verif.php');

//verifie si on a le niveau
if($_SESSION['aut'][1] == 0)
{
	echo 'Page non-trouvée!<br />';
	breakpage();
}

require ('variables_acces_admins.php');

//tableau de head admin
echo "	<table class=\"newsmalltable\">\n";
echo "	<tr>\n";
echo "		<th><a href=\"?p=admin_admin\">Administration</a></th>\n";
echo "	</tr>\n";

//echo "<tr>\n";
//echo "	<td>Vous êtes ici sur la page des admins, si vous y êtes arrivé par erreur, veuillez envoyer un message privé à un admin ou un email à bloodwarriors@gmail.com, et en aucun cas n'utiliser les fonctions si dessous, sous peine de banissement pour tricherie!</td>\n";
//echo "</tr>\n";

//rechercher un joueur
echo "	<tr>\n";
	echo "		<form name=\"form1\" method=\"post\" action=\"index.php?p=admin_search\">\n";
	echo "		<th>\n";
	echo "			Rechercher: <select name=\"JId\">\n";
	$sql = "SELECT `pseudo`, `id` FROM joueurs ORDER BY `pseudo` ASC" ;
	$result = mysql_query($sql);
	while($res = mysql_fetch_array($result))
	{
		echo "				<option value=\"".$res['id']."\">".clean($res['pseudo'])."</option>\n";
	}
	echo "			</select>\n";
	echo "			<input type=\"submit\" name=\"Submit\" value=\"Rechercher\">\n";
	echo "		</th>\n";
	echo "		</form>\n";
echo "	</tr>\n";
echo "	</table><br />\n\n";

function retour() {
	echo "<a href=\"?p=admin_admin\">Retour à la page admin</a>\n";
}
?>