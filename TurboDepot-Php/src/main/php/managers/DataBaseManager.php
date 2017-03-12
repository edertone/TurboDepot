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
	 * @see DataBaseManager::getQueryHistory
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


	/**
	 * Stores the name for the database that is currently being selected on the active engine connection.
	 * Empty value means no database is selected yet.
	 */
	private $_selectedDatabase = '';


	/** Variable that stores the mysql database connection id so it can be used for all the operations */
	private $_mysqlConnectionId = null;


	/**
	 * Initialize a mysql database connection with the specified parameters
	 *
	 * @param string $host Path to the mysql server (possible values: an ip, a hostname, 'localhost', etc ...)
	 * @param string $userName The database user we will use for the connection
	 * @param string $password The database user password
	 * @param string $dataBaseName The name for the database to which we want to connect. leave it empty if we are connecting only to the mysql host.
	 *
	 * @return boolean True on success or false if connection was not possible
	 */
	public function connectMysql($host, $userName, $password, $dataBaseName = null){

		$id = mysqli_connect($host, $userName, $password, $dataBaseName);

		if(mysqli_connect_errno()){

			throw new Exception('Could not connect to MYSQL'.mysqli_connect_error());
		}

		// Force MYSQL and PHP to speak each other in unicode  UTF8 format.
		if(!mysqli_query($id, "SET NAMES 'utf8'")){

			throw new Exception('Could not set connection encoding');
		}

		$this->_engine = self::MYSQL;
		$this->_mysqlConnectionId = $id;
		$this->_selectedDatabase = StringUtils::isEmpty((string)$dataBaseName) ? '' : $dataBaseName;

		return true;
	}


	/**
	 * Initialize a postgresql database connection with the specified parameters
	 * TODO
	 *
	 * @return boolean True on success or false if connection was not possible
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
	 * Tells if the last executed query was succesful or not
	 *
	 * @return boolean True if the last executed query succeeded without error, false if any error happened
	 */
	public function isLastQuerySucceeded(){

		return $this->_lastQuerySucceeded;
	}


	/**
	 * Creates a new empty database with the specified name
	 *
	 * @param string $dataBaseName the name of the database to create
	 *
	 * @return boolean True if the database could be created, false otherwise
	 */
	public function dataBaseCreate($dataBaseName){

		if($this->_engine == self::MYSQL){

			return $this->query('CREATE DATABASE '.$dataBaseName);
		}

		return false;
	}


	/**
	 * Set the specified database as the active one.
	 * DataBaseManager must be connected to a db host for this method to work.
	 *
	 * @param string $dataBaseName The name for the database that we want to set as active.
	 *
	 * @return boolean True if it was possible to select the specified database, false if not.
	 */
	public function dataBaseSelect($dataBaseName){

		// An active engine connection must be available to select a database
		if(!$this->isConnected()){

			throw new Exception('DataBaseManager->dataBaseSelect : Not connected to a database host.');
		}

		if($this->_engine == self::MYSQL){

			if(mysqli_select_db($this->_mysqlConnectionId, $dataBaseName)){

				$this->_selectedDatabase = $dataBaseName;

			}else{

				return false;
			}
		}

		return true;
	}


	/**
	 * Drops the specified database. Use with caution.
	 *
	 * @param string $dataBaseName the name of the database
	 *
	 * @return boolean True if the database was successfully deleted, false if deletion failed.
	 */
	public function dataBaseDelete($dataBaseName){

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
     * @see DataBaseManager::getLastError
     * @see DataBaseManager::getQueryHistory
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
	 * Get the name for the database that is currently selected
	 *
	 * @return boolean|string The name for the currently selected database, '' if no database is selected, false if an error happened.
	 */
	public function getSelectedDataBase(){

		if($this->_engine == self::MYSQL){

			$mysqlResult = $this->query('SELECT DATABASE() as d');

			if(!$mysqlResult){

				return false;
			}

			return (string)$mysqlResult[0]['d'];
		}

		return false;
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

		if($this->_engine == self::MYSQL){

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

		return false;
	}


	/**
	 * Get a list with all the column names from the specified table with the same order as they appear on it.
	 *
	 * @param string $tableName the table name
	 *
	 * @return boolean|array The list of all column names on the requested table, or false if an error happens
	 */
	public function getTableColumnNames($tableName){

		if($this->_engine == self::MYSQL){

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

		return false;
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

		if($this->_engine == self::MYSQL){

			$mysqlResult = $this->query('SELECT CAST('.$columnName.' AS SIGNED) AS '.$columnName.' FROM '.$tableName.' ORDER BY '.$columnName.' DESC LIMIT 1');

			if(!$mysqlResult){

				return false;
			}

			return $mysqlResult[0][$columnName];
		}

		return false;
	}


	/**
	 * Get the total number of rows for a given table
	 *
	 * @param string $tableName The name for the table we want to count
	 *
	 * @return int The total number of table rows or false if an error happened
	 */
	public function getTableRowCount($tableName){

		$mysqlResult = $this->query('select count(1) as c FROM '.$tableName);

		if(!$mysqlResult){

			return false;
		}

		return (int)$mysqlResult[0]['c'];
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
	 * Start a database transaction.
	 *
	 * @return boolean True if the transaction started correctly, false otherwise
	 */
	public function transactionBegin(){

		if($this->_engine == self::MYSQL){

			return $this->query('START TRANSACTION');
		}

		return false;
	}


	/**
	 * Rollback a database transaction
	 *
	 * @return boolean True if the transaction rolled correctly, false otherwise
	 */
	public function transactionRollback(){

		if($this->_engine == self::MYSQL){

			return $this->query('ROLLBACK');
		}

		return false;
	}


	/**
	 * Commit a database transaction
	 *
	 * @return boolean True if the transaction commited correctly, false otherwise
	 */
	public function transactionCommit(){

		if($this->_engine == self::MYSQL){

			return $this->query('COMMIT');
		}

		return false;
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
			$this->_selectedDatabase = '';
		}

		return $this->_mysqlConnectionId == null;
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
