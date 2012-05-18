<?php if ( ! defined('SITE') ) exit('No direct script access allowed');

class Model_Configs
{
	protected $cash = array();

	/**
	 * ------------------------------------------------------
 	 * Get configs
 	 * ------------------------------------------------------
 	 * @param 1  string $key
 	 * @return array
 	 */
	public function get($name)
	{
		$name = strtolower($name);

		if ( isset($this->cash[$name]['data']) ) return $this->cash[$name]['data'];
		
		if ( ! ($file = $this->getFileName($name)) )
		{
			return $this->cash[$name]['data'] = false;
		} else {
			return $this->cash[$name]['data'] = include($file[0].$file[1]);
		}
	}
	
	protected function getFileName($name)
	{
		if ( isset($this->cash[$name]['file']) ) return $this->cash[$name]['file'];
	
		if ( file_exists(DIR_SITE.'configs/'.$name.'.php') )
		{
			return $this->cash[$name]['file'] = array(DIR_SITE, 'configs/'.$name.'.php');
		}
		else if ( file_exists(DIR_APP.'configs/'.$name.'.php') )
		{
			return $this->cash[$name]['file'] = array(DIR_APP, 'configs/'.$name.'.php');
		}
		else
		{
			return $this->cash[$name]['file'] = false;
		}
	}
	
	public function writable($name)
	{
		$name = strtolower($name);
		
		if ( ! ($file = $this->getFileName($name)) 
			OR ($file[0] == DIR_APP AND ! Load::Acl()->check('app_config_write', false) )
			OR ($file[0] == DIR_SITE AND ! Load::Acl()->check('site_config_write', false) )
			)
		{
			return false;
		}
		
		return is_writable($file[0].$file[1]);
	}
	
	public function write($name, $config)
	{
		$name = strtolower($name);
		if ( ! ($file = $this->getFileName($name)) ) return false;
		return file_put_contents($file[0].$file[1], $this->preparePHP($config));
	}
	
	public function preparePHP($config)
	{
		unset($config['_file']);
		return '<?php if ( ! defined(\'SITE\') ) exit(\'No direct script access allowed\');'
				.PHP_EOL.PHP_EOL.'return array('.PHP_EOL
				.$this->configArrayPHP($config)
				.');'.PHP_EOL.PHP_EOL
				.'/* End of config file */'.PHP_EOL
				.'/* Created automatic by Lib_Config.php ('.date("Y/m/d H:i:s").') */';
	}
	
	protected function configArrayPHP($arr, $lvl = 1)
	{
		$content = '';
		foreach ( $arr as $key => $val)
		{
			for($i=0;$i<$lvl;$i++) $content .= "	";
			
			// echo var_dump($key);
			if ( is_string($key) )	$content .= "'".$key."' => ";
				else $content .= $key.' => ';
			
			if ( is_array($val) )
			{
				$content .= 'array('.PHP_EOL.$this->configArrayPHP($val, $lvl+1);
				for($i=0;$i<$lvl;$i++) $content .= "	";
				$content .= "),".PHP_EOL;
			} else if ( false === $val ) {
				$content .= "false,".PHP_EOL;
			} else if ( true === $val ) {
				$content .= "true,".PHP_EOL;
			} else if ( null === $val ) {
				$content .= "null,".PHP_EOL;
			} else if ( is_string($val) OR ( '' === $val AND null !== $val ) ) {
				$content .= "'".str_replace("'", "\\'",$val)."',".PHP_EOL;
			} else {
				$content .= $val.",".PHP_EOL;
			}
		}
		return $content;
	}
	
	/**
	 * Edit configurations files
	 * ---------------------------------
	 **/ 
	public function configsAdminGET($ajax = false, $name = '')
	{
		if ( $ajax == 'load' ) return $this->configsAdminAJAX($name);
		
		$configs = array();

		// Уровень доступа: сайт
		if ( Load::Acl()->check('site_config_write', false) )
		{
			$fp = opendir(DIR_SITE.'configs');
			
			while($cv_file = readdir($fp))
				if ( is_file(DIR_SITE.'configs/'.$cv_file) )
					$configs[] = SITE.':'.substr($cv_file,0,-4);

	    	closedir($fp);
		}

		// Уровень доступа: фреймворк
		if ( Load::Acl()->check('app_config_write', false) )
		{
			$fp = opendir(DIR_APP.'configs/');
			
		    while($cv_file = readdir($fp))
		        if ( is_file(DIR_APP.'configs/'.$cv_file) )
		        	$configs[] = '@bike:'.substr($cv_file,0,-4);

		    closedir($fp);
		}
		Load::View('configs/admin', array('configs' => $configs));
	}
	
	public function configsAdminPOST()
	{
		$log = Load::Log();
		list($type, $name) = explode(':', $_POST['name']);

		$type = ( $type == '@bike' ? DIR_APP : ( $type == SITE ? DIR_SITE : false ) );

		if ( ! $type ) {
			if ( $log )$log->write('File type is not allowed '.$_POST['name'], 'errors');
			Load::Msg()->Add('Неверный тип данных. Операция прервана!');
		} else if ( ! $tmp = $this->writable($name) ) {
			if ( $log )$log->write('Can not write config '.$_POST['name']);
			Load::Msg()->Add('Файл защищен от записи '.print_r($tmp,1));
		} else {
			if ( $log )$log->write('Save config '.$_POST['name']);
			file_put_contents($type.'/configs/'.$name.'.php', $_POST['config']);
			Load::Msg()->Add('Файл конфигурации <strong>"'.$name.'"</strong> обновлен', 'ok');
		}

		Core::refresh();	
	}
	
	public function configsAdminAJAX($name)
	{
		$ajax = Load::Ajax()->setFormat('text');
		
		list($type, $name) = explode(':', $name);
		
		$type = ( $type == '@bike' ? DIR_APP : ( $type == SITE ? DIR_SITE : false ) );
		
		if ( ! $type OR  ! $tmp = $this->writable($name) )
		{
			Core::error('File not faund', '404');
		} else {
			$ajax->add(file_get_contents($type.'configs/'.$name.'.php'));
		}
	}
}