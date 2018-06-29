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
use org\turbocommons\src\main\php\utils\SerializationUtils;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbodepot\src\main\php\model\TurboDepotEntity;
use UnexpectedValueException;


/**
 * Singleton class that contains the full turbodepot api. Use it to save, read, list and operate with entities
 */
class TurboDepotManager extends BaseStrictClass{


	/**
	 * A text that will be appended at the beginning of all tables created by this class at the target db.
	 * This is used to prevent the auto generated tables from colliding with any possible existing db tables
	 * or any ones that are created by another library or application. If you know for sure that no name collisions
	 * will happen, you can clear this prefix to get a cleaner database schema. Make sure to rename any existing
	 * tables which already have this prefix before changing this value.
	 */
	public $dataBasePrefix = 'tdp_';


	/**
	 * Enables or disables the recycle bin feature.
	 * Setting it to true will move all deleted entities to the trash instead of permanently deleting them.
	 */
	public $recycleBinEnabled = false;


	/**
	 * Stores the setup data after being loaded via the loadSetup method
	 *
	 * @see TurboDepotManager::loadSetup
	 */
	private $_setup = null;


	/**
	 * Stores the global DataBaseManager object that is used to operate with db
	 */
	private $_sqlGenerationManager = null;


	/**
	 * Stores the global DataBaseManager object that is used to operate with db
	 */
	private $_dataBaseManager = null;


	/**
	 * Stores the global FilesManager object that is used to operate with file system
	 */
	private $_filesManager = null;


	/**
	 * Class constructor initializes the required objects
	 */
	public function __construct(){

		$this->_sqlGenerationManager= new SqlGenerationManager();

		$this->_dataBaseManager = new DataBaseManager();

		$this->_filesManager = new FilesManager();
	}


	/**
	 * Load all the TurboDepotManager parameters directly from a specified TurboDepot.xml setup file.
	 *
	 * @param string $path The path where a valid TurboDepot.xml settings file is located
	 *
	 * @return boolean True on success or false if anything fails
	 */
	public function loadSetup($path){

		$setupData = $this->_filesManager->readFile($path);

		if(!XmlUtils::isXML($setupData)){

			throw new UnexpectedValueException('TurboDepotManager->loadSetup: Invalid setup data specified');
		}

		// TODO Validate that loaded setup is correct, and throw an exception if the structure is not valid.

		$this->_setup = SerializationUtils::stringToXML($this->_filesManager->readFile($path));

		return true;
	}


	/**
	 * Connect TurboDepot to a mysql database.
	 *
	 * @param string $host Path to the mysql server (possible values: an ip, a hostname, 'localhost', etc ...). If left empty, the value will be obtained from loaded setup.
	 * @param string $userName The database user we will use for the connection. If left empty, the value will be obtained from loaded setup.
	 * @param string $password The database user password. If left empty, the value will be obtained from loaded setup.
	 * @param string $dataBaseName The name for the database to which we want to connect. If left empty, the value will be obtained from loaded setup.
	 *
	 * @return boolean True on success or false if connection was not possible
	 */
	public function connectMysql($host = '', $userName = '', $password = '', $dataBaseName = ''){

		$this->_sqlGenerationManager->setEngine(SqlGenerationManager::MYSQL);

		// Load the host from setup if not specified
		if($host == '' && $this->_setup->DataBase->MySql['host'] != ''){

			$host = (string)$this->_setup->DataBase->MySql['host'];
		}

		// Load the username from setup if not specified
		if($userName == '' && $this->_setup->DataBase->MySql['userName'] != ''){

			$userName = (string)$this->_setup->DataBase->MySql['userName'];
		}

		// Load the password from setup if not specified
		if($password == '' && $this->_setup->DataBase->MySql['password'] != ''){

			$password = (string)$this->_setup->DataBase->MySql['password'];
		}

		// Load the database name from setup if not specified
		if($dataBaseName == '' && $this->_setup->DataBase->MySql['dataBaseName'] != ''){

			$dataBaseName = (string)$this->_setup->DataBase->MySql['dataBaseName'];
		}

		return $this->_dataBaseManager->connectMysql($host, $userName, $password, $dataBaseName);
	}


	// TODO
	public function connectFileSystem($storage){


	}


	// TODO
	public function connectPostgreSql($todo){

		return $this->_dataBaseManager->connectPostgreSql($todo);
	}


	/**
	 * Saves an entity to database by updating it if already exists (non empty id) or by creating a new one (empty id)
	 *
	 * @param TurboDepotEntity $entity An instance of an entity to save
	 *
	 * @return
	 */
	public function save(TurboDepotEntity $entity){

		// Validate the received instance
		if(!$this->_isValidInstance($entity)){

			throw new UnexpectedValueException('TurboDepotManager->save: Specified entity is not valid');
		}

		$className = $this->_entityClassNameToSnakeCase($entity);

		// Update the entity modification date
		$entity->modificationDate = DateTimeUtils::getDateTimeNow();

		// Start a transaction
		$this->_dataBaseManager->transactionBegin();

		// Check if we are saving a new instance or updating an existing one
		if($entity->id == ''){

			$result = $this->_dataBaseManager->query($this->_entityToSqlInsert($entity));

		}else{

			$result = $this->_dataBaseManager->query($this->_entityToSqlUpdate($entity));
		}

		// Finish the transaction
		if($result === false){

			$this->_dataBaseManager->transactionRollback();

		}else{

			$this->_dataBaseManager->transactionCommit();
		}

		// Result may fail because this is the first time an instance of this class is being saved.
		// In this case, we will create the class table structure and try to save the entity again.
		if(!$result && !$this->_dataBaseManager->tableExists($className)){

			if($this->_dataBaseManager->query($this->_entityToSqlCreateTable($entity))){

				return $this->save($entity);
			}
		}

		return $result;
	}


	/**
	 * TODO
	 */
	public function listAsTable(){

		// TODO

	}


	/**
	 * TODO
	 */
	public function listAsInstances(){

		// TODO

	}


	/**
	 * Close all connections to database and/or file system
	 *
	 * @return boolean True if the disconnect was successful, false otherwise.
	 */
	public function disconnect() {

		// Check if we are currently connected to a mysql engine
		return $this->_dataBaseManager->disconnect();
	}


	/**
	 *
	 * @param TurboDepotEntity $entity
	 * @return string
	 */
	private function _generateUUID(){

		// http://php.net/manual/es/function.uniqid.php
	}


	/**
	 *
	 * @param TurboDepotEntity $entity
	 * @return string
	 */
	private function _isValidInstance($entity){

		$classProperties = ClassUtils::getPublicProperties($entity);

		// TODO - validate that received entity extends TurboDepotEntity and has the mandatory values and properties set
		// TODO - validate properties follow the strict order: id first, creation_Date second ..
		// TODO - Validate entity properties match the default defined types

		// TODO - validate creation date is before or equal to modification date
		// TODO - Validate date values are valid ISO8601

		return true;
	}


	/**
	 * Obtains the snake case class name from the given entity
	 *
	 * @param TurboDepotEntity $entity An entity instance
	 *
	 * @return string The entity class name with snake case
	 */
	private function _entityClassNameToSnakeCase(TurboDepotEntity $entity){

		$className = StringUtils::getFileNameWithExtension(get_class($entity));

		return StringUtils::formatCase($className, StringUtils::FORMAT_LOWER_SNAKE_CASE);
	}


	/**
	 * Generates the SQL CREATE TABLE sentence for the specified entity instance.
	 *
	 * @param TurboDepotEntity $entity An entity instance
	 *
	 * @return string The generated sql query
	 */
	private function _entityToSqlCreateTable(TurboDepotEntity $entity){

		// Loop all the entity properties to generate an array
		$sqlCreateColumns = [];

		// Create a dummy instance of the entity to get the default values of its properties
		$entityDefaults = new $entity;

		foreach ($entity as $property => $value){

			switch ($property) {

				case 'id':

					if($entityDefaults->id === 0){
						$sqlCreateColumns[] = $this->_sqlGenerationManager->columnCreateAutoIncrement('id', 1);
						//'id BIGINT NOT NULL AUTO_INCREMENT = 1;
					}

					if($entityDefaults->id === ''){
						//$sqlCreateColumns, $property.' VARCHAR(35) NOT NULL');
					}
					break;

				case 'creationDate':
				case 'modificationDate':
					$sqlCreateColumns[] = $property.' VARCHAR(35) NOT NULL';
					break;

				default:
					$sqlCreateColumns[] = $property.' VARCHAR(50) NOT NULL';
					break;
			}
		}

		return 'CREATE TABLE '.$this->_entityClassNameToSnakeCase($entity).' ('.implode(',', $sqlCreateColumns).')';
	}


	/**
	 * Generates the SQL INSERT sentence for the specified entity instance.
	 *
	 * @param TurboDepotEntity $entity An entity instance
	 *
	 * @return string The generated sql query
	 */
	private function _entityToSqlInsert(TurboDepotEntity $entity){

		// TODO - generate entity UUID

		$entity->creationDate = DateTimeUtils::getDateTimeNow();

		// TODO
		return 'TODO';
	}


	/**
	 * Generates the SQL INSERT sentence for the specified entity instance.
	 *
	 * @param TurboDepotEntity $entity An entity instance
	 *
	 * @return string The generated sql query
	 */
	private function _entityToSqlUpdate(TurboDepotEntity $entity){

		// TODO
		return 'TODO';
	}
}

?>