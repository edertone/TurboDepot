<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\altered\removedSimpleTypedProperty;


use org\turbodepot\src\main\php\model\DataBaseObject;


/**
 * This class modifies the original one by removing a simple typed property
 */
class ObjectToAlter extends DataBaseObject{


    protected function setup(){
    }

    public $city = '';
}

?>