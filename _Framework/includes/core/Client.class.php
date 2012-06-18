<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Client
{
	/**
	 * Client-information
	 * 
	 * @access private
	 * @var array
	 */
	private $data = array();	
	
	/**
	 * Has Core-Access?
	 * 
	 * @access private
	 * @var bool
	 */
	private $coreAccess = false;
	
	
	/**
	 * Init Client
	 *
	 * @access public
	 */
	public function __construct()
	{
		if (self::inputGet('coreAccessKey') && self::inputGet('coreAccessKey') == Settings::get('coreAccessKey'))
		{
			$this->coreAccess = true;
		}
		
		###### get user-data
		$ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1');

		if ($ip == '::1')
		{
			$ip = '127.0.0.1';
		}

		$this->data['ip'] = $ip;
		$this->data['userAgent'] = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
		$this->data['referer'] = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
		$this->data['countryCode'] = (function_exists('apache_note') && apache_note('GEOIP_COUNTRY_CODE') ? apache_note('GEOIP_COUNTRY_CODE') : Settings::get('defaultCountryCode'));
		$this->data['language'] = Settings::get('defaultLanguage');
		#####
	}	
	
	/**
	 * Has Core-Access?
	 * 
	 * @access public
	 * @return bool Core-Access?
	 */
	public function hasCoreAccess()
	{
		return $this->coreAccess;
	}
	
	/**
	 * Get User-Data
	 * 
	 * @access public
	 * @param string $data Data-Key
	 * @return mixed value, array or false
	 */
	public function getUserData($data = false)
	{
		if ($data == false)
		{
			return $this->data;
		}
		
		return (isset($this->data[$data]) ? $this->data[$data] : false);
	}
	
	/**
	 * Set User-Data
	 * 
	 * @access public
	 * @param string $data	Data-Key
	 * @param string $value	Data-Value
	 */
	public function setUserData($data, $value)
	{
		$this->data[$data] = $value;
	}
	
	/**
	 * Get GET-Data
	 * 
	 * @access public
	 * @param string 	$post		GET-Param				(optional)
	 * @param bool	 	$isNumeric	Param numeric?			(optional)
	 * @param bool	 	$canBeEmpty	Accept empty values?	(optional)
	 * @return mixed(string or bool) Returns GET-Data or false
	 */
	public static function inputGet($get = false, $isNumeric = false, $canBeEmpty = false)
	{
		if ($get == false)
		{
			return $_GET;
		}
		
		if (isset($_GET[$get]))
		{
			if ($isNumeric && is_numeric($_GET[$get]) == false)
			{
				return false;
			}
			
			if ($canBeEmpty == false && empty($_GET[$get]))
			{
				return false;
			}
			
			return $_GET[$get];
			
		} else {

			return false;
		}
	}
	
	/**
	 * Get POST-Data
	 * 
	 * @access public
	 * @param string 	$post		POST-Param				(optional)
	 * @param bool	 	$isNumeric	Param numeric?			(optional)
	 * @param bool	 	$canBeEmpty	Accept empty values?	(optional)
	 * @return mixed(string or bool) Returns POST-Data or false
	 */
	public static function inputPost($post = false, $isNumeric = false, $canBeEmpty = false)
	{
		if ($post == false)
		{
			return $_POST;
		}
		
		if (isset($_POST[$post]))
		{
			if ($isNumeric && is_numeric($_POST[$post]) == false)
			{
				return false;
			}
			
			if ($canBeEmpty == false && empty($_POST[$post]))
			{
				return false;
			}
			
			return $_POST[$post];
			
		} else {

			return false;
		}		
	}
	
	/**
	 * Get REQUEST-Data
	 * 
	 * @access public
	 * @param string 	$post		REQUEST-Param			(optional)
	 * @param bool	 	$isNumeric	Param numeric?			(optional)
	 * @param bool	 	$canBeEmpty	Accept empty values?	(optional)
	 * @return mixed(string or bool) Returns REQUEST-Data or false
	 */
	public static function inputRequest($request, $isNumeric = false, $canBeEmpty = false)
	{
		if ($request == false)
		{
			return $_REQUEST;
		}
		
		if (isset($_REQUEST[$request]))
		{
			if ($isNumeric && is_numeric($_REQUEST[$request]) == false)
			{
				return false;
			}
			
			if ($canBeEmpty == false && empty($_REQUEST[$request]))
			{
				return false;
			}
			
			return $_REQUEST[$request];
			
		} else {

			return false;
		}		
	}

	/**
	 * Set Cookie
	 * 
	 * @access public
	 * @param string 	$name		Cookie-Name
	 * @param string 	$value		Cookie-Value	(optional)
	 * @param int	 	$expire		Cookie-Lifetime	(optional)
	 * @param string 	$path		Cookie-Path		(optional)
	 * @param string 	$domain		Cookie-Domain	(optional)
	 * @param bool	 	$secure		Secure-Cookie?	(optional)
	 * @param bool	 	$httponly	HTTP only?		(optional)
	 */
	public static function setCookie($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
	{
	    $_COOKIE[$name] = $value;
	    
	    setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);		
	} 

	/**
	* Delete Cookie
	*
	* @access public
	* @param string 	$name		Cookie-Name
	* @param string 	$path		Cookie-Path		(optional)
	* @param string 	$domain		Cookie-Domain	(optional)
	*/	
	public static function deleteCookie($name, $path = '/', $domain = '')
	{
		unset($_COOKIE[$name]);
		setcookie($name, '', 0, $path, $domain);
	}
	
	/**
	 * Get Cookie
	 * 
	 * @access public
	 * @param string $name Cookie-Name
	 * @return mixed(string or bool) Returns Cookie-Value or false
	 */
	public static function getCookie($name)
	{
		if (isset($_COOKIE[$name]))
		{
			return $_COOKIE[$name];
			
		} else {
			
			return false;
		}
	}
}

?>