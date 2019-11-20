<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This customer class strongly defines the properties types so databaseobjects manager is able to create
 * the table columns with greater precision
 */
class CustomerTyped extends DataBaseObject{


    public function __construct(){

        $this->_types['name'] = [DataBaseObjectsManager::TYPE_STRING, 20];
        $this->_types['commercialName'] = [DataBaseObjectsManager::TYPE_STRING, 25];
        $this->_types['age'] = [DataBaseObjectsManager::TYPE_INT, 2];
        $this->_types['oneDigitInt'] = [DataBaseObjectsManager::TYPE_INT, 1];
        $this->_types['sixDigitInt'] = [DataBaseObjectsManager::TYPE_INT, 6];
        $this->_types['twelveDigitInt'] = [DataBaseObjectsManager::TYPE_INT, 12];
        $this->_types['doubleValue'] = [DataBaseObjectsManager::TYPE_DOUBLE];
        $this->_types['setup'] = [DataBaseObjectsManager::TYPE_BOOL];
        // TODO - define an array type
    }


    public $name = '';

    public $commercialName = '';

    public $age = 0;

    public $oneDigitInt = 0;

    public $sixDigitInt = 0;

    public $twelveDigitInt = 0;

    public $doubleValue = 0;

    public $setup = false;
}

?>