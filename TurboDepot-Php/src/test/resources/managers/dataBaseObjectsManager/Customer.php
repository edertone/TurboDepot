<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;


class Customer extends DataBaseObject{


    protected function setup(){

    }


    public $name = '';

    public $commercialName = '';

    public $age = 0;

    public $debt = 0.0;
}
