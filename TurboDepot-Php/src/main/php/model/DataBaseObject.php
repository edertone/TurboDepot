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
 * Base class for all the objects that are manipulated by the DataBaseObjectsManager class.
 */
abstract class DataBaseObject extends BaseStrictClass{


    /**
     * Associative array that defines the data types to use with the object properties. Each array key must be the object property to set
     * and the value an array with the following elements:<br>
     * 1. The property data type: DataBaseObjectsManager::BOOL, ::INT, ::DOUBLE, ::STRING or ::ARRAY<br>
     * 2. The property data size (for int and string values the maximum number of digits that can be stored)
     * 3. True if the property can have null values, false otherwise
     *
     * @var array
     */
    protected $_types = [];


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
     * we need to empty the trash
     */
    public $deleted = null;
}

?>