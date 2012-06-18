<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }

class SessionException extends Exception {}

class Session extends Library
{
	/**
	 * Init Session
	 * 
	 * @access public
	 */
	public function __construct()
	{
		session_start();
	}
	
	/**
	 * Save session
	 * 
	 * @access public
	 * @param string	$key	Identifier
	 * @param mixed		$value	Value
	 */
	public static function save($key, $value)
	{
		$_SESSION[$key] = $value;
	}
	
	/**
	 * Get session-value
	 * 
	 * @access public
	 * @param string $key	Identifiert
	 * @throws SessionException
	 * @return mixed Value
	 */
	public static function get($key)
	{
		if (isset($_SESSION[$key]))
		{
			return $_SESSION[$key];
			
		} else {
			
			throw new SessionException('Session('.$key.') not found');
		}
	}
	
	/**
	 * Delete session
	 * 
	 * @access public
	 * @param string $key
	 */
	public static function delete($key)
	{
		unset($_SESSION[$key]);
	}
}

?>