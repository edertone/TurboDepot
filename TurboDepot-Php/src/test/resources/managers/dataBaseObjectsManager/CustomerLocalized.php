<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class defines a customer that can have multi language values for some of its properties
 */
class CustomerLocalized extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [self::STRING, 250];
        $this->_types['nameLocalized'] = [self::MULTI_LANGUAGE, self::STRING, 20];
        $this->_types['nameLocalizedNotNull'] = [self::MULTI_LANGUAGE, self::STRING, 20, self::NOT_NULL];
        $this->_types['birthDate'] = [self::DATETIME, 0];
        $this->_types['birthDateLocalized'] = [self::MULTI_LANGUAGE, self::DATETIME, 0];
        $this->_types['age'] = [self::INT, 2];
        $this->_types['ageLocalized'] = [self::INT, 2, self::MULTI_LANGUAGE];
        $this->_types['setup'] = [self::BOOL];
        $this->_types['setupLocalized'] = [self::BOOL, self::MULTI_LANGUAGE];
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