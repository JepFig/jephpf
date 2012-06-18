<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class StringHelper
{
	/**
	 * Generates random-string
	 * 
	 * @access public
	 * @return string random-string
	 */
	public static function random()
	{
		return md5(time().rand(1, 100000).rand(1, 100000));
	} 
	
	/**
	 * Replace unnecessary chars
	 * 
	 * @access public
	 * @param string	$keyword	string
	 * @return string Replaced string
	 */
	public static function searchIndexReplace($keyword)
	{
		$keyword = utf8_decode($keyword);
		$keyword = strtolower(str_replace(array("(", ")", ",", ";", ":", "-", ".", "'", '"'), " ", $keyword));
		$keyword = str_replace(array("�", "�", "�", "�", "�", "�", "�"), array("ae", "ue", "oe", "ss", "ae", "ue", "oe"), $keyword);
		
		return $keyword;	
	}
	
	/**
	 * Returns type of a string
	 * 
	 * @access public
	 * @param string	$chars	  string
	 * @return string First char/type of a string
	 */
	public static function firstChar($chars)
	{
		if (ctype_alpha($chars[0]))
		{
			$char = strtolower($chars[0]);
			
		} else {	
		
			$char = 'int';
		}
		
		return $char;
	}
	
	/**
	 * Shortens string
	 * 
	 * @access public
	 * @param string	$string	  string
	 * @param int		$limit	  
	 * @return string Shorted string
	 */
	public static function short($string, $limit)
	{
		if (strlen($string) > $limit) 
		{
			$string = substr($string, 0, $limit - 3).'...';
		}
		
		return $string;		
	}
	
	/**
	 * Makes string url-compliant
	 * 
	 * @access public
	 * @param string	$string		title	
	 * @return string Url-compliant string
	 */
	public static function createSeotitle($title)
	{
		$search = array("�", "�", "�", "�", "�", "�", "�", "�", "RE:-", "RE:", "<", ">");		
		$replace = array("ae", "Ae", "ue", "Ue", "oe", "Oe", "ss", "euro", "");
		$title = str_replace($search, $replace, $title);
		$title = preg_replace("/[^\d\w]+/", "-", $title);
		$title = trim($title, "-");
		$title = strtolower($title);
			
		return urlencode($title);
	}

	/**
	 * Check is string is numeric
	 * 
	 * @param String $string String
	 * @return Boolean Is integer?
	 */
	public static function isNumeric($string)
	{
		$int = intval($string);

		if (strlen($string) == strlen($int)) 
		{
			return true;
		}
		
		return false;
	}
}

?>