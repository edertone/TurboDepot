<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\main\php\managers;

use UnexpectedValueException;
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
     * @see DataBaseManager::getLastError
     */
    private $_lastError = '';


    /**
     * Stores the database engine to which this class is currently being connected
     */
    private $_engine = '';


    /**
     * Stores the name for the database that is currently being selected on the active engine connection.
     * Empty value means no database is selected yet.
     */
    private $_selectedDatabase = '';


    /**
     * Stores the mysql database connection id so it can be used for all the operations
     */
    private $_mysqlConnectionId = null;


    /**
     * @see DataBaseManager::isAnyTransactionActive
     */
    private $_isAnyTransactionActive = false;


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

        if(StringUtils::isEmpty($host) || StringUtils::isEmpty($userName)){

            throw new UnexpectedValueException('host and userName must be non empty strings');
        }

        // If we are currently connected, an exception will happen
        if($this->isConnected()){

            throw new UnexpectedValueException('There\'s an active database connection. Disconnect before connecting');
        }

        $id = mysqli_connect($host, $userName, $password, $dataBaseName);

        if(mysqli_connect_errno()){

            throw new UnexpectedValueException('Could not connect to MYSQL '.mysqli_connect_error());
        }

        // Force MYSQL and PHP to speak each other in unicode  UTF8 format.
        if(!mysqli_query($id, "SET NAMES 'utf8'")){

            throw new UnexpectedValueException('Could not set connection encoding');
        }

        $this->_engine = self::MYSQL;
        $this->_mysqlConnectionId = $id;
        $this->_selectedDatabase = StringUtils::isEmpty((string)$dataBaseName) ? '' : $dataBaseName;

        return true;
    }


    /**
     * Initialize a mariadb database connection with the specified parameters
     *
     * @param string $host Path to the mariadb server (possible values: an ip, a hostname, 'localhost', etc ...)
     * @param string $userName The database user we will use for the connection
     * @param string $password The database user password
     * @param string $dataBaseName The name for the database to which we want to connect. leave it empty if we are connecting only to the mariadb host.
     *
     * @return boolean True on success or false if connection was not possible
     */
    public function connectMariaDb($host, $userName, $password, $dataBaseName = null){

        return $this->connectMysql($host, $userName, $password, $dataBaseName);
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
     * Detect if the specified database exists on the current connection
     *
     * @param string $dataBaseName the name of the database
     *
     * @return boolean True if the specified database exists, false otherwise
     */
    public function dataBaseExists($dataBaseName) {

        if($this->_engine === self::MYSQL){

            return (mysqli_num_rows(mysqli_query($this->_mysqlConnectionId, "SHOW DATABASES LIKE '".$dataBaseName."'")) === 1);
        }
    }


    /**
     * Creates a new empty database with the specified name
     *
     * @param string $dataBaseName the name of the database to create
     *
     * @return boolean True if database could be created
     */
    public function dataBaseCreate($dataBaseName){

        if($this->_engine === self::MYSQL &&
           $this->query('CREATE DATABASE '.$dataBaseName) !== false){

            return true;
        }

        throw new UnexpectedValueException('Could not create DB: '.$this->_lastError);
    }


    /**
     * Set the specified database as the active one.
     * DataBaseManager must be connected to a db host for this method to work.
     *
     * @param string $dataBaseName The name for the database that we want to set as active.
     *
     * @return boolean True if database could be selected
     */
    public function dataBaseSelect($dataBaseName){

        if(!is_string($dataBaseName)|| $dataBaseName === ''){

            throw new UnexpectedValueException('DataBase name must be a non empty string');
        }

        if(!$this->isConnected()){

            throw new UnexpectedValueException('Not connected to a database host');
        }

        if($this->_engine === self::MYSQL){

            if(mysqli_select_db($this->_mysqlConnectionId, $dataBaseName)){

                $this->_selectedDatabase = $dataBaseName;

            }else{

                throw new UnexpectedValueException('Could not select database '.$dataBaseName);
            }
        }

        return true;
    }


    /**
     * Drops the specified database. Use with caution.
     *
     * @param string $dataBaseName the name of the database
     *
     * @return boolean True if the database was successfully deleted
     */
    public function dataBaseDelete($dataBaseName){

        if($this->_engine === self::MYSQL && $this->query('DROP DATABASE '.$dataBaseName) === false){

            throw new UnexpectedValueException('Could not delete database '.$dataBaseName.': '.$this->_lastError);
        }

        return true;
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
     * @return boolean|int|array <br>
     * - False if the query generates any error (error message will be available with getLastError())<br>
     * - An associative array with the query result data for queries that generate values (like SELECT, SHOW, DESCRIBE or EXPLAIN).<br>
     * - An integer containing the affected rows for successful queries that do not generate vaules (like CREATE, DROP, UPDATE, INSERT...).<br>
     */
    public function query($query){

        if(!$this->isConnected()){

            throw new UnexpectedValueException('Not connected to a database host');
        }

        $result = false;

        $queryStart = microtime(true);

        if($this->_engine === self::MYSQL){

            $mysqlResult = mysqli_query($this->_mysqlConnectionId, $query);

            if($mysqlResult === false){

                $errorMessage = mysqli_error($this->_mysqlConnectionId);

                $this->_lastError = StringUtils::isEmpty($errorMessage) ? 'unknown sql error' : $errorMessage;

            }else{

                if($mysqlResult === true){

                    $result = mysqli_affected_rows($this->_mysqlConnectionId);

                }else{

                    $result = [];

                    while($line = mysqli_fetch_assoc($mysqlResult)){

                        $result[] = $line;
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
     * @return mixed The id value
     */
    public function getLastInsertId(){

        if($this->_engine === self::MYSQL){

            return mysqli_insert_id($this->_mysqlConnectionId);
        }
    }


    /**
     * Contains the last error that happened to the database (if any) as a result of operating with this class.
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
     * @return string The name for the currently selected database or '' if no database is selected
     */
    public function getSelectedDataBase(){

        // An active engine connection must be available
        if(!$this->isConnected()){

            throw new UnexpectedValueException('Not connected to a database host');
        }

        if($this->_engine === self::MYSQL){

            $mysqlResult = mysqli_query($this->_mysqlConnectionId, 'SELECT DATABASE() as d');

            if($mysqlResult !== false){

                $line = mysqli_fetch_assoc($mysqlResult);

                return (string)$line['d'];
            }
        }

        throw new UnexpectedValueException('Could not get selected database');
    }


    /**
     * Gets all the values for a given table column, sorted as they are on the table rows.
     * This should be used when we are listing values from a table column that does not have many lines,
     * otherwise we may overflow the result and get performance problems
     *
     * @param string $tableName Table that contains the requested column
     * @param string $columnName Name for the table column we want to list
     *
     * @return array A list with all the values that can be found on the specified table and column. Note that list is unique, repeated values are removed
     */
    public function getTableColumnValues($tableName, $columnName){

        if($this->_engine === self::MYSQL){

            $mysqlResult = $this->query('SELECT DISTINCT '.$columnName.' FROM '.$tableName);

            if($mysqlResult !== false){

                $result = [];

                foreach ($mysqlResult as $value) {

                    $result[] = $value[$columnName];
                }

                return $result;
            }
        }

        throw new UnexpectedValueException('Could not list table column values: '.$this->getLastError());
    }


    /**
     * Get a list with all the column names from the specified table with the same order as they appear on it.
     *
     * @param string $tableName the table name
     *
     * @return array The list of all column names on the requested table
     */
    public function getTableColumnNames($tableName){

        if($this->_engine === self::MYSQL){

            $mysqlResult = $this->query('SHOW COLUMNS FROM '.$tableName);

            if($mysqlResult !== false){

                $result = [];

                foreach ($mysqlResult as $row) {

                    $result[] = $row['Field'];
                }

                return $result;
            }
        }

        throw new UnexpectedValueException('Could not get column names: '.$this->getLastError());
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

        if($this->_engine === self::MYSQL &&
           ($result = $this->query('SELECT CAST('.$columnName.' AS SIGNED) AS '.$columnName.' FROM '.$tableName.' ORDER BY '.$columnName.' DESC LIMIT 1')) !== false){

            return $result[0][$columnName];
        }

        throw new UnexpectedValueException('Could not get column max value: '.$this->getLastError());
    }


    /**
     * Get the total number of rows for a given table
     *
     * @param string $tableName The name for the table we want to count
     *
     * @return int The total number of table rows
     */
    public function countTableRows($tableName){

        if(($result = $this->query('select count(1) as c FROM '.$tableName)) !== false){

            return (int)$result[0]['c'];
        }

        throw new UnexpectedValueException('Could not count table rows: '.$this->getLastError());
    }


    /**
     * Check if the specified table exists on database
     *
     * @param string $tableName the name of the table
     *
     * @return boolean True if the specified table exists, false otherwise
     */
    public function tableExists($tableName) {

        if(!is_string($tableName) || $tableName === ''){

            throw new UnexpectedValueException('Table name name must be a non empty string');
        }

        // A selected database must be available
        if($this->getSelectedDataBase() === ''){

            throw new UnexpectedValueException('Not connected to a database host or database not selected');
        }

        if($this->_engine === self::MYSQL){

            return (mysqli_num_rows(mysqli_query($this->_mysqlConnectionId, "SHOW TABLES LIKE '".$tableName."'")) === 1);
        }
    }


    /**
     * Creates a new empty table with the specified name and columns
     *
     * @param string $tableName The name for the new table to create
     * @param array $columns An array containing all the columns to create and their type. Each array element must be a string with the
     *        column name and the sql data type to use. For example: 'column1 bigint', 'column2 varchar(255)', etc..
     *
     * @throws UnexpectedValueException If the table cannot be created
     *
     * @return boolean True if the table can be correctly created
     */
    public function tableCreate($tableName, array $columns){

        if($this->query('CREATE TABLE '.$tableName.' ('.implode(',', $columns).')') !== false){

            return true;
        }

        throw new UnexpectedValueException('Could not create table '.$tableName.' '.$this->_lastError);
    }


    /**
     * Add a new column to the specified table on the active database
     *
     * @param string $tableName The name for the table where we want to add a new column
     * @param string $columnName The name for the new column we want to add
     * @param string $type The SQL type for the data that will be stored at the new column (for example varchar(255))
     *
     * @throws UnexpectedValueException If the column cannot be added
     *
     * @return boolean True if the table was correctly added
     */
    public function tableAddColumn($tableName, $columnName, $type){

        if($this->query('ALTER TABLE '.$tableName.' ADD '.$columnName.' '.$type) !== false){

            return true;
        }

        throw new UnexpectedValueException('Could not add column '.$columnName.' to table '.$tableName.': '.$this->_lastError);
    }


    /**
     * Add all the values for a single row to the specified database table
     *
     * @param string $tableName The name for the table we want to update
     * @param array $rowValues An associative array with all the data for a single table row, where each array key is the column name and
     *              each array value the column value
     *
     * @return boolean True if the row was correctly added
     */
    public function tableAddRow($tableName, array $rowValues){

        $cols = array_keys($rowValues);

        $values = [];

        foreach ($rowValues as $value) {

            $values[] = $value === null ? 'NULL' : "'".$value."'";
        }

        if($this->query('INSERT INTO '.$tableName.' ('.implode(',', $cols).') VALUES ('.implode(',', $values).')') !== false){

            return true;
        }

        throw new UnexpectedValueException('Could not add row to table '.$tableName.': '.$this->_lastError);
    }


    /**
     * Update all the values for an existing single row on the specified database table
     *
     * @param string $tableName The name for the table we want to update
     * @param string $primaryKeyName The name for the column that contains the table primary key
     * @param mixed $primaryKeyValue The value that must be found on the primary key for the row to be updated
     * @param array $rowValues An associative array with all the data for a single table row, where each array key is the column name and
     *              each array value the column value
     *
     * @throws UnexpectedValueException In case the update could not be performed
     *
     * @return boolean True if the row is correctly updated
     */
    public function tableUpdateRow($tableName, $primaryKeyName, $primaryKeyValue, array $rowValues){

        $values = [];

        foreach ($rowValues as $colName => $value) {

            $values[] = $colName.' = '.($value === null ? 'NULL' : "'".$value."'");
        }

        if($this->query('UPDATE '.$tableName.' SET '.implode(',', $values).' WHERE '.$primaryKeyName."='".$primaryKeyValue."'") === 1){

            return true;
        }

        throw new UnexpectedValueException('Could not update row on table '.$tableName.': '.$this->_lastError);
    }


    /**
     * Delete the specified database table
     *
     * @param string $tableName the name of the table to delete
     *
     * @return boolean True if the specified was successfuly deleted, false otherwise
     */
    public function tableDelete($tableName) {

        $this->_validateTable($tableName);

        if($this->_engine === self::MYSQL &&
           $this->query('DROP TABLE '.$tableName) !== false){

            return true;
        }

        throw new UnexpectedValueException('Could not delete table '.$tableName.': '.$this->_lastError);
    }


    /**
     * Validate that the specified table is correct and exists on db
     *
     * @param string $tableName The name for a table to validate
     *
     * @throws UnexpectedValueException In case any problem is found with the specified table
     */
    private function _validateTable($tableName){

        // An active engine connection must be available and a database selected
        if($this->getSelectedDataBase() === ''){

            throw new UnexpectedValueException('Not connected to a database host or database not selected');
        }

        if(!is_string($tableName) || $tableName === ''){

            throw new UnexpectedValueException('Table name name must be a non empty string');
        }

        if(!$this->tableExists($tableName)){

            throw new UnexpectedValueException('Table '.$tableName.' does not exist');
        }
    }


    /**
     * Start a database transaction.
     *
     * @throws UnexpectedValueException In case transaction could not start
     *
     * @return boolean True if the transaction started correctly
     */
    public function transactionBegin(){

        if($this->_engine === self::MYSQL && $this->query('START TRANSACTION') !== false){

            $this->_isAnyTransactionActive = true;

            return true;
        }

        $this->_isAnyTransactionActive = false;

        throw new UnexpectedValueException('Could not start transaction');
    }


    /**
     * Tells if there's any transaction in progress
     *
     * @return boolean
     */
    public function isAnyTransactionActive(){

        return $this->_isAnyTransactionActive;
    }


    /**
     * Rollback a database transaction
     *
     * @throws UnexpectedValueException In case transaction could not be rolled back
     *
     * @return boolean True if the transaction rolled correctly
     */
    public function transactionRollback(){

        if($this->_engine === self::MYSQL && $this->query('ROLLBACK') !== false){

            $this->_isAnyTransactionActive = false;

            return true;
        }

        throw new UnexpectedValueException('Could not rollback transaction');
    }


    /**
     * Commit a database transaction
     *
     * @throws UnexpectedValueException In case transaction could not be commited
     *
     * @return boolean True if the transaction commited correctly
     */
    public function transactionCommit(){

        if($this->_engine === self::MYSQL && $this->query('COMMIT') !== false){

            $this->_isAnyTransactionActive = false;

            return true;
        }

        throw new UnexpectedValueException('Could not commit transaction');
    }


    /**
     * Close the current database conection
     *
     * @throws UnexpectedValueException In case connection could not be closed
     *
     * @return boolean True if the disconnect was successful
     */
    public function disconnect() {

        // Check if we are currently connected to a mysql engine
        if($this->_engine === self::MYSQL && mysqli_close($this->_mysqlConnectionId)){

            $this->_engine = '';
            $this->_mysqlConnectionId = null;
            $this->_queryHistory = [];
            $this->_lastError = '';
            $this->_accumulatedQueryTime = 0;
            $this->_lastQuerySucceeded = false;
            $this->_selectedDatabase = '';

            return true;
        }

        throw new UnexpectedValueException('Could not close connection');
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
        if($query === 'START TRANSACTION' || $query === 'ROLLBACK' || $query === 'COMMIT'){

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
        $this->_queryHistory[] = $queryHistory;
    }
}

?>
