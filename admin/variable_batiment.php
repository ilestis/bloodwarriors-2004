<?php
//header
require ('admin/adminheader.php');

if($_SESSION['aut'][$adminpage['variable_batiment']] == 0) exit;

$Level = 1;
if(isset($_GET['level']) && is_numeric($_GET['level'])) $Level = clean($_GET['level']);

$FRM = clean_form($_POST);
?>

<H2>Modifier les variables des bâtiments</H2>

<?php
if (isset($_GET['id']) && is_numeric($_GET['id'])) 
{//MAJ du bâtiments
	if(isset($FRM['gold']) && isset($FRM['food']) && isset($FRM['wood']) && isset($FRM['stone']) && isset($FRM['paysans']) && isset($FRM['cases']) && isset($FRM['puissance']) && isset($FRM['life']) && isset($FRM['nom']))
	{
		$Up = "UPDATE liste_batiments SET `or` = '".$FRM['gold']."', champ = '".$FRM['food']."', bois = '".$FRM['wood']."', pierre = '".$FRM['stone']."', paysan = '".$FRM['paysans']."', cases = '".$FRM['cases']."', puissance = '".$FRM['puissance']."', power = '".$FRM['text']."', life = '".$FRM['life']."', `nom` = '".$FRM['nom']."' WHERE `id` = '".clean($_GET['id'])."'";
		sql_query($Up);
		echo "<span class=\"info\">Bâtiment mis à jour.</span><br />\n";
	}
}
?>

<table class="newsmalltable">
<tr>
	<th colspan="4">
		<a href="index.php?p=admin_variable_batiment&level=1">Village</a> - 
		<a href="index.php?p=admin_variable_batiment&level=2">Ville</a> - 
		<a href="index.php?p=admin_variable_batiment&level=3">Cité</a> - 
		<a href="index.php?p=admin_variable_batiment&level=4">Métropole</a>
	</th>
</tr>
<tr>
	<th width="90px" align="left">Nom</th>
	<th width="140px" align="left">Coûts</th>
	<th width="240px" align="left">Texte</th>
	<th width="60px" align="left">Modifier</th>
</tr>
<?
$sql = "SELECT * FROM liste_batiments WHERE niveau = '".$Level."' ORDER BY `niveau` ASC, `num_batiment` ASC";
$req = sql_query($sql);
while ($res = mysql_fetch_array($req))
{	
	//Variables
	$Nom		= $res['nom'];
	$Id			= $res['id'];
	$Niv		= $res['niveau'];
	$Cout_or	= $res['or'];
	$Cout_no	= $res['champ'];
	$Cout_bo	= $res['bois'];
	$Cout_pi	= $res['pierre'];
	$Cout_ca	= $res['cases'];
	$Paysans	= $res['paysan'];
	$Duree		= $res['duree'];
	$Puissance	= $res['puissance'];
	$Life		= $res['life'];

	$Text		= affiche($res['power']);

	echo "	<tr id=\"fixe_".$Id."\">\n";
	echo "		<td colspan=\"3\">".$Nom."</td>\n";
	echo "		<td><a href=\"javascript:show('modif_".$Id."'); hide('fixe_".$Id."');\">Modifier</a></td>\n";
	echo "	</tr>\n";
	?>
	<tr id="modif_<?php echo $Id; ?>" style="display:none;">
		<form method='POST' action="index.php?p=admin_variable_batiment&id=<?php echo $Id;?>&level=<?php echo $Level ?>">
		<td>
			<input type="text" name="nom" value="<? echo $Nom; ?>" size="10" />
		</td>

		<td>
			Gol:<input type="text" name="gold" maxlength="3" size="2" value="<?php echo $Cout_or; ?>">
			Foo:<input type="text" name="food" maxlength="3" size="2" value="<?php echo $Cout_no; ?>"><br />
			Woo:<input type="text" name="wood" maxlength="3" size="2" value="<?php echo $Cout_bo; ?>">
			Sto:<input type="text" name="stone" maxlength="3" size="2" value="<?php echo $Cout_pi; ?>"><br />
			Pay:<input type="text" name="paysans" maxlength="3" size="2" value="<?php echo $Paysans; ?>">
			Cas:<input type="text" name="cases" maxlength="3" size="2" value="<?php echo $Cout_ca; ?>"><br />
			Dur:<input type="text" name="duree" maxlength="3" size="2" value="<?php echo $Duree; ?>">
			Pui:<input type="text" name="puissance" maxlength="3" size="2" value="<?php echo $Puissance; ?>"><br />
			Life:<input type="text" name="life" maxlength="3" size="2" value="<?php echo $Life; ?>">
		</td>

		<td>
			<TEXTAREA NAME="text" ROWS="5" COLS="30"><?php echo $Text; ?></TEXTAREA>
		</td>

		<td>
			<INPUT TYPE="submit" value="MAJ"><br />
			<a href="javascript:hide('modif_<?php echo $Id; ?>'); show('fixe_<?php echo $Id; ?>');">Annuler</a>
		</td>
		</form>
	</tr>
<? } ?>
</table>

<a href="index.php?p=admin_admin">Retour à la page Admin</a>