<?php
//verifie l'état de la partie en cours
$partie = $CONF['game_status'];


//verifie si la session est en cours
if (session_is_registered("id_joueur") == false)
{
	?>
		<div class="colonne_top">Blood Warriors</div>
		<div class="colonne_main">
				<a href="?"><?php echo $lang_links['nocon_linkhome']; ?></a><br />
			<img src="images/trait.png" /><br />
				<a href="?p=faq"><?php echo $lang_links['nocon_linkfaq']; ?></a> / 
				<a href="?p=regles"><?php echo $lang_links['rules']; ?></a><br />
			<img src="images/trait.png" /><br />
				<a href="?p=merci"><?php echo $lang_links['nocon_linkowner']; ?></a><br />
				<a href="?p=parte"><?php echo $lang_links['nocon_linklink']; ?></a><br />
				<a href="?p=download"><?php echo $lang_links['nocon_linkdown']; ?></a><br />
		</div>
		<div class="colonne_end"></div>

	<?php
		if ($CONF['game_status'] >= 1) { ?>
	<!--tableau pour se connecter-->	
	<form name="form1" method="post" action="log.php">
		<div class="colonne_top"><?php echo $lang_links['nocon_title']; ?></div>
		<div class="colonne_main">
			<?php if($CONF['game_status'] >= 2) { 
				echo "<p class=\"entoure\"><a href=\"?p=inscrip\">".$lang_links['nocon_linksub']."</a></p>\n";
			} ?>

			<div style="width: 80px; font-weight: bold;font-size: 8pt; float:left;">
				<?php echo $lang_links['nocon_login']; ?>
			</div>
			<div style=" float:left;">
				<input name="login" type="text" size="10">
			</div>
			<br style="clear: both;"/>

			<div style="width: 80px; font-weight: bold; font-size: 8pt; float:left;">
				<?php echo $lang_links['nocon_pwd']; ?>
			</div>
			<div style=" float:left;">
				<input name="mdp" type="password" size="10">
			</div>
			<br style="clear: both;"/>
			<?php echo $lang_links['nocon_auto']; ?>: <input type="checkbox" name="cookie" value="1" /><br />

			<input type="submit" name="Submit" value="<?php echo $lang_links['nocon_btn'];?>"><br />
			<a href="?p=lostpassword"><em><?php echo $lang_links['nocon_lost']; ?></em></a>
		</div>
		<div class="colonne_end"></div>
	</form>
	<?php } ?>
	
	<br />
<?php
}
else
{
	echo "\n";
	//Verifie les bâtiments
	$FortR			= bw_batiavailable(3);
	$MarcheR		= bw_batiavailable('marche', false);
	$FermR			= bw_batiavailable('ferme', false);
	$SortR			= bw_batiavailable('sorcelerie', false);
	$AllyR			= bw_batiavailable('auberge', false);
	$WoodMuraille	= bw_batiavailable(2);
	$StoneMuraille	= bw_batiavailable(29);
	$GranitMuraille	= bw_batiavailable(41);

	//connexion est active

	echo "			<div class=\"colonne_top\">\n";
	echo "				".$Joueur->pseudo;

	//selectionne les warnings et bon points
	$sql = "select id_type, warning FROM warnings WHERE id_joueur = '".$_SESSION['id_joueur']."'";
	$req = sql_query($sql);
	while ($warn = mysql_fetch_array($req))
	{
	if($warn['id_type'] == 1) {//Warning
		echo bw_icon("i_avertissement.png",$lang_over['warns'].": ".stripslashes($warn['warning'])); 
	} 
	elseif($warn['id_type'] == 2) {//Bon comportement
		echo bw_icon("i_bon_comportement.png", $lang_over['good'].": ".stripslashes($warn['warning']));
	}
	}//fin des warning
	echo "\n			</div>\n";

	echo "			<div class=\"colonne_main\">\n";

	echo "				<a href=\"?p=index\">".$lang_links['index'].'</a> / <a href="?p=search2">'.$lang_links['search']."</a>";

	echo "				<br /><img src=\"images/trait.png\" /><br />\n";

	if($CONF['game_status'] >= 2) { 
		echo "				<a href=\"?p=paysans\">".$lang_links['peasant']."</a> / ";
		//echo '<a href="index.php?p=taxes">'.$lang_links['tax'].'</a><br/>'
		echo " <a href=\"?p=const\">".$lang_links['const']."</a><br />\n";
		echo "				<a href=\"?p=province\">".$lang_links['province']."</a>";

		if($FermR == 1)	echo " / <a href=\"?p=bsp_terrain\">".$lang_links['land']."</a>";
		echo "<br />";
		if($FortR == 1)	echo "<a href=\"?p=bsp_fort\">".$lang_links['fort']."</a>";
		if($MarcheR == 1)	echo " / <a href=\"?p=bsp_marche\">".$lang_links['market']."</a>";
		if($SortR == 1)	echo " / <a href=\"?p=bsp_sort\">".$lang_links['sorcery']."</a>";
		if($WoodMuraille || $StoneMuraille || $GranitMuraille) echo " / <a href=\"?p=bsp_murailles\">Murailles</a>";

		echo "\n				<br /><img src=\"images/trait.png\" /><br />\n";

		echo "				<a href=\"?p=unites\">".$lang_links['unites']."</a><br />\n";
		echo "				<a href=\"?p=war\">".$lang_links['war']."</a> / ";
		echo "<a href=\"?p=scores\">".$lang_links['scores']."</a><br />\n";
		echo "				<a href=\"?p=carte\">".$lang_links['worldmap']."</a>\n";
	}
	echo "			</div>\n";
	echo "			<div class=\"colonne_end\"></div>\n";

	echo "			<br />\n\n";


	//----------------------------ALLIANCE--------
	if($AllyR == 1 && $CONF['game_status'] >= 2)
	{
		echo "			<div class=\"colonne_top\">".$lang_links['allyhead']."</div>\n";
		echo "			<div class=\"colonne_main\">\n";
		if($Joueur->ally_id != 0)
		{//on a une alliance
			echo '				<a href="index.php?p=ally_news">'.$lang_links['allynews'].'</a> / ';
			echo '				<a href="index.php?p=forum2&theme=9">'.$lang_links['allyforum']."</a><br />\n";


			if($Joueur->ally_lvl >= 4)
			{//on peut administrer
				echo '				<a href="index.php?p=ally_admin">'.$lang_links['allyadmin']."</a>\n";
			}
		}//end alliance
		else
		{
			echo "				<a href=\"index.php?p=ally_none\">".$lang_links['allysearch']."</a>\n";
		}//end alliance a pas
		echo "			</div>\n";		
		echo "			<div class=\"colonne_end\">&nbsp;</div>\n";

		//echo "</div>\n";
		echo "			<br />\n\n";
	}
	//-----------------------AUTRES----------------
	echo "			<div class=\"colonne_top\">".$lang_links['title2']."</div>\n";

	echo "			<div class=\"colonne_main\">\n";
	echo '				<img src="images/icons/icon_parametres.png" alt="admin" /><a href="index.php?p=para">'.$lang_links['conf'].'</a> / <a href="index.php?p=memo">'.$lang_links['memos'].'</a>';
	//Nombre de mémos
	$sqlmemo = "SELECT id_message FROM messages WHERE location = 'meo' AND id_from = '".$_SESSION['id_joueur']."'";
	$reqmemo = sql_query($sqlmemo);
	$nbrmemo = mysql_num_rows($reqmemo);
	echo ' ('.$nbrmemo.')<br />';

	echo '				<a href="index.php?p=faq">'.$lang_links['FAQ'].'</a> - <a href="index.php?p=regles">'.$lang_links['rules']."</a><br />\n";
	echo "				<a href=\"?p=errors\">BUGS</a> - \n";
	echo "				<a href=\"log.php\">".$lang_links['logout']."</a>\n";
	echo "			</div>\n";

	echo "			<div class=\"colonne_end\">&nbsp;</div>\n";
	echo "			<br />\n\n";


	//----------------------ADMIN----------------
	if($_SESSION['aut'][1] == 1) 
	{

		echo "			<div class=\"colonne_top\">".$lang_links['title3']."</div>\n";
		echo "			<div class=\"colonne_main\">\n";
		echo "				<a href=\"index.php?p=admin_admin\">".$lang_links['admin']."</a>\n";
		echo "			</div>\n";
		/*
		if ($Joueur->acceslvl >= 99) {//Debug?
			echo "	<div class=\"colonne_main\">";
			if (!isset($_GET['p'])) $PageEnCour = "index";
			else $PageEnCour = $_GET['p'];

			if ($_SESSION['debug'] == TRUE)
					echo "<a href=\"index.php?p=".$PageEnCour."&deb=off\">Désactiver Débug</a>\n";
			
			else
				echo "<a href=\"index.php?p=".$PageEnCour."&deb=on\">Activer Débug</a>\n";
			
			echo "	</div>\n";
		}*/

		echo "			<div class=\"colonne_end\">&nbsp;</div>\n";
		echo "			<br />\n";
	}
	//--------------------------FIN----------------
}

?>