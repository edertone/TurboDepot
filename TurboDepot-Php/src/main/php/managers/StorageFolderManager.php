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

use UnexpectedValueException;
use org\turbocommons\src\main\php\model\BaseStrictClass;


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
     * temporary files, executable binaries, extra libraries, cached data, custom files, application data, etc..
     *
     * By defining a standard organization for all this information, we improve the structure and cleanliness of our project. And
     * a faster learning curve when switching between projects.
     *
     * @param string $storageFolderRoot The full file system path to the root of the folder that we want to use as the storage folder.
     *        all the required structure of folders and files must exist or this class will throw several errors.
     */
    public function __construct(string $storageFolderRoot = ''){

        if(!is_dir($storageFolderRoot.DIRECTORY_SEPARATOR.'custom')){

            throw new UnexpectedValueException('Could not find storage folder based on: '.$storageFolderRoot);
        }

        $this->_storagePath = $storageFolderRoot;
    }


    /**
     * Validate that the current storage path structure is perfectly correct.
     * If any element is missing or incorrect, an exception with the error details will be thrown.
     *
     * @throws UnexpectedValueException
     */
    public function validateFolderStructure(){

        $filesManager = new FilesManager();

        if($filesManager->countDirectoryItems($this->_storagePath, 'folders', 0) !== 7){

            throw new UnexpectedValueException('The storage folder must have 7 directories: '.$this->_storagePath);
        }

        if(!$filesManager->isFile($this->_storagePath.DIRECTORY_SEPARATOR.'README.txt')){

            throw new UnexpectedValueException('The current storage folder does not have a README.txt file: '.$this->_storagePath);
        }

        if(!$filesManager->isDirectory($this->_storagePath.DIRECTORY_SEPARATOR.'cache')){

            throw new UnexpectedValueException('The current storage folder does not have a cache folder: '.$this->_storagePath);
        }

        if(!$filesManager->isDirectory($this->_storagePath.DIRECTORY_SEPARATOR.'custom')){

            throw new UnexpectedValueException('The current storage folder does not have a custom folder: '.$this->_storagePath);
        }

        if(!$filesManager->isDirectory($this->_storagePath.DIRECTORY_SEPARATOR.'db')){

            throw new UnexpectedValueException('The current storage folder does not have a db folder: '.$this->_storagePath);
        }

        if(!$filesManager->isDirectory($this->_storagePath.DIRECTORY_SEPARATOR.'data')){

            throw new UnexpectedValueException('The current storage folder does not have a data folder: '.$this->_storagePath);
        }

        if(!$filesManager->isDirectory($this->_storagePath.DIRECTORY_SEPARATOR.'executable')){

            throw new UnexpectedValueException('The current storage folder does not have a executable folder: '.$this->_storagePath);
        }

        if(!$filesManager->isDirectory($this->_storagePath.DIRECTORY_SEPARATOR.'logs')){

            throw new UnexpectedValueException('The current storage folder does not have a logs folder: '.$this->_storagePath);
        }

        if(!$filesManager->isDirectory($this->_storagePath.DIRECTORY_SEPARATOR.'tmp')){

            throw new UnexpectedValueException('The current storage folder does not have a tmp folder: '.$this->_storagePath);
        }

        return true;
    }


    /**
     * Gives the filesystem location to the storage folder root. It contains several folders with different purposes for our application.
     * Avoid storing anything on this root, always use the specific subfolder for your needs.
     *
     * @return string
     */
    public function getPathToRoot(){

        return $this->_storagePath;
    }


    /**
     * Gives the filesystem location to the storage/cache folder
     * This folder purpose is to store all the data which may be temporarily or undefinitely cached by our application. It is normally safe to delete the folder contents.
     * at any time we want.
     *
     * @return string
     */
    public function getPathToCache(){

        return $this->_storagePath.DIRECTORY_SEPARATOR.'cache';
    }


    /**
     * Gives the filesystem location to the storage/custom folder
     * This folder has no specific purpose. It is a place where any custom user data may be stored. For example, it may be
     * allowed for our users to acces this folder and place there anything they want.
     *
     * @return string
     */
    public function getPathToCustom(){

        return $this->_storagePath.DIRECTORY_SEPARATOR.'custom';
    }


    /**
     * Gives the filesystem location to the storage/executable folder
     * This folder purpose is to store executable binaries that are required by the application to perform operations
     *
     * @return string
     */
    public function getPathToExecutable(){

        return $this->_storagePath.DIRECTORY_SEPARATOR.'executable';
    }


    /**
     * Gives the filesystem location to the storage/data folder.
     * This folder purpose is to store all the data and files which may be used by our application to persist or read information
     *
     * @return string
     */
    public function getPathToData(){

        return $this->_storagePath.DIRECTORY_SEPARATOR.'data';
    }
}

?>