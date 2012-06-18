<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }

class ShortlinkException extends Exception {}

class Shortlink
{
	/**
	 * Available Linkshorter
	 * 
	 * @var array
	 */
	private $availableLinkshorter = array('bit.ly', 'goo.gl');
	
	
	/**
	 * Init Shortlink
	 * 
	 * @access public
	 */
	public function __construct()
	{
		Settings::load('Shortlink.class');
		Helper::load('ArrayHelper');
		$this->curl = Library::load('Curl');
	}
	
	/**
	 * Get available Linkshorter
	 * 
	 * @access public
	 * @return array Linkshorter
	 */
	public function getAvailableLinkshorter()
	{
		return $this->availableLinkshorter;
	}
	
	/**
	 * Create shortlink
	 * 
	 * @access public
	 * @throws ShortlinkException
	 * @param string	$link			Link
	 * @param string	$linkshorter	Linkshorter (optional)
	 * @return string short-link
	 */
	public function short($link, $linkshorter = false)
	{
		$shortlink = '';
		
		if ($linkshorter == false)
		{
			$linkshorter = Settings::get('defaultLinkshorter');
		}
		
		switch ($linkshorter)
		{
			case 'bit.ly':
			{
				$this->curl->request('http://api.bit.ly/v3/shorten?login='.Settings::get('bitlyUsername').'&apiKey='.Settings::get('bitlyAPIKey').'&uri='.$link.'&format=txt');
		
				if ($this->curl->execute(array(200)))
				{
					$shortlink = $this->curl->getContent();					
				} 	
				
		    	break;		
			}
			
			case 'goo.gl':
			{
				$this->curl->request('https://www.googleapis.com/urlshortener/v1/url?key='.Settings::get('googlAPIKey'));
				$this->curl->setHeader('Content-type', 'application/json');
				$this->curl->setOption(CURLOPT_POST, true);
				$this->curl->setOption(CURLOPT_POSTFIELDS, ArrayHelper::arrayToJson(array('longUrl' => $link)));
				
				if ($this->curl->execute(array(200)))
				{
					$return = ArrayHelper::jsonToArray($this->curl->getContent());

					if (isset($return['id']) && substr($return['id'], 0, 7) == 'http://')
					{
						$shortlink = $return['id'];
					}
				}
				
				break;
			}	
			
			default:
			throw new ShortlinkException('Linkshorter ('.$linkshorter.') not available', Debug::WARNING);	
		}

		if (empty($shortlink))
		{
			throw new ShortlinkException('Request failed', Debug::WARNING);
		}
		
		return $shortlink;
	}
}

?>