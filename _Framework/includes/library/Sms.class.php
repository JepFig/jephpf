<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Sms extends Library
{
	/**
	 * Init Sms
	 * 
	 * @access public
	 */
	public function __construct()
	{
		Settings::load('Sms.class');
	}
	
	/**
	 * Send sms
	 * 
	 * @access public
	 * @param array		$data		data
	 * @param string	$provider	provider (optional)
	 * @return bool status
	 */
	public function send($from, $message, $to, $provider = false)
	{
		$status = false;
		
		if ($provider == false)
		{
			$provider = Settings::get('defaultSmsProvider');
		}
		
		switch ($provider)
		{
			case 'smstrade.de':
			{
				try {

					$client = new SoapClient('http://gateway.smstrade.de/soap/index.php?wsdl');
					$client->sendSMS(Settings::get('smstradeDeApiKey'), $to, $message, Settings::get('smstradeDePackage'), $from);
					$status = true;
					
				} catch (SoapFault $e) {
				
					Debug::logWarning($e);
				}
				
		    	break;		
			}
			
			default:
			Debug::logError('Sms-provider('.$provider.') not available');	
		}

		return $status;
	}
}

?>