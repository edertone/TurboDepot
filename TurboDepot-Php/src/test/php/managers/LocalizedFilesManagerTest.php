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

use Throwable;
use PHPUnit\Framework\TestCase;
use org\turbocommons\src\main\php\utils\ArrayUtils;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbodepot\src\main\php\managers\LocalizedFilesManager;


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
        $this->basePath = __DIR__.'/../resources/managers/localizedFilesManager';

        $this->filesManager = new FilesManager();

        // Create a temporary folder
        $this->tempFolder = $this->filesManager->createTempDirectory('TurboDepot-LocalizedFilesManagerTest');
        $this->assertTrue(strpos($this->tempFolder, 'TurboDepot-LocalizedFilesManagerTest') !== false);
        $this->assertTrue($this->filesManager->isDirectoryEmpty($this->tempFolder));
        $this->assertFalse($this->filesManager->isFile($this->tempFolder));

        $this->defaultLocations = [[
            'label' => 'test-json',
            'path' => $this->basePath.'/locales/test-json/$locale/$bundle.json',
            'bundles' => ['Locales']
        ]];

        $this->sut = new LocalizedFilesManager($this->basePath.'/paths/example-1', ['en_US', 'es_ES'], $this->defaultLocations);
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
            $this->sut = new LocalizedFilesManager(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Too few arguments to function/', $e->getMessage());
        }

        try {
            $this->sut = new LocalizedFilesManager(null, null);
            $this->exceptionMessage = 'null null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Too few arguments to function/', $e->getMessage());
        }

        try {
            $this->sut = new LocalizedFilesManager(null, null, null);
            $this->exceptionMessage = 'null null null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/rootPath must be a string/', $e->getMessage());
        }

        try {
            $this->sut = new LocalizedFilesManager('', '', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Specified rootPath does not exist/', $e->getMessage());
        }

        try {
            $this->sut = new LocalizedFilesManager([], '', '');
            $this->exceptionMessage = '[] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/rootPath must be a string/', $e->getMessage());
        }

        try {
            $this->sut = new LocalizedFilesManager($this->tempFolder, '', '');
            $this->exceptionMessage = '$this->tempFolder, "" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Locations must be an array of objects/', $e->getMessage());
        }

        try {
            $this->sut = new LocalizedFilesManager($this->tempFolder, '', $this->defaultLocations);
            $this->exceptionMessage = '$this->tempFolder, "", $this->defaultLocations did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/no locales defined/', $e->getMessage());
        }

        // Test ok values
        $this->assertSame(DIRECTORY_SEPARATOR, (new LocalizedFilesManager($this->tempFolder, ['en_US'], $this->defaultLocations))->dirSep());
        $this->assertSame(DIRECTORY_SEPARATOR, (new LocalizedFilesManager($this->tempFolder, ['en_US', 'es_ES'], $this->defaultLocations))->dirSep());

        // Test wrong values
        try {
            $this->sut = new LocalizedFilesManager('nonexistant path', ['en_US'], $this->defaultLocations);
            $this->exceptionMessage = 'nonexistant path did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Specified rootPath does not exist: nonexistant path/', $e->getMessage());
        }

        try {
            $this->assertSame(DIRECTORY_SEPARATOR, (new LocalizedFilesManager($this->tempFolder, ['en_US', 'es_ES', 'ca_ES'], $this->defaultLocations))->dirSep());
            $this->exceptionMessage = '"ca_ES" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/File not found.*ca_ES/', $e->getMessage());
        }

        try {
            $this->assertSame(DIRECTORY_SEPARATOR, (new LocalizedFilesManager($this->tempFolder, ['5345345'], $this->defaultLocations))->dirSep());
            $this->exceptionMessage = '"5345345" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/File not found.*5345345/', $e->getMessage());
        }

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
    public function setPrimaryLocales(){

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
    public function setPrimaryLanguage(){

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
    public function setPrimaryLanguages(){

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
        try {
            $this->sut->getDirectoryList(null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/path must be a string/', $e->getMessage());
        }

        $this->assertTrue(ArrayUtils::isArray($this->sut->getDirectoryList('')));
        $this->assertSame(3, count($this->sut->getDirectoryList('')));

        // Test ok values
        $list = $this->sut->getDirectoryList('', '', '', [], 'nameAsc');
        $this->assertSame(3, count($list));

        assertLocalizedFilesObject($list[0], true, '', 'en_US', 'LOGIN', 'Login');
        assertLocalizedFilesObject($list[1], true, '', 'en_US', 'PASSWORD', 'Password');
        assertLocalizedFilesObject($list[2], true, '', 'en_US', 'TAG_NOT_EXISTING_ON_ES_ES', 'Missing tag');

        try {
            $list = $this->sut->getDirectoryList('TAG_NOT_EXISTING_ON_ES_ES/LOGIN', '', '', [], 'nameAsc');
            $this->exceptionMessage = 'getDirectoryList did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/key <non existant key> not found on Locales/', $e->getMessage());
        }

        // Create a new LocalizedFilesManager instance without throwing an exception if a key does not exist
        $this->sut = new LocalizedFilesManager($this->basePath.'/paths/example-1', ['en_US', 'es_ES'], $this->defaultLocations, false);

        $list = $this->sut->getDirectoryList('TAG_NOT_EXISTING_ON_ES_ES/LOGIN', '', '', [], 'nameAsc');
        $this->assertSame(4, count($list));

        assertLocalizedFilesObject($list[0], false, '', 'en_US', 'non existant key', 'non existant key');
        assertLocalizedFilesObject($list[1], false, 'txt', 'en_US', 'non existant key', 'non existant key.txt');
        assertLocalizedFilesObject($list[2], false, '', 'en_US', 'PASSWORD', 'Password');
        assertLocalizedFilesObject($list[3], false, 'txt', 'en_US', 'USER', 'User.txt');

        // Test spanish language
        $this->sut->setPrimaryLocale('es_ES');

        $list = $this->sut->getDirectoryList('TAG_NOT_EXISTING_ON_ES_ES/LOGIN', '', '', [], 'nameAsc');
        $this->assertSame(4, count($list));

        assertLocalizedFilesObject($list[0], false, '', 'es_ES', 'non existant key', 'non existant key');
        assertLocalizedFilesObject($list[3], false, 'txt', 'es_ES', 'USER', 'Usuario.txt');

        // Test non existant tags are translated with the next locale by preference

        $list = $this->sut->getDirectoryList('TAG_NOT_EXISTING_ON_ES_ES', '', '', [], 'nameAsc');
        $this->assertSame(2, count($list));

        assertLocalizedFilesObject($list[0], true, '', 'es_ES', 'LOGIN', 'Acceso');
        assertLocalizedFilesObject($list[1], false, 'txt', 'es_ES', 'TAG_NOT_EXISTING_ON_ES_ES', 'Missing tag.txt');

        $this->sut->setPrimaryLocale('en_US');
        $list = $this->sut->getDirectoryList('TAG_NOT_EXISTING_ON_ES_ES', '', '', [], 'nameAsc');
        $this->assertSame(2, count($list));

        assertLocalizedFilesObject($list[0], true, '', 'en_US', 'LOGIN', 'Login');
        assertLocalizedFilesObject($list[1], false, 'txt', 'en_US', 'TAG_NOT_EXISTING_ON_ES_ES', 'Missing tag.txt');

        // Test folder containing files with multiple localized contents

        $list = $this->sut->getDirectoryList('LOGIN', '', '', [], 'nameAsc');
        $this->assertSame(3, count($list));

        assertLocalizedFilesObject($list[0], false, 'TXT', 'en_US', 'PASSWORD', 'Password.TXT');
        assertLocalizedFilesObject($list[1], false, 'txt', 'en_US', 'TAG_NOT_EXISTING_ON_ES_ES', 'Missing tag.txt');
        assertLocalizedFilesObject($list[2], false, 'txt', 'en_US', 'USER', 'User.txt');

        $this->sut->setPrimaryLocale('es_ES');

        $list = $this->sut->getDirectoryList('LOGIN', '', '', [], 'nameAsc');
        $this->assertSame(3, count($list));

        assertLocalizedFilesObject($list[0], false, 'TXT', 'es_ES', 'PASSWORD', 'Contraseña.TXT');
        assertLocalizedFilesObject($list[1], false, 'txt', 'es_ES', 'TAG_NOT_EXISTING_ON_ES_ES', 'Missing tag.txt');
        assertLocalizedFilesObject($list[2], false, 'txt', 'es_ES', 'USER', 'Usuario.txt');

        // Test wrong values
        // Test exceptions
        try {
            $list = $this->sut->getDirectoryList(34545435, '', '', [], 'nameAsc');
            $this->exceptionMessage = '34545435 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/path must be a string/', $e->getMessage());
        }

        try {
            $list = $this->sut->getDirectoryList('LOGIN', 'nonexistantbundle', '', [], 'nameAsc');
            $this->exceptionMessage = 'nonexistantbundle did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Bundle <nonexistantbundle> not loaded/', $e->getMessage());
        }

        try {
            $list = $this->sut->getDirectoryList('LOGIN', '', 999999, [], 'nameAsc');
            $this->exceptionMessage = '999999 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Location <999999> not loaded/', $e->getMessage());
        }
    }


    /**
     * testTODO
     *
     * @return void
     */
    public function testTODO(){

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

    // TODO - add all missing tests
}

?>