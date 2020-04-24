<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This customer class has an error on array type definition cause it does not declare the type size
 */
class CustomerTypedArrayWithoutSize extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [20, DataBaseObjectsManager::NOT_NULL, DataBaseObjectsManager::STRING];
        $this->_types['arrayProp'] = [DataBaseObjectsManager::ARRAY, DataBaseObjectsManager::DOUBLE];
    }


    public $name = '';

    public $arrayProp = [];
}

?>