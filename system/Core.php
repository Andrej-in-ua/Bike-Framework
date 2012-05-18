<?php if ( ! defined('SITE') ) exit('No direct script access allowed');

/**
 * Core of Bike-Framework
 *
 * @author		Andrej Sevastianov
 * @copyright	Copyright (c) 2011
 * @version		0.1.0
 */
 
class Core
{
	static private
		$instance;
	
	private
		$ajax,
		$timestart,
		$register;
	
	/**
	 * Get object of Core
	 * @return object of Core
	 */
	public static function singleton()
	{
		if ( self::$instance === NULL ) self::$instance = new Core();
		return self::$instance;
	}

	/**
	 * Construct object of Core
	 */
	private function __construct()
	{
		$this->timestart = microtime(true);
		self::$instance =& $this;
		
		$this->ajax = (
				isset($_SERVER['HTTP_X_REQUESTED_WITH'])
				AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
			) ? true : false; 
		
		ob_start();
		
		set_error_handler(create_function('$c, $m, $f, $l', 'if ( error_reporting() === 0 ) { return; } throw new Exception("[".$f.":".$l."] ".$m, $c);'), E_ALL);
		register_shutdown_function(create_function('', '$a=error_get_last(); if ($a["type"] == 1) return Core::error("[".$a["file"].":".$a["line"]."] ".$a["message"], 500);'));
		
		Core::event('init_core');
	}
	/**
 	 * Destruct object of Core
 	 */
	public function __destruct()
	{
		if ( ! DEBUG || $this->ajax ) return;
		
		$log = $this->register->log;
		$this->register->log = new stdClass();
		
		$load =& Core::getKey('Load');	
		if ( $load->isLoaded('model', 'Database') )
		{
			$q = Load::Database()->getQuerys();
			$t = Load::Database()->getTimeTotal();
		} else {
			$q = $t = null;
		}
		
		Load::view('console', array(
			'console' => $log->console,
			'errors' => ( isset($log->errors) ? $log->errors : array() ),
			'query' => $q,
			'time_total' => $t,
			'core' => $this
			)
		);
	}
	
	
	/**
 	 * ------------------------------------------------------
 	 * Getters
 	 * ------------------------------------------------------
 	 * @return	bool
 	 */
	public static function isAjax() { return self::$instance->ajax; }
	public static function setAjax($a) { return self::$instance->ajax = $a; }
	
	
	/**
 	 * ------------------------------------------------------
 	 * Run application
 	 * ------------------------------------------------------
 	 * @return	object of Core
 	 */
	public static function run()
	{
		$core = Core::singleton();
		try {

			if ( ! $router = Load::Router() ) throw new Exception('Router not faund', 404);

			// Starting controller
	 		Core::event('pre_controller');
	 	
	 		if ( ! $site = Load::Controller($router->getController()) ) throw new Exception('Controller '.$router->getController().' not faund', 404);
	 		
	 		// Run controller action
	 		if ( Core::isAjax() AND method_exists($site, $router->getAction().'AjaxAction') )
	 		{
	 			call_user_func_array(array(&$site, $router->getAction().'AjaxAction'), $router->getArgs());
	 		}
			else if ( method_exists($site, $router->getAction().'Action'.$_SERVER['REQUEST_METHOD']) )
			{
				call_user_func_array(array(&$site, $router->getAction().'Action'.$_SERVER['REQUEST_METHOD']), $router->getArgs());
			}
			elseif ( method_exists($site, $router->getAction().'Action') )
			{
				call_user_func_array(array(&$site, $router->getAction().'Action'), $router->getArgs());	
			}
			else
			{
				throw new Exception('Can not find need action '.$router->getAction(), 404);
			}
			
			// Controller is finished
	 		Core::event('post_controller');

		} catch(Exception $e) {
		    Core::error($e->getMessage(), $e->getCode());
		}
		return $core;
	}	

 	/**
 	 * ------------------------------------------------------
 	 * Creat Page before send response
 	 * ------------------------------------------------------
 	 * @return	object of Core
 	 */
	public static function creatpage()
	{
		if ( Core::isAjax() === true ) return Load::Ajax()->send();
		
		$meta = Core::getKey('meta');
		$meta['content'] = ob_get_contents();
		ob_clean();
		Load::view('layout', $meta);
		Core::event('post_creatpage');
		
		return self::$instance;
	}
	
	
	
	
	
	/**
 	 * ------------------------------------------------------
 	 * System events triger
 	 * ------------------------------------------------------
 	 * @param 1  string  $event
 	 * @param 2  string  error code
 	 * @return	object of Core
 	 */
 	public static function event($point, $error_code = false)
	{
		if ( DEBUG ) Core::getKey('log')->console[] = array(
				sprintf('%.5f', (microtime(true) - Core::singleton()->timestart)),
				 //memory_get_peak_usage (),
				memory_get_usage(),
				$point,
				'',
				$error_code
			);
		
		$config = Load::Configs()->get('events');
		
		if ( isset( $config[$point]) )
		{
			foreach ($config[$point] as $event)
			{
				
				if ( isset($event['load']) )
				{	
					if ( ! $obj = forward_static_call_array(
									array('Load', $event['load']),
									( isset($event['init-args']) ? $event['init-args'] : array() )
									)
						) Core::error('Event '.$point.' - can not load '.$event['load'], 0);
					
					if ( isset($event['method']) AND method_exists($obj, $event['method']) ){
						call_user_func_array(array(&$obj, $event['method']), ( isset($event['args']) ? $event['args'] : array() ));
					}
				}
			}
		}
		
		
		
		
		
		return self::$instance;
	}
	
	/**
 	 * ------------------------------------------------------
 	 * Get register key
 	 * ------------------------------------------------------
 	 * @param 1  string $key
 	 * @return mixed
 	 */
	public static function &getKey($key)
	{
		return self::$instance->register->$key;
	}
	
	/**
	 * ------------------------------------------------------
 	 * Add register key
 	 * ------------------------------------------------------
 	 * @param 1  string $key
 	 * @param 2  mixed  $val
 	 * @return mixed
 	 */
	public static function &addKey($key, $val)
	{
		self::$instance->register->$key = $val;
		return Core::getKey($key);
	}
	
	
	/**
	 * ------------------------------------------------------
	 *  Error
	 * ------------------------------------------------------ 
 	 * @param 1  string  $msg
 	 * @param 2  int     $code
 	 * @return object of Core
 	 */
	public static function error($msg, $code = 0)
	{
		Core::getKey('log')->errors[] = array($code => $msg);
		Core::event('error', $code);

		if ( Core::isAjax() ) return Load::Ajax()->error($msg, $code);
		
		if ( $hendler = Load::Error() )
		{
			return $hendler->e($msg, $code);
		} else {
			if ( $code == 0 ) return false;
			ob_end_clean();
			ob_start();
			header('HTTP/1.0 404 Not Faund');
			echo '<h2>404 страница не найдена</h2>';
			
			Core::creatpage();
			exit;
		}
	}
	
	/**
 	 * ------------------------------------------------------
	 *  Redirect
	 * ------------------------------------------------------
 	 * @param	1	string	$uri
 	 * @param	2	mixed	$code	http response code
 	 */
	public static function redirect($uri = '', $code = 302)
	{
		if ( is_array($uri) )
		{
			foreach ( $uri as $key => $val )
			{
				$tmp .= $val.'/';
			}
			$uri = $tmp;
			unset($tmp);
		}
		if ( ! preg_match('#^https?://#i', $uri))
		{
			$uri = BASEURL.ltrim($uri, '/|\\');
		}

		header("Location: ".$uri, TRUE, $code);
		exit;
	}
	
	public static function refresh()
	{
		return Core::redirect($_SERVER['PATH_INFO']);
	}
}

class Register
{
	
}

/* End of file Core.php */
/* Location: ./Core.php */