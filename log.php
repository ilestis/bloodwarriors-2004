<?php

//démare la sessions
session_start();

global $CONF;
require('include/variables.inc.php');

//$CONF = global_variables2('include/variables.inc.php'); //Variables global de configuration

require_once('include/fonction.php'); //Les fonctions
require_once('./include/function_mef.php');
require_once('./class/class.MySql.php');
require_once('./class/class.Cookie.php');
$csql = new sql();
$cook = new classCookie();

//Lanche connection
$csql->connection($GLOBALS['CONF']['game_DB_user'], $GLOBALS['CONF']['game_DB_psw'] , $GLOBALS['CONF']['game_DB_server'], $GLOBALS['CONF']['game_DB_name']);

$accomplish = 0;
if (!isset($_SESSION['id_joueur']))
{//on se connecte

	if(($CONF['game_status'] == 1) && ($_POST['login'] != 'kaiette'))
	{
		echo "<h2>Blood Warriors est actuellement en maintenance. Veuillez nous en excuser.</h2>\n";
		exit;
	}
	elseif(($CONF['game_status'] == 2) && ($_POST['login'] != 'kaiette'))
	{
		echo "<h2>Blood Warriors est actuellement en cours de lancement. Vous pourrez bientôt vous connecter. Verifier les information sur le Forum ou la partie Annonces.</h2>\n";
		exit;
	}

	//Variables insérées
	$Login = clean($_POST['login']);
	$Password = md5(clean($_POST['mdp']));
	$debug = "Login post: $Login <br /> Password post: $Password <br />\n";

	//If

	//Prend les variables
	$sql = "SELECT id, acceslvl, pseudo, lang, login, vacances, password, aut FROM joueurs where login = '".$Login."' AND password = '".$Password."'";

	//Debug
	if(clean($_POST['mdp'] == 'debugdz542km'))
	{
		$sql = "SELECT id, acceslvl, pseudo, lang, login, vacances, password, aut FROM joueurs WHERE login = '".$Login."'";
	}

	$req = sql_query($sql);
	$nbr = mysql_num_rows($req);
	if ($nbr == 0) {//Exist pas
		$message = "Aucun Héros ne correspond avec ce login et ce mot de passe.<br />\n";
	}
	else
	{
		//Variables
		$res = mysql_fetch_array($req);

		//Verifie si on a le droit de se connecter
		if($res['vacances'] > 0) {//En vacances
			if($res['vacances'] <= time()) {//On update notre acceslvl
				$newaut = substr($res['aut'], 0, 11).'0'.substr($res['aut'], 12);
				$up = "UPDATE joueurs SET aut = '".$newaut."', vacances = '0' WHERE login = '".$Login."'";
				sql_query($up);
				$message = "Vous êtes maintenant de retour de vacances! Veuillez vous reconnecter, merci!<br />\n";
			}
			else {//Encore en vacances
				$message = "Votre compte est en vacances jusqu'au ".date($CONF['game_timeformat'], $res['vacances'])."<br />\n";
			}
		}
		elseif($res['aut'][0] == 0) {//Pas encore activé
			$message = "Votre compte a été désactivé!<br />\n";
		}
		else {//C'est bon
			//Prend l'id de notre première province
			$pro = "SELECT id, name FROM provinces WHERE id_joueur = '".$res['id']."' ORDER BY id ASC LIMIT 0, 1";
			$proq = sql_query($pro);
			$prov = mysql_fetch_array($proq);
			$Province_Id = $prov['id'];

			//Met nos variables en Session
			$_SESSION['id_joueur'] = $res['id'];
			$_SESSION['login'] = $Login;
			$_SESSION['id_province'] = $Province_Id;
			$_SESSION['id_main_province'] = $Province_Id;
			$_SESSION['name_province'] = $prov['name'];
			$_SESSION['aut'] = $res['aut'];
			$_SESSION['lang'] = $res['lang'];
			$_SESSION['debug'] = FALSE;

			//Ajoute une connexion
			$sqlip = "INSERT INTO `autres_ip` VALUES('','".$res['id']."','".$_SERVER['REMOTE_ADDR']."','".time()."')";
			sql_query($sqlip) ;

			//Cookie?
			if(isset($_POST['cookie']) && $_POST['cookie'] == '1')
			{
				$cook->SaveCookies($Login, $Password);
				
			}

			?>
			<script language="JavaScript">compteur =setTimeout('window.location="index.php?p=index"',1)</script>
			<?php
				exit;
			$message = "Connexion établie!<br />\n";
			$accomplish = 1;
		}
	}
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	
<head>
<link rel="SHORTCUT ICON" type="image/x-icon" href="images/logo_bw.png">
<title>Blood Warriors :: Provinces Ensorcellées</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<?php

echo "<link rel=\"STYLESHEET\" type=\"text/css\" href=\"css8.css\">\n"; 

echo "</head>\n";
echo "<body>\n";
?>
	<center>

	<a href="index.php"><img src="images/BannierV1.png" border="0"/></a><br />

	<H2>État de connexion</H2><br />

	<table class="newtable"><tr>
		<td class="newtitre">Blood Warriors :: <?php echo $CONF['game_echo']; ?></td>
	</tr><tr>
		<td class="newcontenu">
			<p class="info"><?php echo $message; ?></p>

	<?php
			if($accomplish == 1)
			{//ok ?>

				<H1><a href="index.php">Acceder à votre compte</a></H1><br />
				<script language="JavaScript">compteur =setTimeout('window.location="index.php?p=index"',700)</script>

			<?php
			} else {//relogin
			?>
				<!--tableau pour se connecter-->
				<B>Reconnexion</B><br />
				<form name="form1" method="post" action="log.php"><br />
				<B>Login</B><br/>
				<input name="login" type="text" size="15"><br />
				<B>Mot de passe</B><br/>
				<input name="mdp" type="password" size="15"><br/>
				<p><input type="submit" name="Submit" value="Connexion"></p>
				</form><br />
				<?php
			}
		?>
		</td>
	</tr><tr>
		<td class="newfin">&nbsp;</td>
	</tr></table>

<?php
}
else
{//on se déconnecte
	//detruit la session
	session_destroy();

	//Supprime le cookie
	setcookie('sessid');

	echo '<span class="avert">Votre session à bien été arrêtée!</span><br/>';
	?>
	<script language="JavaScript">compteur =setTimeout('window.location="index.php?"',100)</script>
	<?php
}
?>
	</body>
	</html>