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

        $db = $this->_dataBaseManager;

        // Set the creation and modification dates if required
        $object->modificationDate = date('Y-m-d H:i:s');

        if($object->dbId === null){

            $object->creationDate = date('Y-m-d H:i:s');
        }

        $this->_validateDataBaseObject($object);
        $tableName = $this->getTableNameFromObject($object);
        $objectValues = $this->geTableDataFromObject($object);

        $db->transactionBegin();

        // Create the object table if it does not exist
        if(!$db->tableExists($tableName)){

            $this->_createObjectTable($tableName, $objectValues);
        }

        // Store or update the object into the database
        if($object->dbId === null){

            $db->tableAddRow($tableName, $objectValues);

            $object->dbId = $db->getLastInsertId();

        }else{

            try {

                $db->tableUpdateRow($tableName, 'dbId', $object->dbId, $objectValues);

            } catch (Throwable $e) {

                $db->transactionRollback();

                throw $e;
            }
        }

        // TODO - We will store properties that store multiple values as table relations like for example
        // customer_emails which will contain the parent id and the different values. (Think and improve this concept)

        $db->transactionCommit();

        return $object->dbId;
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
     * on different Operating systems. This is cause it does not contain upper case letters which would be problematic in Windows OS for example
     *
     * (Note that also the currently defined tablesPrefix value will be added at the beginning of the table name).
     *
     * @param DataBaseObject $object An instance to calculate its respective table name.
     *
     * @return string The table name that would be used to store the provided object when saved to database.
     */
    public function getTableNameFromObject(DataBaseObject $object) {

        return $this->tablesPrefix.StringUtils::formatCase(StringUtils::getPathElement(get_class($object)), StringUtils::FORMAT_LOWER_SNAKE_CASE);
    }


    public function geTableDataFromObject(DataBaseObject $object){

        // TODO - totes les columnes en snake case, per evitar conflictes amb mayuscules minuscules

        $objectValues = get_object_vars($object);

        // Move the base common properties to the begining of the array, so they get correctly sorted at the db table
        $objectValues = ['deleted' => $objectValues['deleted']] + $objectValues;
        $objectValues = ['modificationDate' => $objectValues['modificationDate']] + $objectValues;
        $objectValues = ['creationDate' => $objectValues['creationDate']] + $objectValues;
        $objectValues = ['sortIndex' => $objectValues['sortIndex']] + $objectValues;
        $objectValues = ['uuid' => $objectValues['uuid']] + $objectValues;
        $objectValues = ['dbId' => $objectValues['dbId']] + $objectValues;

        return $objectValues;
    }


    /**
     * Aux method to create the table that represents the provided object on database
     *
     * @param string $tableName The name for the DataBaseObject table
     * @param array $objectValues Array with all the object column keys and values
     */
    private function _createObjectTable($tableName, $objectValues){

        $tableColumns = [];

        foreach ($objectValues as $property => $value) {

            switch ($property) {

                case 'dbId':
                    $tableColumns[] = $property.' bigint NOT NULL AUTO_INCREMENT';
                    break;

                case 'uuid':
                    $tableColumns[] = $property.' varchar(36) NOT NULL';
                    break;

                case 'parentId':
                case 'sortIndex':
                    $tableColumns[] = $property.' bigint';
                    break;

                case 'creationDate':
                case 'modificationDate':
                    $tableColumns[] = $property.' datetime NOT NULL';
                    break;

                case 'deleted':
                    $tableColumns[] = $property.' datetime';
                    break;

                default:

                    if(is_string($value)){

                        $tableColumns[] = $property.' varchar(255)';
                    }

                    if(!is_string($value)){

                        $tableColumns[] = $property.' bigint';
                    }
                    break;
            }
        }

        $tableColumns[] = 'PRIMARY KEY (dbId)';

        // TODO - we must create an index to improve sorting for the sortIndex column

        $this->_dataBaseManager->tableCreate($tableName, $tableColumns);
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

        if($object->dbId !== null && !is_integer($object->dbId)){

            throw new UnexpectedValueException($className.' dbId invalid value: '.$object->dbId);
        }

        // Database objects must not have any method defined, cause they are only data containers
        if(($classMethods = get_class_methods(get_class($object))) > 0){

            foreach($classMethods as $classMethod){

                // BaseStrictClass methods are the only ones that are allowed
                if($classMethod !== '__set' && $classMethod !== '__get'){

                    throw new UnexpectedValueException('Methods are not allowed on DataBaseObjects: '. $classMethods[0]);
                }
            }
        }
    }
}

?>