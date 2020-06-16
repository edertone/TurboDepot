<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;


/**
 * This customer class strongly defines the properties types so databaseobjects manager is able to create
 * the table columns with greater precision
 */
class CustomerTyped extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [20, self::NOT_NULL, self::STRING];
        $this->_types['commercialName'] = [self::STRING, 25];
        $this->_types['birthDate'] = [self::DATETIME, 0];
        $this->_types['miliSecondsDate'] = [3, self::DATETIME];
        $this->_types['microSecondsDate'] = [6, self::DATETIME];
        $this->_types['age'] = [self::INT, 2];
        $this->_types['oneDigitInt'] = [self::INT, 1];
        $this->_types['sixDigitInt'] = [6, self::INT];
        $this->_types['twelveDigitInt'] = [self::INT, 12];
        $this->_types['doubleValue'] = [self::DOUBLE, 5];
        $this->_types['setup'] = [self::BOOL];
        $this->_types['emails'] = [75, self::ARRAY, self::STRING];
        $this->_types['boolArray'] = [self::BOOL, self::ARRAY, self::NOT_NULL];
        $this->_types['intArray'] = [self::INT, self::ARRAY, 3];
        $this->_types['doubleArray'] = [self::DOUBLE, 5, self::ARRAY];
    }


    public $name = '';

    public $commercialName = '';

    public $birthDate = null;

    public $miliSecondsDate = null;

    public $microSecondsDate = null;

    public $age = 0;

    public $oneDigitInt = 0;

    public $sixDigitInt = 0;

    public $twelveDigitInt = 0;

    public $doubleValue = 0;

    public $setup = false;

    public $emails = [];

    public $boolArray = [];

    public $intArray = [];

    public $doubleArray = [];
}

?>