<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;


class ObjectWithWrongMethods extends DataBaseObject{


    public function methodThatCantBeHere(){

        return 1;
    }
}
