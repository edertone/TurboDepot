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
use stdClass;
use Throwable;
use org\turbocommons\src\main\php\managers\ValidationManager;
use org\turbocommons\src\main\php\utils\ArrayUtils;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbocommons\src\main\php\utils\NumericUtils;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbocommons\src\main\php\utils\ConversionUtils;


/**
 * FilesManager tests
 *
 * @return void
 */
class FilesManagerTest extends TestCase {


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
        $this->basePath = __DIR__.'/../../resources/managers/filesManager';

        $this->sut = new FilesManager();

        // Create a temporary folder
        $this->tempFolder = $this->sut->createTempDirectory('TurboDepot-FilesManagerTest');
        $this->assertTrue(strpos($this->tempFolder, 'TurboDepot-FilesManagerTest') !== false);
        $this->assertTrue($this->sut->isDirectoryEmpty($this->tempFolder));
        $this->assertFalse($this->sut->isFile($this->tempFolder));
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(): void{

        // Delete temporary folder
        $this->sut->deleteDirectory($this->tempFolder);

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
     * Helper method to create a dummy structure of folders with some parameters
     *
     * @param string $root Base directory where the structure will be created
     * @param int $folders Number of folders to create per depth level
     * @param int $depth Number of subfolders to create
     * @param string $fileBaseName Base name for each file to be created
     * @param int $filesPerFolder Number of files to create per each folder
     * @param string $filesContent Content to place inside each created file
     *
     * @return void
     */
    private function createDummyDirectoryStucture(string $root,
                                                  int $folders,
                                                  int $depth,
                                                  string $fileBaseName,
                                                  int $filesPerFolder,
                                                  string $filesContent){

        $s = DIRECTORY_SEPARATOR;

        // Create the structure of folders
        for ($i = 0; $i < $folders; $i++) {

            $pathToCreate = $root;

            for ($j = 0; $j < $depth; $j++) {

                $pathToCreate = $pathToCreate.$s.'folder-'.$i.'-'.$j;

                $this->sut->createDirectory($pathToCreate, true);

                for ($k = 0; $k < $filesPerFolder; $k++) {

                    $fileToCreate = $pathToCreate.$s.$fileBaseName.'-'.$i.'-'.$j.'-'.$k.'.txt';

                    $this->sut->saveFile($fileToCreate, $filesContent);

                    $this->assertTrue($this->sut->isFile($fileToCreate));
                }
            }

            $this->assertTrue($this->sut->isDirectory($pathToCreate));
        }
    }


    /**
     * testConstruct
     *
     * @return void
     */
    public function testConstruct(){

        // Test empty values
        try {
            $this->sut = new FilesManager(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/rootPath must be a string/', $e->getMessage());
        }

        $this->assertSame(DIRECTORY_SEPARATOR, (new FilesManager())->dirSep());
        $this->assertSame(DIRECTORY_SEPARATOR, (new FilesManager(''))->dirSep());

        try {
            $this->sut = new FilesManager('              ');
            $this->exceptionMessage = '"             " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Specified rootPath does not exist/', $e->getMessage());
        }

        try {
            $this->sut = new FilesManager(new stdClass());
            $this->exceptionMessage = 'stdclass did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/rootPath must be a string/', $e->getMessage());
        }

        // Test ok values
        $this->assertSame(DIRECTORY_SEPARATOR, (new FilesManager($this->tempFolder))->dirSep());

        // Test wrong values
        try {
            $this->sut = new FilesManager('nonexistant path');
            $this->exceptionMessage = 'nonexistant path did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Specified rootPath does not exist: nonexistant path/', $e->getMessage());
        }

        // Test exceptions
        // Already tested
    }


    /**
     * testDirSep
     *
     * @return void
     */
    public function testDirSep(){

        $this->assertTrue($this->sut->dirSep() === DIRECTORY_SEPARATOR);
    }


    /**
     * testIsPathAbsolute
     *
     * @return void
     */
    public function testIsPathAbsolute(){

        // Test empty values
        try {
            $this->sut->isPathAbsolute(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/path must be a string/', $e->getMessage());
        }

        $this->assertFalse($this->sut->isPathAbsolute(''));
        $this->assertFalse($this->sut->isPathAbsolute('            '));

        try {
            $this->sut->isPathAbsolute(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->isPathAbsolute([]);
            $this->exceptionMessage = '[] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->isPathAbsolute(new stdClass());
            $this->exceptionMessage = 'new stdClass() did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/path must be a string/', $e->getMessage());
        }

        // Test ok values

        // Windows absolute paths
        $this->assertTrue($this->sut->isPathAbsolute('\\'));
        $this->assertTrue($this->sut->isPathAbsolute('\\\\'));
        $this->assertTrue($this->sut->isPathAbsolute('c:'));
        $this->assertTrue($this->sut->isPathAbsolute('d:'));
        $this->assertTrue($this->sut->isPathAbsolute('f:'));
        $this->assertTrue($this->sut->isPathAbsolute('c:\\'));
        $this->assertTrue($this->sut->isPathAbsolute('d:\\'));
        $this->assertTrue($this->sut->isPathAbsolute('f:\\'));
        $this->assertTrue($this->sut->isPathAbsolute('C:\\temp\\'));
        $this->assertTrue($this->sut->isPathAbsolute('C:\\Documents\\Newsletters\\Summer2018.pdf'));
        $this->assertTrue($this->sut->isPathAbsolute('\\Program Files\\Custom Utilities\\StringFinder.exe'));
        $this->assertTrue($this->sut->isPathAbsolute('C:\\Projects\\apilibrary\\apilibrary.sln'));
        $this->assertTrue($this->sut->isPathAbsolute('\\\\Server2\\Share\\Test\\Foo.txt'));
        $this->assertTrue($this->sut->isPathAbsolute('\\\\system07\\C$\\'));
        $this->assertTrue($this->sut->isPathAbsolute('\\var'));
        $this->assertTrue($this->sut->isPathAbsolute('\\utilities\\dir'));
        $this->assertTrue($this->sut->isPathAbsolute('/'));
        $this->assertTrue($this->sut->isPathAbsolute('//'));
        $this->assertTrue($this->sut->isPathAbsolute('c:/'));
        $this->assertTrue($this->sut->isPathAbsolute('d:/'));
        $this->assertTrue($this->sut->isPathAbsolute('C:/temp/'));
        $this->assertTrue($this->sut->isPathAbsolute('C:/Documents/Newsletters/Summer2018.pdf'));
        $this->assertTrue($this->sut->isPathAbsolute('/Program Files/Custom Utilities/StringFinder.exe'));
        $this->assertTrue($this->sut->isPathAbsolute('C:/Projects/apilibrary/apilibrary.sln'));
        $this->assertTrue($this->sut->isPathAbsolute('//Server2/Share/Test/Foo.txt'));
        $this->assertTrue($this->sut->isPathAbsolute('//system07/C$/'));
        $this->assertTrue($this->sut->isPathAbsolute('/var'));
        $this->assertTrue($this->sut->isPathAbsolute('/utilities/dir'));

        // Windows relative paths
        $this->assertFalse($this->sut->isPathAbsolute(''));
        $this->assertFalse($this->sut->isPathAbsolute('2018\\January.xlsx'));
        $this->assertFalse($this->sut->isPathAbsolute('..\\Publications\\TravelBrochure.pdf'));
        $this->assertFalse($this->sut->isPathAbsolute('C:Projects\\apilibrary\\apilibrary.sln'));
        $this->assertFalse($this->sut->isPathAbsolute('var'));
        $this->assertFalse($this->sut->isPathAbsolute('utilities\\dir'));
        $this->assertFalse($this->sut->isPathAbsolute('..\\Landuse'));
        $this->assertFalse($this->sut->isPathAbsolute('..\\..\\Data\\Final\\Infrastructure.gdb\\Streets'));
        $this->assertFalse($this->sut->isPathAbsolute('2018/January.xlsx'));
        $this->assertFalse($this->sut->isPathAbsolute('../Publications/TravelBrochure.pdf'));
        $this->assertFalse($this->sut->isPathAbsolute('C:Projects/apilibrary/apilibrary.sln'));
        $this->assertFalse($this->sut->isPathAbsolute('utilities/dir'));
        $this->assertFalse($this->sut->isPathAbsolute('../Landuse'));
        $this->assertFalse($this->sut->isPathAbsolute('../../Data/Final/Infrastructure.gdb/Streets'));

        // Linux absolute paths
        $this->assertTrue($this->sut->isPathAbsolute('/'));
        $this->assertTrue($this->sut->isPathAbsolute('//'));
        $this->assertTrue($this->sut->isPathAbsolute('/var'));
        $this->assertTrue($this->sut->isPathAbsolute('/utilities/dir'));
        $this->assertTrue($this->sut->isPathAbsolute('/export/home/heden/rhost'));

        // Linux relative paths
        $this->assertFalse($this->sut->isPathAbsolute(''));
        $this->assertFalse($this->sut->isPathAbsolute('2018/January.xlsx'));
        $this->assertFalse($this->sut->isPathAbsolute('../Publications/TravelBrochure.pdf'));
        $this->assertFalse($this->sut->isPathAbsolute('Projects/apilibrary/apilibrary.sln'));
        $this->assertFalse($this->sut->isPathAbsolute('var'));

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->isPathAbsolute(123253565);
            $this->exceptionMessage = '123253565 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->isPathAbsolute([1,2,3,4,5,7]);
            $this->exceptionMessage = '[1,2,3,4,5,7] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/path must be a string/', $e->getMessage());
        }
    }


    /**
     * testIsFile
     *
     * @return void
     */
    public function testIsFile(){

        // Test empty values
        try {
            $this->sut->isFile(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isFile(new stdClass());
            $this->exceptionMessage = 'new stdClass() did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isFile(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        $this->assertFalse($this->sut->isFile(''));
        $this->assertFalse($this->sut->isFile('          '));
        $this->assertFalse($this->sut->isFile("\n\n\n"));

        // Test ok values
        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'File.txt', '');
        $this->assertTrue($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'File.txt'));
        $this->sut->deleteFile($this->tempFolder.DIRECTORY_SEPARATOR.'File.txt');
        $this->assertFalse($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'File.txt'));

        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'File2.txt', 'Hello baby');
        $this->assertTrue($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'File2.txt'));

        $sut2 = new FilesManager($this->tempFolder);
        $this->assertTrue($sut2->isFile('File2.txt'));

        $this->sut->deleteFile($this->tempFolder.DIRECTORY_SEPARATOR.'File2.txt');
        $this->assertFalse($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'File2.txt'));

        $this->assertFalse($sut2->isFile('File2.txt'));

        // Test wrong values
        $this->assertFalse($this->sut->isFile($this->tempFolder));
        $this->assertFalse($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'asdfsdf.txt353455'));
        $this->assertFalse($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'asdfsdf.txt'));
        $this->assertFalse($this->sut->isFile('49568456'));
        $this->assertFalse($this->sut->isFile('http://www.adkgadsifi.com/ieriteroter3453458852t.pdf'));
        $this->assertFalse($this->sut->isFile('http://www.google.com'));
        $this->assertFalse($this->sut->isFile('https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js'));
        $this->assertFalse($this->sut->isFile('http://www.facebook.com'));

        // Test exceptions
        // Not necessary
    }


    /**
     * testIsFileEqualTo
     *
     * @return void
     */
    public function testIsFileEqualTo(){

        // Test empty values
        try {
            $this->sut->isFileEqualTo(null, null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isFileEqualTo('', '');
            $this->exceptionMessage = 'new stdClass() did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isFileEqualTo(0, 0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test ok values

        // Create some files
        $file1 = $this->tempFolder.DIRECTORY_SEPARATOR.'file1';
        $file2 = $this->tempFolder.DIRECTORY_SEPARATOR.'file2';
        $file3 = $this->tempFolder.DIRECTORY_SEPARATOR.'file3';
        $file4 = $this->tempFolder.DIRECTORY_SEPARATOR.'file4';
        $this->assertTrue($this->sut->saveFile($file1, 'file 1'));
        $this->assertTrue($this->sut->saveFile($file2, 'file 2'));
        $this->assertTrue($this->sut->saveFile($file3, 'file 3'));
        $this->assertTrue($this->sut->saveFile($file4, 'file 1'));

        $this->assertTrue($this->sut->isFileEqualTo($file1, $file1));
        $this->assertFalse($this->sut->isFileEqualTo($file1, $file2));
        $this->assertFalse($this->sut->isFileEqualTo($file2, $file3));
        $this->assertFalse($this->sut->isFileEqualTo($file3, $file4));
        $this->assertTrue($this->sut->isFileEqualTo($file1, $file4));

        $sut2 = new FilesManager($this->tempFolder);

        $this->assertTrue($sut2->isFileEqualTo('file1', 'file1'));
        $this->assertFalse($sut2->isFileEqualTo('file1', 'file2'));

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->isFileEqualTo($file3, $this->tempFolder);
            $this->exceptionMessage = 'tempFolder did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isFileEqualTo($file3, 'etrtert');
            $this->exceptionMessage = 'etrtert did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isFileEqualTo('http://www.google.com', $file3);
            $this->exceptionMessage = 'http://www.google.com did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }
    }


    /**
     * testIsDirectory
     *
     * @return void
     */
    public function testIsDirectory(){

        // Test empty values
        try {
            $this->sut->isDirectory(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectory(new stdClass());
            $this->exceptionMessage = 'new stdClass() did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectory(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        $this->assertFalse($this->sut->isDirectory(''));
        $this->assertFalse($this->sut->isDirectory('          '));
        $this->assertFalse($this->sut->isDirectory("\n\n\n"));

        // Test ok values
        $this->assertTrue($this->sut->isDirectory($this->tempFolder));

        $averageDirectory = $this->tempFolder.DIRECTORY_SEPARATOR.'some folder';
        $this->sut->createDirectory($averageDirectory, true);
        $this->assertTrue($this->sut->isDirectory($averageDirectory));
        $this->sut->deleteDirectory($averageDirectory);
        $this->assertFalse($this->sut->isDirectory($averageDirectory));

        $recursiveDirectory = $this->tempFolder.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'b'.DIRECTORY_SEPARATOR.'c';
        $this->sut->createDirectory($recursiveDirectory, true);
        $this->assertTrue($this->sut->isDirectory($recursiveDirectory));
        $this->sut->deleteDirectory($recursiveDirectory);
        $this->assertFalse($this->sut->isDirectory($recursiveDirectory));
        $this->assertTrue($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'b'));

        $sut2 = new FilesManager($this->tempFolder);
        $this->assertTrue($sut2->isDirectory(''));
        $this->assertTrue($sut2->isDirectory('a'.DIRECTORY_SEPARATOR.'b'));

        // Test wrong values
        $this->assertFalse($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'asdfsdf.txt353455'));
        $this->assertFalse($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'asdfsdf.txt'));
        $this->assertFalse($this->sut->isDirectory('49568456'));
        $this->assertFalse($this->sut->isDirectory('http://www.adkgadsifi.com/ieriteroter3453458852t.pdf'));
        $this->assertFalse($this->sut->isDirectory('http://www.google.com'));
        $this->assertFalse($this->sut->isDirectory('https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js'));
        $this->assertFalse($this->sut->isDirectory('http://www.facebook.com'));

        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'File.txt', '');
        $this->assertFalse($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'File.txt'));

        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'File2.txt', 'Hello baby');
        $this->assertFalse($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'File2.txt'));

        $this->assertFalse($sut2->isDirectory('nonexistantdir'));
    }


    /**
     * testIsDirectoryEqualTo
     *
     * @return void
     */
    public function testIsDirectoryEqualTo(){

        // Test empty values
        try {
            $this->sut->isDirectoryEqualTo(null, null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEqualTo(new stdClass(), new stdClass());
            $this->exceptionMessage = 'new stdClass() did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEqualTo(0, 0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEqualTo('', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEqualTo("\n\n\n", "\n\n\n");
            $this->exceptionMessage = '"\n\n\n" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test ok values
        $this->assertTrue($this->sut->isDirectoryEqualTo($this->tempFolder, $this->tempFolder));

        // Create some folder structures
        $dir1 = $this->tempFolder.DIRECTORY_SEPARATOR.'dir1';
        $dir2 = $this->tempFolder.DIRECTORY_SEPARATOR.'dir2';
        $dir3 = $this->tempFolder.DIRECTORY_SEPARATOR.'dir3';
        $dir4 = $this->tempFolder.DIRECTORY_SEPARATOR.'dir4';
        $this->createDummyDirectoryStucture($dir1, 4, 4, 'somefile', 5, 'file content');
        $this->createDummyDirectoryStucture($dir2, 4, 4, 'somefile', 5, 'file content');
        $this->createDummyDirectoryStucture($dir3, 4, 4, 'somefile', 5, 'file conten');
        $this->createDummyDirectoryStucture($dir4, 8, 2, 'somefile', 2, 'f');

        $this->assertTrue($this->sut->isDirectoryEqualTo($dir1, $dir1));
        $this->assertTrue($this->sut->isDirectoryEqualTo($dir1, $dir2));
        $this->assertTrue($this->sut->isDirectoryEqualTo($dir2, $dir2));
        $this->assertTrue($this->sut->isDirectoryEqualTo($dir3, $dir3));
        $this->assertTrue($this->sut->isDirectoryEqualTo($dir4, $dir4));

        $this->assertFalse($this->sut->isDirectoryEqualTo($dir1, $dir3));
        $this->assertFalse($this->sut->isDirectoryEqualTo($dir2, $dir3));
        $this->assertFalse($this->sut->isDirectoryEqualTo($dir1, $dir4));
        $this->assertFalse($this->sut->isDirectoryEqualTo($dir3, $dir4));

        $this->sut->deleteFiles($this->sut->findDirectoryItems($dir1, '/^somefile-0-0-0\.txt$/', 'absolute'));
        $this->assertFalse($this->sut->isDirectoryEqualTo($dir1, $dir2));

        $sut2 = new FilesManager($this->tempFolder);

        $this->assertTrue($sut2->isDirectoryEqualTo('dir1', 'dir1'));
        $this->assertTrue($sut2->isDirectoryEqualTo('dir1', $dir1));
        $this->assertFalse($sut2->isDirectoryEqualTo('dir1', 'dir3'));

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->isDirectoryEqualTo($this->tempFolder, $this->tempFolder.DIRECTORY_SEPARATOR.'asdfwer');
            $this->exceptionMessage = 'asdfwer did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEqualTo($this->tempFolder, 'etrtert');
            $this->exceptionMessage = 'etrtert did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEqualTo($this->tempFolder, 'http://www.google.com');
            $this->exceptionMessage = 'http://www.google.com did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }
    }


    /**
     * testIsDirectoryEmpty
     *
     * @return void
     */
    public function testIsDirectoryEmpty(){

        // Test empty values
        try {
            $this->sut->isDirectoryEmpty(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEmpty(new stdClass());
            $this->exceptionMessage = 'new stdClass() did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEmpty(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEmpty('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEmpty('          ');
            $this->exceptionMessage = '"         " did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEmpty("\n\n\n");
            $this->exceptionMessage = '"\n\n\n" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test ok values
        $this->assertTrue($this->sut->isDirectoryEmpty($this->tempFolder));

        $sut2 = new FilesManager($this->tempFolder);
        $this->assertTrue($sut2->isDirectoryEmpty(''));

        $averageDirectory = $this->tempFolder.DIRECTORY_SEPARATOR.'some folder';
        $this->sut->createDirectory($averageDirectory);
        $this->assertTrue($this->sut->isDirectoryEmpty($averageDirectory));
        $this->assertTrue($sut2->isDirectoryEmpty('some folder'));
        $this->sut->saveFile($averageDirectory.DIRECTORY_SEPARATOR.'File.txt', 'Hello baby');
        $this->assertFalse($this->sut->isDirectoryEmpty($averageDirectory));
        $this->assertFalse($sut2->isDirectoryEmpty('some folder'));

        // Test wrong values
        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'File.txt', 'Hello baby');
        $this->assertFalse($this->sut->isDirectoryEmpty($this->tempFolder));

        $this->assertFalse($sut2->isDirectoryEmpty(''));

        // Test exceptions
        try {
            $this->sut->isDirectoryEmpty($this->tempFolder.DIRECTORY_SEPARATOR.'asdfwer');
            $this->exceptionMessage = 'asdfwer did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEmpty('etrtert');
            $this->exceptionMessage = 'etrtert did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->isDirectoryEmpty('http://www.google.com');
            $this->exceptionMessage = 'http://www.google.com did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }
    }


    /**
     * testCountDirectoryItems
     *
     * @return void
     */
    public function testCountDirectoryItems(){

        // Test empty values
        try {
            $this->sut->countDirectoryItems(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->countDirectoryItems(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path must be a string/', $e->getMessage());
        }

        // Test ok values
        $this->assertSame(2, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'files', 0));
        $this->assertSame(3, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'files', 1));
        $this->assertSame(4, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'files', 2));
        $this->assertSame(4, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'files'));

        $this->assertSame(2, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'folders', 0));
        $this->assertSame(3, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'folders', 1));
        $this->assertSame(3, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'folders', 2));
        $this->assertSame(3, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'folders'));

        $this->assertSame(4, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'both', 0));
        $this->assertSame(6, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'both', 1));
        $this->assertSame(7, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'both', 2));
        $this->assertSame(7, $this->sut->countDirectoryItems($this->basePath.'/countDirectoryItems/test-1', 'both'));

        // Test wrong values
        // Not necessary

        // Test exceptions
        // Not necessary
    }


    /**
     * testFindDirectoryItems
     *
     * @return void
     */
    public function testFindDirectoryItems(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->findDirectoryItems(null); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function(){ $this->sut->findDirectoryItems(0); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function(){ $this->sut->findDirectoryItems(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function(){ $this->sut->findDirectoryItems('       '); }, '/Too few arguments to function/');

        // Test ok values
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->findDirectoryItems($this->tempFolder, '/file/'), []));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->findDirectoryItems($this->tempFolder, '/.*/'), []));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->findDirectoryItems($this->tempFolder, '/^name$/'), []));

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->findDirectoryItems($this->tempFolder, '/file/', 'relative', 'files'), []));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->findDirectoryItems($this->tempFolder, '/.*/', 'relative', 'files'), []));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->findDirectoryItems($this->tempFolder, '/^name$/', 'relative', 'files'), []));

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->findDirectoryItems($this->tempFolder, '/file/', 'relative', 'folders'), []));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->findDirectoryItems($this->tempFolder, '/.*/', 'relative', 'folders'), []));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->findDirectoryItems($this->tempFolder, '/^name$/', 'relative', 'folders'), []));

        // Create a structure of folders and files
        $this->createDummyDirectoryStucture($this->tempFolder, 4, 4, 'somefile', 5, 'file content');

        // Test resultFormat = 'name'

        // Test finding all *.txt files on the folder
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name')));
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name', 'files')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name', 'folders')));

        // Test finding all files or folders on the 1st folder depth
        $this->assertSame(4, count($this->sut->findDirectoryItems($this->tempFolder, '/.*$/', 'name', 'both', 0)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*$/', 'name', 'files', 0)));
        $this->assertSame(4, count($this->sut->findDirectoryItems($this->tempFolder, '/.*$/', 'name', 'folders', 0)));

        // Test finding all *.txt files on the 1st 2d and 3d folder depth
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name', 'both', 0)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name', 'files', 0)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name', 'folders', 0)));
        $this->assertSame(20, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name', 'both', 1)));
        $this->assertSame(20, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name', 'files', 1)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name', 'folders', 1)));
        $this->assertSame(40, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name', 'both', 2)));
        $this->assertSame(40, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name', 'files', 2)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name', 'folders', 2)));

        // Test finding all files starting with somefile on the folder
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/^somefile.*/', 'name')));
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/^somefile.*/', 'name', 'files')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^somefile.*/', 'name', 'folders')));

        // Test finding all files starting with samefile on the folder
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^samefile.*/', 'name')));

        // Test finding all files with an exact name on the folder
        $this->assertSame(['somefile-0-0-2.txt'], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-0-0-2.txt$/', 'name'));
        $this->assertSame(['somefile-0-1-2.txt'], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-0-1-2.txt$/', 'name'));
        $this->assertSame(['somefile-2-2-2.txt'], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-2-2-2.txt$/', 'name'));

        // Test finding all files named *-4.txt on the folder
        $this->assertSame(16, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'name')));
        $this->assertSame(16, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'name', 'files')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'name', 'folders')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'name', 'both', 0)));

        // Test finding all folders with an exact name on the folder
        $this->assertSame(['folder-3-3'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-3-3$/', 'name'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-3-3$/', 'name', 'files'));
        $this->assertSame(['folder-3-3'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-3-3$/', 'name', 'folders'));
        $this->assertSame(['folder-1-2'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name', 'files'));
        $this->assertSame(['folder-1-2'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name', 'folders'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name', 'both', 0));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name', 'files', 0));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name', 'folders', 0));

        // Test finding all folders ending with 0-3 or 0-2
        $this->assertSame(['folder-0-2', 'folder-0-3'], $this->sut->findDirectoryItems($this->tempFolder, '/^.*(0-3|0-2)$/i', 'name'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^.*(0-3|0-2)$/i', 'name', 'files'));
        $this->assertSame(['folder-0-2', 'folder-0-3'], $this->sut->findDirectoryItems($this->tempFolder, '/^.*(0-3|0-2)$/i', 'name', 'folders'));

        // Create a folder with some dummy image files
        $temp2Folder = $this->sut->createTempDirectory('TurboDepot-FilesManagerTest-2');

        for ($k = 0; $k < 2; $k++) {

            $this->sut->saveFile($temp2Folder.DIRECTORY_SEPARATOR.$k.'.jpg', 'fake image data');
            $this->sut->saveFile($temp2Folder.DIRECTORY_SEPARATOR.$k.'.png', 'fake image data');
            $this->sut->saveFile($temp2Folder.DIRECTORY_SEPARATOR.$k.'.gif', 'fake image data');
        }

        // Test finding all files ending with .jpg or .png
        $this->assertSame(['0.jpg', '0.png', '1.jpg', '1.png'], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name'));
        $this->assertSame(['0.jpg', '0.png', '1.jpg', '1.png'], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name', 'files'));
        $this->assertSame([], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name', 'folders'));

        // Test finding all files that NOT end with .jpg
        $this->assertSame(['0.gif', '0.png', '1.gif', '1.png'], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg)$)/i', 'name'));
        $this->assertSame(['0.gif', '0.png', '1.gif', '1.png'], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg)$)/i', 'name', 'files'));
        $this->assertSame([], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg)$)/i', 'name', 'folders'));

        // Test finding all files that NOT end with .jpg and NOT end with .png
        $this->assertSame(['0.gif', '1.gif'], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg|png)$)/i', 'name'));
        $this->assertSame(['0.gif', '1.gif'], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg|png)$)/i', 'name', 'files'));
        $this->assertSame([], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg|png)$)/i', 'name', 'folders'));

        // Test finding all files that NOT end with .jpg and NOT end with .png and NOT end with gif
        $this->assertSame($this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg|png|gif)$)/i', 'name'), []);

        // Test some exclusion patterns
        $this->assertSame(['0.png', '1.png'], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name', 'both', -1, '/.jpg$/i'));
        $this->assertSame([], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name', 'both', -1, '/g$/i'));
        $this->assertSame(['1.jpg', '1.png'], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name', 'both', -1, '/0/i'));

        // Test resultFormat = 'name-noext'

        // Test finding all *.txt files on the folder
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext')));
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext', 'files')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext', 'folders')));

        // Test finding all files or folders on the 1st folder depth
        $this->assertSame(4, count($this->sut->findDirectoryItems($this->tempFolder, '/.*$/', 'name-noext', 'both', 0)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*$/', 'name-noext', 'files', 0)));
        $this->assertSame(4, count($this->sut->findDirectoryItems($this->tempFolder, '/.*$/', 'name-noext', 'folders', 0)));

        // Test finding all *.txt files on the 1st 2d and 3d folder depth
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext', 'both', 0)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext', 'files', 0)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext', 'folders', 0)));
        $this->assertSame(20, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext', 'both', 1)));
        $this->assertSame(20, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext', 'files', 1)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext', 'folders', 1)));
        $this->assertSame(40, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext', 'both', 2)));
        $this->assertSame(40, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext', 'files', 2)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'name-noext', 'folders', 2)));

        // Test finding all files starting with somefile on the folder
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/^somefile.*/', 'name-noext')));
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/^somefile.*/', 'name-noext', 'files')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^somefile.*/', 'name-noext', 'folders')));

        // Test finding all files starting with samefile on the folder
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^samefile.*/', 'name-noext')));

        // Test finding all files with an exact name on the folder
        $this->assertSame(['somefile-0-0-2'], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-0-0-2.txt$/', 'name-noext'));
        $this->assertSame(['somefile-0-1-2'], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-0-1-2.txt$/', 'name-noext'));
        $this->assertSame(['somefile-2-2-2'], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-2-2-2.txt$/', 'name-noext'));

        // Test finding all files named *-4.txt on the folder
        $this->assertSame(16, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'name-noext')));
        $this->assertSame(16, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'name-noext', 'files')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'name-noext', 'folders')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'name-noext', 'both', 0)));

        // Test finding all folders with an exact name on the folder
        $this->assertSame(['folder-3-3'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-3-3$/', 'name-noext'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-3-3$/', 'name-noext', 'files'));
        $this->assertSame(['folder-3-3'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-3-3$/', 'name-noext', 'folders'));
        $this->assertSame(['folder-1-2'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name-noext'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name-noext', 'files'));
        $this->assertSame(['folder-1-2'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name-noext', 'folders'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name-noext', 'both', 0));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name-noext', 'files', 0));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'name-noext', 'folders', 0));

        // Test finding all folders ending with 0-3 or 0-2
        $this->assertSame(['folder-0-2', 'folder-0-3'], $this->sut->findDirectoryItems($this->tempFolder, '/^.*(0-3|0-2)$/i', 'name-noext'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^.*(0-3|0-2)$/i', 'name-noext', 'files'));
        $this->assertSame(['folder-0-2', 'folder-0-3'], $this->sut->findDirectoryItems($this->tempFolder, '/^.*(0-3|0-2)$/i', 'name-noext', 'folders'));

        // Create a folder with some dummy image files
        $temp2Folder = $this->sut->createTempDirectory('TurboDepot-FilesManagerTest-2');

        for ($k = 0; $k < 2; $k++) {

            $this->sut->saveFile($temp2Folder.DIRECTORY_SEPARATOR.$k.'.jpg', 'fake image data');
            $this->sut->saveFile($temp2Folder.DIRECTORY_SEPARATOR.$k.'.png', 'fake image data');
            $this->sut->saveFile($temp2Folder.DIRECTORY_SEPARATOR.$k.'.gif', 'fake image data');
        }

        // Test finding all files ending with .jpg or .png
        $this->assertSame(['0', '0', '1', '1'], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name-noext'));
        $this->assertSame(['0', '0', '1', '1'], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name-noext', 'files'));
        $this->assertSame([], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name-noext', 'folders'));

        // Test finding all files that NOT end with .jpg
        $this->assertSame(['0', '0', '1', '1'], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg)$)/i', 'name-noext'));
        $this->assertSame(['0', '0', '1', '1'], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg)$)/i', 'name-noext', 'files'));
        $this->assertSame([], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg)$)/i', 'name-noext', 'folders'));

        // Test finding all files that NOT end with .jpg and NOT end with .png
        $this->assertSame(['0', '1'], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg|png)$)/i', 'name-noext'));
        $this->assertSame(['0', '1'], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg|png)$)/i', 'name-noext', 'files'));
        $this->assertSame([], $this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg|png)$)/i', 'name-noext', 'folders'));

        // Test finding all files that NOT end with .jpg and NOT end with .png and NOT end with gif
        $this->assertSame($this->sut->findDirectoryItems($temp2Folder, '/^(?!.*\.(jpg|png|gif)$)/i', 'name-noext'), []);

        // Test some exclusion patterns
        $this->assertSame(['0', '1'], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name-noext', 'both', -1, '/.jpg$/i'));
        $this->assertSame([], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name-noext', 'both', -1, '/g$/i'));
        $this->assertSame(['1', '1'], $this->sut->findDirectoryItems($temp2Folder, '/^.*\.(jpg|png)$/i', 'name-noext', 'both', -1, '/0/i'));

        // Test resultFormat = 'relative'

        // Test finding all *.txt files on the folder
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'relative')));
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'relative', 'files')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'relative', 'folders')));

        // Test finding all files or folders on the 1st folder depth
        $this->assertSame(4, count($this->sut->findDirectoryItems($this->tempFolder, '/.*$/', 'relative', 'both', 0)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*$/', 'relative', 'files', 0)));
        $this->assertSame(4, count($this->sut->findDirectoryItems($this->tempFolder, '/.*$/', 'relative', 'folders', 0)));

        // Test finding all *.txt files on the 1st 2d and 3d folder depth
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'relative', 'both', 0)));
        $this->assertSame(20, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'relative', 'both', 1)));
        $this->assertSame(40, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'relative', 'both', 2)));

        // Test finding all files starting with somefile on the folder
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/^somefile.*/', 'relative')));
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/^somefile.*/', 'relative', 'files')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^somefile.*/', 'relative', 'folders')));

        // Test finding all files starting with samefile on the folder
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^samefile.*/', 'relative')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^samefile.*/', 'relative', 'files')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^samefile.*/', 'relative', 'folders')));

        // Test finding all files named somefile-2.txt on the folder
        $this->assertSame($this->sut->findDirectoryItems($this->tempFolder, '/^somefile-0-0-2.txt$/', 'relative'), ['folder-0-0'.DIRECTORY_SEPARATOR.'somefile-0-0-2.txt']);
        $this->assertSame($this->sut->findDirectoryItems($this->tempFolder, '/^somefile-0-1-2.txt$/', 'relative'), ['folder-0-0'.DIRECTORY_SEPARATOR.'folder-0-1'.DIRECTORY_SEPARATOR.'somefile-0-1-2.txt']);
        $this->assertSame($this->sut->findDirectoryItems($this->tempFolder, '/^somefile-2-2-2.txt$/', 'relative'), ['folder-2-0'.DIRECTORY_SEPARATOR.'folder-2-1'.DIRECTORY_SEPARATOR.'folder-2-2'.DIRECTORY_SEPARATOR.'somefile-2-2-2.txt']);

        // Test finding all files named *-4.txt on the folder
        $this->assertSame(16, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'relative')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'relative', 'both', 0)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'relative', 'files', 0)));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'relative', 'folders', 0)));

        // Test finding all folders named folder-3-3 on the folder
        $this->assertSame(['folder-3-0'.DIRECTORY_SEPARATOR.'folder-3-1'.DIRECTORY_SEPARATOR.'folder-3-2'.DIRECTORY_SEPARATOR.'folder-3-3'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-3-3$/', 'relative'));
        $this->assertSame(['folder-1-0'.DIRECTORY_SEPARATOR.'folder-1-1'.DIRECTORY_SEPARATOR.'folder-1-2'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'relative'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'relative', 'both', 0));

        // Test some exclusion patterns
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-3-3$/', 'relative', 'both', -1, '/-3-0/'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-3-3$/', 'relative', 'both', -1, '/folder-3-1/'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-3-3$/', 'relative', 'both', -1, '/folder-3-2/'));

        // Test resultFormat = 'absolute'

        // Test finding all *.txt files on the folder
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'absolute')));
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'absolute', 'files')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'absolute', 'folders')));

        // Test finding all files or folders on the 1st folder depth
        $this->assertSame(4, count($this->sut->findDirectoryItems($this->tempFolder, '/.*$/', 'absolute', 'both', 0)));

        // Test finding all *.txt files on the 1st 2d and 3d folder depth
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'absolute', 'both', 0)));
        $this->assertSame(20, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'absolute', 'both', 1)));
        $this->assertSame(40, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'absolute', 'both', 2)));

        // Test finding all files starting with somefile on the folder
        $this->assertSame(4 * 4 * 5, count($this->sut->findDirectoryItems($this->tempFolder, '/^somefile.*/', 'absolute')));

        // Test finding all files starting with samefile on the folder
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^samefile.*/', 'absolute')));

        // Test finding all files named somefile-2.txt on the folder
        $this->assertSame([$this->tempFolder.DIRECTORY_SEPARATOR.'folder-0-0'.DIRECTORY_SEPARATOR.'somefile-0-0-2.txt'], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-0-0-2.txt$/', 'absolute'));
        $this->assertSame([$this->tempFolder.DIRECTORY_SEPARATOR.'folder-0-0'.DIRECTORY_SEPARATOR.'folder-0-1'.DIRECTORY_SEPARATOR.'somefile-0-1-2.txt'], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-0-1-2.txt$/', 'absolute'));
        $this->assertSame([$this->tempFolder.DIRECTORY_SEPARATOR.'folder-2-0'.DIRECTORY_SEPARATOR.'folder-2-1'.DIRECTORY_SEPARATOR.'folder-2-2'.DIRECTORY_SEPARATOR.'somefile-2-2-2.txt'], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-2-2-2.txt$/', 'absolute'));

        // Test finding all files named *-4.txt on the folder
        $this->assertSame(16, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'absolute')));
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'absolute', 'both', 0)));

        // Test finding all folders named folder-3-3 on the folder
        $this->assertSame([$this->tempFolder.DIRECTORY_SEPARATOR.'folder-3-0'.DIRECTORY_SEPARATOR.'folder-3-1'.DIRECTORY_SEPARATOR.'folder-3-2'.DIRECTORY_SEPARATOR.'folder-3-3'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-3-3$/', 'absolute'));
        $this->assertSame([$this->tempFolder.DIRECTORY_SEPARATOR.'folder-1-0'.DIRECTORY_SEPARATOR.'folder-1-1'.DIRECTORY_SEPARATOR.'folder-1-2'], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'absolute'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^folder-1-2$/', 'absolute', 'both', 0));

        $sut2 = new FilesManager($this->tempFolder);
        $this->assertSame([$this->tempFolder.DIRECTORY_SEPARATOR.'folder-3-0'.DIRECTORY_SEPARATOR.'folder-3-1'.DIRECTORY_SEPARATOR.'folder-3-2'.DIRECTORY_SEPARATOR.'folder-3-3'], $sut2->findDirectoryItems('', '/^folder-3-3$/', 'absolute'));

        // Test some exclusion patterns
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-0-0-2.txt$/', 'absolute', 'both' , -1, '/folder-0-0/'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-0-0-2.txt$/', 'absolute', 'both' , -1, '/-FilesManagerTest/', 'name'));
        $this->assertSame([], $this->sut->findDirectoryItems($this->tempFolder, '/^somefile-0-0-2.txt$/', 'absolute', 'both' , -1, '/-FilesManagerTest/', 'absolute'));

        // test searchMode values
        $this->assertSame(1, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-0-1/', 'name', 'both', -1, '', 'name')));
        $this->assertSame(18, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-0-1/', 'name', 'both', -1, '', 'absolute')));
        $this->assertSame(1, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-0-2/', 'name', 'both', -1, '', 'name')));
        $this->assertSame(12, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-0-2/', 'name', 'both', -1, '', 'absolute')));
        $this->assertSame(12, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-0-1.folder-0-2/', 'name', 'both', -1, '', 'absolute')));
        $this->assertSame(1, count($this->sut->findDirectoryItems($this->tempFolder, '/somefile-0-3-1\.txt/', 'name', 'both', -1, '', 'name')));
        $this->assertSame(1, count($this->sut->findDirectoryItems($this->tempFolder, '/somefile-0-3-1\.txt/', 'name', 'both', -1, '', 'absolute')));
        $this->assertSame(1, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-2-0/', 'name', 'both', -1, '', 'name')));
        $this->assertSame(24, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-2-0/', 'name', 'both', -1, '', 'absolute')));
        $this->assertSame(20, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-2-0/', 'name', 'files', -1, '', 'absolute')));
        $this->assertSame(4, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-2-0/', 'name', 'folders', -1, '', 'absolute')));
        $this->assertSame(1, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-0-1(\/|\\\\)folder-0-2(\/|\\\\)folder-0-3/', 'name', 'folders', -1, '', 'absolute')));
        $this->assertSame(5, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-0-1(\/|\\\\)folder-0-2(\/|\\\\)folder-0-3/', 'name', 'files', -1, '', 'absolute')));
        $this->assertSame(6, count($this->sut->findDirectoryItems($this->tempFolder, '/folder-0-1(\/|\\\\)folder-0-2(\/|\\\\)folder-0-3/', 'name', 'both', -1, '', 'absolute')));

        // Test wrong values
        $this->assertSame(0, count($this->sut->findDirectoryItems($this->tempFolder, '/.*\.txt$/', 'noformat', 'both', 0)));

        // Test exceptions
        AssertUtils::throwsException(function(){ $this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'noformat'); }, '/Invalid returnFormat: noformat/');
        AssertUtils::throwsException(function(){ $this->sut->findDirectoryItems($this->tempFolder, '/folder-0-1/', 'x', 'both', -1, '', 'absolute'); }, '/Invalid returnFormat: x/');
        // Not necessary
    }


    /**
     * testFindUniqueDirectoryName
     *
     * @return void
     */
    public function testFindUniqueDirectoryName(){

        // Test empty values
        try {
            $this->sut->findUniqueDirectoryName(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->findUniqueDirectoryName('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist/', $e->getMessage());
        }

        try {
            $this->sut->findUniqueDirectoryName(new stdClass());
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->findUniqueDirectoryName('           ');
            $this->exceptionMessage = '"          " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist/', $e->getMessage());
        }

        $this->assertSame('1', $this->sut->findUniqueDirectoryName($this->tempFolder, ''));
        $this->assertSame('1', $this->sut->findUniqueDirectoryName($this->tempFolder, '           '));

        // Test ok values
        // Test generated directory names for the created empty folder
        $this->assertSame('1', $this->sut->findUniqueDirectoryName($this->tempFolder));
        $this->assertSame('NewFolder', $this->sut->findUniqueDirectoryName($this->tempFolder, 'NewFolder'));
        $this->assertSame('NewFolder', $this->sut->findUniqueDirectoryName($this->tempFolder, 'NewFolder', '-'));
        $this->assertSame('NewFolder', $this->sut->findUniqueDirectoryName($this->tempFolder, 'NewFolder', '', '-', true));

        // Create some folders
        $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'1');
        $this->assertTrue($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'1'));
        $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'NewFolder');
        $this->assertTrue($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'NewFolder'));

        // Create a file that is named like a directory (without extension)
        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'2', 'test file');
        $this->assertTrue($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'2'));

        // Verify generated dir names when folders already exist at destination path
        $this->assertSame('3', $this->sut->findUniqueDirectoryName($this->tempFolder));
        $this->assertSame('NewFolder-1', $this->sut->findUniqueDirectoryName($this->tempFolder, 'NewFolder'));
        $this->assertSame('1-NewFolder', $this->sut->findUniqueDirectoryName($this->tempFolder, 'NewFolder', '', '-', true));
        $this->assertSame('NewFolder-copy-1', $this->sut->findUniqueDirectoryName($this->tempFolder, 'NewFolder', 'copy', '-', false));
        $this->assertSame('copy-1-NewFolder', $this->sut->findUniqueDirectoryName($this->tempFolder, 'NewFolder', 'copy', '-', true));

        // Create some more folders
        $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'3');
        $this->assertTrue($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'3'));
        $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'NewFolder-1');
        $this->assertTrue($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'NewFolder-1'));
        $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'NewFolder-copy-1');
        $this->assertTrue($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'NewFolder-copy-1'));

        // Verify generated names again
        $this->assertSame('4', $this->sut->findUniqueDirectoryName($this->tempFolder));
        $this->assertSame('NewFolder-2', $this->sut->findUniqueDirectoryName($this->tempFolder, 'NewFolder'));
        $this->assertSame('1-NewFolder', $this->sut->findUniqueDirectoryName($this->tempFolder, 'NewFolder', '', '-', true));
        $this->assertSame('NewFolder-copy-2', $this->sut->findUniqueDirectoryName($this->tempFolder, 'NewFolder', 'copy', '-', false));
        $this->assertSame('copy-1-NewFolder', $this->sut->findUniqueDirectoryName($this->tempFolder, 'NewFolder', 'copy', '-', true));

        $sut2 = new FilesManager($this->tempFolder);

        $this->assertSame('copy-1-NewFolder', $sut2->findUniqueDirectoryName('', 'NewFolder', 'copy', '-', true));
        $this->assertSame('invalid**chars', $sut2->findUniqueDirectoryName('', 'invalid**chars'));

        // Test wrong values
        try {
            $this->sut->findUniqueDirectoryName('invalid??chars');
            $this->exceptionMessage = 'invalid??chars did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist: invalid/', $e->getMessage());
        }

        // Test exceptions
        // not necessary
    }


    /**
     * testFindUniqueFileName
     *
     * @return void
     */
    public function testFindUniqueFileName(){

        // Test empty values
        try {
            $this->sut->findUniqueFileName(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->findUniqueFileName('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->findUniqueFileName(new stdClass());
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->findUniqueFileName('           ');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test ok values
        // Test generated file names for the created empty folder
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder) == '1', 'error '.$this->sut->findUniqueFileName($this->tempFolder));
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder, 'NewFile.txt') == 'NewFile.txt');
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder, 'NewFile.txt', '-') == 'NewFile.txt');
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder, 'NewFile.txt', '-', true) == 'NewFile.txt');

        // Create some files
        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'1', 'hello baby');
        $this->assertTrue($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'1'));
        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'NewFile.txt', 'hello baby');
        $this->assertTrue($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'NewFile.txt'));

        // Create a folder that is named like a possible file
        $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'2');
        $this->assertTrue($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'2'));

        // Verify generated file names when files already exist at destination path
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder) == '3');
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder, 'NewFile.txt') == 'NewFile-1.txt');
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder, 'NewFile.txt', '', '-', true) == '1-NewFile.txt');
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder, 'NewFile.txt', 'copy', '-', false) == 'NewFile-copy-1.txt');
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder, 'NewFile.txt', 'copy', '-', true) == 'copy-1-NewFile.txt');

        // Create some more files
        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'3', 'hello baby');
        $this->assertTrue($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'3'));
        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'NewFile-1.txt', 'hello baby');
        $this->assertTrue($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'NewFile-1.txt'));
        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'NewFile-copy-1.txt', 'hello baby');
        $this->assertTrue($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'NewFile-copy-1.txt'));

        // Verify generated names again
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder) == '4');
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder, 'NewFile.txt') == 'NewFile-2.txt');
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder, 'NewFile.txt', '', '-', true) == '1-NewFile.txt');
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder, 'NewFile.txt', 'copy', '-', false) == 'NewFile-copy-2.txt');
        $this->assertTrue($this->sut->findUniqueFileName($this->tempFolder, 'NewFile.txt', 'copy', '-', true) == 'copy-1-NewFile.txt');

        $sut2 = new FilesManager($this->tempFolder);

        $this->assertTrue($sut2->findUniqueFileName('', 'NewFile.txt', 'copy', '-', true) == 'copy-1-NewFile.txt');

        // Test wrong values
        // not necessary

        // Test exceptions
        // not necessary
    }


    /**
     * testCreateDirectory
     *
     * @return void
     */
    public function testCreateDirectory(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->createDirectory(null); }, '/Path must be a non empty string/');
        AssertUtils::throwsException(function(){ $this->sut->createDirectory(''); }, '/Path must be a non empty string/');
        AssertUtils::throwsException(function(){ $this->sut->createDirectory('     '); }, '/Path must be a non empty string/');
        AssertUtils::throwsException(function(){ $this->sut->createDirectory("\n\n\n"); }, '/Path must be a non empty string/');

        // Test ok values
        $this->assertTrue($this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'test1'));
        $this->assertTrue($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'test1'));

        $this->assertTrue($this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'1234'));
        $this->assertTrue($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'1234'));

        $this->assertTrue($this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'-go-'));
        $this->assertTrue($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'-go-'));

        // Test already existing folders
        $this->assertTrue(!$this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'test1'));
        $this->assertTrue(!$this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'1234'));
        $this->assertTrue(!$this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'-go-'));

        // Test already existing files
        $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'3', 'hello baby');
        AssertUtils::throwsException(function(){ $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'3'); }, '/specified path is an existing file/');

        // Test creating recursive folders
        $recursive1 = $this->tempFolder.DIRECTORY_SEPARATOR.'test55'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'tes5'.DIRECTORY_SEPARATOR.'t5';
        AssertUtils::throwsException(function() use ($recursive1) { $this->sut->createDirectory($recursive1); }, '/file or directory/');

        $this->assertFalse($this->sut->isDirectory($recursive1));
        $this->assertTrue($this->sut->createDirectory($recursive1, true));
        $this->assertTrue($this->sut->isDirectory($recursive1));

        $recursive2 = $this->tempFolder.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'a';
        AssertUtils::throwsException(function() use ($recursive2) { $this->sut->createDirectory($recursive2); }, '/file or directory/');

        $this->assertFalse($this->sut->isDirectory($recursive2));
        $this->assertTrue($this->sut->createDirectory($recursive2, true));
        $this->assertTrue($this->sut->isDirectory($recursive2));

        $sut2 = new FilesManager($this->tempFolder);
        $this->assertFalse($sut2->isDirectory('subfolder-tocreate'));
        $this->assertTrue($sut2->createDirectory('subfolder-tocreate', true));
        $this->assertTrue($sut2->isDirectory('subfolder-tocreate'));
        $this->assertTrue($sut2->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'subfolder-tocreate'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function(){ $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'wrongchars????'); }, '/Forbidden .* chars found/');
        AssertUtils::throwsException(function(){ $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'wrongchars*'); }, '/Forbidden .* chars found/');
        AssertUtils::throwsException(function(){ $this->sut->createDirectory('\345\ertert'); }, '/file or directory/');
        AssertUtils::throwsException(function(){ $this->sut->createDirectory(['\345\ertert', 1]); }, '/Path must be a non empty string/');
    }


    /**
     * testGetOSTempDirectory
     *
     * @return void
     */
    public function testGetOSTempDirectory(){

        $this->assertSame(StringUtils::formatPath(sys_get_temp_dir(), DIRECTORY_SEPARATOR), $this->sut->getOSTempDirectory());
    }


    /**
     * testCreateTempDirectory
     *
     * @return void
     */
    public function testCreateTempDirectory(){

        // Test empty values
        try {
            $this->sut->createTempDirectory(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/desiredName must be a string/', $e->getMessage());
        }

        $emptyTempFolder = $this->sut->createTempDirectory('');
        $this->assertTrue($this->sut->isDirectoryEmpty($emptyTempFolder));
        $this->assertTrue(NumericUtils::isNumeric(StringUtils::getPathElement($emptyTempFolder)));

        $emptyTempFolder = $this->sut->createTempDirectory('   ');
        $this->assertTrue($this->sut->isDirectoryEmpty($emptyTempFolder));
        $this->assertTrue(NumericUtils::isNumeric(StringUtils::getPathElement($emptyTempFolder)));

        AssertUtils::throwsException(function(){ $this->sut->createTempDirectory([]); }, '/must be a string/');
        AssertUtils::throwsException(function(){ $this->sut->createTempDirectory("\n\n\n"); }, '/Forbidden .* chars found/');

        // Test ok values

        // Create a temp directory with a name
        $someTempFolder = $this->sut->createTempDirectory('some-temp-folder');
        $this->assertTrue($this->sut->isDirectoryEmpty($someTempFolder));
        $this->assertTrue(strpos($someTempFolder, 'some-temp-folder') !== false);

        // Try to create a temp folder with the same name
        $someTempFolder2 = $this->sut->createTempDirectory('some-temp-folder');
        $this->assertTrue($this->sut->isDirectoryEmpty($someTempFolder2));
        $this->assertNotSame($someTempFolder, $someTempFolder2);
        $this->assertTrue(strpos($someTempFolder2, 'some-temp-folder') !== false);

        // Try to create a temp folder with a strange name
        $someTempFolder2 = $this->sut->createTempDirectory('--');
        $this->assertTrue($this->sut->isDirectoryEmpty($someTempFolder2));
        $this->assertNotSame($someTempFolder, $someTempFolder2);
        $this->assertTrue(strpos($someTempFolder2, '--') !== false);

        // Test wrong values
        AssertUtils::throwsException(function(){ $this->sut->createTempDirectory("invalid??chars"); }, '/Forbidden .* chars found/');

        // Test exceptions
        // already tested
    }


    /**
     * testGetDirectoryList
     *
     * @return void
     */
    public function testGetDirectoryList(){

        $validationManager = new ValidationManager();

        // Test empty values
        try {
            $this->sut->getDirectoryList(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->getDirectoryList('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->getDirectoryList('       ');
            $this->exceptionMessage = '"      " did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test ok values

        // Create some folders and files
        $this->assertTrue($this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'file.txt', 'hello baby'));
        $this->assertTrue($this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'test1'));
        $this->assertTrue($this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'1234'));
        $this->assertTrue($this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'-go-'));
        $this->assertTrue($this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'ABC'));
        $this->assertTrue($this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'PROPERTY'));

        // Check that list is ok
        $res = $this->sut->getDirectoryList($this->tempFolder);
        $this->assertTrue($validationManager->isArray($res));
        $this->assertTrue(count($res) == 6);
        $this->assertTrue(in_array('file.txt', $res));
        $this->assertTrue(in_array('test1', $res));
        $this->assertTrue(in_array('1234', $res));
        $this->assertTrue(in_array('-go-', $res));
        $this->assertTrue(in_array('ABC', $res));
        $this->assertTrue(in_array('PROPERTY', $res));

        $sut2 = new FilesManager($this->tempFolder);
        $res = $sut2->getDirectoryList('');
        $this->assertTrue($validationManager->isArray($res));
        $this->assertTrue(count($res) == 6);
        $this->assertTrue(in_array('file.txt', $res));
        $this->assertTrue(in_array('PROPERTY', $res));

        // Check sorted lists
        $res = $this->sut->getDirectoryList($this->tempFolder, 'nameAsc');
        $this->assertTrue(ArrayUtils::isEqualTo($res, ['-go-', '1234', 'ABC', 'file.txt', 'PROPERTY', 'test1']));

        $res = $this->sut->getDirectoryList($this->tempFolder, 'nameDesc');
        $this->assertTrue(ArrayUtils::isEqualTo($res, ['test1', 'PROPERTY', 'file.txt', 'ABC', '1234', '-go-']));

        // TODO - test sort by modification date
        //$res = $this->sut->getDirectoryList($this->tempFolder, 'mDateAsc');
        //$this->assertTrue(ArrayUtils::isEqualTo($res, ['file.txt', 'test1', '1234', '-go-']));

        //$res = $this->sut->getDirectoryList($this->tempFolder, 'mDateDesc');
        //$this->assertTrue(ArrayUtils::isEqualTo($res, ['-go-', '1234', 'test1', 'file.txt']));

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->getDirectoryList('wrtwrtyeyery');
            $this->exceptionMessage = 'wrtwrtyeyery did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->getDirectoryList([1,2,3,4]);
            $this->exceptionMessage = 'wrtwrtyeyery did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }
    }


    /**
     * testGetDirectorySize
     *
     * @return void
     */
    public function testGetDirectorySize(){

        // Test empty values
        try {
            $this->sut->getDirectorySize(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->getDirectorySize('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->getDirectorySize('       ');
            $this->exceptionMessage = '"      " did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test ok values
        $this->createDummyDirectoryStucture($this->tempFolder, 4, 4, 'somefile', 5, 'file content');
        $this->assertTrue($this->sut->getDirectorySize($this->tempFolder) === 960);

        $this->createDummyDirectoryStucture($this->tempFolder.DIRECTORY_SEPARATOR.'testsize-1', 2, 2, 'biggerFile', 2, StringUtils::generateRandom(250, 250));
        $this->assertTrue($this->sut->getDirectorySize($this->tempFolder.DIRECTORY_SEPARATOR.'testsize-1') === 2000);

        $this->createDummyDirectoryStucture($this->tempFolder.DIRECTORY_SEPARATOR.'testsize-2', 2, 2, 'biggerFile', 2, StringUtils::generateRandom(1250, 1250));
        $this->assertTrue($this->sut->getDirectorySize($this->tempFolder.DIRECTORY_SEPARATOR.'testsize-2') === 10000);

        $this->assertTrue($this->sut->getDirectorySize($this->tempFolder) === 12960);

        $sut2 = new FilesManager($this->tempFolder);
        $this->assertTrue($sut2->getDirectorySize('') === 12960);

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->getDirectorySize('wrtwrtyeyery');
            $this->exceptionMessage = 'wrtwrtyeyery did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->getDirectorySize([1,2,3,4]);
            $this->exceptionMessage = 'wrtwrtyeyery did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }
    }


    /**
     * testCopyDirectory
     *
     * @return void
     */
    public function testCopyDirectory(){

        // Test empty values
        try {
            $this->sut->copyDirectory(null, null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, null given/', $e->getMessage());
        }

        try {
            $this->sut->copyDirectory('', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/cannot copy a directory into itself/', $e->getMessage());
        }

        try {
            $this->sut->copyDirectory('       ', '       ');
            $this->exceptionMessage = '"      " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/cannot copy a directory into itself/', $e->getMessage());
        }

        // Test ok values

        // Create some folder structures
        $dir1 = $this->tempFolder.DIRECTORY_SEPARATOR.'dir1';
        $dir2 = $this->tempFolder.DIRECTORY_SEPARATOR.'dir2';
        $this->createDummyDirectoryStucture($dir1, 4, 4, 'somefile', 5, 'file content');
        $this->createDummyDirectoryStucture($dir2, 2, 4, 'somefile', 3, 'asdfasdfasdfasdf');

        $dest1 = $this->tempFolder.DIRECTORY_SEPARATOR.'dest1';
        $dest2 = $this->tempFolder.DIRECTORY_SEPARATOR.'dest2';
        $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'dest1');
        $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'dest2');

        $this->assertTrue($this->sut->copyDirectory($dir1, $dest1));
        $this->assertTrue($this->sut->isDirectoryEqualTo($dir1, $dest1));

        $this->assertTrue($this->sut->copyDirectory($dest2, $dir1, false));
        $this->assertTrue($this->sut->isDirectoryEmpty($dest2));
        $this->assertFalse($this->sut->isDirectoryEmpty($dir1));
        $this->assertFalse($this->sut->isDirectoryEqualTo($dest2, $dir1));

        $this->assertTrue($this->sut->copyDirectory($dir1, $dest1, false));
        $this->assertTrue($this->sut->isDirectoryEqualTo($dir1, $dest1));

        $this->assertTrue($this->sut->copyDirectory($dir2, $dest1, false));
        $this->assertFalse($this->sut->isDirectoryEqualTo($dir1, $dest1));

        // Test wrong values
        try {
            $this->sut->copyDirectory($dir1, $dir1);
            $this->exceptionMessage = 'copy on same folder did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->copyDirectory($dir1, $dir1, false);
            $this->exceptionMessage = 'copy on same folder did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->copyDirectory($dir1, $dest1);
            $this->exceptionMessage = 'copy on non empty folder did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->copyDirectory($dir1, $this->tempFolder.DIRECTORY_SEPARATOR.'nonexistant');
            $this->exceptionMessage = 'non existant folder did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test exceptions
        try {
            $this->sut->copyDirectory('wrtwrtyeyery');
            $this->exceptionMessage = 'wrtwrtyeyery did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->copyDirectory([1,2,3,4]);
            $this->exceptionMessage = 'wrtwrtyeyery did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }
    }


    /**
     * testMirrorDirectory
     *
     * @return void
     */
    public function testMirrorDirectory(){

        // Test empty values
        try {
            $this->sut->mirrorDirectory(null, null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->mirrorDirectory('', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist/', $e->getMessage());
        }

        try {
            $this->sut->mirrorDirectory('       ', '       ');
            $this->exceptionMessage = '"      " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist/', $e->getMessage());
        }

        $this->assertTrue($this->sut->createDirectory($this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->mirrorDirectory($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));

        // Test ok values

        // Alter contents on one file from the previously mirrored temp folder and make sure the mirror process restores it
        $this->assertTrue($this->sut->saveFile($this->tempFolder.'/test-1/c/d', 'modified'));
        $this->assertFalse($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->mirrorDirectory($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));

        // Rename one file from the previously mirrored temp folder and make sure the mirror process restores it
        $this->assertTrue($this->sut->renameFile($this->tempFolder.'/test-1/a', $this->tempFolder.'/test-1/a-renamed'));
        $this->assertFalse($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->mirrorDirectory($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));

        // Delete one file from the previously mirrored temp folder and make sure the mirror process restores it
        $this->assertTrue($this->sut->deleteFile($this->tempFolder.'/test-1/c/d'));
        $this->assertFalse($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->mirrorDirectory($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));

        // Add one file to the previously mirrored temp folder and make sure the mirror process restores it
        $this->assertTrue($this->sut->saveFile($this->tempFolder.'/test-1/c/e', 'e'));
        $this->assertFalse($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->mirrorDirectory($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));

        // Rename one folder from the previously mirrored temp folder and make sure the mirror process restores it
        $this->assertTrue($this->sut->renameDirectory($this->tempFolder.'/test-1/c', $this->tempFolder.'/test-1/c-modified'));
        $this->assertFalse($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->mirrorDirectory($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));

        // Delete one folder from the previously mirrored temp folder and make sure the mirror process restores it
        $this->assertSame(1, $this->sut->deleteDirectory($this->tempFolder.'/test-1/c'));
        $this->assertFalse($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->mirrorDirectory($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));

        // Add one empty folder to the previously mirrored temp folder and make sure the mirror process restores it
        $this->assertTrue($this->sut->createDirectory($this->tempFolder.'/test-1/c/e'));
        $this->assertFalse($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->mirrorDirectory($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));
        $this->assertTrue($this->sut->isDirectoryEqualTo($this->basePath.'/mirrorDirectory/test-1', $this->tempFolder.'/test-1'));

        // Test wrong values
        try {
            $this->sut->mirrorDirectory($this->tempFolder.'/test-1', $this->tempFolder.'/test-1');
            $this->exceptionMessage = 'copy on same folder did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/cannot mirror a directory into itself/', $e->getMessage());
        }

        try {
            $this->sut->mirrorDirectory($this->tempFolder.'/test-1', $this->tempFolder.DIRECTORY_SEPARATOR.'nonexistant');
            $this->exceptionMessage = 'non existant folder did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist.*nonexistant/', $e->getMessage());
        }

        // Test exceptions
        try {
            $this->sut->mirrorDirectory('wrtwrtyeyery');
            $this->exceptionMessage = 'wrtwrtyeyery did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Too few arguments to function/', $e->getMessage());
        }

        try {
            $this->sut->mirrorDirectory([1,2,3,4], [1,2,3,4]);
            $this->exceptionMessage = '[1,2,3,4] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path must be a string/', $e->getMessage());
        }
    }


    /**
     * testSyncDirectories
     *
     * @return void
     */
    public function testSyncDirectories(){

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
     * testRenameDirectory
     *
     * @return void
     */
    public function testRenameDirectory(){

        // Test empty values
        try {
            $this->sut->renameDirectory(null, null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->renameDirectory(new stdClass(), new stdClass());
            $this->exceptionMessage = 'new stdClass() did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->renameDirectory(0, 0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->renameDirectory('', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist: /', $e->getMessage());
        }

        try {
            $this->sut->renameDirectory('          ', '          ');
            $this->exceptionMessage = '"          " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist: /', $e->getMessage());
        }

        // Test ok values
        $dir = $this->tempFolder.DIRECTORY_SEPARATOR.'dir1';
        $this->assertTrue($this->sut->createDirectory($dir));
        $this->assertTrue($this->sut->renameDirectory($dir, $dir.'_renamed'));
        $this->assertFalse($this->sut->isDirectory($dir));
        $this->assertTrue($this->sut->isDirectory($dir.'_renamed'));

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->renameDirectory('nonexistant-path', $dir);
            $this->exceptionMessage = 'nonexistant-path did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path does not exist: nonexistant-path/', $e->getMessage());
        }

        $dir = $this->tempFolder.DIRECTORY_SEPARATOR.'dir2';
        $this->assertTrue($this->sut->createDirectory($dir));

        try {
            $this->sut->renameDirectory($dir, $dir);
            $this->exceptionMessage = '$dir, $dir did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid destination:.*dir2/', $e->getMessage());
        }

        try {
            $this->sut->renameDirectory($dir, 'nonexistant-path');
            $this->exceptionMessage = '$dir, nonexistant-path did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Source and dest must be on the same path/', $e->getMessage());
        }

        try {
            $this->sut->renameDirectory($dir, $dir.'_renamed'.DIRECTORY_SEPARATOR.'subrename');
            $this->exceptionMessage = '$dir, $dir_renamed did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Source and dest must be on the same path/', $e->getMessage());
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
            // We expect an exception to happen
        }

        try {
            $this->sut->deleteDirectory(new stdClass());
            $this->exceptionMessage = 'new stdClass() did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->deleteDirectory(0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->deleteDirectory('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->deleteDirectory('          ');
            $this->exceptionMessage = '"          " did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test ok values
        $this->assertTrue($this->sut->isDirectoryEqualTo($this->tempFolder, $this->tempFolder));

        // Create some folder structures
        $dir1 = $this->tempFolder.DIRECTORY_SEPARATOR.'dir1';
        $dir2 = $this->tempFolder.DIRECTORY_SEPARATOR.'dir2';
        $this->createDummyDirectoryStucture($dir1, 4, 4, 'somefile', 5, 'file content');
        $this->createDummyDirectoryStucture($dir2, 8, 2, 'somefile', 2, 'f');

        $this->assertSame(80, $this->sut->deleteDirectory($dir1, false));
        $this->assertTrue($this->sut->isDirectory($dir1));
        $this->assertTrue($this->sut->isDirectoryEmpty($dir1));
        $this->assertSame(0, $this->sut->deleteDirectory($dir1, true));
        $this->assertFalse($this->sut->isDirectory($dir1));

        $this->assertSame(32 ,$this->sut->deleteDirectory($dir2));
        $this->assertFalse($this->sut->isDirectory($dir1));

        // Test wrong values
        try {
            $this->sut->deleteDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'dir-non-existant');
            $this->exceptionMessage = '"dir-non-existant" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test exceptions
        try {
            $this->sut->deleteDirectory([1, 2, 3, 4]);
            $this->exceptionMessage = '[1, 2, 3, 4] did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }
    }


    /**
     * testSaveFile
     *
     * @return void
     */
    public function testSaveFile(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->saveFile(null, null); }, '/Path must be a string/');
        AssertUtils::throwsException(function(){ $this->sut->saveFile(null, null, null); }, '/must be of the type bool, null given/');
        AssertUtils::throwsException(function(){ $this->sut->saveFile('', null); }, '/Filename cannot be empty/');
        AssertUtils::throwsException(function(){ $this->sut->saveFile('', null, null); }, '/must be of the type bool, null given/');
        AssertUtils::throwsException(function(){ $this->sut->saveFile('', ''); }, '/Filename cannot be empty/');
        AssertUtils::throwsException(function(){ $this->sut->saveFile('', '', null); }, '/must be of the type bool, null given/');
        AssertUtils::throwsException(function(){ $this->sut->saveFile('somepath', '', false, null); }, '/must be of the type bool, null given/');

        // Test ok values
        $this->assertFalse($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'empty.txt'));
        $this->assertTrue($this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'empty.txt'));
        $this->assertSame('', $this->sut->readFile($this->tempFolder.DIRECTORY_SEPARATOR.'empty.txt'));

        $this->assertFalse($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'file.txt'));
        $this->assertTrue($this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'file.txt', 'test'));
        $this->assertSame('test', $this->sut->readFile($this->tempFolder.DIRECTORY_SEPARATOR.'file.txt'));

        $this->assertTrue($this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'file.txt', 'test', true));
        $this->assertSame('testtest', $this->sut->readFile($this->tempFolder.DIRECTORY_SEPARATOR.'file.txt'));

        $this->assertTrue($this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'file.txt', 'replaced', false));
        $this->assertSame('replaced', $this->sut->readFile($this->tempFolder.DIRECTORY_SEPARATOR.'file.txt'));

        $this->assertFalse($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'file2.txt'));
        $this->assertTrue($this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'file2.txt', 'file2', true));
        $this->assertSame('file2', $this->sut->readFile($this->tempFolder.DIRECTORY_SEPARATOR.'file2.txt'));

        $sut2 = new FilesManager($this->tempFolder);
        $this->assertFalse($sut2->isFile('file3.txt'));
        $this->assertTrue($sut2->saveFile('file3.txt', 'file3'));
        $this->assertSame('file3', $sut2->readFile('file3.txt'));

        $this->assertFalse($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'dir1'));
        $this->assertFalse($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'dir1'.DIRECTORY_SEPARATOR.'dir2'));
        $this->assertFalse($this->sut->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'dir1'.DIRECTORY_SEPARATOR.'dir2'.DIRECTORY_SEPARATOR.'file.txt'));
        $this->assertTrue($this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'dir1'.DIRECTORY_SEPARATOR.'dir2'.DIRECTORY_SEPARATOR.'file.txt', 'test', false, true));
        $this->assertSame('test', $this->sut->readFile($this->tempFolder.DIRECTORY_SEPARATOR.'dir1'.DIRECTORY_SEPARATOR.'dir2'.DIRECTORY_SEPARATOR.'file.txt'));

        // Test wrong values
        AssertUtils::throwsException(function(){ $this->sut->saveFile('nonexistantpath/nonexistantfile'); }, '/No such file or directory/');
        AssertUtils::throwsException(function(){ $this->sut->saveFile([1,2,3,4,5]); }, '/Path must be a string/');

        $this->assertTrue($this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'dir1'.DIRECTORY_SEPARATOR.'file'));
        AssertUtils::throwsException(function(){ $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'dir1'.DIRECTORY_SEPARATOR.'file'.DIRECTORY_SEPARATOR.'file.txt', 'test', false, true); }, '/specified path is an existing file/');

        // Test exceptions
        $this->assertFalse($this->sut->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'dir'));
        $this->assertTrue($this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'dir'));

        AssertUtils::throwsException(function(){ $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'dir'); }, '/failed to open stream/');
    }


    /**
     * testCreateTempFile
     *
     * @return void
     */
    public function testCreateTempFile(){

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
     * testMergeFiles
     *
     * @return void
     */
    public function testMergeFiles(){

        // Test empty values
        try {
            $this->sut->mergeFiles(null, null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->mergeFiles('', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->mergeFiles('       ', '       ');
            $this->exceptionMessage = '"      " did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test ok values

        // Create some dummy text files
        for ($i = 0; $i < 3; $i++) {

            $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'empty-'.$i.'.txt', '');
            $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'a-'.$i.'.txt', 'text a-'.$i);
            $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'b-'.$i.'.txt', 'text b-'.$i);
            $this->sut->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'c-'.$i.'.txt', 'text c-'.$i);
        }

        // Test merging empty files
        $files = [
            $this->tempFolder.DIRECTORY_SEPARATOR.'empty-0.txt',
            $this->tempFolder.DIRECTORY_SEPARATOR.'empty-1.txt',
            $this->tempFolder.DIRECTORY_SEPARATOR.'empty-2.txt'
        ];

        $destFile = $this->tempFolder.DIRECTORY_SEPARATOR.'merged-file.txt';

        $this->sut->mergeFiles($files, $destFile);
        $this->assertTrue($this->sut->readFile($destFile) === '');

        $this->sut->mergeFiles($files, $destFile, "\n\n");
        $this->assertTrue($this->sut->readFile($destFile) === "\n\n\n\n");

        $this->sut->mergeFiles($files, $destFile, '---');
        $this->assertTrue($this->sut->readFile($destFile) === '------');

        // Test merging non empty files
        $files = [
            $this->tempFolder.DIRECTORY_SEPARATOR.'a-0.txt',
            $this->tempFolder.DIRECTORY_SEPARATOR.'a-1.txt',
            $this->tempFolder.DIRECTORY_SEPARATOR.'a-2.txt'
        ];

        $this->sut->mergeFiles($files, $destFile);
        $this->assertTrue($this->sut->readFile($destFile) === 'text a-0text a-1text a-2');

        $this->sut->mergeFiles($files, $destFile, ' ');
        $this->assertTrue($this->sut->readFile($destFile) === 'text a-0 text a-1 text a-2');

        $this->sut->mergeFiles($files, $destFile, "\n\n");
        $this->assertTrue($this->sut->readFile($destFile) === "text a-0\n\ntext a-1\n\ntext a-2");

        $files = [
            $this->tempFolder.DIRECTORY_SEPARATOR.'a-0.txt',
            $this->tempFolder.DIRECTORY_SEPARATOR.'b-1.txt',
            $this->tempFolder.DIRECTORY_SEPARATOR.'c-2.txt'
        ];

        $this->sut->mergeFiles($files, $destFile);
        $this->assertTrue($this->sut->readFile($destFile) === 'text a-0text b-1text c-2');

        $this->sut->mergeFiles($files, $destFile, '||');
        $this->assertTrue($this->sut->readFile($destFile) === 'text a-0||text b-1||text c-2');

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->mergeFiles(1, $destFile);
            $this->exceptionMessage = '1 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->mergeFiles(false, $destFile);
            $this->exceptionMessage = 'false did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->mergeFiles([1,2,3,4], $destFile);
            $this->exceptionMessage = '[1,2,3,4] did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->mergeFiles($files, $this->tempFolder.DIRECTORY_SEPARATOR.'nonexistant'.DIRECTORY_SEPARATOR.'nonexistant');
            $this->exceptionMessage = 'non existant folder did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }
    }


    /**
     * testGetFileSize
     *
     * @return void
     */
    public function testGetFileSize(){

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
     * testGetFileModificationTime
     *
     * @return void
     */
    public function testGetFileModificationTime(){

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
     * testReadFile
     *
     * @return void
     */
    public function testReadFile(){

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
     * readFileAsBase64
     *
     * @return void
     */
    public function testReadFileAsBase64(){

        // Test empty values
        // TODO

        // Test ok values
        $this->assertSame('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',
                          $this->sut->readFileAsBase64($this->basePath.'/readFile/excel-sheet.xlsx'));
        // TODO - more cases

        // Test wrong values
        // TODO

        // Test exceptions
        // TODO
    }


    /**
     * testReadFileBuffered
     *
     * @return void
     */
    public function testReadFileBuffered(){

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
     * testReadFileBuffered
     *
     * @return void
     */
    public function testCopyFile(){

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
     * testRenameFile
     *
     * @return void
     */
    public function testRenameFile(){

        // Test empty values
        try {
            $this->sut->renameFile(null, null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->renameFile(new stdClass(), new stdClass());
            $this->exceptionMessage = 'new stdClass() did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->renameFile(0, 0);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path must be a string/', $e->getMessage());
        }

        try {
            $this->sut->renameFile('', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/File does not exist: /', $e->getMessage());
        }

        try {
            $this->sut->renameFile('          ', '          ');
            $this->exceptionMessage = '"          " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/File does not exist: /', $e->getMessage());
        }

        // Test ok values
        $file = $this->tempFolder.DIRECTORY_SEPARATOR.'file1';
        $this->assertTrue($this->sut->saveFile($file, 'data'));
        $this->assertTrue($this->sut->renameFile($file, $file.'_renamed'));
        $this->assertFalse($this->sut->isFile($file));
        $this->assertTrue($this->sut->isFile($file.'_renamed'));

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->renameFile('nonexistant-path', $file);
            $this->exceptionMessage = 'nonexistant-path did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/File does not exist: nonexistant-path/', $e->getMessage());
        }

        $file = $this->tempFolder.DIRECTORY_SEPARATOR.'dir2';
        $this->assertTrue($this->sut->saveFile($file, 'data'));

        try {
            $this->sut->renameFile($file, $file);
            $this->exceptionMessage = '$dir, $dir did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid destination:.*dir2/', $e->getMessage());
        }

        try {
            $this->sut->renameFile($file, 'nonexistant-path');
            $this->exceptionMessage = '$dir, nonexistant-path did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Source and dest must be on the same path/', $e->getMessage());
        }

        try {
            $this->sut->renameFile($file, $file.'_renamed'.DIRECTORY_SEPARATOR.'subrename');
            $this->exceptionMessage = '$dir, $dir_renamed did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Source and dest must be on the same path/', $e->getMessage());
        }
    }


    // TODO - Add all missing tests
}

?>