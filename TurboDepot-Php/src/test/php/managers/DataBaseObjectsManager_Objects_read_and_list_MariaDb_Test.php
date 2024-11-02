<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\test\php\managers;

use PHPUnit\Framework\TestCase;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\CustomerTyped;
use org\turbodepot\src\main\php\managers\FilesManager;


/**
 * This suite of tests contains all the tests that are related with reading and listing objects from database
 *
 * @return void
 */
class DataBaseObjectsManager_Objects_read_and_list_MariaDb_Test extends TestCase {


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

        $this->sut = DataBaseManager_MariaDb_Test::createAndConnectToTestingMariaDb();
        $this->db = $this->sut->getDataBaseManager();
        $this->dbSetup = json_decode((new FilesManager())->readFile(__DIR__.'/../../resources/managers/databaseManager/database-setup-for-testing.json'));
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){

        DataBaseManager_MariaDb_Test::deleteAndDisconnectFromTestingMariaDb($this->sut);
    }


    /**
     * @see TestCase::tearDownAfterClass()
     *
     * @return void
     */
    public static function tearDownAfterClass(){
    }


    /**
     * test
     *
     * @return void
     */
    public function testFindAll(){

        // Create 1 customer typed and make sure its data is correctly obtained
        $object = new CustomerTyped();
        $object->name = 'name';
        $object->age = 25;
        $this->sut->save($object);

        $list = $this->sut->findAll(CustomerTyped::class);
        $this->assertSame(1, count($list));
        $this->assertSame('name', $list[0]->name);
        $this->assertSame('', $list[0]->commercialName);
        $this->assertSame(null, $list[0]->birthDate);
        $this->assertSame(null, $list[0]->miliSecondsDate);
        $this->assertSame(null, $list[0]->microSecondsDate);
        $this->assertSame(25, $list[0]->age);
        $this->assertSame(0, $list[0]->oneDigitInt);
        $this->assertSame(0, $list[0]->sixDigitInt);
        $this->assertSame(0, $list[0]->twelveDigitInt);
        $this->assertSame(0.0, $list[0]->doubleValue);
        $this->assertSame(false, $list[0]->setup);
        $this->assertSame([], $list[0]->emails);
        $this->assertSame([], $list[0]->boolArray);
        $this->assertSame([], $list[0]->intArray);
        $this->assertSame([], $list[0]->doubleArray);

        // Create 10 customer typed objects and verify the list is correct
        for ($i = 0; $i < 10; $i++) {

            $this->sut->save(new CustomerTyped());
        }

        $this->assertSame(11, count($this->sut->findAll(CustomerTyped::class)));
    }


    /**
     * test
     *
     * @return void
     */
    public function testFindAllToArray(){

        // Create 1 customer typed and make sure its data is correctly obtained
        $object = new CustomerTyped();
        $object->name = 'name';
        $object->age = 25;
        $this->sut->save($object);

        $list = $this->sut->findAllToArray(CustomerTyped::class);
        $this->assertSame(1, count($list));
        $this->assertSame('name', $list[0]['name']);
        $this->assertSame('', $list[0]['commercialName']);
        $this->assertSame(null, $list[0]['birthDate']);
        $this->assertSame(null, $list[0]['miliSecondsDate']);
        $this->assertSame(null, $list[0]['microSecondsDate']);
        $this->assertSame(25, $list[0]['age']);
        $this->assertSame(0, $list[0]['oneDigitInt']);
        $this->assertSame(0, $list[0]['sixDigitInt']);
        $this->assertSame(0, $list[0]['twelveDigitInt']);
        $this->assertSame(0.0, $list[0]['doubleValue']);
        $this->assertSame(false, $list[0]['setup']);
        $this->assertSame([], $list[0]['emails']);
        $this->assertSame([], $list[0]['boolArray']);
        $this->assertSame([], $list[0]['intArray']);
        $this->assertSame([], $list[0]['doubleArray']);

        // Create 10 customer typed objects and verify the list is correct
        for ($i = 0; $i < 10; $i++) {

            $this->sut->save(new CustomerTyped());
        }

        $this->assertSame(11, count($this->sut->findAllToArray(CustomerTyped::class)));
    }
}
