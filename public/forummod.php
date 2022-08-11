<?php
/*----------------------[TABLEAU]---------------------
|Nom:			ForumMod.php
+-----------------------------------------------------
|Description:	Gestion sur les thèmes des forums
+-----------------------------------------------------
|Date de création:				17/06/05
|Dernière modification[Auteur]: jj/mm/aa[Pseudo]
+---------------------------------------------------*/

if (isset($_POST['do']))
{//on ajoute
	//Verifie si il y a titre, pseudo et contenu	
	If (!isset($_POST['commentaire']) OR !isset($_POST['title'])) {
		echo "<H2>Votre sujet doit avoir un contenu ainsi qu'un titre</H2>\n";
	}
	//Verifie le pseudo
	elseif(!isset($_SESSION['id_joueur']))
	{
		if (!isset($_POST['postposter']))
		{
			$Pseudo = 'Annonyme'.mt_rand(65, 744);
			$SessionId = 0;
		}
		else
		{//Y'a un pseudo, vérifie si le pseudo n'existe pas déjà
			$sql = "SELECT pseudo FROM joueurs WHERE pseudo = '".$_POST['postposter']."'";
			$req = sql_query($sql);
			$nbr = mysql_num_rows($req);
			if ($nbr == 1) 
			{
				echo "Quelqun utilise déjà ce pseudo!<br />";
				$Pseudo = 'Annonyme'.mt_rand(65, 744);
				$SessionId = 0;
			}
			else
			{//Ok
				$Pseudo = clean($_POST['postposter']);
				$SessionId = 0;
			}
		}
	}	
	else
	{	
		$Pseudo = $Joueur->pseudo; 
		$SessionId = $_SESSION['id_joueur'];
	}

	//Verifie si le thème existe
	$sql = "SELECT theme_id, theme_status FROM forum_themes WHERE theme_id = '".clean($_GET['theme'])."'";
	$req = sql_query($sql);
	$Verification = mysql_num_rows($req);
	If ($Verification == 0) 
	{//Le theme n'existe pas!
		echo "<H2>Le thème n'existe pas!</H2><br />\n"; 
		exit;
	}
	
	//Verifie si on a le droit de créer
	$res = mysql_fetch_array($req);
	//Variables
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
	{//Verifie alliance
		If (isset($_SESSION['id_joueur']))
		{//Connecté
			if ($Joueur->ally_id != 0) {#Ok
				$CanView = TRUE;
				$ZeTheme_Id = "11660".$Joueur->ally_id;
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

	if (!isset($ZeTheme_Id)) $ZeTheme_Id = $Theme_Id;

	if ($CanView == TRUE)
		{//On ajoute
			//Variables
			$Pseudo = $Pseudo;
			$Contenu = clean($_POST['commentaire']);
			$Contenu = forummessage($Contenu);
			$Titre = clean($_POST['title']);
			$Time = time();
			$Date = "Le ".date("d/m/Y").' à '.date("G:i");

			//Insert dans le forum_subjects
			$sql = "INSERT INTO forum_subjects VALUES('', '".$ZeTheme_Id."', '".$Titre."', '".$Pseudo."', '".$SessionId."', '".$Time."', '0', '1', '1', '0', '0', '".$Time."')";
			sql_query($sql);

			//Prend l'id du sujet qu'on vient de créer
			$Subject_Id = mysql_insert_id();

			$sql = "INSERT INTO forum_messages VALUES('', '".$Subject_Id."', '".$Pseudo."', '".$SessionId."', '".$Contenu."', '".$Time."', '', '0')";
			sql_query($sql);
			$Message_Id = mysql_insert_id();
			$debug .= $sql.'<br />';

			$up = "UPDATE forum_subjects SET subject_last_post_id = '".$Message_Id."' WHERE subject_id = '".$Subject_Id."'";
			sql_query($up);
			$debug .= $up.'<br />';

			$up2 = "UPDATE forum_themes SET theme_last_post_id = '".$Message_Id."' WHERE theme_id = '".$Theme_Id."'";
			sql_query($up2);
			$debug .= $up2.'<br />';

			//Ajoute +1 topic +1 message
			$up3 = "UPDATE forum_themes SET theme_topics = (theme_topics+1), theme_posts = (theme_posts+1) WHERE theme_id = '".$Theme_Id."'";
			sql_query($up3);

			//Redirige sur notre nouveau post!
			redirection("index.php?p=topic2&id=".$Subject_Id,10);

		}
}
if (isset($_GET['delid']))
{//On veut supprimer
	



	//Verifie si le sujet existe
	$sql = "SELECT subject_id, theme_id FROM forum_subjects WHERE subject_id = '".clean($_GET['delid'])."'";
	$req = sql_query($sql);
	$nbr = mysql_num_rows($req);

	if ($nbr == 0)
	{
		echo "Erreur, ce sujet n'existe pas!";

	}
	else
	{//Existe

		$res = mysql_fetch_array($req);

		//Verifie si on peut supprimer
		$sql_2 = "SELECT theme_id, theme_status FROM forum_themes WHERE theme_id = '".$res['theme_id']."'";
		$req_2 = sql_query($sql_2);
		$Theme_Status = $res_2['theme_status'];
		$CanDel = false;
		If ($Theme_Status == 3)
		{//Verifie alliance
			If ($Joueur->ally_lvl >= 4)
			{//Connecté
				$CanDel = TRUE;
			}
		}
		else
		{//S'enfou du reste, doit être admin
			If($Joueur->acceslvl >= 3)
			{//On a acces
				$CanDel = TRUE;
			}
		}


		$Id = $res['subject_id'];
		$Theme_Id = $res['theme_id'];
		//Verifie notre niveau
		if ($CanDel)
		{//On peut delete
			$del = "DELETE FROM forum_subjects WHERE subject_id = '".$Id."'";
			sql_query($del);

			$del2 = "DELETE FROM forum_messages WHERE subject_id = '".$Id."'";
			sql_query($del2);

			//Compte les posts
			//Prend les sujets
			$nbr = 0;
			$Lastpostid = 0;
			$sql = "SELECT subject_id FROM forum_subjects WHERE theme_id = '".$Theme_Id."'";
			$req = sql_query($sql);
			while ($res = mysql_fetch_array($req))
			{
				$nr = "SELECT message_id FROM forum_messages WHERE subject_id = '".$res['subject_id']."'";
				$re = sql_query($nr);
				$nbr += mysql_num_rows($re);
			}
			
			//Selectionne le last post id
			$sql = "SELECT subject_id FROM forum_subjects WHERE theme_id = '".$Theme_Id."' ORDER BY subject_last_post_id DESC LIMIT 0, 1";
			$req = sql_query($sql);
			$res = mysql_fetch_array($req);

			//Update posts et topics
			$delsub = "UPDATE forum_themes SET theme_topics = (theme_topics-1), theme_posts = '".$Nbr."', theme_last_post_id = '".$res['subject_last_post_id']."' WHERE theme_id = '".$Theme_Id."'";
			sql_query($delsub);

			//Redirection
			redirection("index.php?p=forum2&theme=".$Theme_Id,10);		
		}
	}
}


if (isset($_GET['do']) && $_GET['do'] == 'up')
{//On verifie si on a les droits
	if ($Joueur->acceslvl >= 3) 
	{//OK

		//Verifie si le theme existe
		$sql = "SELECT subject_status FROM forum_subjects WHERE subject_id = '".$_GET['subject_id']."'";
		$req = sql_query($sql);
		$nbr = mysql_num_rows($req);
		if ($nbr == 1) {//Ok¨
			$res = mysql_fetch_array($req);
			if ($res['subject_status'] == 2) {//On dé-up
				$up = "UPDATE forum_subjects SET subject_status = '1' WHERE subject_id = '".$_GET['subject_id']."'";
			}
			else {//On up
				$up = "UPDATE forum_subjects SET subject_status = '2' WHERE subject_id = '".$_GET['subject_id']."'";
			}
			sql_query($up);

		}
		else
		{//Existe pas
			echo "Ce sujet n'existe pas!<br />\n";
		}
	
	//Redirection
	redirection("index.php?p=topic2&id=".$_GET['subject_id'],10);	
	}
}

if (isset($_GET['do']) && ($_GET['do'] == 'lock'))
{//On verifie si on a les droits
	if ($Joueur->acceslvl >= 3) 
	{//OK

		//Verifie si le theme existe
		$sql = "SELECT subject_locked FROM forum_subjects WHERE subject_id = '".$_GET['subject_id']."'";
		$req = sql_query($sql);
		$nbr = mysql_num_rows($req);
		if ($nbr == 1) {//Ok¨
			$res = mysql_fetch_array($req);
			if ($res['subject_locked'] == 1) {//On dé-lock
				$up = "UPDATE forum_subjects SET subject_locked = '0' WHERE subject_id = '".$_GET['subject_id']."'";
			}
			else {//On lock
				$up = "UPDATE forum_subjects SET subject_locked = '1' WHERE subject_id = '".$_GET['subject_id']."'";
			}
			sql_query($up);

		}
		else
		{//Existe pas
			echo "Ce sujet n'existe pas!<br />\n";
		}
	
		//Redirection
		redirection("index.php?p=topic2&id=".$_GET['subject_id'],10);	
	}
}
?>