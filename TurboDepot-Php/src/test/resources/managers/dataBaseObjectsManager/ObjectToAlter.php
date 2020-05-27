<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This is an object that is used to test changes that may happen over time when the structure of database objects
 * changes, by adding removing or renaming properties
 */
class ObjectToAlter extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;
        $this->_types['name'] = [20, self::NOT_NULL, self::STRING];
    }

    public $name = '';

    public $city = '';
}

?>