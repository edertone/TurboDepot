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

use PHPUnit\Framework\TestCase;
use Throwable;
use org\turbodepot\src\main\php\managers\LogsManager;
use org\turbodepot\src\main\php\managers\FilesManager;


/**
 * LogsManagerTest tests
 *
 * @return void
 */
class LogsManagerTest extends TestCase {


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
        $this->tempFolder = $this->filesManager->createTempDirectory('TurboCommons-LogsManagerTest');
        $this->assertTrue(strpos($this->tempFolder, 'TurboCommons-LogsManagerTest') !== false);
        $this->assertTrue($this->filesManager->isDirectoryEmpty($this->tempFolder));
        $this->assertFalse($this->filesManager->isFile($this->tempFolder));

        $this->sut = new LogsManager($this->tempFolder);
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
            $this->sut = new LogsManager(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, null given/', $e->getMessage());
        }

        try {
            $this->sut = new LogsManager(0);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/LogsManager received an invalid rootPath: 0/', $e->getMessage());
        }

        try {
            $this->sut = new LogsManager('');
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/LogsManager received an invalid rootPath: /', $e->getMessage());
        }

        try {
            $this->sut = new LogsManager('          ');
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/LogsManager received an invalid rootPath:           /', $e->getMessage());
        }

        // Test ok values
        $this->assertSame('org\turbodepot\src\main\php\managers\LogsManager', get_class(new LogsManager($this->tempFolder)));

        // Test wrong values
        try {
            $this->sut = new LogsManager('invalid/path/here');
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/LogsManager received an invalid rootPath: invalid\/path\/here/', $e->getMessage());
        }

        try {
            $this->sut = new LogsManager([1, 2, 3]);
            $this->exceptionMessage = '[1, 2, 3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, array given/', $e->getMessage());
        }

        // Test exceptions
        // Already tested;
    }


    /**
     * testWrite
     *
     * @return void
     */
    public function testWrite(){

        $sep = $this->filesManager->dirSep();

        // Test empty values
        try {
            $this->sut->write(null, null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, null given/', $e->getMessage());
        }

        try {
            $this->sut->write('', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/logFile must not be empty/', $e->getMessage());
        }

        // Test ok values
        $this->sut->write('some text', 'testFile');
        $this->assertRegExp('/....-..-.. ..:..:......... some text\n/', $this->filesManager->readFile($this->tempFolder.$sep.'testFile'));

        $this->sut->write('some text', 'testFile');
        $this->assertRegExp('/....-..-.. ..:..:......... some text\n....-..-.. ..:..:......... some text\n/',
            $this->filesManager->readFile($this->tempFolder.$sep.'testFile'));

        $this->sut->write('another text', 'testFile');
        $this->assertRegExp('/....-..-.. ..:..:......... some text\n....-..-.. ..:..:......... some text\n....-..-.. ..:..:......... another text\n/',
            $this->filesManager->readFile($this->tempFolder.$sep.'testFile'));

        $this->sut->write('some text', 'fileWithoutDate', false);
        $this->assertRegExp('/some text\n/', $this->filesManager->readFile($this->tempFolder.$sep.'fileWithoutDate'));

        $this->sut->write('a', 'fileWithoutDate', false);
        $this->sut->write('b', 'fileWithoutDate', false);
        $this->sut->write('c', 'fileWithoutDate', false);
        $this->sut->write('d', 'fileWithoutDate', false);
        $this->sut->write('e', 'fileWithoutDate', false);
        $this->sut->write('f', 'fileWithoutDate', false);
        $this->assertRegExp('/some text\na\nb\nc\nd\ne\nf/', $this->filesManager->readFile($this->tempFolder.$sep.'fileWithoutDate'));

        try {
            $this->sut->write('must not be written', 'mustnotbewritten', false, false);
            $this->exceptionMessage = 'mustnotbewritten did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Log file does not exist and createFile is false: mustnotbewritten/', $e->getMessage());
        }

        $this->assertFalse($this->filesManager->isFile($this->tempFolder.$sep.'mustnotbewritten'));

        $this->sut->write('some text on a subfolder log file', 'subfolder/testFile', false);
        $this->assertRegExp('/some text on a subfolder log file\n/', $this->filesManager->readFile($this->tempFolder.$sep.'subfolder/testFile'));
        $this->assertTrue($this->filesManager->isFile($this->tempFolder.$sep.'subfolder'.$sep.'testFile'));

        // Test wrong values
        // already tested

        // Test exceptions
        // already tested
    }


    /**
     * testTruncate
     *
     * @return void
     */
    public function testTruncate(){

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