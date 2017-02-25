<?php

/**
 * TurboDepot is a cross language ORM library that allows saving, retrieving, listing, filtering and more with complex class data instances
*
* Website : -> http://www.turbodepot.org
* License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
* License Url : -> http://www.apache.org/licenses/LICENSE-2.0
* CopyRight : -> Copyright 2017 Edertone Advanded Solutions (Barcelona). http://www.edertone.com
*/

namespace org\turbodepot\src\main\php\managers;

use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * Abstraction layer that is used to connect and operate with multiple database engines.
 * Each instance of this class is independant and works with a single database connection, so multiple instances
 * can be used to operate with different databases or connections at the same time.
 *
 * Common operations that can be performed by this class include: Connect to a database engine, execute queries,
 * count elements, list table columns and lot more.
 */
class DataBaseManager extends BaseStrictClass {


	/**
	 * Defines the mysql database engine name
	 */
	const MYSQL = 'mysql';


	/**
	 * If any of the queries executed by this class exceeds the number of seconds specified here, a warning
	 * will be thrown. Setting this value to 0 will disable this feature.
	 */
	public $warnForSlowQueries = 0;


	/**
	 * The class will throw a PHP warning if the total accumulated query execution time exceeds the specified seconds (Set it to 0 to disable any warning).
	 */
	public $warnForSlowQueriesTotal = 0;


	/**
	 * Stores the total accumulated time for all the executed queries since first connection
	 */
	private $_accumulatedQueryTime = 0;


	/**
	 * Tells if the last executed query was successful or not
	 */
	private $_lastQuerySucceeded = false;


	/**
	 * @see $this->getQueryHistory()
	 */
	private $_queryHistory = [];


	/**
	 * Contains the last error that happened on the database (if any).
	 * Note: Any error that is raised by a db operation or query that is performed outside this class will not
	 * appear here.
	 */
	private $_lastError = '';


	/** Stores the database engine to which this class is currently being connected */
	private $_engine = '';


	/** Variable that stores the mysql database connection id so it can be used for all the operations */
	private $_mysqlConnectionId = null;


	/** Array that is used to chain the different transaction operations so multiple calls are taken into account */
	private $_transactionQueue = [];


	/**
	 * Initialize a mysql database connection with the specified parameters
	 *
	 * @param string $host Path to the mysql server (possible values: an ip, a hostname, 'localhost', etc ...)
	 * @param string $userName The database user we will use for the connection
	 * @param string $psw The database user password
	 * @param string $dataBaseName The name for the database to which we want to connect. leave it empty if we are connecting only to the mysql host.
	 *
	 * @return boolean True on success or false if connection was not possible
	 */
	public function connectMysql($host, $userName, $psw, $dataBaseName = null){

		$id = mysqli_connect($host, $userName, $psw, $dataBaseName);

		if(mysqli_connect_errno()){

			throw new Exception('Could not connect to MYSQL'.mysqli_connect_error(), E_USER_ERROR);
		}

		// Force MYSQL and PHP to speak each other in unicode  UTF8 format.
		if(!mysqli_query($id, "SET NAMES 'utf8'")){

			throw new Exception('Could not set connection encoding', E_USER_ERROR);
		}

		$this->_engine = self::MYSQL;
		$this->_mysqlConnectionId = $id;

		return true;
	}


	/**
	 * TODO
	 */
	public function connectPostgreSql($todo){

		// TODO
	}


	/**
	 * Tells if the database manager is correctly connected to a dababase engine at this moment.
	 *
	 * @return boolean True if there is an active database connection, false if not.
	 */
	public function isConnected(){

		return $this->_mysqlConnectionId !== null;
	}


	/**
	 * Creates a new empty database with the specified name
	 *
	 * @param string $dataBaseName the name of the database to create
	 *
	 * @return boolean True if the database could be created, false otherwise
	 */
	public function createDataBase($dataBaseName){

		if($this->_engine == self::MYSQL){

			return $this->query('CREATE DATABASE '.$dataBaseName);
		}

		return false;
	}


	/**
	 * Drops the specified database. Use with caution.
	 *
	 * @param string $dataBaseName the name of the database
	 *
	 * @return boolean True if the database was successfully deleted, false if deletion failed.
	 */
	public function deleteDataBase($dataBaseName){

		if($this->_engine == self::MYSQL){

			return $this->query('DROP DATABASE '.$dataBaseName);
		}

		return false;
	}


	/**
	 * Detect if the specified database exists on the current connection
	 *
	 * @param string $dataBaseName the name of the database
	 *
	 * @return boolean True if the specified database exists, false otherwise
	 */
	public function dataBaseExists($dataBaseName) {

		if($this->_engine == self::MYSQL){

			return (mysqli_num_rows(mysqli_query($this->_mysqlConnectionId, "SHOW DATABASES LIKE '".$dataBaseName."'")) == 1);
		}
	}


    /**
     * Execute the specified query against the current database connection.<br><br>
     * If the query generates an error, the full description will be available by calling $this->getLastError().<br>
     * Also, a detailed history of all the executed queries can be obtained by calling $this->getQueryHistory()
     *
     * @param string $query The SQL query to execute
     *
     * @see $this->getLastError
     * @see $this->getQueryHistory
     *
     * @return boolean|array <br>
     * - An associative array with the query data for queries that generate values (like a SELECT).<br>
     * - True for successful queries that do not generate vaules.<br>
     * - False for any query that generates an error.
     */
    public function query($query){

    	$result = false;

    	$queryStart = microtime(true);

    	if($this->_engine == self::MYSQL){

    		$mysqlResult = mysqli_query($this->_mysqlConnectionId, $query);

    		if($mysqlResult === false){

    			$errorMessage = mysqli_error($this->_mysqlConnectionId);

    			$this->_lastError = StringUtils::isEmpty($errorMessage) ? 'unknown sql error' : $errorMessage;

    		}else{

    			if($mysqlResult === true){

    				$result = true;

    			}else{

    				$result = [];

    				while($line = mysqli_fetch_assoc($mysqlResult)){

    					array_push($result, $line);
    				}
    			}
    		}
    	}

    	// Save the query information to history
    	$this->_addQueryToHistory($query, $queryStart, $result);

    	// Store if the last query was successful or not
    	$this->_lastQuerySucceeded = ($result !== false);

    	return $result;
    }


	/**
	 * Gets the auto increment id value that's been generated by the last executed insert query
	 *
	 * @return The id value
	 */
	public function getLastInsertId(){

		if($this->_engine == self::MYSQL){

			return mysqli_insert_id($this->_mysqlConnectionId);
		}
	}


	/**
	 * Contains the result of the last executed query.
	 *
	 * @return boolean True if the last executed query succeeded without error, false if any error happened
	 */
	public function getLastQuerySucceeded(){

		return $this->_lastQuerySucceeded;
	}


	/**
	 * Contains the last error that happened on the database (if any).
	 *
	 * Note: Only database errors that are caused by operations made with this class are tracked here.
	 *
	 * @return string The last error that happened (if any)
	 */
	public function getLastError(){

		return $this->_lastError;
	}


	/**
	 * Outputs an array containing all the queries that have been executed for the current connection.
	 * Each array entry contains another associative array with the following values:<br><br>
	 * - query: the SQL query<br>
	 * - queryDate: The date and time when the query was executed<br>
	 * - queryError: Error message for the query in case it failed execution.<br>
	 * - querySeconds: The time it took to execute it in seconds (with ms decimal precision)<br>
	 * - querySecondsAccumulated: The total time spent on db queries plus the time of this query<br>
	 * - queryCount: The number of times the same exact query has been executed since the first connection<br>
	 *
	 * @return array The query history information
	 */
	public function getQueryHistory(){

		return $this->_queryHistory;
	}


	/**
	 * Gets all the values for a given table column, sorted as they are on the table rows.
	 * This should be used when we are listing values from a table column that does not have many lines,
	 * otherwise we may overflow the result and get performance problems
	 *
	 * @param string $tableName Table that contains the requested column
	 * @param string $columnName Name for the table column we want to list
	 *
	 * @return boolean|array <br>
     * - A list with all the values that can be found on the specified table and column. Note that list is unique, repeated values are removed.<br>
     * - False if an error happened
	 */
	public function getTableColumnValues($tableName, $columnName){

		$mysqlResult = $this->query('SELECT DISTINCT '.$columnName.' FROM '.$tableName);

		if(!$mysqlResult){

			return false;
		}

		$result = [];

		foreach ($mysqlResult as $value) {

			array_push($result, $value[$columnName]);
		}

		return $result;
	}


	/**
	 * Get a list with all the column names from the specified table with the same order as they appear on it.
	 *
	 * @param string $tableName the table name
	 *
	 * @return boolean|array The list of all column names on the requested table, or false if an error happens
	 */
	public function getTableColumnNames($tableName){

		$mysqlResult = $this->query('SHOW COLUMNS FROM '.$tableName);

		if(!$mysqlResult){

			return false;
		}

		$result = [];

		foreach ($mysqlResult as $row) {

			array_push($result, $row['Field']);
		}

		return $result;
	}


	/**
	 * Gets the maximum value for a specified column on the specified table.
	 *
	 * @param string $tableName the table name
	 * @param string $columnName Name for the column we want to get the maximum value
	 *
	 * @return float The maximum value that was found on the given table and colum
	 */
	public function getTableColumnMaxValue($tableName, $columnName){

		$mysqlResult = $this->query('SELECT CAST('.$columnName.' AS SIGNED) AS '.$columnName.' FROM '.$tableName.' ORDER BY '.$columnName.' DESC LIMIT 1');

		if(!$mysqlResult){

			return false;
		}

		return $mysqlResult[0][$columnName];
	}


	/**
	 * Detect if the specified table exists on database
	 *
	 * @param string $tablename the name of the table
	 *
	 * @return boolean True if the specified table exists, false otherwise
	 */
	public function tableExists($tablename) {

		if($this->_engine == self::MYSQL){

			return (mysqli_num_rows(mysqli_query($this->_mysqlConnectionId, "SHOW TABLES LIKE '".$tablename."'")) == 1);
		}
	}


	/**
	 * Get the total amount of rows by applying the COUNT method on the specified table primary key.
	 *
	 * @param int $primaryKey The query field that contains the table primary key, that will be used to count the items
	 * @param string $from    The sql FORM part that will be used on the query
	 * @param string $where	  The sql WHERE part that will be used on the query (optional)
	 *
	 * @return integer The total number of items
	 */
	public function countByPrimaryKey($primaryKey, $from, $where = ''){

		$line = $this->getNextLine($this->query('SELECT COUNT('.$primaryKey.') as items '.$from.' '.$where));

		if($line == null){

			return 0;

		}else{

			return $line['items'];
		}
	}


	/**
	 * Count the total number of rows that a SQL query will generate
	 *
	 * @param string $query the SQL Query that we want to count
	 *
	 * @return integer The total count of rows that the query will generate
	 */
	public function countByQuery($query){

		$result = $this->query($query);

		if(!$result){

			$this->_lastError = mysqli_error($this->_mysqlConnectionId);
			return false;
		}

		return mysqli_num_rows($result);
	}


	/**
	 * Start a database transaction
	 * TODO explicar com funciona el mecanisme de encadenament de transaccions i dir que es independent del motor de bd
	 * @return void
	 */
	public function transactionBegin(){

		if(count($this->_transactionQueue) <= 0){

			$this->query('START TRANSACTION');
		}

		// We must chain each transaction begin call so only the first time is executed
		array_push($this->_transactionQueue, '1');

	}


	/**
	 * Rollback a database transaction
	 *
	 * @return void
	 */
	public function transactionRollback(){

		// reset the transaction queue
		$this->_transactionQueue = [];

		$this->query('ROLLBACK');

	}


	/**
	 * Commit a database transaction
	 *
	 * @return void
	 */
	public function transactionCommit(){

		// We remove an element from the transaction queue and execute only if array is empty.
		array_shift($this->_transactionQueue);

		if(count($this->_transactionQueue) <= 0){

			$this->query('COMMIT');
		}

	}


	/**
	 * Close the current database conection
	 *
	 * @return boolean True if the disconnect was successful, false otherwise.
	 */
	public function disconnect() {

		// Check if we are currently connected to a mysql engine
		if($this->_engine == self::MYSQL && mysqli_close($this->_mysqlConnectionId)){

			$this->_engine = '';
			$this->_mysqlConnectionId = null;
			$this->_queryHistory = [];
			$this->_lastError = '';
			$this->_accumulatedQueryTime = 0;
			$this->_lastQuerySucceeded = false;
			$this->_transactionQueue = [];

			return $this->_mysqlConnectionId == null;
		}

		return false;
	}


	/**
	 * Store a new entry to the query history array
	 *
	 * @param string $query The SQL query to store
	 * @param string $queryStart The microtime when the query started. This method will calculate the total time
	 * @param boolean $queryResult True if the query execution succeeded or false if there was any error
	 *
	 * @return void
	 */
	private function _addQueryToHistory($query, $queryStart, $queryResult){

		// Some queries are not useful
		if($query == 'START TRANSACTION' || $query == 'ROLLBACK' || $query == 'COMMIT'){

			return;
		}

		$queryEnd = microtime(true);
		$querySeconds = round($queryEnd - $queryStart, 4);

		// Calculate the accumulated time for all the queries since start to now
		$this->_accumulatedQueryTime += $querySeconds;

		// generate the query history structure
		$queryHistory = [];
		$queryHistory['query'] = $query;
		$queryHistory['queryError'] = ($queryResult === false ? $this->_lastError : '');
		$queryHistory['queryDate'] = date('Y/m/d H:i:s', $queryStart);
		$queryHistory['querySeconds'] = $querySeconds;
		$queryHistory['querySecondsAccumulated'] = $this->_accumulatedQueryTime;
		$queryHistory['queryCount'] = 1;

		// Check if we must generate a warning due to slow query times
		if($this->warnForSlowQueries > 0 && $querySeconds > $this->warnForSlowQueries){

			trigger_error('Warning: The following SQL query required more than '.$this->warnForSlowQueries.' seconds to execute: '.$query, E_USER_WARNING);
		}

		// Check if we must generate a warning due to slow query total times
		if($this->warnForSlowQueriesTotal > 0 && $this->_accumulatedQueryTime > $this->warnForSlowQueriesTotal){

			trigger_error('Warning: The SQL queries total execution time has exceeded '.$this->warnForSlowQueriesTotal.' seconds', E_USER_WARNING);
		}

		// Reaching here means this is the first time the query is executed, so we will store it
		array_push($this->_queryHistory, $queryHistory);
	}
}

?>
