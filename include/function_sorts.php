<?php
# Verifie si on a le droit de lancer ce sort
function sort_available($batID, $raceIDs, $myRace) {
	$Allowed = false;

	# On a besoin d'un bâtiment?	
	if($batID != 0) {
		# Verifie si on l'a
		if(bw_batiavailable($batID)) {
			$Allowed = true; // C'est bon
		}
	} else { # Pas besoin de bâtiment, donc c'est bon
		$Allowed = true;
	}
	
	# Si on a le droit et que y'a une race
	if($Allowed && !empty($raceIDs)) {
		# Si on trouve notre race dans la liste des races acceptées
		if(strpos($raceIDs, $myRace) !== false) {
			$Allowed = true; // Alors c'est bon
		} else {
			$Allowed = false;
		}
	}
	return $Allowed;
}


?>