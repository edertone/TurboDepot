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

use UnexpectedValueException;
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * Base class for all the objects that are manipulated by the DataBaseObjectsManager class.
 */
abstract class DataBaseObject extends BaseStrictClass{


    /**
     * The instance db identifier. Null value means the entity is not yet stored on db
     */
    public $dbId = null;


    /**
     * Universal identifier value for this object in case it is enabled
     */
    public $uuid = null;


    /**
     * Numeric value that can be used as a custom sorting method for this class created objects
     */
    public $sortIndex = null;


    /**
     * Date when the object was created
     */
    public $creationDate = null;


    /**
     * Date when the object was last modified
     */
    public $modificationDate = null;


    /**
     * When an object is deleted, the date and time of deletion is stored on this property, meaning it's been moved to trash. To delete it totally,
     * we need to empty the trash (or disable the trash feature)
     */
    public $deleted = null;


    /**
     * If set to true and the types array is used to specify data types for the object properties, all the properties must have their respective type
     * definition or an exception will be thrown.
     *
     * If set to false, it will be allowed to leave one or more object properties without type definition.
     *
     * This flag won't have any effect if the _types array is not used
     *
     * @see DataBaseObject::$_types
     *
     * @var string
     */
    protected $_isTypingMandatory = true;


    /**
     * Associative array that defines the data types to use with the object properties. Each array key must be the object property to set
     * and each value an array with the following elements:<br>
     * 1. The property data type: DataBaseObjectsManager::BOOL, ::INT, ::DOUBLE, ::STRING, ::DATETIME or ::ARRAY<br>
     * 2. The property data size (for int and string values the maximum number of digits that can be stored)
     * 3. True if the property can have null values, false otherwise
     *
     * @var array
     */
    protected $_types = [];


    /**
     * TODO
     *
     * @var array
     */
    private $_locales = [];


    /**
     * Flag that stores if the current object has localized properties. It is stored here the first time it is calculated to improve performance
     *
     * @var boolean
     */
    private $_isMultiLanguage = null;


    public function __construct(array $locales = []){

        // TODO - convert constructor method to final and use a setup() method to configure db objects the same way as WebServiceManager class does.
        // This is important to prevent database objects from overriding the constructor method without having the locales parameter

        $this->setLocales($locales);
    }


    /**
     * TODO
     * @param array $locales
     * @throws UnexpectedValueException
     * @return unknown
     */
    public final function setLocales(array $locales){

        if($this->isMultiLanguage()){

            if($locales === []){

                throw new UnexpectedValueException('Class is multi language and expects a list of locales');
            }

        }else if($locales !== []){

            throw new UnexpectedValueException('Class is not multi language and does not expect a list of locales');
        }

        // Duplicate values are not allowed on the locales array
        if(count(array_unique($locales)) < count($locales)){

            throw new UnexpectedValueException('Duplicate elements found on locales list');
        }

        $this->_locales = [];

        foreach ($locales as $locale) {

            if($locale !== '' && preg_match('/[a-z][a-z]_[A-Z][A-Z]/', $locale) === 0){

                throw new UnexpectedValueException('Invalid locale specified: '.$locale);
            }

            $this->_locales[$locale] = [];
        }

        return $locales;
    }


    /**
     * TODO
     * @return boolean
     */
    public final function isMultiLanguage(){

        if($this->_isMultiLanguage === null){

            $this->_isMultiLanguage = false;

            foreach ($this->_types as $type) {

                if(in_array(DataBaseObjectsManager::MULTI_LANGUAGE, $type, true)){

                    return $this->_isMultiLanguage = true;
                }
            }
        }

        return $this->_isMultiLanguage;
    }


    /**
     * TODO
     * @throws UnexpectedValueException
     * @return array
     */
    public final function getLocales(){

        if($this->isMultiLanguage()){

            return array_keys($this->_locales);
        }

        throw new UnexpectedValueException('Class is not multi language');
    }
}

?>