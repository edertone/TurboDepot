<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class defines a customer that can have multi language values for some of its properties
 */
class CustomerLocalized extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [DataBaseObject::STRING, 250];
        $this->_types['nameLocalized'] = [DataBaseObject::MULTI_LANGUAGE, DataBaseObject::STRING, 20];
        $this->_types['nameLocalizedNotNull'] = [DataBaseObject::MULTI_LANGUAGE, DataBaseObject::STRING, 20, DataBaseObject::NOT_NULL];
        $this->_types['birthDate'] = [DataBaseObject::DATETIME, 0];
        $this->_types['birthDateLocalized'] = [DataBaseObject::MULTI_LANGUAGE, DataBaseObject::DATETIME, 0];
        $this->_types['age'] = [DataBaseObject::INT, 2];
        $this->_types['ageLocalized'] = [DataBaseObject::INT, 2, DataBaseObject::MULTI_LANGUAGE];
        $this->_types['setup'] = [DataBaseObject::BOOL];
        $this->_types['setupLocalized'] = [DataBaseObject::BOOL, DataBaseObject::MULTI_LANGUAGE];
    }


    public $name = null;

    public $nameLocalized = null;

    public $nameLocalizedNotNull = '';

    public $birthDate = null;

    public $birthDateLocalized = null;

    public $age = 0;

    public $ageLocalized = 0;

    public $setup = false;

    public $setupLocalized = false;
}

?>