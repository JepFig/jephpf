<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Model extends CoreClassRegistry
{
	/**
	 * Init Model
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
		$this->file = Core::load('File');
		$this->response = Core::load('Response');
		$this->template = Core::load('Template');
		
		$instances = Core::getInstance();
		
		foreach ($instances as $name => $instance)
		{
			$this->$name = $instance;
		}
	}
	
	/**
	 * Load model
	 * 
	 * @access public
	 * @param string $class Class-name
	 * @param string $name	Instance-name
	 * @return object Model-instance
	 */
	public static function load($class, $name = '')
	{
		return Core::loadClass($class, 'model', $name);
	}	
}

?>