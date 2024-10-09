<?php

namespace org\turbodepot\src\test\resources\managers\usersManager;

use org\turbodepot\src\main\php\model\DataBaseObject;


/**
 * Extra fields for the users objects
 */
class Test1CustomFieldsObject extends DataBaseObject{


    const TYPES = [

        'name' => [100, self::NOT_NULL, self::STRING],
        'surnames' => [200, self::NOT_NULL, self::STRING],
        'phone' => [25, self::NOT_NULL, self::STRING],
        'company' => [200, self::NOT_NULL, self::STRING],
        'occupation' => [200, self::NOT_NULL, self::STRING],
        'address' => [300, self::NOT_NULL, self::STRING],
        'city' => [100, self::NOT_NULL, self::STRING],
        'district' => [100, self::NOT_NULL, self::STRING],
        'postalCode' => [20, self::NOT_NULL, self::STRING]
    ];


    public $name = 'some name';
    public $surnames = '';
    public $phone = '23434534';
    public $company = '';
    public $occupation = 'employee';
    public $address = '';
    public $city = 'barcelona';
    public $district = '';
    public $postalCode = '34534';
}
