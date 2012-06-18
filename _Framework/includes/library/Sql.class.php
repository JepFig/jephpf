<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }

class SqlException extends Exception {}

class Sql extends Library
{
	/**
	 * PDO-instance
	 * 
	 * @access private
	 * @var object
	 */
	private $instances = array();
	
	/**
	 * Server
	 * 
	 * @access public
	 * @var string
	 */
	private $server;
	
	/**
	 * Active transaction
	 * 
	 * @access private
	 * @var bool
	 */
	private $isActiveTransaction = false;
	
	/**
	 * Number of queries
	 * 
	 * @access public
	 * @var int
	 */
	public static $queryCount = 0;
	
	/**
	 * Query-Time
	 * 
	 * @access public
	 * @var int
	 */
	public static $queryTime = 0;
	
	/**
	 * Fetch-Mode
	 * 
	 * @var int
	 */
	private $fetchMode = PDO::FETCH_ASSOC;
	
	
	/**
	 * Init Sql
	 * 
	 * @access public
	 */
	public function __construct()
	{
		require_once Settings::get('corePath').'includes/library/Sql/Statement.class.php';
		Settings::load('Sql.class');
		Helper::load('StringHelper');
		
		$this->benchmark = Core::load('Benchmark');
		$defaultSqlServer = Settings::get('defaultSqlServer');
		
		if (!empty($defaultSqlServer))
		{
			$this->setServer($defaultSqlServer);
		}
	}
	
	/**
	 * Destruct Sql
	 * 
	 * @access public
	 */
	public function __destruct()
	{
		$this->close();
	}
	
	/**
	 * Set Sql-Server
	 * 
	 * @access public
	 * @param string $server Sql-server
	 * @return Sql self-instance
	 */
	public function setServer($server)
	{
		$this->server = $server;
		
		if (!isset($this->instances[$this->server]))
		{
			$SqlServer = Settings::get('sqlServer');
			
			if (!isset($SqlServer[$this->server]))
			{
				throw new SqlException('SqlServer-config ('.$this->server.') cannot be found');	
			}
			
			$config = $SqlServer[$this->server];
			
			try 
			{
				$this->instances[$this->server] = new PDO($config['driver'].':host='.$config['host'].';dbname='.$config['database'].';port='.$config['port'], $config['user'], $config['password'],
														  array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
																PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
																PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
				$this->getNativeInstance()->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('Statement', array($this)));
				
				if (isset($config['fetchMode']) && !empty($config['fetchMode']))
				{
					$this->setFetchMode($config['fetchMode']);
				}
				
			} catch (PDOException $e) {
				
				throw new SqlException('Cannot connect to Sql('.$this->server.'): '.$e->getMessage());
			}
		}
		
		return $this;
	}
	
	public function setFetchMode($fetchMode)
	{
		switch ($fetchMode)
		{
			case 'array':
			$this->fetchMode = PDO::FETCH_ASSOC;
			break;	
			
			case 'object':
			$this->fetchMode = PDO::FETCH_OBJ;
			break;
			
			default:
			throw new SqlException('Fetch-mode ('.$fetchMode.') not supported', DEBUG::WARNING);
		}
	}
	
	/**
	 * Returns available PDO-driver
	 * 
	 * @access public
	 * @return array Available PDO-driver
	 */
	public function getAvailableDrivers()
	{
		return $this->getNativeInstance()->getAvailableDrivers();
	}
	
	/**
	 * Returns PDO-instance
	 * 
	 * @access public
	 * @return Statement PDO-instance
	 */
	public function getNativeInstance()
	{
		return $this->instances[$this->server];
	}
	
	/**
	 * Start transaction-sequence
	 * 
	 * @access public
	 * @throws SqlException
	 * @return bool success?
	 */
	public function startTransaction()
	{
		if ($this->isActiveTransaction)
		{
			throw new SqlException('Transaction already in progress', DEBUG::WARNING);
		}
		
		$this->isActiveTransaction = true;
		
		return $this->getNativeInstance()->beginTransaction();
	}
	
	/**
	 * Commit transaction-sequence
	 * 
	 * @access public
	 * @throws SqlException
	 * @return bool success?
	 */
	public function commitTransaction()
	{
		if ($this->isActiveTransaction == false)
		{
			throw new SqlException('No active transaction', Debug::WARNING);
		}
		
		$this->isActiveTransaction = false;
		
		return $this->getNativeInstance()->commit();
	}
	
	/**
	 * Revoke transaction-sequence
	 * 
	 * @access public
	 * @throws SqlException
	 * @return bool success?
	 */
	public function revokeTransaction()
	{	
		if ($this->isActiveTransaction == false)
		{
			throw new SqlException('No active transaction', Debug::WARNING);
		}
		
		return $this->getNativeInstance()->rollBack();
	}
	
	/**
	 * Escape value for safe queries
	 * 
	 * @access public
	 * @param mixed $value Value
	 * @return mixed Escaped value
	 */
	public function escape($value)
	{
		$this->getNativeInstance()->quote($value);
	}
	
	/**
	* Execute query that returns the number of executed rows
	* 
	*  Insert, Update, Delete, etc.
	*
	* @access public
	* @throws SqlException
	* @param string 	$query 			Query
	* @throws SqlException
	* @return int Number of excuted rows
	*/
	public function execute($query)
	{
		try {
			
			$this->load('Benchmark')->start('query');
			$rows = $this->getNativeInstance()->exec($query);
			$this->benchmark->end('query');
			self::$queryTime += $this->benchmark->calculate('query');
			self::$queryCount++;
			
			return $rows;
			
		} catch (PDOException $e) {
					
			throw new SqlException($e->getMessage()."\n".$query, DEBUG::ERROR);
		}			
	}	
	
	/**
	 * Execute query and return result-set
	 * 
	 * @param string	$query	Query
	 * @param mixed		$data	Execute-Data
	 * @throws SqlException
	 * @return Statement Statement
	 */
	public function query($query, $data = null)
	{
		$statement = $this->getNativeInstance()->prepare($query);
		$statement->setFetchMode($this->fetchMode);
		$statement->setInstance($this);
		$statement->setQuery($query);
		
		if (isset($data))
		{
			if (is_array($data) == false)
			{
				$data = array($data);
			}
			
			$i = 1;
			
			foreach ($data as $param)
			{
				if (StringHelper::isNumeric($param))
				{
					$param = intval($param);
				}
				
				$statement->bindValue($i, $param, (is_numeric($param) ? PDO::PARAM_INT : PDO::PARAM_STR));
				$i++;
			}
		}
		
		$statement->execute();
		
		return $statement;
	}
	
	/**
	* Prepare query
	*
	* @param string	$query	Query
	* @throws SqlException
	* @return Statement Statement
	*/
	public function prepare($query)
	{
		$statement = $this->getNativeInstance()->prepare($query);
		$statement->setFetchMode($this->fetchMode);
		$statement->setInstance($this);
		$statement->setQuery($query);
		
		return $statement;
	}
	
	/**
	 * Get last inserted Primary-ID
	 * 
	 * @access public
	 * @return in Last ID
	 */
	public function lastInsertID()
	{
		return $this->getNativeInstance()->lastInsertId();
	}
	
	/**
	 * Returns number of queries
	 * 
	 * @access public
	 * @return int Number of queries
	 */
	public function getQueryCount()
	{
		return self::$queryCount;
	}
	
	/**
	 * Returns query-time
	 * 
	 * @access public
	 * @return int Query-Time
	 */
	public function getQueryTime()
	{
		return self::$queryTime;
	}
	
	/**
	 * List tables
	 * 
	 * @access public
	 * @return mixed table-array or false if no tables exist
	 */
	public function listTables()
	{
		$result = $this->query('SHOW TABLES');
		
		if ($result->rows())
		{		
			return $result->fetchAll();
		}
		
		return false;
	}
	
	/**
	 * Repair database
	 * 
	 * @access public
	 */
	public function repairDatabase()
	{
		$tables = $this->listTables();
		
		foreach ($tables as $table)
		{
			$this->repairTable($table[0]);
		}
	}
	
	/**
	 * Optimize database
	 * 
	 * @access public
	 */
	public function optimizeDatabase()
	{
		$tables = $this->listTables();
	
		foreach ($tables as $table)
		{
			$this->optimizeTable($table[0]);
		}
	}	
	
	/**
	 * Optimize Table
	 * 
	 * @access public
	 * @param string $table Table
	 */
	public function repairTable($table)
	{
		$this->execute('REPAIR TABLE '.$table);
	}
	
	/**
	* Repair Table
	*
	* @access public
	* @param string $table Table
	*/	
	public function optimizeTable($table)
	{
		$this->execute('OPTIMIZE TABLE '.$table);
	}
	
	/**
	 * Close connection
	 * 
	 * @access public
	 */
	public function close()
	{
		$this->instances[$this->server] = null;
	}
}

?>