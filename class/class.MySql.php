<?php

class sql
{
	var $con_id;
	var $error;
	var $errno;

	function connection($user, $pwd , $alias='', $dbname)
	{
		$this->error = '';
		
		$this->con_id = @mysql_connect($alias, $user, $pwd);
		if (!$this->con_id) {
			$this->setError();
			return false;
		}
		if($this->database($dbname))
			return true;
		return false;
	}

	function database($dbname)
	{
		$db = @mysql_select_db($dbname);
		if(!$db) {
			$this->setError();
			return false;
		} else {
			return true;
		}
	}

	function close()
	{
		if ($this->con_id) {
			mysql_close($this->con_id);
			return true;
		} else {
			return false;
		}
	}

	function select($query,$class='recordset')
	{
		if (!$this->con_id) {
			return false;
		}
		
		if ($class == '' || !class_exists($class)) {
			$class = 'recordset';
		}
		
		$cur = mysql_unbuffered_query($query, $this->con_id);
		
		if ($cur)
		{
			# Insertion dans le reccordset
			$i = 0;
			$arryRes = array();
			while($res = mysql_fetch_row($cur))
			{
				for($j=0; $j<count($res); $j++)
				{
					$arryRes[$i][strtolower(mysql_field_name($cur, $j))] = $res[$j];		
				}
				$i++;
			}
			
			return new $class($arryRes);
		}
		else
		{
			$this->setError();
			return false;
		}
	}

	function execute($query)
	{
		if (!$this->con_id) {
			return false;
		}
		
		$cur = mysql_query($query, $this->con_id);
		
		if (!$cur) {
			$this->setError();
			return false;
		} else {
			return true;
		}
		
	}

	function getLastID()
	{
		if ($this->con_id) {
			return mysql_insert_id($this->con_id);
		} else {
			return false;
		}
	}

	function rowCount()
	{
		if ($this->con_id) {
			return mysql_affected_rows($this->con_id);
		} else {
			return false;
		}
	}


	function sqlQuery($query)
	{
		if ($this->con_id) {
			$ret = mysql_query($query);
			return $ret;
		} else {
			return false;
		}
	}

	function numRows($query)
	{
		if ($this->con_id) {
			$ret = mysql_num_rows($this->sqlQuery($query));
			return $ret;
		} else {
			return false;
		}
	}

	function setError()
	{
		if ($this->con_id) {
			$this->error = mysql_error($this->con_id);
			$this->errno = mysql_errno($this->con_id);
		} else {
			$this->error = (mysql_error() !== false) ? mysql_error() : 'Unknown error';
			$this->errno = (mysql_errno() !== false) ? mysql_errno() : 0;
		}
	}
	
	function error()
	{
		if ($this->error != '') {
			return $this->errno.' - '.$this->error;
		} else {
			return false;
		}
	}

	function escapeStr($str)
	{
		return mysql_escape_string($str);
	}
}
?>