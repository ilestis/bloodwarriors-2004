<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Memo.php
+-----------------------------------------------------
|Description:	Permet au joueur de garder des notes
+-----------------------------------------------------
|Date de création:				19/03/05
|Dernière modification[Auteur]: jj/mm/aa[Pseudo]
+---------------------------------------------------*/
//include
include ('profil.php');

bw_tableau_start("Mémos");

if(isset($_GET['do']) && $_GET['do'] == 'add')
{//on ajoute
	//regarde combien on a de memoire
	$sql = "SELECT * FROM `messages` WHERE location = 'meo' AND id_from = '".$_SESSION['id_joueur']."'";
	$sq = sql_query($sql);
	$num = mysql_num_rows($sq);

	//mise en forme
	$comment = forummessage(forumadd($_POST['comment']));

	if($num < 5)
	{//ok
		$up = "INSERT INTO `messages` SET id_from = '".$_SESSION['id_joueur']."', message = '".$comment."', `time` = '".time()."', location = 'meo'";
		sql_query($up);

		$Message = bw_info("Mémoire bien ajoutée!<br />\n");
	}
	else
	{
		$Message = bw_error("Vous être au quota maximum de 5 mémos!<br />\n");
	}
}
if(isset($_GET['do']) && $_GET['do'] == 'delete')
{//on supprime
	$del = "DELETE FROM messages WHERE id_message = '".clean($_GET['id'])."' AND location = 'meo' AND id_from = '".$_SESSION['id_joueur']."';";
	sql_query($del);
	if(mysql_affected_rows() == 0)
	{
		$Message = bw_error("Ce memo ne vous appartiend pas!<br />\n");
	}
	else
	{
		$Message = bw_info("Mémoire bien supprimée!<br />\n");
	}
}

//on affiche les memos et la possibilité d'en ajouter

if(isset($Message)) bw_fieldset("Information", $Message);


$sql = "SELECT * FROM messages where id_from = '".$_SESSION['id_joueur']."' AND location = 'meo'";
$req = sql_query($sql);
$nbr = mysql_num_rows($req);
if ($nbr == 0) { } //
else {//Affiche les mémoires
	echo "<fieldset>\n";
	echo "	<legend>Vos Mémos</legend>\n";
	while ($res = mysql_fetch_array($req))
	{
		echo "<table class=\"newsmalltable\"><tr><td width=\"750px\"><ins>Ajouté le ".date($CONF['game_timeformat'], $res['time'])." :</ins><br />".affiche($res['message'])."</td>\n";
		echo "<th valign=\"top\"><a href=\"index.php?p=memo&do=delete&id=".$res['id_message']."\">".bw_icon("btn_delete2.png")."</a></th></tr></table>\n";
	}
	echo "</fieldset><br />\n";
}
?>
		<fieldset>
			<legend>Ajouter un Mémos</legend>
			<form method="post" action="index.php?p=memo&do=add">
			<TEXTAREA NAME="comment" ROWS="7" COLS="52" maxlength="500"></TEXTAREA><br />
			<INPUT TYPE="submit" value="Ajouter"><br />
			</form>
		</fieldset>
<?php
echo bw_info("Vous avez le droit à un quota maximum de 5 mémoires.");

bw_tableau_end();
?>
