<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }

class CacheException extends Exception {}

class Cache
{
	/**
	 * Engine-Instances
	 * 
	 * @access private
	 * @var array
	 */
	private $instances = array();
	
	/**
	 * Cache-Engine
	 * 
	 * @access private
	 * @var string
	 */
	private $engine;
	
	
	/**
	 * Init Cache
	 * 
	 * @access public
	 */
	public function __construct()
	{	
		Settings::load('Cache.class');
		$this->setEngine(Settings::get('defaultCacheEngine'));
	}
	
	/**
	 * Set Engine
	 * 
	 * @access public
	 * @param string $engine Cache-Engine
	 */
	public function setEngine($engine)
	{
		$this->engine = $engine;
		
		if (array_key_exists($this->engine, $this->instances) == false)
		{
			require Settings::get('corePath').'includes/library/Cache/'.$this->engine.'Engine.class.php';
				
			$engine = $engine.'Engine';
			$this->instances[$this->engine] = new $engine;
		}
		
		return $this->instances[$this->engine];		
	}
	
	/**
	 * Return Cache-Instance
	 * 
	 * @access public
	 * @return object Cache-Instance
	 */
	public function getEngine()
	{
		return $this->instances[$this->engine];
	}

	/**
	 * Save Value
	 * 
	 * @access public
	 * @param string	$key		Key
	 * @param string	$value		Value
	 * @param int		$lifeTime	Cache-LifeTime	(optional)
	 */
	public function save($key, $value, $lifeTime = false)
	{
		if (Settings::get('globalCaching'))
		{
			return $this->getEngine()->get($key, $lifeTime, $renewCache);
		}
				
		if ($lifeTime == false)
		{
			$lifeTime = Settings::get('defaultCacheLifeTime');
		}
		
		if (Settings::get('globalCaching'))
		{
			$this->getEngine()->save($key, $value, $lifeTime);
		}
	}	
	
	/**
	 * Get Value if exists
	 * 
	 * @access public
	 * @param string 	$key 		Key
	 * @param int		$lifeTime	Cache-LifeTime	(optional)
	 * @param bool		$renewCache	Renew cache 	(optional)
	 * @return mixed Value or false if not exists
	 */
	public function get($key, $lifeTime = false, $renewCache = false)
	{
		if (Settings::get('globalCaching'))
		{		
			return $this->getEngine()->get($key, $lifeTime, $renewCache);
		}
		
		return false;
	}
	
	/**
	 * Delete Key
	 * 
	 * @access public
	 * @param string $key Key
	 */
	public function delete($key)
	{
		if (Settings::get('globalCaching'))
		{
			$this->getEngine()->delete($key);
		}
	}
	
	/**
	 * Delete complete cache
	 * 
	 * @access public
	 */
	public function deleteCompleteCache()
	{
		$this->getEngine()->deleteCompleteCache();
	}
	
	/**
	 * Create storage
	 * 
	 * @access public
	 */
	public function createStorage()
	{
		$this->getEngine()->createStorage();
	}
}

?>