<?php
class Model_DbTable_HandlerSocket_Adapter 
{
	
	const PORT = 9998;
	 	
	static $dbName = null;
	static $connection = null;
	static $authStatus = false;
	
	static $config = array();
	
	
	public function __construct($config)
	{
		self::$config = $config;
	}
	
	/**
	 * HandlerSocket Auth
	 *
	 * @return void
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	private function _auth()
	{
		self::$authStatus = self::$connection->auth(self::$config['handlersocketPassword']);
		if (!self::$authStatus) {
			throw new Exception('Auth Faild');
		}
		return self::$authStatus;
	}
		
	/**
	 * getDatabase
	 *
	 * @return HandlerSocket Connection Object
	 * @author eddie
	 * @version 0.06 2012-07-11
	 */
	public function getConnect()
	{
		if (self::$connection === null) {
			self::$connection = new HandlerSocket(self::$config['host'], self::PORT);
		}
		return self::$connection;
	}
	

	
	public function prepare()
	{
		if (self::$config) {
			$this->getConnect();
			$this->_auth();
		}
	}
}