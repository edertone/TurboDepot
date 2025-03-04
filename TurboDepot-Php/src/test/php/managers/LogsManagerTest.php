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
use org\turbodepot\src\main\php\managers\LogsManager;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbocommons\src\main\php\utils\StringUtils;


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
    public static function setUpBeforeClass(): void{

        // Nothing necessary here
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(): void{

        $this->exceptionMessage = '';

        $this->filesManager = new FilesManager();

        // Create a temporary folder
        $this->tempFolder = $this->filesManager->createTempDirectory('TurboCommons-LogsManagerTest');
        $this->assertTrue($this->filesManager->isDirectoryEmpty($this->tempFolder));

        $this->sut = new LogsManager($this->tempFolder);
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(): void{

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
        AssertUtils::throwsException(function(){ $this->sut = new LogsManager(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function(){ $this->sut = new LogsManager(0); }, '/LogsManager received an invalid rootPath: 0/');
        AssertUtils::throwsException(function(){ $this->sut = new LogsManager(''); }, '/LogsManager received an invalid rootPath: /');
        AssertUtils::throwsException(function(){ $this->sut = new LogsManager('          '); }, '/LogsManager received an invalid rootPath:           /');

        // Test ok values
        $this->assertSame('org\turbodepot\src\main\php\managers\LogsManager', get_class(new LogsManager($this->tempFolder)));

        // Test wrong values
        AssertUtils::throwsException(function(){ $this->sut = new LogsManager('invalid/path/here'); }, '/LogsManager received an invalid rootPath: invalid\/path\/here/');
        AssertUtils::throwsException(function(){ $this->sut = new LogsManager([1, 2, 3]); }, '/must be of the type string, array given/');

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
        AssertUtils::throwsException(function(){ $this->sut->write(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function(){ $this->sut->write('', ''); }, '/logFile must not be empt/');

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

        AssertUtils::throwsException(function(){ $this->sut->write('must not be written', 'mustnotbewritten', false, false); }, '/Log file does not exist and createFile is false: mustnotbewritten/');

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
     * trimLogs
     *
     * @return void
     */
    public function testTrimLogs(){

        $sep = $this->filesManager->dirSep();

        // Create some test log files
        $this->filesManager->saveFile($this->tempFolder.$sep.'test1.log', str_repeat('a', 1024));
        $this->filesManager->saveFile($this->tempFolder.$sep.'test2.log', str_repeat('b', 2048));
        $this->filesManager->saveFile($this->tempFolder.$sep.'test3.txt', str_repeat('c', 4096));

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->trimLogs(null, null); }, '/must be of the type float, null given/');
        AssertUtils::throwsException(function(){ $this->sut->trimLogs('', ''); }, '/must be of the type float, string given/');
        AssertUtils::throwsException(function(){ $this->sut->trimLogs(0, ''); }, '/maxSize must be a positive value/');

        // Test trimming all values
        $this->sut->trimLogs(4);
        $this->assertEquals(1024, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test1.log')));
        $this->assertEquals(2048, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test2.log')));
        $this->assertEquals(4096, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test3.txt')));

        $this->sut->trimLogs(2);
        $this->assertEquals(1024, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test1.log')));
        $this->assertEquals(2048, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test2.log')));
        $this->assertEquals(2048, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test3.txt')));

        $this->sut->trimLogs(1);
        $this->assertEquals(1024, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test1.log')));
        $this->assertEquals(1024, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test2.log')));
        $this->assertEquals(1024, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test3.txt')));

        $this->sut->trimLogs(0.5);
        $this->assertEquals(512, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test1.log')));
        $this->assertEquals(512, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test2.log')));
        $this->assertEquals(512, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test3.txt')));

        // Recreate the original log files
        $this->filesManager->saveFile($this->tempFolder.$sep.'test1.log', str_repeat('a', 1024));
        $this->filesManager->saveFile($this->tempFolder.$sep.'test2.log', str_repeat('b', 2048));
        $this->filesManager->saveFile($this->tempFolder.$sep.'test3.txt', str_repeat('c', 4096));

        // Test triming only specific files
        $this->sut->trimLogs(0.5, '/test3.txt/');
        $this->assertEquals(1024, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test1.log')));
        $this->assertEquals(2048, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test2.log')));
        $this->assertEquals(512, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test3.txt')));

        $this->sut->trimLogs(0.5, '/test2.log/');
        $this->assertEquals(1024, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test1.log')));
        $this->assertEquals(512, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test2.log')));
        $this->assertEquals(512, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test3.txt')));

        $this->sut->trimLogs(0.5, '/test1.log/');
        $this->assertEquals(512, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test1.log')));
        $this->assertEquals(512, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test2.log')));
        $this->assertEquals(512, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test3.txt')));

        // Test making sure that the file gets only the upper part trimmed and the last data is kept
        $this->filesManager->saveFile($this->tempFolder.$sep.'test1.log', str_repeat('a', 1024).str_repeat('b', 1024).str_repeat('c', 1024));
        $this->assertTrue(StringUtils::isStartingWith($this->filesManager->readFile($this->tempFolder.$sep.'test1.log'), ['a']));
        $this->assertTrue(StringUtils::isEndingWith($this->filesManager->readFile($this->tempFolder.$sep.'test1.log'), ['c']));

        $this->sut->trimLogs(2, '/test1.log/');
        $this->assertEquals(2048, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test1.log')));
        $this->assertTrue(StringUtils::isStartingWith($this->filesManager->readFile($this->tempFolder.$sep.'test1.log'), ['b']));
        $this->assertTrue(StringUtils::isEndingWith($this->filesManager->readFile($this->tempFolder.$sep.'test1.log'), ['c']));

        $this->sut->trimLogs(1, '/test1.log/');
        $this->assertEquals(1024, strlen($this->filesManager->readFile($this->tempFolder.$sep.'test1.log')));
        $this->assertTrue(StringUtils::isStartingWith($this->filesManager->readFile($this->tempFolder.$sep.'test1.log'), ['c']));
        $this->assertTrue(StringUtils::isEndingWith($this->filesManager->readFile($this->tempFolder.$sep.'test1.log'), ['c']));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function(){ $this->sut->trimLogs(-1, '/test1.log/'); }, '/maxSize must be a positive value/');
        AssertUtils::throwsException(function(){ $this->sut->trimLogs(1, 'test1.log'); }, '/pattern must be a non empty regexp/');
    }
}
