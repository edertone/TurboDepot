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
 * StorageFolderManager class
 */
class StorageFolderManager extends BaseStrictClass{


    /**
     * The path where the root of the storage folder is located
     * @var string
     */
    private $_storagePath = '';


    /**
     * The storage folder is a standarized set of files and folders that are used to save the persistent information of an application.
     *
     * This class manages all the operations that are performed against this folder.
     */
    public function __construct($storageFolderRoot = ''){

        if($storageFolderRoot !== '' && is_dir($storageFolderRoot.DIRECTORY_SEPARATOR.'custom')){

            $this->_storagePath = $storageFolderRoot;

        } else {

            // Try to find the storage path location and store it on the global variable so it is faster the next time
            $lookupPath = $storageFolderRoot.DIRECTORY_SEPARATOR.'..';

            for ($i = 0; $i < 5; $i++) {

                if(is_dir($lookupPath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'custom')){

                    $this->_storagePath = StringUtils::formatPath($lookupPath.DIRECTORY_SEPARATOR.'storage');

                    return;
                }

                $lookupPath .= DIRECTORY_SEPARATOR.'..';
            }

            throw new UnexpectedValueException('Could not find storage folder based on: '.$storageFolderRoot);
        }
    }


    /**
     * TODO - Validate that the current storage path structure is perfectly correct
     */
    public function validateStructure(){

        // TODO
    }


    /**
     * Gives the filesystem location to the storage folder root
     *
     * @return string
     */
    public function getPathToRoot(){

        return $this->_storagePath;
    }


    /**
     * Gives the filesystem location to the storage/custom folder
     *
     * @return string
     */
    public function getPathToCustom(){

        return $this->_storagePath.DIRECTORY_SEPARATOR.'custom';
    }


    /**
     * Gives the filesystem location to the storage/executable folder
     *
     * @return string
     */
    public function getPathToExecutable(){

        return $this->_storagePath.DIRECTORY_SEPARATOR.'executable';
    }
}

?>