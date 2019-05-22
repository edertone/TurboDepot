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
use org\turbodepot\src\main\php\managers\TmpFilesManager;


/**
 * TmpFilesManagerTest tests
 *
 * @return void
 */
class TmpFilesManagerTest extends TestCase {


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

        $this->filesManager = new FilesManager();

        // Create a temporary folder
        $this->tempFolder = $this->filesManager->createTempDirectory('TurboDepot-TmpFilesManagerTest');
        $this->assertTrue(strpos($this->tempFolder, 'TurboDepot-TmpFilesManagerTest') !== false);
        $this->assertTrue($this->filesManager->isDirectoryEmpty($this->tempFolder));
        $this->assertFalse($this->filesManager->isFile($this->tempFolder));

        $this->sut = new TmpFilesManager($this->tempFolder);
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){

        // Delete temporary folder
        $this->assertTrue($this->filesManager->deleteDirectory($this->tempFolder));

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
            $this->sut = new TmpFilesManager(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, null given/', $e->getMessage());
        }

        try {
            $this->sut = new TmpFilesManager('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Specified rootPath does not exist/', $e->getMessage());
        }

        try {
            $this->sut = new TmpFilesManager('              ');
            $this->exceptionMessage = '"             " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Specified rootPath does not exist/', $e->getMessage());
        }

        try {
            $this->sut = new TmpFilesManager(new stdClass());
            $this->exceptionMessage = 'stdclass did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, object given/', $e->getMessage());
        }

        // Test ok values
        $this->assertSame('org\turbodepot\src\main\php\managers\TmpFilesManager',
            get_class(new TmpFilesManager($this->tempFolder)));

        // Test wrong values
        // Already tested

        // Test exceptions
        // Already tested
    }


    /**
     * testAddFile
     *
     * @return void
     */
    public function testAddFile(){

        // Test empty values
        try {
            $this->sut->addFile(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/binaryData must be a string/', $e->getMessage());
        }

        try {
            $this->sut->addFile(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/binaryData must be a string/', $e->getMessage());
        }

        try {
            $this->sut->addFile([]);
            $this->exceptionMessage = '[] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/binaryData must be a string/', $e->getMessage());
        }

        try {
            $this->sut->addFile('', null);
            $this->exceptionMessage = 'null id did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a string/', $e->getMessage());
        }

        try {
            $this->sut->addFile('', '', null);
            $this->exceptionMessage = 'null minutesToLive did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/minutesToLive must be a positive integer/', $e->getMessage());
        }

        $tmpFile = $this->sut->addFile('data1');
        $this->assertRegExp('/....-..-.._..-..-.._/', $tmpFile);
        $this->assertSame('data1', $this->filesManager->readFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile));

        $tmpFile = $this->sut->addFile('data2');
        $this->assertRegExp('/....-..-.._..-..-.._-1/', $tmpFile);
        $this->assertSame('data2', $this->filesManager->readFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile));

        // Test ok values
        $tmpFile = $this->sut->addFile('value1', 'test');
        $this->assertRegExp('/....-..-.._..-..-.._test/', $tmpFile);
        $this->assertSame('value1', $this->filesManager->readFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile));

        $tmpFile = $this->sut->addFile('value2', 'test');
        $this->assertRegExp('/....-..-.._..-..-.._test-1/', $tmpFile);
        $this->assertSame('value2', $this->filesManager->readFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile));

        $tmpFile = $this->sut->addFile('value3', 'test');
        $this->assertRegExp('/....-..-.._..-..-.._test-2/', $tmpFile);
        $this->assertSame('value3', $this->filesManager->readFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile));

        $tmpFile = $this->sut->addFile('nominutes1', 'nominutes', 0);
        $this->assertRegExp('/....-..-.._..-..-.._nominutes/', $tmpFile);
        $this->assertSame('nominutes1', $this->filesManager->readFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile));

        $tmpFile = $this->sut->addFile('nominutes2', 'nominutes', 0);
        $this->assertRegExp('/....-..-.._..-..-.._nominutes-1/', $tmpFile);
        $this->assertSame('nominutes2', $this->filesManager->readFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile));

        $tmpFile = $this->sut->addFile('minutes1', 'minutes', 10000);
        $this->assertRegExp('/....-..-.._..-..-.._minutes/', $tmpFile);
        $this->assertSame('minutes1', $this->filesManager->readFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile));

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->addFile(234234);
            $this->exceptionMessage = '234234 minutesToLive did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/binaryData must be a string/', $e->getMessage());
        }

        try {
            $this->sut->addFile('', '', -20);
            $this->exceptionMessage = '-20 minutesToLive did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/minutesToLive must be a positive integer/', $e->getMessage());
        }
    }


    /**
     * testReadFile
     *
     * @return void
     */
    public function testReadFile(){

        // Test empty values
        try {
            $this->sut->readFile(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->readFile('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->readFile(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->readFile([]);
            $this->exceptionMessage = '[] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        // Test ok values
        $this->assertSame('data1', $this->sut->readFile($this->sut->addFile('data1', 'test')));
        $this->assertSame('data2', $this->sut->readFile($this->sut->addFile('data2', 'test')));
        $this->assertSame('data3', $this->sut->readFile($this->sut->addFile('data3', 'test')));

        // Test wrong values
        try {
            $this->sut->readFile('non-existant-id');
            $this->exceptionMessage = '"non-existant-id" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/File does not exist/', $e->getMessage());
        }

        // Test exceptions
        try {
            $this->sut->readFile([123123, 9]);
            $this->exceptionMessage = '[123123, 9] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }
    }


    /**
     * testAddDirectory
     *
     * @return void
     */
    public function testAddDirectory(){

        // Test empty values
        try {
            $this->sut->addDirectory(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a string/', $e->getMessage());
        }

        $this->assertRegExp('/....-..-.._..-..-.._/', $this->sut->addDirectory());
        $this->assertRegExp('/....-..-.._..-..-.._-1/', $this->sut->addDirectory(''));

        try {
            $this->sut->addDirectory(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a string/', $e->getMessage());
        }

        try {
            $this->sut->addDirectory([]);
            $this->exceptionMessage = '[] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a string/', $e->getMessage());
        }

        try {
            $this->sut->addDirectory('', null);
            $this->exceptionMessage = '"", null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/minutesToLive must be a positive integer/', $e->getMessage());
        }

        // Test ok values
        $tmpDir = $this->sut->addDirectory('test');
        $this->assertRegExp('/....-..-.._..-..-.._test/', $tmpDir);
        $this->assertTrue($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir));

        $tmpDir = $this->sut->addDirectory('test');
        $this->assertRegExp('/....-..-.._..-..-.._test-1/', $tmpDir);
        $this->assertTrue($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir));

        $tmpDir = $this->sut->addDirectory('test');
        $this->assertRegExp('/....-..-.._..-..-.._test-2/', $tmpDir);
        $this->assertTrue($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir));

        $tmpDir = $this->sut->addDirectory('nominutes', 0);
        $this->assertRegExp('/....-..-.._..-..-.._nominutes/', $tmpDir);
        $this->assertTrue($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir));

        $tmpDir = $this->sut->addDirectory('nominutes', 0);
        $this->assertRegExp('/....-..-.._..-..-.._nominutes-1/', $tmpDir);
        $this->assertTrue($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir));

        $tmpDir = $this->sut->addDirectory('minutes', 10000);
        $this->assertRegExp('/....-..-.._..-..-.._minutes/', $tmpDir);
        $this->assertTrue($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir));

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->addDirectory(234234);
            $this->exceptionMessage = '234234 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a string/', $e->getMessage());
        }

        try {
            $this->sut->addDirectory('', -20);
            $this->exceptionMessage = '-20 minutesToLive did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/minutesToLive must be a positive integer/', $e->getMessage());
        }
    }


    /**
     * testGetFilePath
     *
     * @return void
     */
    public function testGetFilePath(){

        // Test empty values
        try {
            $this->sut->getFilePath(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->getFilePath('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->getFilePath(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->getFilePath([]);
            $this->exceptionMessage = '[] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        // Test ok values
        $tmpFile = $this->sut->addFile('data1', 'test');
        $this->assertSame($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile, $this->sut->getFilePath($tmpFile));

        $tmpFile2 = $this->sut->addFile('data2', 'test');
        $this->assertSame($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile2, $this->sut->getFilePath($tmpFile2));
        $this->assertNotSame($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile, $this->sut->getFilePath($tmpFile2));

        // Test wrong values
        try {
            $this->sut->getFilePath('non-existant-id');
            $this->exceptionMessage = '"non-existant-id" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Tmp file not found/', $e->getMessage());
        }

        // Test exceptions
        try {
            $this->sut->getFilePath([123123, 9]);
            $this->exceptionMessage = '[123123, 9] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }
    }


    /**
     * testGetDirectoryPath
     *
     * @return void
     */
    public function testGetDirectoryPath(){

        // Test empty values
        try {
            $this->sut->getDirectoryPath(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->getDirectoryPath('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->getDirectoryPath(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->getDirectoryPath([]);
            $this->exceptionMessage = '[] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        // Test ok values
        $tmpDir = $this->sut->addDirectory('test');
        $this->assertSame($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir, $this->sut->getDirectoryPath($tmpDir));

        $tmpDir2 = $this->sut->addDirectory('test');
        $this->assertSame($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir2, $this->sut->getDirectoryPath($tmpDir2));
        $this->assertNotSame($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir, $this->sut->getDirectoryPath($tmpDir2));

        // Test wrong values
        try {
            $this->sut->getDirectoryPath('non-existant-id');
            $this->exceptionMessage = '"non-existant-id" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Tmp dir not found/', $e->getMessage());
        }

        // Test exceptions
        try {
            $this->sut->getDirectoryPath([123123, 9]);
            $this->exceptionMessage = '[123123, 9] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }
    }


    /**
     * testCleanAllExpired
     *
     * @return void
     */
    public function testCleanAllExpired(){

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
     * testDeleteFile
     *
     * @return void
     */
    public function testDeleteFile(){

        // Test empty values
        try {
            $this->sut->deleteFile(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->deleteFile('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->deleteFile(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->deleteFile([]);
            $this->exceptionMessage = '[] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        // Test ok values
        $tmpFile1 = $this->sut->addFile('data1', 'test');
        $this->assertTrue($this->filesManager->isFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile1));

        $tmpFile2 = $this->sut->addFile('data1', 'test');
        $this->assertTrue($this->filesManager->isFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile2));

        $this->assertTrue($this->sut->deleteFile($tmpFile1));
        $this->assertFalse($this->sut->deleteFile($tmpFile1));
        $this->assertFalse($this->filesManager->isFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile1));

        $this->assertTrue($this->sut->deleteFile($tmpFile2));
        $this->assertFalse($this->sut->deleteFile($tmpFile2));
        $this->assertFalse($this->filesManager->isFile($this->tempFolder.DIRECTORY_SEPARATOR.$tmpFile2));

        // Test wrong values
        $this->assertFalse($this->sut->deleteFile('non-existant-id'));

        // Test exceptions
        try {
            $this->sut->deleteFile([123123, 9]);
            $this->exceptionMessage = '[123123, 9] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }
    }


    /**
     * testDeleteDirectory
     *
     * @return void
     */
    public function testDeleteDirectory(){

        // Test empty values
        try {
            $this->sut->deleteDirectory(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->deleteDirectory('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->deleteDirectory(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->deleteDirectory([]);
            $this->exceptionMessage = '[] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        // Test ok values
        $tmpDir1 = $this->sut->addDirectory('dir');
        $this->assertTrue($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir1));

        $tmpDir2 = $this->sut->addDirectory('dir');
        $this->assertTrue($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir2));

        $this->assertTrue($this->sut->deleteDirectory($tmpDir1));

        try {
            $this->sut->deleteDirectory($tmpDir1);
            $this->exceptionMessage = '$tmpDir1 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist/', $e->getMessage());
        }

        $this->assertFalse($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir1));

        $this->assertTrue($this->sut->deleteDirectory($tmpDir2));

        try {
            $this->sut->deleteDirectory($tmpDir2);
            $this->exceptionMessage = '$tmpDir2 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist/', $e->getMessage());
        }

        $this->assertFalse($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.$tmpDir2));

        // Test wrong values
        try {
            $this->sut->deleteDirectory('non-existant-id');
            $this->exceptionMessage = 'non-existant-id did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist/', $e->getMessage());
        }

        // Test exceptions
        try {
            $this->sut->deleteDirectory([123123, 9]);
            $this->exceptionMessage = '[123123, 9] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }
    }
}

?>