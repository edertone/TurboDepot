<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithWrongArrayTypeSize extends DataBaseObject{


    const TYPES = [

        'array' => [self::ARRAY, self::STRING, 'invalidsize']
    ];


    public $array = '';
}
