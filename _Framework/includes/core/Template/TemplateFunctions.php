<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


if (!function_exists('url'))
{
	function url($module = false, $controller = false, $options = false)
	{
		switch (true)
		{
			case $module && $controller && $options:
			{
				$url = Settings::get('www').$module.'/'.$controller.'/';
				$params = array();
				
				foreach ($options as $key => $value)
				{
					$params[] = $key.'='.$value;
				}
				
				return $url.implode('&', $params).'/';
			}	
			break;
			
			case $module && $controller:
			return Settings::get('www').$module.'/'.$controller.'/';
			break;
			
			case $module:
			return Settings::get('www').$module.'/';
			
			default:
			return Settings::get('www');
		}
	}
}

if (!function_exists('formInputText'))
{
	function formInputText($name, $value = false)
	{
		return '<input type="text" name="'.$name.'" value="'.($value ? $value : '').'" />';
	}
}

if (!function_exists('formSelect'))
{
	function formSelect($options, $selected = false)
	{
		$select = '';
		
		foreach ($options as $key => $value)
		{
			$select .= '<option value="'.$key.'"'.($selected == $key ? ' selected="selected"' : '').'>'.$value.'</option>';
		}
		
		return $select;
	}
}

?>