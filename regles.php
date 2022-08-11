<?php

bw_tableau_start("Les R�gles");


$sql = "SELECT section FROM autres_regles GROUP BY section ORDER BY id ASC";
$req = sql_query($sql);
while($res = sql_array($req))
{
	$Title = $res['section'];
	$Message = '';
	$Step = 0;

	$s = "SELECT valeur FROM autres_regles WHERE section = '".$Title."' ORDER BY id ASC";
	$r = sql_query($s);
	while($rs = sql_array($r))
	{
		$Step += 1;
		$Message .= "<strong>".$Step."</strong> ".nl2br($rs['valeur'])."<br /><br />\n";
	}
	bw_fieldset($Title, $Message, "left"); echo "<br />\n";
}

?>

<B>Les variables g�n�rales</B><br />
Voici un tableau avec les variables g�n�rales de la partie.<br />

<TABLE class="newsmalltable">
<TR>
	<TH>Variable</TH>
	<TH>Param�tre</TH>
</TR>
<TR>
	<TH colspan="2">Forum</TH>
</TR>
<TR>
	<TD>Nombre de topics par page</TD>
	<TD><?php echo $CONF['forum_topics']; ?></TD>
</TR>
<TR>
	<TD>Nombre de messages par page</TD>
	<TD><?php echo $CONF['forum_messages']; ?></TD>
</TR>
<TR>
	<TH colspan="2">G�n�ral</TH>
</TR>
<TR>
	<TD>�tat de la partie (0:ok; 1: maintenance)</TD>
	<TD><?php echo $CONF['game_status']; ?></TD>
</TR>
<TR>
	<TD>Nombre de carr� x et y de la carte</TD>
	<TD><?php echo $CONF['game_case']; ?></TD>
</TR>
<TR>
	<TD>Dur�e minimale de vacances</TD>
	<TD><?php echo $CONF['game_min_holiday']; ?></TD>
</TR>
<TR>
	<TD>Dur�e maximale de vacances</TD>
	<TD><?php echo $CONF['game_max_holiday']; ?></TD>
</TR>
<TR>
	<TD>Nom de la version</TD>
	<TD><?php echo $CONF['game_echo']; ?></TD>
</TR>
<TR>
	<TD>Date de commencement de la partie</TD>
	<TD><?php echo date($CONF['game_timeformat'], $CONF['game_time_start']); ?></TD>
</TR>
<TR>
	<TD>Num�ro du style par d�faut</TD>
	<TD><?php echo $CONF['default_css']; ?></TD>
</TR>
<TR>
	<TH colspan="2">Guerres</TH>
</TR>
<TR>
	<TD>% entre un joueur plus faible que l'on peut attaquer</TD>
	<TD><?php echo $CONF['relation_attack_power']; ?></TD>
</TR>
<TR>
	<TD>Dur�e de d�placement d'une case horizontale ou verticale</TD>
	<TD><?php echo $CONF['war_time']; ?></TD>
</TR>
<TR>
	<TH colspan="2">Joueurs</TH>
</TR>
<TR>
	<TD>Ressources max de chaque possibilit�s</TD>
	<TD><?php echo $CONF['ressources_max']; ?></TD>
</TR>
<TR>
	<TD>Nombre de paysans en inscriptions</TD>
	<TD><?php echo $CONF['start_paysans']; ?></TD>
</TR>
<TR>
	<TD>Ressources en or � l'inscroption</TD>
	<TD><?php echo $CONF['start_or']; ?></TD>
</TR>
<TR>
	<TD>Ressources en nourriture � l'inscroption</TD>
	<TD><?php echo $CONF['start_champs']; ?></TD>
</TR>
<TR>
	<TD>Ressources en pierre � l'inscroption</TD>
	<TD><?php echo $CONF['start_pierre']; ?></TD>
</TR>
<TR>
	<TD>Ressources en bois � l'inscroption</TD>
	<TD><?php echo $CONF['start_bois']; ?></TD>
</TR>
<TR>
	<TD>Ressources en magie � l'inscroption</TD>
	<TD><?php echo $CONF['start_magie']; ?></TD>
</TR>
<TR>
	<TD>Nombre de paysans minimal</TD>
	<TD><?php echo $CONF['paysans_min']; ?></TD>
</TR>
<TR>
	<TH colspan="2">Alliances</TH>
</TR>
<TR>
	<TD>Co�t de cr�ation d'un alliance en or</TD>
	<TD><?php echo $CONF['ally_gold_cost']; ?></TD>
</TR>
<TR>
	<TD>Co�t de cr�ation d'une alliance en magie</TD>
	<TD><?php echo $CONF['ally_craft_cost']; ?></TD>
</TR>
</TABLE>
<?php

bw_tableau_end();

?>
