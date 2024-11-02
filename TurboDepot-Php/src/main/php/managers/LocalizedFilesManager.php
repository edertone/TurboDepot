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
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbodepot\src\main\php\model\LocalizedFilesObject;


/**
 * Localized files manager class
 */
class LocalizedFilesManager extends FilesManager{


    /**
     * A LocalesManager class instance that is used by this class
     *
     * @var LocalesManager
     */
    private $_localesManager = null;


    /**
     * Manager class that allows us to interact with a localized file system.
     * This kind of file system allows us to store folders and files with localized names and content. Each folder or file is stored with a translation
     * key that allows us to retrieve it's name with serveral languages. We can also provide localized content to the same folder or file by appending
     * a -xx_XX key at the end of the translation key to store different contents for each language.
     *
     * We can have a document that is translated into multiple languages or a folder with different contents depending on the language
     *
     * @see LocalesManager::initialize
     *
     * @param string $rootPath We need to specify an existing directory as the entry point where all the localized files and directories are stored. The full OS path must be provided.
     * @param array $locales List of languages for which we want to load the translations (see TurboCommons LocalesManager docs for more info)
     * @param array $localesPaths An array of filesystem paths where translations are stored
     * @param boolean $failIfKeyNotFound If set to true, any operation with a folder or file that is named without a translation key that is available for the currently
     *                defined list of translation locations, will throw an exception. If set to false, the key itself will be used as the folder or file name without throwing an error.
     */
    public function __construct($rootPath, $locales, $localesPaths, $failIfKeyNotFound = false){

        if (!is_string($rootPath)){

            throw new UnexpectedValueException('rootPath must be a string');
        }

        $this->_rootPath = StringUtils::formatPath($rootPath);

        if(!is_dir($this->_rootPath)){

            throw new UnexpectedValueException('Specified rootPath does not exist: '.$rootPath);
        }

        parent::__construct($rootPath);

        $this->_localesManager = new LocalesManager();

        $this->_localesManager->setMissingKeyFormat($failIfKeyNotFound ? '$exception' : '$key');

        $this->_localesManager->initialize($locales, $localesPaths);
    }


    /**
     * Same behaviour as LocalesManager::setPrimaryLocale
     *
     * @see LocalesManager::setPrimaryLocale
     *
     * @param string $locale see LocalesManager::setPrimaryLocale
     *
     * @return void
     */
    public function setPrimaryLocale(string $locale){

        $this->_localesManager->setPrimaryLocale($locale);
    }


    /**
     * Same behaviour as LocalesManager::setPrimaryLocales
     *
     * @see LocalesManager::setPrimaryLocales()
     *
     * @param array $locales see LocalesManager::setPrimaryLocales
     *
     * @return void
     */
    public function setPrimaryLocales(array $locales){

        $this->_localesManager->setPrimaryLocales($locales);
    }


    /**
     * Same behaviour as LocalesManager::setPrimaryLanguage
     *
     * @see LocalesManager::setPrimaryLanguage()
     *
     * @param string $language see LocalesManager::setPrimaryLanguage
     *
     * @return void
     */
    public function setPrimaryLanguage(string $language){

        $this->_localesManager->setPrimaryLanguage($language);
    }


    /**
     * Same behaviour as LocalesManager::setPrimaryLanguages
     *
     * @see LocalesManager::setPrimaryLanguages()
     * @see LocalesManager::setPrimaryLocale()
     * @see LocalesManager::setPrimaryLanguage()
     *
     * @param array $languages see LocalesManager::setPrimaryLanguages()
     *
     * @return void
     */
    public function setPrimaryLanguages(array $languages){

        $this->_localesManager->setPrimaryLanguages($languages);
    }


    /**
     * TODO
     */
    public function createDirectory($path, bool $recursive = false){

        $formattedPath = StringUtils::formatPath($path, DIRECTORY_SEPARATOR);

        // TODO - if the class is configured to fail with non existing keys, we must check it here and launch an exception if
        // the provided path is not composed of all valid translation keys as folder names.
        // $pathParts = explode(DIRECTORY_SEPARATOR, $formattedPath);

        return parent::createDirectory($formattedPath, $recursive);
    }


    /**
     * Gives the list of items that are stored on the specified folder. It will give files and directories, and each element
     * will be an instance of a LocalizedFilesObject
     *
     * The contents of any subfolder will not be listed. We must call this method for each child folder if we want to get it's list.
     * (The method ignores the . and .. items if exist).
     *
     * @see FilesManager::getDirectoryList
     * @see LocalesManager::get
     *
     * @param string $path Absolute or relative path to the directory we want to list
     * @param string $bundlePath A string with the format 'library_name/bundle_name' that is used to locate the bundle were the key to translate is found
     * @param mixed $toReplace A list of values that will replace the wildcards that are found on the translated text. See LocalesManager::get for more info
     * @param string $sort See FilesManager::getDirectoryList
     *
     * @return array The list of LocalizedFilesObject items inside the specified path sorted as requested, or an empty array if no items found inside the folder.
     */
    public function getDirectoryList($path, string $bundlePath = '', $toReplace = [], string $sort = ''){

        $fullPath = $this->_composePath($path);

        $result = [];
        $dirKeys = [];
        $fileKeys = [];

        $list = parent::getDirectoryList($path, $sort);
        $language = $this->_localesManager->getLanguages()[0];
        $locale = $this->_localesManager->getLocales()[0];

        foreach ($list as $item) {

            if(is_dir($fullPath.DIRECTORY_SEPARATOR.$item)){

                $isDirectory = true;
                $extension = '';
                $key = preg_replace("/(.*)-[a-z][a-z]_[A-Z][A-Z]$/", '${1}', $item, 1);
                $translation = $this->_localesManager->t($key, $bundlePath, $toReplace);

                // If the folder contains multiple localized contents, the key is duplicate, so we won't add it to the result
                if(in_array($key, $dirKeys)){

                    continue;
                }

                $dirKeys[] = $key;

            }else{

                $isDirectory = false;
                $extension = StringUtils::getPathExtension($item);
                $extensionWithDot = $extension === '' ? '' : '.'.$extension;
                $elementNoExt = StringUtils::getPathElementWithoutExt($item);
                $key = preg_replace("/(.*)-[a-z][a-z]_[A-Z][A-Z]$/", '${1}', $elementNoExt, 1);
                $translation = $this->_localesManager->t($key, $bundlePath, $toReplace).$extensionWithDot;

                if(preg_match("/.*-[a-z][a-z]_[A-Z][A-Z]$/", $elementNoExt)){

                    foreach ($this->_localesManager->getLocales() as $managerLocale) {

                        if(in_array($key.'-'.$managerLocale.$extensionWithDot, $list)){

                            $item = $key.'-'.$managerLocale.$extensionWithDot;

                            break;
                        }
                    }
                }

                // If the file contains multiple localized contents, the key is duplicate, so we won't add it to the result
                if(in_array($key.$extensionWithDot, $fileKeys)){

                    continue;
                }

                $fileKeys[] = $key.$extensionWithDot;
            }

            $result[] = new LocalizedFilesObject($isDirectory, $path.DIRECTORY_SEPARATOR.$item, $extension, $locale, $language, $key, $translation);
        }

        return $result;
    }


    /**
     * TODO - Implement
     */
    public function copyDirectory(string $sourcePath, string $localizedPath, $destMustBeEmpty = true){
    }


    /**
     * TODO - Implement
     */
    public function mirrorDirectory($sourcePath, $destPath, $timeout = 30){
    }


    /**
     * TODO - Implement
     */
    public function syncDirectories(string $path1, string $path2){
    }


    /**
     * TODO - Implement
     */
    public function renameDirectory($sourcePath, $destPath, int $timeout = 15){
    }


    /**
     * TODO - Implement
     */
    public function deleteDirectory(string $path, bool $deleteDirectoryItself = true, $timeout = 30){
    }


    /**
     * TODO - Implement
     */
    public function saveFile($pathToFile, $data = '', bool $append = false, bool $createDirectories = false){
    }


    /**
     * TODO - Implement
     */
    public function mergeFiles(array $sourcePaths, string $destFile, $separator = ''){
    }


    /**
     * Read and return the content of a localized file (see FilesManager::readFile for more info).
     *
     * @see FilesManager::readFile
     *
     * @param string|LocalizedFilesObject $pathOrObject A LocalizedFilesObject instance that represents a file which we want to read.
     *        We usually get this instance by calling to getDirectoryList method, but we may also provide here a string containing the path
     *        to the file we want to obtain.
     *
     * @return string The file contents as a string.
     */
    public function readFile($pathOrObject){

        if(is_string($pathOrObject)){

            return parent::readFile($pathOrObject);
        }

        if(is_object($pathOrObject) &&
           get_class($pathOrObject) === 'org\turbodepot\src\main\php\model\LocalizedFilesObject' &&
           $pathOrObject->getIsDirectory() === false){

            return parent::readFile($pathOrObject->getPath());
        }

        throw new UnexpectedValueException('pathOrObject is not a valid file');
    }

    /**
     * TODO - Implement
     */
    public function copyFile(string $sourceFilePath, string $destFilePath){
    }


    /**
     * TODO - Implement
     */
    public function renameFile($sourceFilePath, $destFilePath, int $timeout = 15){
    }
}

?>