<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


/**
 * Date time property is set as a not null value
 */
class ObjectWithDateTimeNotNull extends DataBaseObject{


    protected function setup(){

        $this->_types['date'] = [self::DATETIME, self::NOT_NULL, 0];
    }


    public $date = null;
}

?>