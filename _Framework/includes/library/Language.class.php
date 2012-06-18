<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Language extends Library
{
	/**
	 * Current user-language
	 * 
	 * @var String
	 */
	private $currentLanguage;
	
	/**
	 * Current country-code
	 * 
	 * @var String
	 */
	private $currentCountryCode;
	
	/**
	 * Default file-extension
	 * 
	 * @var String
	 */
	private $defaultFileExtension = 'PHP';
	
	/**
	 * Currently loaded language-files
	 * 
	 * @var Array
	 */
	private $loadedLanguageFiles = array();
	
	/**
	 * Language key-value pairs
	 * 
	 * @var Object
	 */
	private $languageValues = null;
	
	
	/**
	 * Init Language
	 *
	 * @access public
	 */
	public function __construct()
	{
		$this->client = Library::load('Client');
		$this->currentLanguage = Settings::get('defaultLanguage');
		$this->currentCountryCode = Settings::get('defaultCountryCode');
		$this->languageValues = new stdClass();
	}
	
	/**
	 * Set default file-extension
	 * 
	 * @param String $defaultFileExtension Default file-extension
	 */
	public function setDefaultFileExtension($defaultFileExtension)
	{
		$this->defaultFileExtension = $defaultFileExtension;
	}
	
	/**
	 * Set current language
	 *
	 * @param String $language 		language
	 * @param String $countryCode	countryCode (optional)
	 * @return Language self-instance
	 */
	public function setLanguage($language, $countryCode = '')
	{
		$this->currentLanguage = strtolower($language);
		$this->client->setUserData('language', $this->currentLanguage);
		
		if (!empty($countryCode))
		{
			$this->currentCountryCode = strtoupper($this->currentLanguage);
			$this->client->setUserData('countryCode', $this->currentCountryCode);
		}
		
		return $this;
	}
	
	/**
	 * Get current language
	 * 
	 * @return String Current language
	 */
	public function getLanguage()
	{
		return $this->currentLanguage;
	}
	
	/**
	 * Load module language-file
	 * 
	 * @param String	$module		Module					(Optional)
	 * @param String	$subModule	Sub-Module				(Optional)
	 * @param String 	$type		Type of language-file	(Optional)
	 * @return Language Language-instance
	 */
	public function loadModuleFile($module = '', $subModule = '', $type = 'PHP')
	{
		// Get default settings
		if (empty($module))
		{
			$module = Library::load('Request')->getModule();
		}
		
		if (empty($subModule))
		{
			$subModule = '';
		}
		
		
		// Load file
		require Settings::get('appPath').'modules/'.$module.'/languages/'.$this->currentCountryCode.'_'.$this->currentLanguage.(empty($subModule) ? '' : '_'.$subModule).'.'.strtolower($this->defaultFileExtension);
		
		switch ($type)
		{
			case 'PHP':
			{
				foreach ($language as $key => $value)
				{
					$this->languageValues->$key = $value;
				}
				break;
			}
					
			default:
			Debug::logError('Type('.$type.') is not supported');
		}
	
		
		// Provide method-chaining
		return $this;
	}
	
	/**
	 * Returns all language-values as key-value pair
	 * 
	 * @param String Return-type (array or object) 
	 * @return Object Language-values
	 */
	public function getAll($returnType = 'object')
	{
		if ($returnType == 'array')
		{
			return (array) $this->languageValues;
		}	
		
		return $this->languageValues;
	}
}

?>