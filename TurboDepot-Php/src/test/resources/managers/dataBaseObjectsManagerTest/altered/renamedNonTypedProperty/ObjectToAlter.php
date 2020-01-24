<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\altered\renamedNonTypedProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class modifies the original one by renaming a non typed property
 */
class ObjectToAlter extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;
        $this->_types['name'] = [20, DataBaseObjectsManager::NOT_NULL, DataBaseObjectsManager::STRING];
    }

    public $name = '';

    public $cityRenamed = '';
}

?>