<?php
class Model_Database extends PDO
{
	protected
		$query_count = 0,
		$total_time = 0,
		$query = array();
	
	
	public function __construct()
	{
		$config = Load::Configs()->get('database');
		
		$time = microtime(true);
		
		parent::__construct($config['dsn'], $config['username'], $config['password'], $config['driver_options']);
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		if (!$this->getAttribute(PDO::ATTR_PERSISTENT)) 
		{
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('DebugPDOStatement', array($this)));
		}
		
		$time = microtime(true) - $time;

		$this->debug($time, 'CONNECT');
		$this->exec("SET NAMES `utf8` COLLATE 'utf8_general_ci';");
	}

	public function exec($sql)
	{
	//	Core::call(false, '[DB] exec "'.$sql.'"');
		$time = microtime(true);
		$return = parent::exec($sql);
		$time = microtime(true) - $time;
		
		$this->debug($time, $sql);
		
		return $return;
	}

	public function query()
	{	
		$args = func_get_args();
		// Core::call(false, '[DB] query "'.$args[0].'"');

		$time = microtime(true);
		$return = call_user_func_array(array($this, 'parent::query'), $args);
		$time = microtime(true) - $time;
		
		$this->debug($time, $args[0]);

		return $return;
	}
	
	public function debug($time, $query, $params = array())
	{
		$this->query_count++;
		$this->time_total += $time;
		$this->query[] = array($time, $query, $params);
		return $this;
	}
	public function getQuerys() { return $this->query; }
	public function getTimeTotal() { return $this->time_total; }
}

class DebugPDOStatement extends PDOStatement
{
	protected $pdo;
	private $params = array();

	protected function __construct(Model_Database $pdo)
	{
		$this->pdo = $pdo;
	}

	public function execute($input_parameters = null)
	{
		// Core::call(false, '[DB] query "'.$this->queryString.'"');
		$time = microtime(true);
		$return = parent::execute($input_parameters);
		$time = microtime(true) - $time;
		
		$this->pdo->debug($time, $this->queryString, $input_parameters);

		return $return;	
	}
}