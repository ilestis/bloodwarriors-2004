<?php
session_start();
global $CONF;
require('include/variables.inc.php');
include 'include/fonction.php'; //Les fonctions
require_once('./class/class.MySql.php');
$csql = new sql();

//Lanche connection
if(!$csql->connection($GLOBALS['CONF']['game_DB_user'], $GLOBALS['CONF']['game_DB_psw'] ,  $GLOBALS['CONF']['game_DB_server'], $GLOBALS['CONF']['game_DB_name'])) {
	echo $csql->error();
	die("Impossible de se connecter au serveur!");
}

// Affiche les connectés au jeu dans un petit cadre
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 4.01t//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	
<head>
	<link rel="SHORTCUT ICON" type="image/x-icon" href="images/logo_bw.png" />
	<title>Blood Warriors :: Jeu de rôle gratuit sur internet</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="STYLESHEET" type="text/css" href="css8.css">
</head>
<script type="text/javascript">
	function shownclose(iID) {
			opener.location = "index.php?p=search2&joueurid="+iID+"&view=1";
			self.close();
	}
</script>
<body>
<?php

bw_f_start("Joueurs connectés");
//Prend chaque joueur
$CinqMin = time() - 300; //5 minutes d'activités
$sql = "SELECT pseudo, id FROM joueurs WHERE user_session >= '".$CinqMin."'";
$req = sql_query($sql);
while ($res = mysql_fetch_array($req))
{
	echo "<a href=\"javascript:shownclose('" . $res['id'] . "');\">".$res['pseudo']."</a>";
	
	if(isset($_SESSION['id_joueur']) && false) {
	?>
		[<a href="sendmessagesprives.php?id=<?php echo $res['id']; ?>" title="Envoyé un message privé" target="sendmessagesprives" onclick="window.open('sendmessagesprives.php?id=<?php echo $res['id']; ?>','sendmessagesprives','height=220px, width=450px, resizable=yes');return false;">MP</a>]
	<?php } ?>
	<br />
<?php
}

bw_f_end();
echo "
	</body>

	</html>";