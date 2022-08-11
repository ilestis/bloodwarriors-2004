<?php

?>
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

<?php

//Prend chaque themes pour faire le lien, et verifie s'il existe
$sql = "SELECT theme_id, theme_name, theme_status FROM forum_themes WHERE theme_id = '".clean($_GET['theme'])."'";
$req = sql_query($sql);
$Verification = mysql_num_rows($req);
If ($Verification == 0) 
{//Le theme n'existe pas!
	bw_tableau_start("Erreur");
	echo bw_error("Ce thème n'existe pas!");
	bw_tableau_end();
	breakpage();
	exit;
}

//Verifie nos acces
$res = mysql_fetch_array($req);
//Variables
$Theme_Name = $res['theme_name'];
$Theme_Id = $res['theme_id'];
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
{//Verifie qu'on fait partie de l'alliance
	If (isset($_SESSION['id_joueur']))
	{//Ok
		if ($Joueur->ally_id != 0) {//OK
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

If ($CanView == TRUE)
{//On peut voir

	
	bw_tableau_start("<a href=\"index.php?p=forumgen\">Forum</a> :: <a href=\"index.php?p=forum2&theme=".$Theme_Id."\">".$Theme_Name."</a>");

	//Tableau
	echo "<table class=\"newsmalltable\">\n";
	echo "<tr>\n";
		echo "	<TH colspan=\"2\" width=\"300px\">Sujet</TH>\n";
		echo "	<TH width=\"75px\">Auteur</TH>\n";
		echo "	<TH width=\"30px\">Mes.</TH>\n";;
		echo "	<TH width=\"165px\">Dernier Contrib</TH>\n";
	if (isset($_SESSION['id_joueur']))
	{
		if ($Joueur->acceslvl >= 3 OR (($Theme_Status == 3) && ($Joueur->ally_lvl >= 3)))
			echo "	<TH width=\"50px\">Admin</TH>\n";
	}
	echo "</td>\n";

	//Prend tous les sujets
	$sql = "SELECT * FROM forum_subjects WHERE theme_id = '".$Theme_Id."' ORDER BY subject_status DESC, subject_last_post_id DESC";
	
	if($Theme_Status == 3) {#Alliance
		$sql = "SELECT * FROM forum_subjects WHERE theme_id  = '11660".$Joueur->ally_id."' ORDER BY subject_status DESC, subject_last_post_id DESC";
	}#Alliance End

	//Compteur de sujet non-lus
	$Cpt_ToutNonLus = true;

	$req = sql_query($sql);

	$nbr = mysql_num_rows($req);
	if ($nbr == 0) {
		echo "</table>\n";
		echo "<span class=\"info\">Il n'y a pas encore de sujet sous ce thème!</span><br />\n";
	}
	else
	{//Il a des sujets
		while ($res = mysql_fetch_array($req))
		{
			//Variables
			$Subject_Id = $res['subject_id'];
			$Subject_Name = affiche($res['subject_title']);

			//Prend le pseudo du posteur
			@$Subject_Poster_ID = $res['subject_poster_id'];
			$Subject_Poster = $res['subject_poster'];
			$Subject_Title = affiche($res['subject_title']);
			@$Subject_Date = date($CONF['game_timeformat'], $res['subject_time']);
			$Subject_Replies = $res['subject_replies'];
			$Subject_Status = $res['subject_status'];
			$Subject_Locked = $res['subject_locked'];
			//$Subject_Last_Poster = $res['subject_last_poster'];
			//$Subject_Last_Date = $res['subject_last_date'];
			$Subject_Last_Post_Id = $res['subject_last_post_id'];
			echo "<tr>\n";

			//Verifie si il y a de nouveaux messages
			echo "	<td width=\"20px\">";
			if (isset($_SESSION['id_joueur']) AND ($Subject_Last_Post_Id != 0))
			{//Verifie le timing
				//On cherche le time du dernier message
				$Last_id = "SELECT message_time FROM forum_messages WHERE message_id = '".$Subject_Last_Post_Id."'";
				$Last_req = sql_query($Last_id);
				$Last_res = mysql_fetch_array($Last_req);
				$Last_id_Time = $Last_res['message_time'];
				/*
				//Prend notre dernière visite si il y a
				$Last_visit = "SELECT `time` FROM forum_visiting WHERE id_joueur = '".$_SESSION['id_joueur']."' AND id_subject = '".."'";
				$Last_visit_req = sql_query($Last_visit);
				if(sql_rows($Last_visist_req) == 1

				//Prend notre dernière visite
				if ($Theme_Status == 3) $Time_Theme_Id = 'ally';
				else $Time_Theme_Id = $Theme_Id;
				$Last_visit = "SELECT `".$Time_Theme_Id."` FROM `forum_last_visite` WHERE id_joueur = '".$_SESSION['id_joueur']."'";
				$Last_visit_req = sql_query($Last_visit);
				$Last_visit_res = mysql_fetch_array($Last_visit_req);*/

				$Last_Visiting = get_visiting($Subject_Id);

				if($Last_Visiting < $Last_id_Time) $Cpt_ToutNonLus = false;

				echo Forum_new($Last_Visiting,$Last_id_Time);
				//echo $Last_visit_res[$Theme_Id].'<br>'.$Last_id_Time;
			}
			else
			{//On affiche blanc
				echo Forum_new(1,0);
			}
			echo "	</td>\n\n";

			//Affiche le titre
			echo "	<td>";
				if ($Subject_Status == 2) { //Uppé
					echo "<img src=\"images/up.png\" title=\"Sujet uppé\">";
				}
				if ($Subject_Locked <> 0) {//Locké
					echo "<img src=\"images/lock.png\" title=\"Sujet blocké\">";
				}
				echo "<a href=\"index.php?p=topic2&id=".$Subject_Id."\">".$Subject_Title."</a>";
			echo "	</td>\n\n";

			//Affiche l'auteur
			echo "	<td>\n";
				echo "<a href=\"index.php?p=search2&joueurid=".$Subject_Poster_ID."\">".$Subject_Poster."</a>";
			echo "	</td>\n\n";

			//Affiche les réponses et les vus
			echo "	<td>";
				echo $Subject_Replies;
			echo "	</td>\n\n";

			//Affiche la dernière contribuation
			echo "	<td>";
			if ($Subject_Last_Post_Id != 0)
			{//On prend la dernière contribution
				$Contrib = "SELECT message_poster, message_poster_id, message_time FROM forum_messages WHERE message_id = '".$Subject_Last_Post_Id."'";
				$Contribr = sql_query($Contrib);
				$Con_Res = mysql_fetch_array($Contribr);

				echo "<a href=\"index.php?p=search2&joueurid=".$Con_Res['message_poster_id']."\">".$Con_Res['message_poster']."</a> ".date($CONF['game_timeformat'], $Con_Res['message_time']);

			}
			else
			{//Sinon, rien
				echo "&nbsp;";
			}
			echo "	</td>\n\n";
		
			//ADMIN
			if (isset($_SESSION['login']))
			{//
				if (($Joueur->acceslvl >= 3) OR (($Theme_Status == 3) && ($Joueur->ally_lvl >= 3)))
				{//On peut delete
					echo "	<td><a href=\"index.php?p=forummod2&delid=".$Subject_Id."\"><img src=\"images/icons/btn_delete.png\" alt=\"Supprimer\" /></a></td>\n\n";
				}
			}

			echo "</tr>\n";
		}//END WHILE
		echo "</table>\n";
		echo "<br>\n";
	}//END IF CANOT 

	//Si on a tout lu?
	if($Cpt_ToutNonLus)
	{//Update last_visited_theme machin
		if(isset($_SESSION['id_joueur'])) {//Update notre visite
			$up2 = "UPDATE forum_last_visite SET `".$Theme_Id."` = '".time()."' WHERE id_joueur = '".$_SESSION['id_joueur']."'";

			if ($Theme_Status == 3) {#Alliance
				$up2 = "UPDATE forum_last_visite SET `ally` = '".time()."' WHERE id_joueur = '".$_SESSION['id_joueur']."'";
			}
			sql_query($up2);
		}
	}

	//AJOUTER UN SUJET
	?>

	<TABLE class="newsmalltable">
	<TR>
		<TH>Ajouter un Sujet</TH>
	</TR>
	<TR>
		<TD>
		<FORM METHOD="POST" name="post" ACTION="index.php?p=forummod2&theme=<?php echo $Theme_Id; ?>">
		<center><?php 
		if(!isset($_SESSION['id_joueur'])) {//Pas connecté donc ->
		echo "	Pseudo: <INPUT TYPE=\"text\" NAME=\"postposter\" maxlength=\"20\"><br />\n";
		}

		include ('include/mise_en_forme.inc.php');
		bw_afficheToolbar("Poster", "", true);
		?>
		<INPUT TYPE="hidden" name="do" value="add">
		</FORM></center>
		</TD>
	</TR>
	</TABLE>
<?php
} else {
	bw_tableau_start("Erreur");
	echo bw_error("Vous n'avez pas accès à ce thème!");
}
bw_tableau_end();
?>