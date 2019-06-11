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
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * Temporary files manager class
 */
class TmpFilesManager extends BaseStrictClass{


    /**
     * The full OS filesystem path to the root of the temporary files storage folder
     * @var string
     */
    private $_rootPath = '';


    /**
     * A files manager instance that is used by this class
     * @var FilesManager
     */
    private $_filesManager = null;


   /**
     * Manager class that lets us use any file system location as a temporary files storage. We can define the root folder for our temporary storage and
     * interact with it to add, list or delete our temporary data.
     *
     * This class does not use the native OS temporary files location or management utilities. It is an independent implementation for temporary files management
     *
     * @param string $rootPath A full valid OS filesystem path to the root of a folder that we want to use as the temporary files storage
     */
    public function __construct(string $rootPath){

        $this->_rootPath = StringUtils::formatPath($rootPath, DIRECTORY_SEPARATOR);

        // Check specified folder exists
        if(!is_dir($this->_rootPath)){

            throw new UnexpectedValueException('Specified rootPath does not exist: '.$rootPath);
        }

        $this->_filesManager = new FilesManager();
    }


    /**
     * Save the specified binary string data to the current temporary storage path as a temporary file.
     *
     * @param string $binaryData The contents of the new temporary file
     * @param string $id The identifier we want to assign to the created file. Leaving it empty means an automatic id will be generated.
     *        IMPORTANT: This method returns the effective id that is used to store the file, which will NEVER be the same as the one that we specify.
     *        So we will always use the file id that is returned by this method to later read or modify the created temporary file data
     * @param number $minutesToLive The number of minutes that the file is guaranteed to be available on storage. Once it is created, after the specified number
     *               of minutes passes, the file may be deleted. (One day equals 1440 minutes)
     *
     * @return string The identifier that will be required to retrieve this file later. This will be the one that we have specified plus extra characters
     *         or an auto generated one if we didn't specify any id. In both cases, the id will start with a date value in the format Y-m-d_H-i-s_
     */
    public function addFile($binaryData, $id = '', $minutesToLive = 1440){

        if(!is_string($binaryData)){

            throw new UnexpectedValueException('binaryData must be a string');
        }

        if(!is_string($id)){

            throw new UnexpectedValueException('id must be a string');
        }

        $this->_testMinutesToLive($minutesToLive);

        // Calculate the file expiry date
        $expiryDate =  date('Y-m-d_H-i-s', strtotime('+'.$minutesToLive.' minutes'));

        // Get the definitive name for the temp file to add
        $uniqueId = $this->_filesManager->findUniqueFileName($this->_rootPath, $expiryDate.'_'.$id);

        // Store the file
        try {

            $this->_filesManager->saveFile($this->_rootPath.DIRECTORY_SEPARATOR.$uniqueId, $binaryData);

        } catch (Throwable $e) {

            throw new UnexpectedValueException('Could not create temporary file: '.$this->_rootPath.DIRECTORY_SEPARATOR.$uniqueId);
        }

        return $uniqueId;
    }


    /**
     * Get the binary data from a stored temporary file
     *
     * @param string $id Identifier for the file we want to read. This id must have been obtained via calling to this->addFile() method
     *
     * @return string The file binary contents
     */
    public function readFile($id){

        if(!is_string($id) || StringUtils::isEmpty($id)){

            throw new UnexpectedValueException('id must be a non empty string');
        }

        return $this->_filesManager->readFile($this->_rootPath.DIRECTORY_SEPARATOR.$id);
    }


    /**
     * Creates a temporary folder on the storage system.
     *
     * @param string $id The identifier we want to assign to the created folder. Leaving it empty means an automatic id will be generated.
     * @param number $minutesToLive The number of minutes that the folder is guaranteed to be available on storage. Once it is created, after the specified number of minutes
     *        passes, the folder and all its contents may be deleted. (One day equals 1440 minutes)
     *
     * @return string The identifier that will be required to retrieve this folder later. This will be the one that we have specified plus extra characters
     *         or an auto generated one if we didn't specify any id. In both cases, the id will start with a date value in the format Y-m-d_H-i-s_
     */
    public function addDirectory($id = '', $minutesToLive = 2880){

        if(!is_string($id)){

            throw new UnexpectedValueException('id must be a string');
        }

        $this->_testMinutesToLive($minutesToLive);

        // Calculate the file expiry date
        $expiryDate =  date('Y-m-d_H-i-s', strtotime('+'.$minutesToLive.' minutes'));

        // Get the definitive name for the temp dir to add
        $uniqueId = $this->_filesManager->findUniqueDirectoryName($this->_rootPath, $expiryDate.'_'.$id);

        // Store the folder
        if(!$this->_filesManager->createDirectory($this->_rootPath.DIRECTORY_SEPARATOR.$uniqueId)){

            throw new UnexpectedValueException('Could not create temporary directory: '.$this->_rootPath.DIRECTORY_SEPARATOR.$uniqueId);
        }

        return $uniqueId;
    }


    /**
     * Gives the full OS filesystem path to a temporary file, given its id.
     *
     * @param string $id The identifier for the file we want to retrieve. This id must have been obtained via calling the this->addFile() method
     *
     * @return string The requested path (including the file name) or an empty string if the file does not exist.
     */
    public function getFilePath($id){

        if(!is_string($id) || StringUtils::isEmpty($id)){

            throw new UnexpectedValueException('id must be a non empty string');
        }

        if(!file_exists($this->_rootPath.DIRECTORY_SEPARATOR.$id)){

            throw new UnexpectedValueException('Tmp file not found : '.$id);
        }

        return $this->_rootPath.DIRECTORY_SEPARATOR.$id;
    }


    /**
     * Gives the full OS filesystem path to a temporary directory, given its id.
     *
     * @param string $id The identifier for the directory we want to retrieve. This id must have been obtained via calling to this->addDirectory() method
     *
     * @return string The requested path (including the directory name) or an empty string if the directory does not exist.
     */
    public function getDirectoryPath($id){

        if(!is_string($id) || StringUtils::isEmpty($id)){

            throw new UnexpectedValueException('id must be a non empty string');
        }

        if(!is_dir($this->_rootPath.DIRECTORY_SEPARATOR.$id)){

            throw new UnexpectedValueException('Tmp dir not found : '.$id);
        }

        // Get the full path
        return $this->_rootPath.DIRECTORY_SEPARATOR.$id;
    }


    /**
     * TODO
     */
    public function cleanAllExpired(){

        // TODO
    }


    /**
     * Force the specified temporary file to be deleted, even if it's not expired yet.
     *
     * @param string $id Identifier for the file we want to delete
     *
     * @return bool True on success or false on failure
     */
    public function deleteFile($id){

        if(!is_string($id) || StringUtils::isEmpty($id)){

            throw new UnexpectedValueException('id must be a non empty string');
        }

        return $this->_filesManager->deleteFile($this->_rootPath.DIRECTORY_SEPARATOR.$id);
    }


    /**
     * Force the specified temporary folder to be deleted with all its contents, even if it's not expired yet.
     *
     * @param string $id Identifier for the directory we want to delete
     *
     * @return bool The number of files that have been deleted as part of the directory removal process. If directory does not exist
     *         or it could not be deleted, an exception will be thrown
     */
    public function deleteDirectory($id){

        if(!is_string($id) || StringUtils::isEmpty($id)){

            throw new UnexpectedValueException('id must be a non empty string');
        }

        return $this->_filesManager->deleteDirectory($this->_rootPath.DIRECTORY_SEPARATOR.$id);
    }


    /**
     * Aux method to test that the minutestolive parameter is correct
     */
    private function _testMinutesToLive($minutesToLive){

        if(!is_integer($minutesToLive) || $minutesToLive < 0){

            throw new UnexpectedValueException('minutesToLive must be a positive integer');
        }
    }
}

?>