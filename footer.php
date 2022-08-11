<?php
echo "		</div>\n\n";


//Fin d'exécution
$Temp = explode(" ",microtime()); 
$MicroTime['end'] = $Temp[1]+$Temp[0];
$MicroTime['final'] = $MicroTime['end'] - $MicroTime['debut'];

echo "
	<br style=\"clear:both;\" />

	</div>
	<!-- Footer -->

	<div class=\"footer\">
		<a href=\"mailto:".$CONF['game_admin_mail']."\">Contacter l'administration</a> 
		| Blood Warriors Version ".$CONF['game_version']." 
		| <a href=\"#top\">Haut de la page</a> 
		| Butineur conseillé: <a href=\"http://www.mozilla-europe.org/fr/products/firefox/\">Firefox</a>. 
		| (<span style=\"font-size: 8pt\">Exéc: ".$MicroTime['final']."</span>)
	</div>
	<!-- //Footer -->

</div>
<!-- Fermeture des balises -->
</body>
</html>\n";
?>