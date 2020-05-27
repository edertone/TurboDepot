<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithWrongArrayTypeSize extends DataBaseObject{


    protected function setup(){

        $this->_types['array'] = [self::ARRAY, self::STRING, 'invalidsize'];
    }

    public $array = '';
}

?>