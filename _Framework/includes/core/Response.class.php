<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Response
{
	/**
	 * page-title
	 *
	 * @access private
	 * @var string
	 */
	private $pageTitle;

	/**
	 * page-description
	 *
	 * @access private
	 * @var string
	 */
	private $pageDescription;

	/**
	 * page-keywords
	 *
	 * @access private
	 * @var array
	 */
	private $pageKeywords = array();

	/**
	 * page-content
	 *
	 * @access private
	 * @var string
	 */
	private $pageContent;

	/**
	 * content-type
	 *
	 * @access private
	 * @var string
	 */
	private $contentType = 'html';
	
	/**
	 * headers
	 * 
	 * @access private
	 * @var array
	 */
	private $header = array();
		
	
	/**
	 * Redirect to
	 *
	 * @access public
	 * @param string	$location		Redirect-URI
	 * @param bool		$directAbort	direct abort?	(optional)  
	 */
	public function redirect($location, $directAbort = true)
	{
		Core::load('Buffer')->delete();
		header('Location: '.$location);
		
		if ($directAbort)
		{
			die();
		}
	}
	
	/**
	 * Return pageTitle
	 * 
	 * @access public
	 * @return string pageTitle
	 */
	public function getPageTitle()
	{
		return $this->pageTitle.' - '.Settings::get('pageTitle');
	}

	/**
	 * Return pageKeywords
	 * 
	 * @access public
	 * @return string pageKeywords
	 */	
	public function getPageKeywords()
	{
		$keywords = '';

		foreach ($this->pageKeywords as $keyword)
		{
			$keywords .= $keyword.', ';
		}	

		foreach (Settings::get('pageKeywords') as $keyword)
		{
			$keywords .= $keyword.', ';
		}		
		
		return substr($keywords, 0, -2);
	}
	
	/**
	 * Return pageTitle
	 * 
	 * @access public
	 * @return string pageDescription
	 */	
	public function getPageDescription()
	{
		return $this->pageDescription.' - '.Settings::get('pageDescription');
	}
	
	/**
	 * set Header
	 *
	 * @access public
	 * @param string	$header		header
	 */
	public function setHeader($header)
	{
		$this->header[] = $header;
	}	
	
	/**
	 * set Meta-Tags
	 *
	 * @access public
	 * @param string	$title 			page-title
	 * @param string	$description	page-description	(optional)
	 * @param array 	$keywords		page-keywords 		(optional)
	 */
	public function setMeta($title, $description = '', $keywords = array())
	{
		$this->pageTitle = $title;
		$this->pageDescription = $description;
		$this->pageKeywords = (is_array($keywords) ? $keywords : (array)$keywords);
	}	
	
	/**
	 * set Content-Type
	 * 
	 * @access public
	 * @param string	$contentType	Content-Type
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;
	}
	
	/**
	 * Get Content-Type
	 * 
	 * @access public
	 * @return string Content-Type
	 */
	public function getContentType()
	{
		return $this->contentType;
	}
	
	/**
	 * Flush HTTP-Header
	 * 
	 * @access public
	 */
	public function flushHeaders()
	{
		foreach ($this->header as $header)
		{
			header($header);
		}
	}
		
	/**
	 * Return response
	 * 
	 * @access public
	 */
	public function send()
	{
		$buffer = Core::load('Buffer');
		$request = Core::load('Request');
		$template = Core::load('Template');
		
		switch ($this->contentType)
		{
			case 'javascript':
			{
				$this->setHeader('Content-type: text/javascript');
				$this->pageContent .= $buffer->getContent('main');
				break;
			}
		
			case 'css':
			{
				$this->setHeader('Content-type: text/css');
				$this->pageContent .= $buffer->getContent('main');
				break;
			}
				
			case 'plain':
			case 'text':
			{
				$this->setHeader('Content-type: text/plain');
				$this->pageContent .= $buffer->getContent('main');
				break;
			}
		
			case 'xml':
			{
				$this->setHeader('Content-type: text/xml');
				$this->pageContent .= $buffer->getContent('main');
				break;
			}
		
			default:
			{
				$this->setHeader('Content-type: text/html');
		
				if ($request->isAjaxRequest())
				{
					$this->pageContent .= $buffer->getContent('main');
						
				} else {
		
					$this->pageContent .= $template->getHTMLHeader();
					$this->pageContent .= $buffer->getContent('main');
					$this->pageContent .= $template->getHTMLFooter();
				}
			}
		}
		
		$this->flushHeaders();
		
		if ($request->hasResponder())
		{
			$request->callResponder($this->pageContent);
			
		} else {
			
			//$this->buffer->start();
			echo $this->pageContent;
			//$this->buffer->output();
		}
	}
}

?>