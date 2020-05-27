<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;


class ObjectWithWrongTypeHasDuplicateValues extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [50, self::STRING, self::STRING];
    }

    public $name = '';
}

?>