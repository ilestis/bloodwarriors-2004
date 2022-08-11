<?php
require('./prepend.php');

if(!isset($_SESSION['user'])) {
	require('./log.php');
	exit;
}

sql_connect($_SESSION['user'], $_SESSION['psw'], $_SESSION['host']);


//Les bdd
if(isset($_POST['db'])) {
	if($_POST['db'] != "...") {
		$db_selected = mysql_select_db($_POST['db']);
		if ($db_selected) {
			$_SESSION['db'] = $_POST['db'];
		}
	} else { $_SESSION['db'] = "..."; }
}
echo "<table style=\"border: 0px; width:100%; border-collapse: collapse;\">\n";
echo "<tr><td style=\"width:200px; border: 1px solid red;\" valign=\"top\">\n";
echo "<form method=\"post\" action=\"?\" id=\"select_db\">\n";
echo "<select name=\"db\" onchange=\"this.form.submit();\">\n";
echo "	<option value=\"...\">...</option>\n";
$req = sql_query("SHOW DATABASES");
while($res = mysql_fetch_row($req)) {
	echo "	<option value=\"".$res[0]."\" ".($_SESSION['db'] == $res[0] ? "selected=\"selected\"" : '').">".$res[0]."</option>\n";
}
echo "</select><input type=\"submit\" value=\"Go\" /><br />\n";
echo "</form>\n";

if(isset($_SESSION['db']) && $_SESSION['db'] != "...") {
	mysql_select_db($_SESSION['db']);

	$req = sql_query("SHOW TABLES");
	while($res = mysql_fetch_row($req)) {
		echo "<a href=\"?table=".$res[0]."\">".$res[0]."</a><br />\n";
	}
}
echo "</td>\n";

echo "<td style=\"border: 1px solid red;\" valign=\"top\">\n";

//On a pas de db sélectionné: en créer?
if(!isset($_SESSION['db']) || $_SESSION['db'] == "...") {
	echo "<h1>Créer une base de donnée</h1>\n";

	if(isset($_POST['db_name'])) {
		if(!mysql_select_db($_POST['db_name'])) {
			$soru = 'CREATE DATABASE '.$_POST['db_name'];
			if(!sql_query($soru)) {
				$_SESSION['db'] = $_POST['db_name'];
				redirect('?'); 
				exit;
			} else { 
				echo err("Impossible de créer la base de donnée"); 
			}
		} else { 
			echo err("Une base possède déjà ce nom!"); 
		}
	}

	echo "<form method=\"post\" action=\"?\">\n";
	echo "Nom: <input type=\"text\" name=\"db_name\" maxlength=\"50\" /> <input type=\"submit\" value=\"Créer\" /><br />\n";
	echo "</form>\n";

} else {

	if(!isset($_GET['table'])) {
		if(isset($_GET['do'])) {
			if($_GET['do'] == "del") {
				$drop = "DROP DATABASE `".$_SESSION['db']."`;";
				sql_query($drop);
				redirect("?"); exit;
			} 
			elseif($_GET['do'] == "create") {
				//echo "<pre>";print_r($_POST); echo "</pre>";
				$Up = "CREATE TABLE `".clean($_POST['db_name'])."` (";
				for($i=0; $i<$_POST['db_fields']; $i++) {
					if(!empty($_POST['field_'.$i.'_name'])) {
						if($i>0) $Up .= ", ";
						$Up .= "`".clean($_POST['field_'.$i.'_name'])."` ".
							$_POST['field_'.$i.'_type'].
							(!empty($_POST['field_'.$i.'_value']) ? "(".stripslashes($_POST['field_'.$i.'_value']).")" : '').
							(!empty($_POST['field_'.$i.'_attributs']) ? " ".clean($_POST['field_'.$i.'_attributs']) : '')." ".
							clean($_POST['field_'.$i.'_null']).
							(!empty($_POST['field_'.$i.'_default']) ? "DEFAULT '".clean($_POST['field_'.$i.'_default'])."'" : '')." ".
							(!empty($_POST['field_'.$i.'_extra']) ? " ".clean($_POST['field_'.$i.'_extra']) : '')." ".
							(!empty($_POST['field_'.$i.'_plus']) ? " ".clean($_POST['field_'.$i.'_plus']) : '');
					}
				}
				$Up .= ") ";
				$Up .= ($_POST['db_comment'] != "" ? "COMMENT = '".clean($_POST['db_comment'])."'" : '');
				if(mysql_query($Up)) {
					echo "<h2>Table créée!</h2>
					".$Up."<br />
					";
					redirect("?");
				} else {
					echo "<h2>Erreur SQL!</h2>
					".mysql_error()."<br />";
				}
			}
		}
		
		echo "<h1>BDD ".$_SESSION['db']."</h1>";

		if(!isset($_GET['s'])) {
			echo "<a href=\"?do=del\">Supprimer la table</a><br />\n";
			echo "<fieldset><legend>Créer une nouvelle table</legend>
				<form method=\"post\" action=\"?s=create\">
				Nom: <input type=\"text\" name=\"db_name\" />
				Nombre de champs: <input type=\"text\" name=\"db_fields\" size=\"3\" maxlenght=\"2\" />
				<input type=\"submit\" value=\"Exécuter\" />
				</form>
				</fieldset>
				";
		} elseif($_GET['s'] == 'create') {
			echo "<fieldset><legend>Créer une nouvelle table :</legend>
				<form method=\"post\" action=\"?do=create\">
				<table>
				<tr>
					<th>Nom</th>
					<th>Type</th>
					<th>Taille/Valeur</th>
					<th>Attribut</th>
					<th>Null</th>
					<th>Défaut</th>
					<th>Extra</th>
					<th>Primaire</th>
					<th>Index</th>
					<th>Unique</th>
					<th>-</th>
					<th>Text Entier</th>
				</tr>";
				for($i = 0; $i<$_POST['db_fields']; $i++) {
					echo "
				<tr>
					<td><input type=\"text\" name=\"field_".$i."_name\" /></td>
					<td><select name=\"field_".$i."_type\">
						<option value=\"VARCHAR\">VARCHAR</option>
						<option value=\"TINYINT\">TINYINT</option>
						<option value=\"TEXT\">TEXT</option>
						<option value=\"DATE\">DATE</option>
						<option value=\"SMALLINT\">SMALLINT</option>

						<option value=\"MEDIUMINT\">MEDIUMINT</option>
						<option value=\"INT\">INT</option>
						<option value=\"BIGINT\">BIGINT</option>
						<option value=\"FLOAT\">FLOAT</option>
						<option value=\"DOUBLE\">DOUBLE</option>
						<option value=\"DECIMAL\">DECIMAL</option>

						<option value=\"DATETIME\">DATETIME</option>
						<option value=\"TIMESTAMP\">TIMESTAMP</option>
						<option value=\"TIME\">TIME</option>
						<option value=\"YEAR\">YEAR</option>
						<option value=\"CHAR\">CHAR</option>
						<option value=\"TINYBLOB\">TINYBLOB</option>

						<option value=\"TINYTEXT\">TINYTEXT</option>
						<option value=\"BLOB\">BLOB</option>
						<option value=\"MEDIUMBLOB\">MEDIUMBLOB</option>
						<option value=\"MEDIUMTEXT\">MEDIUMTEXT</option>
						<option value=\"LONGBLOB\">LONGBLOB</option>
						<option value=\"LONGTEXT\">LONGTEXT</option>

						<option value=\"ENUM\">ENUM</option>
						<option value=\"SET\">SET</option>
						<option value=\"BOOL\">BOOL</option>
						<option value=\"BINARY\">BINARY</option>
						<option value=\"VARBINARY\">VARBINARY</option></select>
						</td>
					<td><input type=\"text\" name=\"field_".$i."_value\" /></td>
					<td><select name=\"field_".$i."_attributs\" />
						<option value=\"\" selected=\"selected\"></option>
						<option value=\"UNSIGNED\">UNSIGNED</option>
						<option value=\"UNSIGNED ZEROFILL\">UNSIGNED ZEROFILL</option>
						<option value=\"ON UPDATE CURRENT_TIMESTAMP\">ON UPDATE CURRENT_TIMESTAMP</option></select>
					</td>
					<td><select name=\"field_".$i."_null\" />
						<option value=\"NOT NULL\" selected=\"selected\">NOT NULL</option>
						<option value=\"NULL\">NULL</option></select>
					</td>
					<td><input type=\"text\" name=\"field_".$i."_extra\" /></td>
					<td><select name=\"field_".$i."_extra\">
						<option value=\"\"></option>
						<option value=\"AUTO_INCREMENT\">auto_increment</option></select>
					</td>
					<td><input type=\"radio\" name=\"field_".$i."_plus\" value=\"PRIMARY\" /></td>
					<td><input type=\"radio\" name=\"field_".$i."_plus\" value=\"INDEX\" /></td>
					<td><input type=\"radio\" name=\"field_".$i."_plus\" value=\"UNIQUE\" /></td>
					<td><input type=\"radio\" name=\"field_".$i."_plus\" value=\"\" checked=\"checked\" /></td>
					<td><input type=\"checkbox\" name=\"field_".$i."_fulltext\" value=\"FULLTEXT\" /></td>
				</tr>";
				}
				echo "
				</table><br />
				Commentaire de la table: <input type=\"textbox\" name=\"db_comment\" /><br />
				<input type=\"hidden\" name=\"db_name\" value=\"".$_POST['db_name']."\" />
				<input type=\"hidden\" name=\"db_fields\" value=\"".$_POST['db_fields']."\" />
				<input type=\"submit\" value=\"Exécuter\" />
				</form>
				</fieldset>
				";


		}
	} else {
		$table = $_GET['table'];

		echo "<h1>Table ".$_GET['table']." de ".$_SESSION['db']."</h1>\n";

		if(!isset($_GET['do'])) {
			echo "<table class=\"table\">\n";
			echo "<tr>\n";
			echo "	<th>Champ</th>\n";
			echo "	<th>Type</th>\n";
			echo "	<th>Attributs</th>\n";
			echo "	<th>Null</th>\n";
			echo "	<th>Défaut</th>\n";
			echo "	<th>Extra</th>\n";
			echo "	<th colspan=\"2\">Action</th>\n";
			echo "</tr>\n";

			$sql = "SHOW COLUMNS FROM ".$_GET['table'].";";
			$req = sql_query($sql);
			while($res = mysql_fetch_row($req)) {
				print_r($res)."<br />";
				echo "<tr>\n";
				for($i = 0; $i <= 5; $i++) echo "	<td>".$res[$i]."</td>\n";
				echo "<td><a href=\"?table=".$table."&do=modifie&col=".$res[0]."\">Modifier</a></td>\n";
				echo "<td><a href=\"?table=".$table."&do=delete&col=".$res[0]."\">Delete</a></td>\n";
				echo "</tr>\n";
			}
			echo "</table>\n";
		} elseif($_GET['do'] == 'delete') {
			$sql = "ALTER TABLE `".$_GET['table']."` DROP `".$_GET['col']."`;";
			sql_query($sql);
			redirect("?table=".$_GET['table']);
		} elseif($_GET['do'] == 'modifie') {

		}
	}




}

echo "</td></tr></table>\n";