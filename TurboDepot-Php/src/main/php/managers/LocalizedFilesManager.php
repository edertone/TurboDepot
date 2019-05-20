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
use org\turbocommons\src\main\php\managers\LocalizationManager;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbodepot\src\main\php\model\LocalizedFilesObject;


/**
 * Localized files manager class
 */
class LocalizedFilesManager extends FilesManager{


    /**
     * A LocalizationManager class instance that is used by this class
     *
     * @var LocalizationManager
     */
    private $_localizationManager = null;


    /**
     * Manager class that allows us to interact with a localized file system.
     * This kind of file system allows us to store folders and files with localized names and content. Each folder or file is stored with a translation
     * key that allows us to retrieve it's name with serveral languages. We can also provide localized content to the same folder or file by appending
     * a -xx_XX key at the end of the translation key to store different contents for each language.
     *
     * We can have a document that is translated into multiple languages or a folder with different contents depending on the language
     *
     * @see LocalizationManager::initialize
     *
     * @param string $rootPath We need to specify an existing directory as the entry point where all the localized files and directories are stored. The full OS path must be provided.
     * @param array $locales List of languages for which we want to load the translations (see TurboCommons LocalizationManager docs for more info)
     * @param array $locations A list (sorted by preference) where each item defines a translations location (see TurboCommons LocalizationManager::initialize docs for more info)
     * @param boolean $failIfKeyNotFound If set to true, any operation with a folder or file that is named without a translation key that is available for the currently
     *                defined list of translation locations, will throw an exception. If set to false, the key itself will be used as the folder or file name without throwing an error.
     */
    public function __construct($rootPath, $locales, $locations, $failIfKeyNotFound = false){

        if (!is_string($rootPath)){

            throw new UnexpectedValueException('rootPath must be a string');
        }

        $this->_rootPath = StringUtils::formatPath($rootPath);

        if(!is_dir($this->_rootPath)){

            throw new UnexpectedValueException('Specified rootPath does not exist: '.$rootPath);
        }

        parent::__construct($rootPath);

        $this->_localizationManager = new LocalizationManager();

        $this->_localizationManager->missingKeyFormat = $failIfKeyNotFound ? '$exception' : '$key';

        $this->_localizationManager->initialize(new FilesManager(), $locales, $locations, function($errors) {

            if(count($errors) > 0){

                throw new UnexpectedValueException($errors[0]['errorMsg']);
            }
        });
    }


    /**
     * Same behaviour as LocalizationManager::setPrimaryLocale
     *
     * @see LocalizationManager::setPrimaryLocale
     *
     * @param string $locale see LocalizationManager::setPrimaryLocale
     *
     * @return void
     */
    public function setPrimaryLocale(string $locale){

        $this->_localizationManager->setPrimaryLocale($locale);
    }


    /**
     * Same behaviour as LocalizationManager::setPrimaryLocales
     *
     * @see LocalizationManager::setPrimaryLocales()
     *
     * @param array $locales see LocalizationManager::setPrimaryLocales
     *
     * @return void
     */
    public function setPrimaryLocales(array $locales){

        $this->_localizationManager->setPrimaryLocales($locales);
    }


    /**
     * Same behaviour as LocalizationManager::setPrimaryLanguage
     *
     * @see LocalizationManager::setPrimaryLanguage()
     *
     * @param string $language see LocalizationManager::setPrimaryLanguage
     *
     * @return void
     */
    public function setPrimaryLanguage(string $language){

        $this->_localizationManager->setPrimaryLanguage($language);
    }


    /**
     * Same behaviour as LocalizationManager::setPrimaryLanguages
     *
     * @see LocalizationManager::setPrimaryLanguages()
     * @see LocalizationManager::setPrimaryLocale()
     * @see LocalizationManager::setPrimaryLanguage()
     *
     * @param array $languages see LocalizationManager::setPrimaryLanguages()
     *
     * @return void
     */
    public function setPrimaryLanguages(array $languages){

        $this->_localizationManager->setPrimaryLanguages($languages);
    }


    /**
     * TODO
     */
    public function createDirectory(string $path, bool $recursive = false){

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
     * @see LocalizationManager::get
     *
     * @param string $path Absolute or relative path to the directory we want to list
     * @param string $bundle The name for the resource bundle file. See LocalizationManager::get for more info
     * @param string $location A location label to uniquely reference the location and resolve possible conflicts. See LocalizationManager::get for more info
     * @param mixed $toReplace A list of values that will replace the wildcards that are found on the translated text. See LocalizationManager::get for more info
     * @param string $sort See FilesManager::getDirectoryList
     *
     * @return array The list of LocalizedFilesObject items inside the specified path sorted as requested, or an empty array if no items found inside the folder.
     */
    public function getDirectoryList($path, string $bundle = '', string $location = '', $toReplace = [], string $sort = ''){

        $fullPath = $this->_composePath($path);

        $result = [];
        $dirKeys = [];
        $fileKeys = [];

        $list = parent::getDirectoryList($path, $sort);
        $language = $this->_localizationManager->languages()[0];
        $locale = $this->_localizationManager->locales()[0];

        foreach ($list as $item) {

            if(is_dir($fullPath.DIRECTORY_SEPARATOR.$item)){

                $isDirectory = true;
                $extension = '';
                $key = preg_replace("/(.*)-[a-z][a-z]_[A-Z][A-Z]$/", '${1}', $item, 1);
                $translation = $this->_localizationManager->get($key, $bundle, $location, $toReplace);

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
                $translation = $this->_localizationManager->get($key, $bundle, $location, $toReplace).$extensionWithDot;

                if(preg_match("/.*-[a-z][a-z]_[A-Z][A-Z]$/", $elementNoExt)){

                    foreach ($this->_localizationManager->locales() as $managerLocale) {

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
    public function mirrorDirectory(string $sourcePath, string $destPath){
    }


    /**
     * TODO - Implement
     */
    public function syncDirectories(string $path1, string $path2){
    }


    /**
     * TODO - Implement
     */
    public function renameDirectory(string $sourcePath, string $destPath){
    }


    /**
     * TODO - Implement
     */
    public function deleteDirectory(string $path, bool $deleteDirectoryItself = true){
    }


    /**
     * TODO - Implement
     */
    public function saveFile(string $pathToFile, string $data = '', bool $append = false){
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
    public function renameFile(string $sourceFilePath, string $destFilePath){
    }
}

?>