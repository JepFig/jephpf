<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class ArrayHelper
{
	/**
	 * Convert array to XML
	 *
	 * @access public
	 * @param array	$array 	 array
	 * @param bool	$parent	 parent? (optional)		
	 * @return string XML-string
	 */
	public static function arrayToXml($array, $parent = true)
	{
		$xml = '';
		 
		if ($parent)
		{
			$xml .= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
			$xml .= '<data>'."\n";
		}
		 
		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				$xml .= '<'.$key.'>'."\n";
				$xml .= self::arrayToXml($value, false);
				$xml .= '</'.$key.'>'."\n";
				 
			} else {

				$key = (is_numeric($key) ? $value : $key);
				$xml .= '<'.$key.'>'.$value.'</'.$key.'>'."\n";
			}
		}
		 
		if ($parent)
		{
			$xml .= '</data>'."\n";
		}
		 
		return $xml;
	}
	
	/**
	 * Convert XML to array
	 *
	 * @access public
	 * @param string  $xml	XML-string 
	 * @return array Array
	 */
	public static function xmlToArray($xml)
	{
		libxml_use_internal_errors(true);
		
		if ($xml = simplexml_load_string($xml))
		{
			return json_decode(json_encode((array)$xml), true);
		}
		
		return false;
	}	
	
	/**
	 * Convert array to JSON
	 *
	 * @access public
	 * @param array  $array  Array
	 * @return string JSON
	 */
	public static function arrayToJson($array)
	{
		return json_encode($array);
	}
	
	/**
	 * Convert JSON to array
	 *
	 * @access public
	 * @param string $json JSON
	 * @return array Array
	 */
	public static function jsonToArray($json)
	{
		return json_decode($json, true);
	}
}

?>