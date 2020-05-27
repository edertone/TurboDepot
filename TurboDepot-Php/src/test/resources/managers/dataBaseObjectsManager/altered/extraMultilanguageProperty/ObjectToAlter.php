<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\extraMultilanguageProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class modifies the original one by adding an extra multilanguage property that does not exist on the original
 */
class ObjectToAlter extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;
        $this->_types['name'] = [20, DataBaseObject::NOT_NULL, DataBaseObject::STRING];
        $this->_types['nameLocalized'] = [DataBaseObject::MULTI_LANGUAGE, DataBaseObject::STRING, 400];
    }


    public $name = '';

    public $city = '';

    public $nameLocalized = '';
}

?>