<?php
class Model_DbTable_HandlerSocket_Select
{
	/* 	filter array index */
	const FILTER_OPERATOR = 0;
	const FILTER_OPERATE = 1;
	const FILTER_POSITION = 2;
	const FILTER_VALUES = 3;
	
	/* __call function list */
	static $operateList = array (
			'gt' => '>',
			'gte' => '>=',
			'eq' => '=',
			'lt' => '<',
			'lte' => '<=');
	/* __call function list */
	static $filterOperateList = array (
			'andGt' => '>',
			'andGte' => '>=',
			'andEq' => '=',
			'andLt' => '<',
			'andLte' => '<='); 
	
	/* query document */
	protected $_indexId;
	protected $_operate = '=';
	protected $_values = array();
	protected $_limit = 1;
	protected $_offset = 0;
	protected $_filters = array();
	protected $_filterColumn = array();
	protected $_inValues = array();
	protected $_columns = array();
	
	public function __construct ($openIndex)
	{
		$this->_indexId = $openIndex['indexId'];
		$this->_filterColumn = $openIndex['filters'];
		$this->_columns = $openIndex['columns'];
		return $this;
	}
	
	
	/**
	 * function mapping
	 *
	 * @author eddie
	 * @uses eddie
	 * @version 0.06 2012-07-11
	 */
	public function __call($func, $arguments)
	{
		if (isset(self::$operateList[$func])) {
			$this->_operate = self::$operateList[$func];
			$this->_search($arguments[0]);
		} else if (isset(self::$filterOperateList[$func])) {
			$column = $arguments[0];
			$values = $arguments[1];
			$this->_filters[] = array(
					self::FILTER_OPERATOR => 'F',
					self::FILTER_OPERATE => self::$filterOperateList[$func],
					self::FILTER_POSITION => $this->_getIndexPosition($column),
					self::FILTER_VALUES => $values);
		} else {
			throw new Exception('Not Found Method (filter)');
		}
		return $this;
	}
	
	/**
	 * 依照所選的Index做搜尋
	 *
	 * @param array, int, string $value 
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	private function _search($values)
	{
		if (is_array($values)){
			$this->_values = $values;
		} else {
			$this->_values[0] = $values;
		}
		return $this;
	}
	
	/**
	 * IN搜尋, example: WHERE key IN (1, 2, 3, 5)
	 *
	 * @param array $values 
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	public function in($values)
	{
		$this->_inValues = $values;
		return $this;
	}
	
	/**
	 * 設定limit值
	 *
	 * @param int $limit, int $offset
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	public function limit($limit, $offset = 0)
	{
		$this->_limit = $limit;
		$this->_offset = $offset;
		return $this;
	}
	
	/**
	 * 自動尋找filter array position
	 *
	 * @param string $chkColumn
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	private function _getIndexPosition($chkColumn)
	{
		foreach($this->_filterColumn as $position => $column) {
			if ($chkColumn === $column) {
				return $position;
			}
		}
		throw new Exception('Not found index column');
	}
	
	/**
	 * preview query object
	 *
	 * @return string
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
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
	
	/**
	 * 取得query object
	 *
	 * @return array 
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
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
	
	
	/**
	 * 取得index columns
	 *
	 * @return array
	 * @author eddie
	 * @version 0.06 2012-07-12
	 */
	public function getColumns()
	{
		return $this->_columns;
	}
}