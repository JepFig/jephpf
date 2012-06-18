<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }

class APIHelperException extends Exception {}

class APIHelper
{
	/**
	 * Supported output formats
	 * 
	 * @access private
	 * @var array
	 */
	private $supportedFormats = array('json', 'xml');
	
	
	/**
	 * Return API-Output
	 * 
	 * 	You can send $_GET['output_format'] to define the format
	 *
	 * @access public
	 * @param array 	$array 		API-Return
	 * @param bool		$format		Output-format	(optional)
	 * @return string formatted return
	 */
	public function output($array, $format = null)
	{
		$format = strtolower($format);
		
		if (!isset($format))
		{
			if (Client::inputGet('output_format'))
			{
				$format = Client::inputGet('output_format');
			
			} else {
				
				$format = Settings::get('defaultAPIOutputFormat');
			}
		}
		
		if (in_array($format, $this->supportedFormats) == false)
		{
			throw new APIHelperException('Format ('.$format.') not supported', Debug::ERROR);	
		}
		
		return $this->printOutput($format, $array);
	}
	
	/**
	 * Select output-format
	 *
	 * @access private
	 * @param string	$outputFormat 	selected Return-Format
	 * @param array 	$array			API-Output
	 * @return formatted output
	 */
	private function printOutput($outputFormat, $array)
	{
		$outputFormat = strtolower($outputFormat);
		
		switch ($outputFormat)
		{
			case 'xml':
			{
				Core::load('Response')->setContentType('xml');
				return Helper::load('ArrayHelper')->arrayToXML($array);
				break;
			}

			case 'json':
			{
				Core::load('Response')->setContentType('text');
				return Helper::load('ArrayHelper')->arrayToJSON($array);
				break;
			}	
					
			default:
			return $this->printOutput(Settings::get('defaultAPIOutputFormat'), $array);
		}
	}
}

?>