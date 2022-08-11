<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Memo.php
+-----------------------------------------------------
|Description:	Permet au joueur de garder des notes
+-----------------------------------------------------
|Date de cr�ation:				19/03/05
|Derni�re modification[Auteur]: jj/mm/aa[Pseudo]
+---------------------------------------------------*/
//include
include ('profil.php');

bw_tableau_start("M�mos");

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

		$Message = bw_info("M�moire bien ajout�e!<br />\n");
	}
	else
	{
		$Message = bw_error("Vous �tre au quota maximum de 5 m�mos!<br />\n");
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
		$Message = bw_info("M�moire bien supprim�e!<br />\n");
	}
}

//on affiche les memos et la possibilit� d'en ajouter

if(isset($Message)) bw_fieldset("Information", $Message);


$sql = "SELECT * FROM messages where id_from = '".$_SESSION['id_joueur']."' AND location = 'meo'";
$req = sql_query($sql);
$nbr = mysql_num_rows($req);
if ($nbr == 0) { } //
else {//Affiche les m�moires
	echo "<fieldset>\n";
	echo "	<legend>Vos M�mos</legend>\n";
	while ($res = mysql_fetch_array($req))
	{
		echo "<table class=\"newsmalltable\"><tr><td width=\"750px\"><ins>Ajout� le ".date($CONF['game_timeformat'], $res['time'])." :</ins><br />".affiche($res['message'])."</td>\n";
		echo "<th valign=\"top\"><a href=\"index.php?p=memo&do=delete&id=".$res['id_message']."\">".bw_icon("btn_delete2.png")."</a></th></tr></table>\n";
	}
	echo "</fieldset><br />\n";
}
?>
		<fieldset>
			<legend>Ajouter un M�mos</legend>
			<form method="post" action="index.php?p=memo&do=add">
			<TEXTAREA NAME="comment" ROWS="7" COLS="52" maxlength="500"></TEXTAREA><br />
			<INPUT TYPE="submit" value="Ajouter"><br />
			</form>
		</fieldset>
<?php
echo bw_info("Vous avez le droit � un quota maximum de 5 m�moires.");

bw_tableau_end();
?>
