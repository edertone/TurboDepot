<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This customer class has an error on type definition cause it does not declare the type size
 */
class CustomerTypedWithoutSize extends DataBaseObject{


    const TYPES = [

        'name' => [20, self::NOT_NULL, self::STRING],
        'commercialName' => [self::STRING]
    ];


    public $name = '';

    public $commercialName = '';
}
