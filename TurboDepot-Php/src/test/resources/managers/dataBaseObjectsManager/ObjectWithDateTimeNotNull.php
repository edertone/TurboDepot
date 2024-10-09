<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * Date time property is set as a not null value
 */
class ObjectWithDateTimeNotNull extends DataBaseObject{


    const TYPES = [

        'date' => [self::DATETIME, self::NOT_NULL, 0]
    ];


    public $date = null;
}
