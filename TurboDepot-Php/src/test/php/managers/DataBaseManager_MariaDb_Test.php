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

use UnexpectedValueException;
use PHPUnit\Framework\TestCase;
use org\turbodepot\src\main\php\managers\DataBaseManager;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbotesting\src\main\php\utils\AssertUtils;


/**
 * DataBaseManagerTest
 *
 * @return void
 */
class DataBaseManager_MariaDb_Test extends TestCase {


    /**
     * Load the database setup for testing parameters from resources, connect to the provided database engine and make sure an empty
     * database exists (with the name that is defined at the testing parameters), and return a DataBaseObjectsManager instance
     * which is fully initialized and ready to be used with that db
     *
     * @return DataBaseObjectsManager An initialized instance that is ready to operate with the testing database
     */
    public static function createAndConnectToTestingMariaDb(){

        $filesManager = new FilesManager();

        $dbSetup = json_decode($filesManager->readFile(__DIR__.'/../../resources/managers/databaseManager/database-setup-for-testing.json'));

        $db = new DataBaseManager();

        if(!$db->connectMariaDb($dbSetup->host, $dbSetup->user, $dbSetup->psw)){

            throw new UnexpectedValueException('Could not connect to database '.$dbSetup->host);
        }

        if($db->dataBaseExists($dbSetup->dbName) && !$db->dataBaseDelete($dbSetup->dbName)){

            throw new UnexpectedValueException('Could not delete database '.$dbSetup->dbName);
        }

        if(!$db->dataBaseCreate($dbSetup->dbName)){

            throw new UnexpectedValueException('Could not create database '.$dbSetup->dbName);
        }

        if(!$db->dataBaseSelect($dbSetup->dbName)){

            throw new UnexpectedValueException('Could not select database '.$dbSetup->dbName);
        }

        if($dbSetup->dbName !== $db->dataBaseGetSelected()){

            throw new UnexpectedValueException('Could not select database '.$dbSetup->dbName);
        }

        $dbObjectsManager = new DataBaseObjectsManager();

        if(!$dbObjectsManager->connectMariaDb($dbSetup->host, $dbSetup->user, $dbSetup->psw, $dbSetup->dbName)){

            throw new UnexpectedValueException('Could not connect to database '.$dbSetup->dbName);
        }

        return $dbObjectsManager;
    }


    /**
     * Delete the testing database as it is defined on the setup for testing file and disconnect from the db engine
     *
     * @param DatabaseObjectsManager $databaseObjectsManager The same DatabaseObjectsManager instance that was previously initialized
     *        and returned by the createAndConnectToTestingMariaDb() method
     */
    public static function deleteAndDisconnectFromTestingMariaDb(DatabaseObjectsManager $databaseObjectsManager){

        $filesManager = new FilesManager();

        $dbSetup = json_decode($filesManager->readFile(__DIR__.'/../../resources/managers/databaseManager/database-setup-for-testing.json'));

        $db = $databaseObjectsManager->getDataBaseManager();

        if($db->isAnyTransactionActive()){

            throw new UnexpectedValueException('Unclosed transactions exist!! ');
        }

        if(!$db->dataBaseExists($dbSetup->dbName)){

            throw new UnexpectedValueException('Database does not exist '.$dbSetup->dbName);
        }

        if(!$db->dataBaseDelete($dbSetup->dbName)){

            throw new UnexpectedValueException('Could not delete database '.$dbSetup->dbName);
        }

        if(!$db->disconnect()){

            throw new UnexpectedValueException('Could not disconnect from host '.$dbSetup->host);
        }
    }


    /**
     * @see TestCase::setUpBeforeClass()
     *
     * @return void
     */
    public static function setUpBeforeClass(){

        // Nothing necessary here
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(){

        $this->databaseObjectsManager = DataBaseManager_MariaDb_Test::createAndConnectToTestingMariaDb();
        $this->sut = $this->databaseObjectsManager->getDataBaseManager();
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){

        DataBaseManager_MariaDb_Test::deleteAndDisconnectFromTestingMariaDb($this->databaseObjectsManager);
    }


    /**
     * @see TestCase::tearDownAfterClass()
     *
     * @return void
     */
    public static function tearDownAfterClass(){

        // Nothing necessary here
    }


    /**
     * test
     */
    public function testConstruct(){

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


    // TODO - implement all missing tests


    /**
     * test
     */
    public function testQuery(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->query(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function(){ $this->sut->query(null); }, '/Empty query/');
        AssertUtils::throwsException(function(){ $this->sut->query(''); }, '/Empty query/');
        // TODO

        // Test ok values
        // TODO

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function(){ $this->sut->query('INVALID QUERY'); }, '/You have an error in your SQL syntax/');
        $this->assertRegExp('/You have an error in your SQL syntax/', $this->sut->getLastError());
        AssertUtils::throwsException(function(){ $this->sut->query('SELECT * FROM invalidtable'); }, '/.*invalidtable.*doesn.t exist/');
        $this->assertRegExp('/.*invalidtable.*doesn.t exist/', $this->sut->getLastError());
        $this->assertSame(2, count($this->sut->getQueryHistory()));
        // TODO
    }


    // TODO - implement all missing tests
}

?>