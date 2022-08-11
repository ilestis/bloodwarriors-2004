<?php

//Header d'admin (session cheacker)
include ('adminheader.php');

$Page = 'nouveauxcomptes';
if($_SESSION['aut'][$adminpage['nouveauxcomptes']] == 0) breakpage();
?>

<H2>Activer - Annuler les inscriptions des nouveaux comptes.</H2>

<table class="newtable">
	<tr>
		<td class="newtitre">&nbsp;</td>
	</tr><tr>
		<td class="newcontenu">

<?php
$sql = "SELECT * FROM `inscriptions` ORDER BY `pseudo`";
$req = sql_query($sql);
$nbr = mysql_num_rows($req);
if ($nbr == 0) { ?>
	<span class="info">Aucune demande d'inscription actuellement enregistrée.</span><br />
	<?php 
}
else
{
	while($res = mysql_fetch_array($req))
	{
		//Variables
		$JId		=	$res['Id'];
		$JPseudo	=	$res['Pseudo'];
		$JLogin		=	$res['Login'];
		$JSurname	=	$res['Surname'];
		$JName		=	$res['Name'];
		$JEmail		=	$res['Email'];
		$JDisco		=	$res['Discovery'];
		$JRace		=	$res['Race'];
		$JIp		=	$res['Ip'];
		?>

		<table class="newsmalltable">
		<tr>
			<th> STATS: </th><th> DOUBLE IDENTITÉ: </th>
		</tr>

		<!-- PSEUDO -->
		<tr>
			<td>Pseudo: <?php echo $JPseudo; ?></td>
			<td><?php
				//Cherche joueurs avec pseudo similaires
				$SqlPse = "SELECT pseudo FROM `joueurs` WHERE `pseudo` LIKE '%".$JPseudo."%' AND `pseudo` <> '".$JPseudo."'";
				$ReqPse = sql_query($SqlPse);
				while ($ResPse = mysql_fetch_array($ReqPse)) {
					echo "<a href=\"index.php?p=admin_search&joueur=".$ResPse['pseudo']."\">".$ResPse['pseudo']."</a><br />";
				}?>
			</td>
		</tr>

		<!-- LOGIN -->
		<tr>
			<td>Login: <?php echo $JLogin; ?></td>
			<td><?php
				//Cherche joueurs avec pseudo similaires
				$SqlLog = "SELECT pseudo FROM `joueurs` WHERE `login` LIKE '%".$JLogin."%' AND `login` <> '".$JLogin."'";
				$ReqLog = sql_query($SqlLog);
				while ($ResLog = mysql_fetch_array($ReqLog)) {
					echo "<a href=\"index.php?p=admin_search&joueur=".$ResLog['pseudo']."\">".$ResLog['pseudo']."</a><br />";
				}?>
			</td>
		</tr>

		<!-- NOM -->
		<tr>
			<td>Nom: <?php echo $JSurname; ?></td>
			<td><?php
				//Cherche joueurs avec noms similaires
				$SqlNom = "SELECT pseudo FROM `joueurs` WHERE `nom` LIKE '%".$JSurname."%' AND `pseudo` <> '".$JPseudo."'";
				$ReqNom = sql_query($SqlNom);
				while ($ResNom = mysql_fetch_array($ReqNom)) {
					echo "<a href=\"index.php?p=admin_search&joueur=".$ResNom['pseudo']."\">".$ResNom['pseudo']."</a><br />";
				}?>
			</td>
		</tr>

		<!-- PRENOM -->
		<tr>
			<td>Prenom: <?php echo $JName; ?></td>
			<td><?php
				//Cherche joueurs avec prenom similaires
				$SqlPre = "SELECT pseudo FROM `joueurs` WHERE `prenom` LIKE '%".$JName."%' AND `pseudo` <> '".$JPseudo."'";
				$ReqPre = sql_query($SqlPre);
				while ($ResPre = mysql_fetch_array($ReqPre)) {
					echo "<a href=\"index.php?p=admin_search&joueur=".$ResPre['pseudo']."\">".$ResPre['pseudo']."</a><br />";
				}?>
			</td>
		</tr>

		<!-- EMAIL -->
		<tr>
			<td>Email: <?php echo $JEmail; ?></td>
			<td><?php
				//Cherche joueurs avec prenom similaires
				$SqlEma = "SELECT pseudo FROM `joueurs` WHERE `Email` LIKE '%".$JEmail."%' AND `pseudo` <> '".$JPseudo."'";
				$ReqEma = sql_query($SqlEma);
				while ($ResEma = mysql_fetch_array($ReqEma)) {
					echo "<a href=\"index.php?p=admin_search&joueur=".$ResEma['pseudo']."\">".$ResEma['pseudo']."</a><br />";
				}?>
			</td>
		</tr>

		<!-- IP -->
		<tr>
			<td>Ip: <?php echo $JIp; ?></td>
			<td><?php
				//Cherche joueurs avec prenom similaires
			$PseudoPasse = $JPseudo;
				$SqlIp = "SELECT id_joueur FROM `ip` WHERE `ip` LIKE '%".$JIp."%' AND `id_joueur` <> '".$JId."' ORDER BY `id_joueur`";
				$ReqIp = sql_query($SqlIp);
				while ($ResIp = mysql_fetch_array($ReqIp)) {
					if ($PseudoPasse != $ResIp['id_joueur']) {
						echo "<a href=\"index.php?p=admin_search&joueur=".$ResIp['id_joueur']."\">".$ResIp['id_joueur']."</a><br />";
						$PseudoPasse = $ResIp['id_joueur'];
					}
				}?>
			</td>
		</tr>

		<!-- Découverte -->
		<tr>	
			<td colspan="2">Découverte:<br />
			<?php echo $JDisco; ?></td>
		</tr>

	<tr>
		<th><a href="index.php?p=admin_validnew&id=<?php echo $JId; ?>&do=add">Accepter</a></th>
		<th><a href="index.php?p=admin_validnew&id=<?php echo $JId; ?>&do=del">Refuser</a></th>
	</tr>
	</table>
	<br />
	<?php
	}
}
?>
		</td>
	</tr><tr>
		<td class="newfin">&nbsp;</td>
	</tr>
</table>
<br />