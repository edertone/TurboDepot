<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This customer class has an error on array type definition cause it does not declare the type size
 */
class CustomerTypedArrayWithoutSize extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [20, self::NOT_NULL, self::STRING];
        $this->_types['arrayProp'] = [self::ARRAY, self::DOUBLE];
    }


    public $name = '';

    public $arrayProp = [];
}

?>