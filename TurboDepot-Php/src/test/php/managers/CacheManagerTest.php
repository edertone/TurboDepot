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

        $this->assertFalse($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'));
        $this->assertFalse($this->filesManager->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR.'metadata'));
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
            $this->assertRegExp('/Invalid rootPath received: 0/', $e->getMessage());
        }

        try {
            $this->sut = new CacheManager('', 'test');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid rootPath received: /', $e->getMessage());
        }

        try {
            $this->sut = new CacheManager('          ', 'test');
            $this->exceptionMessage = '"      " did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid rootPath received:           /', $e->getMessage());
        }

        // Test ok values
        $this->assertSame('org\turbodepot\src\main\php\managers\CacheManager', get_class(new CacheManager($this->tempFolder, 'test')));

        // Test wrong values
        // Test exceptions
        try {
            $this->sut = new CacheManager('invalid/path/here', 'test');
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid rootPath received: invalid\/path\/here/', $e->getMessage());
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
     * testConstructClearsZoneIfExpired
     *
     * @return void
     */
    public function testConstructClearsZoneIfExpired(){

        // This test checks that class constructor performs an expiration check, so
        // when the defined zone is expired, its cached data will be removed by the constructor itself
        $sectionFolder = $this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR.'someSection';

        $this->sut->setZoneTimeToLive(1);

        $this->assertSame('someData1', $this->sut->save('someSection', 'someId', 'someData1'));

        $this->assertTrue($this->filesManager->isDirectory($sectionFolder));

        $this->assertFalse($this->sut->isZoneExpired());

        sleep(2);

        $this->sut = new CacheManager($this->tempFolder, 'test-zone');

        $this->assertTrue($this->filesManager->isDirectoryEmpty($sectionFolder));

        $this->assertFalse($this->sut->isZoneExpired());

        sleep(2);

        $this->assertSame('someData', $this->sut->save('someSection', 'someId', 'someData'));
        $this->assertTrue($this->filesManager->isDirectory($sectionFolder));
        $this->assertFalse($this->sut->isZoneExpired());

        sleep(2);
        $this->assertTrue($this->filesManager->isDirectory($sectionFolder));
        $this->assertTrue($this->sut->isZoneExpired());
        $this->assertTrue($this->filesManager->isDirectoryEmpty($sectionFolder));
    }


    /**
     * testGetZoneName
     *
     * @return void
     */
    public function testGetZoneName(){

        $this->assertSame('test-zone', $this->sut->getZoneName());
    }


    /**
     * testSetZoneTimeToLive
     *
     * @return void
     */
    public function testSetZoneTimeToLive(){

        // This test checks the time to live feature for the cache zones
        $zoneRoot = $this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR;

        $this->sut->setZoneTimeToLive(3);

        $this->assertTrue($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertSame('3|', $this->filesManager->readFile($zoneRoot.'metadata'));

        $this->assertSame('someData1', $this->sut->save('someSection', 'someId', 'someData1'));
        $this->assertSame('someData2', $this->sut->save('someSection', 'someId2', 'someData2'));

        // Test that expiry part of the metadata contains at least 8 digits
        $metadataFileContents = $this->filesManager->readFile($zoneRoot.'metadata');
        $this->assertRegExp('/3\|\d{8,}/', $metadataFileContents);

        $this->assertSame('someData1', $this->sut->get('someSection', 'someId'));

        sleep(1);

        $this->assertSame('someData3', $this->sut->save('someSection', 'someId3', 'someData3'));
        $this->assertSame($metadataFileContents, $this->filesManager->readFile($zoneRoot.'metadata'));
        $this->assertSame('someData1', $this->sut->get('someSection', 'someId'));
        $this->assertSame('someData3', $this->sut->get('someSection', 'someId3'));

        sleep(2);

        $this->assertSame(null, $this->sut->get('someSection', 'someId'));
        $this->assertSame(null, $this->sut->get('someSection', 'someId2'));
        $this->assertSame(null, $this->sut->get('someSection', 'someId3'));

        $this->assertTrue($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'));
        $this->assertTrue($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertSame('3|', $this->filesManager->readFile($zoneRoot.'metadata'));
    }


    /**
     * testSetZoneTimeToLive_infinite_value
     *
     * @return void
     */
    public function testSetZoneTimeToLive_infinite_value(){

        // This test checks the time to live feature for the cache zones
        $zoneRoot = $this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR;

        $this->sut->setZoneTimeToLive(0);

        $this->assertTrue($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertSame('0|', $this->filesManager->readFile($zoneRoot.'metadata'));

        $this->assertSame('someData1', $this->sut->save('someSection', 'someId', 'someData1'));

        // Test that expiry part of the metadata contains at least 8 digits
        $metadataFileContents = $this->filesManager->readFile($zoneRoot.'metadata');
        $this->assertRegExp('/0\|\d{8,}/', $metadataFileContents);

        $this->assertSame('someData1', $this->sut->get('someSection', 'someId'));

        // As we cannot wait infinite to check that the cache zone never expires, we use a 10 secs value
        sleep(10);

        $this->assertSame('someData2', $this->sut->save('someSection', 'someId2', 'someData2'));
        $this->assertSame($metadataFileContents, $this->filesManager->readFile($zoneRoot.'metadata'));
        $this->assertSame('someData1', $this->sut->get('someSection', 'someId'));
        $this->assertSame('someData2', $this->sut->get('someSection', 'someId2'));
    }


    /**
     * testSetZoneTimeToLive_Metadata_Not_Changes_When_Value_Is_Already_TheSame
     *
     * @return void
     */
    public function testSetZoneTimeToLive_Metadata_Not_Changes_When_Value_Is_Already_TheSame(){

        $zoneFile = $this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR.'metadata';

        $this->assertFalse($this->filesManager->isFile($zoneFile));

        $this->sut->setZoneTimeToLive(2);

        $this->assertTrue($this->filesManager->isFile($zoneFile));
        $modificationTime = $this->filesManager->getFileModificationTime($zoneFile);

        sleep(1);

        $this->sut->setZoneTimeToLive(2);

        $this->assertSame($modificationTime, $this->filesManager->getFileModificationTime($zoneFile));

        sleep(1);

        $this->sut->setZoneTimeToLive(2);

        $this->assertSame($modificationTime, $this->filesManager->getFileModificationTime($zoneFile));

        $this->sut->setZoneTimeToLive(3);

        $this->assertNotSame($modificationTime, $this->filesManager->getFileModificationTime($zoneFile));
    }


    /**
     * testSetSectionTimeToLive
     *
     * @return void
     */
    public function testSetSectionTimeToLive(){

        // This test checks the time to live feature for the cache sections
        $zoneRoot = $this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR;
        $sectionRoot = $zoneRoot.'someSection'.DIRECTORY_SEPARATOR;

        $this->assertFalse($this->filesManager->isDirectory($zoneRoot));
        $this->assertFalse($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertFalse($this->filesManager->isFile($sectionRoot.'metadata'));

        $this->sut->setZoneTimeToLive(4);
        $this->assertSame('4|', $this->filesManager->readFile($zoneRoot.'metadata'));
        $this->assertTrue($this->filesManager->isDirectory($zoneRoot));
        $this->assertTrue($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertFalse($this->filesManager->isFile($sectionRoot.'metadata'));

        $this->sut->setSectionTimeToLive('someSection', 2);
        $this->assertSame('2|', $this->filesManager->readFile($sectionRoot.'metadata'));

        $this->assertSame('someData', $this->sut->save('someSection', 'someId', 'someData'));

        // Test that expiry part of the metadata files contain at least 8 digits
        $this->assertRegExp('/4\|\d{8,}/', $this->filesManager->readFile($zoneRoot.'metadata'));
        $this->assertRegExp('/2\|\d{8,}/', $this->filesManager->readFile($sectionRoot.'metadata'));

        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('someSection'));
        $this->assertTrue($this->filesManager->isFile($this->sut->getPath('someSection', 'someId')));

        sleep(2);

        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertTrue($this->sut->isSectionExpired('someSection'));
        $this->assertSame('2|', $this->filesManager->readFile($sectionRoot.'metadata'));
        $this->assertSame(null, $this->sut->getPath('someSection', 'someId'));

        sleep(2);

        $this->assertTrue($this->sut->isZoneExpired());
        $this->assertSame('4|', $this->filesManager->readFile($zoneRoot.'metadata'));
    }


    /**
     * testSetSectionTimeToLive_infinite_value
     *
     * @return void
     */
    public function testSetSectionTimeToLive_infinite_value(){

        // This test checks the time to live feature for the cache sections
        $zoneRoot = $this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR;
        $sectionRoot = $zoneRoot.'someSection'.DIRECTORY_SEPARATOR;

        $this->sut->setZoneTimeToLive(2);
        $this->assertSame('2|', $this->filesManager->readFile($zoneRoot.'metadata'));
        $this->assertTrue($this->filesManager->isDirectory($zoneRoot));
        $this->assertTrue($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertFalse($this->filesManager->isFile($sectionRoot.'metadata'));

        $this->sut->setSectionTimeToLive('someSection', 0);
        $this->assertSame('0|', $this->filesManager->readFile($sectionRoot.'metadata'));

        $this->assertSame('someData', $this->sut->save('someSection', 'someId', 'someData'));

        // Test that expiry part of the metadata files contain at least 8 digits
        $this->assertRegExp('/2\|\d{8,}/', $this->filesManager->readFile($zoneRoot.'metadata'));
        $this->assertRegExp('/0\|\d{8,}/', $this->filesManager->readFile($sectionRoot.'metadata'));

        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('someSection'));
        $this->assertTrue($this->filesManager->isFile($this->sut->getPath('someSection', 'someId')));

        sleep(2);

        $this->assertTrue($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('someSection'));
        $this->assertSame('2|', $this->filesManager->readFile($zoneRoot.'metadata'));

        // As we cannot wait infinite to check that the cache zone never expires, we use a 8 secs value
        sleep(8);

        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('someSection'));
        $this->assertSame('2|', $this->filesManager->readFile($zoneRoot.'metadata'));
        $this->assertRegExp('/0\|\d{8,}/', $this->filesManager->readFile($sectionRoot.'metadata'));
    }


    /**
     * testIsZoneExpired
     *
     * @return void
     */
    public function testIsZoneExpired(){

        $zoneRoot = $this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR;

        $this->assertFalse($this->sut->isZoneExpired());

        $this->assertSame('someData', $this->sut->save('someSection', 'someId', 'someData'));

        $this->assertFalse($this->sut->isZoneExpired());

        sleep(1);

        $this->assertSame('someData2', $this->sut->save('someSection', 'someId2', 'someData2'));
        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertFalse($this->filesManager->isFile($zoneRoot.'metadata'));

        $this->sut->setZoneTimeToLive(2);
        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertSame('2|', $this->filesManager->readFile($zoneRoot.'metadata'));

        sleep(1);

        $this->assertSame('someData3', $this->sut->save('someSection', 'someId3', 'someData3'));
        $this->assertFalse($this->sut->isZoneExpired());

        $metadataFileContents = $this->filesManager->readFile($zoneRoot.'metadata');
        $this->assertRegExp('/2\|\d{8,}/', $metadataFileContents);

        sleep(1);

        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertSame($metadataFileContents, $this->filesManager->readFile($zoneRoot.'metadata'));

        sleep(1);

        $this->assertTrue($this->sut->isZoneExpired());
        $this->assertSame('2|', $this->filesManager->readFile($zoneRoot.'metadata'));
    }


    /**
     * testIsSectionExpired
     *
     * @return void
     */
    public function testIsSectionExpired(){

        try {
            $this->sut->isSectionExpired('nonexistantsection');
            $this->exceptionMessage = 'nonexistantsection did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/section <nonexistantsection> does not exist/', $e->getMessage());
        }

        $zoneRoot = $this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR;
        $sectionRoot = $zoneRoot.'section'.DIRECTORY_SEPARATOR;

        $this->assertFalse($this->sut->isZoneExpired());

        try {
            $this->sut->isSectionExpired('section');
            $this->exceptionMessage = 'section did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/section <section> does not exist/', $e->getMessage());
        }

        $this->assertFalse($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertFalse($this->filesManager->isFile($sectionRoot.'metadata'));

        $this->assertSame('data', $this->sut->save('section', 'id', 'data'));
        $this->assertFalse($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertFalse($this->filesManager->isFile($sectionRoot.'metadata'));
        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('section'));

        $this->sut->setSectionTimeToLive('section', 2);
        $this->assertFalse($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertTrue($this->filesManager->isFile($sectionRoot.'metadata'));
        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('section'));

        sleep(1);

        $this->assertSame('data2', $this->sut->save('section', 'id2', 'data2'));
        $this->assertFalse($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertTrue($this->filesManager->isFile($sectionRoot.'metadata'));
        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('section'));

        $metadataFileContents = $this->filesManager->readFile($sectionRoot.'metadata');
        $this->assertRegExp('/2\|\d{8,}/', $metadataFileContents);

        sleep(1);

        $this->assertSame('data3', $this->sut->save('section', 'id3', 'data3'));
        $this->assertSame($metadataFileContents, $this->filesManager->readFile($sectionRoot.'metadata'));
        $this->assertFalse($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertTrue($this->filesManager->isFile($sectionRoot.'metadata'));
        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('section'));

        sleep(1);

        $this->assertSame($metadataFileContents, $this->filesManager->readFile($sectionRoot.'metadata'));
        $this->assertFalse($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertTrue($this->filesManager->isFile($sectionRoot.'metadata'));
        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertTrue($this->sut->isSectionExpired('section'));
        $this->assertSame('2|', $this->filesManager->readFile($sectionRoot.'metadata'));
    }


    /**
     * testIsSectionExpired_BeforeZoneExpiration
     *
     * @return void
     */
    public function testIsSectionExpired_BeforeZoneExpiration(){

        $zoneRoot = $this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR;
        $sectionRoot = $zoneRoot.'section'.DIRECTORY_SEPARATOR;

        $this->sut->setZoneTimeToLive(4);
        $this->sut->setSectionTimeToLive('section', 2);
        $this->sut->setSectionTimeToLive('section2', 0);
        $this->assertTrue($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertTrue($this->filesManager->isFile($sectionRoot.'metadata'));
        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('section'));
        $this->assertFalse($this->sut->isSectionExpired('section2'));

        $this->assertSame('data', $this->sut->save('section', 'id', 'data'));
        $this->assertSame('data2', $this->sut->save('section2', 'id2', 'data2'));

        sleep(2);

        $this->assertTrue($this->sut->isSectionExpired('section'));
        $this->assertFalse($this->sut->isSectionExpired('section2'));
        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertSame(null, $this->sut->get('section', 'id'));

        sleep(2);

        $this->assertTrue($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('section'));
        $this->assertFalse($this->sut->isSectionExpired('section2'));
        $this->assertSame(null, $this->sut->get('section', 'id'));
    }


    /**
     * testIsSectionExpired_AfterZoneExpiration
     *
     * @return void
     */
    public function testIsSectionExpired_AfterZoneExpiration(){

        $zoneRoot = $this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR;
        $sectionRoot = $zoneRoot.'section'.DIRECTORY_SEPARATOR;

        try {
            $this->sut->isSectionExpired('section');
            $this->exceptionMessage = 'section did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/section <section> does not exist/', $e->getMessage());
        }

        $this->sut->setZoneTimeToLive(1);
        $this->sut->setSectionTimeToLive('section', 3);
        $this->assertTrue($this->filesManager->isFile($zoneRoot.'metadata'));
        $this->assertTrue($this->filesManager->isFile($sectionRoot.'metadata'));
        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('section'));

        $this->assertSame('data', $this->sut->save('section', 'id', 'data'));

        sleep(1);

        $this->assertTrue($this->sut->isZoneExpired());
        $this->assertFalse($this->sut->isSectionExpired('section'));
        $this->assertSame('data', $this->sut->get('section', 'id'));

        sleep(2);

        $this->assertFalse($this->sut->isZoneExpired());
        $this->assertTrue($this->sut->isSectionExpired('section'));
        $this->assertSame(null, $this->sut->get('section', 'id'));
    }


    /**
     * testSave
     *
     * @return void
     */
    public function testSave(){

        // Test empty values
        try {
            $this->sut->save(null, null, null);
            $this->exceptionMessage = '$emptyValue section did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/data must be a string/', $e->getMessage());
        }

        foreach ($this->emptyValues as $emptyValue) {

            try {
                $this->sut->save($emptyValue, 'validId', 'validData');
                $this->exceptionMessage = '$emptyValue section did not cause exception';
            } catch (Throwable $e) {
                $this->assertRegExp('/section must be a non empty string/', $e->getMessage());
            }

            if($emptyValue === '' || $emptyValue === '     ' || $emptyValue === "\n\n\n"){

                $this->assertSame('validData', $this->sut->save('validSection', $emptyValue, 'validData'));

            }else{

                try {
                    $this->sut->save('validSection', $emptyValue, 'validData');
                    $this->exceptionMessage = '$emptyValue id did not cause exception';
                } catch (Throwable $e) {
                    $this->assertRegExp('/id must be a string/', $e->getMessage());
                }
            }
        }

        try {
            $this->sut->save('validSection', 'validId', null);
            $this->exceptionMessage = '$emptyValue data did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/data must be a string/', $e->getMessage());
        }

        $this->sut->save('validSection', 'validId', '');
        $this->assertTrue($this->filesManager->isDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR.'validSection'));

        $this->assertSame('data', $this->sut->save('validSection', '', 'data'));
        $this->assertSame('data', $this->filesManager->readFile($this->tempFolder.'/test-zone/validSection/.cache'));

        // Test ok values
        $this->sut->save('someSection', 'someId', 'someData1');
        $this->assertFalse($this->filesManager->isDirectoryEmpty($this->tempFolder));

        $this->assertSame('someData2', $this->sut->save('someSection', 'someId2', 'someData2'));
        $this->assertSame('someData3', $this->sut->save('someSection2', 'someId', 'someData3'));
        $this->assertSame('someData4', $this->sut->save('someSection2', 'someId2', 'someData4'));
        $this->assertSame('someData4-overriden', $this->sut->save('someSection2', 'someId2', 'someData4-overriden'));

        $this->assertSame('someData1', $this->sut->get('someSection', 'someId'));
        $this->assertSame('someData2', $this->sut->get('someSection', 'someId2'));
        $this->assertSame('someData3', $this->sut->get('someSection2', 'someId'));
        $this->assertSame('someData4-overriden', $this->sut->get('someSection2', 'someId2'));

        // Test wrong values
        // Test exceptions
        try {
            $this->sut->save(123123, 'validId', 'valid');
            $this->exceptionMessage = '123123 data did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/section must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->save('validSection', [1,2,3,4], 'valid');
            $this->exceptionMessage = '123123 data did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a string/', $e->getMessage());
        }

        try {
            $this->sut->save('validSection', 'validId', new stdClass());
            $this->exceptionMessage = 'stdClass data did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/data must be a string/', $e->getMessage());
        }
    }


    /**
     * testSave_DoesNotIncrementZoneExpiryTime
     *
     * @return void
     */
    public function testSave_DoesNotIncrementZoneExpiryTime(){

        $zoneRoot = $this->tempFolder.DIRECTORY_SEPARATOR.'test-zone'.DIRECTORY_SEPARATOR;

        $this->sut->setZoneTimeToLive(3);
        $this->assertSame('3|', $this->filesManager->readFile($zoneRoot.'metadata'));

        $this->sut->save('someSection', 'someId1', 'someData1');
        $metadataContents = $this->filesManager->readFile($zoneRoot.'metadata');
        $this->assertRegExp('/3\|\d{8,}/', $metadataContents);

        $this->sut->save('someSection', 'someId2', 'someData2');
        $this->assertSame($metadataContents, $this->filesManager->readFile($zoneRoot.'metadata'));

        sleep(1);
        $this->sut->save('someSection', 'someId3', 'someData3');
        $this->assertSame($metadataContents, $this->filesManager->readFile($zoneRoot.'metadata'));

        sleep(1);
        $this->sut->save('someSection', 'someId4', 'someData4');
        $this->assertSame($metadataContents, $this->filesManager->readFile($zoneRoot.'metadata'));

        sleep(2);
        $this->sut->save('someSection', 'someId5', 'someData5');
        $this->assertNotSame($metadataContents, $this->filesManager->readFile($zoneRoot.'metadata'));
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
            $this->assertRegExp('/id must be a string/', $e->getMessage());
        }

        // Test ok values
        $this->sut->save('s0', '', '0');
        $this->sut->save('s1', '1', '1');
        $this->sut->save('s1', '2', '2');
        $this->sut->save('s1', '3', '3');
        $this->sut->save('s2', '1', '1');
        $this->sut->save('s2', '2', '2');
        $this->sut->save('s2', '3', '3');

        $this->assertSame('0', $this->sut->get('s0', ''));
        $this->assertSame('1', $this->sut->get('s1', '1'));
        $this->assertSame('2', $this->sut->get('s1', '2'));
        $this->assertSame('3', $this->sut->get('s1', '3'));
        $this->assertSame('1', $this->sut->get('s2', '1'));
        $this->assertSame('2', $this->sut->get('s2', '2'));
        $this->assertSame('3', $this->sut->get('s2', '3'));

        // Test wrong values
        try {
            $this->sut->get('nonexistantsection', 'nonexistantid');
            $this->exceptionMessage = 'nonexistantsection did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/section <nonexistantsection> does not exist/', $e->getMessage());
        }

        $this->assertSame(null, $this->sut->get('s0', 'nonexistantid'));
        $this->assertSame(null, $this->sut->get('s1', '4'));

        try {
            $this->sut->get('s3', '1');
            $this->exceptionMessage = 's3 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/section <s3> does not exist/', $e->getMessage());
        }

        // Test exceptions
        try {
            $this->sut->get([1,2,3,4,5], '1');
            $this->exceptionMessage = '[1,2,3,4,5] id did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/section must be a non empty string/', $e->getMessage());
        }
    }


    /**
     * testGetPath
     *
     * @return void
     */
    public function testGetPath(){

        // Test empty values
        try {
            $this->sut->getPath(null, null);
            $this->exceptionMessage = 'null section did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/section must be a non empty string/', $e->getMessage());
        }

        try {
            $this->sut->getPath('someSection', null);
            $this->exceptionMessage = 'null id did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/id must be a string/', $e->getMessage());
        }

        // Test ok values
        $this->sut->save('s0', '', '0');
        $this->sut->save('s1', '1', '1');
        $this->sut->save('s1', '2', '2');
        $this->sut->save('s1', '3', '3');
        $this->sut->save('s2', '1', '1');
        $this->sut->save('s2', '2', '2');
        $this->sut->save('s2', '3', '3');

        $this->assertContains('.cache', $this->sut->getPath('s0', ''));
        $this->assertContains('.cache', $this->sut->getPath('s1', '1'));
        $this->assertContains('.cache', $this->sut->getPath('s1', '2'));
        $this->assertContains('.cache', $this->sut->getPath('s1', '3'));
        $this->assertContains('.cache', $this->sut->getPath('s2', '1'));
        $this->assertContains('.cache', $this->sut->getPath('s2', '2'));
        $this->assertContains('.cache', $this->sut->getPath('s2', '3'));

        // Test wrong values
        $this->assertSame(null, $this->sut->getPath('nonexistantsection', 'nonexistantid'));
        $this->assertSame(null, $this->sut->getPath('someSection', 'nonexistantid'));
        $this->assertSame(null, $this->sut->getPath('s1', '4'));
        $this->assertSame(null, $this->sut->getPath('s3', '1'));

        // Test exceptions
        try {
            $this->sut->getPath([1,2,3,4,5], '1');
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


    /**
     * testClearId
     *
     * @return void
     */
    public function testClearId(){

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