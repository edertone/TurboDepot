<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class tries to define a type for a non existant property
 */
class ObjectWithWrongNonExistantTypedProperty extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [self::STRING, 20];
        $this->_types['commercialName'] = [self::STRING, 25];
        $this->_types['nonexistant'] = [self::INT, 2];
    }


    public $name = '';

    public $commercialName = '';
}

?>