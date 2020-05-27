<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\renamedTypedProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class modifies the original one by renaming a typed property
 */
class ObjectToAlter extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;
        $this->_types['nameRenamed'] = [20, self::NOT_NULL, self::STRING];
    }

    public $nameRenamed = '';

    public $city = '';
}

?>