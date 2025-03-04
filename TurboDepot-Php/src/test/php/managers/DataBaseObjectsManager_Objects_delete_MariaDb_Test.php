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
use ReflectionObject;
use stdClass;
use org\turbocommons\src\main\php\model\DateTimeObject;
use org\turbodepot\src\main\php\managers\DataBaseManager;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\Customer;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\CustomerLocalized;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\CustomerTyped;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\CustomerWithArrayProps;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectToAlter;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithDateTimeNotNull;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithTypingDisabled;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongArrayMultilanProperty;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongArrayTypeSize;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongDateTypeSize;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongExtendedDbCreationDateProperty;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongExtendedDbDeletedProperty;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongExtendedDbIdProperty;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongMethods;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongNonExistantTypedProperty;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongNotAllTypesDefined;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongNullNonTypedProperty;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongPropThatStartsWithUnderscore;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongStringTypeSize;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\CustomerTypedWithoutSize;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\CustomerTypedArrayWithoutSize;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongArrayNoTypeSpecified;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\CustomerTypedWithNoDuplicates;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongTypeHasDuplicateValues;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\CustomerTypedForeignObject;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongForeignPropertiesDefined;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongForeignPropertiesDefined2;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\ObjectWithWrongForeignPropertiesDefined3;


/**
 * This suite of tests contains all the tests that are related with removing objects from database
 *
 * @return void
 */
class DataBaseObjectsManager_Objects_delete_MariaDb_Test extends TestCase {


    /**
     * @see TestCase::setUpBeforeClass()
     *
     * @return void
     */
    public static function setUpBeforeClass(): void{
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(): void{

        $this->sut = DataBaseManager_MariaDb_Test::createAndConnectToTestingMariaDb();
        $this->db = $this->sut->getDataBaseManager();
        $this->dbSetup = json_decode((new FilesManager())->readFile(__DIR__.'/../../resources/managers/databaseManager/database-setup-for-testing.json'));
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(): void{

        DataBaseManager_MariaDb_Test::deleteAndDisconnectFromTestingMariaDb($this->sut);
    }


    /**
     * @see TestCase::tearDownAfterClass()
     *
     * @return void
     */
    public static function tearDownAfterClass(): void{
    }


    /**
     * test
     */
    public function testDeleteByInstances(){

        // Create a single customer, make sure it exists, delete it and make sure it does not exist
        $object = new Customer();
        $this->assertSame(null, $object->getDbId());
        $objectDbId = $this->sut->save($object);

        $this->assertSame(1, count($this->sut->findByDbIds(Customer::class, [$objectDbId])));
        $this->assertSame(1, $this->sut->deleteByInstances($object));
        $this->assertSame(0, count($this->sut->findByDbIds(Customer::class, [$objectDbId])));
        $this->assertSame(null, $object->getDbId());

        // Create 3 customers, make sure they exist, delete them one by one and make sure they get correctly deleted
        $object1 = new Customer();
        $this->sut->save($object1);
        $object2 = new Customer();
        $this->sut->save($object2);
        $object3 = new Customer();
        $this->sut->save($object3);

        $this->assertSame(3, count($this->sut->findAll(Customer::class)));
        $this->assertSame(1, $this->sut->deleteByInstances($object1));
        $this->assertSame(2, count($this->sut->findAll(Customer::class)));
        $this->assertSame(null, $object1->getDbId());
        $this->assertSame(1, $this->sut->deleteByInstances($object2));
        $this->assertSame(1, count($this->sut->findAll(Customer::class)));
        $this->assertSame(null, $object2->getDbId());
        $this->assertSame(1, $this->sut->deleteByInstances($object3));
        $this->assertSame(0, count($this->sut->findAll(Customer::class)));
        $this->assertSame(null, $object3->getDbId());
    }


    /**
     * test
     */
    public function testDeleteByPropertyValues(){

        // Create a single customer, make sure it exists, delete it and make sure it does not exist
        $object = new CustomerTyped();
        $object->name = 'foo';
        $this->sut->save($object);

        $this->assertSame(1, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(1, $this->sut->deleteByPropertyValues(CustomerTyped::class, ['name' => 'foo']));
        $this->assertSame(0, count($this->sut->findAll(CustomerTyped::class)));

        // Create 3 customers, make sure they exist, delete them one by one and make sure they get correctly deleted
        $object1 = new CustomerTyped();
        $object1->name = 'foo1';
        $this->sut->save($object1);
        $object2 = new CustomerTyped();
        $object2->name = 'foo2';
        $this->sut->save($object2);
        $object3 = new CustomerTyped();
        $object3->name = 'foo3';
        $this->sut->save($object3);

        $this->assertSame(3, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(1, $this->sut->deleteByPropertyValues(CustomerTyped::class, ['name' => 'foo1']));
        $this->assertSame(2, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(1, $this->sut->deleteByPropertyValues(CustomerTyped::class, ['name' => 'foo2']));
        $this->assertSame(1, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(1, $this->sut->deleteByPropertyValues(CustomerTyped::class, ['name' => 'foo3']));
        $this->assertSame(0, count($this->sut->findAll(CustomerTyped::class)));
    }


    /**
     * test
     */
    public function testDeleteByDbIds(){

        // Create a single customer, make sure it exists, delete it and make sure it does not exist
        $object = new CustomerTyped();
        $objectId = $this->sut->save($object);

        $this->assertSame(1, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(1, $this->sut->deleteByDbIds(CustomerTyped::class, [$objectId]));
        $this->assertSame(0, count($this->sut->findAll(CustomerTyped::class)));

        // Try to delete non existant ids
        AssertUtils::throwsException(function(){ $this->sut->deleteByDbIds(CustomerTyped::class, [234234]); }, '/Error deleting objects: object dbid not found: 234234/');

        // Create 3 customers, make sure they exist, delete them one by one and make sure they get correctly deleted
        $object1 = new CustomerTyped();
        $object1Id = $this->sut->save($object1);
        $object2 = new CustomerTyped();
        $object2Id = $this->sut->save($object2);
        $object3 = new CustomerTyped();
        $object3Id = $this->sut->save($object3);

        $this->assertSame(3, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(1, $this->sut->deleteByDbIds(CustomerTyped::class, [$object1Id]));
        $this->assertSame(2, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(2, $this->sut->deleteByDbIds(CustomerTyped::class, [$object2Id, $object3Id]));
        $this->assertSame(0, count($this->sut->findAll(CustomerTyped::class)));
    }


    /**
     * test
     */
    public function testDeleteByDbIds_foreign_objects_get_also_deleted(){

        // Create one customer, link it to one customer foreign object, make sure both exist, delete the customer
        // and verify that both objects have been destroyed
        $object = new CustomerTyped();
        $objectId = $this->sut->save($object);

        $foreignObject = new CustomerTypedForeignObject();
        $foreignObject->customerId = $objectId;
        $this->sut->save($foreignObject);

        $this->assertSame(1, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(1, count($this->sut->findAll(CustomerTypedForeignObject::class)));
        $this->assertSame(1, $this->sut->deleteByInstances([$object]));
        $this->assertSame(0, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(0, count($this->sut->findAll(CustomerTypedForeignObject::class)));

        // Create two customers, only one linked to foreign object, make sure all 3 exist, delete each customers and
        // verify linked object only gets deleted when correct
        $object1 = new CustomerTyped();
        $this->sut->save($object1);
        $object2 = new CustomerTyped();
        $object2Id = $this->sut->save($object2);

        $foreignObject = new CustomerTypedForeignObject();
        $foreignObject->customerId = $object2Id;
        $this->sut->save($foreignObject);

        $this->assertSame(2, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(1, count($this->sut->findAll(CustomerTypedForeignObject::class)));
        $this->assertSame(1, $this->sut->deleteByInstances([$object1]));
        $this->assertSame(1, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(1, count($this->sut->findAll(CustomerTypedForeignObject::class)));
        $this->assertSame(1, $this->sut->deleteByInstances([$object2]));
        $this->assertSame(0, count($this->sut->findAll(CustomerTyped::class)));
        $this->assertSame(0, count($this->sut->findAll(CustomerTypedForeignObject::class)));

        // Test Objects with invalid foreign object properties definitions
        $object = new ObjectWithWrongForeignPropertiesDefined();
        $this->sut->save($object);
        AssertUtils::throwsException(function() use ($object) { $this->sut->deleteByInstances([$object]); }, '/Error deleting objects: Unknown column .nonexistant. in .WHERE/');

        $object = new ObjectWithWrongForeignPropertiesDefined2();
        $this->sut->save($object);
        AssertUtils::throwsException(function() use ($object) { $this->sut->deleteByInstances([$object]); }, '/Error deleting objects: Property nonexistant does not exist/');

        $object = new ObjectWithWrongForeignPropertiesDefined3();
        $this->sut->save($object);
        AssertUtils::throwsException(function() use ($object) { $this->sut->deleteByInstances([$object]); }, '/Invalid foreign class specified: NonexistantClass/');
    }
}
