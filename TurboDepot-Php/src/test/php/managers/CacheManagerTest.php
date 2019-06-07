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
use org\turbodepot\src\main\php\managers\CacheManager;
use org\turbodepot\src\main\php\managers\FilesManager;


/**
 * CacheManagerTest tests
 *
 * @return void
 */
class CacheManagerTest extends TestCase {


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

        $this->tempFolder = $this->filesManager->createTempDirectory('TurboCommons-CacheManagerTest');
        $this->assertTrue($this->filesManager->isDirectoryEmpty($this->tempFolder));

        $this->sut = new CacheManager($this->tempFolder, 'test-zone');

        $this->assertTrue($this->filesManager->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR.'metadata'));
        $this->assertSame(1, count($this->filesManager->getDirectoryList($this->tempFolder.DIRECTORY_SEPARATOR.'test-zone')));
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
            $this->sut = new CacheManager(null, 'test');
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, null given/', $e->getMessage());
        }

        try {
            $this->sut = new CacheManager(0, 'test');
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid rootPath Received: 0/', $e->getMessage());
        }

        try {
            $this->sut = new CacheManager('', 'test');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid rootPath Received: /', $e->getMessage());
        }

        try {
            $this->sut = new CacheManager('          ', 'test');
            $this->exceptionMessage = '"      " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid rootPath Received:           /', $e->getMessage());
        }

        // Test ok values
        $this->assertSame('org\turbodepot\src\main\php\managers\CacheManager', get_class(new CacheManager($this->tempFolder, 'test')));

        // TODO - test the timeToLive constructor parameter

        // Test wrong values
        // Test exceptions
        try {
            $this->sut = new CacheManager('invalid/path/here', 'test');
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid rootPath Received: invalid\/path\/here/', $e->getMessage());
        }

        try {
            $this->sut = new CacheManager([1, 2, 3], 'test');
            $this->exceptionMessage = '[1, 2, 3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, array given/', $e->getMessage());
        }

        try {
            $this->sut = new CacheManager($this->tempFolder, null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, null given/', $e->getMessage());
        }

        try {
            $this->sut = new CacheManager($this->tempFolder, '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/zone must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut = new CacheManager($this->tempFolder, '       ');
            $this->exceptionMessage = '"       " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/zone must be a non empty string/', $e->getMessage());
        }
    }


    /**
     * testIsZoneExpired
     *
     * @return void
     */
    public function testIsZoneExpired(){

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
     * testAdd
     *
     * @return void
     */
    public function testAdd(){

        // Test empty values
        try {
            $this->sut->add(null, null, null);
            $this->exceptionMessage = '$emptyValue section did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/data must be a string/', $e->getMessage());
        }

        foreach ($this->emptyValues as $emptyValue) {
            try {
                $this->sut->add($emptyValue, 'validId', 'validData');
                $this->exceptionMessage = '$emptyValue section did not cause exception';
            } catch (Throwable $e) {
                $this->assertRegExp('/section must be a non empty string/', $e->getMessage());
            }

            try {
                $this->sut->add('validSection', $emptyValue, 'validData');
                $this->exceptionMessage = '$emptyValue id did not cause exception';
            } catch (Throwable $e) {
                $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
            }
        }

        try {
            $this->sut->add('validSection', 'validId', null);
            $this->exceptionMessage = '$emptyValue data did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/data must be a string/', $e->getMessage());
        }

        $this->sut->add('validSection', 'validId', '');

        $this->assertTrue($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR.'validSection'));

        // Test ok values
        $this->sut->add('someSection', 'someId', 'someData1');
        $this->assertFalse($this->filesManager->isDirectoryEmpty($this->tempFolder));

        $this->assertSame('someData2', $this->sut->add('someSection', 'someId2', 'someData2'));
        $this->assertSame('someData3', $this->sut->add('someSection2', 'someId', 'someData3'));
        $this->assertSame('someData4', $this->sut->add('someSection2', 'someId2', 'someData4'));

        $this->assertSame('someData1', $this->sut->get('someSection', 'someId'));
        $this->assertSame('someData2', $this->sut->get('someSection', 'someId2'));
        $this->assertSame('someData3', $this->sut->get('someSection2', 'someId'));
        $this->assertSame('someData4', $this->sut->get('someSection2', 'someId2'));

        // TODO - test the zone timeToLive parameter

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->add(123123, 'validId', 'valid');
            $this->exceptionMessage = '123123 data did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/section must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->add('validSection', [1,2,3,4], 'valid');
            $this->exceptionMessage = '123123 data did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->add('validSection', 'validId', new stdClass());
            $this->exceptionMessage = 'stdClass data did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/data must be a string/', $e->getMessage());
        }
    }


    /**
     * testGet
     *
     * @return void
     */
    public function testGet(){

        // Test empty values
        try {
            $this->sut->get(null, null);
            $this->exceptionMessage = 'null section did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/section must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->get('someSection', null);
            $this->exceptionMessage = 'null id did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a non empty string/', $e->getMessage());
        }

        // Test ok values
        $this->sut->add('s1', '1', '1');
        $this->sut->add('s1', '2', '2');
        $this->sut->add('s1', '3', '3');
        $this->sut->add('s2', '1', '1');
        $this->sut->add('s2', '2', '2');
        $this->sut->add('s2', '3', '3');

        $this->assertSame('1', $this->sut->get('s1', '1'));
        $this->assertSame('2', $this->sut->get('s1', '2'));
        $this->assertSame('3', $this->sut->get('s1', '3'));
        $this->assertSame('1', $this->sut->get('s2', '1'));
        $this->assertSame('2', $this->sut->get('s2', '2'));
        $this->assertSame('3', $this->sut->get('s2', '3'));

        // TODO - test the zone timeToLive parameter

        // Test wrong values
        $this->assertSame(null, $this->sut->get('nonexistantsection', 'nonexistantid'));
        $this->assertSame(null, $this->sut->get('someSection', 'nonexistantid'));
        $this->assertSame(null, $this->sut->get('s1', '4'));
        $this->assertSame(null, $this->sut->get('s3', '1'));

        // Test exceptions
        try {
            $this->sut->get([1,2,3,4,5], '1');
            $this->exceptionMessage = '[1,2,3,4,5] id did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/section must be a non empty string/', $e->getMessage());
        }
    }


    /**
     * testClearZone
     *
     * @return void
     */
    public function testClearZone(){

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
     * testClearSection
     *
     * @return void
     */
    public function testClearSection(){

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