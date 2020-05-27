<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;


class CustomerWithArrayProps extends DataBaseObject{


    protected function setup(){

    }


    public $name = '';

    public $age = 0;

    public $emails = [];

    public $boolArray = [];

    public $intArray = [];

    public $doubleArray = [];
}

?>