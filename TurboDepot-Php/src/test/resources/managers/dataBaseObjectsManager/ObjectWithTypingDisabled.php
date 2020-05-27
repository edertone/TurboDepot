<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithTypingDisabled extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;

        $this->_types['array'] = [DataBaseObject::ARRAY, 20, DataBaseObject::STRING];
    }

    public $array = [];

    public $notDefined = '';
}

?>