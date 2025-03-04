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

use Throwable;
use PHPUnit\Framework\TestCase;
use org\turbocommons\src\main\php\utils\ArrayUtils;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbodepot\src\main\php\managers\LocalizedFilesManager;
use org\turbotesting\src\main\php\utils\AssertUtils;


/**
 * LocalizedFilesManagerTest
 *
 * @return void
 */
class LocalizedFilesManagerTest extends TestCase {


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
        $this->basePath = __DIR__.'/../../resources/managers/localizedFilesManager';

        $this->filesManager = new FilesManager();

        // Create a temporary folder
        $this->tempFolder = $this->filesManager->createTempDirectory('TurboDepot-LocalizedFilesManagerTest');
        $this->assertTrue(strpos($this->tempFolder, 'TurboDepot-LocalizedFilesManagerTest') !== false);
        $this->assertTrue($this->filesManager->isDirectoryEmpty($this->tempFolder));
        $this->assertFalse($this->filesManager->isFile($this->tempFolder));

        $this->sut = new LocalizedFilesManager($this->basePath.'/paths/test-1', ['en_US', 'es_ES'], [$this->basePath.'/locales']);
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
        AssertUtils::throwsException(function() { $this->sut = new LocalizedFilesManager(null); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut = new LocalizedFilesManager(null, null); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut = new LocalizedFilesManager(null, null, null); }, '/rootPath must be a string/');
        AssertUtils::throwsException(function() { $this->sut = new LocalizedFilesManager('', '', ''); }, '/Specified rootPath does not exist/');
        AssertUtils::throwsException(function() { $this->sut = new LocalizedFilesManager([], '', ''); }, '/rootPath must be a string/');

        // Test ok values
        $this->assertSame(DIRECTORY_SEPARATOR, (new LocalizedFilesManager($this->tempFolder, ['en_US'], [$this->basePath.'/locales']))->dirSep());
        $this->assertSame(DIRECTORY_SEPARATOR, (new LocalizedFilesManager($this->tempFolder, ['en_US', 'es_ES'], [$this->basePath.'/locales']))->dirSep());

        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut = new LocalizedFilesManager('nonexistant path', ['en_US'], [$this->basePath.'/locales']); }, '/Specified rootPath does not exist: nonexistant path/');
        AssertUtils::throwsException(function() { $this->assertSame(DIRECTORY_SEPARATOR, (new LocalizedFilesManager($this->tempFolder, ['5345345'], [$this->basePath.'/locales']))->dirSep()); }, '/locale must be a valid xx_XX value/');

        // Test exceptions
        // Already tested
    }


    /**
     * testSetPrimaryLocale
     *
     * @return void
     */
    public function testSetPrimaryLocale(){

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
     * setPrimaryLocales
     *
     * @return void
     */
    public function testSetPrimaryLocales(){

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
     * setPrimaryLanguage
     *
     * @return void
     */
    public function testSetPrimaryLanguage(){

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
     * setPrimaryLanguages
     *
     * @return void
     */
    public function testSetPrimaryLanguages(){

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
     * testCreateDirectory
     *
     * @return void
     */
    public function testCreateDirectory(){

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
     * testGetDirectoryList
     *
     * @return void
     */
    public function testGetDirectoryList(){

        // Aux function to test the LocalizedFilesObject instances
        function assertLocalizedFilesObject($object, $isDir, $extension, $locale, $key, $translation) {

            TestCase::assertSame($isDir, $object->getIsDirectory());
            TestCase::assertSame($extension, $object->getExtension());
            TestCase::assertSame($locale, $object->getLocale());
            TestCase::assertSame(substr($locale, 0, 2), $object->getLanguage());
            TestCase::assertSame($key, $object->getKey());
            TestCase::assertSame($translation, $object->getTranslation());
        }

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->getDirectoryList(null); }, '/Path must be a string/');

        $this->assertTrue(ArrayUtils::isArray($this->sut->getDirectoryList('', 'test-json/users')));
        $this->assertSame(3, count($this->sut->getDirectoryList('', 'test-json/users')));

        // Test ok values
        $list = $this->sut->getDirectoryList('', 'test-json/users', [], 'nameAsc');
        $this->assertSame(3, count($list));

        assertLocalizedFilesObject($list[0], true, '', 'en_US', 'LOGIN', 'Login');
        assertLocalizedFilesObject($list[1], true, '', 'en_US', 'PASSWORD', 'Password');
        assertLocalizedFilesObject($list[2], true, '', 'en_US', 'TAG_NOT_EXISTING_ON_ES_ES', 'Missing tag');

        $list = $this->sut->getDirectoryList('TAG_NOT_EXISTING_ON_ES_ES/LOGIN', 'test-json/users', [], 'nameAsc');
        $this->assertSame(4, count($list));

        assertLocalizedFilesObject($list[0], false, '', 'en_US', 'non existant key', 'non existant key');
        assertLocalizedFilesObject($list[1], false, 'txt', 'en_US', 'non existant key', 'non existant key.txt');
        assertLocalizedFilesObject($list[2], false, '', 'en_US', 'PASSWORD', 'Password');
        assertLocalizedFilesObject($list[3], false, 'txt', 'en_US', 'USER', 'User.txt');

        // Test spanish language

        $this->sut->setPrimaryLocale('es_ES');

        $list = $this->sut->getDirectoryList('TAG_NOT_EXISTING_ON_ES_ES/LOGIN', 'test-json/users', [], 'nameAsc');
        $this->assertSame(4, count($list));

        assertLocalizedFilesObject($list[0], false, '', 'es_ES', 'non existant key', 'non existant key');
        assertLocalizedFilesObject($list[3], false, 'txt', 'es_ES', 'USER', 'Usuario.txt');

        // Test non existant tags are translated with the next locale by preference

        $list = $this->sut->getDirectoryList('TAG_NOT_EXISTING_ON_ES_ES', 'test-json/users', [], 'nameAsc');
        $this->assertSame(2, count($list));

        assertLocalizedFilesObject($list[0], true, '', 'es_ES', 'LOGIN', 'Acceso');
        assertLocalizedFilesObject($list[1], false, 'txt', 'es_ES', 'TAG_NOT_EXISTING_ON_ES_ES', 'Missing tag.txt');

        $this->sut->setPrimaryLocale('en_US');
        $list = $this->sut->getDirectoryList('TAG_NOT_EXISTING_ON_ES_ES', 'test-json/users', [], 'nameAsc');
        $this->assertSame(2, count($list));

        assertLocalizedFilesObject($list[0], true, '', 'en_US', 'LOGIN', 'Login');
        assertLocalizedFilesObject($list[1], false, 'txt', 'en_US', 'TAG_NOT_EXISTING_ON_ES_ES', 'Missing tag.txt');

        // Test folder containing files with multiple localized contents

        $list = $this->sut->getDirectoryList('LOGIN', 'test-json/users', [], 'nameAsc');
        $this->assertSame(3, count($list));

        assertLocalizedFilesObject($list[0], false, 'TXT', 'en_US', 'PASSWORD', 'Password.TXT');
        assertLocalizedFilesObject($list[1], false, 'txt', 'en_US', 'TAG_NOT_EXISTING_ON_ES_ES', 'Missing tag.txt');
        assertLocalizedFilesObject($list[2], false, 'txt', 'en_US', 'USER', 'User.txt');

        $this->sut->setPrimaryLocale('es_ES');

        $list = $this->sut->getDirectoryList('LOGIN', 'test-json/users', [], 'nameAsc');
        $this->assertSame(3, count($list));

        assertLocalizedFilesObject($list[0], false, 'TXT', 'es_ES', 'PASSWORD', 'Contraseña.TXT');
        assertLocalizedFilesObject($list[1], false, 'txt', 'es_ES', 'TAG_NOT_EXISTING_ON_ES_ES', 'Missing tag.txt');
        assertLocalizedFilesObject($list[2], false, 'txt', 'es_ES', 'USER', 'Usuario.txt');

        // Test folders with multiple localized contents

        $this->sut = new LocalizedFilesManager($this->basePath.'/paths/test-2', ['en_US', 'es_ES'], [$this->basePath.'/locales']);

        $list = $this->sut->getDirectoryList('2019', 'test-json/users', [], 'nameAsc');
        $this->assertSame(1, count($list));

        assertLocalizedFilesObject($list[0], true, '', 'en_US', '10', '10');

        $list = $this->sut->getDirectoryList('2019/10/29', 'test-json/users', [], 'nameAsc');
        $this->assertSame(2, count($list));

        assertLocalizedFilesObject($list[0], true, '', 'en_US', 'some-folder-title', 'some-folder-title');
        assertLocalizedFilesObject($list[1], true, '', 'en_US', 'USER', 'User');

        // Test wrong values
        $this->sut = new LocalizedFilesManager($this->basePath.'/paths/test-1', ['en_US', 'es_ES'], [$this->basePath.'/locales'], true);

        AssertUtils::throwsException(function() { $this->sut->getDirectoryList('TAG_NOT_EXISTING_ON_ES_ES/LOGIN', 'test-json/users', [], 'nameAsc'); }, '/key <non existant key> not found on test-json.users/');

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->getDirectoryList(34545435, 'test-json/users', [], 'nameAsc'); }, '/Path must be a string/');
        AssertUtils::throwsException(function() { $this->sut->getDirectoryList('LOGIN', 'test-json/nonexistant', [], 'nameAsc'); }, '/key <PASSWORD> not found on test-json.nonexistant/');
        AssertUtils::throwsException(function() { $this->sut->getDirectoryList('LOGIN', '999999/users', [], 'nameAsc'); }, '/key <PASSWORD> not found on 999999.users/');
    }


    /**
     * testCopyDirectory
     *
     * @return void
     */
    public function testCopyDirectory(){

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
     * testMirrorDirectory
     *
     * @return void
     */
    public function testMirrorDirectory(){

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
     * testDeleteDirectory
     *
     * @return void
     */
    public function testDeleteDirectory(){

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
     * testSaveFile
     *
     * @return void
     */
    public function testSaveFile(){

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
        AssertUtils::throwsException(function() { $this->sut->readFile(null); }, '/pathOrObject is not a valid file/');
        AssertUtils::throwsException(function() { $this->sut->readFile(''); }, '/File does not exist.*test-1/');
        AssertUtils::throwsException(function() { $this->sut->readFile(0); }, '/pathOrObject is not a valid file/');

        // Test ok values - Passing a LocalizedFilesObject instance

        $list = $this->sut->getDirectoryList('LOGIN', 'test-json/users', [], 'nameAsc');

        $this->assertSame('0', $this->sut->readFile($list[0]));
        $this->assertSame('1', $this->sut->readFile($list[1]));
        $this->assertSame('2-en_US', $this->sut->readFile($list[2]));

        $this->sut->setPrimaryLocale('es_ES');
        $this->assertSame('2-en_US', $this->sut->readFile($list[2]));

        $list = $this->sut->getDirectoryList('LOGIN', 'test-json/users', [], 'nameAsc');

        $this->assertSame('0', $this->sut->readFile($list[0]));
        $this->assertSame('1', $this->sut->readFile($list[1]));
        $this->assertSame('2-es_ES', $this->sut->readFile($list[2]));

        $this->sut->setPrimaryLocale('en_US');
        $list = $this->sut->getDirectoryList('PASSWORD/LOGIN', 'test-json/users', [], 'nameAsc');
        $this->assertSame('1-en_US', $this->sut->readFile($list[0]));
        $this->assertSame('0', $this->sut->readFile($list[1]));
        $this->assertSame('2', $this->sut->readFile($list[2]));

        $this->sut->setPrimaryLocale('es_ES');
        $this->assertSame('1-en_US', $this->sut->readFile($list[0]));

        $list = $this->sut->getDirectoryList('PASSWORD/LOGIN', 'test-json/users', [], 'nameAsc');
        $this->assertSame('1-es_ES', $this->sut->readFile($list[0]));
        $this->assertSame('0', $this->sut->readFile($list[1]));
        $this->assertSame('2', $this->sut->readFile($list[2]));

        // Test ok values - Passing directly a path as a string

        $this->sut = new LocalizedFilesManager($this->basePath.'/paths/test-2', ['en_US', 'es_ES'], [$this->basePath.'/locales']);

        $this->assertSame('some english text', $this->sut->readFile('2019/10/29/some-folder-title-en_US/text.md'));
        $this->assertSame('Un texto en español', $this->sut->readFile('2019/10/29/some-folder-title-es_ES/text.md'));
        $this->assertSame('1', $this->sut->readFile('2019/10/29/USER/somefile'));
        $this->assertSame('2', $this->sut->readFile('2019/10/29/USER-en_US/somefile'));
        $this->assertSame('3', $this->sut->readFile('2019/10/29/USER-es_ES/somefile'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->readFile(9283498234); }, '/pathOrObject is not a valid file/');
        AssertUtils::throwsException(function() { $this->sut->readFile([12121, 454 , 4545]); }, '/pathOrObject is not a valid file/');
    }


    /**
     * testCopyFile
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