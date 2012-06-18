<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Buffer
{
	/**
	 * Buffer-content
	 *
	 * @access private
	 * @var array
	 */
	private $content = array();


	/**
	 * Start buffer
	 *
	 * @access public
	 */
	public function start()
	{
		if (Settings::get('enableGZHandler') && preg_match('/gzip/', getenv('HTTP_ACCEPT_ENCODING')))
		{
			//ob_start('ob_gzhandler');
			ob_start();

		} else {
				
			ob_start();
		}
	}

	/**
	 * Save buffer
	 *
	 * @access public
	 * @param string 	$key		buffer-name 	(optional)
	 */
	public function end($key = 'index', $delete = false)
	{
		$this->content[$key] = ob_get_contents();

		ob_end_clean();
		
		if ($delete)
		{
			$this->delete();
		}
	}

	/**
	 * Delete buffer
	 *
	 * @access public
	 */
	public function delete()
	{
		if (count(ob_list_handlers())) 
		{
			ob_clean();
		}
	}
	
	/**
	 * Get Buffer-Content
	 * 
	 * @access public
	 * @param string 	$key		Content-Key
	 * @param bool		$delete		Delete Buffer-Cache?
	 * @return string Buffer-Content
	 */
	public function getContent($key, $delete = true)
	{
		if (isset($this->content[$key]))
		{
			$content = $this->content[$key];
			
			if ($delete)
			{
				unset($this->content[$key]);
			}
			
			return $content;
		}
		
		Debug::LogError('Content-key ('.$key.') cannot be not found');
	}
}

?>