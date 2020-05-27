<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\removedNonTypedProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class modifies the original one by removing a non typed property
 */
class ObjectToAlter extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;
        $this->_types['name'] = [20, DataBaseObject::NOT_NULL, DataBaseObject::STRING];
    }

    public $name = '';
}

?>