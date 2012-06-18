<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Template
{
	/**
	 * Show Theme-Header und Footer?
	 *
	 * @var bool
	 */
	private $themeWrapper = true;
	
	/**
	 * Show HTML-Wrapper?
	 * 
	 * @var bool
	 */
	private $htmlWrapper = true;

	/**
	 * Theme-Instance
	 *
	 * @var object
	 */
	private $themeInstance = null;
	
	/**
	 * Handler-Instances
	 * 
	 * @var object
	 */
	private $handlerInstances = array();
	
	/**
	 * Template-Handler
	 * 
	 * @var object
	 */
	private $templateHandler = null;
	
	/**
	 * Assignments
	 * 
	 * @var array
	 */
	private $assign = array();
	
	/**
	 * CSS-Files
	 *
	 * @var Array
	 */
	private $cssFiles = array();
	
	/**
	 * Javascript-Files
	 * 
	 * @var Array
	 */
	private $javascriptFiles = array();
	
	/**
	 * Meta-Information
	 * 
	 * @var Array
	 */
	private $metaInformation = array();

	
	/**
	 * Init Template
	 */
	public function __construct()
	{
		Settings::set('themeImages', Settings::get('wwwStatic').'static/templates/'.Settings::get('template').'/images/', true);
	}
	
	/**
	 * Set the Template-Handler
	 * 
	 * @access public
	 * @param string $templateHandler Template-Handler
	 * @return object Template-Handler
	 */
	public function setHandler($templateHandler)
	{
		$this->templateHandler = $templateHandler;
		
		if (array_key_exists($this->templateHandler, $this->handlerInstances) == false)
		{
			require_once Settings::get('corePath').'includes/core/Template/'.$templateHandler.'Handler.class.php';
			
			$handlerClass = $templateHandler.'Handler';
			$this->handlerInstances[$this->templateHandler] = new $handlerClass;
		}
		
		return $this->handlerInstances[$this->templateHandler];
	}
	
	/**
	 * Return instance of template-handler
	 * 
	 * @access public
	 * @return object Template-Handler
	 */
	public function getHandler()
	{
		return $this->handlerInstances[$this->templateHandler];
	}

	/**
	 * Assign template-key with value
	 *
	 * @access public
	 * @param string	$key	template-key
	 * @param string	$value	data
	 * @return object Self
	 */
	public function assign($key, $value)
	{
		$this->assign[$key] = $value;
		
		return $this;
	}

	/**
	 * Show template
	 *
	 * @access public
	 * @param bool		$return		direct return?	(optional)
	 * @param string	$template	template-file 	(optional)
	 * @param string	$extension	file-extension	(optional)
	 * @return string Template
	 */
	public function display($return = true, $template = false, $extension = false)
	{
		
		$request = Core::load('Request');
		$file = Core::load('File');
		Settings::set('wwwSelf', Settings::get('wwwStatic').$request->getModule().'/'.$request->getController().'/', true);
		
		if ($this->templateHandler == null)
		{
			$this->setHandler(Settings::get('defaultTemplateHandler'));
		}
		
		if ($template == false)
		{
			$template = $request->getController();			
		}
		
		if ($extension == false)
		{
			$extension = $this->getHandler()->getExtension();
		}
		
		foreach ($this->assign as $key => $value)
		{
			$this->getHandler()->assign($key, $value);
		}
		
		if ($file->exists(Settings::get('appPath').'modules/'.$request->getModule().'/templates/'.Settings::get('template').'/'.$template.'.'.$extension))
		{
			$content = $this->getHandler()->fetch(Settings::get('appPath').'modules/'.$request->getModule().'/templates/'.Settings::get('template').'/'.$template.'.'.$extension);
				
		} else {
				
			$content = $this->getHandler()->fetch(Settings::get('appPath').'modules/'.$request->getModule().'/templates/default/'.$template.'.'.$extension);
		}
		
		if ($return)
		{
			echo $content;
			
		} else {
			
			return $content;
		}
	}
	
	/**
	 * Show HTMLWrapper?
	 * 
	 * @access public
	 * @param bool $htmlWrapper Show HTMLWrapper?
	 */
	public function setHtmlWrapper($htmlWrapper = false)
	{
		$this->htmlWrapper = $htmlWrapper;
	}
	
	/**
	 * Show ThemeWrapper?
	 * 
	 * @access public
	 * @param bool $themeWrapper Show ThemeWrapper?
	 */
	public function setThemeWrapper($themeWrapper = false)
	{
		$this->themeWrapper = $themeWrapper;
	}
	
	/**
	 * Add a Css-File
	 * 
	 * @param String $cssFile Path to Css-file
	 */
	public function addCssFile($cssFile)
	{
		if (in_array($cssFile, $this->cssFiles) == false)
		{
			$this->cssFiles[] = $cssFile;
		}
	}
	
	/**
	 * Add meta-information
	 * 
	 * @param String $name		Meta-name
	 * @param Stromg $content	Meta-content
	 */
	public function addMetaInformation($name, $content)
	{
		if (in_array($name, $this->metaInformation) == false)
		{
			$this->metaInformation[$name] = $content;
		}
	}

	/**
	 * Set a Javascript-file
	 * 
	 * @param String $javascriptFile Path to Javascript-file
	 */
	public function setJavascriptFile($javascriptFile)
	{
		if (in_array($javascriptFile, $this->javascriptFiles) == false)
		{
			$this->javascriptFiles[] = $javascriptFile;
		}
	}
		
	/**
	 * Create HTML-Header
	 *
	 * @access public
	 * @return string HTML-Header
	 */
	public function getHTMLHeader()
	{
		if ($this->htmlWrapper)
		{
			require Settings::get('appPath').'static/templates/'.Settings::get('template').'/Theme.class.php';
			
			$this->themeInstance = new Theme();
			$response = Core::load('Response');
			$content = $this->themeInstance->getHtmlDeclaration();
			$content .= '<head>'."\n";
			$content .= '	<title>'.$response->getPageTitle().'</title>'."\n";
			$content .= '	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n";
			$content .= '	<meta name="description" content="'.$response->getPageDescription().'" />'."\n";
			$content .= '	<meta name="keywords" content="'.$response->getPageKeywords().'" />'."\n";
			
			foreach ($this->metaInformation as $metaName => $metaContent)
			{
				$content .= '	<meta http-equiv="'.$metaName.'" content="'.$metaContent.'">'."\n";
			}
			
			$content .= '	<link rel="shortcut icon" href="'.Settings::get('wwwStatic').'static/templates/'.Settings::get('template').'/images/favicon.ico" type="image/ico" />'."\n";
			$content .= '	<link rel="stylesheet" href="'.Settings::get('wwwStatic').'static/template/css/'.Settings::get('template').'/'.Settings::get('cssVersion').'/core.css" type="text/css" />'."\n";
			
			foreach ($this->cssFiles as $cssFile)
			{
				$content .= '	<link rel="stylesheet" href="'.$cssFile.'" type="text/css" />'."\n";
			}
			
			$content .= '	<script type="text/javascript" src="'.(preg_match('/https/i', Settings::get('www')) ? 'https://' : 'http://').'ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js"></script>'."\n";			
			$content .= '</head>'."\n";
			$content .= '<body>'."\n\n";

			if ($this->themeWrapper)
			{
				$content .= $this->themeInstance->header();
			}

			return $content;
		}
	}

	/**
	 * Create HTML-Footer
	 *
	 * @access public
	 * @return HTML-Footer
	 */
	public function getHTMLFooter()
	{
		if ($this->htmlWrapper)
		{
			$content = '';

			if ($this->themeWrapper)
			{
				$content .= $this->themeInstance->footer();
			}
			
			$content .= "\n";
			$content .= '	'.Settings::get('HTMLFooterContent');
			$content .= '	<script type="text/javascript" src="'.Settings::get('wwwStatic').'static/clientscript/'.Settings::get('template').'/'.Settings::get('clientscriptVersion').'/core.js"></script>'."\n";
			
			foreach ($this->javascriptFiles as $javascriptFile)
			{
				$content .= '	<script type="text/javascript" src="'.$javascriptFile.'"></script>'."\n";
			}
			
			$content .= "\n".'</body>'."\n";
			$content .= '</html>'."\n";
	
			return $content;
		}	
	}
}

?>