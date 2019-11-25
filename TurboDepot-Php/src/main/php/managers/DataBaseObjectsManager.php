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
use ReflectionClass;
use ReflectionObject;
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
     * Boolean type that can be used to constrain object properties
     */
    const BOOL = 'BOOL';


    /**
     * Signed integer type with a max value of 2147483647 that can be used to constrain object properties
     */
    const INT = 'INT';


    /**
     * Signed float type that can be used to constrain object properties
     */
    const DOUBLE = 'DOUBLE';


    /**
     * Text type that can be used to constrain object properties
     */
    const STRING = 'STRING';


    /**
     * Array type that can be used to constrain object properties
     */
    const ARRAY = 'ARRAY';


    /**
     * To prevent name collisions with any other possible existing database tables, we can define a prefix here that will be
     * added to all the tables that are used by this class. It will be automatically added when tables are created, and expected when
     * tables are read.
     *
     * By default, this class uses the tddo_ prefix, which is an abbreviature of TurboDepotDatabaseObjectsmanager_
     *
     * @var string
     */
    public $tablesPrefix = 'tddo_';


    /**
     * This flag specifies what to do when saving an object which table does not exist on database.
     *
     * If set to true (default), the table and all the columns to store the object will be automatically created from the object properties.
     * If set to false, an exception will happen and we will need to manually alter the database by ourselves.
     *
     * @var boolean
     */
    public $isTableCreatedWhenMissing = true;


    /**
     * This flag specifies what to do when saving an object which table exists on database but has different column names, column number or column data types.
     *
     * If set to true, any difference that is found between the structure of the saved object and the related table or tables will be applied, effectively altering the tables.
     * If set to false (default), an exception will happen and we will need to manually alter the database by ourselves.
     *
     * WARNING: Enabling this flag will keep the database tables up to date to the objects structure, but may lead to data loss in production
     * environments, so use carefully
     *
     * @var boolean
     */
    public $isTableAlteredWhenColumnsChange = false;


    /**
     * This flag specifies what to do when saving an object that contains a property with a value that is bigger than how it is defined at the database table.
     *
     * If set to true, any column that has smaller type size defined will be increased to fit the new value.
     * If set to false (default), an exception will happen and we will need to manually alter the database by ourselves.
     *
     * WARNING: Enabling this flag will keep the database tables up to date to the objects structure, but may lead to data loss in production
     * environments, so use carefully
     *
     * @var boolean
     */
    public $isColumnResizedWhenValueisBigger = false;


    /**
     * Specifies if objects will be moved to trash when deleted instead of being permanently destroyed
     * @var string
     */
    public $isTrashEnabled = false;


    /**
     * A database manager instance that is used by this class
     * @var DataBaseManager
     */
    private $_db = null;


    /**
     * Defines the format in which dates are stored to database tables
     * @var string
     */
    private $_sqlDateFormat = 'Y-m-d H:i:s';


    /**
     * Contains the list of table column names and class types that must exist on all the created objects by default.
     * These represent the DataBaseObject base properties that are common for all the objects.
     *
     * TODO - date types are not correctly defined
     *
     * @var array
     */
    private $_baseObjectColumns = ['db_id' => [self::INT, 11], 'uuid' => [self::STRING, 36], 'sort_index' => [self::INT, 11],
        'creation_date' => [self::STRING, 11], 'modification_date' => [self::STRING, 11], 'deleted' => [self::STRING, 11]];


    /**
     * Class that lets us store objects directly to database without having to care about sql queries
     *
     * TODO - more docs
     */
    public function __construct(){

        $this->_db = new DataBaseManager();
    }


    /**
     * @see DataBaseManager::connectMysql
     */
    public function connectMysql($host, $userName, $password, $dataBaseName = null){

        return $this->_db->connectMysql($host, $userName, $password, $dataBaseName);
    }


    /**
     * @see DataBaseManager::connectMariaDb
     */
    public function connectMariaDb($host, $userName, $password, $dataBaseName = null){

        return $this->_db->connectMariaDb($host, $userName, $password, $dataBaseName);
    }


    /**
     * Reference to a fully initialized DatabaseManager instance which will allow us to directly operate with the curent database
     * connection. This is useful in case we want to perform direct or low level database operations.
     *
     * @return DataBaseManager An initialized DataBaseManager that is connected to the same database.
     */
    public function getDataBaseManager(){

        return $this->_db;
    }


    /**
     * Saves an object to database by updating it if already exists (when dbId is not null) or by creating a new one (when dbId is null)
     *
     * @param DataBaseObject $object An instance to save or update
     *
     * @return int The dbId value for the object that's been saved
     */
    public function save(DataBaseObject $object){

        $this->_validateDataBaseObject($object);

        $tableName = $this->getTableNameFromObject($object);

        $this->_db->transactionBegin();

        try {

            if(!$this->_db->tableExists($tableName)){

                $tableData = $this->_createObjectTables($object, $tableName);

            }else{

                $tableData = $this->_updateTablesToFitObject($object, $tableName);
            }

            // Store or update the object into the database
            $tableData['modification_date'] = date($this->_sqlDateFormat);

            if($object->dbId === null){

                $tableData['creation_date'] = date($this->_sqlDateFormat);

                $this->_db->tableAddRows($tableName, [$tableData]);

                $object->dbId = $this->_db->getLastInsertId();

                // Insert all the values for the array typed properties
                foreach ($this->_getArrayTypedProperties($object) as $property) {

                    if(($propertyCount = count($object->{$property})) > 0){

                        $rowsToAdd = [];
                        $column = StringUtils::formatCase($property, StringUtils::FORMAT_LOWER_SNAKE_CASE);

                        for ($i = 0; $i < $propertyCount; $i++) {

                            $rowsToAdd[] = ['db_id' => $object->dbId, 'value' => $object->{$property}[$i]];
                        }

                        $this->_db->tableAddRows($tableName.'_'.$column, $rowsToAdd);
                    }
                }

            }else{

                $this->_db->tableUpdateRow($tableName, 'db_id', $object->dbId, $tableData);

                // Update all the values for the array typed properties
                // TODO
            }

            $object->modificationDate = $tableData['modification_date'];
            $object->creationDate = $tableData['creation_date'];

            $this->_db->transactionCommit();

            return $object->dbId;

        } catch (Throwable $e) {

            $this->_db->transactionRollback();

            throw $e;
        }
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
     * Note that properties that contain array values are excluded from this table, cause they will be stored on their own sepparate table
     *
     * @param DataBaseObject $object An instance to calculate its respective table columns and data
     *
     * @return array The generated table as an associative array
     */
    public function getTableDataFromObject(DataBaseObject $object){

        $tableData = [];

        foreach (get_object_vars($object) as $property => $value) {

            // Array typed properties are ignored from the tabledata structure
            if(!is_array($object->{$property})){

                $tableData[StringUtils::formatCase($property, StringUtils::FORMAT_LOWER_SNAKE_CASE)] = $value;
            }
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
     * Obtain the SQL type which can be used to store the requested object property with the smallest possible precision.
     *
     * @param DataBaseObject $object An object instance that we want to inspect
     * @param string $property The name for the object property for which we want to obtain the required SQL type
     *
     * @return string The name of the SQL type and precision that can be used to store the object property to database, like SMALLINT, VARCHAR(20), DOUBLE NOT NULL, etc..
     */
    public function getSQLTypeFromObjectProperty(DataBaseObject $object, $property){

        if(!array_key_exists($property, get_object_vars($object))){

            throw new UnexpectedValueException('Undefined property: '.$property);
        }

        // Check if the requested property is one of the base properties which types are already known
        switch (StringUtils::formatCase($property, StringUtils::FORMAT_LOWER_SNAKE_CASE)) {

            case 'db_id':
                return $this->_db->getSQLTypeFromValue(999999999999999, false, true, true);

            case 'uuid':
                return $this->_db->getSQLTypeFromValue('                                    ');

            case 'sort_index':
                return $this->_db->getSQLTypeFromValue(999999999999999, true, true);

            // TODO - SQL date types are hardcoded here
            case 'deleted':
                return 'datetime';

            case 'creation_date':
            case 'modification_date':
                return 'datetime NOT NULL';
        }

        $type = $this->_getTypeFromObjectProperty($object, $property);

        // Array type first element is the array definition itself, so it must be removed
        if($type[0] === self::ARRAY){

            array_shift($type);
        }

        switch ($type[0]) {

            case self::BOOL:
                return $this->_db->getSQLTypeFromValue(true);

            case self::INT:
                return $this->_db->getSQLTypeFromValue(pow(10, $type[1]) - 1);

            case self::DOUBLE:
                return $this->_db->getSQLTypeFromValue(1.0);

            case self::STRING:
            default:
                return $this->_db->getSQLTypeFromValue(str_repeat(' ', $type[1]));
        }
    }


    /**
     * Given the name for an object property, this method will give its class type
     *
     * @param DataBaseObject $object A valid database object instance
     * @param string $property The name of a property for which we want to obtain the type
     *
     * @return array An array with 2 or 3 possible values:<br>
     *         First will be the type (DataBaseObjectsManager::BOOL, ::INT, ::DOUBLE, ::STRING, ::ARRAY)<br>
     *         Second one will be the type size (digits) when the type is a simple one, or the type for each array element if the type is an array
     *         Third one will be the type size if we are declaring an array
     */
    private function _getTypeFromObjectProperty(DataBaseObject $object, string $property){

        // Try to find a strongly defined type for the requested column on the provided object instance.
        // This will have preference over the type that is automatically detected from the table value.
        $objectProperties = array_keys(get_object_vars($object));
        $objectDefinedTypes = (new ReflectionObject($object))->getProperty('_types');
        $objectDefinedTypes->setAccessible(true);
        $typesSetup = $objectDefinedTypes->getValue($object);

        foreach ($typesSetup as $typeProperty => $propertyTypeDefs) {

            if(!in_array($typeProperty, $objectProperties)){

                throw new UnexpectedValueException('Cannot define type for '.$typeProperty.' cause it does not exist on class');
            }

            if($typeProperty === $property){

                if($propertyTypeDefs[0] === self::ARRAY){

                    return [$propertyTypeDefs[0], $propertyTypeDefs[1], isset($propertyTypeDefs[2]) ? $propertyTypeDefs[2] : 1];
                }

                return [$propertyTypeDefs[0], isset($propertyTypeDefs[1]) ? $propertyTypeDefs[1] : 1];
            }
        }

        $colName = StringUtils::formatCase($property, StringUtils::FORMAT_LOWER_SNAKE_CASE);

        if(in_array($colName, array_keys($this->_baseObjectColumns))){

            return $this->_baseObjectColumns[$colName];
        }

        try {

            return $this->_getTypeFromValue($object->{$property});

        } catch (Throwable $e) {

            throw new UnexpectedValueException('Could not detect type from property '.$property.': '.$e->getMessage());
        }

    }


    /**
     * Given a raw value, this method will give its class type
     *
     * @param mixed $value Any value for which we want to obtain the type
     *
     * @return array same as $this->_getTypeFromObjectProperty
     */
    private function _getTypeFromValue($value){

        if(is_bool($value)){

            return [self::BOOL, 1];
        }

        if(is_int($value)){

            return [self::INT, strlen((string)abs($value))];
        }

        if(is_double($value)){

            return [self::DOUBLE,strlen((string)abs($value))];
        }

        if(is_string($value)){

            return [self::STRING, strlen($value)];
        }

        if(is_array($value) && count($value) > 0){

            return array_merge([self::ARRAY], $this->_getTypeFromValue($value[0]));
        }

        throw new UnexpectedValueException('Could not detect type from '.gettype($value));
    }


    /**
     * Obtain a list with all the properties that are typed as arrays for the provided object
     *
     * @param DataBaseObject $object A valid database object instance
     *
     * @return string[] An array with all the property names for those that store array values
     */
    private function _getArrayTypedProperties(DataBaseObject $object){

        $result = [];

        foreach(array_keys(get_object_vars($object)) as $classProperty){

            if($this->_getTypeFromObjectProperty($object, $classProperty)[0] === self::ARRAY){

                $result[] = $classProperty;
            }
        }

        return $result;
    }


    /**
     * Aux method to create the table that represents the provided object on database
     *
     * @param DataBaseObject $object A valid database object instance
     * @param string $tableName The name for the table that represents the database object
     *
     * @return array The object database table structure and values
     */
    private function _createObjectTables(DataBaseObject $object, string $tableName){

        // Obtain the relation between column names and object properties
        $properties = [];

        foreach (array_keys(get_object_vars($object)) as $property) {

            $columnName = StringUtils::formatCase($property, StringUtils::FORMAT_LOWER_SNAKE_CASE);

            $properties[$columnName] = $property;

            // Create all the tables that store array properties
            if($this->_getTypeFromObjectProperty($object, $property)[0] === self::ARRAY){

                $this->_db->tableCreate($tableName.'_'.$columnName, [
                    'db_id '.$this->_db->getSQLTypeFromValue(999999999999999, false, true),
                    'value '.$this->getSQLTypeFromObjectProperty($object, $properties[$columnName])
                ]);
            }
        }

        $columnsToCreate = [];
        $tableData = $this->getTableDataFromObject($object);

        foreach (array_keys($tableData) as $columnName) {

            $columnsToCreate[] = $columnName.' '.$this->getSQLTypeFromObjectProperty($object, $properties[$columnName]);
        }

        $this->_db->tableCreate($tableName, $columnsToCreate, ['db_id'], [['uuid']], [['sort_index']]);

        return $tableData;
    }


    /**
     * This method will check that the table columns and types are valid to store the provided object information,
     * and depending on the configuration values it will update the table structure so the object can be correctly
     * saved. The following checks are performed:
     *
     * 1. Test that the table has the same columns as the object data. If not, exception will be thrown unless $this->isTableAlteredWhenColumnsChange is true
     * 2. Test that all the table columns have data types which can store the same object table column values. If not, exception will be thrown unless $this->isTableAlteredWhenColumnsChange is true
     *
     * @param DataBaseObject $object A valid database object instance
     * @param string $tableName The name for the table we want to inspect and alter if is allowed by current setup
     *
     * @throws UnexpectedValueException If the current setup forbids us to modify the table so it can fit the object data
     *
     * @return array The object database table structure and values
     */
    private function _updateTablesToFitObject(DataBaseObject $object, string $tableName){

        $tableData = $this->getTableDataFromObject($object);
        $tableColumnTypes = $this->_db->tableGetColumnDataTypes($tableName);

        // Test that the table and object have the same columns
        if(!ArrayUtils::isEqualTo(array_keys($tableData), array_keys($tableColumnTypes))){

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
            $objectColumnType = $this->_db->getSQLTypeFromValue($tableData[$tableColumnName]);

            if($objectColumnType !== $tableColumnType){

                // Remove the (N) part if exists from the object and table column data types
                $objectColTypeExploded = explode('(', $objectColumnType);
                $tableColTypeExploded = explode('(', $tableColumnType);

                // Compare the object and table data types without the (N) part. They must be exactly the same or at least the table type must be able to save the
                // object value type
                if($objectColTypeExploded[0] !== $tableColTypeExploded[0] &&
                    !($this->_db->isSQLDoubleType($tableColTypeExploded[0]) && $this->_db->isSQLNumericType($objectColTypeExploded[0]))){

                    if($this->isTableAlteredWhenColumnsChange){

                        // TODO - update the table column to accept the same data type as the object expects

                    }else{

                        throw new UnexpectedValueException($tableName.' column '.$tableColumnName.' data type expected: '.$tableColumnType.' but received: '.$objectColumnType);
                    }
                }

                // Extract the N value from the datatype(N) on each table and object column data types. If the column N value is smaller than the object one,
                // the table cannot store the object value without truncating.
                if(isset($tableColTypeExploded[1]) && isset($objectColTypeExploded[1]) &&
                   (int)substr($tableColTypeExploded[1], 0, -1) < (int)substr($objectColTypeExploded[1], 0, -1)){

                    if($this->isColumnResizedWhenValueisBigger){

                        // Increase the size of the table column so it can fit the object value
                        $this->_db->query('ALTER TABLE '.$tableName.' MODIFY COLUMN '.$tableColumnName.' '.$objectColumnType);

                    }else{

                        throw new UnexpectedValueException($tableName.' column '.$tableColumnName.' data type expected: '.$tableColumnType.' but received: '.$objectColumnType);
                    }
                }
            }
        }

        return $tableData;
    }


    /**
     * Verifies that the specified DataBaseObject instance is correctly defined to be used by this class
     *
     * @param DataBaseObject $object An instance to validate
     *
     * @return string The validation result
     */
    private function _validateDataBaseObject(DataBaseObject $object){

        $class = get_class($object);
        $className = StringUtils::getPathElement($class);
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

        foreach(array_keys(get_class_vars($class)) as $classProperty){

            // Properties that start with _ are forbidden, cause they are reserved for setup private properties
            if(substr($classProperty, 0, 1) === '_'){

                throw new UnexpectedValueException('Properties starting with _ are forbidden, but found: '.$classProperty);
            }

            // Properties that have a type definition must have values of the same type
            if($object->{$classProperty} !== null && $object->{$classProperty} !== []){

                $propertyType = $this->_getTypeFromObjectProperty($object, $classProperty);
                $valueType = $this->_getTypeFromValue($object->{$classProperty});

                if($propertyType[0] === self::ARRAY){

                    array_shift($propertyType);
                }

                if($valueType[0] === self::ARRAY){

                    array_shift($valueType);
                }

                // The only exception is that a property of type double can store a value of type int
                if($propertyType[0] !== $valueType[0] &&
                    !($propertyType[0] === self::DOUBLE && $valueType[0] === self::INT)){

                        throw new UnexpectedValueException('Property '.$classProperty.' ('.print_r($object->{$classProperty}, true).') does not match required type: '.$propertyType[0]);
                }
            }
        }

        // Database objects must not have any unexpected method defined, cause they are only data containers
        if(($classMethods = (new ReflectionClass(get_class($object)))->getMethods()) > 0){

            foreach($classMethods as $classMethod){

                // setup() and BaseStrictClass methods are the only ones that are allowed
                if(!in_array($classMethod->name, ['__construct', '__set', '__get'])){

                    throw new UnexpectedValueException('Only __construct method is allowed for DataBaseObjects but found: '.$classMethod->name);
                }
            }
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

        return $this->_db->disconnect();
    }
}

?>