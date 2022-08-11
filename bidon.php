
<?php


echo "<pre>\n";
print_r($_POST);
echo "</pre>\n";


foreach($_POST as $key => $value)
{
	if(substr($key, 0, 4) == 'lst_')
	{
		if($value != 0)
		{
			echo "Oui! ".$key." - ".$value."<br />\n";
		} else {
			echo "No: value : $value.<br />";
		}
	} else {
		echo substr($key, 0, 4)."<br />";
	}
}
 ?>