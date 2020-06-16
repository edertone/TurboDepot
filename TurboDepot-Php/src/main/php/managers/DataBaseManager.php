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
     * Defines the mysql and mariadb database engine names
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

        // Timezone is set to UTC to prevent date and time problems
        if(!mysqli_query($id, "SET SESSION time_zone = '+00:00'")){

            throw new UnexpectedValueException('Could not set connection to UTC'.mysqli_error($id));
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
     * Initialize an SQLite database connection with the specified parameters
     * TODO
     *
     * @return boolean True on success or false if connection was not possible
     */
    public function connectSQLite($todo){

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
     * Get the name for the database that is currently selected
     *
     * @return string The name for the currently selected database or '' if no database is selected
     */
    public function dataBaseGetSelected(){

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
     * - An array of associative arrays with the query result data for queries that generate values (like SELECT, SHOW, DESCRIBE or EXPLAIN). Note that in PHP
     *   all query result values are returned as strings which must be casted to the appropiate types by the user<br>
     * - An integer containing the affected rows for successful queries that do not generate vaules (like CREATE, DROP, UPDATE, INSERT...).<br>
     */
    public function query($query){

        $result = false;

        $queryStart = microtime(true);

        if($this->_engine === self::MYSQL){

            $mysqlResult = mysqli_query($this->_mysqlConnectionId, $query);

            if($mysqlResult === false){

                $errorMessage = mysqli_error($this->_mysqlConnectionId);

                $this->_lastError = StringUtils::isEmpty($errorMessage) ? 'unknown sql error' : $errorMessage;

            }else if($mysqlResult === true){

                $result = mysqli_affected_rows($this->_mysqlConnectionId);

            }else{

                $result = mysqli_fetch_all($mysqlResult, MYSQLI_ASSOC);
            }
        }

        // Save the query information to history
        $this->_addQueryToHistory($query, $queryStart, $result);

        // Store if the last query was successful or not
        $this->_lastQuerySucceeded = ($result !== false);

        return $result;
    }


    /**
     * Check if the provided SQL type string corresponds to a numeric double (or float) type
     *
     * @param string $sqlType A valid SQL type definition like int, bigint, varchar(20), double NOT NULL, varchar(250) NOT NULL etc...
     *
     * @return boolean True if the provided SQL type represents a numeric double type
     */
    public function isSQLDoubleType(string $sqlType){

        $sqlTypeName = explode(' ', explode('(', trim($sqlType))[0])[0];

        if($this->_engine === self::MYSQL){

            return $sqlTypeName === 'double';
        }
    }


    /**
     * Check if the provided SQL type string corresponds to a numeric data type like int, small int, big int, double...
     *
     * @param string $sqlType A valid SQL type definition like int, bigint, varchar(20), double NOT NULL, varchar(250) NOT NULL etc...
     *
     * @return boolean True if the provided SQL type represents a numeric type
     */
    public function isSQLNumericType(string $sqlType){

        $sqlTypeName = explode(' ', explode('(', trim($sqlType))[0])[0];

        if($this->_engine === self::MYSQL){

            return in_array($sqlTypeName, ['smallint', 'mediumint', 'int', 'bigint', 'double']);
        }
    }


    /**
     * Check if the provided SQL type string corresponds to a string data type like varchar
     *
     * @param string $sqlType A valid SQL type definition like int, bigint, varchar(20), double NOT NULL, varchar(250) NOT NULL etc...
     *
     * @return boolean True if the provided SQL type represents a string type
     */
    public function isSQLStringType(string $sqlType){

        $sqlTypeName = explode(' ', explode('(', trim($sqlType))[0])[0];

        if($this->_engine === self::MYSQL){

            return $sqlTypeName === 'varchar';
        }
    }


    /**
     * Check if the provided SQL type string corresponds to a datetime data type like datetime, datetime(3), ...
     *
     * @param string $sqlType A valid SQL type definition like int, bigint, varchar(20), double NOT NULL, varchar(250) NOT NULL etc...
     *
     * @return boolean True if the provided SQL type represents a datetime type
     */
    public function isSQLDateTimeType(string $sqlType){

        $sqlTypeName = explode(' ', explode('(', trim($sqlType))[0])[0];

        if($this->_engine === self::MYSQL){

            return $sqlTypeName === 'datetime';
        }
    }


    /**
     * Check that two SQL types correspond to numeric data types and the first one has enought precision to store values from the second one
     *
     * @param string $sqlType1 an SQL type definition that must declare a numeric type and have enough precision to store values from sqlType2
     * @param string $sqlType2 an SQL type definition that must declare a numeric type and which must fit into the first one
     *
     * @return boolean True if both SQL type definitions are numeric and values from second SQL type fit on the first one
     */
    public function isSQLNumericTypeCompatibleWith(string $sqlType1, string $sqlType2){

        if($this->_engine === self::MYSQL){

            // List of numeric sql types sorted by smallest to biggest precision
            $sqlTypes = ['smallint', 'mediumint', 'int', 'bigint', 'double'];

            $sqlType1Index = array_search(explode(' ', explode('(', trim($sqlType1))[0])[0], $sqlTypes);
            $sqlType2Index = array_search(explode(' ', explode('(', trim($sqlType2))[0])[0], $sqlTypes);

            if($sqlType1Index !== false && $sqlType2Index !== false){

                return $sqlType1Index >= $sqlType2Index;
            }
        }

        return false;
    }


    /**
     * Check if two SQL types share the same data type
     *
     * @param string $sqlType1 A valid SQL type definition like int, bigint, varchar(20), double NOT NULL, varchar(250) NOT NULL etc...
     * @param string $sqlType2 A valid SQL type definition like int, bigint, varchar(20), double NOT NULL, varchar(250) NOT NULL etc...
     *
     * @return boolean True if both sql types share the same data type like varchar, double, bigint, etc..
     */
    public function isSQLSameType(string $sqlType1, string $sqlType2){

        // Compare the object and table data types without the (N) part. They must be exactly the same
        return $sqlType1 === $sqlType2 || explode('(', $sqlType1)[0] === explode('(', $sqlType2)[0];
    }


    /**
     * Get the SQL type string definition for a date and time database value
     *
     * @param bool $isNullable If set to true, the generated SQL type definition will allow null values, if set to false the type won't allow null values
     * @param bool $secondsPrecision Set it to 0 to use only seconds, to 3 for miliseconds precision and to 6 for microseconds precision
     *
     * @return string A valid datetime SQL type string definition like 'datetime', 'datetime(3)', 'datetime NOT NULL', etc...
     */
    public function getSQLDateTimeType(bool $isNullable = true, int $secondsPrecision = 0){

        $sqlNotNull = $isNullable ? '' : ' NOT NULL';

        if($this->_engine === self::MYSQL){

            switch ($secondsPrecision) {

                case 3:
                    $sqlPrecision = '(3)';
                    break;

                case 6:
                    $sqlPrecision = '(6)';
                    break;

                default:
                    $sqlPrecision = '';
            }

            return 'datetime'.$sqlPrecision.$sqlNotNull;
        }
    }


    /**
     * Obtain the size for the provided SQL type string
     *
     * @param string $sqlType A valid SQL type definition like int, bigint, varchar(20), double NOT NULL, varchar(250) NOT NULL etc...
     *
     * @return number The sql type size. For example: varchar(20) will return 20, varchar(250) 250, etc..
     */
    public function getSQLTypeSize(string $sqlType){

        $sqlTypeExploded = explode('(', $sqlType);

        return isset($sqlTypeExploded[1]) ? (int)substr($sqlTypeExploded[1], 0, -1) : 0;
    }


    /**
     * Given any raw value this method will generate the SQL type string definition that will allow us to store that value with the
     * smallest precision possible.
     *
     * @param mixed $value Any raw value we want to evaluate
     * @param boolean $isNullable If set to true, the generated SQL type definition will allow null values, if set to false the type won't allow null values
     * @param boolean $isUnsigned Only valid for numeric types, If set to true the generated SQL type definition will specify only positive values, if set
     *        to false positive and negative values
     * @param boolean $isAutoIncrement If set to true, the generated SQL type definition will declare auto increment values
     *
     * @return string The SQL type that can be used to declare columns to can store the given value to database tables like SMALLINT,
     *         VARCHAR(20), BIGINT NOT NULL, INT UNSIGNED, etc..
     */
    public function getSQLTypeFromValue($value, bool $isNullable = true, bool $isUnsigned = false, $isAutoIncrement = false){

        if($this->_engine === self::MYSQL){

            $sqlNotNull = $isNullable ? '' : ' NOT NULL';
            $sqlUnsigned = $isUnsigned ? ' UNSIGNED' : '';
            $sqlAutoIncrement = $isAutoIncrement ? ' AUTO_INCREMENT' : '';

            if(is_bool($value)){

                return 'tinyint(1)'.$sqlNotNull;
            }

            if(is_integer($value)){

                // We calculate the biggest possible value with the provided number of digits (9999....) to check which mysql type will fit
                $maxIntValue = pow(10, strlen((string)abs($value))) - 1;

                if($isUnsigned){

                    if($maxIntValue < 65535){

                        return 'smallint'.$sqlUnsigned.$sqlNotNull.$sqlAutoIncrement;
                    }

                    if($maxIntValue < 16777215){

                        return 'mediumint'.$sqlUnsigned.$sqlNotNull.$sqlAutoIncrement;
                    }

                    if($maxIntValue < 4294967295){

                        return 'int'.$sqlUnsigned.$sqlNotNull.$sqlAutoIncrement;
                    }

                    return 'bigint'.$sqlUnsigned.$sqlNotNull.$sqlAutoIncrement;

                }else{

                    if($maxIntValue < 32767){

                        return 'smallint'.$sqlUnsigned.$sqlNotNull.$sqlAutoIncrement;
                    }

                    if($maxIntValue < 8388607){

                        return 'mediumint'.$sqlUnsigned.$sqlNotNull.$sqlAutoIncrement;
                    }

                    if($maxIntValue < 2147483647){

                        return 'int'.$sqlUnsigned.$sqlNotNull.$sqlAutoIncrement;
                    }

                    return 'bigint'.$sqlUnsigned.$sqlNotNull.$sqlAutoIncrement;
                }
            }

            if(is_float($value)){

                return 'double'.$sqlUnsigned.$sqlNotNull.$sqlAutoIncrement;
            }

            if(is_string($value)){

                $valueLen = max(1, strlen($value));
                return $valueLen > 65500 ? 'longtext' : 'varchar('.$valueLen.')'.$sqlNotNull;
            }
        }

        throw new UnexpectedValueException('Could not detect SQL type from value: '.gettype($value));
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

        if($this->_engine === self::MYSQL){

            return (mysqli_num_rows(mysqli_query($this->_mysqlConnectionId, "SHOW TABLES LIKE '".$tableName."'")) === 1);
        }
    }


    /**
     * Creates a new empty table with the specified name, columns and constraints
     *
     * @param string $tableName The name for the new table to create
     * @param array $columns An array containing all the columns to create and their type. Each array element must be a string with the
     *        column name and the sql data type to use. For example: 'column1 bigint', 'column2 varchar(255)', 'column 3 double NOT NULL' etc...
     * @param array $primaryKey An array with all the column names that conform the table primary key (or empty array if no primary key)
     * @param array $uniqueIndices An array of arrays where each element contains all the column names for each unique index to create
     * @param array $indices An array of arrays where each element contains all the column names for each index to create
     *
     * @throws UnexpectedValueException If the table cannot be created
     *
     * @return boolean True if the table can be correctly created
     */
    public function tableCreate($tableName, array $columns, array $primaryKey = [], array $uniqueIndices = [], array $indices = []){

        if($this->_engine === self::MYSQL){

            if(count($primaryKey) > 0){

                $columns[] = 'PRIMARY KEY ('.implode(',', $primaryKey).')';
            }

            foreach ($uniqueIndices as $uniqueIndice) {

                $columns[] = 'UNIQUE KEY '.$tableName.'_'.implode('_', $uniqueIndice).'_uk ('.implode(',', $uniqueIndice).')';
            }

            foreach ($indices as $indice) {

                $columns[] = 'INDEX '.$tableName.'_'.implode('_', $indice).' ('.implode(',', $indice).')';
            }

            if($this->query('CREATE TABLE '.$tableName.' ('.implode(',', $columns).')') !== false){

                return true;
            }
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
     * Create a new foreign key on the specified table
     *
     * @param string $tableName The name for the table where we want to add a new column
     * @param string $fkName The name we want to set to the new foreign key
     * @param array $fkColumns The colums that are affected by the new foreign key
     * @param string $refTable The name for the table that is referenced by this foreign key (the one that contains pk values that must exist on our table)
     * @param array $refColumns The colums on the referenced table that are referenced by the new foreign key
     * @param string $onDelete What to do when a delete happens (by default CASCADE)
     * @param string $onUpdate What to do when an update happens (by default CASCADE)
     *
     * @throws UnexpectedValueException If the foreign key cannot be added
     *
     * @return boolean True if the foreign key was correctly added
     */
    public function tableAddForeignKey($tableName, $fkName, array $fkColumns, $refTable, array $refColumns, $onDelete = 'CASCADE', $onUpdate = 'CASCADE'){

        if($this->query('ALTER TABLE '.$tableName.' ADD CONSTRAINT '.$fkName.
            ' FOREIGN KEY ('.implode(',', $fkColumns).') REFERENCES '.$refTable.'('.implode(',', $refColumns).
            ') ON DELETE '.$onDelete.' ON UPDATE '.$onUpdate) !== false){

            return true;
        }

        throw new UnexpectedValueException('Could not add foreignKey '.$fkName.' to table '.$tableName.': '.$this->_lastError);
    }


    /**
     * Get a list with all the column names from the specified table with the same order as they appear on it.
     *
     * @param string $tableName the table name
     *
     * @return array The list of all column names on the requested table
     */
    public function tableGetColumnNames($tableName){

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

        throw new UnexpectedValueException('Could not get column names: '.$this->_lastError);
    }


    /**
     * Obtain the list of SQL data types and precision for each one of the provided table columns
     *
     * @param string $tableName The table name
     *
     * @throws UnexpectedValueException If column data types could not be obtained
     *
     * @return array Associative array where each key is the table column name and each value the table column data type
     *         (like varchar(20), bigint(20) unsigned, varchar(3) NOT NULL, ...). Array is sorted in the same way as columns are in the table.
     */
    public function tableGetColumnDataTypes($tableName){

        $result = [];

        if($this->_engine === self::MYSQL &&
           ($types = $this->query("SHOW FIELDS FROM `".$tableName."`")) !== false){

            foreach ($types as $type) {

                $result[$type['Field']] = strtolower($type['Type']).($type['Null'] === 'NO' ? ' NOT NULL' : '');
            }

            return $result;
        }

        throw new UnexpectedValueException('Could not get column data types: '.$this->_lastError);
    }


    /**
     * Gets the maximum value for a specified column on the specified table.
     *
     * @param string $tableName the table name
     * @param string $columnName Name for the column we want to get the maximum value
     *
     * @return float The maximum value that was found on the given table and colum
     */
    public function tableGetColumnMaxValue($tableName, $columnName){

        if($this->_engine === self::MYSQL &&
            ($result = $this->query('SELECT CAST('.$columnName.' AS SIGNED) AS '.$columnName.' FROM '.$tableName.' ORDER BY '.$columnName.' DESC LIMIT 1')) !== false){

                return $result[0][$columnName];
        }

        throw new UnexpectedValueException('Could not get column max value: '.$this->_lastError);
    }


    /**
     * Gets all the values for a given table column, sorted as they are on the table rows.
     * This should be used when we are listing values from a table column that does not have many lines,
     * otherwise we may overflow the result and get performance problems
     *
     * @param string $tableName Table that contains the requested column
     * @param string $columnName Name for the table column we want to list
     * @param string $removeDuplicates If set to true, all the duplicate values will be removed from the resulting list
     *
     * @return array A list with all the values that can be found on the specified table and column. Note that all values are returned in a string format,
     *         except the null value which is returned as null.
     */
    public function tableGetColumnValues($tableName, $columnName, $removeDuplicates = false){

        $sqlDistinct = $removeDuplicates ? ' DISTINCT ': '';

        if($this->_engine === self::MYSQL){

            $mysqlResult = $this->query('SELECT '.$sqlDistinct.' '.$columnName.' FROM '.$tableName);

            if($mysqlResult !== false){

                $result = [];

                foreach ($mysqlResult as $value) {

                    $result[] = $value[$columnName];
                }

                return $result;
            }
        }

        throw new UnexpectedValueException('Could not list table column values: '.$this->_lastError);
    }


    /**
     * Add all the values for one or more rows to the specified database table
     *
     * @param string $tableName The name for the table we want to update
     * @param array $rowValues An array of associative arrays with all the data for a single table row, where each array key is the column name and
     *              each array value the column value
     *
     * @return boolean True if the row was correctly added
     */
    public function tableAddRows($tableName, array $rows){

        $cols = array_keys($rows[0]);

        $sqlRows = [];

        foreach ($rows as $row) {

            $sqlRow = [];

            foreach ($row as $value) {

                $sqlRow[] = $this->_prepareRawValeForSqlQuery($value);
            }

            $sqlRows[] = '('.implode(',', $sqlRow).')';
        }

        if($this->query('INSERT INTO '.$tableName.' ('.implode(',', $cols).') VALUES '.implode(',', $sqlRows)) !== false){

            return true;
        }

        throw new UnexpectedValueException('Could not add row to table '.$tableName.': '.$this->_lastError);
    }


    /**
     * Update all the values for an existing single row on the specified database table
     *
     * @param string $tableName The name for the table we want to update
     * @param array $keyValues Associative array with key/value pairs (column names / values) that must exist on the row to be updated
     * @param array $rowValues An associative array with all the data to be updated on the table row, where each array key is the column name and
     *              each array value the column value
     *
     * @throws UnexpectedValueException In case the update could not be performed
     *
     * @return boolean True if the row is correctly updated
     */
    public function tableUpdateRow($tableName, array $keyValues, array $rowValues){

        $sqlKey = [];

        foreach ($keyValues as $keyName => $keyValue) {

            $sqlKey[] = $keyName."='".$keyValue."'";
        }

        $sqlKey = implode(' AND ', $sqlKey);

        $values = [];

        foreach ($rowValues as $colName => $value) {

            $values[] = $colName.' = '.$this->_prepareRawValeForSqlQuery($value);
        }

        $queryResult = $this->query('UPDATE '.$tableName.' SET '.implode(',', $values).' WHERE '.$sqlKey);

        // A 0 result may mean that the table row existed but all the row values were already the same and nothing was updated, but the result is ok
        if($queryResult === 1 ||
           ($queryResult === 0 && count($this->query('SELECT * FROM '.$tableName.' WHERE '.$sqlKey)) === 1)){

            return true;
        }

        throw new UnexpectedValueException('Could not update row on table '.$tableName.' for '.$sqlKey.' '.$this->_lastError);
    }


    /**
     * Look for a row that contains the specified column values and update it with the provided row data or add a new row
     * if the key values are not found on the specified table.
     *
     * @param string $tableName The name for the table we want to update or add a new row
     * @param array $keyValues Associative array with key/value pairs (column names / values) that must exist on the row to be updated. if not
     *        found, a new row will be added
     * @param array $rowValues An associative array where each key is the column name and each value the column value. This row data will be updated on
     *        the provided table if the keyValues are found, or a new row will be added to the table otherwise
     *
     * @return boolean True if the row was correctly added or updated
     */
    public function tableAddOrUpdateRow($tableName, array $keyValues, array $rowValues){

        if(count($this->tableGetRows($tableName, $keyValues)) === 1){

            return $this->tableUpdateRow($tableName, $keyValues, $rowValues);
        }

        return $this->tableAddRows($tableName, [$rowValues]);
    }


    /**
     * Get the total number of rows for a given table
     *
     * @param string $tableName The name for the table we want to count
     *
     * @return int The total number of table rows
     */
    public function tableCountRows($tableName){

        if(($result = $this->query('select count(1) as c FROM '.$tableName)) !== false){

            return (int)$result[0]['c'];
        }

        throw new UnexpectedValueException('Could not count table rows: '.$this->_lastError);
    }


    /**
     * Obtain all the data for the table rows that match the specified column values criteria
     *
     * @param string $tableName The name of the table for which we want to obtain the rows data
     * @param string $columnValues Associative array where keys are column names and values are column values that must be found on all the rows
     *        be returned
     *
     * @return array An array of associative arrays with the query result data. Note that in PHP all query result values are returned as strings
     *         which must be casted to the appropiate types by the user
     */
    public function tableGetRows($tableName, array $columnValues){

        $sqlWherePart = [];

        foreach ($columnValues as $columnName => $value) {

            $sqlWherePart[] = $columnName." = '".$value."'";
        }

        return $this->query('SELECT * FROM '.$tableName.' WHERE '.implode(' AND ', $sqlWherePart));
    }


    /**
     * Delete the specified database table
     *
     * @param string $tableName The name of the table to delete
     *
     * @return boolean True if the specified was successfuly deleted
     */
    public function tableDelete($tableName) {

        if($this->query('DROP TABLE '.$tableName) !== false){

            return true;
        }

        throw new UnexpectedValueException('Could not delete table '.$tableName.': '.$this->_lastError);
    }


    /**
     * Delete one or more columns from the specified table
     *
     * @param string $tableName The name of the table from which columns will be deleted
     * @param array $columnNames List of names for the columns that will be deleted
     *
     * @return boolean True if all the specified columns were successfuly deleted
     */
    public function tableDeleteColumns(string $tableName, array $columnNames) {

        if($this->query('ALTER TABLE '.$tableName.' DROP COLUMN '.implode(', DROP COLUMN', $columnNames)) !== false){

            return true;
        }

        throw new UnexpectedValueException('Could not delete columns ('.implode(',', $columnNames).') from '.$tableName.': '.$this->_lastError);
    }


    /**
     * Deletes all the rows from a table that meet the specified column values
     *
     * @param string $tableName The name for the table that we want to delete rows from
     * @param array $columnValues An associative array where each key is the name of a column and each value the value that must be
     *              found on the table rows that will be deleted. A row will be deleted from the table only if all the column values
     *              defined here are found.
     *
     * @return int The number of deleted rows
     */
    public function tableDeleteRows(string $tableName, array $columnValues) {

        $sqlWhere = [];

        foreach ($columnValues as $columnName => $value) {

            $sqlWhere[] = $columnName."='".$value."'";
        }

        if($this->_engine === self::MYSQL &&
            ($result = $this->query('DELETE FROM '.$tableName.' WHERE '.implode(' AND ', $sqlWhere))) !== false){

            return $result;
        }

        throw new UnexpectedValueException('Error trying to delete rows from '.$tableName.': '.$this->_lastError);
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


    /**
     * Given a raw php value, this method will generate a string that is ready to be used on a SQL query to represent that value.
     * For example, a true boolean value will output 'TRUE', a null value 'NULL', a string value will be sngle qouted, etc..
     *
     * @param mixed $value
     *
     * @return string The php value ready to be used on a SQL query
     */
    private function _prepareRawValeForSqlQuery($value){

        if($value === null){

            return 'NULL';
        }

        if(is_bool($value)){

            return $value === true ? "TRUE" : "FALSE";
        }

        return is_string($value) ? "'".$value."'" : $value;
    }
}

?>