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

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use DirectoryIterator;
use UnexpectedValueException;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbocommons\src\main\php\utils\ArrayUtils;
use org\turbocommons\src\main\php\utils\ConversionUtils;


/**
 * Files manager class
 */
class FilesManager extends BaseStrictClass{


    /**
     * @see FilesManager::__construct
     * @var string
     */
    protected $_rootPath = '';


    /**
     * Manager class that contains the most common file system interaction functionalities
     *
     * @param string $rootPath If we want to use an existing directory as the base path for all the methods on this class, we can define here
     *        a full OS filesystem path to it. Setting this value means all the file operations will be based on that directory.
     */
    public function __construct($rootPath = ''){

        if (!is_string($rootPath)){

            throw new UnexpectedValueException('rootPath must be a string');
        }

        $this->_rootPath = StringUtils::formatPath($rootPath);

        if($this->_rootPath !== '' && !is_dir($this->_rootPath)){

            throw new UnexpectedValueException('Specified rootPath does not exist: '.$rootPath);
        }
    }


    /**
     * Gives us the current OS directory separator character, so we can build cross platform file paths
     *
     * @return string The current OS directory separator character
     */
    public function dirSep(){

        return DIRECTORY_SEPARATOR;
    }


    /**
     * Tells if the provided string represents a relative or absolute file system path (Windows or Linux).
     *
     * Note that this method doesn't check if the path is valid or points to an existing file or directory.
     *
     * @return boolean True if the provided path is absolute, false if it is relative
     */
    public function isPathAbsolute($path){

        if (is_string($path)){

            $len = strlen($path);
            $startsWithAlpha = $len > 0 && ctype_alpha($path[0]);

            return strspn($path, '/\\', 0, 1) ||
                ($len === 2 && $startsWithAlpha && ':' === $path[1]) ||
                ($len > 2 && $startsWithAlpha && ':' === $path[1] && strspn($path, '/\\', 2, 1));
        }

        throw new UnexpectedValueException('path must be a string');
    }


    /**
     * Check if the specified path is a file or not.
     *
     * @param string $path An Operating system absolute or relative path to test
     *
     * @return bool true if the path exists and is a file, false otherwise.
     */
    public function isFile($path){

        if (!is_string($path)){

            throw new UnexpectedValueException('path must be a string');
        }

        try {

            clearstatcache();

            return is_file($this->_composePath($path));

        } catch (Exception $e) {

            return false;
        }
    }


    /**
     * Check if two provided files are identical
     *
     * @param string $pathToFile1 Absolute or relative path to the first file to compare
     * @param string $pathToFile2 Absolute or relative path to the second file to compare
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True if both files are identical, false otherwise
     */
    public function isFileEqualTo($pathToFile1, $pathToFile2){

        $pathToFile1 = $this->_composePath($pathToFile1, false, true);
        $pathToFile2 = $this->_composePath($pathToFile2, false, true);

        if (filesize($pathToFile1) !== filesize($pathToFile2)){

            return false;
        }

        $chunksize = 4096;
        $fp_a = fopen($pathToFile1, 'rb');
        $fp_b = fopen($pathToFile2, 'rb');

        while (!feof($fp_a) && !feof($fp_b)) {

            $d_a = fread($fp_a, $chunksize);
            $d_b = fread($fp_b, $chunksize);

            if ($d_a === false || $d_b === false || $d_a !== $d_b) {

                fclose($fp_a);
                fclose($fp_b);

                return false;
            }
        }

        fclose($fp_a);
        fclose($fp_b);

        return true;
    }


    /**
     * Check if the specified path is a directory or not.
     *
     * @param string $path An Operating system absolute or relative path to test
     *
     * @return bool true if the path exists and is a directory, false otherwise.
     */
    public function isDirectory($path){

        if (!is_string($path)){

            throw new UnexpectedValueException('path must be a string');
        }

        $path = $this->_composePath($path);

        if(StringUtils::isEmpty($path)){

            return false;
        }

        try {

            clearstatcache();

            return is_dir($path);

        } catch (Exception $e) {

            return false;
        }
    }


    /**
     * Check if two directories contain exactly the same folder structure and files.
     *
     * @param string $path1 Absolute or relative path to the first directory to compare
     * @param string $path2 Absolute or relative path to the second directory to compare
     *
     * @return bool true if both paths are valid directories and contain exactly the same files and folders tree.
     */
    public function isDirectoryEqualTo($path1, $path2){

        $path1 = $this->_composePath($path1);
        $path2 = $this->_composePath($path2);

        $path1Items = $this->getDirectoryList($path1, 'nameAsc');
        $path2Items = $this->getDirectoryList($path2, 'nameAsc');

        // Both paths must be exactly the same
        if(!ArrayUtils::isEqualTo($path1Items, $path2Items)){

            return false;
        }

        for ($i = 0, $l = count($path1Items); $i < $l; $i++) {

            $item1Path = $path1.DIRECTORY_SEPARATOR.$path1Items[$i];
            $item2Path = $path2.DIRECTORY_SEPARATOR.$path2Items[$i];
            $isItem1ADir = is_dir($item1Path);

            if($isItem1ADir && !$this->isDirectoryEqualTo($item1Path, $item2Path)){

                return false;
            }

            if (!$isItem1ADir && !$this->isFileEqualTo($item1Path, $item2Path)){

                return false;
            }
        }

        return true;
    }


    /**
     * Checks if the specified folder is empty
     *
     * @param string $path Absolute or relative path to the directory we want to check
     *
     * @return boolean True if directory is empty, false if not. If it does not exist or cannot be read, an exception will be generated
     */
    public function isDirectoryEmpty($path) {

        return count($this->getDirectoryList($path)) <= 0;
    }


    /**
     * Count elements on the specified directory based on their type or specific match with regular expressions.
     * With this method you can count files, directories, both or any items that match more complex regular expressions.
     *
     * @see FilesManager::findDirectoryItems
     *
     * @param string $path Absolute or relative path where the counting will be performed
     *
     * @param string $searchItemsType Defines the type for the directory elements to count: 'files' to count only files, 'folders'
     *        to count only folders, 'both' to count on all the directory contents
     *
     * @param int $depth Defines the maximum number of subfolders where the count will be performed:<br>
     *        - If set to -1 the count will be performed on the whole folder contents<br>
     *        - If set to 0 the count will be performed only on the path root elements<br>
     *        - If set to 2 the count will be performed on the root, first and second depth level of subfolders
     *
     * @param string $searchRegexp A regular expression that files or folders must match to be included
     *        into the results. See findDirectoryItems() docs for pattern examples<br>
     *
     * @param string $excludeRegexp A regular expression that will exclude all the results that match it from the count
     *
     * @return number The total number of elements that match the specified criteria inside the specified path
     */
    public function countDirectoryItems($path,
                                        string $searchItemsType = 'both',
                                        int $depth = -1,
                                        string $searchRegexp = '/.*/',
                                        string $excludeRegexp = ''){

        return count($this->findDirectoryItems($path, $searchRegexp, 'relative', $searchItemsType, $depth, $excludeRegexp));
    }


    /**
     * Find all the elements on a directory that match a specific regexp pattern
     *
     * @param string $path Absolute or relative path where the search will be performed
     *
     * @param string $searchRegexp A regular expression that files or folders must match to be included
     *        into the results (Note that search is dependant on the $searchMode parameter to search only in the item name or the full path).
     *        Here are some useful patterns:<br>
     *        '/.*\.txt$/i' - Match all items which end with '.txt' (case insensitive)<br>
     *        '/^some.*./' - Match all items which start with 'some'<br>
     *        '/text/'  - Match all items which contain 'text'<br>
     *        '/^file\.txt$/' - Match all items which are exactly 'file.txt'<br>
     *        '/^.*\.(jpg|jpeg|png|gif)$/i' - Match all items which end with .jpg,.jpeg,.png or .gif (case insensitive)<br>
     *        '/^(?!.*\.(jpg|png|gif)$)/i' - Match all items that do NOT end with .jpg, .png or .gif (case insensitive)
     *
     * @param string $returnFormat Defines how the array of results will be returned. 4 values are possible:<br>
     *        'relative' - Each result element will contain the path relative to the search root including the file (with extension) or folder name<br>
     *        'absolute' - Each result element will contain the full OS absolute path including the file (with extension) or folder name<br>
     *        'name' - Each result element will contain its file (with extension) or folder name<br>
     *        'name-noext' - Each result element will contain its file (without extension) or folder name
     *
     * @param string $searchItemsType Defines the type for the directory elements to search: 'files' to search only files, 'folders'
     *        to search only folders, 'both' to search on all the directory contents
     *
     * @param int $depth Defines the maximum number of subfolders where the search will be performed:<br>
     *        - If set to -1 (default) the search will be performed on the whole folder contents<br>
     *        - If set to 0 the search will be performed only on the path root elements<br>
     *        - If set to N the search will be performed on the root, first and N depth level of subfolders
     *
     * @param string $excludeRegexp A regular expression that will exclude all the results that match when tested against the item full OS absolute path
     *
     * @param string $searchMode Defines how $searchRegexp will be used to find matches:
     *        - If set to 'name' (default) The regexp will be tested only against the file or folder name<br>
     *        - If set to 'absolute' The regexp will be tested against the full OS absolute path of the file or folder<br>
     *
     * @return array A list formatted as defined in returnFormat, with all the elements that meet the search criteria, sorted ascending
     */
    public function findDirectoryItems($path,
                                       string $searchRegexp,
                                       string $returnFormat = 'relative',
                                       string $searchItemsType = 'both',
                                       int $depth = -1,
                                       string $excludeRegexp = '',
                                       string $searchMode = 'name'){

       $path = $this->_composePath($path);
       $result = [];

       // Create a recursive directory iterator
       $iterator = new RecursiveIteratorIterator(

           new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
           RecursiveIteratorIterator::SELF_FIRST
           );

       // Set maximum depth if specified
       if ($depth >= 0) {

           $iterator->setMaxDepth($depth);
       }

       // Calculate the length of the base path for relative path calculations
       $pathLen = strlen($path) + 1;

       // Iterate through all items in the directory
       foreach ($iterator as $item) {

           $itemPath = $item->getPathname();
           $relativePath = substr($itemPath, $pathLen);

           // Determine which path to use for searching based on the search mode
           $searchOn = $searchMode === 'absolute' ? $itemPath : $item->getFilename();

           // Skip items that match the exclude pattern
           if ($excludeRegexp !== '' && preg_match($excludeRegexp, $itemPath)) {

               continue;
           }

           $isDir = $item->isDir();

           // Skip items that don't match the search type (files or folders)
           if (($searchItemsType === 'folders' && !$isDir) || ($searchItemsType === 'files' && $isDir)) {

               continue;
           }

           // Skip items that don't match the search pattern
           if (!preg_match($searchRegexp, $searchOn)) {

               continue;
           }

           // Add matching items to the result array in the specified format
           switch ($returnFormat) {

               case 'relative':
                   $result[] = $relativePath;
                   break;

               case 'absolute':
                   $result[] = $itemPath;
                   break;

               case 'name':
                   $result[] = $item->getFilename();
                   break;

               case 'name-noext':
                   $result[] = pathinfo($item->getFilename(), PATHINFO_FILENAME);
                   break;

               default:
                   throw new UnexpectedValueException("Invalid returnFormat: $returnFormat");
           }
       }

       // Sort the results alphabetically
       sort($result);

       return $result;
    }


    /**
     * Search for a folder name that does not exist on the provided path.
     *
     * If we want to create a new folder inside another one without knowing for sure what does it contain, this method will
     * guarantee us that we have a unique directory name that does not collide with any other folder or file that currently
     * exists on the path.
     *
     * NOTE: This method does not create any folder or alter the given path in any way.
     *
     * @param string $path Absolute or relative path to the directoy we want to check for a non existant folder name
     * @param string $desiredName This is the folder name that we would like to be available on the provided path. This method will verify
     *        that it does not exist, or otherwise give us a name based on the desired one that is available on the path. If we provide
     *        here an empty value, the method will take care of providing the non existant directory name we need.
     * @param string $text Text that will be appended to the suggested name in case it already exists.
     *        For example: text='copy' will generate a result like 'NewFolder-copy' or 'NewFolder-copy-1' if a folder named 'NewFolder' exists
     * @param string $separator String that will be used to join the suggested name with the text and the numeric file counter.
     *        For example: separator='---' will generate a result like 'NewFolder---copy---1' if a folder named 'NewFolder' already exists
     * @param string $isPrefix Defines if the extra text that will be appended to the desired name will be placed after or before the name on the result.
     *        For example: isPrefix=true will generate a result like 'copy-1-NewFolder' if a folder named 'NewFolder' already exists
     *
     * @return string A directory name that can be safely created on the specified path, cause no one exists with the same name
     *         (No path is returned by this method, only a directory name. For example: 'folder-1', 'directoryName-5', etc..).
     */
    public function findUniqueDirectoryName($path,
                                            $desiredName = '',
                                            $text = '',
                                            $separator = '-',
                                            bool $isPrefix = false){

        if(!is_string($path)){

            throw new UnexpectedValueException('path must be a string');
        }

        if(!is_string($desiredName)){

            throw new UnexpectedValueException('desiredName must be a string');
        }

        if(!is_string($text)){

            throw new UnexpectedValueException('text must be a string');
        }

        if(!is_string($separator)){

            throw new UnexpectedValueException('separator must be a string');
        }

        $path = $this->_composePath($path, true);

        $i = 1;
        $result = StringUtils::isEmpty($desiredName) ? (string)$i : $desiredName;

        while(is_dir($path.DIRECTORY_SEPARATOR.$result) ||
              is_file($path.DIRECTORY_SEPARATOR.$result)){

            $result = $this->_generateUniqueNameAux($i, $desiredName, $text, $separator, $isPrefix);

            $i++;
        }

        return $result;
    }


    /**
     * Search for a file name that does not exist on the provided path.
     *
     * If we want to create a new file inside a folder without knowing for sure what does it contain, this method will
     * guarantee us that we have a unique file name that does not collide with any other folder or file that currently
     * exists on the path.
     *
     * NOTICE: This method does not create any file or alter the given path in any way.
     *
     * @param string $path Absolute or relative path to the directoy we want to check for a unique file name
     * @param string $desiredName We can specify a suggested name for the unique file. This method will verify that it
     *                            does not exist, or otherwise give us a name based on our desired one that is unique for the path
     * @param string $text Text that will be appended to the suggested name in case it already exists.
     *                     For example: text='copy' will generate a result like 'NewFile-copy' or 'NewFile-copy-1' if a file named 'NewFile' exists
     * @param string $separator String that will be used to join the suggested name with the text and the numeric file counter.
     *                          For example: separator='---' will generate a result like 'NewFile---copy---1' if a file named 'NewFile' already exists
     * @param string $isPrefix Defines if the extra text that will be appended to the desired name will be placed after or before the name on the result.
     *                         For example: isPrefix=true will generate a result like 'copy-1-NewFile' if a file named 'NewFile' already exists
     *
     * @return string A file name that can be safely created on the specified path, cause no one exists with the same name
     *                (No path is returned by this method, only a file name. For example: 'file-1', 'fileName-5', etc..).
     */
     public function findUniqueFileName(string $path,
                                        string $desiredName = '',
                                        string $text = '',
                                        string $separator = '-',
                                        bool $isPrefix = false){

        $i = 1;
        $result = ($desiredName == '' ? $i : $desiredName);

        $path = $this->_composePath($path, true);

        $extension = StringUtils::getPathExtension($desiredName);

        if($extension !== ''){

            $extension = '.'.$extension;
        }

        while(is_dir($path.DIRECTORY_SEPARATOR.$result) ||
              is_file($path.DIRECTORY_SEPARATOR.$result)){

            $result = $this->_generateUniqueNameAux($i, StringUtils::getPathElementWithoutExt($desiredName), $text, $separator, $isPrefix).$extension;

            $i++;
        }

        return $result;
    }


    /**
     * Create a directory at the specified filesystem path
     *
     * @param string $path Absolute or relative path to the directoy we want to create. For example: c:\apps\my_new_folder
     * @param bool $recursive Allows the creation of nested directories specified in the path. Defaults to false.
     *
     * @throws Throwable An exception will be thrown if a file exists with the same name or folder cannot be created (If the folder already
     *         exists, no exception will be thrown).
     *
     * @return boolean True on success or false if the folder already exists.
     */
    public function createDirectory($path, bool $recursive = false){

        if(!is_string($path) || StringUtils::isEmpty($path)){

            throw new UnexpectedValueException('Path must be a non empty string');
        }

        // Test for not allowed chars * " < > | ?
        if(preg_match('/[*"<>|?\r\n]/', $path)) {

            throw new UnexpectedValueException('Forbidden * " < > | ? chars found in path: '.$path);
        }

        $path = $this->_composePath($path);

        // If folder already exists we won't create it
        if(is_dir($path)){

            return false;
        }

        // If specified folder exists as a file, exception will happen
        if(is_file($path)){

            throw new UnexpectedValueException('specified path is an existing file '.$path);
        }

        // Create the requested folder
        try{

            mkdir($path, 0755, $recursive);

        }catch(Throwable $e){

            // It is possible that multiple concurrent calls create the same folder at the same time.
            // We will ignore those exceptions cause there's no problen with this situation, the first of the calls creates it and we are ok with it.
            // But if the folder to create does not exist at the time of catching the exception, we will throw it, cause it will be another kind of error.
            if(!is_dir($path)){

                throw new UnexpectedValueException($e->getMessage().' '.$path);
            }

            return false;
        }

        return true;
    }


    /**
     * Obtain the full path to the current operating system temporary folder location.
     * It will be correctly formated and without any trailing separator character.
     */
    public function getOSTempDirectory(){

        return StringUtils::formatPath(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
    }


    /**
     * Create a TEMPORARY directory on the operating system tmp files location, and get us the full path to access it.
     * OS should take care of its removal but it is not assured, so it is recommended to make sure all the tmp data is deleted after
     * using it (This is specially important if the tmp folder contains sensitive data).
     *
     * @param string $desiredName A name we want for the new directory to be created. If name exists on the system temporary folder, a unique one
     *               (based on the desired one) will be generated automatically. We can also leave this value empty to let the method
     *               calculate it.
     * @param boolean $deleteOnExecutionEnd Defines if the generated temp folder must be deleted after the current application execution finishes.
     *                Note that when files inside the folder are still used by the app or OS, exceptions or problems may happen,
     *                and it is not 100% guaranteed that the folder will be always deleted. So it is better to always handle the
     *                temporary folder removal in our code by ourselves
     *
     * @return string The full path to the newly created temporary directory, including the directory itself (without a trailing slash).
     *                For example: C:\Users\Me\AppData\Local\Temp\MyDesiredName
     */
    public function createTempDirectory($desiredName, $deleteOnExecutionEnd = true) {

        $tempRoot = $this->getOSTempDirectory();

        $tempDirectory = $tempRoot.DIRECTORY_SEPARATOR.$this->findUniqueDirectoryName($tempRoot, $desiredName);

        if(!$this->createDirectory($tempDirectory)){

            throw new UnexpectedValueException('Could not create TMP directory '.$tempDirectory);
        }

        // Add a shutdown function to try to delete the file when the current script execution ends
        if($deleteOnExecutionEnd){

            self::$_tempDirectoriesToDelete[] = $tempDirectory;

            // Note that as _tempDirectoriesToDelete is a static property shared by all the FilesManager instances,
            // Only one register_shutdown_function will be attached. This way we prevent possible memory leaks by
            // Letting only the first FilesManager instance to be the responsible of cleaning the temporary folders at application end.
            if(count(self::$_tempDirectoriesToDelete) < 2){

                register_shutdown_function(function () {

                    foreach (self::$_tempDirectoriesToDelete as $temp) {

                        if($this->isDirectory($temp)){

                            $this->deleteDirectory($temp);
                        }
                    }
                });
            }
        }

        return $tempDirectory;
    }


    /**
     * Aux property that globally stores the list of all paths to temporary folders that must be removed when application execution ends.
     * This is defined static so only one shared property exists for all the FilesManager instances, and therefore we prevent memory leaks
     * by using also a single register_shutdown_function listener
     */
    private static $_tempDirectoriesToDelete = [];


    /**
     * Gives the list of items that are stored on the specified folder. It will give files and directories, and each element will be the item name, without the path to it.
     * The contents of any subfolder will not be listed. We must call this method for each child folder if we want to get it's list.
     * (The method ignores the . and .. items if exist).
     *
     * @param string $path Absolute or relative path to the directory we want to list
     * @param string $sort Specifies the sort for the result:<br>
     * &emsp;&emsp;'' will not sort the result.<br>
     * &emsp;&emsp;'nameAsc' will sort the result by filename ascending.
     * &emsp;&emsp;'nameDesc' will sort the result by filename descending.
     * &emsp;&emsp;'mDateAsc' will sort the result by modification date ascending.
     * &emsp;&emsp;'mDateDesc' will sort the result by modification date descending.
     *
     * @return array The list of item names inside the specified path sorted as requested, or an empty array if no items found inside the folder.
     */
    public function getDirectoryList($path, string $sort = ''){

        $path = $this->_composePath($path, true);

        // Get all the folder contents
        $result = [];
        $sortRes = [];

        foreach (new DirectoryIterator($path) as $fileInfo){

            if(!$fileInfo->isDot()){

                switch($sort) {

                    case 'mDateAsc':
                    case 'mDateDesc':
                        $sortRes[$fileInfo->getMTime()] = $fileInfo->getFilename();
                        break;

                    default:
                        $result[] = $fileInfo->getFilename();
                        break;
                }
            }
        }

        // Apply result sorting as requested
        switch($sort) {

            case 'nameAsc':
                sort($result, SORT_NATURAL | SORT_FLAG_CASE);
                break;

            case 'nameDesc':
                rsort($result, SORT_NATURAL | SORT_FLAG_CASE);
                break;

            case 'mDateAsc':
                ksort($sortRes);
                foreach ($sortRes as $value) {

                    $result[] = $value;
                }
                break;

            case 'mDateDesc':
                krsort($sortRes);
                foreach ($sortRes as $value) {

                    $result[] = $value;
                }
                break;

            default:
                if($sort !== ''){

                    throw new UnexpectedValueException('Unknown sort method');
                }
        }

        return $result;
    }


    /**
     * Calculate the full size in bytes for a specified folder and all its contents.
     *
     * @param string $path Absolute or relative path to the directory we want to calculate its size
     *
     * @return int the size of the directory in bytes. An exception will be thrown if value cannot be obtained
     */
    public function getDirectorySize(string $path){

        $path = $this->_composePath($path);

        $result = 0;

        foreach ($this->getDirectoryList($path) as $fileOrDir){

            $fileOrDirPath = $path.DIRECTORY_SEPARATOR.$fileOrDir;

            $result += is_dir($fileOrDirPath) ?
                $this->getDirectorySize($fileOrDirPath) :
                $this->getFileSize($fileOrDirPath);
        }

        return $result;
    }


    /**
     * Copy all the contents from a source directory to a destination one (Both source and destination paths must exist).
     *
     * Any source files that exist on destination will be overwritten without warning.
     * Files that exist on destination but not on source won't be modified, removed or altered in any way.
     *
     * @param string $sourcePath Absolute or relative path to the source directory where files and folders to copy exist
     * @param string $destPath Absolute or relative path to the destination directory where files and folders will be copied
     * @param boolean $destMustBeEmpty if set to true, an exception will be thrown if the destination directory is not empty.
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True if copy was successful, false otherwise
     */
    public function copyDirectory(string $sourcePath, string $destPath, $destMustBeEmpty = true){

        $sourcePath = $this->_composePath($sourcePath);
        $destPath = $this->_composePath($destPath);

        if($sourcePath === $destPath){

            throw new UnexpectedValueException('cannot copy a directory into itself: '.$sourcePath);
        }

        if($destMustBeEmpty && !$this->isDirectoryEmpty($destPath)){

            throw new UnexpectedValueException('destPath must be empty');
        }

        foreach ($this->getDirectoryList($sourcePath) as $sourceItem){

            $sourceItemPath = $sourcePath.DIRECTORY_SEPARATOR.$sourceItem;
            $destItemPath = $destPath.DIRECTORY_SEPARATOR.$sourceItem;

            if(is_dir($sourceItemPath)){

                if(!$this->isDirectory($destItemPath) && !$this->createDirectory($destItemPath)){

                    return false;
                }

                if(!$this->copyDirectory($sourceItemPath, $destItemPath, $destMustBeEmpty)){

                    return false;
                }

            }else{

                if(!$this->copyFile($sourceItemPath, $destItemPath)){

                    return false;
                }
            }
        }

        return true;
    }


    /**
     * This method performs a one way sync process which consists in applying the minimum modifications to the destination path
     * that will guarantee that it is an exact copy of the source path. Any files or folders that are identical on both provided paths
     * will be left untouched
     *
     * @param string $sourcePath Absolute or relative path to the source directory where files and folders to mirror exist
     * @param string $destPath Absolute or relative path to the destination directory that will be modified to exactly match the source one
     * @param int $timeout The amount of seconds that this method will be trying to delete or modify a file in case it is blocked
     *            by the OS or temporarily not accessible. If the file can't be deleted after the given amount of seconds, an exception
     *            will be thrown.
     *
     * @throws UnexpectedValueException In case any of the necessary file operations fail
     *
     * @return boolean True on success
     */
    public function mirrorDirectory($sourcePath, $destPath, $timeout = 15){

        $sourcePath = $this->_composePath($sourcePath, true);
        $destPath = $this->_composePath($destPath, true);

        if($sourcePath === $destPath){

            throw new UnexpectedValueException('cannot mirror a directory into itself: '.$sourcePath);
        }

        // Get the full list of source items to mirror
        $sourceItems = $this->getDirectoryList($sourcePath);

        // Loop all source Items. If not found on destination, we will mirror them.
        foreach ($sourceItems as $sourceItem) {

            $sourceItemPath = $sourcePath.DIRECTORY_SEPARATOR.$sourceItem;
            $destItemPath = $destPath.DIRECTORY_SEPARATOR.$sourceItem;

            if (is_dir($sourceItemPath)) {

                // If a file exists with the same name, it will be removed
                if (is_file($destItemPath)) {

                    $this->deleteFile($destItemPath, $timeout);
                }

                // If a folder exists with the same name, we must verify that it is equal by calling mirror inside it.
                // Otherwise, we will directly copy the source directory
                if (is_dir($destItemPath)) {

                    $this->mirrorDirectory($sourceItemPath, $destItemPath, $timeout);

                }else{

                    $this->createDirectory($destItemPath);
                    $this->copyDirectory($sourceItemPath, $destItemPath, true);
                }

            } else {

                // If a dir exists with the same name, it will be removed
                if (is_dir($destItemPath)) {

                    $this->deleteDirectory($destItemPath, true, $timeout);
                }

                // If no file exists or it contains different data, the source file will be copied to destination
                if((!is_file($destItemPath) || !$this->isFileEqualTo($sourceItemPath, $destItemPath)) &&
                    !$this->copyFile($sourceItemPath, $destItemPath)) {

                    throw new UnexpectedValueException('Could not copy file from source <' . $sourceItemPath . '> to destination <' . $destItemPath . '>');
                }
            }
        }

        // get all destination items, and substract the source items.
        // Any element that still appears on the list must be removed
        $destinationItemsToRemove = array_diff($this->getDirectoryList($destPath), $sourceItems);

        foreach ($destinationItemsToRemove as $destItem) {

            $destItemPath = $destPath.DIRECTORY_SEPARATOR.$destItem;

            if (is_dir($destItemPath)) {

                $this->deleteDirectory($destItemPath, true, $timeout);

            } else {

                $this->deleteFile($destItemPath, $timeout);
            }
        }

        return true;
    }


    /**
     * TODO implement this method
     */
    public function syncDirectories(string $path1, string $path2){

        // TODO - this method will modify both directories so they merge all files from one side to the other
    }


    /**
     * Renames a directory.
     *
     * @param string $sourcePath Absolute or relative path to the source directory that must be renamed (including the directoy itself).
     * @param string $destPath Absolute or relative path to the new directoy name (including the directoy itself). It must not exist.
     * @param int $timeout The amount of seconds that this method will be trying to rename the specified directory in case it is blocked
     *            by the OS or temporarily not accessible. If the directory can't be renamed after the given amount of seconds, an exception
     *            will be thrown.
     *
     * @return boolean True on success
     */
    public function renameDirectory($sourcePath, $destPath, int $timeout = 15){

        return $this->_renameFSResource($this->_composePath($sourcePath, true), $this->_composePath($destPath), $timeout);
    }


    /**
     * Aux method that is used by renameFile and renameDirectory to rename a file or folder after their specific checks have been performed
     *
     * @param string $sourcePath Source path for the resource to rename
     * @param string $destPath Dest path for the resource to rename
     * @param int $timeout Amount of seconds to wait if not possible
     */
    private function _renameFSResource($sourcePath, $destPath, $timeout){

        if($this->isDirectory($destPath) || $this->isFile($destPath)){

            throw new UnexpectedValueException('Invalid destination: '.$destPath);
        }

        if(realpath(StringUtils::getPath($sourcePath)) !== realpath(StringUtils::getPath($destPath))){

            throw new UnexpectedValueException('Source and dest must be on the same path');
        }

        $passedTime = 0;
        $startTime = time();

        do {

            if(rename($sourcePath, $destPath) === true){

                return true;
            }

            $passedTime = time() - $startTime;

        } while ($passedTime < $timeout);

        throw new UnexpectedValueException("Error renaming ($passedTime seconds timeout):\n$sourcePath\n".error_get_last()['message']);
    }


    /**
     * Delete a directory from the filesystem and all its contents (folders and files).
     *
     * @param string $path Absolute or relative path to the directory that will be removed
     * @param string $deleteDirectoryItself Set it to true if the specified directory must also be deleted.
     * @param int $timeout The amount of seconds that this method will be trying to perform a delete operation in case it is blocked
     *            by the OS or temporarily not accessible. If the operation can't be performed after the given amount of seconds,
     *            an exception will be thrown.
     *
     * @return int The number of files that have been deleted as part of the directory removal process. If directory is empty or ContainsElement
     *         only folders, 0 will be returned even if many directories are deleted. If directory does not exist or it could not be deleted,
     *         an exception will be thrown
     */
    public function deleteDirectory(string $path, bool $deleteDirectoryItself = true, $timeout = 15){

        $deletedFilesCount = 0;
        $path = $this->_composePath($path, true);

        $dirIterator = new DirectoryIterator($path);

        foreach ($dirIterator as $fileInfo){

            if(!$fileInfo->isDot()){

                if(is_dir($path.DIRECTORY_SEPARATOR.$fileInfo->getFilename())){

                    $deletedFilesCount += $this->deleteDirectory($path.DIRECTORY_SEPARATOR.$fileInfo->getFilename(), true, $timeout);

                }else{

                    $this->deleteFile($path.DIRECTORY_SEPARATOR.$fileInfo->getFilename(), $timeout);

                    $deletedFilesCount ++;
                }
            }
        }

        // Only for windows, to prevent folder deleting permision error
        unset($fileInfo);
        unset($dirIterator);

        if($deleteDirectoryItself) {

            $passedTime = 0;
            $deleteStartTime = time();

            do {

                if(rmdir($path) === true){

                    return $deletedFilesCount;
                }

                $passedTime = time() - $deleteStartTime;

            } while ($passedTime < $timeout);

            throw new UnexpectedValueException("Could not delete directory itself ($passedTime seconds timeout):\n$path\n".error_get_last()['message']);
        }

        return $deletedFilesCount;
    }


    /**
     * Writes the specified data to a physical file, which will be created (if it does not exist) or overwritten without warning.
     * This method can be used to create a new empty file, a new file with any contents or to overwrite an existing one.
     *
     * We must check for file existence before executing this method if we don't want to inadvertently replace existing files.
     *
     * @see FilesManager::isFile
     *
     * @param string $pathToFile Absolute or relative path including full filename where data will be saved. File will be created or overwritten without warning.
     * @param string $data Any information to save on the file.
     * @param boolean $append Set it to true to append the data to the end of the file instead of overwritting it. File will be created if it does
     *        not exist, even with append set to true.
     * @param boolean $createDirectories If set to true, all necessary non existant directories on the provided file path will be also created.
     *
     * @return True on success or false on failure.
     */
    public function saveFile($pathToFile, $data = '', bool $append = false, bool $createDirectories = false){

        $pathToFile = $this->_composePath($pathToFile);

        if($createDirectories){

            $this->createDirectory(StringUtils::getPath($pathToFile), true);
        }

        $filePointer = fopen($pathToFile, $append ? 'a' : 'w');

        if($filePointer === false){

            throw new UnexpectedValueException('Could not write to file: '.$pathToFile);
        }

        // Acquire exclusive lock. If any other process is already writting to this file and has it locked,
        // this method will wait till the lock is released
        if(flock($filePointer, LOCK_EX)) {

            fwrite($filePointer, $data);

            // flush output before releasing the lock
            fflush($filePointer);

            // Release the lock so other processes can write to the file
            flock($filePointer, LOCK_UN);

        } else {

            throw new UnexpectedValueException('Could not lock file: '.$pathToFile);
        }

        return fclose($filePointer);
    }


    /** TODO */
    public function createTempFile(){

    }


    /**
     * Concatenate all the provided files, one after the other, into a single destination file.
     *
     * @param array $sourcePaths A list with the absolute or relative paths to the files we want to join. The result will be generated in the same order.
     * @param string $destFile The full path where the merged file will be stored, including the full file name (will be overwitten if exists).
     * @param string $separator An optional string that will be concatenated between each file content. We can for example use "\n\n" to
     *        create some empty space between each file content
     *
     * @return bool True on success or false on failure.
     */
    public function mergeFiles(array $sourcePaths, string $destFile, $separator = ''){

        $mergedData = '';

        for ($i = 0, $l = count($sourcePaths); $i < $l; $i++) {

            $mergedData .= $this->readFile($this->_composePath($sourcePaths[$i]));

            // Place separator string on all files except the last one
            if($i < $l - 1 && $separator !== ''){

                $mergedData .= $separator;
            }
        }

        return $this->saveFile($destFile, $mergedData);
    }


    /**
     * Get the size from a file
     *
     * @param string $pathToFile Absolute or relative file path, including the file name and extension
     *
     * @return int the size of the file in bytes. An exception will be thrown if value cannot be obtained
     */
    public function getFileSize(string $pathToFile){

        $pathToFile = $this->_composePath($pathToFile, false, true);

        $fileSize = filesize($pathToFile);

        if($fileSize === false){

            throw new UnexpectedValueException('Error reading file size');
        }

        return $fileSize;
    }


    /**
     * Get the time when a file has been last modified
     *
     * @param string $pathToFile Absolute or relative file path, including the file name and extension
     *
     * @return int A Unix timestamp containing the data when the file was last modified. An exception will be thrown if value cannot be obtained
     */
    public function getFileModificationTime(string $pathToFile){

        $pathToFile = $this->_composePath($pathToFile, false, true);

        clearstatcache();

        $modificationDate = filemtime($pathToFile);

        if($modificationDate === false){

            throw new UnexpectedValueException('Error reading file modification date');
        }

        return $modificationDate;
    }


    /**
     * Read and return the content of a file. Not suitable for big files (More than 5 MB) cause the script memory
     * may get full and the execution fail
     *
     * @param string $pathToFile An Operating system absolute or relative path containing some file
     *
     * @return string The file contents as a string. If the file is not found or cannot be read, an exception will be thrown.
     */
    public function readFile($pathToFile){

        $pathToFile = $this->_composePath($pathToFile, false, true);

        if(($contents = file_get_contents($pathToFile, true)) === false){

            throw new UnexpectedValueException('Error reading file - '.$pathToFile);
        }

        return $contents;
    }


    /**
     * Read and return the content of a file. Not suitable for big files (More than 5 MB) cause the script memory
     * may get full and the execution fail
     *
     * @param string $pathToFile An Operating system absolute or relative path containing some file
     *
     * @return string The file contents as a string. If the file is not found or cannot be read, an exception will be thrown.
     */
    public function readFileAsBase64($pathToFile){

        $pathToFile = $this->_composePath($pathToFile, false, true);

        if(($contents = file_get_contents($pathToFile, true)) === false){

            throw new UnexpectedValueException('Error reading file - '.$pathToFile);
        }

        return ConversionUtils::stringToBase64($contents);
    }


    /**
     * Reads a file and performs a buffered output to the browser, by sending it as small fragments.<br>
     * This method is mandatory with big files, as reading the whole file to memory will cause the script or RAM to fail.<br><br>
     *
     * Adapted from code suggested at: http://php.net/manual/es/function.readfile.php
     *
     * @param string $pathToFile An Operating system absolute or relative path containing some file
     * @param float $downloadRateLimit If we want to limit the download rate of the file, we can do it by setting this value to > 0. For example: 20.5 will set the file download rate to 20,5 kb/s
     *
     * @return int the number of bytes read from the file.
     */
    public function readFileBuffered(string $pathToFile, int $downloadRateLimit = 0){

        $pathToFile = $this->_composePath($pathToFile);

        if(!is_file($pathToFile)){

            return 0;
        }

        // Disable script time limit
        set_time_limit(0);

        // How many bytes per chunk
        if($downloadRateLimit <= 0){

            $chunkSize = 1*(1024*1024);

        }else{

            $chunkSize = round($downloadRateLimit * 1024);
        }

        $buffer = '';
        $cnt = 0;

        $handle = fopen($pathToFile, 'rb');

        if($handle === false) {

            return $cnt;
        }

        // Output the file chunk by chunk
        while(!feof($handle)){

            $buffer = fread($handle, $chunkSize);

            echo $buffer;

            // This makes sure that when output buffering is on, the file data will be written to browser
            if(ob_get_level() > 0){

                ob_flush();
            }

            // Forces a write of the data to the browser
            flush();

            $cnt += strlen($buffer);

            // Sleep one second if download rate limit is set
            if($downloadRateLimit > 0){

                sleep(1);
            }
        }

        fclose($handle);

        // return num. bytes delivered like readfile() does.
        return $cnt;
    }


    /**
     * Copies a file from a source location to the defined destination
     * If the destination file already exists, it will be overwritten.
     *
     * @param string $sourceFilePath Absolute or relative path to the source file that must be copied (including the filename itself).
     * @param string $destFilePath Absolute or relative path to the destination where the file must be copied (including the filename itself).
     *
     * @return boolean Returns true on success or false on failure.
     */
    public function copyFile(string $sourceFilePath, string $destFilePath){

        $sourceFilePath = $this->_composePath($sourceFilePath);
        $destFilePath = $this->_composePath($destFilePath);

        return copy($sourceFilePath, $destFilePath);
    }


    /**
     * Renames a file.
     *
     * @param string $sourceFilePath Absolute or relative path to the source file that must be renamed (including the filename itself).
     * @param string $destFilePath Absolute or relative path to the new file name (including the filename itself). It must not exist.
     * @param int $timeout The amount of seconds that this method will be trying to rename the specified file in case it is blocked
     *            by the OS or temporarily not accessible. If the file can't be renamed after the given amount of seconds, an exception
     *            will be thrown.
     *
     * @return boolean True on success
     */
    public function renameFile($sourceFilePath, $destFilePath, int $timeout = 15){

        return $this->_renameFSResource($this->_composePath($sourceFilePath, false, true), $this->_composePath($destFilePath), $timeout);
    }


    /**
     * Delete a filesystem file.
     *
     * @param string $pathToFile Absolute or relative path to the file we want to delete
     * @param int $timeout The amount of seconds that this method will be trying to delete the specified file in case it is blocked
     *            by the OS or temporarily not accessible. If the file can't be deleted after the given amount of seconds, an exception
     *            will be thrown.
     *
     * @throws UnexpectedValueException If the file cannot be deleted or does not exist
     *
     * @return boolean True on success ONLY if the file existed and was deleted.
     */
    public function deleteFile(string $pathToFile, int $timeout = 15){

        $pathToFile = $this->_composePath($pathToFile, false, true);

        $lastError = '';
        $passedTime = 0;
        $deleteStartTime = time();

        if(is_file($pathToFile)){

            do {

                if(unlink($pathToFile) === true){

                    return true;
                }

                // Add one second delay before retrying
                sleep(1);

                $passedTime = time() - $deleteStartTime;

            } while ($passedTime < $timeout);

            $lastError = error_get_last()['message'];

        }else{

            $lastError = 'File does not exist';
        }

        throw new UnexpectedValueException("Error deleting file ($passedTime seconds timeout):\n$pathToFile\n".$lastError);
    }


    /**
     * Delete a list of filesystem files.
     *
     * @param array $pathsToFiles A list of filesystem absolute or relative paths to files to delete
     * @param int $timeout The amount of seconds that this method will be trying to delete a file in case it is blocked
     *            by the OS or temporarily not accessible. If the file can't be deleted after the given amount of seconds, an exception
     *            will be thrown.
     *
     * @throws UnexpectedValueException If any of the files cannot be deleted, an exception will be thrown
     *
     * @return boolean True on success
     */
    public function deleteFiles(array $pathsToFiles, int $timeout = 15){

        foreach ($pathsToFiles as $pathToFile) {

            $this->deleteFile($pathToFile, $timeout);
        }

        return true;
    }


    /**
     * Auxiliary method that is used by the findUniqueFileName and findUniqueDirectoryName methods
     *
     * @param int $i Current index for the name generation
     * @param string $desiredName Desired name as used on the parent method
     * @param string $text text name as used on the parent method
     * @param string $separator separator name as used on the parent method
     * @param bool $isPrefix isPrefix name as used on the parent method
     *
     * @return string The generated name
     */
    private function _generateUniqueNameAux(int $i, string $desiredName, string $text, string $separator, bool $isPrefix){

        $result = [];

        if($isPrefix){

            if($text !== ''){

                $result[] = $text;
            }

            $result[] = $i;

            if($desiredName !== ''){

                $result[] = $desiredName;
            }

        }else{

            if($desiredName !== ''){

                $result[] = $desiredName;
            }

            if($text !== ''){

                $result[] = $text;
            }

            $result[] = $i;
        }

        return implode($separator, $result);
    }


    /**
     * Auxiliary method to generate a full path from a relative (or absolute) one and the configured root path
     *
     * If an absolute path is passed to the relativePath variable, the result of this method will be that value, ignoring
     * any possible value on _rootPath.
     */
    protected function _composePath($relativePath, $testIsDirectory = false, $testIsFile = false){

        if (!is_string($relativePath)){

            throw new UnexpectedValueException('Path must be a string');
        }

        $composedPath = '';

        if (StringUtils::isEmpty($this->_rootPath) ||
            $this->isPathAbsolute($relativePath)) {

            $composedPath = $relativePath;

        } else {

            $composedPath = $this->_rootPath.$this->dirSep().$relativePath;
        }

        $path = StringUtils::formatPath($composedPath, $this->dirSep());

        if ($testIsDirectory && !$this->isDirectory($path)){

            throw new UnexpectedValueException('Path does not exist: '.$path);
        }

        if($testIsFile && !is_file($path)){

            throw new UnexpectedValueException('File does not exist: '.$path);
        }

        return $path;
    }
}

?>