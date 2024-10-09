<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;


/**
 * This customer class strongly defines the properties types so databaseobjects manager is able to create
 * the table columns with greater precision
 */
class CustomerTyped extends DataBaseObject{


    const TYPES = [

        'name' => [20, self::NOT_NULL, self::STRING],
        'commercialName' => [self::STRING, 25],
        'birthDate' => [self::DATETIME, 0],
        'miliSecondsDate' => [3, self::DATETIME],
        'microSecondsDate' => [6, self::DATETIME],
        'age' => [self::INT, 2],
        'oneDigitInt' => [self::INT, 1],
        'sixDigitInt' => [6, self::INT],
        'twelveDigitInt' => [self::INT, 12],
        'doubleValue' => [self::DOUBLE, 5],
        'setup' => [self::BOOL],
        'emails' => [75, self::ARRAY, self::STRING],
        'boolArray' => [self::BOOL, self::ARRAY, self::NOT_NULL],
        'intArray' => [self::INT, self::ARRAY, 3],
        'doubleArray' => [self::DOUBLE, 5, self::ARRAY]
    ];


    const FOREIGN_DELETE_OBJECTS = [

        'org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\CustomerTypedForeignObject' => ['dbId' => 'customerId']
    ];


    public $name = '';

    public $commercialName = '';

    public $birthDate = null;

    public $miliSecondsDate = null;

    public $microSecondsDate = null;

    public $age = 0;

    public $oneDigitInt = 0;

    public $sixDigitInt = 0;

    public $twelveDigitInt = 0;

    public $doubleValue = 0;

    public $setup = false;

    public $emails = [];

    public $boolArray = [];

    public $intArray = [];

    public $doubleArray = [];
}
