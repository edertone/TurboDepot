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

use Throwable;
use UnexpectedValueException;
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbocommons\src\main\php\utils\ArrayUtils;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * DataBaseObjectsManager class
 */
class DataBaseObjectsManager extends BaseStrictClass{


    /**
     * To prevent name collisions with any other possible existing database tables, we can define a prefix here that will be
     * added to all the tables that are used by this class. It will be automatically added when tables are created, and expected when
     * tables are read.
     *
     * @var string
     */
    public $tablesPrefix = 'tdp_';


    /**
     * This flag specifies what to do when saving an object which table does not exist on database.
     *
     * If set to true (default), the table and all the columns to store the object will be automatically created from the object properties.
     * If set to false, an exception will happen and we will need to manually alter the database by ourselves.
     *
     * TODO - implement this
     *
     * @var boolean
     */
    public $isTableCreatedWhenMissing = true;


    /**
     * This flag specifies what to do when saving an object which table exists on database but has different column names, number or data types.
     *
     * If set to true, any difference that is found between the structure of the saved object and the related table or tables will be applied, effectively altering the tables.
     * If set to false (default), an exception will happen and we will need to manually alter the database by ourselves.
     *
     * WARNING: Enabling this flag will keep the database tables up to date to the objects structure changes, but may lead to data loss in production
     * environments, so use carefully
     *
     * TODO - implement this
     *
     * @var boolean
     */
    public $isTableAlteredWhenColumnsChange = false;


    /**
     * This flag specifies what to do when saving an object that contains a property with a value that is bigger than how it is defined at the database table.
     *
     * If set to true (default), any column that has smaller type size defined will be increased to fit the new value.
     * If set to false, an exception will happen and we will need to manually alter the database by ourselves.
     *
     * TODO - implement this
     *
     * @var boolean
     */
    public $isColumnResizedWhenValueisBigger = true;


    /**
     * A database manager instance that is used by this class
     * @var DataBaseManager
     */
    private $_dataBaseManager = null;


    /**
     * Defines the format in which dates are stored to database tables
     * @var string
     */
    private $_sqlDateFormat = 'Y-m-d H:i:s';

    /**
     * Contains the list of table column names and data types that must exist on all the created objects by default.
     * These represent the DataBaseObject base properties that are common to all the objects.
     *
     * @var array
     */
    private $_baseObjectColumns = ['db_id' => 'bigint', 'uuid' => 'varchar(36)', 'sort_index' => 'bigint',
        'creation_date' => 'datetime', 'modification_date' => 'datetime', 'deleted' => 'datetime'];


    /**
     * Class that lets us store objects directly to database without having to care about sql queries
     *
     * TODO - more docs
     */
    public function __construct(){

        $this->_dataBaseManager = new DataBaseManager();
    }


    /**
     * @see DataBaseManager::connectMysql
     */
    public function connectMysql($host, $userName, $password, $dataBaseName = null){

        return $this->_dataBaseManager->connectMysql($host, $userName, $password, $dataBaseName);
    }


    /**
     * @see DataBaseManager::connectMariaDb
     */
    public function connectMariaDb($host, $userName, $password, $dataBaseName = null){

        return $this->_dataBaseManager->connectMariaDb($host, $userName, $password, $dataBaseName);
    }


    /**
     * Reference to a fully initialized DatabaseManager instance which will allow us to directly operate with the curent database
     * connection. This is useful in case we want to perform direct or low level database operations.
     *
     * @return DataBaseManager An initialized DataBaseManager that is connected to the same database.
     */
    public function getDataBaseManager(){

        return $this->_dataBaseManager;
    }


    /**
     * Saves an object to database by updating it if already exists (when dbId is not null) or by creating a new one (when dbId is null)
     *
     * @param DataBaseObject $object An instance to save (when dbId is null) or update (when it has a dbId that exists on db)
     *
     * @return int The dbId value for the object that's been saved
     */
    public function save(DataBaseObject $object){

        $this->_validateDataBaseObject($object);

        $tableName = $this->getTableNameFromObject($object);
        $objectValues = $this->geTableDataFromObject($object);

        $this->_dataBaseManager->transactionBegin();

        try {

            if(!$this->_dataBaseManager->tableExists($tableName)){

                $this->_createObjectTable($tableName, $objectValues);

            }else{

                $this->_updateTableToFitObject($tableName, $objectValues);
            }

            // Store or update the object into the database
            $objectValues['modification_date'] = date($this->_sqlDateFormat);

            if($object->dbId === null){

                $objectValues['creation_date'] = date($this->_sqlDateFormat);

                $this->_dataBaseManager->tableAddRow($tableName, $objectValues);

                $object->dbId = $this->_dataBaseManager->getLastInsertId();

            }else{

                $this->_dataBaseManager->tableUpdateRow($tableName, 'db_id', $object->dbId, $objectValues);
            }

            // TODO - We will store properties that store multiple values as table relations like for example
            // customer_emails which will contain the parent id and the different values. (Think and improve this concept)

            $object->modificationDate = $objectValues['modification_date'];
            $object->creationDate = $objectValues['creation_date'];

            $this->_dataBaseManager->transactionCommit();

            return $object->dbId;

        } catch (Throwable $e) {

            $this->_dataBaseManager->transactionRollback();

            throw $e;
        }
    }


    /**
     * TODO
     */
    public function getByDbId($class, $dbid) {

        // TODO - Obtain one or more Database objects given its id or ids
    }


    /**
     * TODO
     */
    public function getByDbIds($class, array $dbids) {

        // TODO - Obtain one or more Database objects given its id or ids
    }


    /**
     * TODO
     */
    public function getByFilter() {

        // TODO - Obtain one or more Database objects given a complex filter
    }


    /**
     * @see DataBaseManager::disconnect
     */
    public function disconnect() {

        return $this->_dataBaseManager->disconnect();
    }


    /**
     * Obtain the name for the table that would be used store the provided object when saved to database.
     *
     * It is calculated by converting its classname to lower_snake_case, which is the most compatible way to store table names
     * on different Operating systems (this is cause it does not contain upper case letters which would be problematic in Windows OS for example).
     *
     * (Note that also the currently defined tablesPrefix value will be added at the beginning of the table name).
     *
     * @see DataBaseObjectsManager::$tablesPrefix
     *
     * @param DataBaseObject $object An instance to calculate its respective table name.
     *
     * @return string The table name that would be used to store the provided object when saved to database.
     */
    public function getTableNameFromObject(DataBaseObject $object) {

        return $this->tablesPrefix.StringUtils::formatCase(StringUtils::getPathElement(get_class($object)), StringUtils::FORMAT_LOWER_SNAKE_CASE);
    }


    /**
     * Obtain an associative array containing column name and values for the table that would be used store the provided object when saved to database.
     *
     * It is calculated by converting all the object properties to lower_snake_case, which is the most compatible way to store table column names
     * on different Operating systems (this is cause it does not contain upper case letters which would be problematic in Windows OS for example).
     *
     * @param DataBaseObject $object An instance to calculate its respective table columns and data
     *
     * @return number
     */
    public function geTableDataFromObject(DataBaseObject $object){

        $tableData = [];

        foreach (get_object_vars($object) as $key => $value) {

            // Convert all column names to snake case
            $tableData[StringUtils::formatCase($key, StringUtils::FORMAT_LOWER_SNAKE_CASE)] = $value;
        }

        // Move the base common properties to the begining of the array, so they get correctly sorted at the db table
        $tableData = ['deleted' => $tableData['deleted']] + $tableData;
        $tableData = ['modification_date' => $tableData['modification_date']] + $tableData;
        $tableData = ['creation_date' => $tableData['creation_date']] + $tableData;
        $tableData = ['sort_index' => $tableData['sort_index']] + $tableData;
        $tableData = ['uuid' => $tableData['uuid']] + $tableData;
        $tableData = ['db_id' => $tableData['db_id']] + $tableData;

        return $tableData;
    }


    /**
     * Aux method to create the table that represents the provided object on database
     *
     * @param string $tableName The name for the DataBaseObject table
     * @param array $tableData Array with all the object data with snake case column names
     */
    private function _createObjectTable($tableName, $tableData){

        $tableColumns = [];

        foreach ($tableData as $columnName => $value) {

            switch ($columnName) {

                case 'db_id':
                    $tableColumns[] = $columnName.' '.$this->_baseObjectColumns['db_id'].' NOT NULL AUTO_INCREMENT';
                    break;

                case 'uuid':
                    $tableColumns[] = $columnName.' '.$this->_baseObjectColumns['uuid'];
                    break;

                case 'sort_index':
                    $tableColumns[] = $columnName.' '.$this->_baseObjectColumns['sort_index'];
                    break;

                case 'creation_date':
                case 'modification_date':
                    $tableColumns[] = $columnName.' '.$this->_baseObjectColumns['creation_date'].' NOT NULL';
                    break;

                case 'deleted':
                    $tableColumns[] = $columnName.' '.$this->_baseObjectColumns['deleted'];
                    break;

                default:
                    $tableColumns[] = $columnName.' '.$this->_dataBaseManager->getSQLTypeFromValue($value);
                    break;
            }
        }

        $tableColumns[] = 'PRIMARY KEY (db_id)';

        // TODO - we must create an index to improve sorting for the sortIndex column

        $this->_dataBaseManager->tableCreate($tableName, $tableColumns);
    }


    /**
     * Given a table name and the data so store for one specific object, this method will check that the table columns and types are valid
     * to store the provided object information, and depending on the configuration values it will update the table structure so the object can be correctly
     * saved. The following checks are performed:
     *
     * 1. Test that the table has the same columns as the object data. If not, exception will be thrown unless $this->isTableAlteredWhenColumnsChange is true
     * 2. Test that all the table columns have data types which can store the same object table column values. If not, exception will be thrown unless $this->isTableAlteredWhenColumnsChange is true
     *
     * @param string $tableName The name for the table we want to inspect, and alter if setup allows us
     * @param array $objectTableData The object data that we want to save on the database table
     *
     * @throws UnexpectedValueException If the current setup forbids us to modify the table so it can fit the object data
     *
     * @return void
     */
    private function _updateTableToFitObject($tableName, array $objectTableData){

        $tableColumnTypes = $this->_dataBaseManager->tableGetColumnDataTypes($tableName);

        // Test that the table and object have the same columns
        if(!ArrayUtils::isEqualTo(array_keys($objectTableData), array_keys($tableColumnTypes))){

            if($this->isTableAlteredWhenColumnsChange){

                // TODO - update the table to contain the same columns as the object data, trying to destroy the less possible data

            }else{

                throw new UnexpectedValueException($tableName.' columns ('.implode(',', array_keys($tableColumnTypes)).') are different from its related object');
            }
        }

        // Test that all columns have data types which can store the provided object data
        foreach ($tableColumnTypes as $tableColumnName => $tableColumnType) {

            // The base object properties are ignored because they are already tested by the _validateDataBaseObject method
            if(in_array($tableColumnName, array_keys($this->_baseObjectColumns))){

                continue;
            }

            // Get the sql type that fits the provided object value
            $objectColumnType = $this->_dataBaseManager->getSQLTypeFromValue($objectTableData[$tableColumnName]);

            if($objectColumnType !== $tableColumnType){

                // Remove the (N) part if exists from the object and table column data types
                $objectColumnTypeExploded = explode('(', $objectColumnType);
                $tableColumnTypeExploded = explode('(', $tableColumnType);

                // Compare the object and table data types without the (N) part. They must be exactly the same
                if($objectColumnTypeExploded[0] !== $tableColumnTypeExploded[0]){

                    if($this->isTableAlteredWhenColumnsChange){

                        // TODO - update the table column to accept the same data type as the object expects

                    }else{

                        throw new UnexpectedValueException($tableName.' column '.$tableColumnName.' data type is: '.$tableColumnType.' but should be '.$objectColumnType);
                    }
                }

                // Extract the N value from the datatype(N) on each table and object column data types. If the column N value is smaller than the object one,
                // the table cannot store the object value without truncating.
                if(isset($tableColumnTypeExploded[1]) && isset($objectColumnTypeExploded[1]) &&
                   (int)substr($tableColumnTypeExploded[1], 0, -1) < (int)substr($objectColumnTypeExploded[1], 0, -1)){

                    if($this->isColumnResizedWhenValueisBigger){

                        // Increase the size of the table column so it can fit the object value
                        $this->_dataBaseManager->query('ALTER TABLE '.$tableName.' MODIFY COLUMN '.$tableColumnName.' '.$objectColumnType);

                    }else{

                        throw new UnexpectedValueException($tableName.' column '.$tableColumnName.' data type is: '.$tableColumnType.' but should be '.$objectColumnType);
                    }
                }
            }
        }
    }


    /**
     * Verifies that the specified DataBaseObject instance is correctly defined to be used by this class
     *
     * @param DataBaseObject $object An instance to validate
     *
     * @return string The validation result
     */
    private function _validateDataBaseObject(DataBaseObject $object){

        $className = StringUtils::getPathElement(get_class($object));
        $dateRegex = '/[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]/';

        if($object->dbId !== null && (!is_integer($object->dbId) || $object->dbId < 1)){

            throw new UnexpectedValueException('Invalid '.$className.' dbId: '.$object->dbId);
        }

        if($object->uuid !== null && (!is_string($object->uuid) || strlen($object->uuid) !== 36)){

            throw new UnexpectedValueException('Invalid '.$className.' uuid: '.$object->uuid);
        }

        if($object->sortIndex !== null && (!is_integer($object->sortIndex) || $object->sortIndex < 0)){

            throw new UnexpectedValueException('Invalid '.$className.' sortIndex: '.$object->sortIndex);
        }

        if($object->creationDate !== null && (!is_string($object->creationDate) || !preg_match($dateRegex, $object->creationDate))){

            throw new UnexpectedValueException('Invalid '.$className.' creationDate: '.$object->creationDate);
        }

        if($object->modificationDate !== null && (!is_string($object->modificationDate) || !preg_match($dateRegex, $object->modificationDate))){

            throw new UnexpectedValueException('Invalid '.$className.' modificationDate: '.$object->modificationDate);
        }

        if($object->deleted !== null && (!is_string($object->deleted) || !preg_match($dateRegex, $object->deleted))){

            throw new UnexpectedValueException('Invalid '.$className.' deleted: '.$object->deleted);
        }

        if($object->dbId === null && $object->creationDate !== null){

            throw new UnexpectedValueException('Creation date must be null if dbid is null');
        }

        // TODO - properties that start with __ are forbidden, cause they are reserved for setup properties

        // Database objects must not have any method defined, cause they are only data containers
        // TODO - this only gives public methods it seems. Any way to list all public, private and protected??
        if(($classMethods = get_class_methods(get_class($object))) > 0){

            foreach($classMethods as $classMethod){

                // setup() and BaseStrictClass methods are the only ones that are allowed
                if(!in_array($classMethod, ['setup', '__set', '__get'])){

                    throw new UnexpectedValueException('Only setup method is allowed for DataBaseObjects: '. $classMethods[0]);
                }
            }
        }
    }
}

?>