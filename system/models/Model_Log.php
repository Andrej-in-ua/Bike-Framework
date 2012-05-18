<?php if ( ! defined('SITE') ) exit('No direct script access allowed');

class Model_Log
{
	protected
		$patch,
		$ip,
		$uri;
	
	
	public function __construct()
	{
		$this->patch = Load::Configs()->get('log');
		$this->patch = $this->patch['patch'];
		
		$this->ip = ( $session = Load::Session() ) ? $session->getIp() : $_SERVER['REMOTE_ADDR'];
		$this->uri = Load::Router()->getCurrentUri();
	}
	
	public function write($msg, $patch = false)
	{
		if ( $patch === false )
			$patch = ( $user = Load::Acl()->getCurrentUser(true) AND $user['id'] === 0 ) ? 'admins/'.$user['login'] : 'system';

		$patch = $this->patch.$patch.'.log';
		
		if ( ! file_exists(dirname($patch)) ) mkdir(dirname($patch));
		
		$fp = fopen($patch, 'ab');
		fwrite($fp, '['.date('Y-m-d H:i:s').']['.$this->ip.']['.$this->uri.'] '.$msg.PHP_EOL);
		fclose($fp);

	}

/**
 * ------------------------------------------------------------------
 * Admin methods
 * ------------------------------------------------------------------
 **/
	public function activityAdmin($ajax = false, $start = 0)
	{
		if ( $ajax !== 'load' ) return Load::View('log/activity');
		Load::Ajax()->setFormat('json')->send($this->reed('admins/'.$_SESSION['admin_login'], 20, $start));
	}
	
	public function logsAdmin($ajax = false, $start = 0)
	{
		if ( $ajax !== 'load' ) return Load::View('log/admin', array('files' => $this->reedDir()));
		Load::Ajax()->setFormat('json')->send($this->reed($_POST['patch'], 20, $start));
	}
	
	protected function reedDir( $patch = '' )
	{
		$fp = opendir($this->patch.$patch);
		$files = array();
		while($cv_file = readdir($fp))
		{
			if ( $cv_file == '.' || $cv_file == '..' ) continue;
			if ( is_file($this->patch.$patch.$cv_file) )
				$files[] = $patch.substr($cv_file, 0, -4);
			else if ( is_dir($this->patch.$patch.$cv_file))
				$files = array_merge($files, $this->reedDir($patch.$cv_file.'/'));
		}
	    closedir($fp);
	    
	    return $files;
	}
	
	public function reed($patch, $limit = 50, $start = 0)
	{
		$patch = $this->patch.$patch.'.log';
		
		if ( ! is_readable($patch) ) return false;
		
		$file = array();
		$fp = fopen($patch, 'rb');
		while ( $tmp = fgets($fp) )
		{
			$file[] =  preg_split('#\] |\]\[#', ltrim($tmp, '['));
		}
		fclose($fp);
		return array_slice(array_reverse($file), $start, $limit);
	}
}