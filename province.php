<?php
//Gestion de changement de province / création d'une nouvelle / changement de nom / supprimation
require ('profil.php');

bw_tableau_start("Gestion du Royaume");

if (isset($_GET['do'])) {//On fait quelque chose
	if ($_GET['do'] == 'name') {//Change le nom de la province
		$Name = clean($_POST['nomprovince']);
		$Up_Name = "UPDATE provinces SET name = '".$Name."' WHERE id = '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."'";
		sql_query($Up_Name);

		$Message = bw_info("Le nom de votre province a été modifié en ".$Name.".");
	}
	elseif ($_GET['do'] == 'new') {//Créé une nouvelle province
		//Verifie qu'on a la crêche
		if(bw_batiavailable("crechepourenfants", false) || $Joueur->race == 4)
		{
			//Verifie notre nombre de province type crêche par rapport au nombre total
			$sql = "SELECT id FROM provinces WHERE id_joueur = '".$_SESSION['id_joueur']."' AND type_province = '1'";
			$req = sql_query($sql);
			$nbr2 = mysql_num_rows($req);

			$Max = $CONF['province_max_nb_creche'];

			if($nbr == 0 && $Joueur->race == 4)
				$Max = 1;

			if($nbr2 >= $Max)
			{
				$Message = bw_error("Vous avez déjà le nombre maximal de provinces autorisées par joueur (".$Max.")");
			}
			else
			{
				//Verifie nos ressources
				$sql = "SELECT gold, food, mat, craft FROM provinces WHERE id = '".$_SESSION['id_province']."'";
				$req = sql_query($sql);
				$res = mysql_fetch_array($req);

				$pays = "SELECT nombre FROM temp_paysans WHERE section = '0' AND id_province = '".$_SESSION['id_province']."' AND esclave = 'N'";
				$payq = sql_query($pays);
				$payr = mysql_fetch_array($payq);

				$NewGold = $res['gold'] - $CONF['province_gold'];
				$NewFood = $res['food'] - $CONF['province_food'];
				$NewMat = $res['mat'] - $CONF['province_mat'];
				$NewCraft = $res['craft'] - $CONF['province_craft'];
				$NewPeasa = $payr['nombre'] - $CONF['province_pesants'];

				if(($NewGold >= 0) && ($NewFood >= 0) && ($NewMat >= 0) && ($NewCraft >= 0) && ($NewPeasa >= 0))
				{//Ok
					//Choisi un x et un y aléatoirement
					$disponible = FALSE;
					while($disponible == FALSE)
					{//tant que c'est pas disponibles
						$x = mt_rand(1, $CONF['game_case']);
						$y = mt_rand(1, $CONF['game_case']);
						$dispo = "SELECT id FROM provinces WHERE `x` = '".$x."' AND `y` = '".$y."'";
						$requi = sql_query($dispo);
						$repo = mysql_num_rows($requi);

						if ($repo == 0)
						{//Disponnibilité = ok
							$disponible = TRUE;
						}
					}//End - While

					//Update les ressources
					$Up = "UPDATE provinces SET gold = '".$NewGold."', food = '".$NewFood."', mat = '".$NewMat."', craft = '".$NewCraft."', peasant = (peasant-'".$CONF['province_pesants']."') WHERE id = '".$_SESSION['id_province']."'";
					sql_query($Up);

					$UpP = "UPDATE temp_paysans SET nombre = '".$NewPeasa."' WHERE section = '0' AND id_province = '".$_SESSION['id_province']."' AND esclave = 'N'";
					sql_query($UpP);

					//Hasard du nom
					$Name1 = array("Kei", "Dru", "Haz", "Neo", "Uto", "Seto", "Futra", "Futro", "Feia", "Fior", "Zur", "Nem", "Nur", "Ima", "Oguy", "Hera", "Heo", "Sta", "Fio", "Que", "Quer", "Quan", "Qua", "Mon", "Bel", "Biai", "Xi", "Ni", "Chi", "Shi", "San", "Per", "Pui", "Paw", "Gue", "Gan", "Gea", "Mega", "Yio", "Lo", "Lio", "Lar", "Lem", "Wep");
					$Name2 = array("Haguez", "Promina", "Metaro", "Machina", "Hilopem", "Opetias", "Tienalo", "Homilas", "Xerosi", "Lapena", "Destruo", "Jiromen", "Jinko", "Seltera", "Terra", "Grande", "Hespois", "Querang", "Xawaq", "Ximang", "Xopeth", "Welan", "Wixigon", "Multaro", "Naliba", "Uinios", "Seledra", "Yelewa", "Denyst", "Limix", "Tronef", "Felemis", "Senote", "Amarun", "Ambiga", "Ateriu", "Apeler", "Awigno", "Aximaq", "Acepiod", "Betanor", "Bexian", "Balimer");

					$nom = $Name1[mt_rand(0, count($Name1)-1)].$Name2[mt_rand(0, count($Name2)-1)];

					$Ins = "INSERT INTO provinces "
					. "SET id_joueur = '".$_SESSION['id_joueur']."', "
					. "name = '".$nom."', "
					. "x = '".$x."', "
					. "y = '".$y."', "
					. "gold = '".$CONF['start_or']."', "
					. "food = '".$CONF['start_champs']."', "
					. "mat = '".$CONF['start_mat']."', "
					. "craft = '".$CONF['start_magie']."', "
					. "peasant = '".$CONF['start_paysans']."', "
					. "etat = '1', "
					. "cases_usuable = '".$CONF['start_cases_1']."', "
					. "cases_notusuable = '".$CONF['start_cases_2']."', "
					. "cases_total = '".$CONF['start_cases_tot']."', "
					. "type_province = '1', "
					. "satisfaction = '80'; ";
					sql_query($Ins);

					$ProvinceId = mysql_insert_id();

					//Crée la table des paysans
					$sqlpaysans = "INSERT INTO `temp_paysans`  SET "
					. "id_joueur = '".$_SESSION['id_joueur']."', "
					. "section = '0', "
					. "nombre = '".$CONF['start_paysans']."', "
					. "id_province = '".$ProvinceId."', "
					. "esclave = 'N'";
					sql_query($sqlpaysans) ;

					$Message = "Votre nouvelle province a été créée!";

				}
				else
				{//No...
					$Message = bw_error("Vous n'avez pas assez de ressources ou de paysans disponnibles!");
				}
			}

		}
	}
}

$sql = "SELECT name, type_province,  puissance, victoires, pertes, satisfaction  FROM provinces WHERE id = '".$_SESSION['id_province']."' AND id_joueur = '".$_SESSION['id_joueur']."'";
$req = sql_query($sql);
$res = sql_object($req);

(isset($Message) ? bw_fieldset("Information", $Message) : '');

bw_f_start($res->name, "", "left");
echo "			<strong>Type de province:</strong> ".bw_type_province($res->type_province)."<br />\n";
echo "			<strong>Satisfaction:</strong> ".$res->satisfaction."%<br />\n";
echo "			<strong>Puissance:</strong> ".$res->puissance."<br />\n";
echo "			<strong>Victoires/Pertes:</strong> ".$res->victoires."/".$res->pertes."<br />\n";
echo "			<FORM METHOD=\"POST\" ACTION=\"?p=province&do=name\">\n";
echo "				<INPUT TYPE=\"text\" NAME=\"nomprovince\" value=\"".$res->name."\">\n";
echo "				<INPUT TYPE=\"submit\" value=\"Changer le nom\">\n";
echo "			</FORM>\n";
echo "			<br />\n";
bw_f_end();
		
echo "			<fieldset>\n";
echo "				<legend>Passer sur une autre province</legend>\n";
echo "				<strong>Nom[x,y]:</strong><br />\n";

$req = sql_query("SELECT `name`, `id`, `x`, `y` FROM provinces WHERE id_joueur = '".$_SESSION['id_joueur']."'");
while($res = mysql_fetch_array($req))
{
	echo "				<a href=\"?p=index&idprovince=".$res['id']."\">".$res['name']."</a>[".$res['x']."][".$res['y']."]<br />\n";
}
echo "			</fieldset><br />\n";

if(bw_batiavailable("crechepourenfants", false) || $Joueur->race == 4)
{
?>
	<fieldset>
		<legend>Crèche pour enfant</legend>
			<?php if(isset($MessageCreche)) echo $MessageCreche; ?>
			Pour la somme de <?php echo $CONF['province_gold']; ?> pièces d'or, 
			<?php echo $CONF['province_food']; ?> sacs de nouritures, 
			<?php echo $CONF['province_mat']; ?> matériaux, 
			<?php echo $CONF['province_craft']; ?> fioles de magie
			ainsi que <?php echo $CONF['province_pesants']; ?> paysans
			vous pouvez posseder, aléatoirement, une nouvelle province.<br />

			<form method="post" action="index.php?p=province&do=new">
				<input type="hidden" name="go" value="new">
				<input type="submit" value="Étendre mon Royaume!">
			</form>
	</fieldset>
<?php
}
//echo "<img src=\"plan_province_image.php\" />";

bw_tableau_end();
?>