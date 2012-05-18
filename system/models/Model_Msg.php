<?php  if ( ! defined('SITE')) exit('No direct script access allowed');

class Model_Msg
{
	public function __construct()
	{
		if ( ! Load::Session() ) throw new Exception('Need session model', 500);
	}
	
	public function Add($msg, $type = 'error')
	{
		$_SESSION['msg'][] = array($type, $msg);
	}
	
	public function notice($msg) { $this->Add($msg, 'notice'); }
	public function error($msg) { $this->Add($msg, 'error'); }
	public function ok($msg) { $this->Add($msg, 'ok'); }
	
	public function show()
	{
		if ( ! isset($_SESSION['msg']) OR count($_SESSION['msg']) == 0 ) return;
		foreach ( $_SESSION['msg'] as $num => $msg )
		{
			switch ($msg[0]) {
				case 'error': Load::View('msg/error', array('msg' => $msg[1])); break;
				case 'ok': Load::View('msg/ok', array('msg' => $msg[1])); break;
				case 'notice': Load::View('msg/notice', array('msg' => $msg[1])); break;
			}
		}
		unset($_SESSION['msg']);
	}
}