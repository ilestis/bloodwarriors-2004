<?php
session_start();

$Do = (isset($_GET['do']) ? htmlentities($_GET['do']) : 'rien');

if($Do == 'makerandom') {
	$length = 6; // Longueur de la cha�ne g�n�r�e en image
	$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ234567890abcdefghijklnopqrstuvwxyz$@#+%&/()=?![]{}~'; // Liste des caract�res possibles
	$nb_characters = strlen($alphabet); // Nombre de caract�res possibles
	$espace = 17; //Espace pour chaque lettre

	// La variable code contient la cha�ne qui sera g�n�r�e en image
	$string = '';
	for($i = 0; $i < $length; ++$i)
	{
		$string .= $alphabet[mt_rand(0, $nb_characters-1)];
	}

	// R�cup�ration de la longueur de la chaine � afficher
	$str_length = strlen($string);

	// Enregistre la valeur en session
	$_SESSION['subsribe_img_code'] = $string;

	// Cr�ation de la zone image en fonction de la longueur de texte � afficher
	$image = imagecreatetruecolor(20 * $str_length, 70);

	// Cr�ation du fond de l'image
	for($x = 0; $x < imagesx($image); ++$x)
	{
		for($y = 0; $y < imagesy($image); ++$y)
		{
			if (mt_rand(1,5) == 4 )
			{
				$vred = mt_rand(0, 100);
				$vgreen = mt_rand(0, 100);
				$vblue = mt_rand(0, 100);
			}
			else
			{
				$vred = mt_rand(100, 150);
				$vgreen = mt_rand(100, 150);
				$vblue = mt_rand(100, 150);
			}

			// Allocation d'une couleur au fond
			$color = imagecolorallocate($image, $vred, $vgreen, $vblue);

			// Affichage d'un pixel ayant la couleur du fond
			 imagesetpixel($image, $x, $y, $color);

			// Suppression de la couleur du fond allou�e
			 imagecolordeallocate($image, $color);
		}
	}

	// Cr�ation de la bordure
	$vred = mt_rand(0, 240);
	$vgreen = mt_rand(0, 240);
	$vblue = mt_rand(0, 240);

	// Allocation d'une couleur � la bordure
	$color = imagecolorallocate($image, $vred, $vgreen, $vblue);

	// Trac� de la bordure
	 imagerectangle($image, 0, 0, imagesx($image)-1 , imagesy($image)-1, $color);

	// Suppression la couleur de la bordure allou�e
	 imagecolordeallocate($image, $color);

	// Cr�ation du texte
	for($i = 0; $i < $str_length; ++$i)
	{
		$vred = mt_rand(150, 240);
		$vgreen = mt_rand(150, 240);
		$vblue = mt_rand(150, 240);

		$size = mt_rand(20, 30);
		$angle = mt_rand(-10, 20);
		$x = 13 + (15 * $i);
		$y = mt_rand(30, imagesy($image) - 10);
		$color = imagecolorallocate($image, $vred, $vgreen, $vblue);
		$font = "./comic.ttf";
		
		// Dessin du texte
		imagettftext($image, $size, $angle, $x, $y, $color, $font, $string[$i]);

		// Suppression de la couleur du texte allou�e
		imagecolordeallocate($image, $color);
	}

	// Cr�ation de l'image compl�te au format PNG
	header("Content-type: image/png");
	imagepng($image);
}
?>