<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest;


use org\turbodepot\src\main\php\model\DataBaseObject;


class ObjectWithWrongExtendedDbIdProperty extends DataBaseObject{


    protected function setup(){

    }


    public $dbId = null;
}

?>