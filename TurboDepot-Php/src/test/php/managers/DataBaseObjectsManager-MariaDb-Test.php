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

use stdClass;
use PHPUnit\Framework\TestCase;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;
use org\turbodepot\src\main\php\managers\DataBaseManager;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\Customer;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbodepot\src\test\resources\managers\dataBaseObjectsManagerTest\WrongObjectWithMethods;


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
        AssertUtils::throwsException(function() use ($objectTableName) { $this->db->countTableRows($objectTableName); }, '/Could not count table rows: Table .*tdp_customer.* doesn\'t exist/');
        $this->assertRegExp('/Table .*customer\' doesn\'t exist/', $this->db->getLastError());

        $object = new Customer();
        $this->assertSame(1, $this->sut->save($object));
        $this->assertSame(1, $object->dbId);
        $this->assertSame(1, $this->db->countTableRows($objectTableName));

        $object = new Customer();
        $this->assertSame(2, $this->sut->save($object));
        $this->assertSame(2, $object->dbId);
        $this->assertSame(2, $this->db->countTableRows($objectTableName));

        $object = new Customer();
        $this->assertSame(3, $this->sut->save($object));
        $this->assertSame(3, $object->dbId);
        $this->assertSame(3, $this->db->countTableRows($objectTableName));

        // Test ok values - update instances

        $object = new Customer();
        $object->name = 'customer1';
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame(4, $object->dbId);
        $this->assertSame('customer1', $object->name);
        $this->assertSame(4, $this->db->countTableRows($objectTableName));

        $object->name = 'customer1-updated';
        $this->assertSame(4, $object->dbId);
        $this->assertSame(4, $this->sut->save($object));
        $this->assertSame('customer1-updated', $object->name);
        $this->assertSame(4, $this->db->countTableRows($objectTableName));
        // TODO

        // Test wrong values
        // Test exceptions

        $object = new Customer();
        $object->dbId = 'invalid';
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Customer dbId invalid value: invalid/');

        // Put a non existant id number
        $object = new Customer();
        $object->dbId = 5000000;
        AssertUtils::throwsException(function() use ($object) { $this->sut->save($object); }, '/Could not update row on table tdp_customer/');

        // Try to save a database object that contains declared methods
        AssertUtils::throwsException(function() { $this->sut->save(new WrongObjectWithMethods()); }, '/Methods are not allowed on DataBaseObjects: methodThatCantBeHere/');

        // TODO - wrong type values
    }
}

?>