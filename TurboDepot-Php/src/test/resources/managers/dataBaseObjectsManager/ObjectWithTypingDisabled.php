<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithTypingDisabled extends DataBaseObject{


    protected function setup(){

        $this->_isTypingMandatory = false;

        $this->_types['array'] = [self::ARRAY, 20, self::STRING];
    }

    public $array = [];

    public $notDefined = '';
}

?>