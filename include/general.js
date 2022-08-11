function showHide(nom_element) {

	if (document.getElementById(nom_element).style.display == 'none') {
		document.getElementById(nom_element).style.display = ''
	}
	else {
		document.getElementById(nom_element).style.display = 'none'
	}
}

function hide(nom_element) {

   document.getElementById(nom_element).style.display = 'none'
}

function show(nom_element) {

	document.getElementById(nom_element).style.display = ''
}

function popupConnected() {
	window.open('joueursconnectes.php', 'ConnectedPlayers', 'height=200px, width:240px, resizable=yes');
}