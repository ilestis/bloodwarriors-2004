<?php
//verifie si la session est en cours
if (session_is_registered("login") == false)
{
	include 'news.php';
	exit;
}
?>
<body background="images/fond2.jpg">
<script language='JavaScript'>
function details(id) {
if (document.getElementById('t'+id).style.display == 'none') {
document.getElementById('t'+id).style.display = '';
document.getElementById('i'+id).src='images/moins.jpg';
} else {
document.getElementById('t'+id).style.display = 'none';
document.getElementById('i'+id).src='images/plus.jpg';
}}
</script>
<center><B><U>Modifier les variables des guildes</U></B></center>
<table width='95%' border="1" background="images/table.jpg">
<tr>
<? //1?>
<td width=9><img id='i1' src='images/plus.jpg' onClick='javascript:details(1)'></td><td>Places</td></tr>
<tr><td class="entete" colspan=2 id='t1' style='display:none'><center><table border=0 width='100%' bgcolor="#000000" cellpadding=2 cellspacing=2>
<tr>
<td class="entete">Variable</td>
<td class="entete">Modifier</td>
<td class="entete">Comments</td>
<td class="entete">Modifier</td>
</tr>
<?
$sql = "select * from `info_guilde`";
$req = mysql_query($sql);
while ($res = mysql_fetch_array($req))
{
	$relm = $res['royaume'];
	$place_total = $res['place_libre'];
	$comment = $res['comment'];
	?>
<tr><form method='POST' action='index.php?p=admin_variable_change&id=guilde&change=place&guilde=<? echo $relm; ?>'>
<td class="in">place_total_<? echo $relm; ?></td>
<td class="in">
<input type="text" name="valeur" maxlength="3" size="3" value="<? echo $place_total; ?>"></td>
<td class="in"><? echo $comment; ?></td>
<td class="in"><INPUT TYPE="submit" value="Changer"></td>
</form>
<? } ?>
</tr>
</table>
</td></tr>
<tr>
<? //2?>
<td width=9>
<img id='i2' src='images/plus.jpg' onClick='javascript:details(2)'></td>
<td>Bonus</td></tr>
<tr>
<td colspan=2 id='t2' style='display:none'><center>
<table border=0 width='100%' bgcolor="#000000">
<tr>
<td class="entete">Variable</td>
<td class="entete">Force</td>
<td class="entete">Endurance</td>
<td class="entete">Attaque</td>
<td class="entete">Défense</td>
<td class="entete">Commentaire</td>
<td class="entete">Modifier</td>
</tr>
<?
$sql = "select * from `info_guilde`";
$req = mysql_query($sql);
while ($res = mysql_fetch_array($req))
{
	$relm = $res['royaume'];
	$comment = $res['comment'];
	?>
<tr>
<form method='POST' action='index.php?p=admin_variable_change&id=guilde&change=bonus&guilde=<? echo $relm; ?>'>
<td class="in">bonus_<? echo $relm; ?></td>
<td class="in"><input type="text" name="b1" maxlength="1" size="1" value="<? echo $res['bonus_1']; ?>"></td>
<td class="in"><input type="text" name="b2" maxlength="1" size="1" value="<? echo $res['bonus_2']; ?>"></td>
<td class="in"><input type="text" name="b3" maxlength="1" size="1" value="<? echo $res['bonus_3']; ?>"></td>
<td class="in"><input type="text" name="b4" maxlength="1" size="1" value="<? echo $res['bonus_4']; ?>"></td>
<td class="in"><? echo $comment; ?></td>
<td class="in"><INPUT TYPE="submit" value="Changer"></td>
</form>

<? } ?>

</td></tr></table>
<tr>

<? //3 ?>
<td width=9>
<img id='i3' src='images/plus.jpg' onClick='javascript:details(3)'></td>
<td>Politique (1 = diplomatie / 2 = monarchie)</td></tr>
<tr>
<td colspan=2 id='t3' style='display:none'><center>
<table border=0 width='100%' bgcolor="#000000">
<tr>
<td class="entete">Variable</td>
<td class="entete">Politique</td>
<td class="entete">Commentaire</td>
<td class="entete">Modifier</td>
</tr>
<?
$sql = "select * from `info_guilde`";
$req = mysql_query($sql);
while ($res = mysql_fetch_array($req))
{
	$relm = $res['royaume'];
	$comment = $res['comment'];
	?>
<tr>
<form method='POST' action='index.php?p=admin_variable_change&id=guilde&change=politique&guilde=<? echo $relm; ?>'>
<td class="in">politique_<? echo $relm; ?></td>
<td class="in"><input type="text" name="politique" maxlength="1" size="1" value="<? echo $res['politique']; ?>"></td>
<td class="in"><? echo $comment; ?></td>
<td class="in"><INPUT TYPE="submit" value="Changer"></td>
</form>

<? } ?>

</td></tr></table>
<tr>
<? //4 ?>
<td width=9>
<img id='i4' src='images/plus.jpg' onClick='javascript:details(4)'></td>
<td>Ajouter une guilde</td></tr>
<tr>
<td colspan=2 id='t4' style='display:none'><center>
<table border=0 width='100%' bgcolor="#000000">
<tr>
<td class="entete">Nom de la guilde</td>
<td class="entete">Ajouter</td>
</tr>
<tr>
<form method='POST' action='index.php?p=admin_variable_change&id=guilde&change=addnew'>
<td class="in"><input type="text" name="name" maxlength="30" size="30">
<td class="in"><INPUT TYPE="submit" value="Changer"></td>
</form>

</td></tr></table>

</table>
<a href="admin.php">Retour à la page Admin</a>
