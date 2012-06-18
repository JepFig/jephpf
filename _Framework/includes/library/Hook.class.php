<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Hook
{
	/**
	 * Hooks
	 * 
	 * @access private
	 * @var array
	 */
	private $hooks = array();
	
	
	/**
	 * Register Hook
	 * 
	 * @access public
	 * @param string $name		Name
	 * @param string $class		Class
	 * @param string $method	Method
	 * @param array	 $params	Params
	 */
	public function register($name, $class, $method, $params = array())
	{
		$this->hooks[$name] = array('name' => $name, 'class' => $class, 'method' => $method, 'params' => $params);
	}
	
	/**
	 * Unregister Hook
	 * 
	 * @access public
	 * @param string $name Name
	 */
	public function unregister($name)
	{
		unset($this->hooks[$name]);
	}
	
	/**
	 * Execute Hook
	 * 
	 * @access public
	 * @param string $name Name
	 */
	public function execute($name)
	{
		if (isset($this->hooks[$name]))
		{
			$hook = $this->hooks[$name];
			Core::loadClass($hook['class'], 'hook')->$hook['method']($hook['params']);
		}
	}
}

?>