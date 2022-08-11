<?php
//header
include ('adminheader.php');
?>
<script language='JavaScript'>
function details(id) 
{
	if (document.getElementById('t'+id).style.display == 'none') {
		document.getElementById('t'+id).style.display = '';
		document.getElementById('i'+id).src='images/moins.jpg';
	} else {
		document.getElementById('t'+id).style.display = 'none';
		document.getElementById('i'+id).src='images/plus.jpg';
	}
}
</script>
<?php 
if($Joueur->acceslvl >= $adminpage['variable_player'])
{
	//TITRE
	echo "<H2>Accès Level</H2>\n";
	echo "<span class=\"info\">Vous trouverez ici les joueurs classé par niveau de droit d'accès</span>\n";

	if(isset($_GET['id']) && isset($_POST['lvl']) && ($Joueur->acceslvl >= 9))
	{//on update des lvl
		$sql = "update `joueurs` set acceslvl = '".clean($_POST['lvl'])."' where id = '".clean($_GET['id'])."'";
		sql_query($sql);

		//On va vite chercher son pseudo...
		$res = mysql_fetch_array(sql_query("SELECT pseudo FROM joueurs WHERE id = '".clean($_GET['id'])."'"));

		echo "<br />Niveau mis à jour!</br>\n";
		journal_admin($Joueur->pseudo, "<img src=\"images/admin/ok.png\">Le niveau d\'acces de ".$res['pseudo']." est passé à ".clean($_POST['lvl']).".");
	}
?>
<table class="newsmalltable">
	<tr>
		<th width="9"><img id="i1" src="images/plus.jpg" onClick="javascript:details(1)"></td>
		<th>Niveau 3-9</td>
	</tr>
	<tr>
		<td colspan="2" id="t1" style="display:none">
			<table width="90%">
			<tr>
				<th width="35%">Pseudo</th>
				<th width="25%">Niveau d'acces</th>
				<th width="40%">Changer</th>
			</tr>
<?php
	$sql = "SELECT pseudo, acceslvl, id FROM `joueurs` WHERE `acceslvl` >= '3' ORDER BY acceslvl DESC";
	$req = sql_query($sql);
	while ($res = mysql_fetch_array($req))
	{
?>	
			<tr>
			<form method="POST" action="index.php?p=admin_variable_player&id=<?php echo $res['id']; ?>">
				<td><?php echo $res['pseudo']; ?></td>
				<td><?php echo $res['acceslvl']; ?></td>
				<td>
<?php	
if($Joueur->acceslvl >= 9)
{//on peut augmenter le niveau
	echo "					<select name=\"lvl\">\n";
	if($Joueur->acceslvl == 99) echo "						<option value=\"99\">99</option>\n";
	for($i = 9; $i >= -2; $i--)
	{
		echo "						<option value=\"".$i."\">".$i."</option>\n";
	}?>
					</select>
					<INPUT TYPE="submit" value="Changer">
				</td>
			</form>

<?php } else {?>
					&nbsp;
<?php }?>
				</td>
			</tr>
<?php
	 }
?>
			</table>
		</td>
	</tr>
	</table>

	<br />
<?php
	//-------------------------------------LVL 1-2 -------------------------------

	echo "<table class=\"newsmalltable\">\n";
	echo "<tr>\n";
	echo "	<th width=\"9\">\n";
	echo "		<img id=\"i2\" src=\"images/plus.jpg\" onClick=\"javascript:details(2)\">\n";
	echo "	</td>\n";
	echo "	<th>\n";
	echo "		Niveau 1 et 2\n";
	echo "	</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "	<td colspan=\"2\" id=\"t2\" style=\"display:none\">\n";
		//On a cliquer sur le plus
		echo "		<center>\n";
		echo "		<table width=\"90%\">\n";
		echo "		<tr>\n";
		echo "			<th width=\"35%\">Pseudo</th>\n";
		echo "			<th width=\"25%\">Niveau d'acces</th>\n";
		echo "			<th width=\"40%\">Changer</th>\n";
		echo "		</tr>\n";

	  $sql = "SELECT pseudo, acceslvl, id FROM `joueurs` WHERE `acceslvl` < '3' AND `acceslvl` > 0 ORDER BY acceslvl DESC";
	  $req = sql_query($sql);
	  while ($res = mysql_fetch_array($req))
	  {	
		echo "		<tr>\n";
		echo "			<form method=\"POST\" action=\"index.php?p=admin_variable_player&id=".$res['id']."\">\n";
		echo "			<td>".$res['pseudo']."</td>\n";
		echo "			<td>".$res['acceslvl']."</td>\n";
		echo "			<td>\n";
		if($Joueur->acceslvl >= 9)
		{//on peut augmenter le niveau
			echo "			<select name=\"lvl\">\n";
			for($i = 9; $i >= -3; $i--)
			{
				echo "			<option value=\"".$i."\">".$i."</option>\n";
			}
			echo "			</select>\n";
			echo "			<INPUT TYPE=\"submit\" value=\"Changer\"></td>\n";
			echo "			</form>\n";
		}
		else 
		{ 
			echo "		&nbsp;\n"; 
		}
		echo "			</td>\n";
	 }
	echo "		</table>\n";
	echo "	</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	echo "<br />\n";

	//-------------------------------------LVL 0 - -2 -------------------------------

	echo "<table class=\"newsmalltable\">\n";
	echo "<tr>\n";
	echo "	<th  width=\"9\">\n";
	echo "		<img id=\"i3\" src=\"images/plus.jpg\" onClick=\"javascript:details(3)\">\n";
	echo "	</td>\n";
	echo "	<th>\n";
	echo "		Niveau 0 et moins\n";
	echo "	</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "	<td colspan=\"2\" id=\"t3\" style=\"display:none\">\n";
		//On a cliquer sur le plus
		echo "		<center>\n";
		echo "		<table width=\"90%\">\n";
		echo "		<tr>\n";
		echo "			<th width=\"35%\">Pseudo</th>\n";
		echo "			<th width=\"25%\">Niveau d'acces</th>\n";
		echo "			<th width=\"40%\">Changer</th>\n";
		echo "		</tr>\n";

	  $sql = "SELECT pseudo, acceslvl, id FROM `joueurs` WHERE `acceslvl` < '1' ORDER BY acceslvl DESC";
	  $req = sql_query($sql);
	  while ($res = mysql_fetch_array($req))
	  {	
		echo "		<tr>\n";
		echo "			<form method=\"POST\" action=\"index.php?p=admin_variable_player&id=".$res['id']."\">\n";
		echo "			<td>".$res['pseudo']."</td>\n";
		echo "			<td>".$res['acceslvl']."</td>\n";
		echo "			<td>\n";
		if($Joueur->acceslvl >= 9)
		{//on peut augmenter le niveau
			echo "			<select name=\"lvl\">\n";
			for($i = 9; $i >= -3; $i--)
			{
				echo "			<option value=\"".$i."\">".$i."</option>\n";
			}
			echo "			</select>\n";
			echo "			<INPUT TYPE=\"submit\" value=\"Changer\"></td>\n";
			echo "			</form>\n";
		}
		else 
		{ 
			echo "		&nbsp;\n"; 
		}
		echo "			</td>\n";
	 }
	echo "		</table>\n";
	echo "	</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	echo "<br />\n";
	
	echo "Lexique: <br />0 suspention, -1 maintenance, -2 en cours d'activation, -3 désactivé mais pas supprimé)<br />\n";

}