<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Benchmark
{
	/**
	 * benchmarks
	 *
	 * @access private
	 * @var array
	 */
	private $benchmarks = array();


	/**
	 * Start benchmark
	 *
	 * @access public
	 * @param string 	$benchmark 	benchmark-identifier
	 * @param int 		$startTime 	microtime 				(Optional)
	 */
	public function start($benchmark, $startTime = false)
	{
		if ($startTime)
		{
			$this->benchmarks[$benchmark]['start'] = $startTime;
				
		} else {
				
			$this->benchmarks[$benchmark]['start'] = microtime(true);
		}
	}

	/**
	 * End runtime
	 *
	 * @access public
	 * @param string 	$benchmark 	benchmark-identifier
	 * @param int 		$endTime 	microtime 				(Optional)
	 */
	public function end($benchmark, $endTime = false)
	{
		if (!isset($this->benchmarks[$benchmark]))
		{
			Debug::logWarning('Benchmark ('.$benchmark.') does not exist');
			return false;
		}
		
		if ($endTime)
		{
			$this->benchmarks[$benchmark]['end'] = $endTime;
				
		} else {
				
			$this->benchmarks[$benchmark]['end'] = microtime(true);
		}
	}
	
	/**
	 * Calculate runtime
	 * 
	 * @access public
	 * @param string $benchmark benchmark-identifier
	 * @return int milliseconds
	 */
	public function calculate($benchmark)
	{
		if (!isset($this->benchmarks[$benchmark]))
		{
			Debug::logWarning('benchmark ('.$benchmark.') does not exist');
			return false;
		}		
		
		return ($this->benchmarks[$benchmark]['end'] - $this->benchmarks[$benchmark]['start']);
	}
}

?>