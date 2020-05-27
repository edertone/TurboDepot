<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


class ObjectWithWrongArrayNoTypeSpecified extends DataBaseObject{


    protected function setup(){

        $this->_types['arrayVal'] = [DataBaseObject::ARRAY, 20];
    }

    public $arrayVal = '';
}

?>