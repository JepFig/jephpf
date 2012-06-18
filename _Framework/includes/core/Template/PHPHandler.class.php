<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }

							
class PHPHandler
{
	/**
	 * Template-Data
	 * 
	 * @access private
	 * @var array
	 */
	private static $data = array();
	
	
	/**
	 * Get Template-Extension
	 * 
	 * @access public
	 * @return string Template-Extension
	 */
	public function getExtension()
	{
		return 'php';
	}
	
	/**
	* Assign template-key with value
	*
	* @access public
	* @param string	$key	template-key
	* @param string	$value	data
	*/	
	public function assign($key, $value)
	{
		self::$data[$key] = $value;
	}
	
	/**
	 * Show Template
	 * 
	 * @access public
	 * @param string $file Template-file
	 * @return string Template
	 */
	public function fetch($file)
	{
		require_once Settings::get('appPath').'includes/TemplateFunctions.php';
		require_once Settings::get('appPath').'static/templates/'.Settings::get('template').'/TemplateFunctions.php';
		require_once 'TemplateFunctions.php';
		require $file;
		
		$buffer = Core::load('Buffer');
		$class = explode('/', $file);
		$class = str_replace('.'.$this->getExtension(), '', $class[(count($class) - 1)]).'Template';
		$buffer->start();
		$template = new $class;
		
		foreach (self::$data as $key => $value)
		{
			$template->$key = $value;
		}
		
		$template->index();
		$buffer->end('template');
		unset($template);
		
		return $buffer->getContent('template');
	}
	
	/**
	 * Return value by key
	 * 
	 * @access public
	 * @param string	$key	Key
	 * @param mixed		$return	Alternative return-value	(optional)
	 * @return mixed data
	 */
	public function get($key, $return = null)
	{
		if (!isset(self::$data[$key]))
		{
			return $return;
		}
		
		return self::$data[$key];
	}
}

?>