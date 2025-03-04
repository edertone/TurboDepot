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
use Throwable;
use stdClass;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbodepot\src\main\php\managers\StorageFolderManager;
use org\turbotesting\src\main\php\utils\AssertUtils;


/**
 * StorageFolderManagerTest tests
 *
 * @return void
 */
class StorageFolderManagerTest extends TestCase {


    /**
     * @see TestCase::setUpBeforeClass()
     *
     * @return void
     */
    public static function setUpBeforeClass(): void{

        // Nothing necessary here
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(): void{

        $this->emptyValues = [null, '', [], new stdClass(), '     ', "\n\n\n", 0];

        $this->filesManager = new FilesManager();

        $this->tempFolder = $this->filesManager->createTempDirectory('TurboCommons-StorageFolderManagerTest');

        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'cache');
        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'custom');
        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'db');
        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'data');
        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'executable');
        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'logs');
        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'tmp');
        $this->filesManager->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'README.txt');

        $this->sut = new StorageFolderManager($this->tempFolder);
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(): void{

        // Delete temporary folder
        $this->filesManager->deleteDirectory($this->tempFolder);
    }


    /**
     * @see TestCase::tearDownAfterClass()
     *
     * @return void
     */
    public static function tearDownAfterClass(): void{

        // Nothing necessary here
    }


    /**
     * testConstruct
     *
     * @return void
     */
    public function testConstruct(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut = new StorageFolderManager(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut = new StorageFolderManager(0); }, '/Could not find storage folder based on/');
        AssertUtils::throwsException(function() { $this->sut = new StorageFolderManager(''); }, '/Could not find storage folder based on/');
        AssertUtils::throwsException(function() { $this->sut = new StorageFolderManager('          '); }, '/Could not find storage folder based on/');

        // Test ok values
        $this->assertSame('org\turbodepot\src\main\php\managers\StorageFolderManager', get_class(new StorageFolderManager($this->tempFolder)));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut = new StorageFolderManager('invalid/path/here'); }, '/Could not find storage folder based on: invalid\/path\/here/');
        AssertUtils::throwsException(function() { $this->sut = new StorageFolderManager([1, 2, 3], 'test'); }, '/must be of the type string, array given/');
    }


    /**
     * testValidateFolderStructure
     *
     * @return void
     */
    public function testValidateFolderStructure(){

        // Test empty values
        // Not necessary

        // Test ok values
        $this->assertTrue($this->sut->validateFolderStructure());

        // Test wrong values
        $this->assertSame(0, $this->filesManager->deleteDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'cache'));

        AssertUtils::throwsException(function() { $this->sut->validateFolderStructure(); }, '/The storage folder must have 7 directories/');

        $this->assertTrue($this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'invalid'));

        AssertUtils::throwsException(function() { $this->sut->validateFolderStructure(); }, '/The current storage folder does not have a cache folder/');

        // Test exceptions
        // Not necessary
    }


    // TODO - add missing tests
}

?>