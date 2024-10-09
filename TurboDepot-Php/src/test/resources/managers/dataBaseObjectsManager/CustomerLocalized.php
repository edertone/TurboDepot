<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class defines a customer that can have multi language values for some of its properties
 */
class CustomerLocalized extends DataBaseObject{


    const TYPES = [

        'name' => [self::STRING, 250],
        'nameLocalized' => [self::MULTI_LANGUAGE, self::STRING, 20],
        'nameLocalizedNotNull' => [self::MULTI_LANGUAGE, self::STRING, 20, self::NOT_NULL],
        'birthDate' => [self::DATETIME, 0],
        'birthDateLocalized' => [self::MULTI_LANGUAGE, self::DATETIME, 0],
        'age' => [self::INT, 2],
        'ageLocalized' => [self::INT, 2, self::MULTI_LANGUAGE],
        'setup' => [self::BOOL],
        'setupLocalized' => [self::BOOL, self::MULTI_LANGUAGE]
    ];


    public $name = null;

    public $nameLocalized = null;

    public $nameLocalizedNotNull = '';

    public $birthDate = null;

    public $birthDateLocalized = null;

    public $age = 0;

    public $ageLocalized = 0;

    public $setup = false;

    public $setupLocalized = false;
}
