<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest;


use org\turbodepot\src\main\php\model\DataBaseObject;


class WrongObjectWithMethods extends DataBaseObject{

    public function methodThatCantBeHere(){

        return 1;
    }
}

?>