<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */


namespace org\turbodepot\src\main\php\managers;

use DateTime;
use DateTimeZone;
use Exception;
use ReflectionClass;
use ReflectionObject;
use Throwable;
use UnexpectedValueException;
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbocommons\src\main\php\model\DateTimeObject;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbocommons\src\main\php\utils\NumericUtils;


/**
 * DataBaseObjectsManager class
 */
class DataBaseObjectsManager extends BaseStrictClass{


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
     * TODO
     * @var string
     */
    public $isDbUUIDEnabled = false;
    // TODO - implement - this should be optional and implementation must be studied with care


    /**
     * This flag specifies what to do when saving an object that does not have one or more properties which do exist on the equivalent db table.
     *
     * WARNING / DANGER: Setting this flag to true may lead to permanent data loss if not used with care. So it is better to manually delete all the obsolete properties from database
     * instead of letting this class perform it automatically.
     *
     * If set to true (DANGER!), the database table that represents the object being saved will be modified by removing the column or property tables which do not exist on the object.
     * If set to false (default and RECOMMENDED), an exception will happen when there's a missing property on the object and we will need to manually alter the database by ourselves.
     *
     * @var boolean
     */
    public $isColumnDeletedWhenMissingOnObject = false;


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
     * Defines the format in which dates are stored to database tables (using microseconds)
     * @var string
     */
    private $_sqlDateFormat = 'Y-m-d H:i:s.u';


    /**
     * Contains the list of object base properties that must exist on all the created objects by default.
     *
     * @var array
     */
    private $_baseObjectProperties = ['dbId', 'dbUUID', 'dbCreationDate', 'dbModificationDate', 'dbDeleted'];


    /**
     * Stores the sql data type that can be used anywhere on this class to reference a big unsigned integer SQL type on object tables.
     * It is stored globally to improve performance instead of calculating it every time.
     * @var string
     */
    private $_unsignedBigIntSqlTypeDef = '';


    /**
     * To improve overall performance, this array stores a global cache for the calculated database table names for all received object class names
     *
     * @var array
     */
    private static $_objectTableNamesCache = [];



    /**
     * To improve overall performance, this array stores a global cache for the list of properties that object instances have for each class
     *
     * @var array
     */
    private static $_objectPropertiesCache = [];



    /**
     * Class that lets us store objects directly to database without having to care about sql queries
     */
    public function __construct(){

        $this->_db = new DataBaseManager();
    }


    /**
     * @see DataBaseManager::connectMysql
     */
    public function connectMysql($host, $userName, $password, $dataBaseName = null){

        return $this->connectMariaDb($host, $userName, $password, $dataBaseName);
    }


    /**
     * @see DataBaseManager::connectMariaDb
     */
    public function connectMariaDb($host, $userName, $password, $dataBaseName = null){

        $connection = $this->_db->connectMariaDb($host, $userName, $password, $dataBaseName);

        $this->_unsignedBigIntSqlTypeDef = $this->_db->getSQLTypeFromValue(999999999999999, false, true);

        return $connection;
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
     * TODO
     *
     * @param DataBaseObject $objectInstance
     */
    public function serializeObjectToJSON(DataBaseObject $objectInstance){

        // TODO
    }


    /**
     * Converts a JSON string (which must be valid) into a DataBaseObject instance.
     *
     * @param string $jsonString The JSON string to deserialize.
     * @param DataBaseObject $objectInstance The object instance to populate with deserialized data.
     *
     * @throws UnexpectedValueException If the JSON string is empty, doesn't contain required properties or is invalid
     *
     * @return DataBaseObject The object instance populated with all the values on the json string
     */
    public function serializeObjectFromJSON(string $jsonString, DataBaseObject $objectInstance){

        if(StringUtils::isEmpty($jsonString)){

            throw new UnexpectedValueException('Provided json does not contain a valid DataBaseObject');
        }

        $jsonObject = json_decode($jsonString, false);

        // Fill the private base properties if exist
        if(isset($jsonObject->dbId)){

            $this->_setPrivatePropertyValue($objectInstance, 'dbId', $jsonObject->dbId);
            $this->_setPrivatePropertyValue($objectInstance, 'dbCreationDate', $jsonObject->dbCreationDate);
            $this->_setPrivatePropertyValue($objectInstance, 'dbModificationDate', $jsonObject->dbModificationDate);
        }

        foreach ($this->getBasicProperties($objectInstance, false) as $property) {

            if(!isset($jsonObject->{$property})){

                throw new UnexpectedValueException("Json does not contain required object property: $property");
            }

            $objectInstance->{$property} = $jsonObject->{$property};
        }

        // TODO - serialize complex properties like array typed, multi language, etc..

        $this->validateObject($objectInstance);

        return $objectInstance;
    }


    /**
     * Saves one or more objects to database by updating them if already exist (that means dbId of an object is not null) or by creating new
     * objects on db (when object dbId is null)
     *
     * IMPORTANT: This method is fully transactional. That means if the object or any of the objects being passed to this method fail when being saved,
     * none of them will be saved or updated. In other words, all passed objects on a single save() call must be successfully saved or updated, on none will.
     *
     * @param DataBaseObject|array $objects A single DataBaseObject instance to save or update, or an array with a list of DataBaseObject instances Token
     *        save or upate.
     *
     * @throws Exception If there was any problem trying to save the object or objects.
     *
     * @return int|array If a single DataBaseObject is passed, an int containing the dbId value for the object that's been saved. If an array of
     *         DataBaseObjects are passed, this method will return an array with the dbId for each one of the passed objects, in the same order.
     */
    public function save($objects){

        // TODO - PENDING:
        // TODO - Create a constant PARENT_FOREIGN_OBJECTS to check that an object with the value on the linked property exists stored on database or throw an error
        // TODO - save multilanguage properties
        // TODO - save a property with a complex type
        // TODO - save an array of complex types
        // TODO - save pictures and binary files linked to the object
        // TODO - implement performance tests for massive amounts of data save and list
        // TODO - verify that all unit test methods are sorted in the same order as this class methods

        // Compute as much values as possible outside of the for loop
        $resultsDbId = [];
        $resultsCreationDate = [];
        $resultsModificationDate = [];
        $objectsToSave = is_array($objects) ? $objects : [$objects];
        $objectsToSaveCount = count($objectsToSave);
        $nowDateTime = (new DateTime("now", new DateTimeZone('UTC')))->format($this->_sqlDateFormat);

        try {

            $this->_db->transactionBegin();

            for ($i = 0, $object = $objectsToSave[$i]; $i < $objectsToSaveCount; $i++) {

                $this->validateObject($object);

                $tableName = $this->getTableNameFromObject($object);
                $tableData = $this->_updateTablesToFitObject($object, $tableName);
                $resultsModificationDate[$i] = $tableData['dbmodificationdate'] = $nowDateTime;

                // Create or update the object into the database
                if($tableData['dbid'] === null){

                    $resultsCreationDate[$i] = $tableData['dbcreationdate'] = $tableData['dbmodificationdate'];

                    $this->_db->tableAddRows($tableName, [$tableData]);
                    $tableData['dbid'] = $this->_insertArrayPropsToDb($object, $tableName, $this->_db->getLastInsertId());
                    $this->_insertMultiLanguagePropsToDb($object, $tableName, $tableData['dbid']);

                }else{

                    // We are updating an existing object, so the creation date field will be ignored in the update query
                    unset($tableData['dbcreationdate']);

                    $this->_db->tableUpdateRow($tableName, ['dbid' => $tableData['dbid']], $tableData);
                    $this->_insertArrayPropsToDb($object, $tableName, $tableData['dbid'], true);
                    $this->_insertMultiLanguagePropsToDb($object, $tableName, $tableData['dbid'], true);
                }

                $resultsDbId[$i] = $tableData['dbid'];
            }

            $this->_db->transactionCommit();

        } catch (Throwable $e) {

            $this->_db->transactionRollback();

            throw $e;
        }

        // After transaction is ok, update all the objects with the values that have changed
        for ($i = 0; $i < $objectsToSaveCount; $i++) {

            $this->_setPrivatePropertyValue($objectsToSave[$i], 'dbId', $resultsDbId[$i]);

            if($objectsToSave[$i]->getDbCreationDate() === null){

                // Apply a UTC timezone format to the date so we can set it to the php object again
                $this->_setPrivatePropertyValue($objectsToSave[$i], 'dbCreationDate', $resultsCreationDate[$i].'+00:00');
            }

            // Apply a UTC timezone format to the date so we can set it to the php object again
            $this->_setPrivatePropertyValue($objectsToSave[$i], 'dbModificationDate', $resultsModificationDate[$i].'+00:00');
        }

        return is_array($objects) ? $resultsDbId : $resultsDbId[0];
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

        foreach ($this->getArrayTypedProperties($object) as $property) {

            $column = strtolower($property);

            if($deleteBeforeInsert){

                $this->_db->tableDeleteRows($tableName.'_'.$column, ['dbid' => $object->getDbId()]);
            }

            if(($propertyCount = count($object->{$property})) > 0){

                $rowsToAdd = [];

                for ($i = 0; $i < $propertyCount; $i++) {

                    $rowsToAdd[] = ['dbid' => $dbId, 'arrayindex' => $i, 'value' => $object->{$property}[$i]];
                }

                $this->_db->tableAddRows($tableName.'_'.$column, $rowsToAdd);
            }
        }

        return $dbId;
    }


    /**
     * Auxiliary method to store all the multi language typed properties values to database
     *
     * @param DataBaseObject $object An instance to save or update
     * @param string $tableName The name for the object table
     * @param int $dbId The object dbId to which the array values will be linked
     * @param boolean $deleteBeforeInsert TODO
     *
     * @return int The object dbId
     */
    private function _insertMultiLanguagePropsToDb(DataBaseObject $object, string $tableName, int $dbId, $deleteBeforeInsert = false){

        // TODO - Regarding $deleteBeforeInsert: multilan properties must be updated instead of deleted and inserted !??!?!?!

        foreach ($this->getMultiLanguageTypedProperties($object) as $property) {

            $rowToAdd = [];
            $propertyTable = $tableName.'_'.strtolower($property);

            if($deleteBeforeInsert){

                $this->_db->tableDeleteRows($propertyTable, ['dbid' => $object->getDbId()]);
            }

            foreach ($this->_getMultiLanguagePropertyAllLocalesList($object, $propertyTable) as $locale) {

                $rowToAdd[$locale] = $this->_getMultiLanguagePropertyValue($object, $property, $locale);
            }

            // All object properties that are defined by user as datetime must be converted to a valid mysql date format before
            // being stored, otherwise an exception will happen
            if(isset($object->{$property}) && $this->_isDateTypeProperty($object, $property)){

                foreach ($rowToAdd as $key => $value) {

                    $rowToAdd[$key] = (new DateTime($value, new DateTimeZone('UTC')))
                        ->format($this->_sqlDateFormat);
                }
            }

            $rowToAdd['dbid'] = $dbId;

            $this->_db->tableAddRows($propertyTable, [$rowToAdd]);
        }

        return $dbId;
    }


    /**
     * Assign the provided value to a database object private property. It uses reflection to access the object private variables and allows
     * us to modify properties that are not normally available to the regular users.
     *
     * @param DataBaseObject $object The instance that we want to modify
     * @param string $property The name of the protected or private property that we want to modify
     * @param string $value The value that we want to set to the private or protected property
     */
    private function _setPrivatePropertyValue(DataBaseObject $object, string $property, $value){

        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
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

        return $this->_getTableNameFromClass(get_class($object));
    }


    /**
     * Aux method to obtain the table name from a given class
     *
     * @param string $class The full path to the class we want to conver to table name
     *
     * @return string
     */
    private function _getTableNameFromClass(string $class){

        // Implement a static cache to globally reuse table names for object classes to improve performance
        if(!isset(self::$_objectTableNamesCache[$class])){

            self::$_objectTableNamesCache[$class] = strtolower(StringUtils::getPathElement($class));
        }

        return $this->tablesPrefix.self::$_objectTableNamesCache[$class];
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
    private function _convertObjectToTableData(DataBaseObject $object){

        $tableData = [];
        $dateTimeZone = new DateTimeZone('UTC');

        foreach ($this->getBasicProperties($object, true) as $property) {

            $columnName = strtolower($property);

            $tableData[$columnName] = $this->_getPropertyValue($object, $property);

            // All object properties that are defined by user as datetime must be converted to a valid mysql date format before being stored
            // otherwise an exception will happen
            if(isset($object->{$property}) && $this->_isDateTypeProperty($object, $property)){

                $tableData[$columnName] = (new DateTime($tableData[$columnName], $dateTimeZone))->format($this->_sqlDateFormat);
            }

            if(is_array($tableData[$columnName])){

                throw new UnexpectedValueException('unexpected array value for property: '.$property);
            }
        }

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
    public function getSQLTypeFromObjectProperty(DataBaseObject $object, string $property){

        // Check if the requested property is one of the base properties which types are already known
        switch (strtolower($property)) {

            case 'dbid':
                return $this->_db->getSQLTypeFromValue(999999999999999, false, true, true);

            case 'dbuuid':
                return $this->_db->getSQLTypeFromValue('                                    ');

            case 'dbdeleted':
                return $this->_db->getSQLDateTimeType(true, 6);

            case 'dbcreationdate':
            case 'dbmodificationdate':
                return $this->_db->getSQLDateTimeType(false, 6);
        }

        $type = $this->_getTypeFromObjectProperty($object, $property);

        $isNullable = !in_array(DataBaseObject::NOT_NULL, $type, true);

        switch ($type[0]) {

            case DataBaseObject::BOOL:
                return $this->_db->getSQLTypeFromValue(true, $isNullable);

            case DataBaseObject::INT:
                return $this->_db->getSQLTypeFromValue(pow(10, $type[1]) - 1, $isNullable);

            case DataBaseObject::DOUBLE:
                return $this->_db->getSQLTypeFromValue(1.0, $isNullable);

            case DataBaseObject::DATETIME:
                return $this->_db->getSQLDateTimeType($isNullable, $type[1]);

            default:
                return $this->_db->getSQLTypeFromValue(str_repeat(' ', $type[1]), $isNullable);
        }
    }


    /**
     * Given the name for an object property, this method will give an array with all the information about its class defined type
     *
     * @param DataBaseObject $object A valid database object instance
     * @param string $property The name of a property for which we want to obtain the class type info
     *
     * @return array An array with the following possible values:<br>
     *         First element: The property type (DataBaseObject::BOOL, DataBaseObject::INT, DataBaseObject::DOUBLE, DataBaseObject::STRING, DataBaseObject::DATETIME)<br>
     *         Second element: The type precision size (or digits) when the type is a simple one, or the type for each array element if the type is an array<br>
     *         Next elements may contain any of the following extra flags:<br>
     *         - DataBaseObject::NOT_NULL If the property does not allow null values
     *         - DataBaseObject::MULTI_LANGUAGE If the property values can be different depending on the language
     *         - DataBaseObject::ARRAY If the property is an array of elements, in which case each element will match the same type definition
     */
    private function _getTypeFromObjectProperty(DataBaseObject $object, string $property){

        // Try to find a strongly defined type for the requested column on the provided object instance.
        // This will have preference over the type that is automatically detected from the table value.
        $typesSetup = get_class($object)::TYPES;

        if(isset($typesSetup[$property])){

            return $this->_validateAndFormatTypeArray($typesSetup[$property], $property);
        }

        // If types definition are mandatory, we will check here that all the object properties have a defined data type
        if(!empty($typesSetup) && get_class($object)::IS_TYPING_MANDATORY){

            throw new UnexpectedValueException($property.' has no defined type but typing is mandatory. Define a type or disable this restriction by setting IS_TYPING_MANDATORY = false');
        }

        try {

            return $this->_getTypeFromValue($object->{$property});

        } catch (Throwable $e) {

            throw new UnexpectedValueException('Could not detect property '.$property.' type: '.$e->getMessage());
        }
    }


    /**
     * Given a property value obtained from database which is always retrieved as a string, this method will
     * cast it again to the correct language type ready to be stored on the specified object property.
     *
     * @param mixed $value The property value we want to convert to the correct data type
     * @param DataBaseObject $object The object that contains the property which type we want to cast
     * @param string $property The object property we wan to obtain its type from
     *
     * @return boolean|number|string The received value casted to the correct data type
     */
    private function _castDbValueToPropertyType($value, DataBaseObject $object, string $property){

        $objectType = $this->_getTypeFromObjectProperty($object, $property);

        switch ($objectType[0]) {

            case DataBaseObject::BOOL:
                return (boolean)$value;

            case DataBaseObject::INT:
                return (int)$value;

            case DataBaseObject::DOUBLE:
                return (float)$value;

            case DataBaseObject::DATETIME:
                return $value === null ? null : $value.'+00:00';

            default:
                return $value;
        }
    }


    /**
     * Aux method to evaluate if the property of an object is typed as date.
     *
     * @param DataBaseObject $object The object to test
     * @param string $property The property to test on the object
     *
     * @return boolean true if the property is typed as date, false otherwise. This works with all basic and non basic
     *         properties like multi language
     */
    private function _isDateTypeProperty(DataBaseObject $object, string $property){

        return $this->_getTypeFromObjectProperty($object, $property)[0] === DataBaseObject::DATETIME;
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

        $result = ['', null];
        $isArray = false;
        $isNotNull = false;
        $isNoDuplicates = false;
        $isMultiLanguage = false;

        foreach(array_count_values($array) as $item => $itemRepeatCount){

            // Duplicate values are not allowed on the properties TYPES setup
            if($itemRepeatCount > 1){

                throw new UnexpectedValueException('Duplicate value <'.$item.'> found on ::TYPES for '.$property.' property');
            }

            switch ((string)$item) {
                case DataBaseObject::BOOL: case DataBaseObject::INT: case DataBaseObject::DOUBLE: case DataBaseObject::STRING: case DataBaseObject::DATETIME:
                    $result[0] = $item;
                    break;

                case DataBaseObject::ARRAY:
                    $isArray = true;
                    break;

                case DataBaseObject::NOT_NULL:
                    $isNotNull = true;
                    break;

                case DataBaseObject::NO_DUPLICATES:
                    $isNoDuplicates = true;
                    break;

                case DataBaseObject::MULTI_LANGUAGE:

                    if(in_array(DataBaseObject::ARRAY, $array, true)){

                        throw new UnexpectedValueException('ARRAY type is not supported by multi language properties: '.$property);
                    }

                    $isMultiLanguage = true;
                    break;

                default:

                    if(!is_int($item)){

                        throw new UnexpectedValueException($property.' is defined as '.(($isArray) ? 'an array of ' : '').$result[0].' but size is invalid');
                    }

                    $result[1] = $item;
            }
        }

        if($result[1] === null){

            if($result[0] === DataBaseObject::BOOL){

                $result[1] = 1;

            }else{

                throw new UnexpectedValueException($property.' size is not specified');
            }
        }

        if($result[0] === DataBaseObject::DATETIME && $result[1] !== 0  && $result[1] !== 3  && $result[1] !== 6){

            throw new UnexpectedValueException($property.' DATETIME size must be 0, 3 or 6');
        }

        if($isArray){

            if(!in_array($result[0], [DataBaseObject::BOOL, DataBaseObject::INT, DataBaseObject::DOUBLE, DataBaseObject::STRING, DataBaseObject::DATETIME], true)){

                throw new UnexpectedValueException($property.' defined as ARRAY but no type for the array elements is specified');
            }

            $result[] = DataBaseObject::ARRAY;
        }

        if($isNotNull){

            $result[] = DataBaseObject::NOT_NULL;
        }

        if($isNoDuplicates){

            $result[] = DataBaseObject::NO_DUPLICATES;
        }

        if($isMultiLanguage){

            $result[] = DataBaseObject::MULTI_LANGUAGE;
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

            return [DataBaseObject::BOOL, 1, DataBaseObject::NOT_NULL];
        }

        if(is_int($value)){

            return [DataBaseObject::INT, strlen((string)abs($value)), DataBaseObject::NOT_NULL];
        }

        if(is_double($value)){

            return [DataBaseObject::DOUBLE,strlen((string)abs($value)), DataBaseObject::NOT_NULL];
        }

        if(is_string($value)){

            return [DataBaseObject::STRING, strlen($value), DataBaseObject::NOT_NULL];
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
                if((is_string($value[$i]) && ($valueSize = strlen($value[$i])) > $biggestValueSize) ||
                   ((is_int($value[$i]) || is_double($value[$i])) && ($valueSize = $value[$i]) > $biggestValueSize)){

                    $biggestValueIndex = $i;
                    $biggestValueSize = $valueSize;
                }
            }

            return array_merge($this->_getTypeFromValue($value[$biggestValueIndex]), [DataBaseObject::ARRAY, DataBaseObject::NOT_NULL]);
        }

        throw new UnexpectedValueException('Could not detect type from '.gettype($value));
    }


    /**
     * Obtain a list with all the properties that are typed as basic types for the provided object.
     * All the properties that require a specific db table to be stored like arrays, multilanguage and so will be excluded from this list.
     *
     * @param DataBaseObject $object A valid database object instance
     * @param bool $includeBaseProperties If true all the private internal object properties will also be listed. If false, only
     *             the custom public properties will be in the list
     *
     * @return string[] An array with all the basic property names, sorted as they must be on the object database table.
     */
    public function getBasicProperties(DataBaseObject $object, bool $includeBaseProperties = false){

        $basicProperties = [];
        $class = get_class($object);
        $excludedProps = array_merge($this->getArrayTypedProperties($object), $this->getMultiLanguageTypedProperties($object));

        if(!isset(self::$_objectPropertiesCache[$class])){

            self::$_objectPropertiesCache[$class] = array_keys(get_object_vars($object));
        }

        foreach (self::$_objectPropertiesCache[$class] as $property) {

            if(!in_array($property, $excludedProps, true)){

                $basicProperties[] = $property;
            }
        }

        return $includeBaseProperties ? array_unique(array_merge($this->_baseObjectProperties, $basicProperties)) : $basicProperties;
    }


    /**
     * Obtain a list with all the properties that are typed as arrays for the provided object
     * Notice that these properties need an extra database table to be stored as are not basic types.
     *
     * @param DataBaseObject $object A valid database object instance
     *
     * @return string[] An array with all the property names for those that store array values
     */
    public function getArrayTypedProperties(DataBaseObject $object){

        $result = [];
        $class = get_class($object);
        $types = $class::TYPES;

        // Find the object properties on global cache
        if(!isset(self::$_objectPropertiesCache[$class])){

            self::$_objectPropertiesCache[$class] = array_keys(get_object_vars($object));
        }

        foreach(array_diff(self::$_objectPropertiesCache[$class], $this->_baseObjectProperties) as $property){

            if(isset($types[$property])){

                if(in_array(DataBaseObject::ARRAY, $types[$property], true)){

                    $result[] = $property;
                }

            }elseif(is_array($object->{$property})){

                $result[] = $property;
            }
        }

        return $result;
    }


    /**
     * Obtain the value for any of the object properties, even the private ones.
     * If not public, reflection will be used. This means that we are also able to access the values
     * of protected and private properties
     *
     * @param DataBaseObject $object The instance that we want to read
     * @param string $property The name for the property that we want to read from the object instance
     *
     * @return mixed The property value
     */
    private function _getPropertyValue(DataBaseObject $object, string $property){

        if(isset($object->{$property})){

            return $object->{$property};
        }

        $reflectionObject = new ReflectionObject($object);
        $reflectionProperty = $reflectionObject->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }


    /**
     * Obtain a list with all the properties that are typed as multi language for the provided object
     *
     * @param DataBaseObject $object A valid database object instance
     *
     * @return string[] An array with all the property names for those that are defined to store multi language values
     */
    public function getMultiLanguageTypedProperties(DataBaseObject $object){

        $result = [];

        foreach(get_class($object)::TYPES as $property => $type){

            // Note that we ignore all arrays cause they are not allowed for multi language properties
            if(in_array(DataBaseObject::MULTI_LANGUAGE, $type, true) && !is_array($object->{$property})){

                $result[] = $property;
            }
        }

        return $result;
    }


    /**
     * Get an object multi language property value for the specified locale
     *
     * @param DataBaseObject $object The object for which we want to obtain the property value
     * @param string $property The name for the property
     * @param string $locale The language we want to get for the localized property
     *
     * @return mixed The value for the specified multi language property for the specified locale
     */
    private function _getMultiLanguagePropertyValue(DataBaseObject $object, string $property, string $locale){

        $locales = $object->getLocales();
        $localeLowerCase = strtolower($locale);

        for ($i = 1, $l = count($locales); $i < $l; $i++) {

            if($localeLowerCase === strtolower($locales[$i])){

                $localesData = $this->_getPropertyValue($object, '_locales');
                return $localesData[$locales[$i]][$property];
            }
        }

        return $object->{$property};
    }


    /**
     * Auxilary method to obtain the full list of available locales for a provided database object property.
     * It will get the list from the object itself but also from the stored database table (if exist)
     *
     * @param DataBaseObject $object The object to inspect
     * @param string $propertyTable The table were the multi locales property is stored
     *
     * @return array Full list of the property available locales. IMPORTANT: All will be lower case
     */
    private function _getMultiLanguagePropertyAllLocalesList(DataBaseObject $object, $propertyTable){

        // Generate a list with all the possible locales: the undefined locale, those on db and those on the object
        $localeslist = array_merge([''], $object->getLocales());

        if($this->_db->tableExists($propertyTable)){

            $localeslist = array_merge($localeslist, $this->_db->tableGetColumnNames($propertyTable));
        }

        // Replace the undefined locale and the dbid column name with a '_', and then make the array unique
        return array_unique(array_map(function($v) {

            return ($v === '' || $v === 'dbid') ? '_' : strtolower($v);

        }, $localeslist));
    }


    /**
     * This method will check that the table columns and types are valid to store the provided object information, and depending on the configuration values it will
     * update the table structure so the object can be correctly saved. If table does not exist, it will be created. The following checks are performed:
     *
     * 1. Test that the table has the same columns as the object data. If not, depending on configuration values, table may be altered or an exception thrown.
     * 2. Test that all the table columns have data types which can store the same object table column values. If not, depending on configuration values, table may be altered or an exception thrown.
     *
     * @param DataBaseObject $object A valid database object instance
     * @param string $tableName The name for the table we want to inspect and alter if is allowed by current setup
     *
     * @throws UnexpectedValueException If the current setup forbids us to modify the table so it can fit the object data
     *
     * @return array The object database table structure and values
     */
    private function _updateTablesToFitObject(DataBaseObject $object, string $tableName){

        // Sync all the base object properties to the object table
        $tableDef = ['primaryKey' => ['dbid'],
                     'uniqueIndices' => [['dbuuid']],
                     'columns' => [],
                     'resizeColumns' => $this->isColumnResizedWhenValueisBigger,
                     'deleteColumns' => $this->isColumnDeletedWhenMissingOnObject ? 'yes' : 'fail'];

        foreach ($this->getBasicProperties($object, true) as $property) {

            $tableDef['columns'][] = strtolower($property).' '.$this->getSQLTypeFromObjectProperty($object, $property);

            if(isset($object->{$property}) &&
               in_array(DataBaseObject::NO_DUPLICATES, $this->_getTypeFromObjectProperty($object, $property), true)){

                $tableDef['uniqueIndices'][] = [strtolower($property)];
            }
        }

        foreach (get_class($object)::UNIQUEINDICES as $uniqueIndex) {

            if(!empty($uniqueIndex)){

                $tableDef['uniqueIndices'][] =  array_map(function ($p) {return strtolower($p);}, $uniqueIndex);
            }
        }

        try {

            $this->_db->tableAlterToFitDefinition($tableName, $tableDef);

        } catch (Throwable $e) {

            if($e->getCode() === 3){

                throw new UnexpectedValueException('<'.$tableName.'> table contains a column which must exist as a basic property on object being saved: '.$e->getMessage());
            }

            throw $e;
        }

        // Sync all the array typed properties to their respective database tables
        foreach ($this->getArrayTypedProperties($object) as $property) {

            $arrayPropTableName = $tableName.'_'.strtolower($property);

            $tableDef = ['uniqueIndices' => [['dbid', 'arrayindex']],
                'columns' => ['dbid '.$this->_unsignedBigIntSqlTypeDef, 'arrayindex '.$this->_unsignedBigIntSqlTypeDef],
                'resizeColumns' => $this->isColumnResizedWhenValueisBigger,
                'foreignKey' => [[$arrayPropTableName.'_dbid_fk', ['dbid'], $tableName, ['dbid']]],
                'deleteColumns' => 'no'];

            if(count($object->{$property}) > 0){

                $tableDef['columns'][] = 'value '.$this->getSQLTypeFromObjectProperty($object, $property);
            }

            $this->_db->tableAlterToFitDefinition($arrayPropTableName, $tableDef);
        }

        // Sync all multi language properties to their respective database tables
        foreach ($this->getMultiLanguageTypedProperties($object) as $property) {

            $multiLanPropTableName = $tableName.'_'.strtolower($property);

            $tableDef = ['columns' => ['dbid '.$this->_unsignedBigIntSqlTypeDef],
                'resizeColumns' => $this->isColumnResizedWhenValueisBigger,
                'foreignKey' => [[$multiLanPropTableName.'_dbid_fk', ['dbid'], $tableName, ['dbid']]],
                'deleteColumns' => 'no'];

            // Always set the 'no locale' column
            $tableDef['columns'][] = '_ '.$this->getSQLTypeFromObjectProperty($object, $property);

            foreach ($object->getLocales() as $locale) {

                if($locale !== ''){

                    $tableDef['columns'][] = strtolower($locale).' '.$this->getSQLTypeFromObjectProperty($object, $property);
                }
            }

            $this->_db->tableAlterToFitDefinition($multiLanPropTableName, $tableDef);
        }

        return $this->_convertObjectToTableData($object);
    }


    /**
     * Verifies that the specified DataBaseObject instance is correctly defined to be used by this class
     * This method tests the object at the language level: No database checks or queries are performed, only class
     * values and types are checked to make sure the Object is correctly set up.
     *
     * @param DataBaseObject $object An instance to validate
     *
     * @throws UnexpectedValueException If validation fails, with the failure cause message
     *
     * @return void
     */
    public function validateObject(DataBaseObject $object){

        // Cache the most used object values to improve performance
        $class = get_class($object);
        $className = StringUtils::getPathElement($class);
        $dbId = $object->getDbId();
        $dbUUID = $object->getDbUUID();
        $creationDate = $object->getDbCreationDate();
        $modificationDate = $object->getDbModificationDate();

        if($dbId !== null && (!is_integer($dbId) || $dbId < 1)){

            throw new UnexpectedValueException('Invalid '.$className.' dbId: '.$dbId);
        }

        if($dbUUID !== null && (!is_string($dbUUID) || strlen($dbUUID) !== 36)){

            throw new UnexpectedValueException('Invalid '.$className.' dbUUID: '.$dbUUID);
        }

        if($creationDate !== null){

            $this->_validateDateTimeValue($creationDate, 6, 'dbCreationDate');
        }

        if($modificationDate !== null){

            $this->_validateDateTimeValue($modificationDate, 6, 'dbModificationDate');
        }

        if($object->getDbDeleted() !== null){

            $this->_validateDateTimeValue($object->getDbDeleted(), 6, 'dbDeleted');
        }

        if($dbId === null && ($creationDate !== null || $modificationDate !== null)){

            throw new UnexpectedValueException('Creation and modification date must be null if dbid is null');
        }

        // Reflection is costly in compute power, so we will statically cache it for reuse between validation calls
        static $classMethodsCache = [];

        if (!isset($classMethodsCache[$class])) {

            $classMethodsCache[$class] = (new ReflectionClass($class))->getMethods();
        }

        $classMethods = $classMethodsCache[$class];

        // Database objects must not have any unexpected method defined, cause they are only data containers
        if($classMethods > 0){

            static $acceptedMethods = ['__set', '__get', 'setup', '__construct', 'getDbId', 'getDbUUID', 'getDbCreationDate',
                                       'getDbModificationDate', 'getDbDeleted', 'isMultiLanguage', 'getLocales', 'setLocales'];

            foreach($classMethods as $classMethod){

                // Custom methods are not allowed on database objects. Only properties can be created
                if(!in_array($classMethod->name, $acceptedMethods, true)){

                    throw new UnexpectedValueException('Method is not allowed for DataBaseObject class '.$class.': '.$classMethod->name);
                }
            }
        }

        // Verify that all the object properties are valid regarding naming and type
        $isMultiLanguageObject = $object->isMultiLanguage();
        $objectLocales = $isMultiLanguageObject ? $object->getLocales() : [];
        $objectMultiLanProperties = $this->getMultiLanguageTypedProperties($object);

        // Cache class variables
        static $classPropertiesCache = [];

        if (!isset($classPropertiesCache[$class])) {

            $classPropertiesCache[$class] = array_keys(get_class_vars($class));
        }

        foreach($classPropertiesCache[$class] as $classProperty){

            // Properties that start with _ are forbidden, cause they are reserved for setup private properties
            if ($classProperty[0] === '_') {

                throw new UnexpectedValueException('Properties starting with _ are forbidden, but found: '.$classProperty);
            }

            // Database private properties cannot be overriden
            if(in_array($classProperty, $this->_baseObjectProperties, true)){

                throw new UnexpectedValueException('Overriding private db property is not allowed: '.$classProperty);
            }

            $objectClassPropertyValue = $object->{$classProperty};

            if($objectClassPropertyValue === []) {

                continue;
            }

            if(is_array($objectClassPropertyValue) && in_array(null, $objectClassPropertyValue, true)){

                throw new UnexpectedValueException('NULL value is not accepted inside array: '.$classProperty);
            }

            $propertyExpectedType = $this->_getTypeFromObjectProperty($object, $classProperty);
            $propertyExpectedTypeIsArray = in_array(DataBaseObject::ARRAY, $propertyExpectedType, true);
            $propertyValuesToCheck = [$objectClassPropertyValue];
            $isMultilanProperty = in_array($classProperty, $objectMultiLanProperties, true);

            if($isMultilanProperty){

                $propertyValuesToCheck = [];

                foreach ($objectLocales as $locale) {

                    $propertyValuesToCheck[] = $this->_getMultiLanguagePropertyValue($object, $classProperty, $locale);
                }
            }

            for ($i = 0, $l = count($propertyValuesToCheck); $i < $l; $i++) {

                $multilanErrorTag = $isMultilanProperty ? ' (locale '.$objectLocales[$i].')' : '';

                if($propertyValuesToCheck[$i] === null){

                    if(in_array(DataBaseObject::NOT_NULL, $propertyExpectedType, true) || $propertyExpectedTypeIsArray){

                        throw new UnexpectedValueException('NULL value is not accepted by '.$classProperty.' property'.$multilanErrorTag);
                    }

                    continue;
                }

                $propertyValueType = $this->_getTypeFromValue($propertyValuesToCheck[$i]);

                if($propertyExpectedTypeIsArray && !is_array($propertyValuesToCheck[$i])){

                    throw new UnexpectedValueException($classProperty.' must be an array');
                }

                // Check that property type matches expected one (note that double types are able to store int values and datetime types string values)
                // Property type must be valid based on the object defined restrictions and it must fit the expected precision
                if($propertyExpectedType[0] !== $propertyValueType[0] &&
                   !($propertyExpectedType[0] === DataBaseObject::DOUBLE && $propertyValueType[0] === DataBaseObject::INT)){

                    if($propertyExpectedType[0] === DataBaseObject::DATETIME){

                        $this->_validateDateTimeValue($propertyValuesToCheck[$i], $propertyExpectedType[1], $classProperty, $multilanErrorTag);

                    }else{

                        throw new UnexpectedValueException($classProperty.' ('.print_r($propertyValuesToCheck[$i], true).') does not match '.
                            $propertyExpectedType[0].'('.$propertyExpectedType[1].')'.$multilanErrorTag);
                    }
                }

                // The property maximum allowed type size must be respected
                if($propertyExpectedType[0] !== DataBaseObject::DATETIME && $propertyValueType[1] > $propertyExpectedType[1]){

                    throw new UnexpectedValueException($classProperty.' value size '.$propertyValueType[1].' exceeds '.$propertyExpectedType[1].$multilanErrorTag);
                }
            }
        }
    }


    /**
     * Validate that the provided value is acceptable to be stored as a DatabaseObject datetime property
     * Only valid ISO 8601 strings will be accepted
     *
     * @param string $dateValue The value to test
     * @param int $microseconds The number of digits that are accepted for the microseconds precision (0, 3 or 6)
     * @param string $classProperty The name for the property that stores the value so it can be shown by error messages
     * @param string $extraErrorMsg An extra text that can be added at the end of the exception messages that are thrown when date is invalid
     *
     * @throws UnexpectedValueException If the provided datetime value does not meet requirements
     *
     * return void
     */
    private function _validateDateTimeValue($dateValue, $microseconds, string $classProperty, string $extraErrorMsg = ''){

        if(!DateTimeObject::isValidDateTime($dateValue)){

            throw new UnexpectedValueException($classProperty.' ('.print_r($dateValue, true).') is not a DATETIME('.$microseconds.')'.$extraErrorMsg);
        }

        $microSeconds = [];

        if(preg_match('/(\.......|\....)?(\+00:00|-00:00|Z)$/', $dateValue, $microSeconds) === 0){

            throw new UnexpectedValueException($classProperty.' ('.print_r($dateValue, true).') must have a UTC timezone'.$extraErrorMsg);
        }

        $microLen = isset($microSeconds[1]) ? max(0, strlen($microSeconds[1]) - 1) : 0;

        if($microLen !== $microseconds){

            throw new UnexpectedValueException($classProperty.' ('.print_r($dateValue, true).') does not match DATETIME('.$microseconds.')'.$extraErrorMsg);
        }
    }


    /**
     * Search all the instances that are stored for a given database object class
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object types that we want to obtain. Fo example: User::class
     *
     * @return DataBaseObject[] An array of object instances with all the objects that match the specified class, or empty array if no objects found.
     */
    public function findAll($class) {

        return $this->_generateObjectsFromDbTableData($class,
            $this->_db->tableGetRows($this->_getTableNameFromClass($class), []));
    }


    /**
     * Search all the instances that are stored for a given database object class and return an associative array.
     * This is normally useful if we want to directly serialize the result as a json string
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object types that we want to obtain. Fo example: User::class
     *
     * @return array An array were each element is a database object but serialized as an associative array instead of a DataBaseObject instance, or empty array if no objects found.
     */
    public function findAllToArray($class) {

        return $this->_generateAssociativeFromDbTableData($class,
            $this->_db->tableGetRows($this->_getTableNameFromClass($class), []));
    }


    /**
     * Search a database object that has the specified dbId value
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object type that we want to obtain. Fo example: User::class
     * @param int $dbid Integer with the dbId value we are looking for
     *
     * @return DataBaseObject An object instance that matches the specified id or null if object not found.
     */
    public function findByDbId($class, int $dbid) {

        if($dbid === null || !NumericUtils::isInteger($dbid)){

            throw new UnexpectedValueException('dbid non integer value: '.$dbid);
        }

        $result = $this->findByDbIds($class, [$dbid]);

        return $result === [] ? null : $result[0];
    }


    /**
     * Search a database object that has the specified dbId value and return an associative array.
     * This is normally useful if we want to directly serialize the result as a json string
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object type that we want to obtain. Fo example: User::class
     * @param int $dbid Integer with the dbId value we are looking for
     *
     * @return array A database object but serialized as an associative array instead of a DataBaseObject instance, or null if object not found.
     */
    public function findByDbIdToArray($class, int $dbid) {

        if($dbid === null || !NumericUtils::isInteger($dbid)){

            throw new UnexpectedValueException('dbid non integer value: '.$dbid);
        }

        $result = $this->findByDbIdsToArray($class, [$dbid]);

        return $result === [] ? null : $result[0];
    }


    /**
     * Search database objects which have the specified dbId values
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object types that we want to obtain. Fo example: User::class
     * @param array $dbids Array of integers with all the dbId values we are looking for
     *
     * @return DataBaseObject[] An array of object instances with all the objects that match the specified ids, or empty array if no objects found.
     */
    public function findByDbIds($class, array $dbids) {

        return $this->_findByDbIdsAux($class, $dbids, 'DataBaseObject');
    }


    /**
     * Search database objects which have the specified dbId values and return an associative array.
     * This is normally useful if we want to directly serialize the result as a json string
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object types that we want to obtain. Fo example: User::class
     * @param array $dbids Array of integers with all the dbId values we are looking for
     *
     * @return  array An array were each element is a database object but serialized as an associative array instead of a DataBaseObject instance, or empty array if no objects found.
     */
    public function findByDbIdsToArray($class, array $dbids) {

        return $this->_findByDbIdsAux($class, $dbids, 'associative');
    }


    /**
     * Auxiliary method to find values by dbid and return the data in different possible formats
     *
     * @param mixed $class See findByDbIds() method
     * @param array $dbids See findByDbIds() method
     * @param string $format Specifies which format will be returned by this method: 'DataBaseObject' or 'associative'
     *
     * @return array See findByDbIds() method
     */
    private function _findByDbIdsAux($class, array $dbids, string $format) {

        $dbidsArray = [];

        for ($i = 0, $l = count($dbids); $i < $l; $i++) {

            $dbid = $dbids[$i];

            if($dbid === null || !NumericUtils::isInteger($dbid)){

                throw new UnexpectedValueException('dbids array non integer value ('.$dbid.') at position '.$i);
            }

            $dbidsArray['dbid'] = $dbid;
        }

        $data = $this->_db->tableGetRows($this->_getTableNameFromClass($class), $dbidsArray);

        if($data === false){

            return [];
        }

        return $format === 'DataBaseObject' ?
            $this->_generateObjectsFromDbTableData($class, $data) :
            $this->_generateAssociativeFromDbTableData($class, $data);
    }


    /**
     * Search database objects of the specified type which have values that match on the specified properties
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object types that we want to obtain. Fo example: User::class
     * @param array $propertyValues Associative array where keys are the property names and values the property values that must be found on all
     *        the objects that will be returned by this Method
     *
     * @return array An array of object instances with all the objects that match the specified properties, or empty array if no objects found.
     */
    public function findByPropertyValues($class, array $propertyValues) {

        return $this->_findByPropertyValuesAux($class, $propertyValues, 'DataBaseObject');
    }


    /**
     * Search database objects of the specified type which have values that match on the specified properties and return an associative array.
     * This is normally useful if we want to directly serialize the result as a json string
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object types that we want to obtain. Fo example: User::class
     * @param array $propertyValues Associative array where keys are the property names and values the property values that must be found on all
     *        the objects that will be returned by this Method
     *
     * @return array An array were each element is a database object but serialized as an associative array instead of a DataBaseObject instance, or empty array if no objects found.
     */
    public function findByPropertyValuesToArray($class, array $propertyValues) {

        return $this->_findByPropertyValuesAux($class, $propertyValues, 'associative');
    }


    /**
     * Auxiliary method to find values by property and return the data in different possible formats
     *
     * @param mixed $class See findByPropertyValues() method
     * @param array $propertyValues See findByPropertyValues() method
     * @param string $format Specifies which format will be returned by this method: 'DataBaseObject' or 'associative'
     *
     * @return array See findByPropertyValues() method
     */
    private function _findByPropertyValuesAux($class, array $propertyValues, string $format) {

        $columns = [];

        foreach ($propertyValues as $key => $value) {

            $columns[strtolower($key)] = $value;
        }

        try {

            $data = $this->_db->tableGetRows($this->_getTableNameFromClass($class), $columns);

        } catch (Throwable $e) {

            return [];
        }

        return $format === 'DataBaseObject' ?
            $this->_generateObjectsFromDbTableData($class, $data) :
            $this->_generateAssociativeFromDbTableData($class, $data);
    }


    /**
     * TODO
     */
    public function findByFilter() {

        // TODO - Obtain one or more Database objects given a complex filter
    }


    /**
     * TODO
     */
    public function findByFilterToArray() {

        // TODO
    }


    /**
     * Aux method to generate a list of fully initialized database object instances from the loaded rows of a database table.
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object types that we want to obtain. Fo example: User::class
     * @param array $data The table rows loaded from db
     *
     * @return array A list of initialized database object instances
     */
    private function _generateObjectsFromDbTableData($class, array $data){

        if(empty($data)){

            return [];
        }

        // Generate the object instance for each of the table rows
        $basicProperties = $this->getBasicProperties(new $class(), false);

        $result = [];

        foreach ($data as $row) {

            $object = new $class();
            $this->_setPrivatePropertyValue($object, 'dbId', (int)$row['dbid']);
            $this->_setPrivatePropertyValue($object, 'dbCreationDate', $row['dbcreationdate'] . '+00:00');
            $this->_setPrivatePropertyValue($object, 'dbModificationDate', $row['dbmodificationdate'] . '+00:00');

            foreach ($basicProperties as $property) {

                $object->{$property} = $this->_castDbValueToPropertyType($row[strtolower($property)], $object, $property);
            }

            $result[] = $object;
        }

        // Add the array typed property values if necessary
        $tableName = $this->getTableNameFromObject($result[0]).'_';

        foreach ($this->getArrayTypedProperties($result[0]) as $property) {

            $arrayPropTableName = $tableName.strtolower($property);

            foreach ($result as $object) {

                $object->{$property} = [];

                try {

                    $arrayPropValues = $this->_db->query("SELECT value FROM $arrayPropTableName WHERE dbid=".$object->getDbId()." ORDER BY arrayindex ASC");

                    $object->{$property} = array_map(function ($r) {return $r['value']; }, $arrayPropValues);

                } catch (Throwable $e) {

                    // Nothing to do
                }
            }
        }

        return $result;
    }


    /**
     * Aux method to generate a list of associative arrays representing the data of object instances from the loaded rows of a database table.
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object types that we want to obtain. Fo example: User::class
     * @param array $data The table rows loaded from db
     *
     * @return array A list of associative arrays were each one contains the same data as the respective Database Object
     */
    private function _generateAssociativeFromDbTableData($class, array $data){

        if(empty($data)){

            return [];
        }

        // Generate the associative array for each of the table rows
        $objectDummyInstance = new $class();
        $basicProperties = $this->getBasicProperties($objectDummyInstance);

        $result = array_map(function ($row) use ($basicProperties, $objectDummyInstance) {

            $array = [
                'dbId' => (int)$row['dbid'],
                'dbCreationDate' => $row['dbcreationdate'] . '+00:00',
                'dbModificationDate' => $row['dbmodificationdate'] . '+00:00'
            ];

            foreach ($basicProperties as $property) {

                $array[$property] = $this->_castDbValueToPropertyType($row[strtolower($property)], $objectDummyInstance, $property);
            }

            return $array;

        }, $data);

        // Add the array typed property values if necessary
        $tableName = $this->getTableNameFromObject($objectDummyInstance).'_';

        foreach ($this->getArrayTypedProperties($objectDummyInstance) as $property) {

            $arrayPropTableName = $tableName.strtolower($property);

            for ($i = 0, $l = count($result); $i < $l; $i++){

                $result[$i][$property] = [];

                try {

                    $arrayPropValues = $this->_db->query("SELECT value FROM $arrayPropTableName WHERE dbid=".$result[$i]['dbId']." ORDER BY arrayindex ASC");

                    $result[$i][$property] = array_map(function ($r) {return $r['value']; }, $arrayPropValues);

                } catch (Throwable $e) {

                    // Nothing to do
                }
            }
        }

        return $result;
    }


    /**
     * Erase one or more DatabaseObject instances from database. Method is transactional so if any of the objects can't be deleted, none will be.
     *
     * If $this->isTrashEnabled is true, the object instances will be moved to trash. Otherwise they will be permanently deleted
     *
     * @param array|DataBaseObject $objects A single database object instance or an array with instances to be deleted
     *
     * @throws UnexpectedValueException If any problem arises
     *
     * @return int The number of deleted objects from db (if delete was successful)
     */
    public function deleteByInstances($objects){

        if(!is_array($objects)){

            $objects = [$objects];
        }

        $result = $this->deleteByDbIds(get_class($objects[0]), array_map(function ($o) {return $o->getDbId();}, $objects));

        // Clear all db ids from the received objects
        foreach ($objects as $object) {

            $this->_setPrivatePropertyValue($object, 'dbId', null);
        }

        return $result;
    }


    /**
     * Erase one or more DatabaseObject instances from database given the value of some object properties. Method is transactional
     * so if any of the objects can't be deleted, none will be.
     *
     * If $this->isTrashEnabled is true, the object instances will be moved to trash. Otherwise they will be permanently deleted
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object types that we want to delete. Fo example: User::class
     * @param array $propertyValues Associative array where keys are the property names and values the property values that must be found on all
     *        the objects that will be deleted by this Method
     *
     * @throws UnexpectedValueException If any problem arises
     *
     * @return int The number of deleted objects from db (if delete was successful)
     */
    public function deleteByPropertyValues($class, array $propertyValues){

        $columns = [];

        foreach ($propertyValues as $key => $value) {

            $columns[strtolower($key)] = $value;
        }

        return $this->deleteByDbIds($class,
            array_map(function ($p) { return $p['dbid']; }, $this->_db->tableGetRows($this->_getTableNameFromClass($class), $columns)));
    }


    /**
     * Erase one or more DatabaseObject instances from database given their dbids. Method is transactional so if any of the objects
     * can't be deleted, none will be.
     *
     * If $this->isTrashEnabled is true, the object instances will be moved to trash. Otherwise they will be permanently deleted
     *
     * @param mixed $class The class (which extends DataBaseObject) for the object types that we want to delete. Fo example: User::class
     * @param array $dbIds A list of dbids for the objects to delete
     *
     * @throws UnexpectedValueException If any problem arises
     *
     * @return int The number of deleted objects from db (if delete was successful)
     */
    public function deleteByDbIds($class, array $dbIds){

        $deletedObjectsCount = 0;
        $tableName = $this->_getTableNameFromClass($class);

        $this->_db->transactionBegin();

        foreach ($dbIds as $dbId) {

            // TODO - Implement isTrashEnabled feature

            try {

                // If the FOREIGN_DELETE_OBJECTS contains any data, we will first delete any objects related to the parent one
                if(!empty($class::FOREIGN_DELETE_OBJECTS) && ($object = $this->findByDbId($class, $dbId)) !== null){

                    // Loop all the foreign object classes to be deleted
                    foreach ($class::FOREIGN_DELETE_OBJECTS as $foreignClass => $properties) {

                        // Obtain the values for the object that must exist on foreign objects to be deleted
                        $propertiesToDelete = [];

                        foreach ($properties as $objectProperty => $foreignObjectProperty) {

                            $propertiesToDelete[$foreignObjectProperty] = $this->_getPropertyValue($object, $objectProperty);
                        }

                        // Execute the deletion of the related foreign objects of the current class that match the object property values
                        try {

                            $this->deleteByPropertyValues($foreignClass, $propertiesToDelete);

                        } catch (Throwable $e) {

                            if(!class_exists($foreignClass)){

                                throw new UnexpectedValueException('Invalid foreign class specified: '.$foreignClass);
                            }

                            // If the foreign object db table does not exist, we will ignore the deletion error. Otherwise, the error
                            // will be thrown. This is because maybe no any foreign object is saved yet.
                            if($this->getDataBaseManager()->tableExists($this->_getTableNameFromClass($foreignClass))){

                                throw $e;
                            }
                        }
                    }
                }

                // Delete the requested object itself
                if(($deleteCount = $this->_db->tableDeleteRows($tableName, ['dbid' => $dbId])) !== 1){

                    throw new UnexpectedValueException('object dbid not found: '.$dbId);
                }

            } catch (Throwable $e) {

                $this->_db->transactionRollback();

                throw new UnexpectedValueException('Error deleting objects: '.$e->getMessage());
            }

            $deletedObjectsCount += $deleteCount;
        }

        $this->_db->transactionCommit();

        return $deletedObjectsCount;
    }


    /**
     * @see DataBaseManager::disconnect
     */
    public function disconnect() {

        return $this->_db->disconnect();
    }
}
