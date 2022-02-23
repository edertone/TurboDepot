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

use DateTime;
use UnexpectedValueException;
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * logs manager class
 */
class LogsManager extends BaseStrictClass{


    /**
     * Defines how many blank lines will be added after each write() call is performed
     *
     * @var integer
     */
    public $blankLinesBetweenLogs = 0;


    /**
     * The full filesystem path to the root of the folder where logs are stored
     * @var string
     */
    private $_rootPath = '';


    /**
     * Centralized logs manager.
     * It can be used standalone when a full depot is not necessary.
     * If we want to use it as part of a depot instance, we must interact with our logs via the DepotManager class.
     *
     * @param string $rootPath The full absolute filesystem path to the root of the folder where logs will be output
     */
    public function __construct(string $rootPath){

        if(!is_dir($rootPath)){

            throw new UnexpectedValueException('LogsManager received an invalid rootPath: '.$rootPath);
        }

        $this->_rootPath = $rootPath;
    }


    /**
     * Add text to the specified log file
     *
     * @param string $text The text to be added to the log file
     * @param string $logFile A log fine name or a relative path (based on the currently loaded logs root path) where the text will be written
     * @param bool $writeDateAndTime If set to true, each log entry that is saved with write() method will start with the current date and time
     * @param bool $createFile If set to true and the target log file does not exist, it will be created. If set to false and it does not exist, an exception will be thrown
     *
     * @return void
     */
    public function write(string $text, string $logFile, bool $writeDateAndTime = true, bool $createFile = true){

        $filePath = StringUtils::formatPath($this->_rootPath.DIRECTORY_SEPARATOR.$logFile, DIRECTORY_SEPARATOR);

        if(StringUtils::isEmpty($logFile)){

            throw new UnexpectedValueException('logFile must not be empty');
        }

        if(!is_file($filePath) && !$createFile){

            throw new UnexpectedValueException('Log file does not exist and createFile is false: '.$logFile);
        }

        // Create the subfolder if necessary
        if(!is_dir(StringUtils::getPath($filePath))){

            (new FilesManager())->createDirectory(StringUtils::getPath($filePath), true);
        }

        // Open a pointer to the end of the file to append the new text
        // If the file does not exist, it will be created
        $filePointer = fopen($filePath, 'a+');

        if($filePointer === false){

            throw new UnexpectedValueException('Could not access the log file: '.$logFile);
        }

        // Acquire exclusive lock. If any other process is already writting to this file and has it locked,
        // this method will wait till the lock is released
        if(flock($filePointer, LOCK_EX)) {

            $blankLines = "\n";

            for ($i = 0; $i < $this->blankLinesBetweenLogs; $i++) {

                $blankLines .= "\n";
            }

            $date = $writeDateAndTime ? DateTime::createFromFormat('U.u', microtime(true))->format("Y-m-d H:i:s.u").' ' : '';

            fwrite($filePointer, $date.$text.$blankLines);

            // flush output before releasing the lock
            fflush($filePointer);

            // Release the lock so other processes can write to the file
            flock($filePointer, LOCK_UN);

        } else {

            throw new UnexpectedValueException('Could not lock the log file: '.$logFile);
        }

        fclose($filePointer);
    }


    /**
     * TODO
     */
    public function truncate(string $logFile){

    }
}

?>