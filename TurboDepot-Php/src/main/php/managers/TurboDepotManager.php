<?php

/**
 * TurboDepot is a cross language ORM library that allows saving, listing and retrieving multiple kinds of objects
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2017 Edertone Advanded Solutions (Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\main\php\managers;

use org\turbocommons\src\main\php\model\BaseSingletonClass;
use org\turbocommons\src\main\php\managers\FilesManager;
use org\turbocommons\src\main\php\utils\SerializationUtils;


/**
 * SINGLETON class that contains the full turbodepot api. Use it to save, read, list and operate with stored objects
 */
class TurboDepotManager extends BaseSingletonClass{


	/**
	 * Returns the global singleton instance.
	 *
	 * @return TurboDepotManager The singleton instance.
	 */
	public static function getInstance(){

		// This method is overriden from the singleton one simply to get correct
		// autocomplete annotations when returning the instance
		 $instance = parent::getInstance();

		 return $instance;
	}


	/**
	 * A text that will be appended at the beginning of all tables created by this class at the target db.
	 * This is used to prevent the auto generated tables from colliding with any possible existing db tables
	 * or any ones that are created by another library or application. If you know for sure that no name collisions
	 * will happen, you can clear this prefix to get a cleaner database schema. Make sure to rename any existing
	 * tables which already have this prefix before changing this value.
	 */
	public $dataBasePrefix = 'turbodepot_';


	/**
	 * Stores the global database manager object that is used to operate and perform queries
	 */
	private $_dataBaseManager = new DataBaseManager();


	/**
	 * TODO
	 */
	public function loadSetup($path){

		// TODO
		$setup = SerializationUtils::stringToXML(FilesManager::getInstance()->readFile($path));

	}


	/**
	 * TODO
	 */
	public function connect(){

		// TODO: Connect to database

	}


	/**
	 * Saves an entity to database by updating it if already exists (non empty id) or by creating a new one (empty id)
	 *
	 * @param EntityGeneric $entity The data of the entry to save
	 *
	 * @return SimpleXMLElement The operation result
	 */
	public function save(EntityGeneric $entity){


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
	 * TODO
	 */
	public function disconnect(){

		// TODO
	}
}

?>