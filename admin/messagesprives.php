<?php
/*----------------------[TABLEAU]---------------------
|Nom:			MessagesPrivés.Php
+-----------------------------------------------------
|Description:	Permet de voir / envoyer des messages privés sous le compte ADMIN
+-----------------------------------------------------
|Date de création:				07/05/04
|Dernière modification[Auteur]: 10/02/06[Escape]
+---------------------------------------------------*/

include ('admin/adminheader.php');

if($_SESSION['aut'][$adminpage['messagesprives']] == 0)  breakpage();

echo "	<table class=\"newtable\"><tr>\n";

if($_GET['go'] == 'select')
{//permet de voire les messages privés
	echo "		<td class=\"newtitre\">Les Messages Privés</td>\n";
	echo "	</tr><tr>\n";
	echo "		<td class=\"newcontenu\">\n";

	echo "<table class=\"newsmalltable\">\n";
	echo "<form name=\"form1\" method=\"post\" action=\"index.php?p=admin_messagesprives&go=select\">\n";
	
	echo "<tr>\n";
	echo "	<th>\n";
		echo "	Choisissez un joueur: <select name=\"idjoueur\">\n";
		$sql = "SELECT `id`, `pseudo` FROM joueurs ORDER BY pseudo ASC" ;
		$result = sql_query($sql);
		while($res = mysql_fetch_array($result))
		{
			echo "<option value=\"".$res['id']."\">".$res['pseudo']."</option>\n";
		}
		echo "	</select>\n";
		echo "	<input type=\"submit\" name=\"Submit\" value=\"Rechercher\">\n";
	echo "	</th>\n";
	echo "</tr>\n";

	echo "</form>\n";
	echo "</table><br />\n";

	if(isset($_POST['idjoueur']))
	{//on affiche les messages
		//On va chercher son pseudo au petit...
		$res = mysql_fetch_array(sql_query("SELECT pseudo FROM joueurs WHERE id = '".clean($_POST['idjoueur'])."'"));

		echo "Voici les messages privés de ".$res['pseudo'].".<br />\n";
		echo "<table class=\"newsmalltable\">\n";
		$req = "SELECT * FROM `messages` WHERE `id_to` = '".clean($_POST['idjoueur'])."' ORDER by numero_message_prive DESC" ;
		$result = sql_query($req);
		while($res = mysql_fetch_array($result))
		{//prend chaque message
			echo "<tr>\n";

			//Cherche le pseudo de l'envoyeur
			$reqp = sql_query("SELECT pseudo FROM joueurs WHERE id = '".$res['from']."'");
			if(mysql_num_rows($reqp) == 0)
			{
				switch($res['from'])
				{
					case '999999991':
						$Pseudo_Aff = 'Centre de combat';
						break;
					case '999999992':
						$Pseudo_Aff = 'Rapport de guerre';
						break;	
					case '999999993':
						$Pseudo_Aff = 'Administration';
						break;
					default:
						$Pseudo_Aff = 'Introuvable';
				}
			} else {
				$resp = mysql_fetch_array($reqp);
				$Pseudo_Aff = $resp['pseudo'];
			}
			

			//Affichage
			echo "<th>De: ".$Pseudo_Aff." || Le ".date($CONF['game_timeformat'], $res['time'])."</th>\n";
			echo "</tr>\n <tr>\n";
			echo "<td>".affiche($res['message'])."</td>\n";
			echo "</td>\n";
		}
		echo "</table>\n";
	}
}
elseif($_GET['go'] == 'sendall')
{//envoyer un message collectife
	echo "		<td class=\"newtitre\">Message Collectif</td>\n";
	echo "	</tr><tr>\n";
	echo "		<td class=\"newcontenu\">\n";

	if(isset($_POST['message']))
	{//on demande d'envoyer
		//mise en form
		$comment = forummessage($_POST['message']);

		$echo = "Le message à été envoyé à aux joueurs:\n";
		//insertion dans la base de donnée pour chaque pseudo
		$sql = "select `pseudo`, `id` from joueurs order by pseudo ASC" ;
		$result = mysql_query($sql);
		while($res = mysql_fetch_array($result))
		{
			send_message(999999993, $res['id'], $comment, 1);
			$echo .= $res['pseudo'].", ";
		}

		echo $echo;
	}

	echo "<table class=\"newsmalltable\"\n";
	echo "<form name=\"form1\" method=\"post\" action=\"index.php?p=admin_messagesprives&go=sendall\">\n";
	
	echo "<tr>\n";
	echo "	<th>\n";
		echo "	<TEXTAREA NAME=\"message\" ROWS=\"7\" COLS=\"60\"></TEXTAREA><br />\n";
		echo "	<input type=\"submit\" name=\"Submit\" value=\"Envoyer\">\n";
	echo "	</th>\n";
	echo "</tr>\n";

	echo "</form>\n";
	echo "</table><br />\n";
}
echo "		</td>\n";
echo "	</tr><tr>\n";
echo "		<td class=\"newfin\">&nbsp;</td>\n";
echo "	</tr><tr>\n";
echo "</table>\n";
echo "</form>\n";

echo "<a href=\"?p=admin_admin\">Retour à l'administration</a><br />\n";
?>