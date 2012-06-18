<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }

class CoreException extends Exception { }

class Core extends CoreClassRegistry
{	
	/**
	 * Class-objects
	 *
	 * @access private
	 * @var object
	 */
	private static $instances = null;
	
	/**
	 * Config-Files
	 * 
	 * @access private
	 * @var array
	 */
	private static $configFiles = array();
	
	/**
	 * Loaded classes
	 * 
	 * @access private
	 * @var array
	 */
	private static $classesLoaded = array();
	
	/**
	 * Class-structure
	 * 
	 * @access private
	 * @var array
	 */
	private static $classStructure = array(
												'core' => array(
																	'Benchmark',
																	'Buffer',
																	'Client',
																	'File',
																	'Request',
																	'Response',
																	'Template'
															),
															
												'helper' => array(
																	'ArrayHelper',
																	'StringHelper',
																	'URLHelper'
															),
												'library' => array(
																	'Cache',
																	'Curl',
																	'Hook',
																	'Image',
																	'Language',
																	'Mail',
																	'Session',
																	'Shortlink',
																	'Sms',
																	'Sql'
															)
										);

	
	/**
	 * Init core
	 *
	 * @access public
	 * @param string  $appPath	path to project
	 */
	public static function init($appPath)
	{ 		
		self::$instances = new stdClass();
		
		###### Required classes
		require 'Core/Controller.class.php';
		require 'Core/Helper.class.php';
		require 'Core/Model.class.php';
		require 'Core/Library.class.php';
		require 'Core/Exception.class.php';
		######		
		
		
		###### Debug
		require 'Debug.class.php';
		self::$instances->debug = Debug::getInstance();
		######
		
		
		###### Settings
		require 'Settings.class.php';
		
		Settings::set('corePath', dirname(dirname(dirname(__FILE__))).'/', true);
		Settings::set('appPath', $appPath, true);
		Settings::set('scriptState', SCRIPT_STATE, true);
		Settings::load(array('_Config', '_Hooks'));
		
		require Settings::get('appPath').'includes/config/_Definitions.php';
		######
			
		
		###### Autoload		
		self::loadClass('Benchmark', 'core', '');
		self::loadClass('Buffer', 'core', '');
		self::loadClass('Client', 'core', '');
		self::loadClass('Request', 'core', '');
		
		require Settings::get('appPath').'includes/config/_Autoload.php';
		
		foreach ($configs as $config)
		{
			Settings::load($config);
		}
		
		foreach ($helper as $class)
		{
			self::loadClass($class, 'helper');
		}
		
		foreach ($libraries as $class)
		{
			self::loadClass($class, 'library');
		}
		
		foreach ($models as $class)
		{
			self::loadClass($class, 'model');
		}
		######
	}
	
	/**
	 * Unset unused instances
	 * 
	 * @access public
	 */
	public static function unload()
	{
		$requiredInstances = array('benchmark', 'response', 'sql');
		
		foreach (self::$instances as $name => $instance)
		{
			if (in_array($name, $requiredInstances) == false)
			{
				self::$instances->$name = null;
			}
		}
	}
	
	/**
	 * Return class-instances
	 * 
	 * @access public
	 * @return object Class-instances
	 */
	public static function getInstance()
	{
		return self::$instances;
	}
	
	/**
	 * Load core-class
	 * 
	 * @access public
	 * @param string $class Class
	 * @param string $name	Instance-name (optional)
	 */
	public static function load($class, $name = '')
	{
		return self::loadClass($class, 'core', $name);
	}
	
	/**
	 * Load & init class
	 * 
	 * @access public
	 * @param string	$class	Class
	 * @param string	$type	Class-Type
	 * @param string	$name	Instance-name	(optional)
	 * @return object instance
	 */
	public static function loadClass($class, $type, $name = '')
	{
		$subPath = '';

		// Subpackage?
		if (stristr($class, '::'))
		{
			$folder = explode('::', $class);
			$class = $folder[(count($folder) - 1)];
			unset($folder[(count($folder) - 1)]);
			$subPath = str_replace($class, '', implode('/', $folder).'/');
		}
		
		$name = (empty($name) ? $class : $name);
		$name = lcfirst($name);
		$class = ucfirst($class);
		
		if (!isset(self::$instances->$name))
		{
			$type = strtolower($type);
			
			switch ($type)
			{
				case 'core':
				case 'helper':
				case 'library':
				{
					if (in_array($class, self::$classStructure[$type]))
					{
						$path = Settings::get('corePath').'includes/'.$type.'/'.$subPath.$class.'.class.php';
						
					} elseif (file_exists(Settings::get('appPath').'includes/'.$type.'/'.$subPath.$class.'.class.php')) {
						
						$path = Settings::get('appPath').'includes/'.$type.'/'.$subPath.$class.'.class.php';
					
					} else {
						
						throw new CoreException('Class '.$class.' ('.$type.') cannot be found not found', Debug::ERROR);
					}
					
					break;
				}		
				
				case 'hook':
				case 'model':
				{
					if (file_exists(Settings::get('appPath').'includes/'.$type.'/'.$subPath.$class.'.class.php')) {
					
						$path = Settings::get('appPath').'includes/'.$type.'/'.$subPath.$class.'.class.php';
							
					} else {
					
						throw new CoreException('Class '.$class.' ('.$type.') cannot be found not found', Debug::ERROR);
					}
					
					break;
				}
				
				default:
				throw new CoreException('Class-type not exists', Debug::ERROR);
			}
			
			if (in_array($class, self::$classesLoaded))
			{
				self::$instances->$name = new $class();
			
			} else {
				
				require_once $path;
				self::$instances->$name = new $class();
				self::$classesLoaded[] = $class;				
			}
		}		
			
		return self::$instances->$name;
	}	
	
	/**
	 * Check if class is already loaded
	 * 
	 * @access public
	 * @param string $class Class
	 * @return bool class loaded?
	 */
	public static function isClassLoaded($class)
	{
		if (in_array($class, self::$classesLoaded))
		{
			return true;
			
		} else {
			
			return false;
		}
	}

	/**
	 * Set max script-runtime
	 *
	 * @access public
	 * @param string $string runtime
	 */
	public static function setRuntime($runtime)
	{
		ini_set('max_execution_time', $runtime); 
		set_time_limit($runtime);
	}
}

?>