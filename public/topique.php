<?php
/*
+---------------------
|Nom: Le topique
+---------------------
|Description: affichage du sujet
+---------------------
|Date de création: Août 04
|Date du premier test: Août 04
|Dernière modification: 15 Aout 05 [V.1]
+-------------------*/
?>
<script language="JavaScript" type="text/javascript">
//pour les émoticons
function emoticon(text) {
	text = ' ' + text + ' ';
	if (document.post.commentaire.createTextRange && document.post.commentaire.caretPos) {
		var caretPos = document.post.commentaire.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		document.post.commentaire.focus();
	} else {
	document.post.commentaire.value  += text;
	document.post.commentaire.focus();
	}
}
</script>

<?php
//Verifie si le sujet existe
$sql = "SELECT subject_id, subject_title,subject_view, subject_status, subject_locked, theme_id FROM forum_subjects WHERE subject_id = '".$_GET['id']."'";
$req = sql_query($sql);
$nbr = mysql_num_rows($req);
if ($nbr == 0)
{//Existe pas
	?>
	<table class="newtable"><tr>
		<td class="newtitre">Information</td>
	</tr><tr>
		<td class="newfin"><span class="info">Ce sujet n'existe pas</span></td>
	</tr></table>
	<?php
		exit;
}

//Verifie si on a acces au theme
$res = mysql_fetch_array($req);
//Variables
$Subject_Id = $res['subject_id'];
$Subject_Name = affiche($res['subject_title']);
$Subject_Status = $res['subject_status'];
$Subject_ThemeId = $res['theme_id'];
$Subject_Locked = $res['subject_locked'];
$Subject_Views = $res['subject_view'];

if (($Subject_ThemeId - 116600) > 0) {#C'est dans l'alliance
	$Sql_Subject_ThemeId = 9;
	//$Subject_ThemeId -= 116600; 
}
else {#Normal
	$Sql_Subject_ThemeId = $Subject_ThemeId;
}
$sql = "SELECT theme_id, theme_status, theme_name FROM forum_themes WHERE theme_id = '".$Sql_Subject_ThemeId."'";

$req = sql_query($sql);
$res = mysql_fetch_array($req);
//Variables
$Theme_Id = $res['theme_id'];
$Theme_Name = affiche($res['theme_name']);
$Theme_Status = $res['theme_status'];

//Regarde si on a acces
$CanView = FALSE;
If ($Theme_Status == 1)
{//Pas blocké, c'est bon signe
	$CanView = TRUE;
}
If ($Theme_Status == 2)
{//Verifie la connextion
	If (isset($_SESSION['id_joueur']))
	{//Ok
		$CanView = TRUE;
	}
}
If ($Theme_Status == 3)
{//Verifie si on a une alliance
	If (isset($_SESSION['id_joueur']))
	{#Connecté
		if ("11660".$Joueur->ally_id == $Subject_ThemeId) {#A une alliance
			$CanView = TRUE;
		}
	}
}
If ($Theme_Status == 4)
{
	If(isset($_SESSION['id_joueur']))
	{//on est connecté
		If($Joueur->acceslvl >= 3)
		{//On a acces
			$CanView = TRUE;
		}
	}
}

if ($CanView)
{//On peut voir
	//Update un view
	$up = "UPDATE forum_subjects SET subject_view = (subject_view+1) WHERE subject_id = '".$Subject_Id."'";
	sql_query($up);
	?>
	<table class="newtable"><tr>
		<td class="newtitre"><?php echo $Subject_Name." (Vu ".$Subject_Views."x)"; 

	//Modération
	if (isset($_SESSION['id_joueur']) and ($Joueur->acceslvl >= 3)) {//Peut moderer
		if ($Subject_Status == 2) {//Upé
			echo "<a href=\"index.php?p=forummod2&do=up&subject_id=".$Subject_Id."\"><img src=\"images/icons/btn_lock.png\"></a>"; }
		elseif ($Subject_Status == 1) {//Pas uppé
			echo "<a href=\"index.php?p=forummod2&do=up&subject_id=".$Subject_Id."\"><img src=\"images/up.png\"></a>"; }
		
		//Locked
		if ($Subject_Locked == 1) {//Locké
			echo "<a href=\"index.php?p=forummod2&do=lock&subject_id=".$Subject_Id."\"><img src=\"images/icons/btn_unlock.png\"></a>"; }
		elseif ($Subject_Locked == 0) {//Pas locké
			echo "<a href=\"index.php?p=forummod2&do=lock&subject_id=".$Subject_Id."\"><img src=\"images/icons/btn_lock.png\"></a>"; }

	}
	?>
		</td>
	</tr><tr>
		<td class="newcontenu">
	
	<?php
	echo "<a href=\"index.php?p=forumgen\">Forum</a> :: <a href=\"index.php?p=forum2&theme=".$Theme_Id."\">".$Theme_Name."</a> :: <a href=\"index.php?p=topic2&id=".$Subject_Id."&page=1\">".$Subject_Name."</a><br />\n";

	//Prend chaque message
	$NbrPages = dernierepage($Subject_Id, $CONF['forum_messages']);

//Compteur de page
	if ($NbrPages > 1) {//Il y a plusieurs pages
		echo "Saut de page: ";
		for ($i = 1; $i <= $NbrPages; $i++) {
			echo "<a href=\"index.php?p=topic2&id=".$Subject_Id."&page=".$i."\">".$i."</a>&nbsp;";
		}
		echo "<br />\n";
	}

	//Notre dernière visite
	if(isset($_SESSION['id_joueur'])) {
		$Last_Visiting =  get_visiting($Subject_Id);

		//Update notre dernière visite
		update_visiting($Subject_Id);
	} else {
		$Last_Visiting = 0;
	}

	if(!isset($_GET['page'])) $Page = $NbrPages;
	else $Page = $_GET['page'];
	$Start = (($Page*$CONF['forum_messages'])-$CONF['forum_messages']);
	$End = $CONF['forum_messages'];
	$sql = "SELECT * FROM forum_messages WHERE subject_id = '".$Subject_Id."' ORDER BY message_id ASC LIMIT $Start, $End";
	$req = sql_query($sql);
	while ($res = mysql_fetch_array($req))
	{//Prend chaque message
		//Variables
		$Message_Id = $res['message_id'];
		$Subject_Id = $res['subject_id'];
		$Message_Poster = affiche($res['message_poster']);
		$Message_Poster_ID = $res['message_poster_id'];
		$Message_Text = affiche($res['message_text']);
		$Message_Date = date($CONF['game_timeformat'], $res['message_time']);
		$Message_Edit = $res['message_edit'];
		$Message_Edit_Count = $res['message_edit_count'];
		$UnRead = ($res['message_time'] > $Last_Visiting ? Forum_new(0, 1, false)." / " : "");

		
		//Verifie s'il est inscrit ou annonyme
		$ins = "SELECT avatar FROM joueurs WHERE id = '".$Message_Poster_ID."'";
		$inq = sql_query($ins);
		$exi = mysql_fetch_array($inq);
		if ($exi['avatar'] != "")
		{//existe
			$Avatar = $exi['avatar'];
		}
		else
		{	$Avatar = 'annonyme'; }

		echo "<table class=\"newsmalltable\">\n";
		echo "<tr>\n";

		//Pseudo et avatar

		echo "	<th rowspan=\"2\" width=\"130px\" height=\"10px\" valign=\"top\">\n";
		echo "		".$Message_Poster."<br />\n";
		if ($Avatar != 'annonyme') echo "		<img src=\"images/avatars/".$Message_Poster_ID."_".$Avatar."\" /><br />\n";
		echo "</th>\n";

		echo "	<th style=\"text-align: left; height: 17px; max-height: 17px;\" colspan=\"2\" width=\"auto\">\n";
		echo "		<div style=\"float:left; height:15px;\">".$Message_Date."</div>";
		
		echo "<div style=\"float:right;height:auto;\">".$UnRead;
		
		if ($Subject_Locked == 0) {
			echo "<a href=\"index.php?p=messwright&id=".$Subject_Id."&quoteid=".$Message_Id."\"><img src=\"images/icons/btn_quote.png\" alt=\"Quote\" /></a>\n";
		}
		if (isset($_SESSION['id_joueur']))
		{//Editer/Supprimer?
			if ($Joueur->acceslvl >= 3)
			{//Admin -> peut editer & supprimer
				echo "		<a href=\"index.php?p=topicmod2&do=edit&id=".$Message_Id."\"><img src=\"images/icons/btn_edit.png\" /></a> ";
				echo "		<a href=\"index.php?p=topicmod2&do=del&id=".$Message_Id."\"><img src=\"images/icons/btn_delete.png\" /></a>\n";
			}
			elseif ($Message_Poster_ID == $_SESSION['id_joueur'])
			{//Son message -> peut editer
				echo "		<a href=\"index.php?p=topicmod2&do=edit&id=".$Message_Id."\"><img src=\"images/edit.png\" /></a>\n";
			}
		}
		echo "</div>";
		echo "</th>";
		
		echo "</tr>\n";
		echo "<tr>\n";

		echo "	<td class=\"in\" colspan=\"2\" valign=\"top\" style=\"text-align: left; min-height:40px;\">\n";
		echo "		".$Message_Text."\n";
		if ($Message_Edit_Count > 0) {
			//Ca a été édité
			echo "<br><br><div style=\"float: right;\"><em>Édité ".$Message_Edit_Count." fois. Modifié en dernier par ".$Message_Edit."</em></float>\n";
		}
		echo "<br />\n";
		echo "	</td>\n";
		echo "</tr>\n";
		echo "</table><br />\n";
	}

	//Liens
	echo "<a href=\"index.php?p=forumgen\">Forum</a> :: <a href=\"index.php?p=forum2&theme=".$Theme_Id."\">".$Theme_Name."</a> :: <a href=\"index.php?p=topic2&id=".$Subject_Id."&page=1\">".$Subject_Name."</a><br />\n";

	//Compteur de page
	if ($NbrPages > 1) {//Il y a plusieurs pages
		echo "Saut de page: ";
		for ($i = 1; $i <= $NbrPages; $i++) {
			echo "<a href=\"index.php?p=topic2&id=".$Subject_Id."&page=".$i."\">".$i."</a>&nbsp;";
		}
		echo "<br />\n";
	}

	//Ajouter une réponse
	if ($Subject_Locked == 0)
	{?>	<br>
		<TABLE class="newsmalltable">
		<TR>
			<TH>Ajouter une réponse</TH>
		</TR>
		<TR>
			<TD>
			<FORM METHOD="POST" name="post" ACTION="index.php?p=topicmod2&do=add&sujet=<?php echo $Subject_Id; ?>">
			<?php 
			if(!isset($_SESSION['id_joueur'])) {//Pas connecté donc ->
			echo "	Pseudo: <INPUT TYPE=\"text\" NAME=\"postposter\" maxlength=\"20\"><br />\n";
			}

			include ('include/mise_en_forme.inc.php');
			bw_afficheToolbar("Poster");

			?>
			<INPUT TYPE="hidden" name="do" value="add">
			</FORM>
			</TD>
		</TR>
		</TABLE>
	<?php
	}
	bw_tableau_end();
}