<?php
//verifie si la session est en cours
include ('adminheader.php');

if($_SESSION['aut'][$adminpage['messagesaison']] == 0)  breakpage();

bw_tableau_start("Modifier le message d'accueil");


//Si le text est saisi, on met à jour
if(isset($_POST['passage']))
{
	$comment = forummessage(forumadd($_POST['commentaire']));

	if(sql_rows(sql_query("SELECT message FROM messages WHERE location = 'acc'")) == 1) {
		sql_query("UPDATE messages SET message = '".$comment."' WHERE location = 'acc'");
	} else {
		sql_query("INSERT INTO messages SET message = '".$comment."', location = 'acc'");
	}

	//hop dans le journal
	journal_admin($Joueur->pseudo, "<img src=\"images/admin/edit.png\">Message de la saison modifié.");
}


bw_f_info("Attention", "N'oubliez pas que ce message et sur le profil, premier message qu'un nouveau joueur lit! Il doit contenir une bienvenue, une orthographe correcte, ainsi que de la mise en forme, des liens etc... N'oubliez pas aussi de le rediriger sur les forums! Et aussi un points sur les nouveaux par rapport aux vieilles saison! Merci.")
?>

<form name="form1" method="post" action="index.php?p=admin_messagesaison&leed=2">

<?php
# Sélectionne le message dans la BDD
$sql = "SELECT message FROM messages WHERE location = 'acc'";
$req = sql_query($sql);
$res = sql_array($req);


# FORMULAIRE
include ('include/mise_en_forme.inc.php');
bw_afficheToolbar("Enregistrer", reversemessage($res['message']));
?>
<br />
<input type="hidden" name="passage" value="1">
</form>
<?php


echo "<a href=\"?p=admin_admin\">Retour à l'administration</a><br />\n";
bw_tableau_end();
?>