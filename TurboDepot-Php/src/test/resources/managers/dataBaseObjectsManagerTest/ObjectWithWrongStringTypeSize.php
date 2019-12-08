<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithWrongStringTypeSize extends DataBaseObject{


    public function __construct(){

        $this->_types['name'] = [DataBaseObjectsManager::STRING, 'invalidsize'];
    }

    public $name = '';
}

?>