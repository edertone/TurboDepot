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

use DateTime;
use Throwable;
use ReflectionClass;
use ReflectionObject;
use UnexpectedValueException;
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbodepot\src\main\php\model\DataBaseObject;
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
     * Date time type that can be used to constrain object properties.
     * Accepted size values are 19 for seconds precision and 23 for miliseconds
     */
    const DATETIME = 'DATETIME';


    /**
     * Array type that can be used to constrain object properties
     */
    const ARRAY = 'ARRAY';


    /**
     * TODO
     */
    const MULTI_LANGUAGE = 'MULTI_LANGUAGE';


    /**
     * TODO
     */
    const NOT_NULL = 'NOT_NULL';


    /**
     * To prevent name collisions with any other possible existing database tables, we can define a prefix here that will be
     * added to all the tables that are used by this class. It will be automatically added when tables are created, and expected when
     * tables are read.
     *
     * By default, this class uses the td_ prefix, which is an abbreviature of TurboDepot_
     *
     * @var string
     */
    public $tablesPrefix = 'td_';


    /**
     * TODO - this should be optional and may be specifically modified by each databaseobject if necessary
     * @var string
     */
    public $isUuidEnabled = false;


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
    private $_sqlDateFormat = 'Y-m-d H:i:s.v';


    /**
     * Contains the list of table column names and class types that must exist on all the created objects by default.
     * These represent the DataBaseObject base properties that are common for all the objects.
     *
     * @var array
     */
    private $_baseObjectColumns = ['dbid' => [self::INT, 11], 'uuid' => [self::STRING, 36], 'sortindex' => [self::INT, 11],
        'creationdate' => [self::DATETIME, 23], 'modificationdate' => [self::DATETIME, 23], 'deleted' => [self::DATETIME, 23]];


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

            $tableData = $this->_db->tableExists($tableName) ?
                $this->_updateTablesToFitObject($object, $tableName) :
                $this->_createObjectTables($object, $tableName);

            $tableData['modificationdate'] = (DateTime::createFromFormat('U.u', microtime(true)))->format($this->_sqlDateFormat);

            // Store or update the object into the database
            if($object->dbId === null){

                $tableData['creationdate'] = $tableData['modificationdate'];

                $this->_db->tableAddRows($tableName, [$tableData]);
                $object->dbId = $this->_insertArrayPropsToDb($object, $tableName, $this->_db->getLastInsertId());

            }else{

                $this->_db->tableUpdateRow($tableName, ['dbid' => $object->dbId], $tableData);
                $this->_insertArrayPropsToDb($object, $tableName, $object->dbId, true);
            }

            $object->modificationDate = $tableData['modificationdate'];
            $object->creationDate = $tableData['creationdate'];

            $this->_db->transactionCommit();

            return $object->dbId;

        } catch (Throwable $e) {

            $this->_db->transactionRollback();

            throw $e;
        }

        // TODO - PENDING:
        // TODO - save multilanguage objects
        //      1 decide if sepparate class or the same is used
        //      2 localized properties must have a special type definition
        // TODO - save a property with a complex type
        // TODO - save an array of complex types
        // TODO - save pictures and binary files linked to the object
        // TODO - implement performance tests for massive amounts of data save and list
    }


    /**
     * Auxiliary method to store all the array typed properties values to database
     *
     * @param DataBaseObject $object An instance to save or update
     * @param string $tableName The name for the object table
     * @param int $dbId The object dbId to which the array values will be linked
     * @param boolean $deleteBeforeInsert If set to true, all existing array values will be deleted before inserting
     *
     * @return int The object dbId
     */
    private function _insertArrayPropsToDb(DataBaseObject $object, string $tableName, int $dbId, $deleteBeforeInsert = false){

        foreach ($this->_getArrayTypedProperties($object) as $property) {

            $column = strtolower($property);

            if($deleteBeforeInsert){

                $this->_db->tableDeleteRows($tableName.'_'.$column, ['dbid' => $object->dbId]);
            }

            if(($propertyCount = count($object->{$property})) > 0){

                $rowsToAdd = [];

                for ($i = 0; $i < $propertyCount; $i++) {

                    $rowsToAdd[] = ['dbid' => $dbId, 'value' => $object->{$property}[$i]];
                }

                $this->_db->tableAddRows($tableName.'_'.$column, $rowsToAdd);
            }
        }

        return $dbId;
    }


    /**
     * Obtain the name for the table that would be used store the provided object when saved to database.
     *
     * It is calculated by converting its classname to lower case, which is the most compatible and compact way to store table names
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

        return $this->tablesPrefix.strtolower(StringUtils::getPathElement(get_class($object)));
    }


    /**
     * Obtain an associative array containing column name and values for the table that would be used store the provided object when saved to database.
     *
     * It is calculated by converting all the object properties to lower case, which is the most compatible and compact way to store table column names
     * on different Operating systems (this is cause it does not contain upper case letters which would be problematic in Windows OS for example).
     *
     * Note that properties that contain array values are excluded from this table, cause they will be stored on their own sepparate table
     *
     * @param DataBaseObject $object An instance to calculate its respective table columns and data
     *
     * @return array The generated table as an associative array
     */
    public function convertObjectToTableData(DataBaseObject $object){

        $tableData = [];

        foreach (get_object_vars($object) as $property => $value) {

            // Array typed properties are ignored from the tabledata structure
            if(!is_array($object->{$property})){

                $tableData[strtolower($property)] = $value;
            }
        }

        // Move the base common properties to the begining of the array, so they get correctly sorted at the db table
        $tableData = ['deleted' => $tableData['deleted']] + $tableData;
        $tableData = ['modificationdate' => $tableData['modificationdate']] + $tableData;
        $tableData = ['creationdate' => $tableData['creationdate']] + $tableData;
        $tableData = ['sortindex' => $tableData['sortindex']] + $tableData;
        $tableData = ['uuid' => $tableData['uuid']] + $tableData;
        $tableData = ['dbid' => $tableData['dbid']] + $tableData;

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
        switch (strtolower($property)) {

            case 'dbid':
                return $this->_db->getSQLTypeFromValue(999999999999999, false, true, true);

            case 'uuid':
                return $this->_db->getSQLTypeFromValue('                                    ');

            case 'sortindex':
                return $this->_db->getSQLTypeFromValue(999999999999999, true, true);

            case 'deleted':
                return $this->_db->getSQLDateTimeType(true, true);

            case 'creationdate':
            case 'modificationdate':
                return $this->_db->getSQLDateTimeType(false, true);
        }

        $type = $this->_getTypeFromObjectProperty($object, $property);

        switch ($type[0]) {

            case self::BOOL:
                return $this->_db->getSQLTypeFromValue(true);

            case self::INT:
                return $this->_db->getSQLTypeFromValue(pow(10, $type[1]) - 1);

            case self::DOUBLE:
                return $this->_db->getSQLTypeFromValue(1.0);

            case self::DATETIME:
                return $this->_db->getSQLDateTimeType(true, $type[1] === 23);

            default:
                return $this->_db->getSQLTypeFromValue(str_repeat(' ', $type[1]));
        }
    }


    /**
     * Given the name for an object property, this method will give an array with all the information about its class defined type
     *
     * @param DataBaseObject $object A valid database object instance
     * @param string $property The name of a property for which we want to obtain the class type info
     *
     * @return array An array with the following possible values:<br>
     *         First element: The property type (DataBaseObjectsManager::BOOL, DataBaseObjectsManager::INT, DataBaseObjectsManager::DOUBLE, DataBaseObjectsManager::STRING, DataBaseObjectsManager::DATETIME)<br>
     *         Second element: The type precision size (or digits) when the type is a simple one, or the type for each array element if the type is an array<br>
     *         Next elements may contain any of the following extra flags:<br>
     *         - DataBaseObjectsManager::NOT_NULL If the property does not allow null values
     *         - DataBaseObjectsManager::MULTI_LANGUAGE If the property values can be different depending on the language
     *         - DataBaseObjectsManager::ARRAY If the property is an array of elements, in which case each element will match the same type definition
     */
    private function _getTypeFromObjectProperty(DataBaseObject $object, string $property){

        // Try to find a strongly defined type for the requested column on the provided object instance.
        // This will have preference over the type that is automatically detected from the table value.
        $reflectionObject = new ReflectionObject($object);
        $objectDefinedTypes = $reflectionObject->getProperty('_types');
        $objectDefinedTypes->setAccessible(true);
        $typesSetup = $objectDefinedTypes->getValue($object);

        if(isset($typesSetup[$property])){

            return $this->_validateAndFormatTypeArray($typesSetup[$property], $property);
        }

        // If types definition are mandatory, we will check here that all the object properties have a defined data type
        $colName = strtolower($property);
        $isBaseColumn = in_array($colName, array_keys($this->_baseObjectColumns), true);

        if(count($typesSetup) > 0 && !$isBaseColumn){

            $isTypingMandatory = $reflectionObject->getProperty('_isTypingMandatory');
            $isTypingMandatory->setAccessible(true);
            $isTypingMandatory = $isTypingMandatory->getValue($object);

            if($isTypingMandatory){

                throw new UnexpectedValueException($property.' has no defined type but typing is mandatory. Define a type or disable this restriction by setting _isTypingMandatory = false');
            }
        }

        // Check that all the defined types belong to object properties.
        $objectProperties = array_keys(get_object_vars($object));

        foreach (array_keys($typesSetup) as $propertyType) {

            if(!in_array($propertyType, $objectProperties, true)){

                throw new UnexpectedValueException('Cannot define type for '.$propertyType.' cause it does not exist on class');
            }
        }

        // Check if the requested column is found at the base object columns
        if($isBaseColumn){

            return $this->_baseObjectColumns[$colName];
        }

        try {

            return $this->_getTypeFromValue($object->{$property});

        } catch (Throwable $e) {

            throw new UnexpectedValueException('Could not detect type from property '.$property.': '.$e->getMessage());
        }
    }


    /**
     * Aux method that will process a raw array containing type definitions like [STRING, 20], [10, INT, ARRAY],...
     * It will check that all values are valid to define a property type and return the same array but sorted as this class expects:
     * First element will be the data type (STRING, BOOL, INT, ...)
     * Second element will be an integer wit the data size
     * All the next elements will contain optional flags like ARRAY, NOTNULL, MULTILANGUAGE, etc..
     *
     * @param array $array An unordered array containing type definitions
     * @param string $property The name for the property to which the type defs are applied
     *
     * @throws UnexpectedValueException In case the type definitions are invalid
     *
     * @return array The expected array
     */
    private function _validateAndFormatTypeArray(array $array, $property){

        $result = ['', 1];
        $isArray = false;
        $isNotNull = false;
        $isMultiLanguage = false;

        foreach ($array as $item) {

            switch ($item) {
                case self::BOOL: case self::INT: case self::DOUBLE: case self::STRING: case self::DATETIME:
                    $result[0] = $item;
                    break;

                case self::ARRAY:
                    $isArray = true;
                    break;

                case self::MULTI_LANGUAGE:
                    $isMultiLanguage = true;
                    break;

                default:
                    $result[1] = $item;
            }
        }

        if(!is_int($result[1])){

            throw new UnexpectedValueException($property.' is defined as '.(($isArray) ? 'an array of ' : '').$result[0].' but size is invalid');
        }

        if($result[0] === self::DATETIME && !in_array($result[1], [19, 23], true)){

            throw new UnexpectedValueException($property.' DATETIME size must be 19 or 23');
        }

        if($isArray){

            $result[] = self::ARRAY;
        }

        if($isNotNull){

            $result[] = self::NOT_NULL;
        }

        if($isMultiLanguage){

            $result[] = self::MULTI_LANGUAGE;
        }

        return $result;
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

            $biggestValueIndex = 0;
            $biggestValueSize = 0;

            for ($i = 0, $l = count($value); $i < $l; $i++) {

                // All array elements must be the same type
                if($i < $l - 1 && gettype($value[$i]) !== gettype($value[$i + 1])){

                    throw new UnexpectedValueException('All array elements must be the same type');
                }

                // Calculate the index for the biggest value on the array, so it's type can be correctly detected
                if((is_string($value[$i]) && strlen($value[$i]) > $biggestValueSize) ||
                   ((is_int($value[$i]) || is_double($value[$i])) && $value[$i] > $biggestValueSize)){

                    $biggestValueIndex = $i;
                }
            }

            return array_merge($this->_getTypeFromValue($value[$biggestValueIndex]), [self::ARRAY]);
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

        foreach(array_keys(get_object_vars($object)) as $property){

            try {

                if(in_array(self::ARRAY, $this->_getTypeFromObjectProperty($object, $property), true)){

                    $result[] = $property;
                }

            } catch (Throwable $e) {

                // A property may still be an empty array which has an already created database table to detect its type, so
                // we will check it here
                if(is_array($object->{$property}) && count($object->{$property}) === 0 &&
                   $this->_db->tableExists($this->getTableNameFromObject($object).'_'.strtolower($property))){

                    $result[] = $property;

                }else{

                    throw $e;
                }
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

            $properties[strtolower($property)] = $property;
        }

        $columnsToCreate = [];
        $tableData = $this->convertObjectToTableData($object);

        foreach (array_keys($tableData) as $columnName) {

            $columnsToCreate[] = $columnName.' '.$this->getSQLTypeFromObjectProperty($object, $properties[$columnName]);
        }

        $this->_db->tableCreate($tableName, $columnsToCreate, ['dbid'], [['uuid']], [['sortindex']]);

        // Create all the tables that store array properties
        foreach ($this->_getArrayTypedProperties($object) as $property) {

            $columnName = strtolower($property);

            $this->_db->tableCreate($tableName.'_'.$columnName, [
                'dbid '.$this->_db->getSQLTypeFromValue(999999999999999, false, true),
                'value '.$this->getSQLTypeFromObjectProperty($object, $properties[$columnName])
            ]);

            $this->_db->tableAddForeignKey($tableName.'_'.$columnName, $tableName.'_'.$columnName.'_dbid_fk', ['dbid'], $tableName, ['dbid']);
        }

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

        // Verify that array typed properties can be stored on their respective database tables
        foreach ($this->_getArrayTypedProperties($object) as $property) {

            if(count($object->{$property}) > 0){

                $arrayPropTableName = $tableName.'_'.strtolower($property);
                $tableColumnType = $this->_db->tableGetColumnDataTypes($arrayPropTableName)['value'];

                foreach ($object->{$property} as $value) {

                    $this->_checkColumnFitsType($arrayPropTableName, 'value', $tableColumnType, $this->_db->getSQLTypeFromValue($value));
                }
            }
        }

        $tableData = $this->convertObjectToTableData($object);
        $tableColumnTypes = $this->_db->tableGetColumnDataTypes($tableName);

        // Test that the table and object have the same columns
        if(array_keys($tableData) !== array_keys($tableColumnTypes)){

            if(!$this->isTableAlteredWhenColumnsChange){

                throw new UnexpectedValueException($tableName.' columns ('.implode(',', array_keys($tableColumnTypes)).') are different from its related object');
            }

            // TODO - update the table to contain the same columns as the object data, trying to destroy the less possible data
        }

        // Test that all columns have data types which can store the provided object data
        $baseObjectColumnNames = array_keys($this->_baseObjectColumns);

        foreach ($tableColumnTypes as $tableColumnName => $tableColumnType) {

            // The base object properties are ignored because they are already tested by the _validateDataBaseObject method
            if(!in_array($tableColumnName, $baseObjectColumnNames, true)){

                $this->_checkColumnFitsType($tableName, $tableColumnName, $tableColumnType, $this->_db->getSQLTypeFromValue($tableData[$tableColumnName]));
            }
        }

        return $tableData;
    }


    /**
     * Verify that the specified table column type fits the specified object property value type.
     * When the column does not fit the value type, depending on the class setup, the table will altered to fit the value type or an exception will be
     * thrown
     *
     * @param string $tableName The Name for the table that contains the column to check
     * @param string $tableColumnName The name for the column to check
     * @param string $tableColumnType The sql type for the column to check
     * @param string $valueType The sql type for the value that we want to test against the column
     *
     * @throws UnexpectedValueException If the class setup is restrictive to table changes, when column does not fit the value type,
     *         an exception will be thrown
     */
    private function _checkColumnFitsType(string $tableName, string $tableColumnName, string $tableColumnType, string $valueType){

        $isTableColumnDateType = false;

        if(!$this->_db->isSQLSameType($valueType, $tableColumnType) &&
           !$this->_db->isSQLNumericTypeCompatibleWith($tableColumnType, $valueType) &&
           !($isTableColumnDateType = $this->_db->isSQLDateTimeType($tableColumnType) && $this->_db->isSQLStringType($valueType))){

            if(!$this->isTableAlteredWhenColumnsChange){

                throw new UnexpectedValueException($tableName.' column '.$tableColumnName.' data type expected: '.$tableColumnType.' but received: '.$valueType);
            }

            // TODO - update the table column to accept the same data type as the object expects
        }

        $valueTypeSize = $this->_db->getSQLTypeSize($valueType);
        $tableColumnTypeSize = $this->_db->getSQLTypeSize($tableColumnType);

        if($tableColumnTypeSize < $valueTypeSize &&
           !($isTableColumnDateType && $tableColumnTypeSize === 0 && ($valueTypeSize === 1 || $valueTypeSize === 19)) &&
           !($isTableColumnDateType && $tableColumnTypeSize === 3 && ($valueTypeSize === 1 || $valueTypeSize === 23))){

            if(!$this->isColumnResizedWhenValueisBigger){

                throw new UnexpectedValueException($tableName.' column '.$tableColumnName.' data type expected: '.$tableColumnType.' but received: '.$valueType);
            }

            // Increase the size of the table column so it can fit the object value
            $this->_db->query('ALTER TABLE '.$tableName.' MODIFY COLUMN '.$tableColumnName.' '.$valueType);
        }
    }


    /**
     * Verifies that the specified DataBaseObject instance is correctly defined to be used by this class
     * This method tests the object at the language level: No database checks are performed, only class values and types are checked
     *
     * @param DataBaseObject $object An instance to validate
     *
     * @return string The validation result
     */
    private function _validateDataBaseObject(DataBaseObject $object){

        $class = get_class($object);
        $className = StringUtils::getPathElement($class);
        $dateRegex = '/[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]\.[0-9][0-9][0-9]/';

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

        if($object->dbId === null && ($object->creationDate !== null || $object->modificationDate !== null)){

            throw new UnexpectedValueException('Creation and modification date must be null if dbid is null');
        }

        // Database objects must not have any unexpected method defined, cause they are only data containers
        if(($classMethods = (new ReflectionClass(get_class($object)))->getMethods()) > 0){

            foreach($classMethods as $classMethod){

                // setup() and BaseStrictClass methods are the only ones that are allowed
                if(!in_array($classMethod->name, ['__construct', '__set', '__get'], true)){

                    throw new UnexpectedValueException('Only __construct method is allowed for DataBaseObjects but found: '.$classMethod->name);
                }
            }
        }

        // Verify that all the object properties are valid regarding naming and type
        foreach(array_keys(get_class_vars($class)) as $classProperty){

            // Properties that start with _ are forbidden, cause they are reserved for setup private properties
            if(substr($classProperty, 0, 1) === '_'){

                throw new UnexpectedValueException('Properties starting with _ are forbidden, but found: '.$classProperty);
            }

            // Property type must be valid based on the object defined restrictions and it must fit the expected precision
            if($object->{$classProperty} !== null && $object->{$classProperty} !== []){

                $propertyExpectedType = $this->_getTypeFromObjectProperty($object, $classProperty);
                $propertyValueType = $this->_getTypeFromValue($object->{$classProperty});

                // Check that property type matches expected one (note that double types are able to store int values and datetime types string values)
                if($propertyExpectedType[0] !== $propertyValueType[0] &&
                   !($propertyExpectedType[0] === self::DOUBLE && $propertyValueType[0] === self::INT) &&
                    !($propertyExpectedType[0] === self::DATETIME && $propertyValueType[0] === self::STRING && in_array(strlen($object->{$classProperty}), [0, $propertyExpectedType[1]], true))){

                        throw new UnexpectedValueException('Property '.$classProperty.' ('.print_r($object->{$classProperty}, true).') does not match '.$propertyExpectedType[0].'('.$propertyExpectedType[1].')');
                }

                // The property maximum allowed type size must be respected
                if($propertyValueType[1] > $propertyExpectedType[1]){

                    throw new UnexpectedValueException('Property '.$classProperty.' value size '.$propertyValueType[1].' exceeds '.$propertyExpectedType[1]);
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