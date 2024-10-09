<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithWrongArrayNoTypeSpecified extends DataBaseObject{


    const TYPES = [

        'arrayVal' => [self::ARRAY, 20]
    ];


    public $arrayVal = '';
}
