<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This customer class strongly defines the properties types so databaseobjects manager is able to create
 * the table columns with greater precision
 */
class CustomerTyped extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [20, DataBaseObject::NOT_NULL, DataBaseObject::STRING];
        $this->_types['commercialName'] = [DataBaseObject::STRING, 25];
        $this->_types['birthDate'] = [DataBaseObject::DATETIME, 0];
        $this->_types['miliSecondsDate'] = [3, DataBaseObject::DATETIME];
        $this->_types['microSecondsDate'] = [6, DataBaseObject::DATETIME];
        $this->_types['age'] = [DataBaseObject::INT, 2];
        $this->_types['oneDigitInt'] = [DataBaseObject::INT, 1];
        $this->_types['sixDigitInt'] = [6, DataBaseObject::INT];
        $this->_types['twelveDigitInt'] = [DataBaseObject::INT, 12];
        $this->_types['doubleValue'] = [DataBaseObject::DOUBLE, 5];
        $this->_types['setup'] = [DataBaseObject::BOOL];
        $this->_types['emails'] = [75, DataBaseObject::ARRAY, DataBaseObject::STRING];
        $this->_types['boolArray'] = [DataBaseObject::BOOL, DataBaseObject::ARRAY, DataBaseObject::NOT_NULL];
        $this->_types['intArray'] = [DataBaseObject::INT, DataBaseObject::ARRAY, 3];
        $this->_types['doubleArray'] = [DataBaseObject::DOUBLE, 5, DataBaseObject::ARRAY];
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