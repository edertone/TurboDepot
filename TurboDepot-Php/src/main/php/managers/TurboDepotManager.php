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

		// Get global server database connection
		$db = Server::getDb(get_class().'_'.__FUNCTION__);

		// Store the name for the received class
		$className = get_class($entity);

		// Set the creation and modification dates if required
		$entity->modificationDate = date('Y-m-d H:i:s');

		if($entity->id == ''){

			$entity->creationDate = date('Y-m-d H:i:s');

			// Verify that the specified category id is created with the same className as the generic entity we want to save.
			// This is important to prevent developers from making errors when storing entities inside categories that are not made for their class.
			if($db->countByQuery("SELECT categoryId FROM generic_category WHERE className = '".$className."' AND categoryid = ".$entity->categoryId) <= 0){

				return ServerResultsUtils::generateErrorResult('Error creating generic: Specified category ('.$entity->categoryId.') does not match entity className ('.$className.')');
			}
		}

		// Begin the transaction
		$db->transactionBegin();

		// Create the generic
		if(!$db->query(SqlGenerationUtils::insertUpdateFromClass($entity, 'id', 'generic', implode(',', GenericsUtils::getFixedProperties()), array('className' => $className)))){

			$db->transactionRollback();
			return ServerResultsUtils::generateErrorResult('Could not execute save operation'.$db->lastError);
		}

		// Get the autonumeric generated id value.
		if($entity->id == ''){

			$entity->id = $db->getLastInsertId();

		}else{

			// Clear all previously existing localized and non localized properties for this generic
			if(!$db->query('DELETE FROM generic_value where genericId = '.$entity->id)){

				$db->transactionRollback();
				return ServerResultsUtils::generateErrorResult('Could not delete generic properties'.$db->lastError);
			}
		}

		$insertValues = array();

		// Generate an INSERT query part for all the NON LOCALIZED properties that have some value
		$props = GenericsUtils::getNonLocalizedProperties($entity);

		foreach ($props as $name){

			if($entity->{$name} != ''){

				array_push($insertValues, "('".$entity->id."','".$name."','', '".addslashes($entity->{$name})."')");
			}
		}

		// Generate an INSERT query part for all the LOCALIZED properties that have some value
		$props = GenericsUtils::getLocalizedProperties($entity);

		foreach ($entity->locales as $l){

			foreach ($props as $name){

				if($l->{$name} != ''){

					array_push($insertValues, "('".$entity->id."','".$name."','".$l->locale."', '".addslashes($l->{$name})."')");
				}
			}
		}

		// Insert all the property values
		if(count($insertValues) > 0){

			if(!$db->query('INSERT INTO generic_value (genericId,propertyName,locale,value) VALUES '.implode(',', $insertValues))){

				$db->transactionRollback();
				return ServerResultsUtils::generateErrorResult('Could not save generic properties '.$db->lastError);
			}
		}else{

			// If there are no values on the entity, we will not save it. An error will happen
			$db->transactionRollback();
			return ServerResultsUtils::generateErrorResult('Generic entity is Empty');
		}


		// Add the links for the pictures that are related to this entity
		if($msg = ControllerUtils::savePictureLinksToEntity($db, 'genericId', $entity->id, 'generic_picture', $entity->pictures)){

			$db->transactionRollback();
			return ServerResultsUtils::generateErrorResult($msg);
		}

		// Add the links for the videos that are related to this entity
		if($msg = ControllerUtils::saveVideoLinksToEntity($db, 'genericId', $entity->id, 'generic_video', $entity->videos)){

			$db->transactionRollback();
			return ServerResultsUtils::generateErrorResult($msg);
		}

		// Add the links for the files that are related to this entity
		if($msg = ControllerUtils::saveFileLinksToEntity($db, 'genericId', $entity->id, 'generic_file', $entity->files)){

			$db->transactionRollback();
			return ServerResultsUtils::generateErrorResult($msg);
		}

		$db->transactionCommit();

		return ServerResultsUtils::generateOkResult('Save complete', array('id' => $entity->id));

	}


	/**
	 * TODO
	 */
	public function disconnect(){

		// TODO: Connect to database

	}
}

?>