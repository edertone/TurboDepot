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


/**
 * This suite of tests contains all the tests that are related with creating and saving objects to database
 *
 * @return void
 */
class DataBaseObjectsManager_Objects_create_and_save_MariaDb_Test extends TestCase {


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
     */
    public function testTablesPrefix(){

        $objectTableName = $this->sut->tablesPrefix.'customer';
        $this->assertFalse($this->db->tableExists($objectTableName));

        $object = new Customer();
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue($this->db->tableExists($objectTableName));
        $this->assertSame($this->sut->getTableNameFromObject($object), $objectTableName);

        $this->sut->tablesPrefix = 'new_';

        $object = new Customer();
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue($this->db->tableExists($objectTableName));
        $this->assertTrue($this->db->tableExists($this->sut->tablesPrefix.'customer'));
        $this->assertNotSame($this->sut->getTableNameFromObject($object), $objectTableName);
        $this->assertSame($this->sut->getTableNameFromObject($object), $this->sut->tablesPrefix.'customer');
    }


    /**
     * test
     */
    public function testIsColumnDeletedWhenMissingOnObject(){

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


    /**
     * test
     */
    public function testIsColumnResizedWhenValueisBigger(){

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


    /**
     * test
     */
    public function testIsTrashEnabled(){

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


    /**
     * test
     */
    public function testConstruct(){

        // Test empty values
        // Test ok values
        // Test wrong values
        // Test exceptions
        $this->assertSame('org\turbodepot\src\main\php\managers\DataBaseObjectsManager', get_class(new DataBaseObjectsManager()));
    }


    /**
     * test
     */
    public function testConnectMysql(){

        // Test empty values
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMysql(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMysql('', '', ''); }, '/host and userName must be non empty strings/');

        // Test ok values
        $test = new DataBaseObjectsManager();
        $this->assertTrue($test->connectMysql($this->dbSetup->host, $this->dbSetup->user, $this->dbSetup->psw));
        $this->assertTrue($test->disconnect());

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMysql(123123, 'root', ''); }, '/value is not a string/');
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMysql([1,2,3,], 'root', ''); }, '/value is not a string/');
    }


    /**
     * test
     */
    public function testConnectMariaDb(){

        // Test empty values
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMariaDb(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMariaDb('', '', ''); }, '/host and userName must be non empty strings/');

        // Test ok values
        $test = new DataBaseObjectsManager();
        $this->assertTrue($test->connectMariaDb($this->dbSetup->host, $this->dbSetup->user, $this->dbSetup->psw));
        $this->assertTrue($test->disconnect());

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMariaDb(123123, 'root', ''); }, '/value is not a string/');
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMariaDb([1,2,3,], 'root', ''); }, '/value is not a string/');
    }


    /**
     * test
     */
    public function testGetDataBaseManager(){

        // Test empty values
        // Test ok values
        // Test wrong values
        // Test exceptions
        $this->assertSame('org\turbodepot\src\main\php\managers\DataBaseManager', get_class($this->sut->getDataBaseManager()));
    }


    /**
     * test
     */
    public function testSave(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->save(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function(){ $this->sut->save(null); }, '/must be an instance of .*DataBaseObject/');
        AssertUtils::throwsException(function(){ $this->sut->save(''); }, '/must be an instance of .*DataBaseObject/');
        AssertUtils::throwsException(function(){ $this->sut->save(new stdClass()); }, '/must be an instance of .*DataBaseObject/');

        $object = new Customer();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $this->assertSame(null, $object->getDbId());
        $this->assertSame(null, $object->getDbCreationDate());
        $this->assertSame(null, $object->getDbModificationDate());

        // Test ok values - new instances
        AssertUtils::throwsException(function() use ($objectTableName) { $this->db->tableCountRows($objectTableName); }, '/Could not count table rows: Table .*td_customer.* doesn\'t exist/');
        $this->assertRegExp('/Table .*customer\' doesn\'t exist/', $this->db->getLastError());

        $object = new Customer();
        $this->assertSame(1, $this->sut->save($object));
        $this->assertSame(1, $object->getDbId());
        $this->assertSame(1, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'dbuuid' => 'varchar(36)',
            'dbcreationdate' => 'datetime(6) NOT NULL', 'dbmodificationdate' => 'datetime(6) NOT NULL', 'dbdeleted' => 'datetime(6)', 'name' => 'varchar(1) NOT NULL',
            'commercialname' => 'varchar(1) NOT NULL', 'age' => 'smallint(6) NOT NULL', 'debt' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        // test that columns are in the correct order
        $this->assertSame(['dbid', 'dbuuid', 'dbcreationdate', 'dbmodificationdate', 'dbdeleted', 'name', 'commercialname', 'age', 'debt'],
            $this->db->tableGetColumnNames($objectTableName));

        // Test that datetime values are stored with miliseconds information
        $this->assertRegExp('/[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]\.[0-9][0-9][0-9]/',
            $this->db->tableGetColumnValues($objectTableName, 'dbcreationdate')[0]);

        $this->assertRegExp('/[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]\.[0-9][0-9][0-9]/',
            $this->db->tableGetColumnValues($objectTableName, 'dbmodificationdate')[0]);

        $this->assertSame($this->db->tableGetColumnValues($objectTableName, 'dbcreationdate')[0], $this->db->tableGetColumnValues($objectTableName, 'dbmodificationdate')[0]);

        $object = new Customer();
        $this->assertSame(2, $this->sut->save($object));
        $this->assertSame(2, $object->getDbId());
        $this->assertSame(2, $this->db->tableCountRows($objectTableName));

        $object = new Customer();
        $this->assertSame(3, $this->sut->save($object));
        $this->assertSame(3, $object->getDbId());
        $this->assertSame(3, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'dbuuid' => 'varchar(36)',
            'dbcreationdate' => 'datetime(6) NOT NULL', 'dbmodificationdate' => 'datetime(6) NOT NULL', 'dbdeleted' => 'datetime(6)', 'name' => 'varchar(1) NOT NULL',
            'commercialname' => 'varchar(1) NOT NULL', 'age' => 'smallint(6) NOT NULL', 'debt' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        // Test ok values - update instances

        $object = new Customer();
        $object->age = 14123412341;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customer column age has type: smallint.6. NOT NULL but trying to set: bigint NOT NULL/');

        $object = new Customer();
        $object->age = 14123412341345345345345345345;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customer column age has type: smallint.6. NOT NULL but trying to set: double NOT NULL/');

        $object = new Customer();
        $object->name = 'customer';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customer column name has type: varchar.1. NOT NULL but trying to set: varchar.8. NOT NULL/');

        $this->sut->isColumnResizedWhenValueisBigger = true;
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame(4, $object->getDbId());
        $this->assertSame('customer', $object->name);
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'dbuuid' => 'varchar(36)',
            'dbcreationdate' => 'datetime(6) NOT NULL', 'dbmodificationdate' => 'datetime(6) NOT NULL', 'dbdeleted' => 'datetime(6)', 'name' => 'varchar(8) NOT NULL',
            'commercialname' => 'varchar(1) NOT NULL', 'age' => 'smallint(6) NOT NULL', 'debt' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        $object->name = 'customer updated';
        $this->assertSame(4, $object->getDbId());
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame('customer updated', $object->name);
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'dbuuid' => 'varchar(36)',
            'dbcreationdate' => 'datetime(6) NOT NULL', 'dbmodificationdate' => 'datetime(6) NOT NULL', 'dbdeleted' => 'datetime(6)', 'name' => 'varchar(16) NOT NULL',
            'commercialname' => 'varchar(1) NOT NULL', 'age' => 'smallint(6) NOT NULL', 'debt' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        $object->name = 'customer updated with a much longer text that should resize the name column to a bigger varchar size';
        $this->assertSame(4, $object->getDbId());
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame('customer updated with a much longer text that should resize the name column to a bigger varchar size', $object->name);
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'dbuuid' => 'varchar(36)',
            'dbcreationdate' => 'datetime(6) NOT NULL', 'dbmodificationdate' => 'datetime(6) NOT NULL', 'dbdeleted' => 'datetime(6)', 'name' => 'varchar(100) NOT NULL',
            'commercialname' => 'varchar(1) NOT NULL', 'age' => 'smallint(6) NOT NULL', 'debt' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        $object->debt = 10;
        $this->assertSame(4, $object->getDbId());
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));

        // test that columns are in the correct order
        $this->assertSame(['dbid', 'dbuuid', 'dbcreationdate', 'dbmodificationdate', 'dbdeleted', 'name', 'commercialname', 'age', 'debt'],
            $this->db->tableGetColumnNames($objectTableName));

        // Test wrong values
        // Test exceptions

        AssertUtils::throwsException(function() use ($object) { $this->sut->save(new ObjectWithWrongExtendedDbCreationDateProperty()); }, '/Overriding private db property is not allowed: dbCreationDate/');
        AssertUtils::throwsException(function() use ($object) { $this->sut->save(new ObjectWithWrongExtendedDbIdProperty()); }, '/Overriding private db property is not allowed: dbId/');
        AssertUtils::throwsException(function() use ($object) { $this->sut->save(new ObjectWithWrongExtendedDbDeletedProperty()); }, '/Overriding private db property is not allowed: dbDeleted/');

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbId');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, -1);
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer dbId: -1/');
        $this->assertSame(-1, $object->getDbId());

        // Put a non existant id number
        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbId');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, 5000000);
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not update row on table td_customer for dbid=\'5000000\'/');

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbId');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, 'string');
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer dbId: string/');
        $this->assertSame('string', $object->getDbId());

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbUUID');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, 123);
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer dbUUID: 123/');

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbUUID');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, 'notanid');
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer dbUUID: notanid/');

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbCreationDate');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, 9234);
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/dbCreationDate .9234. is not a DATETIME.6.$/');

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbCreationDate');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, 'not a date');
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/dbCreationDate .not a date. is not a DATETIME.6.$/');

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbCreationDate');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, '2019-11-16 10:41:38.123');
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/dbCreationDate .2019-11-16 10:41:38.123. must have a UTC timezone$/');

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbCreationDate');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, '2019-11-16 10:41:38.123456Z');
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Creation and modification date must be null if dbid is null/');

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbModificationDate');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, '2019-11-16 10:41:38.123');
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/dbModificationDate .2019-11-16 10:41:38.123. must have a UTC timezone$/');

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbModificationDate');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, '2019-11-16 10:41:38.123456Z');
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Creation and modification date must be null if dbid is null/');

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbModificationDate');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, 1);
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/dbModificationDate .1. is not a DATETIME.6.$/');

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbModificationDate');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, 'hello');
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/dbModificationDate .hello. is not a DATETIME.6.$/');
        $this->assertSame(null, $object->getDbId());
        $this->assertSame(null, $object->getDbCreationDate());
        $this->assertSame('hello', $object->getDbModificationDate());

        $object = new Customer();
        $reflectionProperty = (new ReflectionObject($object))->getParentClass()->getProperty('dbDeleted');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, 1);
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/dbDeleted .1. is not a DATETIME.6.$/');

        $object = new Customer();
        $object->name = 12345;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customer column name has type: varchar.100. NOT NULL but trying to set: mediumint NOT NULL/');

        $object = new Customer();
        $object->name = new stdClass();
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not detect property name type: Could not detect type from object/');

        $object = new Customer();
        $object->age = 'string instead of int';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customer column age has type: smallint.6. NOT NULL but trying to set: varchar.21. NOT NULL/');

        $object = new Customer();
        $object->age = 1.12;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customer column age has type: smallint.6. NOT NULL but trying to set: double NOT NULL/');

        $object = new Customer();
        $object->debt = 'notadouble';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customer column debt has type: double NOT NULL but trying to set: varchar.10. NOT NULL/');

        AssertUtils::throwsException(function() { $this->sut->save(new DataBaseManager()); }, '/Argument 1 passed to.* must be an instance of.*DataBaseObject, instance of.*DataBaseManager given/');

        // Try to save database objects that contains invalid methods or properties
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongMethods()); }, '/Method is not allowed for DataBaseObject class org.*ObjectWithWrongMethods: methodThatCantBeHere/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongPropThatStartsWithUnderscore()); }, '/Properties starting with _ are forbidden, but found: _name/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongNullNonTypedProperty()); }, '/Could not detect property age type: Could not detect type from NULL/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongTypeHasDuplicateValues()); }, '/Duplicate value <STRING> found on _types for name property/');
        AssertUtils::throwsException(function() { new ObjectWithWrongNonExistantTypedProperty(); }, '/Cannot define type for nonexistant cause it does not exist on class/');

        // Add an unexpected column to the customer table and make sure saving fails
        $this->assertTrue($this->db->tableAddColumn($objectTableName, 'unexpected', 'bigint'));
        AssertUtils::throwsException(function() { $this->sut->save(new Customer()); }, '/<td_customer> table contains a column which must exist as a basic property on object being saved.*<unexpected> exists on <td_customer> but not on provided tableDef/');

        // All exceptions must have not created any database object
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
    }


    /**
     * test
     */
    public function testSave_multiple_objects_on_a_single_transaction(){

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


    /**
     * test
     */
    public function testSave_null_and_not_null_values(){

        // Null values can't be detected on objects that have no specific types defined
        $object = new Customer();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = null;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not detect property name type: Could not detect type from NULL/');

        $object = new Customer();
        $this->assertSame(1, $this->sut->save($object));
        $this->assertSame('', $this->db->tableGetColumnValues($objectTableName, 'name')[0]);

        // We cannot save a null value even if the customer table is already created. Null values are not accepted by properties without a specific type definition
        $object = new Customer();
        $object->name = null;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not detect property name type: Could not detect type from NULL/');

        $object = new Customer();
        $object->age = null;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not detect property age type: Could not detect type from NULL/');

        $object = new CustomerTyped();
        $object->name = null;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/NULL value is not accepted by name property$/');

        $object = new CustomerTyped();
        $typedObjectTableName = $this->sut->getTableNameFromObject($object);
        $object->commercialName = null;
        $this->assertSame(1, $this->sut->save($object));
        $this->assertSame(null, $this->db->tableGetColumnValues($typedObjectTableName, 'commercialname')[0]);

        $object = new CustomerTyped();
        $object->birthDate = null;
        $this->assertSame(2, $this->sut->save($object));
        $this->assertSame('', $this->db->tableGetColumnValues($typedObjectTableName, 'commercialname')[1]);
        $this->assertSame(null, $this->db->tableGetColumnValues($typedObjectTableName, 'birthdate')[1]);

        $object = new CustomerTyped();
        $object->emails = null;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/NULL value is not accepted by emails property$/');
        $object->emails = [null];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/NULL value is not accepted inside array: emails/');
        $object->emails = ['a', 'b', null];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/NULL value is not accepted inside array: emails/');

        $object = new CustomerTyped();
        $object->boolArray = null;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/NULL value is not accepted by boolArray property$/');
        $object->boolArray = [null];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/NULL value is not accepted inside array: boolArray/');
        $object->boolArray = ['a', 'b', null];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/NULL value is not accepted inside array: boolArray/');
    }


    /**
     * test
     */
    public function testSave_no_duplicate_values(){

        $object = new CustomerTypedWithNoDuplicates();
        $object->name = 'name1';
        $object->age = 25;
        $this->assertSame(1, $this->sut->save($object));

        $object = new CustomerTypedWithNoDuplicates();
        $object->name = 'name2';
        $object->age = 27;
        $this->assertSame(2, $this->sut->save($object));

        $object = new CustomerTypedWithNoDuplicates();
        $object->name = 'name1';
        $object->age = 30;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Duplicate entry \'name1\'/');

        $object = new CustomerTypedWithNoDuplicates();
        $object->name = 'name3';
        $object->age = 27;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Duplicate entry \'27\'/');

        $object->age = 31;
        $this->assertSame(5, $this->sut->save($object));

        $this->assertSame(3, $this->sut->getDataBaseManager()->tableCountRows($this->sut->getTableNameFromObject($object)));
    }


    /**
     * test
     */
    public function testSave_no_duplicate_values_with_complex_indices(){

        $object = new CustomerTypedWithNoDuplicates();
        $object->name = 'name1';
        $object->age = 25;
        $object->address = 'address 1';
        $object->city = 'city 1';
        $this->assertSame(1, $this->sut->save($object));

        $object = new CustomerTypedWithNoDuplicates();
        $object->name = 'name2';
        $object->age = 26;
        $object->address = 'address 2';
        $object->city = 'city 2';
        $this->assertSame(2, $this->sut->save($object));

        $object = new CustomerTypedWithNoDuplicates();
        $object->name = 'name3';
        $object->age = 27;
        $object->address = 'address 1';
        $object->city = 'city 3';
        $this->assertSame(3, $this->sut->save($object));

        $object = new CustomerTypedWithNoDuplicates();
        $object->name = 'name1';
        $object->age = 28;
        $object->address = 'address 1';
        $object->city = 'city 4';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Duplicate entry \'name1\'/');

        $object = new CustomerTypedWithNoDuplicates();
        $object->name = 'name4';
        $object->age = 29;
        $object->address = 'address 1';
        $object->city = 'city 1';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Duplicate entry \'city 1-address 1\'/');

        $object = new CustomerTypedWithNoDuplicates();
        $object->name = 'name5';
        $object->age = 30;
        $object->address = 'address 2';
        $object->city = 'city 1';
        $this->assertSame(6, $this->sut->save($object));
    }


    /**
     * test
     */
    public function testSave_datetime_values_are_as_expected(){

        // Test that creation and modification dates are correct
        $object = new Customer();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $dateBeforeCreation = (new DateTimeObject())->toString();
        $this->assertSame(1, $this->sut->save($object));
        $dateAfterCreation = (new DateTimeObject())->toString();

        $objectCreationDate = $this->db->tableGetColumnValues($objectTableName, 'dbCreationDate')[0];
        $objectModificationDate = $this->db->tableGetColumnValues($objectTableName, 'dbModificationDate')[0];
        $this->assertTrue((new DateTimeObject($objectCreationDate))->isEqualTo(new DateTimeObject($object->getDbCreationDate())));
        $this->assertTrue((new DateTimeObject($objectModificationDate))->isEqualTo(new DateTimeObject($object->getDbModificationDate())));

        $this->assertSame($objectCreationDate, $objectModificationDate);
        $this->assertContains(DateTimeObject::compare($dateBeforeCreation, $objectCreationDate), [0, 2], $dateBeforeCreation.' must be before '.$objectCreationDate);
        $this->assertContains(DateTimeObject::compare($dateAfterCreation, $objectCreationDate), [0, 1], $dateAfterCreation.' must be after '.$objectCreationDate);
        $this->assertContains(DateTimeObject::compare($dateBeforeCreation, $objectModificationDate), [0, 2], $dateBeforeCreation.' must be before '.$objectModificationDate);
        $this->assertContains(DateTimeObject::compare($dateAfterCreation, $objectModificationDate), [0, 1], $dateAfterCreation.' must be after '.$objectModificationDate);

        // Test that modification date has changed after some time has passed, but creation date remains the same
        sleep(1);
        $this->assertSame(1, $this->sut->save($object));
        $dateAfterModification = (new DateTimeObject())->toString();

        $objectCreationDate2 = $this->db->tableGetColumnValues($objectTableName, 'dbCreationDate')[0];
        $objectModificationDate = $this->db->tableGetColumnValues($objectTableName, 'dbModificationDate')[0];
        $this->assertTrue((new DateTimeObject($objectCreationDate2))->isEqualTo(new DateTimeObject($object->getDbCreationDate())));
        $this->assertTrue((new DateTimeObject($objectModificationDate))->isEqualTo(new DateTimeObject($object->getDbModificationDate())));

        $this->assertSame($objectCreationDate, $objectCreationDate2);
        $this->assertSame(2, DateTimeObject::compare($objectCreationDate2, $objectModificationDate), $objectCreationDate2.' must be before '.$objectModificationDate);
        $this->assertContains(DateTimeObject::compare($dateBeforeCreation, $objectCreationDate2), [0, 2], $dateBeforeCreation.' must be after '.$objectCreationDate2);
        $this->assertSame(1, DateTimeObject::compare($dateAfterModification, $objectCreationDate2), $dateAfterModification.' must be after '.$objectCreationDate2);
        $this->assertSame(2, DateTimeObject::compare($dateBeforeCreation, $objectModificationDate), $dateBeforeCreation.' must be before '.$objectModificationDate);
        $this->assertContains(DateTimeObject::compare($dateAfterModification, $objectModificationDate), [0, 1], $dateAfterModification.' must be after '.$objectModificationDate);

        // Make sure all object dates are always UTC
        $this->assertTrue((new DateTimeObject($object->getDbCreationDate()))->isUTC());
        $this->assertTrue((new DateTimeObject($object->getDbModificationDate()))->isUTC());
        $this->assertNull($object->getDbDeleted());

        $objectTyped = new CustomerTyped();
        $objectTyped->birthDate = (new DateTimeObject())->toString('Y-M-D H:N:SOffset');
        $objectTyped->miliSecondsDate = (new DateTimeObject())->toString('Y-M-DTH:N:S.uZ');
        $objectTyped->microSecondsDate = (new DateTimeObject())->toString();
        $this->assertSame(1, $this->sut->save($objectTyped));

        $this->assertTrue((new DateTimeObject($objectTyped->getDbCreationDate()))->isUTC());
        $this->assertTrue((new DateTimeObject($objectTyped->getDbModificationDate()))->isUTC());
        $this->assertNull($objectTyped->getDbDeleted());
        $this->assertTrue((new DateTimeObject($objectTyped->birthDate))->isUTC());
        $this->assertTrue((new DateTimeObject($objectTyped->miliSecondsDate))->isUTC());
        $this->assertTrue((new DateTimeObject($objectTyped->microSecondsDate))->isUTC());

        // Test that non UTC values throw exceptions
        $nonUtcDate = (new DateTimeObject())->setTimeZoneOffset('+05:00');
        $reflectionProperty = (new ReflectionObject($objectTyped))->getParentClass()->getProperty('dbModificationDate');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($objectTyped, $nonUtcDate->toString('Y-M-DTH:N:SOffset'));
        AssertUtils::throwsException(function() use ($objectTyped) { $this->sut->save($objectTyped); }, '/dbModificationDate .....-..-.....:..:..\+05:00. must have a UTC timezone$/');

        $objectTyped = new CustomerTyped();
        $objectTyped->birthDate = $nonUtcDate->toString('Y-M-DTH:N:SOffset');
        AssertUtils::throwsException(function() use ($objectTyped) { $this->sut->save($objectTyped); }, '/birthDate .....-..-.....:..:..\+05:00. must have a UTC timezone$/');

        $nonUtcDate->setTimeZoneOffset('-05:00');
        $objectTyped = new CustomerTyped();
        $objectTyped->miliSecondsDate = $nonUtcDate->toString('Y-M-D H:N:S.uOffset');
        AssertUtils::throwsException(function() use ($objectTyped) { $this->sut->save($objectTyped); }, '/miliSecondsDate .....-..-.....:..:..\....-05:00. must have a UTC timezone$/');

        $nonUtcDate->setTimeZoneOffset('+03:50');
        $objectTyped = new CustomerTyped();
        $objectTyped->microSecondsDate = $nonUtcDate->toString('Y-M-DTH:N:S.UOffset');
        AssertUtils::throwsException(function() use ($objectTyped) { $this->sut->save($objectTyped); }, '/microSecondsDate .....-..-.....:..:..\.......+03:50. must have a UTC timezone$/');

        // Dates without a specifically defined UTC timezone will throw an error
        $objectTyped = new CustomerTyped();
        $objectTyped->miliSecondsDate = '2019-01-01 01:01:01.333';
        AssertUtils::throwsException(function() use ($objectTyped) { $this->sut->save($objectTyped); }, '/miliSecondsDate .2019-01-01 01:01:01.333. must have a UTC timezone$/');

        // Date values cannot be empty strings. Only null (if null is accepted)
        $objectTyped = new CustomerTyped();
        $objectTyped->miliSecondsDate = '';
        AssertUtils::throwsException(function() use ($objectTyped) { $this->sut->save($objectTyped); }, '/miliSecondsDate .. is not a DATETIME.3.$/');

        $objectTyped = new CustomerTyped();
        $objectTyped->miliSecondsDate = null;
        $this->assertSame(2, $this->sut->save($objectTyped));

        $dateObject = new ObjectWithDateTimeNotNull();
        AssertUtils::throwsException(function() use ($dateObject) { $this->sut->save($dateObject); }, '/NULL value is not accepted by date property$/');
    }


    /**
    * test
    */
    public function testSave_simple_object_performs_no_more_than_5_db_queries(){

        $this->assertFalse($this->sut->getDataBaseManager()->isAnyTransactionActive());
        $this->assertSame(0, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $object = new Customer();
        $object->name = 'customer';
        $this->assertSame(1, $this->sut->save($object));

        $this->assertFalse($this->sut->getDataBaseManager()->isAnyTransactionActive());
        $this->assertSame(5, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $object = new Customer();
        $object->name = 'c2';
        $this->assertSame(2, $this->sut->save($object));
        $this->assertSame(10, count($this->sut->getDataBaseManager()->getQueryHistory()));
    }


    /** test */
    public function testSave_and_update_simple_object_with_array_typed_properties(){

        $this->assertSame(0, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $object = new CustomerWithArrayProps();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = 'this customer has array typed properties';
        $object->emails = ['email1', 'email2', 'email3'];
        $object->boolArray = [true, false];
        $object->intArray = [10, 20, 30, 40];
        $object->doubleArray = [10.0, 100.454, 0.254676];
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(21, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'dbuuid' => 'varchar(36)',
            'dbcreationdate' => 'datetime(6) NOT NULL', 'dbmodificationdate' => 'datetime(6) NOT NULL', 'dbdeleted' => 'datetime(6)', 'name' => 'varchar(40) NOT NULL',
            'age' => 'smallint(6) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'varchar(6) NOT NULL'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));

        $this->assertSame(['1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'dbid'));
        $this->assertSame(['0', '1', '2'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'arrayindex'));
        $this->assertSame(['email1', 'email2', 'email3'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'tinyint(1) NOT NULL'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_boolarray'));

        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_boolarray', 'dbid'));
        $this->assertSame(['0', '1'], $this->db->tableGetColumnValues($objectTableName.'_boolarray', 'arrayindex'));
        $this->assertSame(['1', '0'], $this->db->tableGetColumnValues($objectTableName.'_boolarray', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'smallint(6) NOT NULL'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_intarray'));

        $this->assertSame(['1', '1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'dbid'));
        $this->assertSame(['0', '1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'arrayindex'));
        $this->assertSame(['10', '20', '30', '40'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'double NOT NULL'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_doublearray'));

        $this->assertSame(['1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_doublearray', 'dbid'));
        $this->assertSame(['0', '1', '2'], $this->db->tableGetColumnValues($objectTableName.'_doublearray', 'arrayindex'));
        $this->assertSame(['10', '100.454', '0.254676'], $this->db->tableGetColumnValues($objectTableName.'_doublearray', 'value'));

        // Update the existing object with new array values
        // Warning: This next test may fail if the database is not able to store miliseconds precision on dates. This will lead to the same modification date
        // on the updated object, and therefore no changes will be detected on the object table so no rows will be updated and an error will happen.
        $object->emails = ['new1', 'new2'];
        $object->boolArray = [false, true];
        $object->intArray = [40, 30, 20, 10];
        $object->doubleArray = [9.999];
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'varchar(6) NOT NULL'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));

        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'dbid'));
        $this->assertSame(['new1', 'new2'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'tinyint(1) NOT NULL'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_boolarray'));

        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_boolarray', 'dbid'));
        $this->assertSame(['0', '1'], $this->db->tableGetColumnValues($objectTableName.'_boolarray', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'smallint(6) NOT NULL'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_intarray'));

        $this->assertSame(['1', '1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'dbid'));
        $this->assertSame(['0', '1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'arrayindex'));
        $this->assertSame(['40', '30', '20', '10'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'double NOT NULL'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_doublearray'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_doublearray', 'dbid'));
        $this->assertSame(['9.999'], $this->db->tableGetColumnValues($objectTableName.'_doublearray', 'value'));

        $object->emails = [34563456, 1232323, 12];
        $object->boolArray = [];
        $object->intArray = [];
        $object->doubleArray = [];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customerwitharrayprops_emails column value has type: varchar.6. NOT NULL but trying to set: int NOT NULL/');

        $object = new CustomerWithArrayProps();
        $this->assertSame(2, $this->sut->save($object));
        $object->emails = ['email1', 'email2', 'email3'];
        $this->assertSame(2, $this->sut->save($object));
        $object->boolArray = [true, false, false];
        $this->assertSame(2, $this->sut->save($object));
        $this->assertSame(2, $object->getDbId());

        // Test wrong values

        $object->emails = ['this value is too long for the created table'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customerwitharrayprops_emails column value has type: varchar.6. NOT NULL but trying to set: varchar.44. NOT NULL/');

        $object->emails = ['ok', 'this value is too long for the created table'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customerwitharrayprops_emails column value has type: varchar.6. NOT NULL but trying to set: varchar.44. NOT NULL/');

        $object = new CustomerWithArrayProps();
        $object->intArray = ['string'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customerwitharrayprops_intarray column value has type: smallint.6. NOT NULL but trying to set: varchar.6. NOT NULL/');

        $object = new CustomerWithArrayProps();
        $object->intArray = [111, 452435234523452345];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customerwitharrayprops_intarray column value has type: smallint.6. NOT NULL but trying to set: bigint NOT NULL/');

        $object = new CustomerWithArrayProps();
        $object->boolArray = [111];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_customerwitharrayprops_boolarray column value has type: tinyint.1. NOT NULL but trying to set: smallint NOT NULL/');

        $object = new CustomerWithArrayProps();
        $object->name = ['storing an array into a non array prop'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/<td_customerwitharrayprops> table contains a column which must exist as a basic property on object being saved.*<name> exists on <td_customerwitharrayprops> but not on provided tableDef/');

        $object = new CustomerWithArrayProps();
        $object->name = 'this customer has array typed properties';
        $object->emails = ['email1', 'email2', 'email3'];
        $object->boolArray = [true, 123];
        $object->intArray = [10, 20, 30, 40];
        $object->doubleArray = [10.0, 100.454, 0.254676];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not detect property boolArray type: All array elements must be the same type/');

        $object = new CustomerWithArrayProps();
        $object->name = '';
        $object->emails = ['email1', 1232323, 'email3'];
        $object->boolArray = [true];
        $object->intArray = [10];
        $object->doubleArray = [0.254676];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not detect property emails type: All array elements must be the same type/');
    }


    /** test */
    public function testSave_simple_object_with_array_typed_properties_when_the_arrayindex_column_has_been_deliberately_deleted(){

        $object = new CustomerWithArrayProps();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = 'this customer has array typed properties';
        $object->emails = ['email1'];
        $object->boolArray = [true, false];
        $object->intArray = [10, 20, 30, 40];
        $object->doubleArray = [10.0, 100.454, 0.254676];
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(['dbid', 'arrayindex', 'value'], $this->db->tableGetColumnNames($objectTableName.'_emails'));

        $this->assertNotSame(false, $this->db->query('ALTER TABLE '.$objectTableName.'_emails DROP FOREIGN KEY '.$objectTableName.'_emails_dbid_fk'));
        $this->assertNotSame(false, $this->db->query('ALTER TABLE '.$objectTableName.'_emails DROP INDEX '.$objectTableName.'_emails_dbid_arrayindex_uk'));
        $this->assertTrue($this->db->tableDeleteColumns($objectTableName.'_emails', ['arrayindex']));

        $this->assertSame(['dbid', 'value'], $this->db->tableGetColumnNames($objectTableName.'_emails'));

        $this->assertSame(1, $this->sut->save($object));
        $this->assertSame(['dbid', 'arrayindex', 'value'], $this->db->tableGetColumnNames($objectTableName.'_emails'));
        $this->assertNotSame(false, $this->db->query('ALTER TABLE '.$objectTableName.'_emails DROP FOREIGN KEY '.$objectTableName.'_emails_dbid_fk'));
        $this->assertNotSame(false, $this->db->query('ALTER TABLE '.$objectTableName.'_emails DROP INDEX '.$objectTableName.'_emails_dbid_arrayindex_uk'));

        $object->emails = ['email1', 'email2', 'email3'];
        $this->assertSame(1, $this->sut->save($object));
        $this->assertSame(['email1', 'email2', 'email3'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));

        $this->assertNotSame(false, $this->db->query('ALTER TABLE '.$objectTableName.'_emails DROP FOREIGN KEY '.$objectTableName.'_emails_dbid_fk'));
        $this->assertNotSame(false, $this->db->query('ALTER TABLE '.$objectTableName.'_emails DROP INDEX '.$objectTableName.'_emails_dbid_arrayindex_uk'));
        $this->assertTrue($this->db->tableDeleteColumns($objectTableName.'_emails', ['arrayindex']));
        $this->assertSame(['dbid', 'value'], $this->db->tableGetColumnNames($objectTableName.'_emails'));

        AssertUtils::throwsException(function() use ($object) { $this->assertSame(1, $this->sut->save($object)); },
            '/Could not add unique index td_customerwitharrayprops_emails_dbid_arrayindex_uk.*Duplicate entry/');
    }


    /** test */
    public function testSave_Strong_typed_Object(){

        // Test empty values
        // Not necessary

        // Test ok values
        $object = new CustomerTyped();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = 'customer';
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(17, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'dbuuid' => 'varchar(36)',
            'dbcreationdate' => 'datetime(6) NOT NULL', 'dbmodificationdate' => 'datetime(6) NOT NULL', 'dbdeleted' => 'datetime(6)', 'name' => 'varchar(20) NOT NULL',
            'commercialname' => 'varchar(25)', 'birthdate' => 'datetime', 'milisecondsdate' => 'datetime(3)', 'microsecondsdate' => 'datetime(6)', 'age' => 'smallint(6)',
            'onedigitint' => 'smallint(6)', 'sixdigitint' => 'mediumint(9)', 'twelvedigitint' => 'bigint(20)', 'doublevalue' => 'double',
            'setup' => 'tinyint(1)'
            ], $this->db->tableGetColumnDataTypes($objectTableName));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL'],
                $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));

        $this->assertSame([], $this->db->tableGetColumnValues($objectTableName.'_emails', 'dbid'));

        // Update the object by modifying some properties values
        $object->birthDate = '2019-12-01 12:00:01+00:00';
        $this->assertSame(1, $this->sut->save($object));

        $object->miliSecondsDate = '2019-12-01 12:00:01.123+00:00';
        $this->assertSame(1, $this->sut->save($object));

        $object->emails = ['mail1', 'mail2'];
        $this->assertSame(1, $this->sut->save($object));

        $object->boolArray = [true, true];
        $this->assertSame(1, $this->sut->save($object));

        $object->intArray = [1, 2, 3, 4];
        $this->assertSame(1, $this->sut->save($object));

        $object->doubleArray = [1, 2, 3, 4];
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'varchar(75)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));

        $this->assertSame(['mail1', 'mail2'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'tinyint(1) NOT NULL'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_boolarray'));

        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_boolarray', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'smallint(6)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_intarray'));

        $this->assertSame(['1', '2', '3', '4'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'double'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_doublearray'));

        $this->assertSame(['1', '2', '3', '4'], $this->db->tableGetColumnValues($objectTableName.'_doublearray', 'value'));

        $object2 = new ObjectWithTypingDisabled();
        $this->assertSame(1, $this->sut->save($object2));

        // Test wrong values
        // Test exceptions
        $object->name = [];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/unexpected array value for property: name/');

        $object->name = ['a'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/unexpected array value for property: name/');

        $object = new CustomerTyped();
        $object->name = [];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/unexpected array value for property: name/');

        $object = new CustomerTyped();
        $object->name = ['a'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/unexpected array value for property: name/');

        $object = new CustomerTyped();
        $object->name = '123456789012345678901';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/name value size 21 exceeds 20$/');

        $object = new CustomerTyped();
        $object->name = 123123;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/name .123123. does not match STRING.20.$/');

        $object = new CustomerTyped();
        $object->commercialName = '12345678901234567890123456';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/commercialName value size 26 exceeds 25$/');

        $object = new CustomerTyped();
        $object->birthDate = 12345;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/birthDate .12345. is not a DATETIME.0.$/');

        $object = new CustomerTyped();
        $object->birthDate = 'notadatestring';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/birthDate .notadatestring. is not a DATETIME.0.$/');

        $object = new CustomerTyped();
        $object->birthDate = '2019-10-12';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/birthDate .2019-10-12. must have a UTC timezone$/');

        $object = new CustomerTyped();
        $object->birthDate = '2019-10-12 23:10:x';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/birthDate .2019-10-12 23:10:x. is not a DATETIME.0.$/');

        $object = new CustomerTyped();
        $object->birthDate = '2019-10-12 23:10:667';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/birthDate .2019-10-12 23:10:667. is not a DATETIME.0.$/');

        $object = new CustomerTyped();
        $object->miliSecondsDate = 12345;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/miliSecondsDate .12345. is not a DATETIME.3.$/');

        $object = new CustomerTyped();
        $object->miliSecondsDate = 'notadatestring';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/miliSecondsDate .notadatestring. is not a DATETIME.3.$/');

        $object = new CustomerTyped();
        $object->miliSecondsDate = '2019-10-12 23:10:26';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/miliSecondsDate .2019-10-12 23:10:26. must have a UTC timezone$/');

        $object = new CustomerTyped();
        $object->miliSecondsDate = '2019-10-12 23:10:26.00+00:00';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/miliSecondsDate .2019-10-12 23:10:26.00.00:00. does not match DATETIME.3.$/');

        $object = new CustomerTyped();
        $object->miliSecondsDate = '2019-10-12 23:10:26.0000+00:00';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/miliSecondsDate .2019-10-12 23:10:26.0000.00:00. does not match DATETIME.3.$/');

        $object = new CustomerTyped();
        $object->microSecondsDate = 12345;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/microSecondsDate .12345. is not a DATETIME.6.$/');

        $object = new CustomerTyped();
        $object->microSecondsDate = 'notadatestring';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/microSecondsDate .notadatestring. is not a DATETIME.6.$/');

        $object = new CustomerTyped();
        $object->microSecondsDate = '2019-10-12 23:10:26+00:00';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/microSecondsDate .2019-10-12 23:10:26.00:00. does not match DATETIME.6.$/');

        $object = new CustomerTyped();
        $object->microSecondsDate = '2019-10-12 23:10:26.000+00:00';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/microSecondsDate .2019-10-12 23:10:26.000.00:00. does not match DATETIME.6.$/');

        $object = new CustomerTyped();
        $object->microSecondsDate = '2019-10-12 23:10:26.00000+00:00';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/microSecondsDate .2019-10-12 23:10:26.00000.00:00. does not match DATETIME.6.$/');

        $object = new CustomerTyped();
        $object->age = 'stringinsteadofint';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/age .stringinsteadofint. does not match INT.2.$/');

        $object = new CustomerTyped();
        $object->age = 10.2;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/age .10.2. does not match INT.2.$/');

        $object = new CustomerTyped();
        $object->age = 123;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/age value size 3 exceeds 2$/');

        $object = new CustomerTyped();
        $object->oneDigitInt = 12;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/oneDigitInt value size 2 exceeds 1$/');

        $object = new CustomerTyped();
        $object->sixDigitInt = 1234567;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/sixDigitInt value size 7 exceeds 6$/');

        $object = new CustomerTyped();
        $object->twelveDigitInt = 1234567890123;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/twelveDigitInt value size 13 exceeds 12$/');

        $object = new CustomerTyped();
        $object->doubleValue = 'string';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/doubleValue .string. does not match DOUBLE.5.$/');

        $object = new CustomerTyped();
        $object->setup = 'notabool';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/setup .notabool. does not match BOOL.1.$/');

        $object = new CustomerTyped();
        $object->emails = 12;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/emails must be an array/');

        $object = new CustomerTyped();
        $object->emails = [12, 123];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/emails.*does not match STRING.75./s');

        $object = new CustomerTyped();
        $object->emails = ['a', 'aaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/emails value size 76 exceeds 75$/');

        $object = new CustomerTyped();
        $object->boolArray = ['string', 'string'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/boolArray.*string.*does not match BOOL.1./s');

        $object = new CustomerTyped();
        $object->intArray = 'string';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/intArray must be an array/');

        $object = new CustomerTyped();
        $object->intArray = ['string', 'string'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/intArray.*string.*does not match INT.3./s');

        $object = new CustomerTyped();
        $object->intArray = [1, 22, 333, 4444];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/intArray value size 4 exceeds 3$/');

        $object = new CustomerTyped();
        $object->doubleArray = ['string', 'string'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/doubleArray.*string.*does not match DOUBLE.5./s');

        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongStringTypeSize()); }, '/name is defined as STRING but size is invalid/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongDateTypeSize()); }, '/date DATETIME size must be 0, 3 or 6/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongArrayTypeSize()); }, '/array is defined as an array of STRING but size is invalid/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongArrayNoTypeSpecified()); }, '/arrayVal defined as ARRAY but no type for the array elements is specified/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongNotAllTypesDefined()); }, '/notDefined has no defined type but typing is mandatory. Define a type or disable this restriction by setting _isTypingMandatory = false/');
    }

    /**
     * test
     */
    public function testSave_Strong_typed_Object_that_does_not_declare_a_type_size(){

        $object = new CustomerTypedWithoutSize();
        $object->name = 'john';
        $object->commercialName = 'Smith';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/commercialName size is not specified/');

        $object = new CustomerTypedArrayWithoutSize();
        $object->name = 'john';
        $object->arrayProp = [true];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/arrayProp size is not specified/');

        // This is simply to avoid the "test does not perform any assertion" warning
        $this->assertTrue(true);
    }


    /**
     * test
     */
    public function testSave_Object_With_Multi_Language_Properties(){

        // Test empty values
        AssertUtils::throwsException(function() { new CustomerLocalized(); }, '/Class is multi language and expects a list of locales/');
        AssertUtils::throwsException(function() { new CustomerLocalized(null); }, '/must be of the type array/');
        AssertUtils::throwsException(function() { new CustomerLocalized(''); }, '/must be of the type array/');
        AssertUtils::throwsException(function() { new CustomerLocalized([]); }, '/Class is multi language and expects a list of locales/');

        // Test ok values

        // Test saving the first empty instance to the database with only the empty locale defined
        $object = new CustomerLocalized(['']);
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $this->assertSame(1, $this->sut->save($object));

        $objectMainTableTypes = ['dbid' => 'bigint(20) unsigned NOT NULL', 'dbuuid' => 'varchar(36)',
            'dbcreationdate' => 'datetime(6) NOT NULL', 'dbmodificationdate' => 'datetime(6) NOT NULL', 'dbdeleted' => 'datetime(6)',
            'name' => 'varchar(250)', 'birthdate' => 'datetime', 'age' => 'smallint(6)', 'setup' => 'tinyint(1)'];

        $this->assertSame($objectMainTableTypes, $this->db->tableGetColumnDataTypes($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20)'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'dbid'));
        $this->assertSame([null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', '_'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalizednotnull'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', 'dbid'));
        $this->assertSame([''], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', '_'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'datetime'], $this->db->tableGetColumnDataTypes($objectTableName.'_birthdatelocalized'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', 'dbid'));
        $this->assertSame([null], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', '_'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'smallint(6)'], $this->db->tableGetColumnDataTypes($objectTableName.'_agelocalized'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'dbid'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', '_'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'tinyint(1)'], $this->db->tableGetColumnDataTypes($objectTableName.'_setuplocalized'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'dbid'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', '_'));

        // Test saving a second empty instance to the database with a different locale than the previous empty locale one.
        $object = new CustomerLocalized(['en_US']);
        $this->assertSame(2, $this->sut->save($object));

        $this->assertSame($objectMainTableTypes, $this->db->tableGetColumnDataTypes($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20)', 'en_us' => 'varchar(20)', '_' => 'varchar(20)'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'dbid'));
        $this->assertSame([null, null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', '_'));
        $this->assertSame([null, null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_us'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20) NOT NULL', 'en_us' => 'varchar(20) NOT NULL', '_' => 'varchar(20) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalizednotnull'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', 'dbid'));
        $this->assertSame(['', ''], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', '_'));
        $this->assertSame(['', ''], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', 'en_us'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'datetime', 'en_us' => 'datetime', '_' => 'datetime'], $this->db->tableGetColumnDataTypes($objectTableName.'_birthdatelocalized'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', 'dbid'));
        $this->assertSame([null, null], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', '_'));
        $this->assertSame([null, null], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', 'en_us'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'smallint(6)', 'en_us' => 'smallint(6)', '_' => 'smallint(6)'], $this->db->tableGetColumnDataTypes($objectTableName.'_agelocalized'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'dbid'));
        $this->assertSame(['0', '0'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', '_'));
        $this->assertSame([null, '0'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'en_us'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'tinyint(1)', 'en_us' => 'tinyint(1)', '_' => 'tinyint(1)'], $this->db->tableGetColumnDataTypes($objectTableName.'_setuplocalized'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'dbid'));
        $this->assertSame(['0', '0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', '_'));
        $this->assertSame([null, '0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'en_us'));

        // Test saving a third empty instance to the database with a list of the 2 previously saved locales plus a new one
        $object = new CustomerLocalized(['en_US', '', 'es_ES']);
        $this->assertSame(3, $this->sut->save($object));

        $this->assertSame($objectMainTableTypes, $this->db->tableGetColumnDataTypes($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20)', 'en_us' => 'varchar(20)', '_' => 'varchar(20)', 'es_es' => 'varchar(20)'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'dbid'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', '_'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_us'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'es_es'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20) NOT NULL', 'en_us' => 'varchar(20) NOT NULL', '_' => 'varchar(20) NOT NULL', 'es_es' => 'varchar(20) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalizednotnull'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', 'dbid'));
        $this->assertSame(['', '', ''], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', '_'));
        $this->assertSame(['', '', ''], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', 'en_us'));
        $this->assertSame(['', '', ''], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', 'es_es'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'datetime', 'en_us' => 'datetime', '_' => 'datetime', 'es_es' => 'datetime'], $this->db->tableGetColumnDataTypes($objectTableName.'_birthdatelocalized'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', 'dbid'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', '_'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', 'en_us'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', 'es_es'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'smallint(6)', 'en_us' => 'smallint(6)', '_' => 'smallint(6)', 'es_es' => 'smallint(6)'], $this->db->tableGetColumnDataTypes($objectTableName.'_agelocalized'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'dbid'));
        $this->assertSame(['0', '0', '0'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', '_'));
        $this->assertSame([null, '0', '0'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'en_us'));
        $this->assertSame([null, null, '0'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'es_es'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'tinyint(1)', 'en_us' => 'tinyint(1)', '_' => 'tinyint(1)', 'es_es' => 'tinyint(1)'], $this->db->tableGetColumnDataTypes($objectTableName.'_setuplocalized'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'dbid'));
        $this->assertSame(['0', '0', '0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', '_'));
        $this->assertSame([null, '0', '0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'en_us'));
        $this->assertSame([null, null, '0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'es_es'));

        // TODO - It's been finally decided to not destroy locale columns from multi locale props tables, cause they are not annoying even if not used. Test that this happens as expected
        // TODO - test saving several objects, modifying the same object on already saved locale values, etc..
        // TODO - what happens when we create an object with an A non localized prop, save it to db, and then alter the class to make the A prop a multilanguage one, and then save again?
        // TODO - Create a new test case : Test what should happen when saving a db object with a string property, then modify that property to be a multi language property and save it again
        // TODO - Create a new test case : Test what should happen when saving a db object with a string property, then modify that property to be an array property and save it again

        // Test wrong values
        $object = new CustomerLocalized(['en_US']);
        $object->nameLocalizedNotNull = null;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/NULL value is not accepted by nameLocalizedNotNull property .locale en_US.$/');

        $object = new CustomerLocalized(['en_US']);
        $object->nameLocalized = [];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/unexpected array value for property: nameLocalized/');
        $object->nameLocalized = [1,2,3];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/nameLocalized.*does not match STRING.20./s');
        // TODO - more wrong values

        // Test exceptions
        AssertUtils::throwsException(function() { new Customer(['es_ES']); }, '/Class is not multi language and does not expect a list of locales/');
        AssertUtils::throwsException(function() { new CustomerLocalized([' ']); }, '/Invalid locale specified:  /');
        AssertUtils::throwsException(function() { new CustomerLocalized(['es']); }, '/Invalid locale specified: es/');
        AssertUtils::throwsException(function() { new CustomerLocalized(['es_']); }, '/Invalid locale specified: es_/');
        AssertUtils::throwsException(function() { new CustomerLocalized(['es_E']); }, '/Invalid locale specified: es_E/');
        AssertUtils::throwsException(function() { new CustomerLocalized(['es_Es']); }, '/Invalid locale specified: es_Es/');
        AssertUtils::throwsException(function() { new CustomerLocalized(['es_ES', 'a']); }, '/Invalid locale specified: a/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongArrayMultilanProperty()); }, '/Class is multi language and expects a list of locales/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongArrayMultilanProperty([''])); }, '/ARRAY type is not supported by multi language properties: arrayMul/');
    }


    /**
     * test
     */
    public function testSave_Objects_With_Multi_Language_Properties_and_real_data(){

        $object = new CustomerLocalized(['en_US']);
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->age = 25;
        $object->ageLocalized = 20;
        $object->birthDate = '1950-10-25 00:00:00+00:00';
        $object->birthDateLocalized = '1950-10-20 00:00:00+00:00';
        $object->name = 'William';
        $object->nameLocalized = 'William USA';
        $object->nameLocalizedNotNull = 'not null name USA';
        $object->setup = true;
        $object->setupLocalized = false;
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20)', 'en_us' => 'varchar(20)'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));
        $this->assertSame(['William'], $this->db->tableGetColumnValues($objectTableName, 'name'));
        $this->assertSame(['William USA'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_US'));

        $object = new CustomerLocalized(['es_ES', 'en_US']);
        $object->age = 25;
        $object->ageLocalized = 20;
        $object->birthDate = '1950-10-25 00:00:00+00:00';
        $object->birthDateLocalized = '1950-10-20 00:00:00+00:00';
        $object->name = 'Guillermo';
        $object->nameLocalized = 'Guillermo ES';
        $object->nameLocalizedNotNull = 'not null name ES';
        $object->setup = true;
        $object->setupLocalized = false;
        $this->assertSame(2, $this->sut->save($object));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20)', 'es_es' => 'varchar(20)', 'en_us' => 'varchar(20)'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));
        $this->assertSame(['William', 'Guillermo'], $this->db->tableGetColumnValues($objectTableName, 'name'));
        $this->assertSame(['William USA', null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_US'));
        $this->assertSame([null, 'Guillermo ES'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'es_ES'));

        $object = new CustomerLocalized(['fr_FR']);
        $object->age = 25;
        $object->ageLocalized = 20;
        $object->birthDate = '1950-10-25 00:00:00+00:00';
        $object->birthDateLocalized = '1950-10-20 00:00:00+00:00';
        $object->name = 'Wilanceau';
        $object->nameLocalized = 'Wilanceau FR';
        $object->nameLocalizedNotNull = 'not null name FR';
        $object->setup = true;
        $object->setupLocalized = false;
        $this->assertSame(3, $this->sut->save($object));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20)', 'fr_fr' => 'varchar(20)', 'es_es' => 'varchar(20)', 'en_us' => 'varchar(20)'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));
        $this->assertSame(['William', 'Guillermo', 'Wilanceau'], $this->db->tableGetColumnValues($objectTableName, 'name'));
        $this->assertSame(['William USA', null, 'Wilanceau FR'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_US'));
        $this->assertSame([null, 'Guillermo ES', 'Wilanceau FR'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'es_ES'));
        $this->assertSame([null, null, 'Wilanceau FR'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'fr_FR'));
    }


    /** test */
    public function testSave_Object_with_table_that_exists_with_a_single_valid_column(){

        $object = new Customer();
        $object->name = 'cust1';
        $objectTableName = $this->sut->getTableNameFromObject($object);

        $this->assertFalse($this->db->tableExists($objectTableName));
        $this->db->tableCreate($objectTableName, ['dbid bigint(20) unsigned NOT NULL AUTO_INCREMENT'], ['dbid']);
        $this->assertTrue($this->db->tableExists($objectTableName));

        $this->assertSame('cust1', $this->sut->findByDbId(Customer::class, $this->sut->save($object))->name);
    }


    /** test */
    public function testSave_Object_with_table_that_exists_with_half_the_amount_of_valid_columns(){

        $object = new Customer();
        $object->name = 'cust1';
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $this->assertFalse($this->db->tableExists($objectTableName));
        $dbid = $this->sut->save($object);
        $this->assertTrue($this->db->tableExists($objectTableName));

        $this->db->tableDeleteColumns($objectTableName, ['name', 'commercialname', 'age', 'debt']);
        $this->assertFalse(in_array('commercialname', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertFalse(in_array('age', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertFalse(in_array('debt', $this->db->tableGetColumnNames($objectTableName), true));

        $dbid = $this->sut->save($object);
        $this->assertTrue(in_array('commercialname', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertTrue(in_array('age', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertTrue(in_array('debt', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertSame('cust1', $this->sut->findByDbId(Customer::class, $dbid)->name);
    }


    /** test */
    public function testSave_Object_with_table_that_exists_with_all_the_columns_except_one(){

        $object = new Customer();
        $object->name = 'cust1';
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $this->assertFalse($this->db->tableExists($objectTableName));
        $dbid = $this->sut->save($object);
        $this->assertTrue($this->db->tableExists($objectTableName));

        $this->db->tableDeleteColumns($objectTableName, ['debt']);
        $this->assertTrue(in_array('commercialname', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertTrue(in_array('age', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertFalse(in_array('debt', $this->db->tableGetColumnNames($objectTableName), true));

        $dbid = $this->sut->save($object);
        $this->assertTrue(in_array('commercialname', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertTrue(in_array('age', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertTrue(in_array('debt', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertSame('cust1', $this->sut->findByDbId(Customer::class, $dbid)->name);
    }


    /** test */
    public function testSave_Object_with_table_that_exists_with_one_extra_column_that_is_not_found_on_the_object(){

        $object = new Customer();
        $object->name = 'cust1';
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $this->assertFalse($this->db->tableExists($objectTableName));
        $this->sut->save($object);
        $this->assertTrue($this->db->tableExists($objectTableName));

        $this->assertTrue($this->db->tableAddColumn($objectTableName, 'invalid', $this->db->getSQLTypeFromValue('hello')));
        $this->assertTrue(in_array('invalid', $this->db->tableGetColumnNames($objectTableName), true));

        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); },
            '/<td_customer> table contains a column which must exist as a basic property.*<invalid> exists on <td_customer> but not on provided tableDef.*/');

        $this->sut->isColumnDeletedWhenMissingOnObject = true;

        $this->assertSame('cust1', $this->sut->findByDbId(Customer::class, $this->sut->save($object))->name);
        $this->assertTrue(in_array('commercialname', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertFalse(in_array('invalid', $this->db->tableGetColumnNames($objectTableName), true));
    }


    /** test */
    public function testSave_Object_with_table_that_exists_with_three_extra_columns_that_are_not_found_on_the_object(){

        $object = new Customer();
        $object->name = 'cust1';
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $this->assertFalse($this->db->tableExists($objectTableName));
        $this->sut->save($object);
        $this->assertTrue($this->db->tableExists($objectTableName));

        $this->assertTrue($this->db->tableAddColumn($objectTableName, 'invalid', $this->db->getSQLTypeFromValue('hello')));
        $this->assertTrue($this->db->tableAddColumn($objectTableName, 'invalid2', $this->db->getSQLTypeFromValue('hello2')));
        $this->assertTrue($this->db->tableAddColumn($objectTableName, 'invalid3', $this->db->getSQLTypeFromValue('hello3')));
        $this->assertTrue(in_array('invalid', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertTrue(in_array('invalid2', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertTrue(in_array('invalid3', $this->db->tableGetColumnNames($objectTableName), true));

        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); },
            '/<td_customer> table contains a column which must exist as a basic property.*<invalid> exists on <td_customer> but not on provided tableDef.*/');

        $this->sut->isColumnDeletedWhenMissingOnObject = true;

        $this->assertSame('cust1', $this->sut->findByDbId(Customer::class, $this->sut->save($object))->name);
        $this->assertTrue(in_array('commercialname', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertTrue(in_array('age', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertFalse(in_array('invalid', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertFalse(in_array('invalid2', $this->db->tableGetColumnNames($objectTableName), true));
        $this->assertFalse(in_array('invalid3', $this->db->tableGetColumnNames($objectTableName), true));
    }


    /** test */
    public function testSave_Object_with_table_that_exists_with_a_primary_key_that_is_not_the_same_as_the_object_one(){

        // TODO
        $this->markTestIncomplete('This test has not been implemented yet.');
    }


    /** test */
    public function testSave_Object_with_table_that_exists_with_a_foreign_key_that_is_not_the_same_as_the_object_one(){

        // TODO
        $this->markTestIncomplete('This test has not been implemented yet.');
    }


    /** test */
    public function testSave_Object_with_table_that_has_a_column_with_a_different_type_than_the_one_on_the_object_property(){

        $object = new CustomerTyped();
        $object->name = 'cust1';
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $this->assertFalse($this->db->tableExists($objectTableName));
        $this->sut->save($object);
        $this->assertTrue($this->db->tableExists($objectTableName));

        $this->assertTrue($this->db->tableDeleteColumns($objectTableName, ['age']));
        $this->assertTrue($this->db->tableAddColumn($objectTableName, 'age', $this->db->getSQLTypeFromValue('hello')));

        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); },
        '/table td_customertyped column age has type: varchar.5. but trying to set: smallint/');
    }


    /** test */
    public function testSave_Object_with_table_that_has_a_column_with_a_different_type_size_than_the_one_on_the_object_property(){

        $object = new CustomerTyped();
        $object->name = 'cust1';
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $this->assertFalse($this->db->tableExists($objectTableName));
        $this->sut->save($object);
        $this->assertTrue($this->db->tableExists($objectTableName));

        $this->assertTrue($this->db->tableDeleteColumns($objectTableName, ['name']));
        $this->assertTrue($this->db->tableAddColumn($objectTableName, 'name', $this->db->getSQLTypeFromValue('hello')));

        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); },
            '/table td_customertyped column name has type: varchar.5. but trying to set: varchar.20. NOT NULL/');
    }


    /** test */
    public function testSave_update_object_by_adding_non_typed_prop_that_did_not_exist_previously(){

        $object = new ObjectToAlter();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = 'Jason';
        $object->city = 'New York';
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));
        $this->assertFalse(isset($this->db->tableGetColumnDataTypes($objectTableName)['extranontyped']));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_extranontyped'));

        // It is expected that the new property that previously did not exist will be transparently added to the table
        $object = new \org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\extraNonTypedProperty\ObjectToAlter();
        $object->name = 'Peter';
        $object->city = 'Chicago';
        $object->extraNonTyped = 'extra non typed value';
        $this->assertSame(2, $this->sut->save($object));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['extranontyped']));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_extranontyped'));
    }


    /**
     * test
     */
    public function testSave_update_object_by_removing_non_typed_prop_that_did_exist_previously(){

        $object = new ObjectToAlter();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = 'Jason';
        $object->city = 'New York';
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));

        $object = new \org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\removedNonTypedProperty\ObjectToAlter();
        $object->name = 'Peter';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/<td_objecttoalter> table contains a column which must exist as a basic property on object being saved.*<city> exists on <td_objecttoalter> but not on provided tableDef/');

        // Delete the city column from table and check that object can be saved correctly
        $this->db->tableDeleteColumns($objectTableName, ['city']);
        $this->assertSame(2, $this->sut->save($object));
    }


    /**
     * test
     */
    public function testSave_update_object_by_renaming_non_typed_prop_that_did_exist_previously(){

        $object = new ObjectToAlter();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = 'Jason';
        $object->city = 'New York';
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));

        $object = new \org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\renamedNonTypedProperty\ObjectToAlter();
        $object->name = 'Peter';
        $object->cityRenamed = 'New York 2';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/<td_objecttoalter> table contains a column which must exist as a basic property on object being saved.*<city> exists on <td_objecttoalter> but not on provided tableDef/');

        // Delete the city column from table and check that object can be saved and cityrenamed column's been created
        $this->db->tableDeleteColumns($objectTableName, ['city']);
        $this->assertSame(2, $this->sut->save($object));
        $this->assertFalse(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['cityrenamed']));
    }


    /**
     * test
     */
    public function testSave_update_object_by_adding_simple_typed_prop_that_did_not_exist_previously(){

        $object = new ObjectToAlter();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = 'Jason';
        $object->city = 'New York';
        $this->assertSame(1, $this->sut->save($object));
        $this->assertFalse(isset($this->db->tableGetColumnDataTypes($objectTableName)['extratyped']));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_extratyped'));

        // It is expected that the new property that previously did not exist will be transparently added to the table
        $object = new \org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\extraTypedProperty\ObjectToAlter();
        $object->name = 'Peter';
        $object->city = 'Chicago';
        $object->extraTyped = 'extra typed value';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/extraTyped .extra typed value. does not match INT.10./');
        $object->extraTyped = 345;
        $this->assertSame(2, $this->sut->save($object));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['extratyped']));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_extratyped'));
    }


    /**
     * test
     */
    public function testSave_update_object_by_removing_simple_typed_prop_that_did_exist_previously(){

        $object = new ObjectToAlter();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = 'Jason';
        $object->city = 'New York';
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));

        $object = new \org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\removedSimpleTypedProperty\ObjectToAlter();
        $object->city = 'Chicago';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/<td_objecttoalter> table contains a column which must exist as a basic property on object being saved.*<name> exists on <td_objecttoalter> but not on provided tableDef/');
    }


    /**
     * test
     */
    public function testSave_update_object_by_renaming_simple_typed_prop_that_did_exist_previously(){

        $object = new ObjectToAlter();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = 'Jason';
        $object->city = 'New York';
        $this->assertSame(1, $this->sut->save($object));

        $object = new \org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\renamedTypedProperty\ObjectToAlter();
        $object->nameRenamed = 'Renamed name';
        $object->city = 'New York City 2';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/<td_objecttoalter> table contains a column which must exist as a basic property on object being saved.*<name> exists on <td_objecttoalter> but not on provided tableDef/');

        // Delete the name column from table and check that object can be saved and nameRenamed column's been created
        $this->db->tableDeleteColumns($objectTableName, ['name']);
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/table td_objecttoalter column city has type: varchar.8. NOT NULL but trying to set: varchar.15. NOT NULL/');
        $this->sut->isColumnResizedWhenValueisBigger = true;
        $this->assertSame(2, $this->sut->save($object));
        $this->assertFalse(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['namerenamed']));
    }


    /**
     * test
     */
    public function testSave_Object_With_Multi_Language_Properties_update_object_by_adding_array_prop_that_did_not_exist_previously(){

        $object = new ObjectToAlter();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = 'Jason';
        $object->city = 'New York';
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));
        $this->assertFalse(isset($this->db->tableGetColumnDataTypes($objectTableName)['arrayProp']));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_arrayprop'));

        $object = new \org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\extraArrayProperty\ObjectToAlter();
        $object->name = 'Peter';
        $object->city = 'Chicago';
        $object->arrayProp = [1,2,3,4,5,6,7,8];
        $this->assertSame(2, $this->sut->save($object));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));
        $this->assertFalse(isset($this->db->tableGetColumnDataTypes($objectTableName)['arrayProp']));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'arrayindex' => 'bigint(20) unsigned NOT NULL', 'value' => 'smallint(6)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_arrayprop'));
    }


    /**
     * test
     */
    public function testSave_Object_With_Multi_Language_Properties_update_object_by_removing_array_prop_that_did_exist_previously(){

        // TODO - And what happens with the previously existing property table?? should it be removed also??? or what!
        // It is complicated cause we cannot know if the table exists without performing a query

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


    /**
     * test
     */
    public function testSave_Object_With_Multi_Language_Properties_update_object_by_renaming_array_prop_that_did_exist_previously(){

        // TODO - And what happens with the previously existing property table?? should it be removed also??? or what!

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


    /**
     * test
     */
    public function testSave_Object_With_Multi_Language_Properties_update_object_by_adding_multilan_prop_that_did_not_exist_previously(){

        $object = new ObjectToAlter();
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->name = 'Jason';
        $object->city = 'New York';
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));
        $this->assertFalse(isset($this->db->tableGetColumnDataTypes($objectTableName)['namelocalized']));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_namelocalized'));

        $object = new \org\turbodepot\src\test\resources\managers\dataBaseObjectsManager\altered\extraMultilanguageProperty\ObjectToAlter(['es_ES']);
        $object->name = 'Peter';
        $object->city = 'Chicago';
        $object->nameLocalized = 'Pedro';
        $this->assertSame(2, $this->sut->save($object));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['name']));
        $this->assertTrue(isset($this->db->tableGetColumnDataTypes($objectTableName)['city']));
        $this->assertFalse(isset($this->db->tableGetColumnDataTypes($objectTableName)['namelocalized']));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(400)', 'es_es' => 'varchar(400)'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));
    }


    /** test */
    public function testSave_Object_With_Multi_Language_Properties_update_object_by_removing_multilan_prop_that_did_exist_previously(){

        // TODO - And what happens with the previously existing property table?? should it be removed also??? or what!

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


    /** test */
    public function testSave_Object_With_Multi_Language_Properties_update_object_by_renaming_multilan_prop_that_did_exist_previously(){

        // TODO - And what happens with the previously existing property table?? should it be removed also??? or what!

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


    /** test */
    public function testSave_Object_With_Multi_Language_Properties_set_values_for_two_locales_back_to_first_one_and_save(){

        $object = new CustomerLocalized(['en_US', 'es_ES']);
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $object->nameLocalized = 'en name';
        $object->setLocales(['es_ES', 'en_US']);
        $object->nameLocalized = 'es name';
        $object->setLocales(['en_US', 'es_ES']);
        $object->nameLocalized = 'modified';

        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20)', 'en_us' => 'varchar(20)', 'es_es' => 'varchar(20)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));

        $this->assertSame(['modified'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_US'));
        $this->assertSame(['es name'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'es_ES'));
    }


    /**
     * test
     */
    public function test_Multi_Language_Object_invalid_values_on_multilan_properties(){

        $object = new CustomerLocalized(['en_US', 'es_ES']);
        $object->name = 123123;
        $object->setLocales(['es_ES', 'en_US']);
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/name .123123. does not match STRING.250.$/');

        $object = new CustomerLocalized(['en_US', 'es_ES']);
        $object->nameLocalized = 123123;
        $object->setLocales(['es_ES', 'en_US']);
        $object->nameLocalized = 'name';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/nameLocalized .123123. does not match STRING.20. .locale en_US.$/');

        $object = new CustomerLocalized(['en_US', 'es_ES']);
        $object->nameLocalized = 'name';
        $object->setLocales(['es_ES', 'en_US']);
        $object->nameLocalized = 123123;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/nameLocalized .123123. does not match STRING.20. .locale es_ES.$/');

        $object = new CustomerLocalized(['en_US', 'es_ES', 'fr_FR']);
        $object->nameLocalizedNotNull = 'en name';
        $object->setLocales(['es_ES', 'en_US', 'fr_FR']);
        $object->nameLocalizedNotNull = null;
        $object->setLocales(['fr_FR', 'en_US', 'es_ES']);
        $object->nameLocalizedNotNull = 'fr name';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/NULL value is not accepted by nameLocalizedNotNull property .locale es_ES.$/');

        $object = new CustomerLocalized(['en_US', 'es_ES']);
        $object->birthDateLocalized = null;
        $object->setLocales(['es_ES', 'en_US']);
        $object->birthDateLocalized = '2019-01-01';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/birthDateLocalized .2019-01-01. must have a UTC timezone .locale es_ES.$/');

        $object = new CustomerLocalized(['en_US', 'es_ES']);
        $object->ageLocalized = 1;
        $object->setLocales(['es_ES', 'en_US']);
        $object->ageLocalized = 'string';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/ageLocalized .string. does not match INT.2. .locale es_ES.$/');

        $object = new CustomerLocalized(['en_US', 'es_ES']);
        $object->setupLocalized = true;
        $object->setLocales(['es_ES', 'en_US']);
        $object->setupLocalized = 1;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/setupLocalized .1. does not match BOOL.1. .locale es_ES.$/');

        // This is simply to avoid the "test does not perform any assertion" warning
        $this->assertTrue(true);
    }


    /**
     * test
     */
    public function test_Multi_Language_Object_Change_Locales_Order(){

        $object = new CustomerLocalized(['en_US', 'es_ES', 'fr_FR', 'en_GB']);
        $object->age = 32;
        $object->ageLocalized = 1;
        $object->name = 'james';
        $object->nameLocalized = 'james';
        $object->setup = true;
        $object->setupLocalized = true;

        // Set the es_ES as the first locale and check that default values are found on object props
        $object->setLocales(['es_ES', 'en_US', 'fr_FR', 'en_GB']);
        $this->assertSame(32, $object->age);
        $this->assertSame(0, $object->ageLocalized);
        $this->assertSame('james', $object->name);
        $this->assertSame(null, $object->nameLocalized);
        $this->assertSame(true, $object->setup);
        $this->assertSame(false, $object->setupLocalized);

        // Change values for the es locale and check that moving to fr resets the multilan properties to their defaults
        $object->ageLocalized = 2;
        $object->nameLocalized = 'jaime';
        $object->setupLocalized = false;
        $object->setLocales(['fr_FR', 'es_ES', 'en_US', 'en_GB']);
        $this->assertSame(32, $object->age);
        $this->assertSame(0, $object->ageLocalized);
        $this->assertSame('james', $object->name);
        $this->assertSame(null, $object->nameLocalized);
        $this->assertSame(true, $object->setup);
        $this->assertSame(false, $object->setupLocalized);

        // Switch back to the en_US locale and test that values are the previously assigned ones
        $object->setLocales(['en_US', 'es_ES', 'fr_FR', 'en_GB']);
        $this->assertSame(32, $object->age);
        $this->assertSame(1, $object->ageLocalized);
        $this->assertSame('james', $object->name);
        $this->assertSame('james', $object->nameLocalized);
        $this->assertSame(true, $object->setup);
        $this->assertSame(true, $object->setupLocalized);

        // Switch back to the es_ES locale and test that values are the previously assigned ones
        $object->setLocales(['es_ES', 'en_US', 'fr_FR', 'en_GB']);
        $this->assertSame(32, $object->age);
        $this->assertSame(2, $object->ageLocalized);
        $this->assertSame('james', $object->name);
        $this->assertSame('jaime', $object->nameLocalized);
        $this->assertSame(true, $object->setup);
        $this->assertSame(false, $object->setupLocalized);

        // Switch to en_GB and make sure the values are the default of the class ones
        $object->setLocales(['en_GB', 'fr_FR', 'es_ES', 'en_US']);
        $this->assertSame(32, $object->age);
        $this->assertSame(0, $object->ageLocalized);
        $this->assertSame('james', $object->name);
        $this->assertSame(null, $object->nameLocalized);
        $this->assertSame(true, $object->setup);
        $this->assertSame(false, $object->setupLocalized);

        // Set some values to the en_GB locale and also to the non localized props. Switch back to es and test values are ok
        $object->age = 64;
        $object->ageLocalized = 3;
        $object->name = 'john';
        $object->nameLocalized = 'en_GB';
        $object->setup = false;
        $object->setupLocalized = false;
        $object->setLocales(['es_ES', 'en_US', 'fr_FR', 'en_GB']);
        $this->assertSame(64, $object->age);
        $this->assertSame(2, $object->ageLocalized);
        $this->assertSame('john', $object->name);
        $this->assertSame('jaime', $object->nameLocalized);
        $this->assertSame(false, $object->setup);
        $this->assertSame(false, $object->setupLocalized);

        // Switch back to the en_US locale and test that values are correct
        $object->setLocales(['en_US', 'es_ES', 'fr_FR', 'en_GB']);
        $this->assertSame(64, $object->age);
        $this->assertSame(1, $object->ageLocalized);
        $this->assertSame('john', $object->name);
        $this->assertSame('james', $object->nameLocalized);
        $this->assertSame(false, $object->setup);
        $this->assertSame(true, $object->setupLocalized);

        // Save the object and make sure tables are ok
        $objectTableName = $this->sut->getTableNameFromObject($object);
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue($this->sut->getDataBaseManager()->tableExists($objectTableName));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_age'));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_name'));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_setup'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'smallint(6)', 'en_us' => 'smallint(6)', 'es_es' => 'smallint(6)', 'fr_fr' => 'smallint(6)', 'en_gb' => 'smallint(6)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_agelocalized'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20)', 'en_us' => 'varchar(20)', 'es_es' => 'varchar(20)', 'fr_fr' => 'varchar(20)', 'en_gb' => 'varchar(20)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'tinyint(1)', 'en_us' => 'tinyint(1)', 'es_es' => 'tinyint(1)', 'fr_fr' => 'tinyint(1)', 'en_gb' => 'tinyint(1)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_setuplocalized'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'dbid'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'en_US'));
        $this->assertSame(['2'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'es_ES'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'fr_FR'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'en_GB'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'dbid'));
        $this->assertSame(['james'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_US'));
        $this->assertSame(['jaime'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'es_ES'));
        $this->assertSame([null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'fr_FR'));
        $this->assertSame(['en_GB'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_GB'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'dbid'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'en_US'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'es_ES'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'fr_FR'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'en_GB'));

        // Modify some localized values, save again and test that db values are OK
        $object->setLocales(['en_US', 'es_ES', 'fr_FR', 'en_GB']);
        $object->age = 99;
        $object->ageLocalized = 6;
        $object->name = 'johnedited';
        $object->nameLocalized = 'en_USedited';
        $object->setup = false;
        $object->setupLocalized = true;

        $object->setLocales(['es_ES', 'en_US', 'fr_FR', 'en_GB']);
        $object->ageLocalized = 7;
        $object->nameLocalized = 'jaimeditado';
        $object->setupLocalized = true;

        $object->setLocales(['fr_FR', 'es_ES', 'en_US', 'en_GB']);
        $object->ageLocalized = 8;
        $object->nameLocalized = 'frenchname';

        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue($this->sut->getDataBaseManager()->tableExists($objectTableName));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_age'));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_name'));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_setup'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'smallint(6)', 'en_us' => 'smallint(6)', 'es_es' => 'smallint(6)', 'fr_fr' => 'smallint(6)', 'en_gb' => 'smallint(6)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_agelocalized'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20)', 'en_us' => 'varchar(20)', 'es_es' => 'varchar(20)', 'fr_fr' => 'varchar(20)', 'en_gb' => 'varchar(20)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'tinyint(1)', 'en_us' => 'tinyint(1)', 'es_es' => 'tinyint(1)', 'fr_fr' => 'tinyint(1)', 'en_gb' => 'tinyint(1)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_setuplocalized'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'dbid'));
        $this->assertSame(['6'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'en_US'));
        $this->assertSame(['7'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'es_ES'));
        $this->assertSame(['8'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'fr_FR'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'en_GB'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'dbid'));
        $this->assertSame(['en_USedited'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_US'));
        $this->assertSame(['jaimeditado'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'es_ES'));
        $this->assertSame(['frenchname'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'fr_FR'));
        $this->assertSame(['en_GB'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_GB'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'dbid'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'en_US'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'es_ES'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'fr_FR'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'en_GB'));
    }


    /**
     * test
     */
    public function test_Multi_Language_Object_setLocales(){

        $object = new CustomerLocalized(['']);

        // Test empty values
        AssertUtils::throwsException(function() use($object) {$object->setLocales(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() use($object) {$object->setLocales(null); }, '/must be of the type array/');
        AssertUtils::throwsException(function() use($object) {$object->setLocales(''); }, '/must be of the type array/');
        AssertUtils::throwsException(function() use($object) {$object->setLocales([]); }, '/Class is multi language and expects a list of locales/');
        $this->assertSame([''], $object->setLocales(['']));

        // Test ok values

        $object = new CustomerLocalized(['en_US']);
        $this->assertSame(['en_US'], $object->setLocales(['en_US']));

        $object = new CustomerLocalized(['en_US', 'es_ES']);
        $this->assertSame(['en_US', 'es_ES'], $object->setLocales(['en_US', 'es_ES']));
        $this->assertSame(['es_ES', 'en_US'], $object->setLocales(['es_ES', 'en_US']));

        $object = new CustomerLocalized(['en_US', 'es_ES', 'fr_FR']);
        $this->assertSame(['en_US', 'es_ES', 'fr_FR'], $object->setLocales(['en_US', 'es_ES', 'fr_FR']));
        $this->assertSame(['es_ES', 'en_US', 'fr_FR'], $object->setLocales(['es_ES', 'en_US', 'fr_FR']));
        $this->assertSame(['fr_FR', 'en_US', 'es_ES'], $object->setLocales(['fr_FR', 'en_US', 'es_ES']));

        $object = new CustomerLocalized(['en_US', 'es_ES', '', 'fr_FR']);
        $this->assertSame(['en_US', 'es_ES', 'fr_FR', ''], $object->setLocales(['en_US', 'es_ES', 'fr_FR', '']));
        $this->assertSame(['es_ES', 'en_US', '', 'fr_FR'], $object->setLocales(['es_ES', 'en_US', '', 'fr_FR']));
        $this->assertSame(['fr_FR', 'es_ES', '', 'en_US'], $object->setLocales(['fr_FR', 'es_ES', '', 'en_US']));

        // Test wrong values
        AssertUtils::throwsException(function() use($object) {$object->setLocales(['es_ES', 'es_ES']); }, '/Duplicate elements found on locales list/');
        AssertUtils::throwsException(function() use($object) {$object->setLocales(['es_ES', 'fr_FR', 'es_ES']); }, '/Duplicate elements found on locales list/');
        AssertUtils::throwsException(function() use($object) {$object->setLocales(['a']); }, '/Locales cannot be added or removed from an already created instance, only sorted/');
        AssertUtils::throwsException(function() use($object) {$object->setLocales(['es_ES', 'Es_ES']); }, '/Locales cannot be added or removed from an already created instance, only sorted/');
        AssertUtils::throwsException(function() use($object) {$object->setLocales(['fr_FR', 'es_ES', 'en_US']); }, '/Locales cannot be added or removed from an already created instance, only sorted/');

        // Test exceptions
        AssertUtils::throwsException(function() use($object) {$object->setLocales(123456); }, '/must be of the type array/');
        AssertUtils::throwsException(function() use($object) {$object->setLocales('string'); }, '/must be of the type array/');
        AssertUtils::throwsException(function() use($object) {(new Customer())->setLocales(['es_ES']); }, '/Class is not multi language and does not expect a list of locales/');
    }


    /**
     * test
     */
    public function test_Multi_Language_Object_isMultiLanguage(){

        // Test empty values
        // Not necessary

        // Test ok values
        $this->assertFalse((new Customer())->isMultiLanguage());
        $this->assertFalse((new CustomerTyped())->isMultiLanguage());
        $this->assertTrue((new CustomerLocalized(['']))->isMultiLanguage());

        // Test wrong values
        // Test exceptions
        // Not necessary
    }


    /**
     * test
     */
    public function test_Multi_Language_Object_getLocales(){

        $object = new CustomerLocalized(['']);

        // Test empty values
        $this->assertSame([''], $object->getLocales());

        // Test ok values
        $object = new CustomerLocalized(['es_ES']);
        $object->setLocales(['es_ES']);
        $this->assertSame(['es_ES'], $object->getLocales());

        $object = new CustomerLocalized(['en_US', 'es_ES']);
        $object->setLocales(['en_US', 'es_ES']);
        $this->assertSame(['en_US', 'es_ES'], $object->getLocales());
        $object->setLocales(['es_ES', 'en_US']);
        $this->assertSame(['es_ES', 'en_US'], $object->getLocales());

        $object = new CustomerLocalized(['', 'en_US', 'es_ES']);
        $object->setLocales(['', 'es_ES', 'en_US']);
        $this->assertSame(['', 'es_ES', 'en_US'], $object->getLocales());

        $object = new CustomerLocalized(['', 'en_US', 'es_ES', 'fr_FR']);
        $object->setLocales(['', 'fr_FR', 'en_US', 'es_ES']);
        $this->assertSame(['', 'fr_FR', 'en_US', 'es_ES'], $object->getLocales());
        $object->setLocales(['', 'en_US', 'fr_FR', 'es_ES']);
        $this->assertSame(['', 'en_US', 'fr_FR', 'es_ES'], $object->getLocales());

        // Test wrong values
        // Test exceptions
        $object = new Customer();
        AssertUtils::throwsException(function() use($object) {$object->getLocales(); }, '/Class is not multi language/');
    }


    /**
     * test
     */
    public function testGetTableNameFromObject(){

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


    /**
     * test
     */
    public function testGetSQLTypeFromObjectProperty(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->getSQLTypeFromObjectProperty(null, null); }, '/Argument 1.*must be an instance of.*DataBaseObject, null given/');
        AssertUtils::throwsException(function() { $this->sut->getSQLTypeFromObjectProperty(new Customer(), null); }, '/Argument 2 .* must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->getSQLTypeFromObjectProperty(new Customer(), ''); }, '/Could not detect property  type: .* property  does not exist/');

        // Test ok values
        $object = new Customer();
        $this->assertSame('varchar(1) NOT NULL', $this->sut->getSQLTypeFromObjectProperty($object, 'name'));
        $this->assertSame('varchar(1) NOT NULL', $this->sut->getSQLTypeFromObjectProperty($object, 'commercialName'));
        $this->assertSame('smallint NOT NULL', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));

        $object->name = 'customer name';
        $object->commercialName = 'commercial name';
        $object->age = 12456;
        $this->assertSame('varchar(13) NOT NULL', $this->sut->getSQLTypeFromObjectProperty($object, 'name'));
        $this->assertSame('varchar(15) NOT NULL', $this->sut->getSQLTypeFromObjectProperty($object, 'commercialName'));
        $this->assertSame('mediumint NOT NULL', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));
        $object->age = 1234;
        $this->assertSame('smallint NOT NULL', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));
        $object->age = 1122212121;
        $this->assertSame('bigint NOT NULL', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));

        $object = new CustomerTyped();
        $this->assertSame('varchar(20) NOT NULL', $this->sut->getSQLTypeFromObjectProperty($object, 'name'));
        $this->assertSame('varchar(25)', $this->sut->getSQLTypeFromObjectProperty($object, 'commercialName'));
        $this->assertSame('smallint', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));
        $this->assertSame('smallint', $this->sut->getSQLTypeFromObjectProperty($object, 'oneDigitInt'));
        $this->assertSame('mediumint', $this->sut->getSQLTypeFromObjectProperty($object, 'sixDigitInt'));
        $this->assertSame('bigint', $this->sut->getSQLTypeFromObjectProperty($object, 'twelveDigitInt'));
        $this->assertSame('double', $this->sut->getSQLTypeFromObjectProperty($object, 'doubleValue'));
        $this->assertSame('tinyint(1)', $this->sut->getSQLTypeFromObjectProperty($object, 'setup'));
        $this->assertSame('varchar(75)', $this->sut->getSQLTypeFromObjectProperty($object, 'emails'));
        $this->assertSame('tinyint(1) NOT NULL', $this->sut->getSQLTypeFromObjectProperty($object, 'boolArray'));
        $this->assertSame('smallint', $this->sut->getSQLTypeFromObjectProperty($object, 'intArray'));
        $this->assertSame('double', $this->sut->getSQLTypeFromObjectProperty($object, 'doubleArray'));

        // Test wrong values
        $object = new CustomerTyped();
        $object->name = 1231231;
        $object->commercialName = new stdClass();
        $object->age = 'stringinsteadofint';
        $this->assertSame('varchar(20) NOT NULL', $this->sut->getSQLTypeFromObjectProperty($object, 'name'));
        $this->assertSame('smallint', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));
        $this->assertSame('varchar(25)', $this->sut->getSQLTypeFromObjectProperty($object, 'commercialName'));

        // Test exceptions
        AssertUtils::throwsException(function() use ($object) { $this->sut->getSQLTypeFromObjectProperty($object, 'nonexistantproperty'); }, '/nonexistantproperty has no defined type but typing is mandatory/');
        AssertUtils::throwsException(function() use ($object) { $this->sut->getSQLTypeFromObjectProperty($object, ''); }, '/ has no defined type but typing is mandatory/');
        AssertUtils::throwsException(function() { $this->sut->getSQLTypeFromObjectProperty(new stdClass(), ''); }, '/Argument 1 passed to .*getSQLTypeFromObjectProperty.*must be an instance of.*DataBaseObject.*stdClass given/');
    }
}

?>