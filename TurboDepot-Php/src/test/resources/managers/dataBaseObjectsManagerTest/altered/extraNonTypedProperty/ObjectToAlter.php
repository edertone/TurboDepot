<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\altered\extraNonTypedProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class modifies the original one by adding an extra non typed property that does not exist on the original
 */
class ObjectToAlter extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;
        $this->_types['name'] = [20, DataBaseObjectsManager::NOT_NULL, DataBaseObjectsManager::STRING];
    }

    public $name = '';

    public $city = '';

    public $extraNonTyped = '';
}

?>