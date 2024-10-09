<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\extraTypedProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class modifies the original one by adding an extra typed property that does not exist on the original
 */
class ObjectToAlter extends DataBaseObject{


    const IS_TYPING_MANDATORY = false;


    const TYPES = [

        'name' => [20, self::NOT_NULL, self::STRING],
        'extraTyped' => [10, self::INT]
    ];


    public $name = '';

    public $city = '';

    public $extraTyped = '';
}
