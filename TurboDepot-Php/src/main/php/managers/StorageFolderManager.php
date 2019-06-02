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
     * This class defines an instance that is used to manage an application main storage folder.
     *
     * The storage folder is a file system location where we store all our required application data. It is organized as a
     * standardized set of files and folders which are used to save the most commonly required application data, just like logs,
     * temporary files, executable binaries, extra libraries, cached data, custom files, etc..
     *
     * By defining a standard organization for all this information, we improve the structure and cleanliness of our project. And
     * a faster learning curve when switching between projects.
     *
     * @param string $storageFolderRoot The full file system path to the root of the folder that we want to use as the storage folder.
     *        all the required structure of folders and files must exist or this class will throw several errors.
     */
    public function __construct($storageFolderRoot = ''){

        if(!is_dir($storageFolderRoot.DIRECTORY_SEPARATOR.'custom')){

            throw new UnexpectedValueException('Could not find storage folder based on: '.$storageFolderRoot);
        }

        $this->_storagePath = $storageFolderRoot;
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