<!-- Verifieur de l'inscription -->
<script language="javascript" type="text/javascript">
function verif(form)
{
	if(form.email.value.lastIndexOf("@")==-1 || form.email.value.lastIndexOf(".")==-1) {alert("Erreur de saisie dans l'adresse email. Il manque soit le \"@\" soit le \".\" de \".com\", \".ch\" ou mÃª \".fr\""); return false;}
	if(form.mdp.value!=form.mdp2.value) {alert("Les 2 mots de passe entrÃ© sont différents !"); return false;}
	if(form.list_royaume.value==0) {alert("Vous devez choisir une race!"); return false;}
}				
</script>
<?php

bw_tableau_start("Inscriptions");
//verifie l'état de la partie en cours
if($CONF['game_status'] > 1)
{ //la partie est ouverte

	if($CONF['game_status'] == 1)
	{
		echo "<strong>Attention</strong>: La partie a &eacute;t&eacute; activ&eacute; mais pas encore lanc&eacute;e. Vous pouvez cependant d&eacute;jÃ  faire des demandes d'inscriptions qui seront valid&eacute;es.<br />\n";
	}
	?>
	<form action="index.php?p=inscription_next" method="post" name="inscrip" onSubmit="return verif(this);">
	<fieldset>
		<legend>Votre H&eacute;ros</legend>
		<table style="border: 0px; text-align: left;">
		<tr>
			<td width="250px"><strong>Nom du H&eacute;ros :</strong><br /><em>Le nom qui sera utilis&eacute; par les joueurs</em></td>
			<td valign="top"><?php if(isset($Err_Pse)) echo "<span class=\"info\">".$Err_Pse."</span><br />\n"; ?>
			<input name="pseudo" type="text" id="pseudo" size="30" maxlength="20" <?php 
				if(isset($Err_Pse)) echo "style=\"background-color: ".$CONF['errcol'].";\" "; 
				if(isset($JPseudo)) echo "value= \"".$JPseudo."\" "; ?> /></td>
		</tr>
		<tr>
			<td><strong>Race du H&eacute;ros :</strong><br /><em>Le nombre indique les places disponnibles</em></td>
			<td valign="top"><select name="list_royaume"><?php if(isset($Err_Roy)) echo "<span class=\"info\">".$Err_Roy."</span><br />\n"; ?>
				<option value="0">--Sélectionnez--</option>
<?php
			for ($i=1; $i<=6; $i++)
			{
				$sqlno = "SELECT id FROM joueurs WHERE race = '".$i."'";		
				$resultno	= sql_query($sqlno) ;
				$Pris		= mysql_num_rows($resultno) ;
				$Dispo	= $CONF['game_race_limit'] - $Pris;
				$Selected = (isset($JRace) && $JRace == $i ? ' selected' : ' ');
				echo "				<option value=\"".$i."\" ".$Selected." ".($Dispo > 0 ? '' : 'disabled').">".return_guilde($i, (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr'))." (".$Dispo.")</option>\n";
			}
?>
			</select></td>
		</tr>
		<tr>
			<td><strong>Login :</strong><br /><em>Utilis&eacute; pour vous authentifier</em></td>
			<td valign="top"><?php if(isset($Err_Log)) echo "<span class=\"info\">".$Err_Log."</span><br />\n"; ?>
			<input name="logininsc" type="text" id="pseudo" size="30" maxlength="20" <?php 
				if(isset($Err_Log)) echo "style=\"background-color: ".$CONF['errcol'].";\" "; 
				if(isset($JLogin)) echo "value= \"".$JLogin."\" "; ?> /><br /></td>
		</tr>
		<tr>
			<td><strong>Votre Mot de passe :</strong><br /><em>Les mot de passes sont cryptés</em></td>
			<td valign="top"><?php if(isset($Err_Psw)) echo "<span class=\"info\">".$Err_Psw."</span><br />\n"; ?>
			<input name="mdp" type="password" id="mdp" size="30" maxlength="20" <?php 
				if(isset($Err_Psw)) echo "style=\"background-color: ".$CONF['errcol'].";\" ";
				if(isset($JPsw)) echo "value= \"".$JPsw."\" "; ?> /><br /></td>
		</tr>
		<tr>
			<td><strong>Confirmer le MDP :</strong><br /></td>
			<td valign="top"><?php if(isset($Err_Psw2)) echo "<span class=\"info\">".$Err_Psw2."</span><br />\n"; ?>
			<input name="mdp2" type="password" id="mdp2" size="30" maxlength="20" <?php 
				if(isset($Err_Psw2)) echo "style=\"background-color: ".$CONF['errcol'].";\" ";
				if(isset($JPsw2)) echo "value= \"".$JPsw2."\" "; ?> /></td>
		</tr>
		</table>
	</fieldset><br />

	<fieldset>
		<legend>Informations Personnelles</legend>
		<table style="border: 0px; text-align: left;">
		<tr>
			<td width="250px"><strong>Pr&eacute;nom :</strong></em></td>
			<td valign="top"><?php if(isset($Err_Prenom)) echo "<span class=\"info\">".$Err_Prenom."</span><br />\n"; ?>
			<input name="prenom" type="text" id="prenom" size="30" <?php 
				if(isset($Err_Prenom)) echo "style=\"background-color: ".$CONF['errcol'].";\" "; 
				if(isset($JPrenom)) echo "value= \"".$JPrenom."\" "; ?> /><br /></td>
		</tr>
		<tr>
			<td ><strong>Nom :</strong></em></td>
			<td valign="top"><?php if(isset($Err_Nom)) echo "<span class=\"info\">".$Err_Nom."</span><br />\n"; ?>
			<input name="nom" type="text" id="nom" size="30" <?php 
				if(isset($Err_Nom)) echo "style=\"background-color: ".$CONF['errcol'].";\" "; 
				if(isset($JNom)) echo "value= \"".$JNom."\" "; ?> /><br /></td>
		</tr>
		<tr>
			<td ><strong>Adresse E-mail :</strong></em></td>
			<td valign="top"><?php if(isset($Err_Mail)) echo "<span class=\"info\">".$Err_Mail."</span><br />\n"; ?>
			<input name="email" type="text" id="email" size="30" maxlength="50" <?php 
				if(isset($Err_Mail)) echo "style=\"background-color: ".$CONF['errcol'].";\" "; 
				if(isset($JEmail)) echo "value=\"".$JEmail."\" "; ?>/><br /></td>
		</tr>
		<tr>
			<td valign="top"><strong>D&eacute;couverte :</strong> (Facultatife)<br /><em>Comment avez-vous pris connaissance de BW?</em></td>
			<td valign="top"><textarea name="decouverte" cols="40" rows="4"><?php if(isset($JDisco)) echo $JDisco; ?></textarea><br /></td>
		</tr>
		</table>
	</fieldset><br />

	<fieldset>
		<legend>Charte</legend>
		<!--<textarea name="disclaimer" cols="70" rows="10" disabled style="color: #000;"></textarea>-->
		
		<div style="width:100%; height: 150px; background-color: #ddd; border:1px dotted black; overflow:auto;">
		<? require('./admin/charte.txt'); echo nl2br($sCharte); ?></div><br />
		<p><?php if(isset($Err_Charte)) echo "<span class=\"info\">".$Err_Charte."</span><br />\n"; ?>
		<input type="checkbox" name="charte" <?php 
				if(isset($Err_Charte)) echo "style=\"background-color: ".$CONF['errcol'].";\" ";
				if(isset($JChk) && $JChk == 'on') echo "checked";?>/> : J'ai lu et j'approuve la charte</p>
	</fieldset><br />
	
	<fieldset>
		<legend>Code de verification</legend>
		<p><img src="image.php?do=makerandom" /><br />
		<?php if(isset($Err_Img)) echo "<span class=\"info\">".$Err_Img."</span><br />"; ?>
		<input type="text" maxlength="10" name="sha" <?php if(isset($Err_Img)) echo "style=\"background-color: ".$CONF['errcol'].";\" "; ?> /></p>
	</fieldset>
	
	<p style="text-align: center;"><input type="submit" value="S'inscrire!" /></p>
	</form>


<? 
} else {
	echo bw_info("La partie est encore en cours de mise en place. Les inscriptions ne sont donc pas encore disponnibles!<br/>");
}
bw_tableau_end();
?>