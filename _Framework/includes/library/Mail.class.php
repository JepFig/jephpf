<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Mail
{
	/**
	 * Init Mail
	 * 
	 * @access public
	 */
	public function __construct()
	{
		Settings::load('Mail.class');
		require_once Settings::get('corePath').'includes/library/Mail/PHPMailer.class.php';
	}
	
	/**
	 * Check email-syntax
	 * 
	 * @access public
	 * @source WCF (woltlab.com)
	 * @param string	$email	email
	 * @return bool email-syntax correct?
	 */
	public function check($email)
	{
		$c = '!#\$%&\'\*\+\-\/0-9=\?a-z\^_`\{\}\|~';
		$string = '['.$c.']*(?:\\\\[\x00-\x7F]['.$c.']*)*';
		$localPart = $string.'(?:\.'.$string.')*';
		$name = '[a-z0-9](?:[a-z0-9-]*[a-z0-9])?';
		$domain = $name.'(?:\.'.$name.')*\.[a-z]{2,}';
		$mailbox = $localPart.'@'.$domain;
		
		return preg_match('/^'.$mailbox.'$/i', $email);
	} 	
		
	/**
	 * Send mail
	 * 
	 * @access public
	 * @param string	$tomail		receiver
	 * @param string	$subject	subject
	 * @param string	$body		content
	 * @param string 	$frommail	sender-email	(optional)
	 * @param string	$fromname	sender-name		(optional)
	 * @param bool correct send?
	 */
	public function sendmail($tomail, $subject, $body, $frommail = '', $fromname = '')
	{
		switch (Settings::get('defaultMailer'))
		{
			case 'sendmail':
			{
				$mailer = new PHPMailer();
				
				if (!empty($frommail))
				{
					$mailer->From = $frommail;
				}
				
				if (!empty($fromname))
				{
					$mailer->FromName = $fromname;
				}
				
				$body = utf8_decode($body);
				$body = str_replace("\\r\\n", '<br />', $body);
				$mailer->MsgHTML($body);
				$mailer->AltBody = $body;
				$mailer->Subject = $subject;
				$mailer->AddAddress($tomail);
			
				if ($mailer->Send())
				{
			   		return true;
			   
				} else {
					
			   		return false;
				}	
				
				break;
			}	
			
			case 'smtp':
			{
				$mailer = new PHPMailer();
				$mailer->IsSMTP();
				$mailer->Host = Settings::get('smtpHost');
				$mailer->SMTPAuth = true;
				$mailer->Username = Settings::get('smtpUser'); 
				$mailer->Password = Settings::get('smtpPassword'); 
				
				if (!empty($frommail))
				{
					$mailer->From = $frommail;
				}
				
				if (!empty($fromname))
				{
					$mailer->FromName = $fromname;
				}
				
				$mailer->IsHTML(true);
				$mailer->Body = $body;
				$mailer->AltBody = $body;
				$mailer->Subject = $subject;
				$mailer->AddAddress($tomail);
			
				if ($mailer->Send())
				{
			   		return true;
			   
				} else {
					
			   		return false;
				}	

				break;
			}				
		}
	}
}

?>