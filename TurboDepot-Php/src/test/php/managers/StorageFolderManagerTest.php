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
use Throwable;
use stdClass;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbodepot\src\main\php\managers\StorageFolderManager;


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
    public static function setUpBeforeClass(){

        // Nothing necessary here
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(){

        $this->exceptionMessage = '';
        $this->emptyValues = [null, '', [], new stdClass(), '     ', "\n\n\n", 0];

        $this->filesManager = new FilesManager();

        $this->tempFolder = $this->filesManager->createTempDirectory('TurboCommons-StorageFolderManagerTest');

        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'cache');
        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'custom');
        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'db');
        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'executable');
        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'logs');
        $this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'tmp');

        $this->sut = new StorageFolderManager($this->tempFolder);
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){

        // Delete temporary folder
        $this->filesManager->deleteDirectory($this->tempFolder);

        if($this->exceptionMessage != ''){

            $this->fail($this->exceptionMessage);
        }
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
     * testConstruct
     *
     * @return void
     */
    public function testConstruct(){

        // Test empty values
        try {
            $this->sut = new StorageFolderManager(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, null given/', $e->getMessage());
        }

        try {
            $this->sut = new StorageFolderManager(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Could not find storage folder based on/', $e->getMessage());
        }

        try {
            $this->sut = new StorageFolderManager('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Could not find storage folder based on/', $e->getMessage());
        }

        try {
            $this->sut = new StorageFolderManager('          ');
            $this->exceptionMessage = '"      " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Could not find storage folder based on/', $e->getMessage());
        }

        // Test ok values
        $this->assertSame('org\turbodepot\src\main\php\managers\StorageFolderManager', get_class(new StorageFolderManager($this->tempFolder)));

        // Test wrong values
        // Test exceptions
        try {
            $this->sut = new StorageFolderManager('invalid/path/here');
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Could not find storage folder based on: invalid\/path\/here/', $e->getMessage());
        }

        try {
            $this->sut = new StorageFolderManager([1, 2, 3], 'test');
            $this->exceptionMessage = '[1, 2, 3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, array given/', $e->getMessage());
        }
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

        try {
            $this->sut->validateFolderStructure();
            $this->exceptionMessage = 'validateFolderStructure did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/The storage folder must have 6 directories/', $e->getMessage());
        }

        $this->assertTrue($this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'invalid'));

        try {
            $this->sut->validateFolderStructure();
            $this->exceptionMessage = 'validateFolderStructure did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/The current storage folder does not have a cache folder/', $e->getMessage());
        }

        // Test exceptions
        // Not necessary
    }


    // TODO - add missing tests
}

?>