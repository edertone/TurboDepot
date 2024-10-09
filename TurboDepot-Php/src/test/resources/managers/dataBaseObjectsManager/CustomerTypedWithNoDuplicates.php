<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This customer class uses the NO_DUPLICATES flag and UNIQUEINDICES to prevent duplicate values on the properties
 */
class CustomerTypedWithNoDuplicates extends DataBaseObject{


    const TYPES = [

        'name' => [50, self::NOT_NULL, self::STRING, self::NO_DUPLICATES],
        'age' => [self::INT, self::NO_DUPLICATES, 4],
        'address' => [self::STRING, 94],
        'city' => [self::STRING, 50]
    ];


    const UNIQUEINDICES = [

        ['city', 'address']
    ];


    public $name = '';

    public $age = 0;

    public $address = null;

    public $city = null;
}
