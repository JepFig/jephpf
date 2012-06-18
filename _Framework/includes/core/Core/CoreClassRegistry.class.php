<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class CoreClassRegistry extends AppClassRegistry
{
	/**
	 * @var Benchmark
	 */
	public $benchmark;

	/**
	* @var Buffer
	*/
	public $buffer;	
	
	/**
	* @var Client
	*/
	public $client;

	/**
	* @var Debug
	*/
	public $debug;	
	
	/**
	* @var File
	*/
	public $file;	
	
	/**
	* @var Request
	*/
	public $request;	
	
	/**
	* @var Response
	*/
	public $response;	
	
	/**
	* @var Template
	*/
	public $template;	
	
	/**
	* @var Cache
	*/
	public $cache;	
	
	/**
	* @var Curl
	*/
	public $curl;	
	
	/**
	* @var Hook
	*/
	public $hook;	
	
	/**
	* @var Image
	*/
	public $image;		
	
	/**
	 * @var Language
	 */
	public $language;
	
	/**
	* @var Mail
	*/
	public $mail;
	
	/**
	 * @var Session
	 */
	public $session;

	/**
	* @var Shortlink
	*/
	public $shortlink;

	/**
	* @var Sms
	*/
	public $sms;

	/**
	* @var Sql
	*/
	public $sql;	
}

?>