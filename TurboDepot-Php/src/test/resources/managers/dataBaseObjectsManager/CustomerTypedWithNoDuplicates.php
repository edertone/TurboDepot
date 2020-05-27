<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * This customer class uses the NO_DUPLICATES flag to prevent duplicate values on the properties
 */
class CustomerTypedWithNoDuplicates extends DataBaseObject{


    protected function setup(){

        $this->_types['name'] = [50, DataBaseObject::NOT_NULL, DataBaseObject::STRING, DataBaseObject::NO_DUPLICATES];
        $this->_types['age'] = [DataBaseObject::INT, DataBaseObject::NO_DUPLICATES, 4];
    }


    public $name = '';

    public $age = 0;
}

?>