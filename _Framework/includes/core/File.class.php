<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class File
{
	/**
	 * Check if file exist
	 * 
	 * @access public
	 * @param string   $string	 filepath
	 * @return bool file exist?
	 */
	public function exists($file)
	{
		if (file_exists($file))
		{
			return true;
			
		} else {
			
			return false;
		}
	}
		
	/**
	 * Save content in a file
	 * 
	 * @access public
	 * @param string	$file	 	filepath
	 * @param string 	$content	content
	 */
	public function save($file, $content)
	{
		file_put_contents($file, $content);
	}
	
	/**
	 * Load file
	 * 
	 * @access public
	 * @param string	$file	 filepath
	 * @return string Content
	 */
	public function load($file, $path = '')
	{
		return file_get_contents($file);
	}
	
	/**
	 * Read file and write to output-buffer
	 * 
	 * @access public
	 * @param string $file File
	 * @return string File-Content
	 */
	public function read($file)
	{
		return readfile($file);
	}
	
	/**
	 * Delete file
	 * 
	 * @access public
	 * @param string	$file	 filepath
	 */
	public function delete($file)
	{
		unlink($file);
	}			
}

?>