<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithWrongDateTypeSize extends DataBaseObject{


    protected function setup(){

        $this->_types['date'] = [DataBaseObject::DATETIME, 10];
    }

    public $date = '';
}

?>