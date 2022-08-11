<?php
//connexion à la base de donnée

$var = 5;

if($var == 1)
{
	mysql_connect('localhost', 'root', '');
	mysql_select_db('bw');
}
elseif($var == 2)
{
	mysql_connect('localhost', 'root', '');
	mysql_select_db('bwovh');
}
elseif($var == 3)
{
	mysql_connect('localhost', 'root', '');
	mysql_select_db('bw_beta');
}
elseif($var == 4)
{
	mysql_connect('sql10', 'december', 'DwgWBnnv');
	mysql_select_db('december');
}
?>