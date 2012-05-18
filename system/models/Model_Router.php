<?php if ( ! defined('SITE')) exit('No direct script access allowed');

/**
 * System Router
 *
 * @author		Andrej Sevastianov
 * @copyright	Copyright (c) 2011
 */
class Model_Router
{
	protected 
		$segments = array(),
		$args = array(),
		$controller,
		$method,
		$standart;
		
	public function __construct($uri = false)
	{
		
		Core::event('pre_routing');
		$config = Load::Configs()->get('router');
		
		if ( $uri === false ) $uri = $_SERVER['PATH_INFO'];
		$uri = strtolower(trim($uri, '/'));
	//	if ( $uri === '' ) Core::redirect($config['index']);
		if ( $uri === '' ) $uri = $config['index'];
		
		$this->segments = preg_split('/\//', $uri, -1, PREG_SPLIT_NO_EMPTY);
		// Thanks CodeIgnite!
		// Loop through the route array looking for wild-cards
		foreach ($config['routes'] as $key => $val)
		{
			// Convert wild-cards to RegEx
			$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));

			// Does the RegEx match?
			if (preg_match('#^'.$key.'$#', $uri))
			{
				// Do we have a back-reference?
				if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
				{
					$uri = preg_replace('#^'.$key.'$#', $val, $uri);
				} else {
					$uri = $val;
				}
				break;
			}
		}

		// Разбираем адрес
		$this->args			= preg_split('/\//', $uri, -1, PREG_SPLIT_NO_EMPTY);
		if ( count($this->args) == 0 ) throw new Exception('Router configuration error', 404);
		
		$this->controller 	= ucfirst(strtolower(trim(array_shift($this->args), '_')));
		$this->method		= strtolower(trim((count($this->args) > 0)?array_shift($this->args):'index', '_'));
		
		$this->standart 	= ( isset($config['standart'][$this->controller]) AND $config['standart'][$this->controller] === true ) ? true : false;

		Core::event('post_routing');
	}
	
	
	public function isStandart() { return $this->standart; }
	
	public function getCurrentUri() { return implode('/', $this->segments); }
	
	public function getSegments() { return $this->segments; }
	public function getController() { return $this->controller; }
	public function getAction() { return $this->method; }
	public function getArgs() { return $this->args; }
}