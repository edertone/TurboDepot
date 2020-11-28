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
use stdClass;
use Throwable;
use org\turbocommons\src\main\php\managers\ValidationManager;
use org\turbocommons\src\main\php\utils\ArrayUtils;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbocommons\src\main\php\utils\NumericUtils;
use org\turbocommons\src\main\php\utils\StringUtils;


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
    protected function tearDown(){

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
    public static function tearDownAfterClass(){

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

        $reconstructedPath = '';

        for ($i = 0, $l = StringUtils::countPathElements($this->tempFolder); $i < $l; $i++) {

            $reconstructedPath .= StringUtils::getPathElement($this->tempFolder, $i).DIRECTORY_SEPARATOR;

            $this->assertTrue($this->sut->isDirectory($reconstructedPath));
        }

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
        AssertUtils::throwsException(function(){ $this->sut->findDirectoryItems($this->tempFolder, '/^.*-4.txt$/', 'noformat'); }, '/invalid returnFormat: noformat/');
        AssertUtils::throwsException(function(){ $this->sut->findDirectoryItems($this->tempFolder, '/folder-0-1/', 'x', 'both', -1, '', 'absolute'); }, '/invalid returnFormat: x/');
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
        try {
            $this->sut->createDirectory(null);
            $this->exceptionMessage = 'Null did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->createDirectory('');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->createDirectory('     ');
            $this->exceptionMessage = '"     " did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->createDirectory("\n\n\n");
            $this->exceptionMessage = '"     " did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

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
        try {
            $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'3');
            $this->exceptionMessage = 'basepath did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test creating recursive folders
        $recursive1 = $this->tempFolder.DIRECTORY_SEPARATOR.'test55'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'tes5'.DIRECTORY_SEPARATOR.'t5';
        try {
            $this->sut->createDirectory($recursive1);
            $this->exceptionMessage = 'recursive1 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        $this->assertFalse($this->sut->isDirectory($recursive1));
        $this->assertTrue($this->sut->createDirectory($recursive1, true));
        $this->assertTrue($this->sut->isDirectory($recursive1));

        $recursive2 = $this->tempFolder.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'a';
        try {
            $this->sut->createDirectory($recursive2);
            $this->exceptionMessage = 'recursive2 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/No such file or directory/', $e->getMessage());
        }

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
        try {
            $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'wrongchars????');
            $this->exceptionMessage = 'wrongchars???? did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/No such file or directory/', $e->getMessage());
        }

        try {
            $this->sut->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'wrongchars*');
            $this->exceptionMessage = 'wrongchars* did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/No such file or directory/', $e->getMessage());
        }

        try {
            $this->sut->createDirectory('\345\ertert');
            $this->exceptionMessage = '\345\ertert did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/No such file or directory/', $e->getMessage());
        }

        try {
            $this->sut->createDirectory(['\345\ertert', 1]);
            $this->exceptionMessage = '\345\ertert did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Path must be a string/', $e->getMessage());
        }
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

        try {
            $this->sut->createTempDirectory([]);
            $this->exceptionMessage = '[] did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            $this->sut->createTempDirectory("\n\n\n");
            $this->exceptionMessage = '"\n\n\n"did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

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
        try {
            $this->sut->createTempDirectory("invalid??chars");
            $this->exceptionMessage = 'invalid??chars not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/No such file or directory/', $e->getMessage());
        }

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