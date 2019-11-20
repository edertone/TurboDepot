<?php

namespace org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest;


use org\turbodepot\src\main\php\model\DataBaseObject;


class ObjectWithWrongNullNonTypedProperty extends DataBaseObject{

    public $name = '';

    public $commercialName = '';

    /**
     * This property must fail cause it is defined as null by default and no type has been specified for
     * its table column, so when creating the object table the first time an exception will happen cause no type
     * can be detected
     */
    public $age = null;
}

?>