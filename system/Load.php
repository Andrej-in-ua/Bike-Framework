<?php if ( ! defined('SITE') ) exit('No direct script access allowed');

/**
 * Magic factory
 *
 * @author		Andrej Sevastianov
 * @copyright	Copyright (c) 2011
 * @version		0.1.0
 */
class Load
{
	protected
		$config = array(
			'controller' => array(
				'dir' => 'controllers',
				'prefix' => ''
			),
			'model' => array(
				'dir' => 'models',
				'prefix' => 'Model_'
			)
		),
		
		$cash = array();
		
	public static function __callStatic($name, $args)
	{
		$load =& Core::getKey('Load');	
		if ( ! $load instanceof Load )
		{
			$load =& Core::addKey('Load', new Load);
		}
		
		return $load->factory($name, $args);
	}
	
	
	protected function factory($name, $args)
	{
		$name = ucfirst(strtolower($name));

		switch ($name)
		{
			case 'View': 
				if ( ! isset($args[0]) ) throw new Exception('Required view name');
				if ( ! isset($args[1]) ) $args[1] = array();
				
				return $this->view($args[0], $args[1]);
				break;
			case 'Controller':
				return $this->single('controller', $args[0]);
				break;
			
			case 'Once':
				return $this->init($args);
				break;
				
			case 'Single':
				if ( ! isset($args[0]) OR ! isset($args[1]) ) throw new Exception('Bad use single load method');
				if ( ! isset($args[2]) ) $args[2] = array();
				return $this->single($args[0], $args[1], $args[2]);
				
				break;
			
			default:
				return $this->single('model', $name, $args);
				break;
		}
	}
	
	public function isLoaded($type, $name)
	{
		$name = ucfirst(strtolower($name));
		return ( isset($this->cash[$type][$name]) ) ? true : false;
	}
	
	protected function single($type, $name, $args = array())
	{
		if ( isset($this->cash[$type][$name]) ) {
			if ( count($args) > 0 ) throw new Exception('Class alredy constructed, use "Load::Once(\''.$type.'\', \''.$name.'\', '.print_r($args,1).')"');
			return $this->cash[$type][$name];
		}
		return $this->cash[$type][$name] = $this->init($type, $name, $args);
	}
	
	protected function init($type, $name = false, $args = array())
	{
		if ( is_array($type) )
		{
			if ( count($type) < 2 ) throw new Exception('Required two parametrs');
			$args = ( count($type) === 3 ) ? $type[2] : array();
			$name = $type[1];
			$type = $type[0];
		}
		
		if ( false === $name ) throw new Exception('Not obtained the name of the class');
		
		$name = $this->getName($name, $type);
		$dir =& $this->config[$type]['dir'];
			
		$class = $name;
		if ( file_exists(DIR_SITE.$dir.'/EXT_'.$name.'.php') )
		{
			include_once DIR_SITE.$dir.'/EXT_'.$name.'.php';
			$class = 'EXT_'.$class;
		}
		
		if ( file_exists(DIR_SITE.$dir.'/'.$name.'.php') )
		{
			include_once DIR_SITE.$dir.'/'.$name.'.php';
			
			$reflectionObj = new ReflectionClass($class);
			return $reflectionObj->newInstanceArgs($args);
			
		}
		if ( $type == 'controller' AND ! Load::Router()->isStandart() ) return false;
		
		if ( file_exists(DIR_APP.$dir.'/'.$name.'.php') )
		{
			include_once DIR_APP.$dir.'/'.$name.'.php';
				
			$reflectionObj = new ReflectionClass($class);
			return $reflectionObj->newInstanceArgs($args);
		}
		
		return false;
	}
	
	
	protected function getName($name, $type)
	{
		return $this->config[$type]['prefix'].ucfirst(strtolower($name));
	}
	
	protected function view()
	{
		extract((array)func_get_arg(1));
		
		if ( file_exists(DIR_SITE.'views/'.func_get_arg(0).'.php') )
		{
			include DIR_SITE.'views/'.func_get_arg(0).'.php';
		}
		else if ( file_exists(DIR_APP.'views/'.func_get_arg(0).'.php') )
		{
			include DIR_APP.'views/'.func_get_arg(0).'.php';
		}
		else
		{
			Core::error('Cannot find view "'.func_get_arg(0).'"');
			return false;
		}
	}
}

/* End of file Load.php */
/* Location: ./Load.php */