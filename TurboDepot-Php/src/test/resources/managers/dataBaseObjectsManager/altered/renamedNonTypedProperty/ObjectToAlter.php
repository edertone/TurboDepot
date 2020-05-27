<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\renamedNonTypedProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class modifies the original one by renaming a non typed property
 */
class ObjectToAlter extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;
        $this->_types['name'] = [20, self::NOT_NULL, self::STRING];
    }

    public $name = '';

    public $cityRenamed = '';
}

?>