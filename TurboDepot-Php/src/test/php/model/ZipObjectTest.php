<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\test\php\model;

use PHPUnit\Framework\TestCase;
use org\turbodepot\src\main\php\model\ZipObject;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbotesting\src\main\php\utils\AssertUtils;


/**
 * ZipObjectTest
 *
 * @return void
 */
class ZipObjectTest extends TestCase {


    /**
     * @var ZipObject
     */
    private $sut;

    protected static $basePath;

    protected static $filesManager;


    /**
     * @see TestCase::setUpBeforeClass()
     *
     * @return void
     */
    public static function setUpBeforeClass(): void{

        self::$basePath = __DIR__.'/../../resources/model/zipObject';

        self::$filesManager = new FilesManager();
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(): void{

        $this->sut = new ZipObject();
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(): void{

        $this->assertSame(false, $this->sut->isLoaded());
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
        $this->sut = new ZipObject();
        $this->assertInstanceOf(ZipObject::class, $this->sut);

        // Test ok values
        // Not necessary

        // Test wrong values
        // Not necessary

        // Test exceptions
        // Not necessary

        $this->sut->close();
    }


    /**
     * isLoaded
     *
     * @return void
     */
    public function testIsLoaded(){

        // Test empty values
        $this->assertFalse($this->sut->isLoaded());

        // Test ok values
        $this->assertTrue($this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-0-files.zip'));
        $this->assertTrue($this->sut->isLoaded());

        // Test wrong values
        // Not necessary

        // Test exceptions
        // Not necessary

        $this->sut->close();
        $this->assertFalse($this->sut->isLoaded());
    }


    /**
     * loadPath
     *
     * @return void
     */
    public function testLoadPath(){

        // Test empty values
        $this->assertFalse($this->sut->isLoaded());
        AssertUtils::throwsException(function() { $this->sut->loadPath(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->loadPath(''); }, '/Invalid path/');
        AssertUtils::throwsException(function() { $this->sut->loadPath([]); }, '/must be of the type string, array given/');
        AssertUtils::throwsException(function() { $this->sut->loadPath(0); }, '/Invalid path/');
        $this->assertFalse($this->sut->isLoaded());

        // Test ok values
        $this->assertTrue($this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-0-files.zip'));
        $this->assertTrue($this->sut->isLoaded());
        $this->assertTrue($this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-2-files.zip'));
        $this->assertTrue($this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-files-and-folders.zip'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'nonexistant.zip');
            }, '/Could not load existing zip file path. Error code: No such file/');

        AssertUtils::throwsException(function() { $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'corrupt-zip-file.zip');
            }, '/Could not load existing zip file path. Error code: Not a zip archive/');

        $this->assertFalse($this->sut->isLoaded());
        $this->assertSame(false, self::$filesManager->isFile(self::$basePath.DIRECTORY_SEPARATOR.'nonexistant.zip'));
        $this->assertSame(true, self::$filesManager->isFile(self::$basePath.DIRECTORY_SEPARATOR.'corrupt-zip-file.zip'));

        $this->sut->close();
    }


    /**
     * loadBinary
     *
     * @return void
     */
    public function testLoadBinary(){

        // Test empty values
        $this->assertFalse($this->sut->isLoaded());
        AssertUtils::throwsException(function() { $this->sut->loadBinary(null); }, '/null given/');
        AssertUtils::throwsException(function() { $this->sut->loadBinary(''); }, '/Invalid data/');
        AssertUtils::throwsException(function() { $this->sut->loadBinary([]); }, '/array given/');
        $this->assertFalse($this->sut->isLoaded());

        // Test ok values
        $this->assertTrue($this->sut->loadBinary(base64_decode('UEsFBgAAAAAAAAAAAAAAAAAAAAAAAA==')));
        $this->assertSame(0, $this->sut->countContents());
        $this->assertTrue($this->sut->isLoaded());

        $this->assertTrue($this->sut->loadBinary(base64_decode('UEsDBAoAAAAAAGhclVgwPfsABgAAAAYAAAAKAAAAZmlsZSAxLnR4dGZpbGUgMVBLAwQKAAAAAABrXJVYimzymQYAAAAGAAAACgAAAGZpbGUgMi50eHRmaWxlIDJQSwECPwAKAAAAAABoXJVYMD37AAYAAAAGAAAACgAkAAAAAAAAACAgAAAAAAAAZmlsZSAxLnR4dAoAIAAAAAAAAQAYAK2QfzbPk9oBAAAAAAAAAAAAAAAAAAAAAFBLAQI/AAoAAAAAAGtclViKbPKZBgAAAAYAAAAKACQAAAAAAAAAICAAAC4AAABmaWxlIDIudHh0CgAgAAAAAAABABgAHXyzOs+T2gEAAAAAAAAAAAAAAAAAAAAAUEsFBgAAAAACAAIAuAAAAFwAAAAAAA==')));
        $this->assertSame(2, $this->sut->countContents());
        $this->assertSame('file 1', $this->sut->readEntry('file 1.txt'));
        $this->assertSame('file 2', $this->sut->readEntry('file 2.txt'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->loadBinary('invalid binary'); }, '/Not a zip archive/');
        AssertUtils::throwsException(function() { $this->sut->loadBinary(base64_decode('UEsFBgAAAAAAAAAAAAAAAAAAA==')); }, '/Not a zip archive/');
        $this->assertFalse($this->sut->isLoaded());

        $this->sut->close();
    }


    /**
     * loadBase64
     *
     * @return void
     */
    public function testLoadBase64(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->loadBase64(null); }, '/null given/');
        AssertUtils::throwsException(function() { $this->sut->loadBase64(''); }, '/Invalid data/');
        AssertUtils::throwsException(function() { $this->sut->loadBase64([]); }, '/array given/');

        // Test ok values
        $this->assertTrue($this->sut->loadBase64('UEsFBgAAAAAAAAAAAAAAAAAAAAAAAA=='));
        $this->assertSame(0, $this->sut->countContents());

        $this->assertTrue($this->sut->loadBase64('UEsDBAoAAAAAAGhclVgwPfsABgAAAAYAAAAKAAAAZmlsZSAxLnR4dGZpbGUgMVBLAwQKAAAAAABrXJVYimzymQYAAAAGAAAACgAAAGZpbGUgMi50eHRmaWxlIDJQSwECPwAKAAAAAABoXJVYMD37AAYAAAAGAAAACgAkAAAAAAAAACAgAAAAAAAAZmlsZSAxLnR4dAoAIAAAAAAAAQAYAK2QfzbPk9oBAAAAAAAAAAAAAAAAAAAAAFBLAQI/AAoAAAAAAGtclViKbPKZBgAAAAYAAAAKACQAAAAAAAAAICAAAC4AAABmaWxlIDIudHh0CgAgAAAAAAABABgAHXyzOs+T2gEAAAAAAAAAAAAAAAAAAAAAUEsFBgAAAAACAAIAuAAAAFwAAAAAAA=='));
        $this->assertSame(2, $this->sut->countContents());
        $this->assertSame('file 1', $this->sut->readEntry('file 1.txt'));
        $this->assertSame('file 2', $this->sut->readEntry('file 2.txt'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->loadBase64('invalid binary'); }, '/Not a zip archive/');
        AssertUtils::throwsException(function() { $this->sut->loadBase64('UEsFBgAAAAAAAAAAAAAAAAAAA=='); }, '/Not a zip archive/');

        $this->sut->close();
    }


    /**
     * countContents
     *
     * @return void
     */
    public function testCountContents(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->countContents(); }, '/No zip file is loaded/');
        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-0-files.zip');
        $this->assertSame(0, $this->sut->countContents());

        // Test ok values
        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-2-files.zip');
        $this->assertSame(2, $this->sut->countContents());

        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-files-and-folders.zip');
        $this->assertSame(9, $this->sut->countContents());

        // Test wrong values
        // Test exceptions
        // Nothing to do

        $this->sut->close();
    }


    /**
     * listContents
     *
     * @return void
     */
    public function testListContents(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->listContents(); }, '/No zip file is loaded/');

        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-0-files.zip');
        $this->assertSame([], $this->sut->listContents());

        // Test ok values
        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-2-files.zip');
        $this->assertSame(['file 1.txt', 'file 2.txt'], $this->sut->listContents());

        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-files-and-folders.zip');
        $this->assertSame([
            'file 1.txt',
            'file 2.txt',
            'folder 1/',
            'folder 1/file 3.txt',
            'folder 1/folder 3/',
            'folder 1/folder 3/file 4.txt',
            'folder 2/',
            'folder 2/file 5.txt',
            'empty folder/'
            ], $this->sut->listContents());

        // Test wrong values
        // Not necessary

        // Test exceptions
        // Not necessary

        $this->sut->close();
    }


    /**
     * isEmptyFolder
     *
     * @return void
     */
    public function testIsEmptyFolder(){

        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-0-files.zip');

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->isEmptyFolder(null); }, '/null given/');
        AssertUtils::throwsException(function() { $this->sut->isEmptyFolder(''); }, '/Invalid entry path/');
        AssertUtils::throwsException(function() { $this->sut->isEmptyFolder([]); }, '/array given/');
        AssertUtils::throwsException(function() { $this->sut->isEmptyFolder(0); }, '/Zip file is empty/');
        AssertUtils::throwsException(function() { $this->sut->isEmptyFolder('empty folder/'); }, '/Zip file is empty/');

        // Test ok values
        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-files-and-folders.zip');
        $this->assertFalse($this->sut->isEmptyFolder('folder 1'));
        $this->assertTrue($this->sut->isEmptyFolder('empty folder/'));

        // Test wrong values
        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-2-files.zip');
        $this->assertFalse($this->sut->isEmptyFolder('folder 1'));
        $this->assertFalse($this->sut->isEmptyFolder('folder 2'));

        // Test exceptions
        // Not necessary

        $this->sut->close();
    }


    /**
     * isFile
     *
     * @return void
     */
    public function testIsFile(){

        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-0-files.zip');

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->isFile(null); }, '/null given/');
        AssertUtils::throwsException(function() { $this->sut->isFile(''); }, '/Invalid entry path/');
        AssertUtils::throwsException(function() { $this->sut->isFile([]); }, '/array given/');
        AssertUtils::throwsException(function() { $this->sut->isFile(0); }, '/Zip file is empty/');
        AssertUtils::throwsException(function() { $this->sut->isFile('empty folder/'); }, '/Zip file is empty/');

        // Test ok values
        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-files-and-folders.zip');
        $this->assertTrue($this->sut->isFile('folder 1'));
        $this->assertFalse($this->sut->isFile('empty folder/'));

        // Test wrong values
        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-2-files.zip');
        $this->assertTrue($this->sut->isFile('folder 1'));
        $this->assertTrue($this->sut->isFile('folder 2'));

        // Test exceptions
        // Not necessary

        $this->sut->close();
    }


    /**
     * readEntry
     *
     * @return void
     */
    public function testReadEntry(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->readEntry(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->readEntry(''); }, '/Invalid entry path/');
        AssertUtils::throwsException(function() { $this->sut->readEntry('     '); }, '/Invalid entry path/');
        AssertUtils::throwsException(function() { $this->sut->readEntry('a'); }, '/No zip file is loaded/');

        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-0-files.zip');
        AssertUtils::throwsException(function() { $this->sut->readEntry('a'); }, '/Zip file is empty/');


        // Test ok values
        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-2-files.zip');
        $this->assertSame('file 1', $this->sut->readEntry('file 1.txt'));
        $this->assertSame('file 2', $this->sut->readEntry('file 2.txt'));

        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-files-and-folders.zip');
        $this->assertSame('', $this->sut->readEntry('folder 2/file 5.txt'));
        $this->assertSame('file 4', $this->sut->readEntry('folder 1/folder 3/file 4.txt'));

        $this->sut->loadPath(self::$basePath.DIRECTORY_SEPARATOR.'zip-with-binaryfile.zip');
        $this->assertSame(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'binaryfile.png'), $this->sut->readEntry('binaryfile.png'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->readEntry('file 11.txt'); }, '/zip entry: file 11.txt/');
        AssertUtils::throwsException(function() { $this->sut->readEntry('empty folder'); }, '/zip entry: empty folder/');
        AssertUtils::throwsException(function() { $this->sut->readEntry('empty folder/'); }, '/Trying to read an empty zip folder: empty folder\//');

        $this->sut->close();
    }
}