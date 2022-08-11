<?
/*
+---------------------
|Nom: L'index du forum
+---------------------
|Description: l'index du forum général
+---------------------
|Date de création: Août 04
|Date du premier test: Août 04
|Dernière modification: 15 Aout 05 [V.1]
+---------------------
*/

bw_tableau_start("Forum");

$Text = "Ici vous êtes sur un forum! Pas sur votre téléphone portable! Alors vous écrivez correctement, de manière à ce que l'on vous comprenne! Sinon, les modos et admins supprimerons vos postes et topics!";
bw_f_info("Information", $Text);

echo "<br />\n";

?>
<TABLE class="newsmalltable">
<TR>
	<TH width="370px">Theme</TH>
	<TH width="50px"><a href="#" title="Topics">T</a> / <a href="#" title="Messages">M</a></TH>
	<TH width="200px">Dernier Contribuation</TH>
</TR>

<?php
	//selectionne les threads avec un while
	$mysql = "SELECT * FROM `forum_themes` ORDER BY `theme_order`";
	$request = sql_query($mysql);
	while ($theme = mysql_fetch_array($request))
	{
		//Variables
		$Theme_Id = $theme['theme_id'];
		$Theme_Name = affiche($theme['theme_name']);
		$Theme_Description = affiche($theme['theme_description']);
		$Theme_Status = $theme['theme_status'];
		$Theme_Posts =$theme['theme_posts'];
		$Theme_Topics = $theme['theme_topics'];
		$Theme_Last_Post_Id = $theme['theme_last_post_id'];

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
		{//Verifie groupe
			If (isset($_SESSION['id_joueur']))
			{//Ok
				//Prend les groupes...
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
			//On cherche si on a des messages non plus
			//$sql1 = "SELECT forum_visiting.id FROM forum_visiting, forum_messages WHERE forum_messages.

			echo "<tr>\n";
			echo "	<td>";
			If (isset($_SESSION['id_joueur']) AND ($Theme_Last_Post_Id != 0))
			{//Connecté donc on verifie si nouveaux messages
				
				$Last_id = "SELECT message_time FROM forum_messages WHERE message_id = '".$Theme_Last_Post_Id."'";
				$Last_req = sql_query($Last_id);
				$Last_res = mysql_fetch_array($Last_req);
				$Last_id_Time = $Last_res['message_time'];

				//Prend notre dernière visite
				$Last_visit = "SELECT `".$Theme_Id."` FROM `forum_last_visite` WHERE id_joueur = '".$_SESSION['id_joueur']."'";
				$Last_visit_req = sql_query($Last_visit);
				$Last_visit_res = mysql_fetch_array($Last_visit_req);

				echo Forum_new($Last_visit_res[$Theme_Id],$Last_id_Time); 
			}
			else
			{//On peut pas voir si nouveau message
				echo Forum_new(1,0);
			}

			//Affiche le titre et la descriptions
			echo "<a href=\"index.php?p=forum2&theme=".$Theme_Id."\">".$Theme_Name."</a> :: <br />\n";
			echo $Theme_Description."\n";
			echo "</td>\n\n";

			//Affiche les topics et les réponses
			echo "	<td>";
			echo $Theme_Topics." / ".$Theme_Posts."";
			echo "</td>\n\n";

			//Dernière contrib
			echo "	<td>";
			if ($Theme_Last_Post_Id != 0)
			{//On prend la dernière contribution
				$Contrib = "SELECT subject_title, subject_id FROM forum_subjects WHERE subject_last_post_id = '".$Theme_Last_Post_Id."'";
				$Contribr = sql_query($Contrib);
				$Con_Res = mysql_fetch_array($Contribr);
				$Sujet = $Con_Res['subject_title'];
				$Subject_Id = $Con_Res['subject_id'];

				$Sql2 = "SELECT message_poster, message_poster_id, message_time FROM forum_messages WHERE message_id = '".$Theme_Last_Post_Id."'";
				$Req2 = sql_query($Sql2);
				$Res2 = mysql_fetch_array($Req2);
				echo "\n	<a href=\"index.php?p=search2&joueurid=".$Res2['message_poster_id']."\">".$Res2['message_poster']."</a> dans <a href=\"index.php?p=topic2&id=".$Subject_Id."\">".$Sujet."</a><br /> ".date($CONF['game_timeformat'], $Res2['message_time'])."\n";
			}
			else
			{//Sinon, rien
				echo "&nbsp;";
			}
			echo "	</td>\n";
			echo "</tr>\n\n";
		}
	}//While
echo "		</table>\n";

echo "		<img src=\"images/forum_new.png\" alt=\"Nouveau\">::Nouvelle(s) Réponse(s) ~~ ";
echo "		<img src=\"images/forum_not_new.png\" alt=\"Pas de nouveau\">::Pas De Nouvelle(s) Réponse(s)\n";

bw_tableau_end();
?>
