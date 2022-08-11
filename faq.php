<?php
/*
+---------------------
|Nom: La FAQ
+---------------------
|Description: La Faq
+---------------------
|Date de création: Mars 2007
+---------------------
*/

bw_tableau_start('FAQ');
bw_f_start("Rubriques");
$sql = "SELECT id, name, description FROM faq_rubriques ORDER BY name ASC";
$req = sql_query($sql);
$first = 0;
while($res = sql_object($req))
{
	if($first != 0) echo ", ";
	$first = 1;
	echo "<a href=\"?p=faq&rub=".$res->id."\" title=\"".$res->description."\">".$res->name."</a>";

	if(isset($_GET['rub']) && $_GET['rub'] == $res->id) $NomSection = $res->name;
}
bw_f_end();

if(isset($_GET['rub'])) {
	echo "<br />";
	
	$ID = clean($_GET['rub']);
	$sql = "SELECT question, reponse FROM faq_questions WHERE id_rubrique = '".$ID."';";
	$req = sql_query($sql);
	if(mysql_num_rows($req) > 0) {
		bw_f_start($NomSection, '', 'left');
		while($res = sql_object($req)) {
			echo "<strong>".$res->question."</strong><br />\n";
			echo "<blockquote>".affiche($res->reponse)."</blockquote><br />\n";
		}
		bw_f_end();
	} else {
		echo bw_error("Aucun enregistrement sous cette rubrique");
	}
}
bw_tableau_end();
