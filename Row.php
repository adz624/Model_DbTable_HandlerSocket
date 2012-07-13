<?php
class Model_DbTable_HandlerSocket_Row
{
	private $_data = array();
	private $_columns  = array();
	
	public function __construct($columns, $data)
	{
		$this->_data = $data;
		$this->_columns = $columns;
	}
	
	
	/**
	 * Array mapping取出Row欄位
	 *
	 * @return void
	 * @author eddie
	 * @version 0.06 2012-07-13
	 */
	public function __get($key)
	{
		if (!isset($this->_columns[$key])) {
			return false;
		}
		return $this->_data[$this->_columns[$key]];
	}
	
	/**
	 * dump出row的資料, For test 
	 *
	 * @return void
	 * @author eddie
	 * @version 0.06 2012-07-13
	 */
	public function dump()
	{
		$data = array();
		foreach($this->_columns as $key => $val) {
			$data[$key] = $this->_data[$val];
		}
		var_dump($data);
	}
}