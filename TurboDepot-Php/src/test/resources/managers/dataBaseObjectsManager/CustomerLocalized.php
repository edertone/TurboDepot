<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This class defines a customer that can have multi language values for some of its properties
 */
class CustomerLocalized extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [DataBaseObjectsManager::STRING, 250];
        $this->_types['nameLocalized'] = [DataBaseObjectsManager::MULTI_LANGUAGE, DataBaseObjectsManager::STRING, 20];
        $this->_types['nameLocalizedNotNull'] = [DataBaseObjectsManager::MULTI_LANGUAGE, DataBaseObjectsManager::STRING, 20, DataBaseObjectsManager::NOT_NULL];
        $this->_types['birthDate'] = [DataBaseObjectsManager::DATETIME, 0];
        $this->_types['birthDateLocalized'] = [DataBaseObjectsManager::MULTI_LANGUAGE, DataBaseObjectsManager::DATETIME, 0];
        $this->_types['age'] = [DataBaseObjectsManager::INT, 2];
        $this->_types['ageLocalized'] = [DataBaseObjectsManager::INT, 2, DataBaseObjectsManager::MULTI_LANGUAGE];
        $this->_types['setup'] = [DataBaseObjectsManager::BOOL];
        $this->_types['setupLocalized'] = [DataBaseObjectsManager::BOOL, DataBaseObjectsManager::MULTI_LANGUAGE];
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