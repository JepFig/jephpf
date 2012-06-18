<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }

class SshException extends Exception { }

class Ssh 
{
	/**
	 * SSH2-Resource
	 * 
	 * @access private
	 * @var resource
	 */
	private $resource = null;
	
	/**
	 * Username
	 * 
	 * @access private
	 * @var string
	 */
	private $username;
	
	
	/**
	 * Connect to host (Authentication via password available)
	 * 
	 * @access public
	 * @param string $server	Hostname
	 * @param string $port		Port
	 * @param string $username	Username
	 * @param string $password	Password	(optional)
	 * @throws ScpException
	 */
    public function connect($server, $port, $username, $password = '')
    {
    	$this->resource = ssh2_connect($server, $port, array('hostkey' => 'ssh-rsa'));
    	$this->username = $username;
    		
    	if (!empty($password) && ssh2_auth_password($this->resource, $this->username, $password) == false)
    	{
    		throw new SshException('Authentication failed');
    	}
    }
    
    /**
     * Authenticate via public-key
     * 
     * @access public
     * @param string $publicKey		Path to public-key
     * @param string $privateKey	Path to private-key
     * @throws ScpException
     */
    public function authPubkey($publicKey, $privateKey)
    {
    	if (ssh2_auth_pubkey_file($this->resource, $this->username, $publicKey, $privateKey) == false)
    	{
    		throw new SshException('Authentication failed');
    	}
    }
    
    /**
     * Get accepted authentication-methods
     * 
     * @access public
     * @return array Authentication-methods
     */
    public function getAuthenticationMethods()
    {
    	return ssh2_auth_none($this->resource, $this->username);
    }
    
    /**
     * Execute command
     * 
     * @access public
     * @param String $command Command
     * @return resource Stream
     */
    public function execute($command)
    {
    	return ssh2_exec($this->resource, $command);
    }
    
    /**
     * Upload a file from local-storage to remote-storage
     * 
     * @access public
     * @param string $localFile		Path to local-file
     * @param string $remoteFile	Path to remote-file
     * @param int	 $rights		Access-rights			(optional)
     */
    public function uploadFile($localFile, $remoteFile, $rights = 0644)
    {
    	ssh2_scp_send($this->resource, $localFile, $remoteFile);
    }
    
    /**
     * Download a file from remote-storage to local-storage
     * 
     * @access public
     * @param string $remoteFile
     * @param string $localFile
     */
    public function downloadFile($remoteFile, $localFile)
    {
    	ssh2_scp_recv($this->resource, $remoteFile, $localFile);
    }
}
