<?php
/*----------------------[TABLEAU]---------------------
|Nom:			NewsAlly.php
+-----------------------------------------------------
|Description:	La page principal de l'alliance d'une team
+-----------------------------------------------------
|Date de création:				17/03/05
|Dernière modification[Auteur]: 07.02.06
+---------------------------------------------------*/
//verifie la session
require ('./include/session_verif.php');

bw_tableau_start("Nouvelles internes de l'alliance");



//déjà. on verifie s'il fait partie d'une alliance
if($Joueur->ally_id == 0)
{	
	echo bw_error("Il faut faire partie d'une alliance pour accéder à cette section!");
}
else
{
	if(isset($_GET['do']) && $_GET['do'] == 'quit')
	{
		//verifie si on est pas l'admin créateur
		if($Joueur->ally_lvl == 5)
		{//pas ok
		
			echo bw_error("Vous être le chef de cette alliance! Vous ne pouvez pas quitter l'alliance!");
			bw_tableau_end();
			require ('./footer.php');
		}
		else
		{
			//on quitte l'alliance
			$up = "UPDATE joueurs SET ally_id = '0', ally_lvl = '0' WHERE id = '".$_SESSION['id_joueur']."'";
			sql_query($up);
			
			echo bw_info("Vous avez bien quitté l'alliance!");
			bw_tableau_end();
			require ('./footer.php');
		}

	}

	//ensuite on prend les infos générale de la guilde
	//sql
	$sql = "SELECT * FROM `alliances` WHERE `ally_id` = '".$Joueur->ally_id."'";
	$req = sql_query($sql);
	$alliance = mysql_fetch_array($req);

	//nombre de membres
	$nombres = mysql_num_rows(sql_query("SELECT `id` FROM `joueurs` WHERE `ally_id` = '".$Joueur->ally_id."'"));

	//tableau
	?>
	<img src="<?php echo $alliance['image']; ?>" />

	<table class="newsmalltable">
	<tr>
		<th colspan="2">Ton alliance</th>
	</tr><tr>
		<td width="30%" valign="top">
		<strong>Nom de l'alliance:</strong><br />&nbsp;&nbsp; <?php echo affiche($alliance['name']); ?><br/>
		<strong>Nombre de membres:</strong><br />&nbsp;&nbsp;  <?php echo $nombres; ?><br />
		<strong>Chef(s) de l'alliance:</strong><br />
		<?php
			$chefs = "SELECT pseudo FROM joueurs WHERE ally_id = '".$Joueur->ally_id."' AND ally_lvl = '5'";
			$chefq = sql_query($chefs);
			while($chefr = mysql_fetch_array($chefq))
			{
				echo "&nbsp;&nbsp;".$chefr['pseudo']."<br />\n";
			}
		?>
		<td class="in"><?php echo affiche($alliance['news']); ?>&nbsp;</td>
	</tr>
	</table>

	<?php
	if($Joueur->ally_lvl < 5)
	{//quitter l'alliance
		?>
		<br>
		<table class="newsmalltable">
		<tr>
			<th>Quitter l'alliance</th>
		</tr><tr>
			<td><span class="info">Attention, si vous cliquez sur se lien, vous ne pourrez plus accéder au forum de l'alliance ni aucune partie de l'alliance.</span><br />
			<a href="index.php?p=ally_news&do=quit">Quitter l'alliance</a></td>
		</tr>
		</table>
		<?php
	}

	echo "	<br /><br />\n";

	bw_f_start("Membres de l'alliance");
	?>

	<table class="newsmalltable">
	<tr>
		<th>Pseudo</th>
		<th>Race</th>
		<th>Puissance</th>
	</tr>
	<?php
	$membres_sql = sql_query("SELECT id, pseudo, race, puissance, ally_lvl FROM joueurs WHERE ally_id = '".$Joueur->ally_id."' ORDER BY puissance DESC");
	while($res = mysql_fetch_array($membres_sql))
	{
		echo "<tr>\n";
			echo "<td class=\"in\">";
			if($res['ally_lvl'] == 5) echo "<img src=\"images/president.png\" title=\"Président d'alliance\" /> ";
			echo "<a href=\"?p=search2&joueurid=".$res['id']."\">".$res['pseudo']."</a></td>\n";
			echo "<td class=\"in\">".return_guilde($res['race'], $Joueur->lang)."</td>\n";
			echo "<td class=\"in\">".$res['puissance']."</td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
	bw_f_end();
}
bw_tableau_end();