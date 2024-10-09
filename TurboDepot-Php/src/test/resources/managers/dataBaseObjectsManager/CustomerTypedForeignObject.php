<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;


/**
 * This is a class that defines an object that will be related to the CustomerTyped class as a foreign object
 * to be deleted once their parent object is deleted, using the FOREIGN_DELETE_OBJECTS feature
 */
class CustomerTypedForeignObject extends DataBaseObject{


    const TYPES = [

        'customerId' => [12, self::NOT_NULL, self::INT],
        'name' => [20, self::NOT_NULL, self::STRING]
    ];


    public $customerId = '';
    public $name = '';
}
