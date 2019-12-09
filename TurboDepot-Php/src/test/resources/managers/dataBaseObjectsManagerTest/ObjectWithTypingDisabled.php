<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithTypingDisabled extends DataBaseObject{


    public function __construct(){

        $this->_isTypingMandatory = false;

        $this->_types['array'] = [DataBaseObjectsManager::ARRAY, DataBaseObjectsManager::STRING];
    }

    public $array = [];

    public $notDefined = '';
}

?>