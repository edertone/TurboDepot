<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\altered\renamedTypedProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class modifies the original one by renaming a typed property
 */
class ObjectToAlter extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;
        $this->_types['nameRenamed'] = [20, DataBaseObjectsManager::NOT_NULL, DataBaseObjectsManager::STRING];
    }

    public $nameRenamed = '';

    public $city = '';
}

?>