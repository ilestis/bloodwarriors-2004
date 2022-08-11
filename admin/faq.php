<?php
require('adminheader.php');


echo "<h2>Modification de la FAQ</h2>\n";


$flag = 0;
if(isset($_GET['do']))
{
	if($_GET['do'] == 'view' && isset($_GET['f']))
	{
		$File = clean($_GET['f']);
		$url = './public/faq/'.$File.'.txt';

		if(is_file($url))
		{
			$flag = 1;
			?>
			<table class="newtable"><tr>
				<td class="newtitre"><?php echo clean($_GET['f']); ?> (<a href="?p=admin_faq">Revenir à la FAQ</a>)</td>
			</tr><tr>
				<td class="newcontenu">
					<br />
					<form method="post" action="?p=admin_faq&do=edit&f=<?php echo $File; ?>">
						<textarea name="text" rows="15" cols="60"><?php require($url); ?></textarea><br />
						<input type="submit" value="Enregistrer">

					</form>
				
				</td>
			</tr><tr>
					<td class="newfin">&nbsp;</td>
			</tr></table>

			<?php
		}
	}
	elseif($_GET['do'] == 'edit' && isset($_GET['f']))
	{
		//On save
		$File = clean($_GET['f']);
		$url = './public/faq/'.$File.'.txt';

		if(is_file($url))
		{
			$Text = clean($_POST['text']);
			$handle = fopen($url, 'w');
			fwrite($handle, $Text);
			fclose($handle);

			echo "Section mise à jour!<br />\n";

		}
		else
		{
			echo "Cette section n'existe pas!<br />\n";
		}
	}
}

	
if($flag == 0)
{
	?>
	<table class="newtable"><tr>
		<td class="newtitre">Sections</td>
	</tr><tr>
		<td class="newcontenu">
			<?php
			//Prend tous les possibilités
			$url = './public/faq/';
			$while = opendir($url);
			while(false !== $fichier = readdir($while))
			{
				
				if(is_file($url.'/'.$fichier)) //File
				{
					$nom = explode('.', $fichier);
					if($nom[1] == 'txt') //C'est un fichier txt alors on l'affiche
						$files[]=$nom[0]; 
							
				}
			}
			
			//Reclasse l'array
			natcasesort($files);
			
			foreach($files as $tmp)
			{
				echo "<a href=\"?p=admin_faq&do=view&f=".$tmp."\">".$tmp."</a><br />\n";
			}
			
			?>
		</td>
	</tr><tr>
		<td class="newfin">&nbsp;</td>
	</tr></table>
<?php
}
?>