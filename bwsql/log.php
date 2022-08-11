<?php

if(isset($_POST['det'])) {
	//Verifie formulaire
	$_SESSION['user'] = $_POST['user'];
	$_SESSION['psw'] = $_POST['psw'];
	$_SESSION['host'] = $_POST['host'];
	$_SESSION['db'] = '...';
	
	redirect('?');

	exit;
}


echo "<fieldset><legend>Log</legend><form method=\"post\" action=\"?\">\n";
echo "User:<br />\n";
echo "<input type=\"text\" name=\"user\" value=\"".(isset($_POST['user']) ? $_POST['user'] : '')."\" /><br />\n";
echo "PWD:<br />\n";
echo "<input type=\"password\" name=\"psw\" value=\"".(isset($_POST['psw']) ? $_POST['psw'] : '')."\" /><br />\n";
echo "Hoste:<br />\n";
echo "<input type=\"text\" name=\"host\" value=\"".(isset($_POST['host']) ? $_POST['host'] : '')."\" /><br />\n";


echo "<input type=\"hidden\" name=\"det\" value=\"1\" />\n";

echo "<input type=\"submit\" value=\"Go\" /></form></fieldset>\n";