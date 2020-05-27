<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\extraArrayProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class modifies the original one by adding an extra array property that does not exist on the original
 */
class ObjectToAlter extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;
        $this->_types['name'] = [20, DataBaseObjectsManager::NOT_NULL, DataBaseObjectsManager::STRING];
        $this->_types['arrayProp'] = [DataBaseObjectsManager::ARRAY, DataBaseObjectsManager::INT, 4];
    }


    public $name = '';

    public $city = '';

    public $arrayProp = '';
}

?>