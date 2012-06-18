<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class memory extends core
{
	/**
	 * Memcache-Instanz
	 * 
	 * @access private
	 * @var object
	 */
	private $instance;
		
	
	public function __construct()
	{
		if (is_array(core::$settings['memcache_hosts']) && isset(core::$settings['memcache_hosts'][0]))
		{
			$this->instance = new memcache();
			
			foreach (core::$settings['memcache_hosts'] as $server)
			{
				$this->instance->addServer($server['ip'], $server['port']);
			}
		
		} else {
			
			trigger_error('memcache-server not available');
		}
	}
	
	public function __destruct()
	{
		debug::print_array($this->instance->getExtendedStats());
		$this->instance->close();
	}
	
	public function save($key, $content, $lifetime)
	{
		$compress = false;
		$this->instance->set($key, $content, $compress, $lifetime);
	}	
	
	public function exists($key)
	{
		return ($this->instance->get($key) ? true : false);
	}
	
	public function get($key)
	{
		return $this->instance->get($key);
	}
	
	public function delete($key)
	{
		$this->instance->delete($key);
	}
	
	public function delete_complete()
	{
		$this->instance->flush();
	}
	
	public function createStorage()
	{
		
	}
}

?>