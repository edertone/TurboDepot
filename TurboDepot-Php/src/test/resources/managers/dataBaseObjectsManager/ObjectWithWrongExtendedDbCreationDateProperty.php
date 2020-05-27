<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;


class ObjectWithWrongExtendedDbCreationDateProperty extends DataBaseObject{


    protected function setup(){

    }


    public $dbCreationDate = null;
}

?>