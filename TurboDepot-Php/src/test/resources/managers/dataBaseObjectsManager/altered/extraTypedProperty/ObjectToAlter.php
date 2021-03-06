<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\extraTypedProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class modifies the original one by adding an extra typed property that does not exist on the original
 */
class ObjectToAlter extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;
        $this->_types['name'] = [20, self::NOT_NULL, self::STRING];
        $this->_types['extraTyped'] = [10, self::INT];
    }

    public $name = '';

    public $city = '';

    public $extraTyped = '';
}

?>