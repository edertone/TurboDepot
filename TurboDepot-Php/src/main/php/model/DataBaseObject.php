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


/**
 * DataBaseObject
 */
abstract class DataBaseObject extends BaseStrictClass{


    /**
     * Boolean type that can be used to constrain object properties
     */
    const BOOL = 'BOOL';


    /**
     * Signed integer type with a max value of 2147483647 that can be used to constrain object properties
     */
    const INT = 'INT';


    /**
     * Signed float type that can be used to constrain object properties
     */
    const DOUBLE = 'DOUBLE';


    /**
     * Text type that can be used to constrain object properties
     */
    const STRING = 'STRING';


    /**
     * Date type that can be used to constrain object properties which must be always defined as ISO 8601 strings
     * with a mandatory UTC +0 timezone (yyyy-mm-ddTHH:MM:SS.UUUUUU+00:00), or an exception will be thrown.
     *
     * The UTC offset is mandatory so all the dates are standarized and consistent.Local timezone may be applied at the presentation layer
     * if necessary.
     *
     * Accepted size values are 0 for seconds precision, 3 for miliseconds and 6 for microseconds
     */
    const DATETIME = 'DATETIME';


    /**
     * Array type that can be used to constrain object properties.
     * IMPORTANT: If we define an object property as ARRAY, we must also specify the type of the array elements
     */
    const ARRAY = 'ARRAY';


    /**
     * Flag that is used to specify on a data type that a property is stored with multiple language values.
     *
     * Properties that are multilanguage will contain the value that's specifically set for that language on that property.
     */
    const MULTI_LANGUAGE = 'MULTI_LANGUAGE';


    /**
     * Flag that is used to specify that a data type cannot be null
     */
    const NOT_NULL = 'NOT_NULL';


    /**
     * Flag that is used to specify that a data type cannot have duplicate values. If an object is saved with a value that already exists
     * on db for a property which has this flag set, an exception will be thrown.
     */
    const NO_DUPLICATES = 'NO_DUPLICATES';


    /**
     * @see self::getDbId()
     */
    protected $dbId = null;


    /**
     * @see self::getDbUUID()
     */
    protected $dbUUID = null;


    /**
     * @see self::getDbCreationDate()
     */
    protected $dbCreationDate = null;


    /**
     * @see self::getDbModificationDate()
     */
    protected $dbModificationDate = null;


    /**
     * @see self::getDbDeleted()
     */
    protected $dbDeleted = null;


    /**
     * If set to true and the types array is used to specify data types for the object properties, all the properties must have their respective type
     * definition or an exception will be thrown.
     *
     * If set to false, it will be allowed to leave one or more object properties without type definition.
     *
     * This flag won't have any effect if the _types array is not used
     *
     * @see self::$_types
     *
     * @var string
     */
    protected $_isTypingMandatory = true;


    /**
     * Associative array that defines the data types to use with the object properties. Each array key must be the object property to set
     * and each value an array with the following elements in any order:<br>
     *
     * 1. The property data type: DataBaseObject::BOOL, ::INT, ::DOUBLE, ::STRING, ::DATETIME or ::ARRAY<br>
     * 2. The property data size (for int and string values the maximum number of digits that can be stored). It is mandatory<br>
     * 3. DataBaseObject::NOT_NULL if the property cannot have null values, skip this otherwise (it is optional)
     * 4. DataBaseObject::NO_DUPLICATES if the property cannot have duplicate values on database, skip this otherwise (it is optional)
     *
     * @var array
     */
    protected $_types = [];


    /**
     * Array of arrays where each element contains the names for the properties that will be included on a unique index, so values cannot be repeated.
     * We can define simple indices by providing only one property name (which can also be done via DataBaseObject::NO_DUPLICATES on the _types setup) and
     * we can also define complex indices by providing two or more property names at each array element to generate complex indices.
     *
     * @var array
     */
    protected $_uniqueIndices = [];


    /**
     * @see self::setLocales()
     *
     * @var array
     */
    protected $_locales = [];


    /**
     * Flag that stores if the current object has localized properties. It is stored here the first time it is calculated to improve performance
     *
     * @var boolean
     */
    protected $_isMultiLanguage = null;


    /**
     * This method is always called before any other thing at the database object constructor.

     * It must be declared to define the database object configuration and type values. If nothing is specified here, all setup parameters will be the default ones.
     *
     * @return void
     */
    abstract protected function setup();


    /**
     * Base class for all the objects that are manipulated by the DataBaseObjectsManager class.
     *
     * @see self::setLocales()
     *
     * @param array $locales The list of locales that are used on localized properties by this instance, sorted by preference
     */
    public final function __construct(array $locales = []){

        $this->setup();

        $localesCount = count($locales);
        $classProperties = array_keys(get_object_vars($this));

        foreach ($this->_types as $property => $typedef) {

            // Check that all the defined types belong to object properties.
            if(!in_array($property, $classProperties, true)){

                throw new UnexpectedValueException('Cannot define type for '.$property.' cause it does not exist on class');
            }

            // When instance is constructed, all received locales are set to the class default multilanguage property values.
            // Note that first locale contains the same values as the instance current properties
            for ($i = 0; $i < $localesCount; $i++) {

                if($locales[$i] !== '' && preg_match('/[a-z][a-z]_[A-Z][A-Z]/', $locales[$i]) === 0){

                    throw new UnexpectedValueException('Invalid locale specified: '.$locales[$i]);
                }

                if(in_array(self::MULTI_LANGUAGE, $typedef, true)){

                    $this->_locales[$locales[$i]][$property] = $this->{$property};
                }
            }
        }

        $this->setLocales($locales);
    }


    /**
     * The instance db identifier. Null value means the entity is not yet stored on db
     *
     * @return null|int
     */
    public final function getDbId(){

        return $this->dbId;
    }


    /**
     * Universal identifier value for this object (in case UUIDs are enabled), or null if the object's still not saved to db
     *
     * @return null|string
     */
    public final function getDbUUID(){

        return $this->dbUUID;
    }


    /**
     * Date when the object was created (UTC), or null if the object's still not saved to db
     *
     * @return null|string
     */
    public final function getDbCreationDate(){

        return $this->dbCreationDate;
    }


    /**
     * Date when the object was last modified (UTC), or null if the object's still not saved to db
     *
     * @return null|string
     */
    public final function getDbModificationDate(){

        return $this->dbModificationDate;
    }


    /**
     * When an object is deleted, the date and time (UTC) of deletion is stored on this property, meaning it's been moved to trash. To delete it totally,
     * we need to empty the trash (or disable the trash feature).
     *
     * If the object is still not stored on database or it is stored but not deleted, this value will be null
     *
     * @return null|string
     */
    public final function getDbDeleted(){

        return $this->dbDeleted;
    }


    /**
     * Check if this DatabaseObject is using multi language properties or not
     *
     * @return boolean True if this instance is localized, false if not
     */
    public final function isMultiLanguage(){

        if($this->_isMultiLanguage === null){

            $this->_isMultiLanguage = false;

            foreach ($this->_types as $type) {

                if(in_array(self::MULTI_LANGUAGE, $type, true)){

                    return $this->_isMultiLanguage = true;
                }
            }
        }

        return $this->_isMultiLanguage;
    }


    /**
     * Get the list of strings containing the locales that are used by this class, sorted by preference.
     *
     * @see self::setLocales()
     *
     * @return array The list of locales.
     */
    public final function getLocales(){

        if($this->isMultiLanguage()){

            return array_keys($this->_locales);
        }

        throw new UnexpectedValueException('Class is not multi language');
    }


    /**
     * Specifies the list of strings containing the locales that are used by this class, sorted by preference.
     * Each string must be formatted as a standard locale code with language and country joined by an underscore, like: en_US, fr_FR. The only accepted
     * exceptions is the '' empty locale, which can be used to define localized values when we don't know what locale to use.
     *
     * @param array $locales The list of locales sorted by preference
     *
     * @return void
     */
    public final function setLocales(array $locales){

        $localesCount = count($locales);
        $instanceLocales = array_keys($this->_locales);
        $instanceLocalesCount = count($this->_locales);

        if($this->isMultiLanguage()){

            if($locales === []){

                throw new UnexpectedValueException('Class is multi language and expects a list of locales');
            }

        }else if($locales !== []){

            throw new UnexpectedValueException('Class is not multi language and does not expect a list of locales');
        }

        // Duplicate values are not allowed on the locales array
        if(count(array_unique($locales)) < $localesCount){

            throw new UnexpectedValueException('Duplicate elements found on locales list');
        }

        $sortedLocales = [];

        for ($i = 0; $i < $localesCount; $i++) {

            // Adding or removing locales from the current active list is not allowed. All the available locales for this instance are defined only
            // when the instance is created. The only accepted operation with setLocales() is changing their order
            if($instanceLocalesCount !== $localesCount || !in_array($locales[$i], $instanceLocales, true)){

                throw new UnexpectedValueException('Locales cannot be added or removed from an already created instance, only sorted.');
            }

            $sortedLocales[$locales[$i]] = $this->_locales[$locales[$i]];

            // Set all the multilanguage properties values to the new first locale on the list
            if($i === 0){

                // Update the current property values to the first locale of the list
                $firstCurrentLocale = array_keys($this->_locales)[0];

                foreach (array_keys($this->_locales[$firstCurrentLocale]) as $property) {

                    $this->_locales[$firstCurrentLocale][$property] = $this->{$property};
                }

                // Replace the current propery values with the new locale that will be first on the list
                foreach ($sortedLocales[$locales[$i]] as $property => $value) {

                    $this->{$property} = $value;
                }
            }
        }

        $this->_locales = $sortedLocales;

        return $locales;
    }
}

?>