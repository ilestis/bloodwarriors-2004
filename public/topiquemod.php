<?php
/*
+---------------------
*/

if ($_GET['do'] == 'add')
{//on ajoute
	//Verifie si il y a titre, pseudo et contenu	
	If ($_POST['commentaire'] == '') {
		echo "<H2>Votre message doit avoir un contenu!</H2>\n";
		breakpage();
	}
	//Verifie le pseudo
	if(!isset($_SESSION['id_joueur'])) {
		if (!isset($_POST['postposter']) || $_POST['postposter'] == '') {
			$Pseudo = 'Annonyme'.mt_rand(65, 744);
			$SessionId = 0;
		}
		else
		{//Y'a un pseudo, vérifie si le pseudo n'existe pas déjà
			$sql = "SELECT pseudo FROM joueurs WHERE pseudo = '".clean($_POST['postposter'])."'";
			$req = sql_query($sql);
			$nbr = mysql_num_rows($req);
			if ($nbr == 1) {
				echo "Quelqun utilise déjà ce pseudo!<br />";
				$Pseudo = 'Annonyme'.mt_rand(53, 539);
				$SessionId = 0;
			}
			else {//Ok
				$Pseudo = clean($_POST['postposter']);
				$SessionId = 0;
			}
		}
	}	
	else{	
		$Pseudo = $Joueur->pseudo; 
		$SessionId = $_SESSION['id_joueur'];
	}

	//Verifie si le sujet existe
	$sql = "SELECT subject_id, theme_id,  subject_locked FROM forum_subjects WHERE subject_id = '".$_GET['sujet']."'";
	$req = sql_query($sql);
	$Verification = mysql_num_rows($req);
	If ($Verification == 0)  {//Le theme n'existe pas!
		echo "<H2>Le sujet n'existe pas!</H2><br />\n"; 
		breakpage();
	}
	
	//Verifie si on a le droit de créer
	$res = mysql_fetch_array($req);
	//Variables
	$Subject_Id = $res['subject_id'];
	$Subject_ThemeId = $res['theme_id'];
	$Theme_Id = $res['theme_id'];
	$Subject_Locked = $res['subject_locked'];

	if ($Subject_Locked == 1) {//Sujet locké
		echo "Ce sujet est fermé!";
		breakpage();
	}

	//Verifie les données du theme
	if (($Subject_ThemeId - 11660) > 0) {#C'est dans l'alliance
		$Sql_Subject_ThemeId = 9;
	}
	else {#Normal
		$Sql_Subject_ThemeId = $Subject_ThemeId;
	}
	$sql = "SELECT theme_status FROM forum_themes WHERE theme_id = '".$Sql_Subject_ThemeId."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);
	$Theme_Status = $res['theme_status'];

	//Regarde si on a acces
	$CanView = FALSE;
	If ($Theme_Status == 1) {//Pas blocké, c'est bon signe
		$CanView = TRUE;
	}
	If ($Theme_Status == 2) {//Verifie la connextion
		If (isset($_SESSION['id_joueur'])) {//Ok
			$CanView = TRUE;
		}
	}
	If ($Theme_Status == 3) {//Verifie alliance
		If (isset($_SESSION['id_joueur'])) {//Ok	
			If ('11660'.$Joueur->ally_id == $Subject_ThemeId) {#Ok
				$CanView = TRUE;
			}
		}
	}
	If ($Theme_Status == 4) {
		If(isset($_SESSION['id_joueur'])) {//on est connecté
			If($Joueur->acceslvl >= 3) {//On a acces
				$CanView = TRUE;
			}
		}
	}

	if ($CanView == TRUE) {//On ajoute notre messages
		//Update les tags
		$Pseudo = $Pseudo;
		$Contenu = forumadd($_POST['commentaire']);
		$Contenu = forummessage($Contenu);
		$Time = time();
		$Date = "Le ".date("d/m/Y").' à '.date("G:i");

		$up = "INSERT INTO forum_messages VALUES('', '".$Subject_Id."', '".$Pseudo."', '".$SessionId."', '".$Contenu."', '".$Time."', '0', '0')";
		sql_query($up);
		$Message_Id = mysql_insert_id();

		$up2 = "UPDATE forum_subjects SET subject_last_post_id = '".$Message_Id."' WHERE subject_id = '".$Subject_Id."'";
		sql_query($up2);

		//Seulement si c'est pas une alliance
		if ($Subject_ThemeId < 11660) {
			$up2 = "UPDATE forum_themes SET theme_last_post_id = '".$Message_Id."' WHERE theme_id = '".$Theme_Id."'";
			sql_query($up2);
		}

		//Ajoute +1 message
		$up3 = "UPDATE forum_subjects SET subject_replies = (subject_replies+1) WHERE subject_id = '".$Subject_Id."'";
		sql_query($up3);

		$up4 = "UPDATE forum_themes SET theme_posts = (theme_posts+1) WHERE theme_id = '".$Theme_Id."'";
		sql_query($up4);

		if(isset($_SESSION['id_joueur'])) {//Update notre visite

			if ($Theme_Status == 3) {#Alliance
				$up2 = "UPDATE forum_last_visite SET `ally` = '".$timeplus1."' WHERE id_joueur = '".$_SESSION['id_joueur']."'";
			} else {
				$timeplus1 = time()+1;
				$up2 = "UPDATE forum_last_visite SET `".$Theme_Id."` = '".$timeplus1."' WHERE id_joueur = '".$_SESSION['id_joueur']."'";
			}
			sql_query($up2);
		}

		echo "<script language='JavaScript'>compteur =setTimeout('window.location=\"index.php?p=topic2&id=".$Subject_Id."\"',10)</script>";


	}
	else
	{
		echo "<H2>Vous n'avez pas les droits pour poster ici!</H2>\n";
	}
} elseif ($_GET['do'] == 'del')
{
	//Verifie que le theme existe
	$sql = "SELECT a.subject_id, b.theme_id FROM forum_messages AS a LEFT JOIN forum_subjects AS b ON b.subject_id = a.subject_id WHERE a.message_id = '".clean($_GET['id'])."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);
	//Variable
	$Theme_Id = $res['theme_id'];

	if ($Theme_Id > 11660) {#C'est un forum d'alliance
		$ZeTheme_Id = 9;
	}
	else {#Pas alliance
		$ZeTheme_Id = $Theme_Id;
	}
	
	//Prend les acces du theme
	$sql = "SELECT theme_status FROM forum_themes WHERE theme_id = '".$ZeTheme_Id."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);

	$CanDelete = FALSE;
	if ($res['theme_status'] == 3 && $Joueur->ally_id == $Theme_Id && $Joueur->ally_lvl >= 4) {#Alliance
		$CanDelete = TRUE;
	} elseif ($Joueur->acceslvl >= 3) {#Admin sur les normaux
		$CanDelete = TRUE;
	}

	//Verfie si on est admin ou chef d'alliance
	if ($CanDelete == TRUE) {//Passe
		$del = "DELETE FROM forum_messages WHERE message_id = '".clean($_GET['id'])."'";
		sql_query($del);

		$up = "UPDATE forum_subjects SET subject_replies = (subject_replies-1) WHERE subject_id = '".$Subject_Id."'";
		sql_query($up);
	}

	//rediection
	echo "<script language='JavaScript'>compteur =setTimeout('window.location=\"index.php?p=topic2&id=".$Subject_Id."\"',10)</script>";
} elseif ($_GET['do'] == 'edit') {
	//On edit notre poste

	//Verifie que le theme existe
	$sql = "SELECT a.subject_id, a.message_poster,a. message_text, b.theme_id FROM forum_messages AS a LEFT JOIN forum_subjects AS b ON b.subject_id = a.subject_id WHERE a.message_id = '".clean($_GET['id'])."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);

	//Variables
	$Subject_Id = $res['subject_id'];
	$Post_Poster = $res['message_poster'];
	$Post_Text = $res['message_text'];
	$Theme_Id = $res['theme_id'];

	if ($Theme_Id > 116600) {#C'est un forum d'alliance
		$ZeTheme_Id = 9;
		$Theme_Id = $Theme_Id - 116600;
	}
	else {#Pas alliance
		$ZeTheme_Id = $Theme_Id;
	}
	
	//Prend les acces du theme
	$sql = "SELECT theme_status FROM forum_themes WHERE theme_id = '".$ZeTheme_Id."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);

	$CanEdit = FALSE;
	if ($res['theme_status'] == 3 && $Joueur->ally_id == $Theme_Id && $Joueur->ally_lvl >= 4) {#Alliance
		$CanEdit = TRUE;
	} elseif ($Joueur->acceslvl >= 3) {#Admin sur les normaux
		$CanEdit = TRUE;
	} elseif ($Post_Poster == $Joueur->pseudo) {
		$CanEdit = TRUE;
	}
	
	if ($CanEdit) {
		//Ok on affiche la page
		$Text = reversemessage($Post_Text);
	
		bw_tableau_start("Editer le message");
		echo "<fieldset><legend>Modifier le contenu du message</legend>\n";
		echo "<FORM METHOD=\"POST\" name=\"post\" ACTION=\"index.php?p=topicmod2&do=edit2&id=".$_GET['id']."\">\n";
		require('./include/mise_en_forme.inc.php');
		bw_afficheToolbar("Enregistrer", $Text);
		/*
			<script language="JavaScript" type="text/javascript">
				//pour les émoticons
				function emoticon(text) {
					text = ' ' + text + ' ';
					if (document.post.comment.createTextRange && document.post.comment.caretPos) {
						var caretPos = document.post.comment.caretPos;
						caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
						document.post.comment.focus();
					} else {
					document.post.comment.value  += text;
					document.post.comment.focus();
					}
				}
			</script>

			<table class="newtable"><tr>
				<td class="newtitre">Editer un message</td>
			</tr><tr>
				<td class="newcontenu">

			<!-- Cadre -->
			<TABLE class="newsmalltable">
			<TR>
				<TH>Editer notre message</TH>
			</TR>
			<TR>
				<TD>
				<center>
				<?php
				include ('include/mise_en_forme.inc.php');
				
				<br><textarea name="comment" cols="50" rows="7" id="comment"><?php echo $Text; </textarea><br />

				<INPUT TYPE="submit" VALUE="Prendre en compte les modifications">
				</FORM>
				</center>
				</TD>
			</TR>
			</TABLE>*/
			echo "</fieldset></form>\n";
			bw_tableau_end();
			?>

		

		<?php
	}
	else
	{//Pas ok
		echo "<span class=\"avert\">Ce message ne vous appartient pas!</span>\n";
	}
} elseif ($_GET['do'] == 'edit2') {//On valide notre edit
	//Verifie si on peut editer
		//Verifie que le theme existe
	$sql = "SELECT subject_id, message_poster FROM forum_messages WHERE message_id = '".clean($_GET['id'])."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);
		//Variable
		$Subject_Id = $res['subject_id'];
		$Post_Poster = $res['message_poster'];

	//prend le theme
	$sql = "SELECT theme_id FROM forum_subjects WHERE subject_id = '".$Subject_Id."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);
		//Variable
		$Theme_Id = $res['theme_id'];
	if ($Theme_Id > 116600) {#C'est un forum d'alliance
		$ZeTheme_Id = 9;
		$Theme_Id = $Theme_Id - 116600;
	}
	else {#Pas alliance
		$ZeTheme_Id = $Theme_Id;
	}
	
	//Prend les acces du theme
	$sql = "SELECT theme_status FROM forum_themes WHERE theme_id = '".$ZeTheme_Id."'";
	$req = sql_query($sql);
	$res = mysql_fetch_array($req);

	$CanEdit = FALSE;
	
	if ($res['theme_status'] == 3) {#Alliance
		if($Joueur->ally_id == $Theme_Id) {
			if ($Joueur->ally_lvl >= 4) {
				$CanEdit = TRUE;
			}
		}
	}
	else {
		if ($Joueur->acceslvl >= 3) {#Admin sur les normaux
			$CanEdit = TRUE;
		}
	}
	if ($Post_Poster == $Joueur->pseudo) $CanEdit = TRUE;

	if ($CanEdit) {
		//Ok on valide

		//Transforme
		$Text = forumadd($_POST['commentaire']);
		$Text = forummessage($Text);

		//On update
		$up = "UPDATE forum_messages SET message_text = '".$Text."', message_edit_count = (message_edit_count+1), message_edit = '".$Joueur->pseudo."' WHERE message_id = '".clean($_GET['id'])."'";
		sql_query($up);

		//redirection
		echo "<script language='JavaScript'>compteur =setTimeout('window.location=\"index.php?p=topic2&id=".$Subject_Id."\"',10)</script>";

	}
	else
	{
		echo "<span class=\"avert\">Ce message ne vous appartient pas!</span>\n";
	}
}