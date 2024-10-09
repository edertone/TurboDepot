<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManager;


use org\turbodepot\src\main\php\model\DataBaseObject;


class ObjectWithWrongPropThatStartsWithUnderscore extends DataBaseObject{


    public $_name = '';
}
