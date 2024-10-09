<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithWrongDateTypeSize extends DataBaseObject{


    const TYPES = [

        'date' => [self::DATETIME, 10]
    ];


    public $date = '';
}
