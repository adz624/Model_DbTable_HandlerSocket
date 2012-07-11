<?php
abstract class Model_DbTable_HandlerSocket_Abstract extends Zend_Db_Table_Abstract
{
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
	 * @uses eddie
	 * @version 0.05 2012-07-11
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
	 * @uses eddie
	 * @version 0.05 2012-07-11
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
	 * @uses eddie
	 * @version 0.05 2012-07-11
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
	 * @return Model_DbTable_HandlerSocket_Adapter
	 * @author eddie
	 * @uses eddie
	 * @version 0.05 2012-07-11
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
	 * @return HandlerSocket原生Conenction
	 * @author eddie
	 * @uses eddie
	 * @version 0.05 2012-07-11
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
	 * @uses eddie
	 * @version 0.05 2012-07-11
	 */
	public static function getInstance()
	{
		$modelName = get_called_class();
		if (!isset(self::$_instance[$modelName])) {
			self::$_instance[get_called_class()] = new $modelName();
		}
		return self::$_instance[$modelName];
	}
	
	
	public function getIndexData ($indexName, $columns, $filters)
	{
		$indexKeyName = "INDEX_{$this->_name}_{$indexName}";
		if (!isset(self::$_opendIndexes[$indexKeyName])) {
			$this->_getHandlerAdapter()->prepare();
			$indexId = count(self::$_opendIndexes) + 1;
			if (!$this->_getConnection()->openIndex($indexId, $this->dbName, $this->_name, $indexName, implode(',', $columns), $filters)) {
				throw new Exception( $this->_getConnection()->getError());
			}
			self::$_opendIndexes[$indexKeyName] = array('indexId' => $indexId, 'filters' => $filters);
		}	
		
		return self::$_opendIndexes[$indexKeyName];
	}
	
	
	
	public function handlerSelect($indexId)
	{
		return new Model_DbTable_HandlerSocket_SelectParser($indexId);
	}
	
	public function handlerFetch($select)
	{
		$query = $select->getSelect();
		
		if (count($query['filters']) === 0) {
			$filters = null;
		} else {
			$filters = $query['filters'];
		}
		if (count($query['inValues']) === 0) {
			return $this->_getConnection()->executeSingle(
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
			return $this->_getConnection()->executeSingle(
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
		
		
	}
	
	
	/**
	 *  $query = $this->handlerSelect()->index(MODEL:INDEX_PRIMARY)->values(10)->
	 *  $this->handlerFetch($query);
	 * 
	 */
}