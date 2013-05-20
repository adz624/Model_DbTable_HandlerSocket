<?php
class Model_DbTable_HandlerSocket_RowSet implements SeekableIterator, Countable
{
	const SORT_ASC = 0;
	const SORT_DESC = 1;
	
	private $_position = 0;
	private $_data = array();
	private $_count = 0;
	private $_columnsMapping  = array();
	private $_rowKeyMapping = array();
	private $_assocMode = array();
	private $_keylist = array();
	 
	public function __construct($columns, $data, $assocMode = false )
	{
		$this->_data = $data;
		$this->_columnsMapping = $columns;
		$this->_count = count($data);
		
		/* 若非assoc mode減少使用過多mapping功能 */
		$this->_assocMode = $assocMode;
		if ($assocMode) {
			$this->_rowKeyMapping = array_keys($data);
		}
	}
	
	/**
	 * 輸出Array
	 *
	 * @return array
	 * @author eddie
	 * @version 0.06 2012-07-13
	 */
	public function toArray()
	{
		return $this->_data;
	}
	
	/**
	 * 取得欄位Array
	 *
	 * @return array
	 * @author eddie
	 * @version 0.06 2012-07-13
	 */
	public function getColumns()
	{
		return $this->_columns;
	}
	
	/**
	 * 當前row的array index
	 *
	 * @return int
	 * @author eddie
	 * @version 0.06 2012-07-13
	 */
	public function currentRowPosition()
	{
		return $this->_position;
	}
	
	/**
	 * 取得當前row, 並將指標推下一筆
	 *
	 * @return Model_DbTable_HandlerSocket_Row || false
	 * @author eddie
	 * @version 0.06 2012-07-13
	 */
	private function _getRow($rowIndex)
	{
	    /* 若為 assoc mode 由 key array取值 */
	    if ($this->_assocMode) {
	    	$position = $this->_rowKeyMapping[$rowIndex];
	    } else {
	        $position = $rowIndex;
	    }
	    if (!isset($this->_data[$position])) {
			return false;
		}
		$data = $this->_data[$position];
		return new Model_DbTable_HandlerSocket_Row($this->_columnsMapping, $data);
	}
	
	/**
	 * 由 row key 取得array
	 *
	 * @return array
	 * @author eddie
	 * @version 0.06 2012-07-13
	 */
	public function getRow($position)
	{
	    if (!isset($this->_data[$position])) {
	        return new Model_DbTable_HandlerSocket_Row(array(), array());
	    } 
	    return new Model_DbTable_HandlerSocket_Row($this->_columnsMapping, $this->_data[$position]);
	}
	
	/**
	 * Rewind 返回position至第一個Row
	 * Required by interface Iterator.
	 *
	 * @author eddie
	 * @return self
	 * @version 0.06 2012-07-13
	 */
	public function rewind()
	{
		$this->_position = 0;
		return $this;
	}
	
	/**
	 * 返回集合RowSet裡的當前元素Row
	 * Required by interface Iterator.
	 *
	 * @author eddie
	 * @return Model_DbTable_HandlerSocket_Row 集合RowSet裡的當前元素Row
	 * @version 0.06 2012-07-13
	 */
	public function current()
	{
		if ($this->valid() === false) {
			return null;
		}
		// return the row object
		return $this->_getRow($this->_position);
	}
	
	/**
	 * 返回當前 row 的 rowset key
	 * Required by interface Iterator.
	 *
	 * @author eddie
	 * @return int
	 * @version 0.06 2012-07-13
	 */
	public function key()
	{
		return $this->_position;
	}
	
	/**
	 * 移至下一個row
	 * Required by interface Iterator.
	 * 
	 * @author eddie
	 * @return void
	 * @version 0.06 2012-07-13
	 */
	public function next()
	{
		++$this->_position;
	}
	
	/**
	 * 檢查是否超超出 rowset data index
	 * Required by interface Iterator.
	 * 
	 * @author eddie
	 * @return bool 如果超出回傳False
	 * @version 0.06 2012-07-13 
	 */
	public function valid()
	{
		return $this->_position >= 0 && $this->_position < $this->_count;
	}
	
	/**
	 * 取代直接取用 $_position
	 *
	 * @param int $position Row array的index
	 * @author eddie
	 * @return self
	 * @version 0.06 2012-07-13
	 */
	public function seek($position)
	{
		$position = (int) $position;
		if ($position < 0 || $position >= $this->_count) {
			throw new Exception("Illegal index {$position}");
		}
		$this->_position = $position;
		return $this;
	}
	
	/**
	 * 此RowSet的資料筆數
	 *
	 * @author eddie
	 * @return int 筆數
	 * @version 0.06 2012-07-13
	 */
	public function count()
	{
		return $this->_count;
	}
	
	/**
	 * 排序 assoc mode query出的資料
	 *
	 * @author eddie
	 * @return void
	 * @version 0.06 2012-07-16
	 */
	public function _sortAssocArray($column, $by = self::SORT_ASC)
	{
		if (!isset($this->_columnsMapping[$column])) {
			// mapping不到key時
			throw new Exception("Not found key '{$column}'");
		}
		
		// 取得 $_data與column mapping後的position
		$columnPosition = $this->_columnsMapping[$column];
		// 使用 user-defind function 做sort
		if ($by === self::SORT_ASC) {
			uasort($this->_data, function ($a, $b) use ($columnPosition){ 
				if ($a[$columnPosition] === $b[$columnPosition]) { return 0; }
				return $a[$columnPosition] > $b[$columnPosition] ? 1 : -1;
			});
		} else {
			uasort($this->_data, function ($a, $b) use ($columnPosition){
				if ($a[$columnPosition] === $b[$columnPosition]) { return 0; }
				return $a[$columnPosition] < $b[$columnPosition] ? 1 : -1;
			});
		}
		$this->_rowKeyMapping = array_keys($this->_data);
	}
	
	/**
	 * 排序非 assoc mode query出的資料
	 *
	 * @author eddie
	 * @return void
	 * @version 0.06 2012-07-25
	 */
	public function _sortNormalArray($column, $by = self::SORT_ASC)
	{
		if (!isset($this->_columnsMapping[$column])) {
			// mapping不到key時
			throw new Exception("Not found key '{$column}'");
		}
	
		// 取得 $_data與column mapping後的position
		$columnPosition = $this->_columnsMapping[$column];
		// 使用 user-defind function 做sort
		if ($by === self::SORT_ASC) {
			usort($this->_data, function ($a, $b) use ($columnPosition){
				if ($a[$columnPosition] === $b[$columnPosition]) { return 0; }
				return $a[$columnPosition] > $b[$columnPosition] ? 1 : -1;
			});
		} else {
			usort($this->_data, function ($a, $b) use ($columnPosition){
				if ($a[$columnPosition] === $b[$columnPosition]) { return 0; }
				return $a[$columnPosition] < $b[$columnPosition] ? 1 : -1;
			});
		}
		return $this;
	}
	
	
	/**
	 * 排序$_data資料
	 *
	 * @author eddie
	 * @return self
	 * @version 0.06 2012-07-25
	 */
	public function sort($column, $by = self::SORT_ASC)
	{
	    if ($this->_assocMode) {
	        $this->_sortAssocArray($column, $by);
	    } else {
	        $this->_sortNormalArray($column, $by);
	    }
	    return $this;
	}
	
	/**
	 * 取出陣列的key list
	 *
	 * @return array
	 * @author eddie
	 * @version 0.06 2012-07-26
	 */
	public function getKeys()
	{
		if ($this->_assocMode) {
			return $this->_rowKeyMapping;
		}
		if (count($this->_keylist) > 0) {
			return $this->_keylist;
		}
		$this->_keylist = array_keys($this->_data);
		return $this->_keylist;
	}
}
