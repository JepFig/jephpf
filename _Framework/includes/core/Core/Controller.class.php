<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Controller extends CoreClassRegistry
{
	/**
	 * Init Controller
	 * 
	 * @access public
	 */
	public function __construct()
	{
		self::loader();
	}
	
	/**
	 * Load instances
	 * 
	 * @access public
	 */
	public function loader()
	{
		$this->File = Core::load('File');
		$this->Response = Core::load('Response');
		$this->Template = Core::load('Template');
		
		$instances = Core::getInstance();
		
		foreach ($instances as $name => $instance)
		{
			$this->$name = $instance;
		}
	}
}

?>