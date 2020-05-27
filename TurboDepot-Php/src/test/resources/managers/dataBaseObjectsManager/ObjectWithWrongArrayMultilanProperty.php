<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithWrongArrayMultilanProperty extends DataBaseObject{


    protected function setup(){

        // This object must fail when saving cause arrays are not allowed on multilanguage properties
        $this->_types['arrayMul'] = [self::ARRAY, self::STRING, 10, self::MULTI_LANGUAGE];
    }

    public $arrayMul = '';
}

?>