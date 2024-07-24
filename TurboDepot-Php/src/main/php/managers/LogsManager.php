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
     * A files manager instance to use in this class
     * @var FilesManager
     */
    private $_filesManager = null;


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
        $this->_filesManager = new FilesManager();
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

            $this->_filesManager->createDirectory(StringUtils::getPath($filePath), true);
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
     * Trim log files inside the current root path to ensure they do not exceed the provided max size.
     * Files matching the provided pattern will be trimmed by removing as many lines as necessary from the top to not exceed the max size.
     *
     * @param int $maxSize The maximum allowed size for each log file in kilobytes.
     * @param string $pattern A regular expression string. Only those log files which full OS path matches the pattern will be affected.
     *        Some pattern examples:
     *        '/.*\.txt$/i' - Match all items which end with '.txt' (case insensitive)
     *        '/^some.*./' - Match all items which start with 'some'
     *        '/text/' - Match all items which contain 'text'
     *        '/^file\.txt$/' - Match all items which are exactly 'file.txt'
     *        '/^.*\.(jpg|jpeg|png|gif)$/i' - Match all items which end with .jpg,.jpeg,.png or .gif (case insensitive)
     *        '/^(?!.*\.(jpg|png|gif)$)/i' - Match all items that do NOT end with .jpg, .png or .gif (case insensitive)
     *
     * @return void
     */
    public function trimLogs(float $maxSize, string $pattern = '/.*/'){

        if($maxSize <= 0){

            throw new UnexpectedValueException('maxSize must be a positive value');
        }

        if($pattern === '' ||
           !StringUtils::isStartingWith($pattern, ['/'])  ||
           !StringUtils::isEndingWith($pattern, ['/'])){

            throw new UnexpectedValueException('pattern must be a non empty regexp');
        }

        // Recursively find all the files inside the root path
        $files = $this->_filesManager->findDirectoryItems($this->_rootPath, $pattern, 'relative', 'files', -1, '', 'absolute');

        foreach ($files as $file) {

            $this->trimLogFile($this->_rootPath.DIRECTORY_SEPARATOR.$file, $maxSize);
        }
    }


    /**
     * Helper method to trim a single log file
     *
     * @param string $filePath The full path to the log file
     * @param int $maxSize The maximum allowed size for the log file in kilobytes
     */
    private function trimLogFile(string $filePath, float $maxSize) {

        $content = "";
        $fileSize = filesize($filePath);

        $handle = fopen($filePath, 'rb+');

        // Check if file open was successful
        if (!$handle) {

            throw new UnexpectedValueException("Failed to open file to trim: $filePath");
        }

        // Acquire exclusive lock
        if (!flock($handle, LOCK_EX)) {

            fclose($handle);
            throw new UnexpectedValueException("Failed to acquire lock on file to trim: $filePath");
        }

        // Seek to the position of the data to be read
        $start = max(0, $fileSize - ($maxSize * 1024));
        fseek($handle, $start, SEEK_SET);

        // Read the last N kilobytes
        $content = fread($handle, $fileSize - $start);

        // Truncate the file
        ftruncate($handle, 0);

        // Rewind the handle to the beginning
        rewind($handle);

        // Write the read content back to the file
        fwrite($handle, $content);

        // Release the lock and close the file
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
