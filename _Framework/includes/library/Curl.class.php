<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }

class CurlException extends Exception {}

class Curl
{
	/**
	 * Curl-session
	 * 
	 * @access private
	 * @var resource
	 */
	private $session = null;
	
	/**
	* Request-url
	*
	* @access private
	* @var string
	*/
	private $requestUrl = '';
	
	/**
	 * Post-data
	 * 
	 * @access private
	 * @var array
	 */
	private $postData = array();
	
	/**
	 * Cookies
	 * 
	 * @access
	 * @var array
	 */
	private $cookies = array();
	
	/**
	 * Request-settings
	 * 
	 * @access private
	 * @var array
	 */
	private $settings = array();
	
	/**
	 * Request-information
	 * 
	 * @access private
	 * @var array
	 */
	private $requestInfo = array();
	
	/**
	 * Request-duration
	 * 
	 * @access private
	 * @var int
	 */
	private $requestDuration = 0;
	
	/**
	 * Http status-code
	 * 
	 * @access public
	 * @var int
	 */
	private $httpCode = 0;
	
	/**
	 * Request-content
	 * 
	 * @access private
	 * @var string
	 */
	private $content = '';
	
	/**
	 * Content-type
	 * 
	 * @access public
	 * @var string
	 */
	private $contentType = '';
	
	/**
	 * Response-headers
	 * 
	 * @access public
	 * @var array
	 */
	private $responseHeaders = array();
	
	/**
	* Request-headers
	*
	* @access public
	* @var array
	*/
	private $requestHeaders = array();
	
	
	/**
	 * Prepare request
	 * 
	 * @access public
	 * @param string $requestUrl Url
	 */
	public function request($requestUrl)
	{
		$this->session = curl_init();
		$this->requestUrl = $requestUrl;
		$this->requestDuration = 0;
		$this->httpCode = 0;
		$this->content = '';
		$this->contentType = '';
		$this->postData = array();
		$this->requestInfo = array();
		$this->responseHeaders = array();
		$this->requestHeaders = array();
		$this->settings = array();
		$this->settings[CURLOPT_URL] = $this->requestUrl;
		$this->settings[CURLOPT_FOLLOWLOCATION] = true;
		$this->settings[CURLOPT_RETURNTRANSFER] = true;
		$this->settings[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1';
	}
	
	/**
	 * Set options
	 * 
	 * @access public
	 * @param array $array Options
	 * @throws CurlException
	 */
	public function setOptionArray($array)
	{
		if (is_array($array) == false)
		{
			throw new CurlException('Only arrays allowed');
		}
		
		foreach ($array as $key => $value)
		{
			$this->setOption($key, $value);
		}
	}
	
	/**
	 * Set option
	 * 
	 * @access public
	 * @param string $name		Name
	 * @param string $value		Value
	 */
	public function setOption($name, $value)
	{
		$this->settings[$name] = $value;
	}
	
	/**
	 * Delete option
	 * 
	 * @access public
	 * @param string $name Name
	 */
	public function deleteOption($name)
	{
		unset($this->settings[$name]);
	}
	
	/**
	 * Set request-timeout
	 * 
	 * @access public
	 * @param int $timeout Timeout
	 */
	public function setTimeout($timeout)
	{
		$this->settings[CURLOPT_CONNECTTIMEOUT] = $timeout;
		$this->settings[CURLOPT_TIMEOUT] = $timeout;
	}
	
	/**
	 * Set cookie-array
	 * 
	 * @access public
	 * @param array $array Cookies
	 * @throws CurlException
	 */
	public function setCookieArray($array)
	{	
		if (is_array($array) == false)
		{
			throw new CurlException('Only arrays allowed');
		}
		
		foreach ($array as $name => $value)
		{
			$this->setCookie($name, $value);
		}
	}
	
	/**
	 * Set cookie
	 * 
	 * @access public
	 * @param string $name	Name
	 * @param string $value	Value
	 */
	public function setCookie($name, $value)
	{
		$this->cookies[$name] = $value;
	}
	
	/**
	 * Save cookies
	 * 
	 * @access public
	 * @param string $identifier Cookie-identifier
	 */
	public function saveCookies($identifier)
	{
		$cookieFile = Settings::get('appPath').'data/cookies/'.md5($identifier).'.txt';
		$this->settings[CURLOPT_COOKIEJAR] = $cookieFile;		
	}
	
	/**
	 * Load saved cookies
	 * 
	 * @access public
	 * @param string $identifier Cookie-identifier
	 */
	public function loadCookies($identifier)
	{
		$cookieFile = Settings::get('appPath').'data/cookies/'.md5($identifier).'.txt';
		$this->settings[CURLOPT_COOKIEFILE] = $cookieFile;
	}
	
	/**
	 * Set post-array
	 * 
	 * @access public
	 * @param array $array Post-data
	 * @throws CurlException
	 */
	public function setPostArray($array)
	{
		if (is_array($array) == false)
		{
			throw new CurlException('Only arrays allowed');
		}
		
		foreach ($array as $key => $value)
		{
			$this->setPostValue($key, $value);
		}
	}
	
	/**
	 * Set post-value
	 * 
	 * @access public
	 * @param string $key	Key
	 * @param string $value	Value
	 */
	public function setPostValue($key, $value)
	{
		$this->postData[$key] = $value;
	}
	
	/**
	 * Set header-array
	 * 
	 * @access public
	 * @param array $array Header-array
	 * @throws CurlException
	 */
	public function setHeaderArray($array)
	{
		if (is_array($array) == false)
		{
			throw new CurlException('Only arrays allowed');
		}
		
		foreach ($array as $name => $value)
		{
			$this->setHeader($name, $value);
		}
	}
	
	/**
	 * Set header
	 * 
	 * @access public
	 * @param string $name	Name	
	 * @param string $value	Value
	 */
	public function setHeader($name, $value)
	{
		$this->requestHeaders[$name] = $value;
	}
	
	/**
	 * Http-authentication
	 * 
	 * @access public
	 * @param string $username	Username
	 * @param string $password	Password
	 */
	public function authenticate($username, $password)
	{
		$this->settings[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
		$this->settings[CURLOPT_USERPWD] = $username.':'.$password;
	}
	
	/**
	 * Set proxy
	 * 
	 * @access public
	 * @param string $protocol	Proxy-protocol
	 * @param string $server	Server (:Port)	
	 * @param string $username	Username		(optional)
	 * @param string $password	Password		(optional)
	 * @throws CurlException
	 */
	public function setProxy($protocol, $server, $username = '', $password = '')
	{
		switch ($protocol)
		{
			case 'SOCKS5':
			{
				$this->settings[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
				$this->settings[CURLOPT_PROXY] = $server;
					
				if (!empty($username) && !empty($password))
				{
					$this->settings[CURLOPT_PROXYUSERPWD] = $username.':'.$password;
				}
				
				break;
			}	
			
			case 'SOCKS4':
			{
				if (!defined('CURLPROXY_SOCKS4'))
				{
					define('CURLPROXY_SOCKS4', 4);
				}
				
				$this->settings[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS4;
				$this->settings[CURLOPT_PROXY] = $server;
				break;
			}
			
			default:
			throw new CurlException('Protocol ('.$protocol.') is not supported');
		}
	}
	
	/**
	 * Execute request
	 * 
	 * @access public
	 * @param array $positiveRequestHeaders Positive request http-codes
	 * @return bool Request successfull?
	 */
	public function execute($positiveRequestHeaders = array())
	{
		$this->settings[CURLOPT_HEADER] = true;
		
		if (isset($this->requestUrl[4]) && $this->requestUrl[4] == 's')
		{
			$this->settings[CURLOPT_SSL_VERIFYPEER] = false;
			$this->settings[CURLOPT_SSL_VERIFYHOST] = 2;
		}
		
		if (count($this->postData) > 0)
		{
			$this->settings[CURLOPT_POST] = 1;
			$this->settings[CURLOPT_POSTFIELDS] = $this->postData;
		}
		
		if (count($this->requestHeaders) > 0)
		{
			$headers = array();
			
			foreach ($this->requestHeaders as $name => $value)
			{
				$headers[] = $name.': '.$value;
			}
			
			$this->settings[CURLOPT_HTTPHEADER] = $headers;
		}
		
		if (count($this->settings) > 0)
		{
			$cookieString = '';
			
			foreach ($this->cookies as $name => $value)
			{
				$cookieString .= $name.'='.$value.'; ';	
			}
			
			$this->settings[CURLOPT_COOKIE] = $cookieString;
		}
		
		foreach ($this->settings as $key => $value)
		{
			curl_setopt($this->session, $key, $value);
		}
		
		$this->content = curl_exec($this->session);
		$this->requestInfo = curl_getinfo($this->session);
		$this->httpCode = $this->requestInfo['http_code'];
		$this->contentType = $this->requestInfo['content_type'];
		$this->requestDuration = $this->requestInfo['total_time'];
		
		list($headers, $this->content) = explode("\r\n\r\n", $this->content, 2);
		curl_close($this->session);
		$headers = explode("\n", $headers);
		
		foreach ($headers as $header)
		{
			$header = explode(":", $header);
			
			if (count($header) >= 2)
			{
				$key = str_replace("\n", "", $header[0]);
				unset($header[0]);
				$this->responseHeaders[$key] = trim(implode(':', $header));
			}
		}
		
		if (in_array($this->getHttpCode(), $positiveRequestHeaders))
		{
			return true;
				
		} else if (isset($positiveRequestHeaders[0])) {
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get last request-error
	 * 
	 * @access public
	 * @return string Error
	 */
	public function getLastError()
	{
		return curl_error($this->session);
	}
	
	/**
	 * Get request-info
	 * 
	 * @access public
	 * @return array Request-info
	 */
	public function getRequestInfo()
	{
		return $this->requestInfo;
	}
	
	/**
	 * Get requested content
	 * 
	 * @access public
	 * @return string Content
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * Get content-type
	 * 
	 * @access public
	 * @return string Content-type
	 */
	public function getContentType()
	{
		return $this->contentType;
	}
	
	/**
	 * Get request-duration
	 * 
	 * @access public
	 * @return int Request-duration
	 */
	public function getRequestDuration()
	{
		return $this->requestDuration;
	}
	
	/**
	 * Get headers
	 * 
	 * @access public
	 * @return array Headers
	 */
	public function getHeaders()
	{
		return $this->headers;
	}
	
	/**
	 * Get http-code
	 * 
	 * @access public
	 * @return int Http-code
	 */
	public function getHttpCode()
	{
		return $this->httpCode;
	}
}

?>