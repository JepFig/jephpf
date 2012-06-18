<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Statement extends PDOStatement
{
	/**
	 * Sql-Instance
	 * 
	 * @access private
	 * @var object
	 */
	private $sqlInstance = null;
	
	/**
	 * Query
	 * 
	 * @access private
	 * @var String
	 */
	private $query;
	
	
	/**
	 * Init Statement
	 * 
	 * @access protected
	 */
	protected function __construct()
	{
		$this->benchmark = Core::getInstance()->benchmark;
	}
	
	/**
	 * Set instance
	 * 
	 * @access public
	 * @param object $sqlInstance Sql-Instance
	 */
	public function setInstance($sqlInstance)
	{
		$this->sqlInstance = $sqlInstance;
	}
	
	/**
	 * Set query
	 * 
	 * @access public
	 * @param String $query Query
	 */
	public function setQuery($query)
	{
		$this->query = $query;
	}
	
	/**
	* Returns number of executed rows
	*
	* @access public
	* @return int Number of executed rows
	*/	
	public function rows()
	{
		return parent::rowCount();
	}
	
	/**
	 * Execute Query
	 * 
	 * @access public
	 * @param array $input_parameters Data
	 * @throws SqlException
	 */
	public function execute($input_parameters = null)
	{
		try {
		
			if ($input_parameters != null && is_array($input_parameters) == false)
			{
				$input_parameters = array($input_parameters);
			}
			
			$this->benchmark->start('query');
			$status = parent::execute($input_parameters);
			$this->benchmark->end('query');
			Sql::$queryCount++;
			Sql::$queryTime += $this->benchmark->calculate('query');
		
			return $status;
			
		} catch (PDOException $e) {
		
			throw new SqlException($e->getMessage()."\n".$this->query, DEBUG::ERROR);
		}	
	}
	
	/**
	 * Flush & close statement
	 * 
	 * @access public
	 */
	public function close()
	{
		parent::closeCursor();
	}
}

?>