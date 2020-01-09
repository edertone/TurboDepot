<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This customer class strongly defines the properties types so databaseobjects manager is able to create
 * the table columns with greater precision
 */
class CustomerTyped extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [20, DataBaseObjectsManager::NOT_NULL, DataBaseObjectsManager::STRING];
        $this->_types['commercialName'] = [DataBaseObjectsManager::STRING, 25];
        $this->_types['birthDate'] = [DataBaseObjectsManager::DATETIME, 0];
        $this->_types['miliSecondsDate'] = [3, DataBaseObjectsManager::DATETIME];
        $this->_types['microSecondsDate'] = [6, DataBaseObjectsManager::DATETIME];
        $this->_types['age'] = [DataBaseObjectsManager::INT, 2];
        $this->_types['oneDigitInt'] = [DataBaseObjectsManager::INT, 1];
        $this->_types['sixDigitInt'] = [6, DataBaseObjectsManager::INT];
        $this->_types['twelveDigitInt'] = [DataBaseObjectsManager::INT, 12];
        $this->_types['doubleValue'] = [DataBaseObjectsManager::DOUBLE];
        $this->_types['setup'] = [DataBaseObjectsManager::BOOL];
        $this->_types['emails'] = [75, DataBaseObjectsManager::ARRAY, DataBaseObjectsManager::STRING];
        $this->_types['boolArray'] = [DataBaseObjectsManager::BOOL, DataBaseObjectsManager::ARRAY, DataBaseObjectsManager::NOT_NULL];
        $this->_types['intArray'] = [DataBaseObjectsManager::INT, DataBaseObjectsManager::ARRAY, 3];
        $this->_types['doubleArray'] = [DataBaseObjectsManager::DOUBLE, DataBaseObjectsManager::ARRAY];
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