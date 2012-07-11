<?php
class Model_DbTable_HandlerSocket_SelectParser
{
	const FILTER_OPERATOR = 0;
	const FILTER_OPERATE = 1;
	const FILTER_POSITION = 2;
	const FILTER_VALUES = 3;
	
	static $operateList = array (
			'gt' => '>',
			'gte' => '>=',
			'eq' => '=',
			'lt' => '<',
			'lte' => '<=');
	
	static $filterOperateList = array (
			'andGt' => '>',
			'andGte' => '>=',
			'andEq' => '=',
			'andLt' => '<',
			'andLte' => '<='); 
	
	protected $_indexId;
	protected $_operate = '=';
	protected $_values = array();
	protected $_limit = 1;
	protected $_offset = 0;
	protected $_filters = array();
	protected $_filterColumn = array();
	protected $_inValues = array();
	
	
	
	public function __construct ($indexData)
	{
		$this->_indexId = $indexData['indexId'];
		$this->_filterColumn = $indexData['filters'];
		return $this;
	}
	
	public function __call($func, $arguments)
	{
		
		if (count($arguments) === 2) {
			if (!isset(self::$filterOperateList[$func])) {
				throw new Exception('Not Found Method (filter)');
			}
			$column = $arguments[0];
			$values = $arguments[1];
			$this->_filters[] = array(
						self::FILTER_OPERATOR => 'F', 
						self::FILTER_OPERATE => self::$filterOperateList[$func],
						self::FILTER_POSITION => $this->_getIndexPosition($column), 
						self::FILTER_VALUES => $values);
		} else {
			if (!isset(self::$operateList[$func])) {
				throw new Exception('Not Found Method (key-value)');
			}
			$this->_operate = self::$operateList[$func];
			$this->search($arguments[0]);
		}
		return $this;
	}
	
	
	public function search($values)
	{
		if (is_array($values)){
			$this->_values = $values;
		} else {
			$this->_values[0] = $values;
		}
		return $this;
	}
	
	public function in($values)
	{
		$this->_inValues = $values;
		return $this;
	}
	
	
	public function limit($limit, $offset = 0)
	{
		$this->_limit = $limit;
		$this->_offset = $offset;
		return $this;
	}
	
	private function _getIndexPosition($chkColumn)
	{
		foreach($this->_filterColumn as $position => $column) {
			if ($chkColumn === $column) {
				return $position;
			}
		}
		throw new Exception('Not found index column');
	}
	
	public function __toString()
	{
		return json_encode(array(
				'indexId' => $this->_indexId,
				'operate' => $this->_operate,
				'values' => $this->_values,
				'limit' => $this->_limit,
				'filters' => $this->_filters,
				'offset' =>  $this->_offset,
				'inValues' => $this->_inValues));
	}
	
	public function getSelect()
	{
		return array(
				'indexId' => $this->_indexId,
				'operate' => $this->_operate,
				'values' => $this->_values,
				'limit' => $this->_limit,
				'filters' => $this->_filters,
				'offset' =>  $this->_offset,
				'inValues' => $this->_inValues);
	} 
}