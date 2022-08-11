<?php


function sql_query($req)
{
	$ret = mysql_query($req) or die("<strong>Erreur sql!</strong><br />".$req."<br />".mysql_error());
	return $ret;
}

function redirect($page, $time = 0)
{
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<HTML>
	<HEAD>
		<TITLE>Loading ...</TITLE>
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1" />
	   <meta http-equiv=refresh content=<?php echo $time; ?>;URL=<?php echo $page; ?> />
	</HEAD>
	<?php
	exit;
}

function sql_connect($user, $psw, $host)
{
	mysql_connect($host, $user, $psw);
}

function err($mes)
{
	$ret = "<font color=\"#FF0000\">".$mes."</font>\n";
	return $ret;
}

function clean($text)
{
	$text = htmlentities($text);
	$text = trim($text);
	$text = addslashes($text);
	return $text;

}
?>