HandlerSocket For ZendFramework DbTable
========================
This library is a sub model in `Zend_Db_Table`

Installation
-----------
The first thing, you have to make sure your handlersocket php extension and mysql plugin are setup already, then the following step to setup this library in your zendframework project!

Edit the configuration `application.ini` then add 

`resources.db.params.handlersocketPassword = password` 

find your ZF project model path 

`/path/to/project/application/models/`

make the directory structure to 

`/path/to/project/application/models/DbTable/HandlerSocket/`

put the files `Abstract.php` `Adapter` `Row.php` `RowSet.php` `Select.php` in HandlerSocket folder

Setup Successful!

How to use 
----------
Create a DbTable model

    class Model_DbTable_TableName extends Model_DbTable_HandlerSocket_Abstract {
        // defined your index, you can also use $this->getIndexes() the dump your table indexes
        const INDEX_KEY1 = 'indexName';

        // defined the table name
        protected $_name = 'tableName';
        
        public function getSomeData($keyValue)
        {
            // will shows columns
                $columns = array('column1', 'column2');
            // will search columns except index key
                $filters = array('column3', 'column4')
                $opneIndex = $this->getIndexData(self::INDEX_SUPORT, $columns, $filters);

                $query = $this->handlerSelect($opneIndex)->eq((int)$supplierID)->limit(100);
                return $this->handlerFetchAll($query);
        }
    }

In your controller

    $result = Model_DbTable_TableName::getInstance()->getSomeData(1);

    foreach ($result as $row) {
        echo $row->column1;
        echo $row->column2;
    }

Fetch mode
----------
    <?php $this->handlerFetchAll($select); ?>
it will return the row set object Model_DbTable_HandlerSocket_RowSet()

    $this->handlerFetchRow
it will return the row object (Model_DbTable_HandlerSocket_Row)

Model_DbTable_HandlerSocket_Abstract 
---------
It will query current table indexes to your database, it just for developers to easy knows the table indexes.

    $indexes = $this->getIndexes();

It use singleton pattern. you can create the method more faster and easier

    Model_DbTable_TableName::getInstance()

Create a new select object 
    
    $select = $this->handlerSelect($indexData);

Model_DbTable_HandlerSocket_Select
----------
Create the select object
    
    $select = $this->handlerSelect($indexData);
    
Setup the value you will search

    $select->eq($value);
    $select->gt($value);
    $select->gte($value);
    $select->lt($value);
    $select->lte($value);

Setup the `IN` search, like `WHERE column IN (1, 2, 3, 4)`, `eq`, `gt`, `gte`, `lt`, `lte` will fail when you setup `in` function.

    $values = array(1, 2, 3, 4);
    $select->in($values);

Setup the limit, by default is `LIMIT 1`, if you want to get more records, you have to set it.
    
    // WHERE KEY = $value LIMIT 10, 2
    $select->eq($value)->limit(10, 2)
    // WHERE KEY = $value LIMIT 10
    $select->eq($value)->limit(10);

Setup the filters (you have to setup the columns which will search when you create index via `Model_DbTable_HandlerSocket_Abstract::getIndexData`)

    // WHERE YOU_CHOICE_KEY = $value AND columnName = $value2 AND column2 > $value3
    $select->eq($value)->andEq('columnName', $value2)->andGt('column2', $value3)

Model_DbTable_HandlerSocket_Row 
---------
Use php dump the row data

    $result->dump();

Get the row column data

    echo $result->columnName;

Model_DbTable_HandlerSocket_RowSet 
-------
Return the array of the all rows data

    $result = $this->handlerFetchAll($select);
    $array = $result->toArray();

Return the rows count 

    $result = $this->handlerFetchAll($select);
    $count = $result->count();


