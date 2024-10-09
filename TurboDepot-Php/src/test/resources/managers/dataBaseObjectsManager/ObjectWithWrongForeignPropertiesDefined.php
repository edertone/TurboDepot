<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;


/**
 * This class has an incorrectly defined FOREIGN_DELETE_OBJECTS setup.
 */
class ObjectWithWrongForeignPropertiesDefined extends DataBaseObject{


    const TYPES = [

        'name' => [20, self::NOT_NULL, self::STRING]
    ];


    const FOREIGN_DELETE_OBJECTS = [

        'org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\CustomerTypedForeignObject' => ['dbId' => 'nonexistant']
    ];


    public $name = '';
}
