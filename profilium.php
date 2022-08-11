<?php 
//verifie si la session est en cours
require ('include/session_verif.php');




//on est connecté
require ('./profil.php');

$lang_page	= lang_include($LANG,'lang_profilium');

//des '.return_guilde($Joueur->royaume).' 
/*	
//selection les nouveaux messages
$sqlno = "SELECT id_message FROM messages WHERE nouveau < '2' AND id_to = '".$_SESSION['id_joueur']."' AND location = 'mes'" ;
$resultno = sql_query($sqlno) ;
$no = mysql_num_rows($resultno) ;   //nombre de messages
if ($no == 0) 
{
	echo '<p><img src="images/message_old.jpg"/>';
	echo $lang_page['no_new_message'].'.</p>';
}
else 
{
	echo "<p><img src=\"images/message_new.jpg\"/>\n";
	echo "<span class=\"info\">".$lang_page['new_messages']." <strong><a href=\"index.php?p=mess\">".$no."</a></strong></span>\n";
	//if($no >= 1) echo 'nouveau'.pluriel($no,'x').' message'.pluriel($no,'s');
	//echo 'dans votre messagerie!</span></p>';
}*/


//message admin
bw_tableau_start($lang_page['welcome'].$Joueur->pseudo.$lang_page['welcome_2']);

$sql = "SELECT message FROM messages WHERE location = 'acc'";
$req = sql_query($sql);
$res = sql_array($req);
echo "
<table class=\"newsmalltable\">
<tr>
	<td>
	<p style=\"font-size: 10.5pt;\">".affiche(stripslashes($res['message']))."</p>
	</td>
</tr>
</table>
";

bw_tableau_end();
?>