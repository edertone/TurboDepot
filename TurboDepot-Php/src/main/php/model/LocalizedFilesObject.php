<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */


namespace org\turbodepot\src\main\php\model;
use org\turbocommons\src\main\php\model\BaseStrictClass;

/**
 * LocalizedFilesObject class
 */
class LocalizedFilesObject extends BaseStrictClass{


    /**
     * See getter for docs
     */
    private $_isDirectory = false;


    /**
     * See getter for docs
     */
    private $_path = '';


    /**
     * See getter for docs
     */
    private $_extension = '';


    /**
     * See getter for docs
     */
    private $_locale = '';


    /**
     * See getter for docs
     */
    private $_language = '';


    /**
     * See getter for docs
     */
    private $_key = '';


    /**
     * See getter for docs
     */
    private $_translation = '';


    /**
     * Defines a file or a folder that is stored inside a LocalizedFilesManager filesystem
     * In the case of a file its contents are not found here: we must call to the LocalizedFilesManager::readFile
     * method or similar to obtain the file contents.
     *
     * @param boolean $isDirectory See getter for docs
     * @param string $path See getter for docs
     * @param string $extension See getter for docs
     * @param string $locale See getter for docs
     * @param string $language See getter for docs
     * @param string $key See getter for docs
     * @param string $translation See getter for docs
     */
    public function __construct($isDirectory, $path, $extension, $locale, $language, $key, $translation){

        $this->_isDirectory = $isDirectory;
        $this->_path = $path;
        $this->_extension = $extension;
        $this->_locale = $locale;
        $this->_language = $language;
        $this->_key = $key;
        $this->_translation = $translation;
    }


    /**
     * Tells if this LocalizedFilesObject instance is a file or a directory
     */
    public function getIsDirectory(){

        return $this->_isDirectory;
    }

    /**
     * The path to this object (including the object itself), relative to the folder that is defined as the root of
     *  the localized files manager class
     */
    public function getPath(){

        return $this->_path;
    }

    /**
     * If this LocalizedFilesObject is a file and has any file extension, it will be defined here.
     * For example, if we have somefile.txt, this property will contain 'txt'
     */
    public function getExtension(){

        return $this->_extension;
    }

    /**
     * The locale for which this LocalizedFilesObject instance values have been loaded
     */
    public function getLocale(){

        return $this->_locale;
    }

    /**
     * The language for which this LocalizedFilesObject instance values have been loaded
     */
    public function getLanguage(){

        return $this->_language;
    }

    /**
     * The translation key that is defined for this LocalizedFilesObject instance
     */
    public function getKey(){

        return $this->_key;
    }

    /**
     * The LocalizedFilesObject name translation based on the currently defined locale
     */
    public function getTranslation(){

        return $this->_translation;
    }
}

?>