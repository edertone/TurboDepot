<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest;


use org\turbodepot\src\main\php\model\DataBaseObject;


class CustomerWithArrayProps extends DataBaseObject{

    public $name = '';

    public $age = 0;

    public $emails = [];

    public $boolArray = [];

    public $intArray = [];

    public $doubleArray = [];
}

?>