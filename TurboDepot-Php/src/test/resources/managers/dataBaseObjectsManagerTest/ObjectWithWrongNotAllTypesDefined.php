<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithWrongNotAllTypesDefined extends DataBaseObject{


    public function __construct(){

        $this->_types['array'] = [DataBaseObjectsManager::ARRAY, DataBaseObjectsManager::STRING];
    }

    public $array = [];

    public $notDefined = '';
}

?>