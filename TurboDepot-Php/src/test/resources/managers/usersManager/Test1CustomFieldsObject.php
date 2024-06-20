<?php

namespace org\turbodepot\src\test\resources\managers\usersManager;

use org\turbodepot\src\main\php\model\DataBaseObject;


/**
 * Extra fields for the users objects
 */
class Test1CustomFieldsObject extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [100, self::NOT_NULL, self::STRING];
        $this->_types['surnames'] = [200, self::NOT_NULL, self::STRING];
        $this->_types['phone'] = [25, self::NOT_NULL, self::STRING];
        $this->_types['company'] = [200, self::NOT_NULL, self::STRING];
        $this->_types['occupation'] = [200, self::NOT_NULL, self::STRING];
        $this->_types['address'] = [300, self::NOT_NULL, self::STRING];
        $this->_types['city'] = [100, self::NOT_NULL, self::STRING];
        $this->_types['district'] = [100, self::NOT_NULL, self::STRING];
        $this->_types['postalCode'] = [20, self::NOT_NULL, self::STRING];

        $this->_uniqueIndices[] = [];
    }

    public $name = 'some name';
    public $surnames = '';
    public $phone = '23434534';
    public $company = '';
    public $occupation = 'employee';
    public $address = '';
    public $city = 'barcelona';
    public $district = '';
    public $postalCode = '34534';
}
