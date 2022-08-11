<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Recherche.php
+-----------------------------------------------------
|Description:	Permet de rechercher et proposer sa candidature à une alliance
+-----------------------------------------------------
|Date de création:				19/03/05
|Dernière modification[Auteur]: jj/mm/aa[Pseudo]
+---------------------------------------------------*/
//verifie la session
require ('./include/session_verif.php');

bw_tableau_start("Recherche d'alliance");

if($AllyR == 0)
{//si on a pas l'auberge ou on est pas des rebelles
	echo bw_error("Vous n'avez pas construit d'auberge, ou elle est trop endomagée!");
	bw_tableau_end();
	breakpage();
	die();
}

//verifie si on a pas déjà une alliance
elseif($Joueur->ally_id > 0 )
{
	echo bw_error("Vous avez déjà une alliance!");	
	bw_tableau_end();
	breakpage();
	die();
}


//maintenant on verifie ce qu'on fait
elseif(!isset($_GET['do']))
{
	//on affiche la liste des alliances
	echo "<table class=\"newsmalltable\">\n";
	echo '<tr><th>Alliances disponibles</th></tr>';

	$nbr = 1;
	$sql = "SELECT ally_id, name FROM alliances ORDER BY name ASC";
	$req = sql_query($sql);
	while($res = mysql_fetch_array($req))
	{//prend chaque alliance par ordre alphabétique
		echo '<tr><td>'.$nbr.' - <a href="index.php?p=ally_none&do=view_ally&id='.$res['ally_id'].'">'.$res['name'].'</a></td></tr>';
		$nbr ++;
	}
	echo '</table>';

	echo '<br>';

	//----------créer une alliance
	?>
	<table class="newsmalltable">
	<FORM METHOD="POST" ACTION="index.php?p=ally_none&do=create">
	<tr><th colspan="2">Créer une alliance</th></tr>
	<tr><td colspan="2">Attention, la création d'une alliance demande <?php echo $CONF['ally_gold_cost']." or et ".$CONF['ally_craft_cost']; ?> magie!</td></tr>
	<tr><td width="30%">Nom:</td><td><INPUT TYPE="text" NAME="name" size="70"></td></tr>
	<tr><td class="in">Url de l'image:</td><td><INPUT TYPE="text" NAME="image"  size="70"></td></tr>

	<tr><td class="in">Description</td>
	<td class="in_left"><TEXTAREA NAME="description" ROWS="7" COLS="52"></TEXTAREA></td>
	</tr>

	<tr><td class="in" colspan="2"><center><INPUT TYPE="submit" value="          Créer!          "></center></td></tr>

	</form>
	</table>
	<?php
}
elseif($_GET['do'] == 'view_ally')
{//on regarde une alliance en particulié
	//verifie si elle existe
	$sql = "SELECT * FROM alliances WHERE ally_id = '".clean($_GET['id'])."'";
	$req = sql_query($sql);
	$resultat = mysql_num_rows($req);

	if($resultat == 0)
	{
		echo bw_error("Cette alliance n'existe pas!");
		bw_tableau_end();
		breakpage();
		die();
	}
	//si on est la c'est que c'est bon!
	$res = mysql_fetch_array($req);

	//nombre de membres
	$ss = sql_query("SELECT pseudo FROM joueurs WHERE `ally_id` = '".$res['ally_id']."'");
	$nombres = mysql_num_rows($ss);

	//tableau
	?>

	<table class="newsmalltable">
		<tr>
			<th colspan="2">L'alliance <?php echo $res['name']; ?></th>
		</tr><tr>
			<td width="30%" valign="top"><strong>Nom de l'alliance: <?php echo $res['name']; ?><br/>
			Nombre de membres: <?php echo $nombres; ?></strong><br />
			<img src="<?php echo $res['image']; ?>" /></td>
			<td><?php echo affiche($res['description']); ?></td>
		</tr>
		</table>

		<br>

		<!-- postuler -->
		<table class="newsmalltable">
			<tr><th>Postuler à cette alliance</th></tr>
			<tr> <FORM METHOD=POST ACTION="index.php?p=ally_none&do=ask_ally&ally_id=<?php echo $res['ally_id']; ?>"> 
				<td>
				<center><textarea name="comment" cols="50" rows="7" id="comment">Écrivez ici votre demande de postulation.</textarea><br/>
				<input type="submit" name="Submit2" value="Envoyer">
				</center>
			</td></form>
		</tr>
	</table>
	<?php
}
elseif($_GET['do'] == 'ask_ally')
{//on fait une demande
	//verifie si elle existe
	$sql		=	"SELECT * FROM alliances WHERE ally_id = '".clean($_GET['ally_id'])."'";
	$req		=	sql_query($sql);
	$resultat	=	mysql_num_rows($req);

	if($resultat == 0)
	{
		echo bw_error("Cette alliance n'existe pas!");
		bw_tableau_end();
		breakpage();
		die();
	}

	//on verifie si on a pas deja demandé!
	$ver = "SELECT * FROM temp_alliances WHERE joueur_id = '".$_SESSION['id_joueur']."' AND ally_id = '".clean($_GET['ally_id'])."'";
	$veri = sql_query($ver);
	$verif = mysql_num_rows($veri);
	if($verif > 0)
	{		
		echo bw_error("Vous avez déjà postulé pour cette alliance!");
		bw_tableau_end();
		breakpage();
		die();
	}

	//si on est la c'est que c'est bon!
	$res = mysql_fetch_array($req);

	//enlève le html et tout ca
	$comment = forumadd(clean($_POST['comment']));

	//met dans les alliances_temps
	$ally = "INSERT INTO `temp_alliances` VALUES('','".$res['ally_id']."','".$Joueur->pseudo."', '".$_SESSION['id_joueur']."','".$comment."','".time()."')";
	sql_query($ally);

	echo 'Votre demande à bien été prise en compte!<br />Il vous faudra attendre que l\'alliance accèpte votre demande.<br />';
}
elseif($_GET['do'] == 'create')
{//on créer une alliance
	//verifie nos ressource
	$sqlrr = "SELECT gold, craft FROM provinces WHERE `id` = '".$_SESSION['id_province']."'";
	$sqlr = sql_query($sqlrr);
	$resr = mysql_fetch_array($sqlr);

	if($resr['gold'] >= $CONF['ally_gold_cost'] && $resr['craft'] >= $CONF['ally_craft_cost'])
	{//ok
		//vire le text pas beau
		$name = clean($_POST['name']);
		$desc = forumadd(clean($_POST['description']));
		$image = clean($_POST['image']);

		//verifie si y'a pas une alliance avec le même nom
		$sql = "SELECT ally_id FROM alliances WHERE name = '".$name."'";
		$req = sql_query($sql);
		$resultat = mysql_num_rows($req);

		if($resultat <> 0)
		{
			echo bw_error("Ce nom est déjà utilisé par une autre alliance!");
			bw_tableau_end();
			breakpage();
			die();
		}
		else
		{
			//si on est la c'est que c'est ok!

			//insert dans la base de données
			$alliance = "INSERT INTO alliances(name, description, image, creator) VALUES('".$name."','".$desc."','".$image."', '".$_SESSION['id_joueur']."')";
			sql_query($alliance);

			//prend le numéros de l'alliance
			$id_ally = mysql_insert_id();
			

			sql_query("UPDATE provinces SET gold = (gold - '".$CONF['ally_gold_cost']."'), craft = (craft - '".$CONF['ally_craft_cost']."') WHERE `id` = '".$_SESSION['id_province']."'");
			sql_query("UPDATE joueurs SET ally_id = '".$id_ally."', ally_lvl = '5' WHERE id = '".$_SESSION['id_joueur']."'");

			//supprime nos postulations
			$dele = "DELETE FROM `temp_alliances` WHERE joueur_id = '".$_SESSION['id_joueur']."'";
			sql_query($dele);
			
			bw_f_info("Information", "Votre alliance à bien été créée!");
		}
	}
	else
	{//pas assez
		echo "<span class=\"avert\">Vous n'avez pas assez de ressources!</span><br />\n";
	}
}
bw_tableau_end();