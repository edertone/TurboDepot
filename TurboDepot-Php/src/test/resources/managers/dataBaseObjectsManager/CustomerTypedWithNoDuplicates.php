<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This customer class uses the NO_DUPLICATES flag and _uniqueIndices to prevent duplicate values on the properties
 */
class CustomerTypedWithNoDuplicates extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [50, self::NOT_NULL, self::STRING, self::NO_DUPLICATES];
        $this->_types['age'] = [self::INT, self::NO_DUPLICATES, 4];
        $this->_types['address'] = [self::STRING, 94];
        $this->_types['city'] = [self::STRING, 50];

        $this->_uniqueIndices[] = ['city', 'address'];
    }


    public $name = '';

    public $age = 0;

    public $address = null;

    public $city = null;
}

?>