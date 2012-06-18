<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Request
{
	/**
	 * module
	 *
	 * @access public
	 * @var string
	 */
	public $module;

	/**
	 * controller
	 *
	 * @access public
	 * @var string
	 */
	public $controller;

	/**
	 * settings
	 *
	 * @access private
	 * @var array
	 */
	private $moduleSettings;
	
	/**
	 * module-instance
	 * 
	 * @var object
	 */
	private $moduleInstance;

	/**
	 * ajax-Request?
	 *
	 * @access private
	 * @var bool
	 */
	private $ajaxRequest = false;
	
	/**
	 * CLI-Request?
	 * 
	 * @access private
	 * @var bool
	 */
	private $cliRequest = false;
		
	
	/**
	 * Init Request
	 *
	 * @access public
	 */
	public function __construct()
	{
		$this->benchmark = Core::load('Benchmark');
		$this->buffer = Core::load('Buffer');
		$this->file = Core::load('File');
		
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			$this->ajaxRequest = true;
		}
	}

	/**
	 * Load module
	 *
	 * @access public
	 * @param string	$module			module 		(optional)
	 * @param string	$controller		controller	(optional)
	 */
	public function loadModule($module = false, $controller = false)
	{	
		$subfolder = '';
		
		
		###### Get module & controller
		$this->module = $module;
		
		if ($controller)
		{
			if (preg_match('/::/i', $controller))
			{
				$folder = explode('::', $controller);
				$controller = $folder[count($folder)-1];
				unset($folder[count($folder)-1]);
				$subfolder = implode('/', $folder).'/';
			}
			
			$this->controller = $controller.'Controller';
			
		} else {

			$this->controller = 'index';
		}	
		######
		
		
		###### Maintenance?
		if (Settings::get('maintenanceStatus'))
		{
			require Settings::get('appPath').'modules/static/controller/h503.php';
			$this->module = 'static';
			$this->controller = 'h503Controller';
			$this->moduleInstance = new $this->controller();
			return true;
		}
		###### 
		
		
		###### Check module and start if is enabled
		if ($this->file->exists(Settings::get('appPath').'modules/'.$this->module.'/controller/'.$subfolder.str_replace('Controller', '', $this->controller).'.php'))
		{
			###### Load module-config
			require Settings::get('appPath').'modules/'.$this->module.'/config.php';
			$this->moduleSettings = $settings;
					
			if (isset($this->moduleSettings['template']) && !empty($this->moduleSettings['template']))
			{
				Settings::set('template', $this->moduleSettings['template']);
			}
			######
			
			
			###### Module active?
			if ($this->moduleSettings['moduleStatus'])
			{
				require Settings::get('appPath').'modules/'.$this->module.'/controller/'.$subfolder.str_replace('Controller', '', $this->controller).'.php';
				
				try {
					
					$this->moduleInstance = new $this->controller();
					$this->moduleInstance->index();
					
				} catch (MaintenanceException $e) {
						
					require Settings::get('appPath').'modules/static/controller/h503.php';
					Debug::logInfo($e);
						
					$this->buffer->delete();
					$this->module = 'static';
					$this->controller = 'h503Controller';
					$this->moduleInstance = new $this->controller();
					$this->moduleInstance->index();
					
				} catch (NotFoundException $e) {
					
					require Settings::get('appPath').'modules/static/controller/h404.php';
					Debug::logInfo($e);
					
					$this->buffer->delete();
					$this->module = 'static';
					$this->controller = 'h404Controller';
					$this->moduleInstance = new $this->controller();
					$this->moduleInstance->index();	
									
				} catch (Exception $e) {
					
					require Settings::get('appPath').'modules/static/controller/h503.php';
					Debug::logByExceptionCode($e);
					
					$this->buffer->delete();
					$this->module = 'static';
					$this->controller = 'h503Controller';
					$this->moduleInstance = new $this->controller();
					$this->moduleInstance->index();
				}
				
			} else {

				require Settings::get('appPath').'modules/static/controller/h404.php';
				
				$this->module = 'static';
				$this->controller = 'h404Controller';
				$this->moduleInstance = new $this->controller();						
				$this->moduleInstance->index();	
			}

		} else {

			require Settings::get('appPath').'modules/static/controller/h404.php';
			
			$this->module = 'static';
			$this->controller = 'h404Controller';
			$this->moduleInstance = new $this->controller();
			$this->moduleInstance->index();	
		}
		######
	}
	
	/**
	 * AJAX-Request?
	 * 
	 * @access public
	 * @return bool AJAX-Request?
	 */
	public function isAjaxRequest()
	{
		return $this->ajaxRequest;
	}
	
	/**
	 * CLI-Request?
	 * 
	 * @access public
	 * @return bool CLI-Request?
	 */
	public function isCliRequest()
	{
		return $this->cliRequest;
	}
	
	/**
	 * Return Module
	 * 
	 * @access public
	 * @return string Module
	 */
	public function getModule()
	{
		return $this->module;
	}
	
	/**
	 * Return controller
	 * 
	 * @access public
	 * @return string controller
	 */
	public function getController()
	{
		return str_replace('Controller', '', $this->controller);
	}
	
	/**
	 * Returns the configuration of the controller-module
	 * 
	 * @access public
	 * @return array Settings
	 */
	public function getModuleSettings()
	{
		return $this->moduleSettings;
	}
	
	/**
	 * Check if module has his own responder
	 * 
	 * @access public
	 * @return bool Responder?
	 */
	public function hasResponder()
	{
		if (method_exists($this->moduleInstance, 'response'))
		{
			return true;
			
		} else {
			
			return false;
		}
	}
	
	/**
	 * Call the module-responder
	 * 
	 * @access public
	 * @param string $content Page-Content
	 */
	public function callResponder($content)
	{
		$this->moduleInstance->response($content);
	}
	
	/**
	 * Receive HTTP-Request
	 * 
	 * @access public
	 */
	public function receive()
	{
		if (Client::inputGet('module'))
		{
			$module = basename(Client::inputGet('module'));

			if (Client::inputGet('controller'))
			{
				$controller = basename(Client::inputGet('controller'));
				 
				if ($controller == 'defaultController')
				{
					$controller = Settings::get('defaultControllerName');
				}
				
			} else {
				 
				$controller = Settings::get('defaultControllerName');
			}
			
			unset($_GET['module'], $_GET['controller']);

		} elseif (php_sapi_name() == 'cli' && count($_SERVER['argv']) > 2) {	
			
			$this->cliRequest = true;
			$module = basename($_SERVER['argv'][1]);
			$controller = basename($_SERVER['argv'][2]);
			
			unset($_SERVER['argv'][0], $_SERVER['argv'][1], $_SERVER['argv'][2]);
			
		} else {

			$module = Settings::get('defaultModule');
			$controller = Settings::get('defaultController');
		}		
		
		$controller = str_replace('/', '::', $controller);

		$this->buffer->start();
		$this->benchmark->start('module');
		$this->loadModule($module, $controller);
		$this->benchmark->end('module');
		$this->buffer->end('main', true);	
	}
}

?>