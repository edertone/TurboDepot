<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\renamedNonTypedProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class modifies the original one by renaming a non typed property
 */
class ObjectToAlter extends DataBaseObject{


    const IS_TYPING_MANDATORY = false;


    const TYPES = [

        'name' => [20, self::NOT_NULL, self::STRING]
    ];


    public $name = '';

    public $cityRenamed = '';
}
