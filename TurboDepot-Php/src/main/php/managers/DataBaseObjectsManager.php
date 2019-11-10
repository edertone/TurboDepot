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
use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * DataBaseObjectsManager class
 */
class DataBaseObjectsManager extends BaseStrictClass{


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
     * Saves an object to database by updating it if already exists (when dbId is not null) or by creating a new one (when dbId is null)
     *
     * @param DataBaseObject $object An instance to save (when dbId is null) or update (when it has a dbId that exists on db)
     *
     * @return int The dbId value for the object that's been saved
     */
    public function save(DataBaseObject $object){

        $db = $this->_dataBaseManager;
        $tableName = self::_validateDataBaseObject($object);

        // Set the creation and modification dates if required
        $object->modificationDate = date('Y-m-d H:i:s');

        if($object->dbId === null){

            $object->creationDate = date('Y-m-d H:i:s');
        }

        // Obtain an array with all the object properties and their values
        $objectValues = get_object_vars($object);

        // Move the base common properties to the begining of the array, so they get correctly sorted at the db table
        $objectValues = ['deleted' => $objectValues['deleted']] + $objectValues;
        $objectValues = ['modificationDate' => $objectValues['modificationDate']] + $objectValues;
        $objectValues = ['creationDate' => $objectValues['creationDate']] + $objectValues;
        $objectValues = ['uuid' => $objectValues['uuid']] + $objectValues;
        $objectValues = ['dbId' => $objectValues['dbId']] + $objectValues;

        $db->transactionBegin();

        // Create the object table if it does not exist
        if(!$db->tableExists($tableName)){

            foreach ($objectValues as $property => $value) {

                switch ($property) {

                    case 'dbId':
                        $newColumns[] = $property.' bigint NOT NULL AUTO_INCREMENT';
                        break;

                    case 'creationDate':
                    case 'modificationDate':
                        $newColumns[] = $property.' datetime NOT NULL';
                        break;

                    case 'deleted':
                        $newColumns[] = $property.' datetime';
                        break;

                    default:

                        if(is_string($value)){

                            $newColumns[] = $property.' varchar(255)';
                        }

                        if(!is_string($value)){

                            $newColumns[] = $property.' bigint';
                        }
                        break;
                }
            }

            $newColumns[] = 'PRIMARY KEY (dbId)';

            $db->tableCreate($tableName, $newColumns);
        }

        // Store the object into the database
        $db->tableAddRow($tableName, $objectValues);

        if($object->dbId === null){

            $object->dbId = $db->getLastInsertId();
        }

        $db->transactionCommit();

        return $object->dbId;
    }


    /**
     * @see DataBaseManager::disconnect
     */
    public function disconnect() {

        return $this->_dataBaseManager->disconnect();
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

        // The table name that is used to store the object is calculated by converting its classname to lower_snake_case.
        // This is the most compatible way to store table names cause it does not contain upper case letters that would
        // be problematic in Windows OS for example
        return StringUtils::formatCase($className, StringUtils::FORMAT_LOWER_SNAKE_CASE);
    }
}

?>