<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Helper
{
	/**
	* Load helper
	*
	* @access public
	* @param string $class Class-name
	* @param string $name	Instance-name
	* @return object Helper-instance
	*/	
	public static function load($class, $name = '')
	{
		return Core::loadClass($class, 'helper', $name);
	}
}

?>