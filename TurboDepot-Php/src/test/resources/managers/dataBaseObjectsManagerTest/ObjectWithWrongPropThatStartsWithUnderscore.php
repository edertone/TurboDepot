<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest;


use org\turbodepot\src\main\php\model\DataBaseObject;


class ObjectWithWrongPropThatStartsWithUnderscore extends DataBaseObject{


    protected function setup(){

    }


    public $_name = '';
}

?>