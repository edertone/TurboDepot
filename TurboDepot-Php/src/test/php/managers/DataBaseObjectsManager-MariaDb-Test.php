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
 * DataBaseObjectsManagerTest
 *
 * @return void
 */
class DataBaseObjectsManagerTest extends TestCase {


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

        $this->dbHost = 'localhost';
        $this->dbUser = 'root';
        $this->dbPsw = '';
        $this->dbName = 'data_base_objects_manager_test';
        $this->db = new DataBaseManager();
        $this->assertTrue($this->db->connectMariaDb($this->dbHost, $this->dbUser, $this->dbPsw));

        if($this->db->dataBaseExists($this->dbName)){

            $this->db->dataBaseDelete($this->dbName);
        }

        $this->assertFalse($this->db->dataBaseExists($this->dbName));
        $this->assertTrue($this->db->dataBaseCreate($this->dbName));
        $this->assertTrue($this->db->dataBaseSelect($this->dbName));
        $this->assertSame($this->dbName, $this->db->dataBaseGetSelected());

        $this->sut = new DataBaseObjectsManager();
        $this->assertTrue($this->sut->connectMariaDb($this->dbHost, $this->dbUser, $this->dbPsw, $this->dbName));
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){

        $this->assertFalse($this->db->isAnyTransactionActive(), 'unclosed transactions exist!!');
        $this->assertFalse($this->sut->getDataBaseManager()->isAnyTransactionActive(), 'unclosed transactions exist!!');
        $this->assertTrue($this->db->dataBaseExists($this->dbName));
        $this->assertTrue($this->db->dataBaseDelete($this->dbName));

        $this->assertTrue($this->sut->disconnect());
        $this->assertTrue($this->db->disconnect());
    }


    /**
     * @see TestCase::tearDownAfterClass()
     *
     * @return void
     */
    public static function tearDownAfterClass(){
    }


    /**
     * testTablesPrefix
     *
     * @return void
     */
    public function testTablesPrefix(){

        $objectTableName = $this->sut->tablesPrefix.'customer';
        $this->assertFalse($this->db->tableExists($objectTableName));

        $object = new Customer();
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue($this->db->tableExists($objectTableName));

        $this->sut->tablesPrefix = 'new_';

        $object = new Customer();
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue($this->db->tableExists($objectTableName));
        $this->assertTrue($this->db->tableExists($this->sut->tablesPrefix.'customer'));
    }


    /**
     * testIsTableCreatedWhenMissing
     *
     * @return void
     */
    public function testIsTableCreatedWhenMissing(){

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
     * testIsTableAlteredWhenColumnsChange
     *
     * @return void
     */
    public function testIsTableAlteredWhenColumnsChange(){

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
     * testIsColumnResizedWhenValueisBigger
     *
     * @return void
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
     * testIsTrashEnabled
     *
     * @return void
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
     * testConstruct
     *
     * @return void
     */
    public function testConstruct(){

        // Test empty values
        // Test ok values
        // Test wrong values
        // Test exceptions
        $this->assertSame('org\turbodepot\src\main\php\managers\DataBaseObjectsManager', get_class(new DataBaseObjectsManager()));
    }


    /**
     * testConnectMysql
     *
     * @return void
     */
    public function testConnectMysql(){

        // Test empty values
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMysql(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMysql('', '', ''); }, '/host and userName must be non empty strings/');

        // Test ok values
        $test = new DataBaseObjectsManager();
        $this->assertTrue($test->connectMysql($this->dbHost, $this->dbUser, $this->dbPsw));
        $this->assertTrue($test->disconnect());

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMysql(123123, 'root', ''); }, '/value is not a string/');
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMysql([1,2,3,], 'root', ''); }, '/value is not a string/');
    }


    /**
     * testConnectMariaDb
     *
     * @return void
     */
    public function testConnectMariaDb(){

        // Test empty values
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMariaDb(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMariaDb('', '', ''); }, '/host and userName must be non empty strings/');

        // Test ok values
        $test = new DataBaseObjectsManager();
        $this->assertTrue($test->connectMariaDb($this->dbHost, $this->dbUser, $this->dbPsw));
        $this->assertTrue($test->disconnect());

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMariaDb(123123, 'root', ''); }, '/value is not a string/');
        AssertUtils::throwsException(function(){ (new DataBaseObjectsManager())->connectMariaDb([1,2,3,], 'root', ''); }, '/value is not a string/');
    }


    /**
     * testGetDataBaseManager
     *
     * @return void
     */
    public function testGetDataBaseManager(){

        // Test empty values
        // Test ok values
        // Test wrong values
        // Test exceptions
        $this->assertSame('org\turbodepot\src\main\php\managers\DataBaseManager', get_class($this->sut->getDataBaseManager()));
    }


    /**
     * testSave
     *
     * @return void
     */
    public function testSave(){

        $objectTableName = $this->sut->tablesPrefix.'customer';

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->save(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function(){ $this->sut->save(null); }, '/must be an instance of .*DataBaseObject/');
        AssertUtils::throwsException(function(){ $this->sut->save(''); }, '/must be an instance of .*DataBaseObject/');
        AssertUtils::throwsException(function(){ $this->sut->save(new stdClass()); }, '/must be an instance of .*DataBaseObject/');

        // Test ok values - new instances
        AssertUtils::throwsException(function() use ($objectTableName) { $this->db->tableCountRows($objectTableName); }, '/Could not count table rows: Table .*td_customer.* doesn\'t exist/');
        $this->assertRegExp('/Table .*customer\' doesn\'t exist/', $this->db->getLastError());

        $object = new Customer();
        $this->assertSame(1, $this->sut->save($object));
        $this->assertSame(1, $object->dbId);
        $this->assertSame(1, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'uuid' => 'varchar(36)', 'sortindex' => 'bigint(20) unsigned',
            'creationdate' => 'datetime(6) NOT NULL', 'modificationdate' => 'datetime(6) NOT NULL', 'deleted' => 'datetime(6)', 'name' => 'varchar(1) NOT NULL',
            'commercialname' => 'varchar(1) NOT NULL', 'age' => 'smallint(6) NOT NULL', 'debt' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        // test that columns are in the correct order
        $this->assertSame(['dbid', 'uuid', 'sortindex', 'creationdate', 'modificationdate', 'deleted', 'name', 'commercialname', 'age', 'debt'],
            $this->db->tableGetColumnNames($objectTableName));

        // Test that datetime values are stored with miliseconds information
        $this->assertRegExp('/[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]\.[0-9][0-9][0-9]/',
            $this->db->tableGetColumnValues($objectTableName, 'creationdate')[0]);

        $this->assertRegExp('/[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]\.[0-9][0-9][0-9]/',
            $this->db->tableGetColumnValues($objectTableName, 'modificationdate')[0]);

        $this->assertSame($this->db->tableGetColumnValues($objectTableName, 'creationdate')[0], $this->db->tableGetColumnValues($objectTableName, 'modificationdate')[0]);

        $object = new Customer();
        $this->assertSame(2, $this->sut->save($object));
        $this->assertSame(2, $object->dbId);
        $this->assertSame(2, $this->db->tableCountRows($objectTableName));

        $object = new Customer();
        $this->assertSame(3, $this->sut->save($object));
        $this->assertSame(3, $object->dbId);
        $this->assertSame(3, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'uuid' => 'varchar(36)', 'sortindex' => 'bigint(20) unsigned',
            'creationdate' => 'datetime(6) NOT NULL', 'modificationdate' => 'datetime(6) NOT NULL', 'deleted' => 'datetime(6)', 'name' => 'varchar(1) NOT NULL',
            'commercialname' => 'varchar(1) NOT NULL', 'age' => 'smallint(6) NOT NULL', 'debt' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        // Test ok values - update instances

        $object = new Customer();
        $object->age = 14123412341;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column age data type expected: smallint.6. NOT NULL but received: bigint/');

        $object = new Customer();
        $object->age = 14123412341345345345345345345;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column age data type expected: smallint.6. NOT NULL but received: double/');

        $object = new Customer();
        $object->name = 'customer';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column name data type expected: varchar.1. NOT NULL but received: varchar.8./');

        $this->sut->isColumnResizedWhenValueisBigger = true;
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame(4, $object->dbId);
        $this->assertSame('customer', $object->name);
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'uuid' => 'varchar(36)', 'sortindex' => 'bigint(20) unsigned',
            'creationdate' => 'datetime(6) NOT NULL', 'modificationdate' => 'datetime(6) NOT NULL', 'deleted' => 'datetime(6)', 'name' => 'varchar(8) NOT NULL',
            'commercialname' => 'varchar(1) NOT NULL', 'age' => 'smallint(6) NOT NULL', 'debt' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        $object->name = 'customer updated';
        $this->assertSame(4, $object->dbId);
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame('customer updated', $object->name);
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'uuid' => 'varchar(36)', 'sortindex' => 'bigint(20) unsigned',
            'creationdate' => 'datetime(6) NOT NULL', 'modificationdate' => 'datetime(6) NOT NULL', 'deleted' => 'datetime(6)', 'name' => 'varchar(16) NOT NULL',
            'commercialname' => 'varchar(1) NOT NULL', 'age' => 'smallint(6) NOT NULL', 'debt' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        $object->name = 'customer updated with a much longer text that should resize the name column to a bigger varchar size';
        $this->assertSame(4, $object->dbId);
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame('customer updated with a much longer text that should resize the name column to a bigger varchar size', $object->name);
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'uuid' => 'varchar(36)', 'sortindex' => 'bigint(20) unsigned',
            'creationdate' => 'datetime(6) NOT NULL', 'modificationdate' => 'datetime(6) NOT NULL', 'deleted' => 'datetime(6)', 'name' => 'varchar(100) NOT NULL',
            'commercialname' => 'varchar(1) NOT NULL', 'age' => 'smallint(6) NOT NULL', 'debt' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        $object->debt = 10;
        $this->assertSame(4, $object->dbId);
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));

        // test that columns are in the correct order
        $this->assertSame(['dbid', 'uuid', 'sortindex', 'creationdate', 'modificationdate', 'deleted', 'name', 'commercialname', 'age', 'debt'],
            $this->db->tableGetColumnNames($objectTableName));

        // Test wrong values
        // Test exceptions

        $object = new Customer();
        $object->dbId = -1;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer dbId: -1/');
        $this->assertSame(-1, $object->dbId);

        // Put a non existant id number
        $object = new Customer();
        $object->dbId = 5000000;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not update row on table td_customer for dbid=\'5000000\'/');

        $object = new Customer();
        $object->dbId = 'string';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer dbId: string/');
        $this->assertSame('string', $object->dbId);

        $object = new Customer();
        $object->uuid = 123;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer uuid: 123/');

        $object = new Customer();
        $object->uuid = 'notanid';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer uuid: notanid/');

        $object = new Customer();
        $object->sortIndex = -1;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer sortIndex: -1/');

        $object = new Customer();
        $object->sortIndex = 'string';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer sortIndex: string/');

        $object = new Customer();
        $object->creationDate = 9234;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/creationDate .9234. is not a DATETIME.6.$/');

        $object = new Customer();
        $object->creationDate = 'not a date';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/creationDate .not a date. is not a DATETIME.6.$/');

        $object = new Customer();
        $object->creationDate = '2019-11-16 10:41:38.123';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/creationDate .2019-11-16 10:41:38.123. must have a UTC timezone$/');

        $object = new Customer();
        $object->creationDate = '2019-11-16 10:41:38.123456Z';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Creation and modification date must be null if dbid is null/');

        $object = new Customer();
        $object->modificationDate = '2019-11-16 10:41:38.123';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/modificationDate .2019-11-16 10:41:38.123. must have a UTC timezone$/');

        $object = new Customer();
        $object->modificationDate = '2019-11-16 10:41:38.123456Z';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Creation and modification date must be null if dbid is null/');

        $object = new Customer();
        $object->modificationDate = 1;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/modificationDate .1. is not a DATETIME.6.$/');

        $object = new Customer();
        $object->modificationDate = 'hello';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/modificationDate .hello. is not a DATETIME.6.$/');
        $this->assertSame(null, $object->dbId);
        $this->assertSame(null, $object->creationDate);
        $this->assertSame('hello', $object->modificationDate);

        $object = new Customer();
        $object->deleted = 1;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/deleted .1. is not a DATETIME.6.$/');

        $object = new Customer();
        $object->name = 12345;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column name data type expected: varchar.100. NOT NULL but received: mediumint/');

        $object = new Customer();
        $object->name = new stdClass();
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not detect property name type: Could not detect type from object/');

        $object = new Customer();
        $object->age = 'string instead of int';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column age data type expected: smallint.6. NOT NULL but received: varchar.21./');

        $object = new Customer();
        $object->age = 1.12;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column age data type expected: smallint.6. NOT NULL but received: double/');

        $object = new Customer();
        $object->debt = 'notadouble';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column debt data type expected: double NOT NULL but received: varchar.10./');

        AssertUtils::throwsException(function() { $this->sut->save(new DataBaseManager()); }, '/Argument 1 passed to.*save.. must be an instance of.*DataBaseObject, instance of.*DataBaseManager given/');

        // Try to save database objects that contains invalid methods or properties
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongMethods()); }, '/Method is not allowed for DataBaseObject class org.*ObjectWithWrongMethods: methodThatCantBeHere/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongPropThatStartsWithUnderscore()); }, '/Properties starting with _ are forbidden, but found: _name/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongNullNonTypedProperty()); }, '/Could not detect property age type: Could not detect type from NULL/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongEmptyNonTypedArrayProperty()); }, '/Could not detect property emails type: Could not detect type from array/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongNonExistantTypedProperty()); }, '/Cannot define type for nonexistant cause it does not exist on class/');

        // Add an unexpected column to the customer table and make sure saving fails
        $this->assertTrue($this->db->tableAddColumn($objectTableName, 'unexpected', 'bigint'));
        AssertUtils::throwsException(function() { $this->sut->save(new Customer()); }, '/td_customer columns .dbid,uuid,sortindex,creationdate,modificationdate,deleted,name,commercialname,age,debt,unexpected. are different from its related object/');

        // All exceptions must have not created any database object
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
    }


    /**
     * testSaveNullAndNotNullValues
     *
     * @return void
     */
    public function testSaveNullAndNotNullValues(){

        $objectTableName = $this->sut->tablesPrefix.'customer';
        $typedObjectTableName = $this->sut->tablesPrefix.'customertyped';

        // Null values can't be detected on objects that have no specific types defined
        $object = new Customer();
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
     * testSave_datetime_values_are_as_expected
     *
     * @return void
     */
    public function testSave_datetime_values_are_as_expected(){

        $objectTableName = $this->sut->tablesPrefix.'customer';

        // Test that creation and modification dates are correct
        $object = new Customer();
        $dateBeforeCreation = (new DateTimeObject())->toString();
        $this->assertSame(1, $this->sut->save($object));
        $dateAfterCreation = (new DateTimeObject())->toString();

        $objectCreationDate = $this->db->tableGetColumnValues($objectTableName, 'creationDate')[0];
        $objectModificationDate = $this->db->tableGetColumnValues($objectTableName, 'modificationDate')[0];
        $this->assertTrue((new DateTimeObject($objectCreationDate))->isEqualTo(new DateTimeObject($object->creationDate)));
        $this->assertTrue((new DateTimeObject($objectModificationDate))->isEqualTo(new DateTimeObject($object->modificationDate)));

        $this->assertSame($objectCreationDate, $objectModificationDate);
        $this->assertContains(DateTimeObject::compare($dateBeforeCreation, $objectCreationDate), [0, 2], $dateBeforeCreation.' must be before '.$objectCreationDate);
        $this->assertContains(DateTimeObject::compare($dateAfterCreation, $objectCreationDate), [0, 1], $dateAfterCreation.' must be after '.$objectCreationDate);
        $this->assertContains(DateTimeObject::compare($dateBeforeCreation, $objectModificationDate), [0, 2], $dateBeforeCreation.' must be before '.$objectModificationDate);
        $this->assertContains(DateTimeObject::compare($dateAfterCreation, $objectModificationDate), [0, 1], $dateAfterCreation.' must be after '.$objectModificationDate);

        // Test that modification date has changed after some time has passed, but creation date remains the same
        sleep(1);
        $this->assertSame(1, $this->sut->save($object));
        $dateAfterModification = (new DateTimeObject())->toString();

        $objectCreationDate2 = $this->db->tableGetColumnValues($objectTableName, 'creationDate')[0];
        $objectModificationDate = $this->db->tableGetColumnValues($objectTableName, 'modificationDate')[0];
        $this->assertTrue((new DateTimeObject($objectCreationDate2))->isEqualTo(new DateTimeObject($object->creationDate)));
        $this->assertTrue((new DateTimeObject($objectModificationDate))->isEqualTo(new DateTimeObject($object->modificationDate)));

        $this->assertSame($objectCreationDate, $objectCreationDate2);
        $this->assertSame(2, DateTimeObject::compare($objectCreationDate2, $objectModificationDate), $objectCreationDate2.' must be before '.$objectModificationDate);
        $this->assertContains(DateTimeObject::compare($dateBeforeCreation, $objectCreationDate2), [0, 2], $dateBeforeCreation.' must be after '.$objectCreationDate2);
        $this->assertSame(1, DateTimeObject::compare($dateAfterModification, $objectCreationDate2), $dateAfterModification.' must be after '.$objectCreationDate2);
        $this->assertSame(2, DateTimeObject::compare($dateBeforeCreation, $objectModificationDate), $dateBeforeCreation.' must be before '.$objectModificationDate);
        $this->assertContains(DateTimeObject::compare($dateAfterModification, $objectModificationDate), [0, 1], $dateAfterModification.' must be after '.$objectModificationDate);

        // Make sure all object dates are always UTC
        $this->assertTrue((new DateTimeObject($object->creationDate))->isUTC());
        $this->assertTrue((new DateTimeObject($object->modificationDate))->isUTC());
        $this->assertNull($object->deleted);

        $objectTyped = new CustomerTyped();
        $objectTyped->birthDate = (new DateTimeObject())->toString('Y-M-D H:N:SOffset');
        $objectTyped->miliSecondsDate = (new DateTimeObject())->toString('Y-M-DTH:N:S.uZ');
        $objectTyped->microSecondsDate = (new DateTimeObject())->toString();
        $this->assertSame(1, $this->sut->save($objectTyped));

        $this->assertTrue((new DateTimeObject($objectTyped->creationDate))->isUTC());
        $this->assertTrue((new DateTimeObject($objectTyped->modificationDate))->isUTC());
        $this->assertNull($objectTyped->deleted);
        $this->assertTrue((new DateTimeObject($objectTyped->birthDate))->isUTC());
        $this->assertTrue((new DateTimeObject($objectTyped->miliSecondsDate))->isUTC());
        $this->assertTrue((new DateTimeObject($objectTyped->microSecondsDate))->isUTC());

        // Test that non UTC values throw exceptions
        $nonUtcDate = (new DateTimeObject())->setTimeZoneOffset('+05:00');

        $objectTyped->modificationDate = $nonUtcDate->toString('Y-M-DTH:N:SOffset');
        AssertUtils::throwsException(function() use ($objectTyped) { $this->sut->save($objectTyped); }, '/modificationDate .....-..-.....:..:..\+05:00. must have a UTC timezone$/');

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
    * testSave_simple_object_performs_no_more_than_2_db_queries
    *
    * @return void
    */
    public function testSave_simple_object_performs_no_more_than_2_db_queries(){

        $this->assertFalse($this->sut->getDataBaseManager()->isAnyTransactionActive());
        $this->assertSame(0, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $object = new Customer();
        $object->name = 'customer';
        $this->assertSame(1, $this->sut->save($object));

        $this->assertFalse($this->sut->getDataBaseManager()->isAnyTransactionActive());
        $this->assertSame(2, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $object = new Customer();
        $object->name = 'c2';
        $this->assertSame(2, $this->sut->save($object));
        $this->assertSame(4, count($this->sut->getDataBaseManager()->getQueryHistory()));
    }


    /**
     * testSave_and_update_simple_object_with_array_typed_properties
     *
     * @return void
     */
    public function testSave_and_update_simple_object_with_array_typed_properties(){

        $objectTableName = $this->sut->tablesPrefix.'customerwitharrayprops';
        $this->assertSame(0, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $object = new CustomerWithArrayProps();
        $object->name = 'this customer has array typed properties';
        $object->emails = ['email1', 'email2', 'email3'];
        $object->boolArray = [true, false];
        $object->intArray = [10, 20, 30, 40];
        $object->doubleArray = [10.0, 100.454, 0.254676];
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(14, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'uuid' => 'varchar(36)', 'sortindex' => 'bigint(20) unsigned',
            'creationdate' => 'datetime(6) NOT NULL', 'modificationdate' => 'datetime(6) NOT NULL', 'deleted' => 'datetime(6)', 'name' => 'varchar(40) NOT NULL',
            'age' => 'smallint(6) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'varchar(6) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));
        $this->assertSame(['1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'dbid'));
        $this->assertSame(['email1', 'email2', 'email3'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'tinyint(1) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_boolarray'));
        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_boolarray', 'dbid'));
        $this->assertSame(['1', '0'], $this->db->tableGetColumnValues($objectTableName.'_boolarray', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'smallint(6) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_intarray'));
        $this->assertSame(['1', '1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'dbid'));
        $this->assertSame(['10', '20', '30', '40'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_doublearray'));
        $this->assertSame(['1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_doublearray', 'dbid'));
        $this->assertSame(['10', '100.454', '0.254676'], $this->db->tableGetColumnValues($objectTableName.'_doublearray', 'value'));

        // Update the existing object with new array values
        // Warning: This next test may fail if the database is not able to store miliseconds precision on dates. This will lead to the same modification date
        // on the updated object, and therefore no changes will be detected on the object table so no rows will be updated and an error will happen.
        $object->emails = ['new1', 'new2'];
        $object->boolArray = [false, true];
        $object->intArray = [40, 30, 20, 10];
        $object->doubleArray = [9.999];
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'varchar(6) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));
        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'dbid'));
        $this->assertSame(['new1', 'new2'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'tinyint(1) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_boolarray'));
        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_boolarray', 'dbid'));
        $this->assertSame(['0', '1'], $this->db->tableGetColumnValues($objectTableName.'_boolarray', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'smallint(6) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_intarray'));
        $this->assertSame(['1', '1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'dbid'));
        $this->assertSame(['40', '30', '20', '10'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'value'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'double NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_doublearray'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_doublearray', 'dbid'));
        $this->assertSame(['9.999'], $this->db->tableGetColumnValues($objectTableName.'_doublearray', 'value'));

        $object->emails = [34563456, 1232323, 12];
        $object->boolArray = [];
        $object->intArray = [];
        $object->doubleArray = [];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customerwitharrayprops_emails column value data type expected: varchar.6. NOT NULL but received: int/');

        $object = new CustomerWithArrayProps();
        $this->assertSame(2, $this->sut->save($object));
        $object->emails = ['email1', 'email2', 'email3'];
        $this->assertSame(2, $this->sut->save($object));
        $object->boolArray = [true, false, false];
        $this->assertSame(2, $this->sut->save($object));
        $this->assertSame(2, $object->dbId);

        // Test wrong values

        $object->emails = ['this value is too long for the created table'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customerwitharrayprops_emails column value data type expected: varchar.6. NOT NULL but received: varchar.44./');

        $object->emails = ['ok', 'this value is too long for the created table'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customerwitharrayprops_emails column value data type expected: varchar.6. NOT NULL but received: varchar.44./');

        $object = new CustomerWithArrayProps();
        $object->intArray = ['string'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customerwitharrayprops_intarray column value data type expected: smallint.6. NOT NULL but received: varchar.6./');

        $object = new CustomerWithArrayProps();
        $object->intArray = [111, 452435234523452345];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customerwitharrayprops_intarray column value data type expected: smallint.6. NOT NULL but received: bigint/');

        $object = new CustomerWithArrayProps();
        $object->boolArray = [111];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customerwitharrayprops_boolarray column value data type expected: tinyint.1. NOT NULL but received: smallint/');

        $object = new CustomerWithArrayProps();
        $object->name = ['storing an array into a non array prop'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not get column data types: Table .*td_customerwitharrayprops_name. doesn.t exist/');

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


    /**
     * testSave_Strong_typed_Object
     *
     * @return void
     */
    public function testSave_Strong_typed_Object(){

        $objectTableName = $this->sut->tablesPrefix.'customertyped';

        // Test empty values
        // Not necessary

        // Test ok values
        $object = new CustomerTyped();
        $object->name = 'customer';
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(10, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'uuid' => 'varchar(36)', 'sortindex' => 'bigint(20) unsigned',
            'creationdate' => 'datetime(6) NOT NULL', 'modificationdate' => 'datetime(6) NOT NULL', 'deleted' => 'datetime(6)', 'name' => 'varchar(20) NOT NULL',
            'commercialname' => 'varchar(25)', 'birthdate' => 'datetime', 'milisecondsdate' => 'datetime(3)', 'microsecondsdate' => 'datetime(6)', 'age' => 'smallint(6)',
            'onedigitint' => 'smallint(6)', 'sixdigitint' => 'mediumint(9)', 'twelvedigitint' => 'bigint(20)', 'doublevalue' => 'double',
            'setup' => 'tinyint(1)'
            ], $this->db->tableGetColumnDataTypes($objectTableName));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'varchar(75)'], $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));
        $this->assertSame([], $this->db->tableGetColumnValues($objectTableName.'_emails', 'dbid'));
        $this->assertSame([], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));

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

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'varchar(75)'], $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));
        $this->assertSame(['mail1', 'mail2'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'tinyint(1) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_boolarray'));
        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_boolarray', 'value'));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'smallint(6)'], $this->db->tableGetColumnDataTypes($objectTableName.'_intarray'));
        $this->assertSame(['1', '2', '3', '4'], $this->db->tableGetColumnValues($objectTableName.'_intarray', 'value'));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'value' => 'double'], $this->db->tableGetColumnDataTypes($objectTableName.'_doublearray'));
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
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/doubleValue .string. does not match DOUBLE.1.$/');

        $object = new CustomerTyped();
        $object->setup = 'notabool';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/setup .notabool. does not match BOOL.1.$/');

        $object = new CustomerTyped();
        $object->emails = 12;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/emails.*does not match STRING.75./s');

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
        $object->intArray = ['string', 'string'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/intArray.*string.*does not match INT.3./s');

        $object = new CustomerTyped();
        $object->intArray = [1, 22, 333, 4444];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/intArray value size 4 exceeds 3$/');

        $object = new CustomerTyped();
        $object->doubleArray = ['string', 'string'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/doubleArray.*string.*does not match DOUBLE.1./s');

        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongStringTypeSize()); }, '/name is defined as STRING but size is invalid/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongDateTypeSize()); }, '/date DATETIME size must be 0, 3 or 6/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongArrayTypeSize()); }, '/array is defined as an array of STRING but size is invalid/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongNotAllTypesDefined()); }, '/notDefined has no defined type but typing is mandatory. Define a type or disable this restriction by setting _isTypingMandatory = false/');
    }


    /**
     * testSave_Object_With_Multi_Language_Properties
     *
     * @return void
     */
    public function testSave_Object_With_Multi_Language_Properties(){

        $objectTableName = $this->sut->tablesPrefix.'customerlocalized';

        // Test empty values
        AssertUtils::throwsException(function() { new CustomerLocalized(); }, '/Class is multi language and expects a list of locales/');
        AssertUtils::throwsException(function() { new CustomerLocalized(null); }, '/must be of the type array/');
        AssertUtils::throwsException(function() { new CustomerLocalized(''); }, '/must be of the type array/');
        AssertUtils::throwsException(function() { new CustomerLocalized([]); }, '/Class is multi language and expects a list of locales/');

        // Test ok values

        // Test saving the first empty instance to the database with only the empty locale defined
        $object = new CustomerLocalized(['']);
        $this->assertSame(1, $this->sut->save($object));

        $objectMainTableTypes = ['dbid' => 'bigint(20) unsigned NOT NULL', 'uuid' => 'varchar(36)', 'sortindex' => 'bigint(20) unsigned',
            'creationdate' => 'datetime(6) NOT NULL', 'modificationdate' => 'datetime(6) NOT NULL', 'deleted' => 'datetime(6)',
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
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20)', 'en_US' => 'varchar(20)'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'dbid'));
        $this->assertSame([null, null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', '_'));
        $this->assertSame([null, null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_US'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20) NOT NULL', 'en_US' => 'varchar(20) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalizednotnull'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', 'dbid'));
        $this->assertSame(['', ''], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', '_'));
        $this->assertSame(['', ''], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', 'en_US'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'datetime', 'en_US' => 'datetime'], $this->db->tableGetColumnDataTypes($objectTableName.'_birthdatelocalized'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', 'dbid'));
        $this->assertSame([null, null], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', '_'));
        $this->assertSame([null, null], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', 'en_US'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'smallint(6)', 'en_US' => 'smallint(6)'], $this->db->tableGetColumnDataTypes($objectTableName.'_agelocalized'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'dbid'));
        $this->assertSame(['0', null], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', '_'));
        $this->assertSame([null, '0'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'en_US'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'tinyint(1)', 'en_US' => 'tinyint(1)'], $this->db->tableGetColumnDataTypes($objectTableName.'_setuplocalized'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'dbid'));
        $this->assertSame(['0', null], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', '_'));
        $this->assertSame([null, '0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'en_US'));

        // Test saving a third empty instance to the database with a list of the 2 previously saved locales plus a new one
        $object = new CustomerLocalized(['en_US', '', 'es_ES']);
        $this->assertSame(3, $this->sut->save($object));

        $this->assertSame($objectMainTableTypes, $this->db->tableGetColumnDataTypes($objectTableName));
        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20)', 'en_US' => 'varchar(20)', 'es_ES' => 'varchar(20)'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalized'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'dbid'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', '_'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'en_US'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_namelocalized', 'es_ES'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'varchar(20) NOT NULL', 'en_US' => 'varchar(20) NOT NULL', 'es_ES' => 'varchar(20) NOT NULL'], $this->db->tableGetColumnDataTypes($objectTableName.'_namelocalizednotnull'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', 'dbid'));
        $this->assertSame(['', '', ''], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', '_'));
        $this->assertSame(['', '', ''], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', 'en_US'));
        $this->assertSame(['', '', ''], $this->db->tableGetColumnValues($objectTableName.'_namelocalizednotnull', 'es_ES'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'datetime', 'en_US' => 'datetime', 'es_ES' => 'datetime'], $this->db->tableGetColumnDataTypes($objectTableName.'_birthdatelocalized'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', 'dbid'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', '_'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', 'en_US'));
        $this->assertSame([null, null, null], $this->db->tableGetColumnValues($objectTableName.'_birthdatelocalized', 'es_ES'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'smallint(6)', 'en_US' => 'smallint(6)', 'es_ES' => 'smallint(6)'], $this->db->tableGetColumnDataTypes($objectTableName.'_agelocalized'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'dbid'));
        $this->assertSame(['0', null, '0'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', '_'));
        $this->assertSame([null, '0', '0'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'en_US'));
        $this->assertSame([null, null, '0'], $this->db->tableGetColumnValues($objectTableName.'_agelocalized', 'es_ES'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', '_' => 'tinyint(1)', 'en_US' => 'tinyint(1)', 'es_ES' => 'tinyint(1)'], $this->db->tableGetColumnDataTypes($objectTableName.'_setuplocalized'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'dbid'));
        $this->assertSame(['0', null, '0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', '_'));
        $this->assertSame([null, '0', '0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'en_US'));
        $this->assertSame([null, null, '0'], $this->db->tableGetColumnValues($objectTableName.'_setuplocalized', 'es_ES'));

        // TODO - implement MULTILANGUAGE_CASCADE to tag properties that must get next locale value when the default class or empty one is found
        // TODO - Test saving db object instances with real data
        // TODO - Create a new test case : Test what should happen when saving a db object with a string property, then modify that property to be an array property and save it again
        // TODO - Create a new test case : Test what should happen when saving a db object with a string property, then modify that property to be a multi language property and save it again
        // TODO - test saving with single locale and modifying the values and saving again
        // TODO - test saving with 2 locales and modifying the values and saving again
        // TODO - perform several tests to make sure that multiple locales information is correctly stored in memory of each db object instance at the  $_locales private prop
        //      - The first and active locale for the db object instances must be directly set to the object properties, and all the other loaded data stored on the $_locales prop
        // TODO - It's been finally decided to not destroy locale columns from multi locale props tables, cause they are not annoying even if not used. Test that this happens as expected
        // TODO - test saving several objects, several instances of the same object, different locales, modifying the same object on already saved locale values, adding different locales, etc..
        // TODO - what happens when we create an object with an A non localized prop, save it to db, and then alter the class to make the A prop a multilanguage one, and then save again?

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
     * test_Multi_Language_Object_invalid_values_on_multilan_properties
     *
     * @return void
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
    }


    /**
     * test_Multi_Language_Object_Change_Locales_Order
     *
     * @return void
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
        $objectTableName = $this->sut->tablesPrefix.'customerlocalized';

        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue($this->sut->getDataBaseManager()->tableExists($objectTableName));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_age'));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_name'));
        $this->assertFalse($this->sut->getDataBaseManager()->tableExists($objectTableName.'_setup'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'en_US' => 'smallint(6)', 'es_ES' => 'smallint(6)', 'fr_FR' => 'smallint(6)', 'en_GB' => 'smallint(6)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_ageLocalized'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'en_US' => 'varchar(20)', 'es_ES' => 'varchar(20)', 'fr_FR' => 'varchar(20)', 'en_GB' => 'varchar(20)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_nameLocalized'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'en_US' => 'tinyint(1)', 'es_ES' => 'tinyint(1)', 'fr_FR' => 'tinyint(1)', 'en_GB' => 'tinyint(1)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_setupLocalized'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_ageLocalized', 'dbid'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_ageLocalized', 'en_US'));
        $this->assertSame(['2'], $this->db->tableGetColumnValues($objectTableName.'_ageLocalized', 'es_ES'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_ageLocalized', 'fr_FR'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($objectTableName.'_ageLocalized', 'en_GB'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_nameLocalized', 'dbid'));
        $this->assertSame(['james'], $this->db->tableGetColumnValues($objectTableName.'_nameLocalized', 'en_US'));
        $this->assertSame(['jaime'], $this->db->tableGetColumnValues($objectTableName.'_nameLocalized', 'es_ES'));
        $this->assertSame([null], $this->db->tableGetColumnValues($objectTableName.'_nameLocalized', 'fr_FR'));
        $this->assertSame(['en_GB'], $this->db->tableGetColumnValues($objectTableName.'_nameLocalized', 'en_GB'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_setupLocalized', 'dbid'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_setupLocalized', 'en_US'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_setupLocalized', 'es_ES'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_setupLocalized', 'fr_FR'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_setupLocalized', 'en_GB'));

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

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'en_US' => 'smallint(6)', 'es_ES' => 'smallint(6)', 'fr_FR' => 'smallint(6)', 'en_GB' => 'smallint(6)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_ageLocalized'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'en_US' => 'varchar(20)', 'es_ES' => 'varchar(20)', 'fr_FR' => 'varchar(20)', 'en_GB' => 'varchar(20)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_nameLocalized'));

        $this->assertSame(['dbid' => 'bigint(20) unsigned NOT NULL', 'en_US' => 'tinyint(1)', 'es_ES' => 'tinyint(1)', 'fr_FR' => 'tinyint(1)', 'en_GB' => 'tinyint(1)'],
            $this->db->tableGetColumnDataTypes($objectTableName.'_setupLocalized'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_ageLocalized', 'dbid'));
        $this->assertSame(['6'], $this->db->tableGetColumnValues($objectTableName.'_ageLocalized', 'en_US'));
        $this->assertSame(['7'], $this->db->tableGetColumnValues($objectTableName.'_ageLocalized', 'es_ES'));
        $this->assertSame(['8'], $this->db->tableGetColumnValues($objectTableName.'_ageLocalized', 'fr_FR'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($objectTableName.'_ageLocalized', 'en_GB'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_nameLocalized', 'dbid'));
        $this->assertSame(['en_USedited'], $this->db->tableGetColumnValues($objectTableName.'_nameLocalized', 'en_US'));
        $this->assertSame(['jaimeditado'], $this->db->tableGetColumnValues($objectTableName.'_nameLocalized', 'es_ES'));
        $this->assertSame(['frenchname'], $this->db->tableGetColumnValues($objectTableName.'_nameLocalized', 'fr_FR'));
        $this->assertSame(['en_GB'], $this->db->tableGetColumnValues($objectTableName.'_nameLocalized', 'en_GB'));

        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_setupLocalized', 'dbid'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_setupLocalized', 'en_US'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_setupLocalized', 'es_ES'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_setupLocalized', 'fr_FR'));
        $this->assertSame(['0'], $this->db->tableGetColumnValues($objectTableName.'_setupLocalized', 'en_GB'));
    }


    /**
     * test_Multi_Language_Object_setLocales
     *
     * @return void
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
     * test_Multi_Language_Object_isMultiLanguage
     *
     * @return void
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
     * test_Multi_Language_Object_getLocales
     *
     * @return void
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
     * testGetTableNameFromObject
     *
     * @return void
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
    * testConvertObjectToTableData
    *
    * @return void
    */
    public function testConvertObjectToTableData(){

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
    * testGetSQLTypeFromObjectProperty
    *
    * @return void
    */
    public function testGetSQLTypeFromObjectProperty(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->getSQLTypeFromObjectProperty(null, null); }, '/Argument 1.*must be an instance of.*DataBaseObject, null given/');
        AssertUtils::throwsException(function() { $this->sut->getSQLTypeFromObjectProperty(new Customer(), null); }, '/Undefined/');
        AssertUtils::throwsException(function() { $this->sut->getSQLTypeFromObjectProperty(new Customer(), ''); }, '/Undefined/');

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
        AssertUtils::throwsException(function() use ($object) { $this->sut->getSQLTypeFromObjectProperty($object, 'nonexistantproperty'); }, '/Undefined property: nonexistantproperty/');
        AssertUtils::throwsException(function() use ($object) { $this->sut->getSQLTypeFromObjectProperty($object, ''); }, '/Undefined property:/');
        AssertUtils::throwsException(function() { $this->sut->getSQLTypeFromObjectProperty(new stdClass(), ''); }, '/Argument 1 passed to .*getSQLTypeFromObjectProperty.*must be an instance of.*DataBaseObject.*stdClass given/');
    }
}

?>