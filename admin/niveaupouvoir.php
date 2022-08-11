<?php
//verifie si la session est en cours
include ('adminheader.php');

if($_SESSION['aut'][$adminpage['niveaupouvoir']] == 0)  breakpage();


echo "	<table class=\"newtable\"><tr>\n";
echo "		<td class=\"newtitre\">Arbre des Accès</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newcontenu\">\n";

echo "			<table  class=\"newsmalltable\">\n";
echo "			<tr>\n";
echo "				<th colspan=\"2\">Autorisation</th>\n";
echo "			</tr>\n";

$ArrayAut = array(0, 1, 2, 3, 4, 5, 6, 10, 14);

for($Aut = 0; $Aut < 15; $Aut++)//each($ArrayAut as $Aut)
{
	echo "			<tr>\n";
	echo "				<td align=\"left\">$Aut: ".$admintext[$Aut]."</td>\n";
	echo "			</tr>\n";
}
echo "			</table>\n";
echo "		</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newfin\">&nbsp;</td>\n";
echo "	</tr><tr>\n";
echo "</table>\n";
?>