<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }

require 'Smarty/Smarty.class.php';


class SmartyHandler extends Smarty
{
	/**
	 * Init SmartyHandler
	 * 
	 * @access public
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->compile_dir = Settings::get('appPath').'cache/templates/';
		$this->debugging = false;		
	}
	
	/**
	 * Get Template-Extension
	 * 
	 * @access public
	 * @return string Template-Extension
	 */
	public function getExtension()
	{
		return 'tpl';
	}
}

?>