<?php
abstract class Model_DbTable_HandlerSocket_Abstract extends Zend_Db_Table_Abstract
{
	const FETCH_ALL = 0;
	const FETCH_ROW = 1;
	const FETCH_ASSOC = 2;
	
	const INDEX_PRIMARY = 'PRIMARY';
	
	private $dbName = '';
	
	static $_adapter = null;
	static $_tableIndexes = array();
	static $_opendIndexes = array();
	static $_instance = array();
	
	/**
	 * 當被繼承時, 設定handlersocket adapter
	 *
	 * @author eddie
	 * @return void
	 * @version 0.06 2012-07-11
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_setHandlerAdapter();
	}
	
	/**
	 * 取得此表Index
	 *
	 * @return array
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	public function getIndexes()
	{
		if (!isset(self::$_tableIndexes[$this->_name])) { 
			$SQL = "SHOW INDEX FROM ".$this->_name;
			$result = $this->getAdapter()->fetchAll($SQL);
			$indexes = array();
			foreach ($result as $index) {
				$indexes[$index['Key_name']][] = $index['Column_name'];
			}
			self::$_tableIndexes[$this->_name] = $indexes;
		}
		return self::$_tableIndexes[$this->_name];
	}
	
	/**
	 * 設定 Handlersocket Adapter
	 *
	 * @author eddie
	 * @return void
	 * @version 0.06 2012-07-11
	 */
	private function _setHandlerAdapter()
	{
		$config = $this->_db->getConfig();
		$this->dbName = $config['dbname'];
		if (self::$_adapter === null) {
			self::$_adapter = new Model_DbTable_HandlerSocket_Adapter($config);
		}
	}
	
	/**
	 * 取得 Handlersocket Adapter
	 * 
	 * @return Model_DbTable_HandlerSocket_Adapter
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	private function _getHandlerAdapter()
	{
		if (self::$_adapter === null) {
			return false;
		}
		return self::$_adapter;
	}
	
	/**
	 * 取得 Handlersocket Adapter
	 * 
	 * @return HandlerSocket原生Conenction
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	private function _getConnection()
	{
		if (self::$_adapter === null) {
			throw new Exception("Handlersocket adapter Not Found");
		}
		return self::$_adapter->getConnect();
	}
	
	
	/**
	 * singleton pattern
	 *
	 * @return Model
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	public static function getInstance()
	{
		$modelName = get_called_class();
		if (!isset(self::$_instance[$modelName])) {
			self::$_instance[get_called_class()] = new $modelName();
		}
		return self::$_instance[$modelName];
	}
	
	/**
	 * 取得索引資料
	 *
	 * @return array ($indexId, $filterColumns)
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	public function getIndexData ($indexName, $columns, $filters)
	{
		$indexKeyName = "INDEX_{$this->_name}_{$indexName}_".implode('_', $columns);
		if (!isset(self::$_opendIndexes[$indexKeyName])) {
			$this->_getHandlerAdapter()->prepare();
			$indexId = count(self::$_opendIndexes) + 1;
			if (!$this->_getConnection()->openIndex($indexId, $this->dbName, $this->_name, $indexName, implode(',', $columns), $filters)) {
				throw new Exception( 'Get Index Error');
			}
			self::$_opendIndexes[$indexKeyName] = array('indexId' => $indexId, 'filters' => $filters, 'columns' => $columns);
		}	
		
		return self::$_opendIndexes[$indexKeyName];
	}
	
	
	/**
	 * 取得索引資料
	 *
	 * @param array $openIndex
	 * @return Model_DbTable_HandlerSocket_Select
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	public function handlerSelect($openIndex)
	{
		return new Model_DbTable_HandlerSocket_Select($openIndex);
	}
	
	/**
	 * 將資料送進Query
	 *
	 * @param Model_DbTable_HandlerSocket_Select 
	 * @param fetchMode
	 * @return Model_DbTable_HandlerSocket_RowSet || Model_DbTable_HandlerSocket_Row
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	private function _handlerFetch($select, $fetchMode)
	{
		$query = $select->getSelect();
		
		if (count($query['filters']) === 0) {
			$filters = null;
		} else {
			$filters = $query['filters'];
		}
		
		if (count($query['inValues']) === 0) {
			$data = $this->_getConnection()->executeSingle(
					$query['indexId'],
					$query['operate'],
					$query['values'],
					$query['limit'],
					$query['offset'],
					null,
					null,
					$filters
			);
		} else {
			$data = $this->_getConnection()->executeSingle(
					$query['indexId'],
					$query['operate'],
					$query['values'],
					$query['limit'],
					$query['offset'],
					null,
					null,
					$filters,
					0,
					$query['inValues']
			);
		}
		if (is_bool($data) || count($data) === 0) {
			return false;
		}
		$columnsMapping = $this->_getColumnMapping($select->getColumns());
		if ($fetchMode === self::FETCH_ASSOC) {
			$result = array();
			foreach ($data as $value) {
				$result[$value[0]] = $value;
			}
			$data = $result;
			return new Model_DbTable_HandlerSocket_RowSet($columnsMapping, $data, true);
		} else if ($fetchMode === self::FETCH_ALL) {
			return new Model_DbTable_HandlerSocket_RowSet($columnsMapping, $data);
		} else if ($fetchMode === self::FETCH_ROW) {
			
			return new Model_DbTable_HandlerSocket_Row($columnsMapping, $data[0]);
		} else {
			throw new Exception('Incorrent fetch mode');
		}
	}
	
	
	/**
	 * 將資料送進Query, 取得多筆
	 *
	 * @param Model_DbTable_HandlerSocket_Select
	 * @return Model_DbTable_HandlerSocket_RowSet
	 * @author eddie
	 * @version 0.06 2012-07-13
	 */
	public function handlerFetchAll($select)
	{
		return $this->_handlerFetch($select, self::FETCH_ALL);
	}
	
	/**
	 * 將資料送進Query, 取得多筆, 以第0個column當陣列元素
	 *
	 * @param Model_DbTable_HandlerSocket_Select
	 * @return Model_DbTable_HandlerSocket_RowSet
	 * @author eddie
	 * @version 0.06 2012-07-17
	 */
	public function handlerFetchAssoc($select)
	{
		return $this->_handlerFetch($select, self::FETCH_ASSOC);
	}
	
	/**
	 * 將資料送進Query, 取得單筆
	 *
	 * @param Model_DbTable_HandlerSocket_Select
	 * @return Model_DbTable_HandlerSocket_Row
	 * @author eddie
	 * @version 0.06 2012-07-13
	 */
	public function handlerFetchRow($select)
	{
		return $this->_handlerFetch($select, self::FETCH_ROW);
	}
	
	/**
	 * 回傳給RowSet使用的Columns mapping
	 *
	 * @param Model_DbTable_HandlerSocket_Select
	 * @return array
	 * @author eddie
	 * @version 0.06 2012-07-13
	 */
	private function _getColumnMapping($columns)
	{
		$columnsMapping = array();
		$i = 0;
		foreach ($columns as $column) {
			$columnsMapping[$column] = $i;
			$i++;
		}
		return $columnsMapping;
	}
}