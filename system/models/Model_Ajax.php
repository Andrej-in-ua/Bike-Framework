<?php  if ( ! defined('SITE')) exit('No direct script access allowed');

class Model_Ajax
{
	protected $buffer = array();
	protected $format = 'json';
	
	public function __construct()
	{
		Core::setAjax(true);
	}
	
	public function setFormat($format)
	{
		switch ($format)
		{
			case 'serialize':
			case 'json':
			case 'xml':
			case 'text':
				$this->format = $format;
				return $this;
			default: return false;
		}
	}
	
	public function add($data)
	{
		if ( $this->format != 'text' )
		{
			$this->buffer[] = $data;
		} else {
			$this->buffer = $data;
		}
		return $this;
	}
	
	public function error($msg, $code)
	{
		if ( $this->format != 'text' )
		{
			$this->buffer = array('error' => array('code' => $code, 'msg' => $msg));
		} else {
			$this->buffer = '['.$code.'] ERROR: '.$msg;
		}
		
		return $this->send();
	}
	
	public function send($data = false)
	{
		switch ($this->format)
		{
			case 'serialize':
			case 'json':
				header('Content-Type: text/json; charset=utf-8');
				if ( $data ) {
	
					die(json_encode($data));
				} else {
					die(json_encode($this->buffer));
					header('X-JSON: '.json_encode($this->buffer));
				}
				
				exit;
			case 'xml':
			case 'text': die($this->buffer);
			default: return false;
		}
	}
}