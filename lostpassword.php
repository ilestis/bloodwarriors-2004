<?php
/**LOST PASSWORD
/*Fait: Demande les variables du joueurs et, si correct,
/*      créé un nouveau password et l'envoit par email.
/*Date: 1er Aout 2005
**/
bw_tableau_start("Mot de passe perdu");


$StringTexte = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$Message = "Remplissez les champs si dessous et nous vous renveront un mot de passe par mail.";

if (isset($_POST['pass'])) 
{//Traite la demande
	//Valeurs
	$JPseudo	= clean($_POST['PlayerPseudo']);
	$JLogin		= clean($_POST['PlayerLogin']);
	$JPrenom	= clean($_POST['PlayerSurname']);
	$JNom		= clean($_POST['PlayerName']);

	//Verifie si le joueur existe
	$sql = "SELECT * FROM `joueurs` WHERE login = '".$JLogin."'";
	$req = sql_query($sql);
	$nbr = mysql_num_rows($req);
	if ($nbr == 0) {//Existe pas
		$Message = bw_error("Aucun Héros ne possède ces données. Verifier le contenu");
	} else {//Verifie les données
		$res = mysql_fetch_array($req);
		//echo $res['pseudo'].'-'.$res['nom'].'-'.$res['prenom'].'<br />';
		//echo $JPseudo.'-'.$JNom.'-'.$JPrenom.'<br />';
		if (($JPseudo == $res['pseudo']) AND ($JNom == $res['nom']) AND ($JPrenom == $res['prenom'])) {//Ok
			//Créé un nouveau mot de passe
			$NewPass = '';
			for ($x = 1; $x < 13; $x++) {//On fait un mot de passe de 12 lettres
				$Rand = mt_rand(1, strlen($StringTexte));
				$NewPass .= $StringTexte[$Rand];
				/*$Rand = mt_rand(1, 29);
				switch ($Rand) {
					case '1': $NewPass = $NewPass.'A'; break;
					case '2': $NewPass = $NewPass.'B'; break;
					case '3': $NewPass = $NewPass.'C'; break;
					case '4': $NewPass = $NewPass.'D'; break;
					case '5': $NewPass = $NewPass.'E'; break;
					case '6': $NewPass = $NewPass.'F'; break;
					case '7': $NewPass = $NewPass.'G'; break;
					case '8': $NewPass = $NewPass.'H'; break;
					case '9': $NewPass = $NewPass.'I'; break;
					case '10': $NewPass = $NewPass.'K'; break;
					case '11': $NewPass = $NewPass.'5'; break;
					case '12': $NewPass = $NewPass.'2'; break;
					case '13': $NewPass = $NewPass.'9'; break;
					case '14': $NewPass = $NewPass.'3'; break;
					case '15': $NewPass = $NewPass.'1'; break;
					case '16': $NewPass = $NewPass.'0'; break;
					case '17': $NewPass = $NewPass.'L'; break;
					case '18': $NewPass = $NewPass.'N'; break;
					case '19': $NewPass = $NewPass.'O'; break;
					case '20': $NewPass = $NewPass.'P'; break;
					case '21': $NewPass = $NewPass.'Q'; break;
					case '22': $NewPass = $NewPass.'R'; break;
					case '23': $NewPass = $NewPass.'S'; break;
					case '24': $NewPass = $NewPass.'U'; break;
					case '25': $NewPass = $NewPass.'V'; break;
					case '26': $NewPass = $NewPass.'W'; break;
					case '27': $NewPass = $NewPass.'X'; break;
					case '28': $NewPass = $NewPass.'Y'; break;
					case '29': $NewPass = $NewPass.'6'; break;
				}*/

				
			}
			require ('include/function_email.php');
			//Sauve les données
			$PasswordSafe = md5($NewPass);
			$sql = "UPDATE joueurs SET `password` = '".$PasswordSafe."' WHERE login = '".$JLogin."'";
			sql_query($sql);

			//echo $NewPass;

			//Envoit l'email
			$email_message = "Bonjour ".$res['pseudo'].".<br /><br />Notre base de données a enregistré votre demande d'envoit d'un nouveau mot de passe. Le voici:<br /><br />".$NewPass."<br /><br />Bon jeu sur Blood Warriors!<br /><br />~ Les Administrateurs."; //echo $email_message;
			send_email($res['email'], $email_message, "Blood Warriors: Nouveau mot de passe");
			$Message = "Votre nouveau mot de passe a été envoyé à votre adresse email";

		} else {//Pas les bonnes données
			$Message = bw_error("Les données ne correspondent pas");
		}
	}
}

bw_fieldset("Information", $Message);

echo "		<div style=\"margin: auto; width:300px;\">\n";
echo "		<FORM METHOD=\"POST\" ACTION=\"?p=lostpassword\">\n";
echo "		<table>\n";
echo "		<tr><td align=\"left\" width=\"100px\">Votre Pseudo :</td>\n";
echo "		<td><INPUT TYPE=\"text\" NAME=\"PlayerPseudo\" ".(isset($JPseudo) ? " value=\"".$JPseudo."\" style=\"background-color:".$CONF['errcol']."\"" : '')."/></td></tr>\n";
echo "		<tr><td align=\"left\">Votre Login :</td>\n";
echo "		<td><INPUT TYPE=\"text\" NAME=\"PlayerLogin\" ".(isset($JLogin) ? " value=\"".$JLogin."\" style=\"background-color:".$CONF['errcol']."\"" : '')."/></td></tr>\n";
echo "		<tr><td align=\"left\">Votre Nom :</td>\n";
echo "		<td><INPUT TYPE=\"text\" NAME=\"PlayerName\" ".(isset($JNom) ? " value=\"".$JNom."\" style=\"background-color:".$CONF['errcol']."\"" : '')."/></td></tr>\n";
echo "		<tr><td align=\"left\">Votre Prénom :</td>\n";
echo "		<td><INPUT TYPE=\"text\" NAME=\"PlayerSurname\" ".(isset($JPrenom) ? " value=\"".$JPrenom."\" style=\"background-color:".$CONF['errcol']."\"" : '')."/></td></tr>\n";
echo "		<tr>\n";
echo "			<td colspan=\"2\" align=\"center\">\n";
echo "				<INPUT TYPE=\"submit\" value=\"M'envoyer un nouveau mot de passe\" />\n";
echo "				<INPUT TYPE=\"hidden\" name=\"pass\" value=\"1530\" />\n";
echo "			</td>\n";
echo "		</tr>\n";
echo "		</table>\n";
echo "		</FORM>\n";
echo "		</div>\n";


bw_tableau_end();

?>