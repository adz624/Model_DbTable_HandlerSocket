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

put the files `Abstract.php` `Adapter.php` `Row.php` `RowSet.php` `Select.php` in HandlerSocket folder

Successful!

When can I use Handlersocket instead of libmysql?
----------
1. Primary key, Unique key Search
2. A list with pagination, but without sort
3. A list with sort, but without pagination

Handlersocket just can fetch data, it cannot do the high level things, like count(), max(), and sort with columns, it means you also cannot use handlersocket to do a list with pagination and sort column at same time.

How to use 
----------
Create a DbTable model
    
    <?php 
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
                    $opneIndex = $this->getIndexData(self::INDEX_KEY1, $columns, $filters);

                    $query = $this->handlerSelect($opneIndex)->eq((int)$supplierID)->limit(100);
                    return $this->handlerFetchAll($query);
            }
        }
    ?>
In your controller

    <?php 
        $result = Model_DbTable_TableName::getInstance()->getSomeData(1);

        foreach ($result as $row) {
            echo $row->column1;
            echo $row->column2;
        }
    ?>
Fetch mode
----------
    <?php $this->handlerFetchAll($select); ?>
it will return the row set object (Model_DbTable_HandlerSocket_RowSet)

    <?php $this->handlerFetchRow($select); ?>
it will return the row object (Model_DbTable_HandlerSocket_Row)

    <?php $this->handlerFetchAssoc($select); ?>
it will use the first column which you do select to be the key of result (Model_DbTable_HandlerSocket_RowSet)

Model_DbTable_HandlerSocket_Abstract 
---------
It will query current table indexes to your database, it just for developers to easy knows the table indexes.

    <?php $indexes = $this->getIndexes(); ?>

It use singleton pattern. you can create the method more faster and easier

    <?php Model_DbTable_TableName::getInstance(); ?>

Create a new select object 
    
    <?php $select = $this->handlerSelect($indexData); ?>

Model_DbTable_HandlerSocket_Select
----------
Create the select object
    
    <?php $select = $this->handlerSelect($indexData); ?>
    
Setup the value you will search

    <?php 
        $select->eq($value);
        $select->gt($value);
        $select->gte($value);
        $select->lt($value);
        $select->lte($value);
    ?>
Setup the `IN` search, like `WHERE column IN (1, 2, 3, 4)`, `eq`, `gt`, `gte`, `lt`, `lte` will fail when you setup `in` function.

    <?php
        $values = array(1, 2, 3, 4);
        $select->in($values);
    ?>
Setup the limit, by default is `LIMIT 1`, if you want to get more records, you have to set it.
    
    <?php
        // WHERE KEY = $value LIMIT 10, 2
        $select->eq($value)->limit(10, 2)
        // WHERE KEY = $value LIMIT 10
        $select->eq($value)->limit(10);
    ?>

Setup the filters (you have to setup the columns which will search when you create index via `Model_DbTable_HandlerSocket_Abstract::getIndexData`)

    <?php
        // WHERE YOU_CHOICE_KEY = $value AND columnName = $value2 AND column2 > $value3
        $select->eq($value)->andEq('columnName', $value2)->andGt('column2', $value3)
    ?>

Model_DbTable_HandlerSocket_Row 
---------
Use php dump the row data

    <?php $result->dump(); ?>

Get the row column data

    <?php echo $result->columnName; ?>

Model_DbTable_HandlerSocket_RowSet 
-------
Return the array of the all rows data

    <?php 
        $result = $this->handlerFetchAll($select); 
        $array = $result->toArray();
    ?>

Return the rows count 

    <?php
        $result = $this->handlerFetchAll($select);
        $count = $result->count();
    ?>

Sort data

    <?php
        $this->handlerFetchAll($query)->sort('CategoryID', Model_DbTable_HandlerSocket_RowSet::SORT_ASC);
    ?>

Contact
----------

Eddie Lee   
Mail: eddie@visionbundles.com   
Facebook: http://www.facebook.com/latebird.ticket  

