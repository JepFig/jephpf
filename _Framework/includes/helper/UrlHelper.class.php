<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class UrlHelper
{
	/**
	 * Extract domain out of url
	 * 
	 * @access public
	 * @param string	$url	URL
	 * @return string Domain
	 */
	public static function parseDomain($url)
	{
		$data = parse_url($url);
		
		if (isset($data['host']))
		{
			return str_replace('www.', '', $data['host']);
		}
		
		return false;
	}
}

?>