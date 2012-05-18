<?php  if ( ! defined('SITE')) exit('No direct script access allowed');
class Model_Session
{
	public $ip;

	public function __construct()
	{
		// Get user io
		if ( ! empty($_SERVER['HTTP_CLIENT_IP']) ) { $this->ip = $_SERVER['HTTP_CLIENT_IP']; }
			elseif ( ! empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) { $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }
			else { $this->ip = $_SERVER['REMOTE_ADDR']; }
		
		session_name("SID");
		session_start();
		
		// Validation user session
		if ( ! isset($_SESSION['basis_hash']) )
		{
			$_SESSION['basis_hash'] = md5($this->ip.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
		} 
		else if ( $_SESSION['basis_hash'] != md5($this->ip.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']) )
		{
			$this->destroy();
		}
	}
	
	public function getIp(){ return $this->ip; }
	
	public function destroy()
	{
		session_destroy();
		setcookie('SID', '', time()-3600, BASEPATH);
		session_start();
		Core::refresh();
	}
}