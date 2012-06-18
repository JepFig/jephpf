<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Debug
{
	/**
	 * Error codes
	 * 
	 * @access public
	 * @var int
	 */
	const INFO = 0;
	const DEBUG = 100;
	const WARNING = 2;
	const ERROR = 4;
	
	/**
	 * Instance
	 * 
	 * @access private
	 * @var object
	 */
	private static $instance = null;
	
	/**
	 * Last error
	 * 
	 * @access private
	 * @var String
	 */
	private $lastError = '';
		
	/**
	 * Error-Levels
	 * 
	 * @access private
	 * @var array
	 */
	private $errorLevel = array(0 => array(), 
								1 => array('error'), 
								2 => array('warning', 'error'), 
								3 => array('debug', 'warning', 'error'), 
								4 => array('info', 'debug', 'warning', 'error'));
	
	
	/**
	 * Init Debug
	 *
	 * @access public
	 */
	public function __construct()
	{
		register_shutdown_function(array($this, 'handleShutdown'));
		set_error_handler(array($this, 'handleError'));
		set_exception_handler(array($this, 'handleException'));
		
		// Check if magic_quotes_runtime is active
		if (get_magic_quotes_runtime())
		{
			set_magic_quotes_runtime(false);
		}	
	}
	
	/**
	 * Returns instance
	 * 
	 * @access public
	 * @return object Debug
	 */
	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self();
		}
		
		return self::$instance;
	}
		
	/**
	 * Error-handler
	 *
	 * @access public
	 * @param int		$code		Error-Code
	 * @param string	$message	Error-Message
	 * @param string	$file		File
	 * @param int		$line		Line
	 */
	public function handleError($code, $message, $file, $line)
	{
		$error = array('type' => 'error', 'code' => $code, 'message' => $message, 'file' => $file, 'line' => $line);
		
		switch (true)
		{
			case in_array($error['code'], array(0)):
			{
				$error['type'] = 'info';
				break;
			}
		
			case in_array($error['code'], array(100)):
			{
				$error['type'] = 'debug';
				break;
			}
		
			case in_array($error['code'], array(2, 8, 512, 1024)):
			{
				$error['type'] = 'warning';
				break;
			}
		}	

		$this->saveError($error);
		
		if (Settings::get('scriptState') == 'development' && in_array($error['code'], array(0, 100)) == false)
		{
			Core::load('Buffer')->delete();
			self::printData($error);
			die();					
		}		
	}
	
	/**
	 * Exception-handler
	 * 
	 * @access public
	 * @param object $exception Exception
	 */
	public function handleException($exception)
	{
		self::getInstance()->handleError($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());	
	}
		
	/**
	 * Shutdown-handler
	 *
	 * @access public
	 */
	public function handleShutdown()
	{
		$error = error_get_last();
	
		if (!empty($error['message']))
		{
			$this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}
	
	/**
	* Logging-handler
	*
	* @access private
	* @param mixed $error Error-Message/Exception-Object
	* @param int $type Error-Type
	*/	
	private function handleLog($error, $type)
	{
		// Check if error is instance of exception
		if (is_object($error))
		{
			$this->handleError($type, $error->getMessage(), $error->getFile(), $error->getLine());
				
		} else {
		
			$debugBacktrace = debug_backtrace(false);
		
			if (isset($debugBacktrace[1]))
			{
				$this->handleError($type, $error, $debugBacktrace[1]['file'], $debugBacktrace[1]['line']);
			}
		}		
	}
	
	/**
	* Log info
	*
	* @access public
	* @param mixed $error Error-Message/Exception-Object
	*/
	public static function logInfo($error)
	{
		self::getInstance()->handleLog($error, self::INFO);
	}	
	
	/**
	* Log debug
	*
	* @access public
	* @param mixed $error Error-Message/Exception-Object
	*/
	public static function logDebug($error)
	{
		self::getInstance()->handleLog($error, self::DEBUG);
	}	
	
	/**
	 * Log warning
	 * 
	 * @access public
	 * @param mixed $error Error-Message/Exception-Object
	 */
	public static function logWarning($error)
	{
		self::getInstance()->handleLog($error, self::WARNING);
	} 
	
	/**
	* Log error
	*
	* @access public
	* @param mixed $error Error-Message/Exception-Object
	*/
	public static function logError($error)
	{
		self::getInstance()->handleLog($error, self::ERROR);
	}	
	
	/**
	 * Log error by exception-code
	 * 
	 * @param Exception $e Thrown Exception
	 */
	public static function logByExceptionCode($e)
	{
		self::getInstance()->handleLog($e, $e->getCode());
	}
	
	/**
	 * Print error-log
	 *
	 * @access private
	 * @param $error array error-data
	 */
	private function saveError($error)
	{
		if ($this->lastError == md5($error['type'].$error['message'].$error['file'].$error['line']))
		{
			return;
		}
		
		$this->lastError = md5($error['type'].$error['message'].$error['file'].$error['line']);
		
		//Check if this error-type should be logged
		if (in_array($error['type'], $this->errorLevel[Settings::get('debugLevel')]))
		{
			$filename = Settings::get('appPath').'logs/'.Settings::get('logFile');
			$file = fopen($filename, 'a');
			$content = "
			
####################################################
Type: ".ucfirst($error['type'])."
Error: ".$error['message']."
File: ".$error['file']."
Line: ".$error['line']."
Time: ".date("H:i:s", time())."
####################################################";

			fwrite($file, $content);
			fclose($file);
		}
	}

	/**
	 * print array or object
	 *
	 * @access public
	 * @param $array array data
	 * @return string output
	 */
	public static function printData($array)
	{
		echo '<br /><pre>';
		print_r($array);
		echo '</pre><br />';
	}
}

?>