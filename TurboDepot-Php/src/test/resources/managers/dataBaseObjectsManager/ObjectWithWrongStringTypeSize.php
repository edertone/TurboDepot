<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithWrongStringTypeSize extends DataBaseObject{


    const TYPES = [

        'name' => [self::STRING, 'invalidsize']
    ];


    public $name = '';
}
