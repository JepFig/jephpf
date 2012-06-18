<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Settings
{	
	/**
	 * Settings
	 *
	 * @access public
	 * @var array
	 */
	private static $settings = array();
	
	/**
	 * Config-Files
	 * 
	 * @access private
	 * @var array
	 */
	private static $configFiles = array();
	
	
	/**
	 * Load config-file
	 * 
	 * @access public
	 * @param mixed $configFiles Configuration-files
	 */
	public static function load($configFiles)
	{
		if (is_array($configFiles) == false)
		{
			$configFilesTemp = $configFiles;
			$configFiles = array();
			$configFiles[0] = $configFilesTemp;
		}
		
		foreach ($configFiles as $configFile)
		{
			if (array_key_exists($configFile, self::$configFiles))
			{
				continue;				
				
			} else {
				
				self::$configFiles[$configFile] = true;
			}
			
			if (file_exists(self::get('appPath').'includes/config/'.self::get('scriptState').'/'.$configFile.'.php'))
			{
				require self::get('appPath').'includes/config/'.self::get('scriptState').'/'.$configFile.'.php';	
				self::migrate(isset($settings) ? $settings : array());
			
			} elseif (file_exists(self::get('appPath').'includes/config/'.$configFile.'.php')) {
			
				require self::get('appPath').'includes/config/'.$configFile.'.php';
				self::migrate(isset($settings) ? $settings : array());
			
			} else {	
				
				Debug::logError('Config ('.$configFile.') not found');	
			}
			
			unset($settings);
		}	
	}
	
	/**
	 * Read config-array and migrate to the system
	 * 
	 * @access private
	 * @param array $settings config-array
	 */
	private static function migrate($settings)
	{
		foreach ($settings as $name => $value)
		{
			if (isset(self::$settings['static'][$name]))
			{
				Debug::logWarning('Setting ('.$name.') already defined');
				
			} else {
				
				self::$settings['static'][$name] = $value;
			}
		}
	}
	
	/**
	 * Get setting
	 * 
	 * @access public
	 * @param string $setting name
	 * @return string value
	 */
	public static function get($setting)
	{
		if (isset(self::$settings['static'][$setting]))
		{
			return self::$settings['static'][$setting];
			
		} elseif (isset(self::$settings['temp'][$setting])) {

			return self::$settings['temp'][$setting];
			
		} else {

			Debug::logWarning('Setting('.$setting.') does not exist');
		}
	}
		
	/**
	 * Save setting
	 * 
	 * @access public
	 * @param String 	$settings	name
	 * @param String 	$value		value
	 */
	public static function set($setting, $value)
	{
		if (isset(self::$settings['static'][$setting]))
		{
			self::$settings['static'][$setting] = $value;
			
		} elseif (isset(self::$settings['static'][$setting]) == false) {

			self::$settings['temp'][$setting] = $value;
			
		} else {	
			
			Debug::logWarning('Setting('.$setting.') does not exist');
		}
	}
}

?>