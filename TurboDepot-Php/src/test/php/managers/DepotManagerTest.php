<?php

/**
 * TurboDepot is a cross language ORM: Save, read, list, filter and easily perform any storage operation with your application objects
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\test\php\managers;

use Throwable;
use stdClass;
use PHPUnit\Framework\TestCase;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbodepot\src\main\php\managers\DepotManager;


/**
 * DepotManagerTest tests
 *
 * @return void
 */
class DepotManagerTest extends TestCase {


    /**
     * @see TestCase::setUpBeforeClass()
     *
     * @return void
     */
    public static function setUpBeforeClass(){

        require_once __DIR__.'/../resources/libs/turbocommons-php-1.0.0.phar';
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(){

        $this->exceptionMessage = '';

        $this->filesManager = new FilesManager();

        // Create a temporary folder
        $this->tempFolder = $this->filesManager->createTempDirectory('TurboCommons-DepotManagerTest');
        $this->assertTrue(strpos($this->tempFolder, 'TurboCommons-DepotManagerTest') !== false);
        $this->assertTrue($this->filesManager->isDirectoryEmpty($this->tempFolder));
        $this->assertFalse($this->filesManager->isFile($this->tempFolder));

        $this->sut = new DepotManager(__DIR__.'/../resources/managers/depotManager/empty-turbodepot.json');
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
            $this->sut = new DepotManager(null, '');
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/expects a valid path to users setup or an stdclass/', $e->getMessage());
        }

        try {
            $this->sut = new DepotManager('', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/expects a valid path to users setup or an stdclass/', $e->getMessage());
        }

        try {
            $this->sut = new DepotManager('              ', '');
            $this->exceptionMessage = '"             " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/expects a valid path to users setup or an stdclass/', $e->getMessage());
        }

        try {
            $this->sut = new DepotManager(new stdClass(), '');
            $this->exceptionMessage = 'stdclass did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/expects a valid path to users setup or an stdclass/', $e->getMessage());
        }

        // Test ok values
        $this->assertSame('org\turbodepot\src\main\php\managers\DepotManager',
            get_class(new DepotManager(__DIR__.'/../resources/managers/depotManager/empty-turbodepot.json')));

        $setup = json_decode($this->filesManager->readFile(__DIR__.'/../resources/managers/depotManager/empty-turbodepot.json'));

        $this->assertSame('stdClass', get_class($setup));

        $this->assertSame('org\turbodepot\src\main\php\managers\DepotManager', get_class(new DepotManager($setup)));

        // Test wrong values
        // Already tested

        // Test exceptions
        // Already tested
    }


    /**
     * testGetFilesManager
     *
     * @return void
     */
    public function testGetFilesManager(){

        // Test empty values
        // Not necessary

        // Test ok values
        $this->assertSame('org\turbodepot\src\main\php\managers\FilesManager',
            get_class($this->sut->getFilesManager()));

        // Test wrong values
        // Not necessary

        // Test exceptions
        // Not necessary
    }


    /**
     * testTodo
     *
     * @return void
     */
    public function testTodo(){

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