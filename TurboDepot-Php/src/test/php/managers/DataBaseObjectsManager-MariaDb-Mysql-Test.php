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
use org\turbocommons\src\main\php\utils\StringUtils;
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

        $objectTableName = $this->sut->tablesPrefix.StringUtils::formatCase('Customer', StringUtils::FORMAT_LOWER_SNAKE_CASE);
        $this->assertFalse($this->db->tableExists($objectTableName));

        $object = new Customer();
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue($this->db->tableExists($objectTableName));

        $this->sut->tablesPrefix = 'new_';

        $object = new Customer();
        $this->assertSame(1, $this->sut->save($object));
        $this->assertTrue($this->db->tableExists($objectTableName));
        $this->assertTrue($this->db->tableExists($this->sut->tablesPrefix.StringUtils::formatCase('Customer', StringUtils::FORMAT_LOWER_SNAKE_CASE)));
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

        $objectTableName = $this->sut->tablesPrefix.StringUtils::formatCase('Customer', StringUtils::FORMAT_LOWER_SNAKE_CASE);

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
        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'uuid' => 'varchar(36)', 'sort_index' => 'bigint(20) unsigned',
            'creation_date' => 'datetime(3)', 'modification_date' => 'datetime(3)', 'deleted' => 'datetime(3)', 'name' => 'varchar(1)',
            'commercial_name' => 'varchar(1)', 'age' => 'smallint(6)', 'debt' => 'double'], $this->db->tableGetColumnDataTypes($objectTableName));

        // test that columns are in the correct order
        $this->assertSame(['db_id', 'uuid', 'sort_index', 'creation_date', 'modification_date', 'deleted', 'name', 'commercial_name', 'age', 'debt'],
            $this->db->tableGetColumnNames($objectTableName));

        // Test that datetime values are stored with miliseconds information
        $this->assertRegExp('/[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]\.[0-9][0-9][0-9]/',
            $this->db->tableGetColumnValues($objectTableName, 'creation_date')[0]);

        $this->assertRegExp('/[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]\.[0-9][0-9][0-9]/',
            $this->db->tableGetColumnValues($objectTableName, 'modification_date')[0]);

        $this->assertSame($this->db->tableGetColumnValues($objectTableName, 'creation_date')[0], $this->db->tableGetColumnValues($objectTableName, 'modification_date')[0]);

        $object = new Customer();
        $this->assertSame(2, $this->sut->save($object));
        $this->assertSame(2, $object->dbId);
        $this->assertSame(2, $this->db->tableCountRows($objectTableName));

        $object = new Customer();
        $this->assertSame(3, $this->sut->save($object));
        $this->assertSame(3, $object->dbId);
        $this->assertSame(3, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'uuid' => 'varchar(36)', 'sort_index' => 'bigint(20) unsigned',
            'creation_date' => 'datetime(3)', 'modification_date' => 'datetime(3)', 'deleted' => 'datetime(3)', 'name' => 'varchar(1)',
            'commercial_name' => 'varchar(1)', 'age' => 'smallint(6)', 'debt' => 'double'], $this->db->tableGetColumnDataTypes($objectTableName));

        // Test ok values - update instances

        $object = new Customer();
        $object->age = 14123412341;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column age data type expected: smallint.6. but received: bigint/');

        $object = new Customer();
        $object->age = 14123412341345345345345345345;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column age data type expected: smallint.6. but received: double/');

        $object = new Customer();
        $object->name = 'customer';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column name data type expected: varchar.1. but received: varchar.8./');

        $this->sut->isColumnResizedWhenValueisBigger = true;
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame(4, $object->dbId);
        $this->assertSame('customer', $object->name);
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'uuid' => 'varchar(36)', 'sort_index' => 'bigint(20) unsigned',
            'creation_date' => 'datetime(3)', 'modification_date' => 'datetime(3)', 'deleted' => 'datetime(3)', 'name' => 'varchar(8)',
            'commercial_name' => 'varchar(1)', 'age' => 'smallint(6)', 'debt' => 'double'], $this->db->tableGetColumnDataTypes($objectTableName));

        $object->name = 'customer updated';
        $this->assertSame(4, $object->dbId);
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame('customer updated', $object->name);
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'uuid' => 'varchar(36)', 'sort_index' => 'bigint(20) unsigned',
            'creation_date' => 'datetime(3)', 'modification_date' => 'datetime(3)', 'deleted' => 'datetime(3)', 'name' => 'varchar(16)',
            'commercial_name' => 'varchar(1)', 'age' => 'smallint(6)', 'debt' => 'double'], $this->db->tableGetColumnDataTypes($objectTableName));

        $object->name = 'customer updated with a much longer text that should resize the name column to a bigger varchar size';
        $this->assertSame(4, $object->dbId);
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame('customer updated with a much longer text that should resize the name column to a bigger varchar size', $object->name);
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'uuid' => 'varchar(36)', 'sort_index' => 'bigint(20) unsigned',
            'creation_date' => 'datetime(3)', 'modification_date' => 'datetime(3)', 'deleted' => 'datetime(3)', 'name' => 'varchar(100)',
            'commercial_name' => 'varchar(1)', 'age' => 'smallint(6)', 'debt' => 'double'], $this->db->tableGetColumnDataTypes($objectTableName));

        $object->debt = 10;
        $this->assertSame(4, $object->dbId);
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));

        // test that columns are in the correct order
        $this->assertSame(['db_id', 'uuid', 'sort_index', 'creation_date', 'modification_date', 'deleted', 'name', 'commercial_name', 'age', 'debt'],
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
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not update row on table td_customer for db_id=\'5000000\'/');

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
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer creationDate: 9234/');

        $object = new Customer();
        $object->creationDate = 'not a date';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer creationDate: not a date/');

        $object = new Customer();
        $object->creationDate = '2019-11-16 10:41:38.123';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Creation and modification date must be null if dbid is null/');

        $object = new Customer();
        $object->modificationDate = '2019-11-16 10:41:38.123';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Creation and modification date must be null if dbid is null/');

        $object = new Customer();
        $object->modificationDate = 1;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer modificationDate: 1/');

        $object = new Customer();
        $object->modificationDate = 'hello';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer modificationDate: hello/');
        $this->assertSame(null, $object->dbId);
        $this->assertSame(null, $object->creationDate);
        $this->assertSame('hello', $object->modificationDate);

        $object = new Customer();
        $object->deleted = 1;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Invalid Customer deleted: 1/');

        $object = new Customer();
        $object->name = 12345;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column name data type expected: varchar.100. but received: mediumint/');

        $object = new Customer();
        $object->name = new stdClass();
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not detect type from property name: Could not detect type from object/');

        $object = new Customer();
        $object->age = 'string instead of int';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column age data type expected: smallint.6. but received: varchar.21./');

        $object = new Customer();
        $object->age = 1.12;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column age data type expected: smallint.6. but received: double/');

        $object = new Customer();
        $object->debt = 'notadouble';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer column debt data type expected: double but received: varchar.10./');

        AssertUtils::throwsException(function() { $this->sut->save(new DataBaseManager()); }, '/Argument 1 passed to.*save.. must be an instance of.*DataBaseObject, instance of.*DataBaseManager given/');

        // Try to save database objects that contains invalid methods or properties
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongMethods()); }, '/Only __construct method is allowed for DataBaseObjects but found: methodThatCantBeHere/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongPropThatStartsWithUnderscore()); }, '/Properties starting with _ are forbidden, but found: _name/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongNullNonTypedProperty()); }, '/Could not detect type from property age: Could not detect type from NULL/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongEmptyNonTypedArrayProperty()); }, '/Could not detect type from property emails: Could not detect type from array/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongNonExistantTypedProperty()); }, '/Cannot define type for nonexistant cause it does not exist on class/');

        // Add an unexpected column to the customer table and make sure saving fails
        $this->assertTrue($this->db->tableAddColumn($objectTableName, 'unexpected', 'bigint'));
        AssertUtils::throwsException(function() { $this->sut->save(new Customer()); }, '/td_customer columns .db_id,uuid,sort_index,creation_date,modification_date,deleted,name,commercial_name,age,debt,unexpected. are different from its related object/');

        // All exceptions must have not created any database object
        $this->assertSame(4, $this->db->tableCountRows($objectTableName));
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

        $objectTableName = $this->sut->tablesPrefix.StringUtils::formatCase('CustomerWithArrayProps', StringUtils::FORMAT_LOWER_SNAKE_CASE);
        $this->assertSame(0, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $object = new CustomerWithArrayProps();
        $object->name = 'this customer has array typed properties';
        $object->emails = ['email1', 'email2', 'email3'];
        $object->boolArray = [true, false];
        $object->intArray = [10, 20, 30, 40];
        $object->doubleArray = [10.0, 100.454, 0.254676];
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(14, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'uuid' => 'varchar(36)', 'sort_index' => 'bigint(20) unsigned',
            'creation_date' => 'datetime(3)', 'modification_date' => 'datetime(3)', 'deleted' => 'datetime(3)', 'name' => 'varchar(40)',
            'age' => 'smallint(6)'], $this->db->tableGetColumnDataTypes($objectTableName));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'varchar(6)'], $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));
        $this->assertSame(['1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'db_id'));
        $this->assertSame(['email1', 'email2', 'email3'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'tinyint(1)'], $this->db->tableGetColumnDataTypes($objectTableName.'_bool_array'));
        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_bool_array', 'db_id'));
        $this->assertSame(['1', '0'], $this->db->tableGetColumnValues($objectTableName.'_bool_array', 'value'));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'smallint(6)'], $this->db->tableGetColumnDataTypes($objectTableName.'_int_array'));
        $this->assertSame(['1', '1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_int_array', 'db_id'));
        $this->assertSame(['10', '20', '30', '40'], $this->db->tableGetColumnValues($objectTableName.'_int_array', 'value'));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'double'], $this->db->tableGetColumnDataTypes($objectTableName.'_double_array'));
        $this->assertSame(['1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_double_array', 'db_id'));
        $this->assertSame(['10', '100.454', '0.254676'], $this->db->tableGetColumnValues($objectTableName.'_double_array', 'value'));

        // Update the existing object with new array values
        // Warning: This next test may fail if the database is not able to store miliseconds precision on dates. This will lead to the same modification date
        // on the updated object, and therefore no changes will be detected on the object table so no rows will be updated and an error will happen.
        $object->emails = ['new1', 'new2'];
        $object->boolArray = [false, true];
        $object->intArray = [40, 30, 20, 10];
        $object->doubleArray = [9.999];
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'varchar(6)'], $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));
        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'db_id'));
        $this->assertSame(['new1', 'new2'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'tinyint(1)'], $this->db->tableGetColumnDataTypes($objectTableName.'_bool_array'));
        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_bool_array', 'db_id'));
        $this->assertSame(['0', '1'], $this->db->tableGetColumnValues($objectTableName.'_bool_array', 'value'));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'smallint(6)'], $this->db->tableGetColumnDataTypes($objectTableName.'_int_array'));
        $this->assertSame(['1', '1', '1', '1'], $this->db->tableGetColumnValues($objectTableName.'_int_array', 'db_id'));
        $this->assertSame(['40', '30', '20', '10'], $this->db->tableGetColumnValues($objectTableName.'_int_array', 'value'));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'double'], $this->db->tableGetColumnDataTypes($objectTableName.'_double_array'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($objectTableName.'_double_array', 'db_id'));
        $this->assertSame(['9.999'], $this->db->tableGetColumnValues($objectTableName.'_double_array', 'value'));

        $object->emails = [34563456, 1232323, 12];
        $object->boolArray = [];
        $object->intArray = [];
        $object->doubleArray = [];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_with_array_props_emails column value data type expected: varchar.6. but received: int/');

        $object = new CustomerWithArrayProps();
        $this->assertSame(2, $this->sut->save($object));
        $object->emails = ['email1', 'email2', 'email3'];
        $this->assertSame(2, $this->sut->save($object));
        $object->boolArray = [true, false, false];
        $this->assertSame(2, $this->sut->save($object));
        $this->assertSame(2, $object->dbId);

        // Test wrong values

        $object->emails = ['this value is too long for the created table'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_with_array_props_emails column value data type expected: varchar.6. but received: varchar.44./');

        $object->emails = ['ok', 'this value is too long for the created table'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_with_array_props_emails column value data type expected: varchar.6. but received: varchar.44./');

        $object = new CustomerWithArrayProps();
        $object->intArray = ['string'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_with_array_props_int_array column value data type expected: smallint.6. but received: varchar.6./');

        $object = new CustomerWithArrayProps();
        $object->intArray = [111, 452435234523452345];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_with_array_props_int_array column value data type expected: smallint.6. but received: bigint/');

        $object = new CustomerWithArrayProps();
        $object->boolArray = [111];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_with_array_props_bool_array column value data type expected: tinyint.1. but received: smallint/');

        $object = new CustomerWithArrayProps();
        $object->name = ['storing an array into a non array prop'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Table does not exist: td_customer_with_array_props_name/');

        $object = new CustomerWithArrayProps();
        $object->name = 'this customer has array typed properties';
        $object->emails = ['email1', 'email2', 'email3'];
        $object->boolArray = [true, 123];
        $object->intArray = [10, 20, 30, 40];
        $object->doubleArray = [10.0, 100.454, 0.254676];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not detect type from property boolArray: All array elements must be the same type/');

        $object = new CustomerWithArrayProps();
        $object->name = '';
        $object->emails = ['email1', 1232323, 'email3'];
        $object->boolArray = [true];
        $object->intArray = [10];
        $object->doubleArray = [0.254676];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not detect type from property emails: All array elements must be the same type/');
    }


    /**
     * testSave_Strong_typed_Object
     *
     * @return void
     */
    public function testSave_Strong_typed_Object(){

        $objectTableName = $this->sut->tablesPrefix.StringUtils::formatCase('CustomerTyped', StringUtils::FORMAT_LOWER_SNAKE_CASE);

        // Test empty values
        // Not necessary

        // Test ok values
        $object = new CustomerTyped();
        $object->name = 'customer';
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(10, count($this->sut->getDataBaseManager()->getQueryHistory()));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'uuid' => 'varchar(36)', 'sort_index' => 'bigint(20) unsigned',
            'creation_date' => 'datetime(3)', 'modification_date' => 'datetime(3)', 'deleted' => 'datetime(3)', 'name' => 'varchar(20)',
            'commercial_name' => 'varchar(25)', 'birth_date' => 'datetime', 'mili_seconds_date' => 'datetime(3)', 'age' => 'smallint(6)',
            'one_digit_int' => 'smallint(6)', 'six_digit_int' => 'mediumint(9)', 'twelve_digit_int' => 'bigint(20)', 'double_value' => 'double',
            'setup' => 'tinyint(1)'
            ], $this->db->tableGetColumnDataTypes($objectTableName));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'varchar(75)'], $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));
        $this->assertSame([], $this->db->tableGetColumnValues($objectTableName.'_emails', 'db_id'));
        $this->assertSame([], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));

        // Update the object by modifying some properties values
        $object->birthDate = '2019-12-01 12:00:01';
        $this->assertSame(1, $this->sut->save($object));

        $object->miliSecondsDate = '2019-12-01 12:00:01.123';
        $this->assertSame(1, $this->sut->save($object));

        $object->emails = ['mail1', 'mail2'];
        $this->assertSame(1, $this->sut->save($object));

        $object->boolArray = [true, true];
        $this->assertSame(1, $this->sut->save($object));

        $object->intArray = [1, 2, 3, 4];
        $this->assertSame(1, $this->sut->save($object));

        $object->doubleArray = [1, 2, 3, 4];
        $this->assertSame(1, $this->sut->save($object));

        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'varchar(75)'], $this->db->tableGetColumnDataTypes($objectTableName.'_emails'));
        $this->assertSame(['mail1', 'mail2'], $this->db->tableGetColumnValues($objectTableName.'_emails', 'value'));
        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'tinyint(1)'], $this->db->tableGetColumnDataTypes($objectTableName.'_bool_array'));
        $this->assertSame(['1', '1'], $this->db->tableGetColumnValues($objectTableName.'_bool_array', 'value'));
        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'smallint(6)'], $this->db->tableGetColumnDataTypes($objectTableName.'_int_array'));
        $this->assertSame(['1', '2', '3', '4'], $this->db->tableGetColumnValues($objectTableName.'_int_array', 'value'));
        $this->assertSame(['db_id' => 'bigint(20) unsigned', 'value' => 'double'], $this->db->tableGetColumnDataTypes($objectTableName.'_double_array'));
        $this->assertSame(['1', '2', '3', '4'], $this->db->tableGetColumnValues($objectTableName.'_double_array', 'value'));

        $object = new ObjectWithTypingDisabled();
        $this->assertSame(1, $this->sut->save($object));

        // Test wrong values
        // Test exceptions
        $object = new CustomerTyped();
        $object->name = '123456789012345678901';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/name value size 21 exceeds 20/');

        $object = new CustomerTyped();
        $object->name = 123123;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property name .123123. does not match required type: STRING/');

        $object = new CustomerTyped();
        $object->commercialName = '12345678901234567890123456';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/commercialName value size 26 exceeds 25/');

        $object = new CustomerTyped();
        $object->birthDate = 12345;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property birthDate .12345. does not match required type: DATETIME/');

        $object = new CustomerTyped();
        $object->birthDate = 'notadatestring';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_typed column birth_date data type expected: datetime but received: varchar.14./');

        $object = new CustomerTyped();
        $object->birthDate = '2019-10-12';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_typed column birth_date data type expected: datetime but received: varchar.10./');

        $object = new CustomerTyped();
        $object->birthDate = '2019-10-12 23:10:x';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_typed column birth_date data type expected: datetime but received: varchar.18./');

        $object = new CustomerTyped();
        $object->birthDate = '2019-10-12 23:10:667';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property birthDate value size 20 exceeds 19/');

        $object = new CustomerTyped();
        $object->miliSecondsDate = 12345;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property miliSecondsDate .12345. does not match required type: DATETIME/');

        $object = new CustomerTyped();
        $object->miliSecondsDate = 'notadatestring';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_typed column mili_seconds_date data type expected: datetime.3. but received: varchar.14./');

        $object = new CustomerTyped();
        $object->miliSecondsDate = '2019-10-12 23:10:26';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_typed column mili_seconds_date data type expected: datetime.3. but received: varchar.19./');

        $object = new CustomerTyped();
        $object->miliSecondsDate = '2019-10-12 23:10:26.00';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/td_customer_typed column mili_seconds_date data type expected: datetime.3. but received: varchar.22./');

        $object = new CustomerTyped();
        $object->miliSecondsDate = '2019-10-12 23:10:26.0000';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property miliSecondsDate value size 24 exceeds 23/');

        $object = new CustomerTyped();
        $object->age = 'stringinsteadofint';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property age .stringinsteadofint. does not match required type: INT/');

        $object = new CustomerTyped();
        $object->age = 10.2;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property age .10.2. does not match required type: INT/');

        $object = new CustomerTyped();
        $object->age = 123;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property age value size 3 exceeds 2/');

        $object = new CustomerTyped();
        $object->oneDigitInt = 12;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property oneDigitInt value size 2 exceeds 1/');

        $object = new CustomerTyped();
        $object->sixDigitInt = 1234567;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property sixDigitInt value size 7 exceeds 6/');

        $object = new CustomerTyped();
        $object->twelveDigitInt = 1234567890123;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property twelveDigitInt value size 13 exceeds 12/');

        $object = new CustomerTyped();
        $object->doubleValue = 'string';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property doubleValue .string. does not match required type: DOUBLE/');

        $object = new CustomerTyped();
        $object->setup = 'notabool';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property setup .notabool. does not match required type: BOOL/');

        $object = new CustomerTyped();
        $object->emails = [12, 123];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property emails.*does not match required type: STRING/s');

        $object = new CustomerTyped();
        $object->emails = ['a', 'aaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property emails value size 76 exceeds 75/');

        $object = new CustomerTyped();
        $object->boolArray = ['string', 'string'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property boolArray.*string.*does not match required type.*BOOL/s');

        $object = new CustomerTyped();
        $object->intArray = ['string', 'string'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property intArray.*string.*does not match required type.*INT/s');

        $object = new CustomerTyped();
        $object->intArray = [1, 22, 333, 4444];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property intArray value size 4 exceeds 3/');

        $object = new CustomerTyped();
        $object->doubleArray = ['string', 'string'];
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Property doubleArray.*string.*does not match required type.*DOUBLE/s');

        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongStringTypeSize()); }, '/name is defined as STRING but size is invalid/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongDateTypeSize()); }, '/date DATETIME size must be 19 or 23/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongArrayTypeSize()); }, '/array is defined as an array of STRING but size is invalid/');
        AssertUtils::throwsException(function() { $this->sut->save(new ObjectWithWrongNotAllTypesDefined()); }, '/notDefined has no defined type but typing is mandatory. Define a type or disable this restriction by setting _isTypingMandatory = false/');
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
        $this->assertSame('varchar(1)', $this->sut->getSQLTypeFromObjectProperty($object, 'name'));
        $this->assertSame('varchar(1)', $this->sut->getSQLTypeFromObjectProperty($object, 'commercialName'));
        $this->assertSame('smallint', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));

        $object->name = 'customer name';
        $object->commercialName = 'commercial name';
        $object->age = 12456;
        $this->assertSame('varchar(13)', $this->sut->getSQLTypeFromObjectProperty($object, 'name'));
        $this->assertSame('varchar(15)', $this->sut->getSQLTypeFromObjectProperty($object, 'commercialName'));
        $this->assertSame('mediumint', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));
        $object->age = 1234;
        $this->assertSame('smallint', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));
        $object->age = 1122212121;
        $this->assertSame('bigint', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));

        $object = new CustomerTyped();
        $this->assertSame('varchar(20)', $this->sut->getSQLTypeFromObjectProperty($object, 'name'));
        $this->assertSame('varchar(25)', $this->sut->getSQLTypeFromObjectProperty($object, 'commercialName'));
        $this->assertSame('smallint', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));
        $this->assertSame('smallint', $this->sut->getSQLTypeFromObjectProperty($object, 'oneDigitInt'));
        $this->assertSame('mediumint', $this->sut->getSQLTypeFromObjectProperty($object, 'sixDigitInt'));
        $this->assertSame('bigint', $this->sut->getSQLTypeFromObjectProperty($object, 'twelveDigitInt'));
        $this->assertSame('double', $this->sut->getSQLTypeFromObjectProperty($object, 'doubleValue'));
        $this->assertSame('tinyint(1)', $this->sut->getSQLTypeFromObjectProperty($object, 'setup'));
        $this->assertSame('varchar(75)', $this->sut->getSQLTypeFromObjectProperty($object, 'emails'));
        $this->assertSame('tinyint(1)', $this->sut->getSQLTypeFromObjectProperty($object, 'boolArray'));
        $this->assertSame('smallint', $this->sut->getSQLTypeFromObjectProperty($object, 'intArray'));
        $this->assertSame('double', $this->sut->getSQLTypeFromObjectProperty($object, 'doubleArray'));

        // Test wrong values
        $object = new CustomerTyped();
        $object->name = 1231231;
        $object->commercialName = new stdClass();
        $object->age = 'stringinsteadofint';
        $this->assertSame('varchar(20)', $this->sut->getSQLTypeFromObjectProperty($object, 'name'));
        $this->assertSame('smallint', $this->sut->getSQLTypeFromObjectProperty($object, 'age'));
        $this->assertSame('varchar(25)', $this->sut->getSQLTypeFromObjectProperty($object, 'commercialName'));

        // Test exceptions
        AssertUtils::throwsException(function() use ($object) { $this->sut->getSQLTypeFromObjectProperty($object, 'nonexistantproperty'); }, '/Undefined property: nonexistantproperty/');
        AssertUtils::throwsException(function() use ($object) { $this->sut->getSQLTypeFromObjectProperty($object, ''); }, '/Undefined property:/');
        AssertUtils::throwsException(function() { $this->sut->getSQLTypeFromObjectProperty(new stdClass(), ''); }, '/Argument 1 passed to .*getSQLTypeFromObjectProperty.*must be an instance of.*DataBaseObject.*stdClass given/');
    }
}

?>