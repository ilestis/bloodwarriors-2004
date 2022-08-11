<?php
/*
+---------------------
|Nom: Gestion de la messagerie
+---------------------
|Description: Permet de supprimer/repondre/envoyer des messages privés
+---------------------
|Date de création: 16 Août 04
|Date du premier test: 16 Août 04
|Dernière modification: 10 Fev 06
+---------------------
|Document à terminer
+---------------------
*/

//verifie l'état de la session
require ('include/session_verif.php');

//langue
$lang_text = lang_include($Joueur->lang,'lang_messagerie');
$information = '';


//Code
$id = '';
if (isset($_POST['id'])) $id = clean($_POST['id']);
if (isset($_GET['id'])) $id = clean($_GET['id']);




switch($id)
{
	case 'send':
		if(empty($_POST['commentaire']) || (empty($_POST['reciver']) || !is_numeric($_POST['reciver']))) $information =  $lang_text['infom1'].'<br/>';
		else 
		{
			//Variables
			$To = clean($_POST['reciver']);
			$Message = forummessage(forumadd($_POST['commentaire']));

			send_message($_SESSION['id_joueur'], $To, $Message, 0);
			
			$information = "<span class=\"info\">".$lang_text['infom2'].joueur_name($To)."</span><br />\n";
		}
		include 'messagerie.php';
		break;

//******************************************************
	case 'del':
		//Verifie si le message nous appartient
		$Id = clean($_GET['num']);
		$sql = "SELECT `id_to` FROM `messages` WHERE id_message = '".$Id."' AND location = 'mes'";
		$req = sql_query($sql);
		$res = mysql_fetch_array($req);

		if ($res['id_to'] == $_SESSION['id_joueur']) {		//Ok
			$Del = "DELETE FROM `messages` WHERE id_message = '".$Id."'";
			$information = $lang_text['del_1'].".<br />\n";
			sql_query($Del);
		} else {								//Alerte
			$information = "<span class=\"avert\">".$lang_text['error_del_1']."!</span><br />\n";
		}
	
		include 'messagerie.php';

		break;

//******************************************************
	case 'delselection':
		//Supprime les elements cochés
		foreach($_POST as $Element => $Valeur)
		{//Prend chaque element
			if($Element['checked'] == true)
			{//Verifie s'il est sélectionné
				$Id_Mes = clean($_POST[$Element]);

				//Verifie si le message existe et nous appartiend
				$Sql = "SELECT id_message FROM messages WHERE id_to = '".$_SESSION['id_joueur']."' AND id_message = '".$Id_Mes."' AND location = 'mes'";
				$Req = sql_query($Sql);
				$Res = sql_rows($Req);


				if($Res == 1)
				{//Le message a été trouvé, on le supprime
					$Del = "DELETE FROM messages WHERE id_message = '".$Id_Mes."'";
					sql_query($Del);
					$information .=  $lang_text['del_1'].".<br />\n";
				} 
				else { //Message pas trouvé
					$information .=  bw_info($lang_text['error_del_1'])."!<br />\n";
				}
			}
		}

		include 'messagerie.php';
		break;

//******************************************************
	case 'rep':
		$num = (isset($_GET['num']) && is_numeric($_GET['num']) ? clean($_GET['num']) : '');

		include ('profil.php');

		$sql = "SELECT * FROM `messages` WHERE id_message = '".$num."'" ;
		$req = sql_query($sql);
		$res = mysql_fetch_array($req);

		if ($res['id_to'] == $_SESSION['id_joueur'])
		{//si le message nous est destiné
			//$lang_text = lang_include($Joueur->lang,'lang_messagerie');

			bw_tableau_start("Répondre à ".joueur_name($res['id_from']));

			?>
			
			<FORM METHOD="POST" ACTION="index.php?p=messmod&id=rep2&num=<?php echo $num; ?>">
			<?php
			include ('include/mise_en_forme.inc.php');
			
			//Traite le message
			$Message_Envoit = forummessage(clean($_POST['comment']));
			$Message_Envoit .= "\r\n\r\n\r\n[ligne][b]Message de ".joueur_name($res['id_from'])."[/b], ".date($CONF['game_timeformat'], $res['time'])."\r\n";
			$Message_Envoit .= "".reversemessage($res['message'])."\r\n";

			bw_afficheToolbar("Envoyer", $Message_Envoit);
			?>
			</FORM>
			
			<table class="newsmalltable"><tr><th>Vieu message:</th></tr>
			<tr><td>
			<?php echo affiche($res['message']); ?>
			</td></tr></table>
	 	<?php
			bw_tableau_end();
		} else {
			//Pas bon
			echo bw_error("Ce message ne vous appartiend pas!");
		}
		break;

	case 'rep2':

		//verifie si nous somme le proprio
		$Num = (isset($_GET['num']) && is_numeric($_GET['num']) ? clean($_GET['num']) : '');

		$sql = "select * from `messages` where id_message = '".$Num."'" ;
		$req = sql_query($sql);
		$res = mysql_fetch_array($req);
		if ($res['id_to'] == $_SESSION['id_joueur'] && !empty($_POST['commentaire']))
		{
			$Message_Envoit = forummessage(forumadd($_POST['commentaire']));
			send_message($_SESSION['id_joueur'], $res['id_from'], $Message_Envoit, 0);

			
			$information  = "<span class=\"info\">".$lang_text['infom2'].joueur_name($res['id_from'])."</span><br />\n";
		}
		else { $Message =  "erreur.<br />\n"; }
		include 'messagerie.php';
		break;

	case 'delall':
		//supprime tous nos message
		$del = "DELETE FROM messages WHERE `id_to` = '".$_SESSION['id_joueur']."' AND location = 'mes'";
		sql_query($del);

		redirection("?p=mess", 500);
		break;

	default:
		echo "?";
}