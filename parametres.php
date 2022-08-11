<?php
/*--------------------
|Nom: Les Param�tres
+---------------------
|Description: Permet de modifier nos donn�es
+---------------------
|Date de cr�ation: Ao�t 04
|Date du premier test: Ao�t 04
|Derni�re modification: 06 Fev 2006
+-------------------*/
//verifie si la session est en cours
require ('include/session_verif.php');

bw_tableau_start("Param�tres");

$Gestion = '';
if(isset($_GET['id'])) $Gestion = clean($_GET['id']);
switch($Gestion)
{
	case 'mdp':
		$sql = "SELECT `password` FROM joueurs WHERE `id` = '".$_SESSION['id_joueur']."'";
		$req = sql_query($sql);
		$res = mysql_fetch_array($req);
		if ((md5(clean($_POST['oldpass'])) == $res['password']) && (clean($_POST['newpass']) == clean($_POST['newpass2'])))
		{
			$up = "UPDATE joueurs SET `password` = '".md5(clean($_POST['newpass']))."' where `id` = '".$_SESSION['id_joueur']."'";
			sql_query($up);
			echo 'Nouveau mot de passe bien mis en place.<br/>';
		}
		elseif (clean($_POST['oldpass']) != $res['mdp']) echo 'Votre vieux mot de passe ne correspant pas � celui que vous avez ins�r�.<br/>';
		elseif (clean($_POST['newpass']) != clean($_POST['newpass2'])) echo 'La confirmation du nouveau mot de passe est erron�e.<br/>';
		break;
	
	case 'lang':
		//changement de langague
		$sql = "UPDATE joueurs SET lang = '".clean($_POST['lang1'])."' WHERE id = '".$_SESSION['id_joueur']."'";
		sql_query($sql);
		break;

	case 'theme':
		$sql = "UPDATE joueurs SET theme = '".clean($_POST['theme'])."' WHERE id = '".$_SESSION['id_joueur']."'";
		sql_query($sql) ;
		echo 'Nouveau th�me bien pris en compte.<br/>';
		break;

	case 'avatar':
		//on r�cup�re l'image envoy�e
		$fichier_image = $HTTP_POST_FILES['fichier_image']['tmp_name'];

		//on r�cup�re le nom du fichier envoy�
		$nom_image = $_FILES['fichier_image']['name'];	
	
		//largeur et hauteur
		$taille = @getimagesize($fichier_image);
		$largeur = $taille[0];
		$hauteur = $taille[1];

		//on recupere la taille du fichier envoy� et on l'arrondi � 2 chiffres apres la virgule
		$taille_image = round($_FILES['fichier_image']['size']/1024, 2);
	
		if ($taille_image <= $CONF['img_taille'] && $largeur <= $CONF['img_width'] && $hauteur <= $CONF['img_height'])
		{
			//on defini l'extension
			$extension = substr($nom_image,-4);

			//on d�sactive l'html,les espaces blancs inutiles
			$nom_image = htmlentities(trim(addslashes($nom_image)));
			$nom_image = time();


			//on indique le r�pertoire ou sera enregistr�e l'image
			$destination = "images/avatars/".$_SESSION['id_joueur']."_".$nom_image;
		
			//on regarde si le fichier est une image
			if ($extension == ".gif" || $extension == ".jpg" || $extension == ".png" || $extension == ".JPG" || $extension == ".PNG" || $extension == ".GIF")
			{
				//on copie le fichier
				copy($fichier_image, $destination);	
				$sql = "UPDATE joueurs SET avatar = '".$nom_image."' WHERE id = '".$_SESSION['id_joueur']."'";
				$req = sql_query($sql);
				echo 'Votre <img src="'.$destination.'"> a bien �t� envoy� sur le serveur.<br>';
			}
			else
			{
				echo "<span class=\"avert\">Seul les fichiers de type png, jpg et gif sont accep�s!<br/></span>";
			}
		}
		else
		{
			echo "Image trop volumineuse : ".$CONF['img_taille']." ko maximum autoris� par image.<br/>La taille maximum autoris�e est de ".$CONF['img_width']."x".$CONF['img_height']."(largeur/hauteur) pixels. Votre image fait ".$largeur."*".$hauteur." pixels.<br /><br />";
		}
		break;

	//partire en vacances
	case 'vacs':
		$Vacances = time()+(clean($_POST['vacancesdurees'])*24*3600);
		$newaut = substr($_SESSION['aut'], 0, 11).'1'.substr($_SESSION['aut'], 12);
		$sql = "UPDATE joueurs SET `vacances` = '".$Vacances."', `aut` = '".$newaut."' WHERE id = '".$_SESSION['id_joueur']."'";
		sql_query($sql) ;
		echo 'Bonne vacances! ;)<br/>Votre compte sera � nouveau accessible le  '.date($CONF['game_timeformat'], $Vacances).".<br />\n";
		session_destroy();
		bw_tableau_end();
		breakpage();
		die();

		break;
}

//pas d�faut

//select avatar actuelle
$avatar = $Joueur->avatar;
?>
	
		<form method="post" action="index.php?p=para&id=mdp">
		<fieldset>
			<legend align="top">Mot de passe</legend>
			<label for="oldpass">Ancien mot de passe:</label>	<label><input type="password" id="oldpass" NAME="oldpass" /></label><br style="clear:both;"/>
			<label for="newpass">Nouveau mot de passe:</label>	<label><input type="password" id ="newpass" NAME="newpass" /></label><br style="clear:both;"/>
			<label for="newpass2">Retappez votre Nouveau mot de passe:</label>	<label><input type="password" id="newpass2" NAME="newpass2" /> <input type="submit" value="Changer"></label><br style="clear:both;"/>
		</fieldset>
		</form><br />

		<form method="post" action="index.php?p=para&id=theme">
		<fieldset>
			<legend align="top">Th�me Graphique</legend>
			<label>Changer de th�me</label>
			
			<label><select name="theme">
				<option value="8">Sunset</option>
				<option value="9">Vieux</option>
			</select> <input type="submit" value="Changer"></label><br style="clear:both;"/>
		</fieldset>
		</form><br />
		
		<form method="post" action="index.php?p=para&id=avatar" ENCTYPE="multipart/form-data">
		<fieldset>
			<legend align="top">Avatar</legend>
			<label>Avatar actuel: <br />
			<?php if($Joueur->avatar == '') echo "Aucun avatar!<br />\n";
				else echo "<img src=\"images/avatars/".$_SESSION['id_joueur']."_".$avatar."\"><br />\n"; ?>
			</label>
			
			<label>Envoyer un nouvel avatar:<br /><INPUT TYPE="file" name="fichier_image" enctype="multipart/form-data"><br />
			<INPUT TYPE="submit" value="Envoyer mon nouveal avatar">
			</label><br style="clear:both;" />
		</div>
		<em>Dimensions max: <?php echo $CONF['img_width']."x".$CONF['img_height']; ?>(L/H) pixels pour <?php echo $CONF['img_taille']; ?>Ko</em>
		</fieldset>
		</form><br />
		
		<form method="post" action="index.php?p=para&id=vacs">
		<fieldset>
			<legend align="top">Partir en vacances</legend>
			<label>Dur�e en jours:</label>
			<label><select name="vacancesdurees">
			<?php for ($x = $CONF['game_min_holiday']; $x <= $CONF['game_max_holiday']; $x++)
				{
					echo "<option value=\"".$x."\">".$x."</option>";
				}
			?>
				
			</select> <input type="submit" value="@+ Blood Warriors!"></label><br style="clear:both;"/>
			<br /><em>Pendant que vous �tes en vacances, vos guerres en cours seront termin�es, mais vous ne pourrez plus �tre attaqu� avant votre retour. Aussi, vous ne pourrez plus vous connecter avant votre date de retour!</em><br />
		</fieldset>
		</form><br />
	</td>
</tr><tr>
	<td class="newfin">&nbsp;</td>
</tr>
</table>	
<br />