<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\test\php\managers;

use PHPUnit\Framework\TestCase;
use stdClass;
use org\turbodepot\src\main\php\managers\DataBaseManager;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\Customer;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\CustomerTyped;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\CustomerWithArrayProps;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithWrongEmptyNonTypedArrayProperty;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithWrongMethods;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithWrongNonExistantTypedProperty;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithWrongNullNonTypedProperty;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithWrongPropThatStartsWithUnderscore;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithWrongStringTypeSize;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithWrongDateTypeSize;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithWrongArrayTypeSize;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithWrongNotAllTypesDefined;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithTypingDisabled;
use org\turbocommons\src\main\php\model\DateTimeObject;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithDateTimeNotNull;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\CustomerLocalized;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\ObjectWithWrongArrayMultilanProperty;


/**
 * This suite of tests contains all the tests that are related with reading and listing objects from database
 *
 * @return void
 */
class DataBaseObjectsManagerObjectsReadAndListMariaDbTest extends TestCase {


    /**
     * @see TestCase::setUpBeforeClass()
     *
     * @return void
     */
    public static function setUpBeforeClass(){
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(){


    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){


    }


    /**
     * @see TestCase::tearDownAfterClass()
     *
     * @return void
     */
    public static function tearDownAfterClass(){
    }

    /**
     * testTODO
     *
     * @return void
     */
    public function testTODO(){

        // Test empty values
        // TODO

        // Test ok values
        // TODO

        // Test wrong values
        // TODO

        // Test exceptions
        // TODO

        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}

?>