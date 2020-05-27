<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;


class ObjectWithWrongExtendedDbDeletedProperty extends DataBaseObject{


    protected function setup(){

    }


    public $dbDeleted = null;
}

?>