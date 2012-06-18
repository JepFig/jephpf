<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class FileEngine
{
	/**
	 * Last Key
	 * 
	 * @access private
	 * @var string
	 */
	private $lastKey = '';
	
	/**
	 * Last Value
	 * 
	 * @access public
	 * @var string
	 */
	private $lastValue = '';
	
	
	/**
	* Save Value
	*
	* @access public
	* @param string		$key		Key
	* @param string		$value		Value
	* @param int		$lifeTime	Cache-LifeTime
	*/	
	public function save($key, $value, $lifetime)
	{
		$content = Helper::load('ArrayHelper')->arrayToJSON(array('fileTime' => time(), 'lifeTime' => $lifetime, 'value' => $value));
		Core::load('File')->save($this->createCacheDir($key), $content);
	}

	/**
	* Get Value if exists
	*
	* @access public
	* @param string 	$key 		Key
	* @param int		$lifeTime	Cache-LifeTime	(optional)
	* @param bool		$renewCache
	* @return mixed Value or false if not exists
	*/	
	public function get($key, $lifeTime, $renewCache)
	{
		if ($key == $this->lastKey)
		{
			return $this->lastValue;
		}
		
		$cacheDir = $this->createCacheDir($key);
		
		if (file_exists($cacheDir))
		{
			$content = Core::load('File')->load($cacheDir);
			$content = Helper::load('ArrayHelper')->jsonToArray($content);
			
			if ($content['fileTime'] <= (time() - ($lifeTime ? $lifeTime : $content['lifeTime'])))
			{
				$this->delete($key);
				return false;
			}
			
			if ($renewCache)
			{
				$this->save($key, $content['value'], ($lifeTime ? $lifeTime : $content['lifeTime']));
			}
			
			$this->lastKey = $key;
			$this->lastValue = $content['value'];
			
			return $content['value'];
		}
		
		return false;
	}
	
	/**
	 * Delete cache-file
	 * 
	 * @access public
	 * @param string $key Key
	 */
	public function delete($key)
	{
		unlink($this->createCacheDir($key));
	}
	
	/**
	 * Create dir to cache-file
	 * 
	 * @access private
	 * @param string $key Key
	 * @return string cache-file
	 */
	private function createCacheDir($key)
	{
		$key = md5($key);
		
		return Settings::get('appPath').'cache/stored/'.$key[0].'/'.$key[1].'/'.$key.'.c';
	}
	
	/**
	 * Clear file-system
	 * 
	 * @access public
	 */
	public function deleteCompleteCache()
	{
		$this->deleteDir(Settings::get('appPath').'cache/stored');
		$this->createStorage();
	}
	
	/**
	 * Delete dir
	 * 
	 * @access private
	 * @param string $path Dir to delete
	 */
	private function deleteDir($path)
	{
		$list = array_diff(scandir($path), array('.', '..'));
		
		foreach ($list as $value) 
		{
			$file = $path.'/'.$value;
			
			if (is_dir($file)) 
			{ 
				$this->deleteDir($file); 
				
			} else { 
				
				unlink($file); 
			}
		}
		
		return rmdir($path);
	}
	
	/**
	 * Create storage on file-system 
	 * 
	 * @access public
	 */
	public function createStorage()
	{
		$chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$cacheFolder = Settings::get('appPath').'cache/stored/';
		
		mkdir($cacheFolder);
		
		foreach ($chars as $dir)
		{
			mkdir($cacheFolder.$dir, 0777);
		
			foreach($chars as $subDir)
			{
				mkdir($cacheFolder.$dir.'/'.$subDir, 0777);
			}
		}		
	}	
}

?>