<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\main\php\model;

use UnexpectedValueException;
use ZipArchive;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * ZIP file data abstraction
 *
 * @see ZipObject::__construct
 */
class ZipObject {


    /**
     * Stores the internal php object zip file that will be used
     * @var ZipArchive
     */
    private $_zipFile = null;


    /**
     * @see ZipObject::isLoaded
     * @var boolean
     */
    private $_isLoaded = false;


    /**
     * Stores the handler for a temporary file that may have been created to load zip data from a binary string
     * If null, no handler has been used
     * @var resource
     */
    private $_tmpFilePointer = null;


    /**
     * ZipObject is a class to operate with compressed zip files: Open, list, add, extract, etc..
     *
     * The php zip extension must be enabled in order for this class to work
     *
     * @throws UnexpectedValueException If the ZIP extension is not enabled on the PHP runtime.
     */
    public function __construct(){

        if (!extension_loaded('zip')) {

            throw new UnexpectedValueException('ZIP extension is not enabled on this php runtime');
        }

        $this->_zipFile = new ZipArchive();
    }


    /**
     * Tells if the current object has correctly loaded a zip file or not
     *
     * @return boolean
     */
    public function isLoaded(){

        return $this->_isLoaded;
    }


    /**
     * Open a zip file from the given system path. If the operation succeeds, we will be able to start operating
     * with the zip file contents.
     *
     * @param string $path A full OS file system path that points to an actual Zip file
     *
     * @return bool True if the ZIP file is successfully loaded
     *
     * @throws UnexpectedValueException
     */
    public function loadPath(string $path){

        if(strlen($path) < 2){

            throw new UnexpectedValueException('Invalid path');
        }

        $this->close();

        $res = $this->_zipFile->open($path);

        if ($res !== true) {

            $errorMessage = '';

            switch ($res) {

                case ZipArchive::ER_EXISTS:
                    $errorMessage = 'File already exists';
                    break;

                case ZipArchive::ER_INCONS:
                    $errorMessage = 'Zip archive inconsistent';
                    break;

                case ZipArchive::ER_INVAL:
                    $errorMessage = 'Invalid argument';
                    break;

                case ZipArchive::ER_MEMORY:
                    $errorMessage = 'Malloc failure';
                    break;

                case ZipArchive::ER_NOENT:
                    $errorMessage = 'No such file';
                    break;

                case ZipArchive::ER_NOZIP:
                    $errorMessage = 'Not a zip archive';
                    break;

                case ZipArchive::ER_OPEN:
                    $errorMessage = 'Can\'t open file';
                    break;

                case ZipArchive::ER_READ:
                    $errorMessage = 'Read error';
                    break;

                case ZipArchive::ER_SEEK:
                    $errorMessage = 'Seek error';
                    break;

                default:
                    $errorMessage = 'Unknown error';
                    break;
            }

            throw new UnexpectedValueException('Could not load existing zip file path. Error code: '.$errorMessage);
        }

        return $this->_isLoaded = true;
    }


    /**
     * Loads a ZIP file into memory from a binary string.
     *
     * Due to some limitations of the zip php extension, this method is not 100% in memory. This means that it will use a
     * virtual temporary file that may be written into disk depending on the php installation or OS settings.
     *
     * @param string $data The binary string representing the ZIP file contents.
     *
     * @return bool True if the ZIP file is successfully loaded
     */
    public function loadBinary(string $data){

        if(strlen($data) < 2){

            throw new UnexpectedValueException('Invalid data');
        }

        $this->close();

        // Create a temporary file pointer
        $this->_tmpFilePointer = tmpfile();

        // Write the binary string to the temporary file
        fwrite($this->_tmpFilePointer, $data);

        // Load the ZIP file from the temporary file path
        return $this->loadPath(stream_get_meta_data($this->_tmpFilePointer)['uri']);
    }


    /**
     * Loads a ZIP file from a base 64 encoded binary string.
     *
     * @param string $base64String The base 64 encoded binary string representing the ZIP file content.
     *
     * @return bool True if the ZIP file is successfully loaded
     */
    public function loadBase64(string $base64String){

        return $this->loadBinary(base64_decode($base64String));
    }


    /**
     * Counts the number of items in the ZIP archive.
     * Notice that files and empty folders are considered items
     *
     * @return int The number of files in the ZIP archive.
     */
    public function countContents(){

        $this->_validateFileIsLoaded();

        return $this->_zipFile->numFiles;
    }


    /**
     * Lists the contents of the currently loaded ZIP archive.
     * Notice that even a file or an empty folder are considered items by the list
     *
     * @return array An array of strings with relative paths for each one of the entries inside the ZIP.
     *         for example: 'folder 1/folder 3/file 4.txt'
     *
     * @throws UnexpectedValueException If no ZIP file is loaded.
     */
    public function listContents(){

        $this->_validateFileIsLoaded();

        $list = [];

        for ($i = 0; $i < $this->_zipFile->numFiles; $i++) {

            $list[] = $this->_zipFile->getNameIndex($i);
        }

        return $list;
    }


    /**
     * Checks if the specified entry in the ZIP archive is an empty folder.
     *
     * @param string $entry The relative path of the entry to check.
     *
     * @return bool True if the entry is an empty folder, false otherwise.
     *
     * @throws UnexpectedValueException If no ZIP file is loaded.
     */
    public function isEmptyFolder(string $entry){

        $this->_validateFileIsLoaded();

        if(StringUtils::isEmpty($entry)){

            throw new UnexpectedValueException('Invalid entry path');
        }

        if($this->countContents() === 0){

            throw new UnexpectedValueException('Zip file is empty');
        }

        return StringUtils::isEndingWith($entry, ['/']);
    }


    /**
     * Checks if the specified entry in the ZIP archive is a file.
     *
     * @param string $entry The relative path of the entry to check.
     *
     * @return bool True if the entry is a file, false otherwise.
     *
     * @throws UnexpectedValueException If no ZIP file is loaded.
     */
    public function isFile(string $entry) {

        return !$this->isEmptyFolder($entry);
    }


    /**
     * Reads the data from a specified entry in the ZIP archive (an entry can only be a file or an empty directory)
     * All the information is kept in memory, no files are saved or stored, so be careful when trying to
     * read big files with this method.
     *
     * @param string $entry The relative path of the entry to read.
     *
     * @return string The data from the specified entry in the ZIP archive.
     *
     * @throws UnexpectedValueException If the entry path is empty, the ZIP file is empty, or the specified entry is an empty folder.
     */
    public function readEntry(string $entry){

        if(StringUtils::isEmpty($entry)){

            throw new UnexpectedValueException('Invalid entry path');
        }

        if($this->countContents() === 0){

            throw new UnexpectedValueException('Zip file is empty');
        }

        if($this->isEmptyFolder($entry)){

            throw new UnexpectedValueException('Trying to read an empty zip folder: '.$entry);
        }

        $data = $this->_zipFile->getFromName($entry);

        if($data === false){

            throw new UnexpectedValueException('Could not read specified zip entry: '.$entry);
        }

        return $data;
    }


    /**
     * TODO
     */
    public function addFile(){

        $this->_validateFileIsLoaded();

        // TODO
    }


    /**
     * TODO
     */
    public function extractFile(){

        $this->_validateFileIsLoaded();

        // TODO
    }


    /**
     * TODO
     */
    public function extractAll(){

        $this->_validateFileIsLoaded();

        // TODO
    }


    /**
     * Closes the currently opened zip file.
     *
     * @return void
     */
    public function close(){

        if(!$this->_isLoaded){

            return;
        }

        $this->_zipFile->close();

        if($this->_tmpFilePointer !== null){

            fclose($this->_tmpFilePointer);
        }

        $this->_tmpFilePointer = null;
        $this->_isLoaded = false;
    }


    /**
     * Checks if the internal ZIP file object is loaded.
     *
     * @throws UnexpectedValueException If no ZIP file is loaded.
     */
    private function _validateFileIsLoaded(){

        if(!$this->_isLoaded){

            throw new UnexpectedValueException('No zip file is loaded');
        }
    }
}