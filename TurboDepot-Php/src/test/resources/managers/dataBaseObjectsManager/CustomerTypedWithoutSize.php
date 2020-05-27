<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This customer class has an error on type definition cause it does not declare the type size
 */
class CustomerTypedWithoutSize extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [20, DataBaseObject::NOT_NULL, DataBaseObject::STRING];
        $this->_types['commercialName'] = [DataBaseObject::STRING];
    }


    public $name = '';

    public $commercialName = '';
}

?>