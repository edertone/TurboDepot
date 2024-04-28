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
use stdClass;
use Exception;
use org\turbodepot\src\main\php\model\XlsxObject;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbotesting\src\main\php\utils\AssertUtils;


/**
 * XlsxObjectTest
 *
 * @return void
 */
class XlsxObjectTest extends TestCase {


    /**
     * @var XlsxObject
     */
    private $sut;

    protected static $basePath;

    protected static $filesManager;


    /**
     * @see TestCase::setUpBeforeClass()
     *
     * @return void
     */
    public static function setUpBeforeClass(){

        self::$basePath = __DIR__.'/../../resources/model/xlsxObject';

        self::$filesManager = new FilesManager();
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(){
        // Nothing here
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){
        // Nothing here
    }


    /**
     * @see TestCase::tearDownAfterClass()
     *
     * @return void
     */
    public static function tearDownAfterClass(){
        // Nothing here
    }


    /**
     * testConstruct
     *
     * @return void
     */
    public function testConstruct(){

        // Test empty values
        AssertUtils::throwsException(function() { $c = new XlsxObject(null); }, '/constructor expects a string value/');
        AssertUtils::throwsException(function() { $c = new XlsxObject(0); }, '/constructor expects a string value/');
        AssertUtils::throwsException(function() { $c = new XlsxObject([]); }, '/constructor expects a string value/');

        $this->sut = new XlsxObject('');
        $this->assertInstanceOf(XlsxObject::class, $this->sut);
        $this->assertEquals(0, $this->sut->countCells());
        $this->assertEquals(0, $this->sut->countRows());
        $this->assertEquals(0, $this->sut->countColumns());

        $this->sut = new XlsxObject(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'empty.xlsx'));
        $this->assertInstanceOf(XlsxObject::class, $this->sut);
        $this->assertEquals(0, $this->sut->countCells());
        $this->assertEquals(0, $this->sut->countRows());
        $this->assertEquals(0, $this->sut->countColumns());

        // Test ok values
        $this->sut = new XlsxObject(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'1 sheet with 1 cell.xlsx'));
        $this->assertInstanceOf(XlsxObject::class, $this->sut);
        $this->assertSame(1, $this->sut->countCells());
        $this->assertSame(1, $this->sut->countRows());
        $this->assertSame(1, $this->sut->countColumns());
        $this->assertSame('1', $this->sut->getCell(0, 0));

        $this->sut = new XlsxObject(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'1 sheet with 4 cells.xlsx'));
        $this->assertInstanceOf(XlsxObject::class, $this->sut);
        $this->assertSame(4, $this->sut->countCells());
        $this->assertSame(2, $this->sut->countRows());
        $this->assertSame(2, $this->sut->countColumns());
        $this->assertSame('1', $this->sut->getCell(0, 0));
        $this->assertSame('2', $this->sut->getCell(0, 1));
        $this->assertSame('3', $this->sut->getCell(1, 0));
        $this->assertSame('4', $this->sut->getCell(1, 1));

        $this->sut = new XlsxObject(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'1 sheet with complex cells.xlsx'));
        $this->assertInstanceOf(XlsxObject::class, $this->sut);
        $this->assertSame(120, $this->sut->countCells());
        $this->assertSame(24, $this->sut->countRows());
        $this->assertSame(5, $this->sut->countColumns());
        $this->assertSame('PEDIDO STOCK --BLANCO-- 1225', $this->sut->getCell(0, 0));
        $this->assertSame(null, $this->sut->getCell(0, 1));
        $this->assertSame(null, $this->sut->getCell(0, 2));
        $this->assertSame(null, $this->sut->getCell(0, 3));
        $this->assertSame('', $this->sut->getCell(1, 0));
        $this->assertSame(null, $this->sut->getCell(1, 1));
        $this->assertSame(null, $this->sut->getCell(1, 2));
        $this->assertSame(null, $this->sut->getCell(1, 3));
        $this->assertSame('Fecha', $this->sut->getCell(2, 0));
        $this->assertSame('Denominación', $this->sut->getCell(2, 3));
        $this->assertSame('Cantidad', $this->sut->getCell(2, 4));
        $this->assertSame('45398', $this->sut->getCell(3, 0));
        $this->assertSame('2024/1225', $this->sut->getCell(3, 1));
        $this->assertSame('60.1116 30', $this->sut->getCell(3, 2));
        $this->assertSame('a', $this->sut->getCell(3, 3));
        $this->assertSame('26', $this->sut->getCell(3, 4));
        $this->assertSame('45398', $this->sut->getCell(16, 0));
        $this->assertSame('2024/1225', $this->sut->getCell(16, 1));
        $this->assertSame('68.2370 30', $this->sut->getCell(16, 2));
        $this->assertSame('n', $this->sut->getCell(16, 3));
        $this->assertSame('26.57', $this->sut->getCell(16, 4));
        $this->assertSame('', $this->sut->getCell(17, 0));
        $this->assertSame('Q', $this->sut->getCell(18, 3));
        $this->assertSame('A', $this->sut->getCell(23, 4));

        $this->sut = new XlsxObject(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'1 sheet with extreme cell index values.xlsx'));
        $this->assertInstanceOf(XlsxObject::class, $this->sut);
        $this->assertSame(14340, $this->sut->countCells());
        $this->assertSame(239, $this->sut->countRows());
        $this->assertSame(60, $this->sut->countColumns());
        $this->assertSame('A', $this->sut->getCell(0, 59));
        $this->assertSame('C', $this->sut->getCell(238, 59));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $c = new XlsxObject(123123); }, '/constructor expects a string value/');
        AssertUtils::throwsException(function() { $c = new XlsxObject('123123123'); }, '/Not a valid xlsx file/');
    }


    /**
     * setActiveSheet
     *
     * @return void
     */
    public function testSetActiveSheet(){

        // Test empty values
        $this->sut = new XlsxObject('');
        AssertUtils::throwsException(function() { $this->sut->setActiveSheet(); }, '/Too few arguments/');
        AssertUtils::throwsException(function() { $this->sut->setActiveSheet(null); }, '/null given/');
        AssertUtils::throwsException(function() { $this->sut->setActiveSheet(''); }, '/string given/');
        AssertUtils::throwsException(function() { $this->sut->setActiveSheet([]); }, '/array given/');
        $this->assertTrue($this->sut->setActiveSheet(0));
        $this->assertSame(0, $this->sut->countCells());

        // Test ok values
        $this->sut = new XlsxObject(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'2 sheets with 4 cells each.xlsx'));
        $this->assertSame(4, $this->sut->countCells());
        $this->assertSame('1', $this->sut->getCell(0, 0));
        $this->assertSame('2', $this->sut->getCell(0, 1));
        $this->assertSame('3', $this->sut->getCell(1, 0));
        $this->assertSame('4', $this->sut->getCell(1, 1));

        $this->assertTrue($this->sut->setActiveSheet(1));
        $this->assertSame(4, $this->sut->countCells());
        $this->assertSame('A', $this->sut->getCell(0, 0));
        $this->assertSame('B', $this->sut->getCell(0, 1));
        $this->assertSame('C', $this->sut->getCell(1, 0));
        $this->assertSame('D', $this->sut->getCell(1, 1));

        $this->assertTrue($this->sut->setActiveSheet(0));
        $this->assertSame(4, $this->sut->countCells());
        $this->assertSame('1', $this->sut->getCell(0, 0));
        $this->assertSame('2', $this->sut->getCell(0, 1));
        $this->assertSame('3', $this->sut->getCell(1, 0));
        $this->assertSame('4', $this->sut->getCell(1, 1));

        $this->sut = new XlsxObject(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'2 sheets with 2 and 4 cells each.xlsx'));
        $this->assertSame(2, $this->sut->countCells());
        $this->assertSame('1', $this->sut->getCell(0, 0));
        $this->assertSame('2', $this->sut->getCell(0, 1));

        $this->assertTrue($this->sut->setActiveSheet(1));
        $this->assertSame(4, $this->sut->countCells());
        $this->assertSame('A', $this->sut->getCell(0, 0));
        $this->assertSame('B', $this->sut->getCell(1, 0));
        $this->assertSame('C', $this->sut->getCell(2, 0));
        $this->assertSame('D', $this->sut->getCell(3, 0));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->setActiveSheet(3); }, '/Index 3 exceeds number of sheets/');
        AssertUtils::throwsException(function() { $this->sut->setActiveSheet(8); }, '/Index 8 exceeds number of sheets/');
        AssertUtils::throwsException(function() { $this->sut->setActiveSheet('43345'); }, '/Index 43345 exceeds number of sheets/');
        AssertUtils::throwsException(function() { $this->sut->setActiveSheet('asdf'); }, '/string given/');
    }


    /**
     * countSheets
     *
     * @return void
     */
    public function testCountSheets(){

        // Test empty values
        $this->sut = new XlsxObject('');
        $this->assertSame(0, $this->sut->countSheets());

        // Test ok values
        $this->sut = new XlsxObject(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'empty.xlsx'));
        $this->assertSame(1, $this->sut->countSheets());
        $this->sut = new XlsxObject(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'1 sheet with 1 cell.xlsx'));
        $this->assertSame(1, $this->sut->countSheets());
        $this->sut = new XlsxObject(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'2 sheets with 4 cells each.xlsx'));
        $this->assertSame(2, $this->sut->countSheets());
        $this->sut = new XlsxObject(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'9 sheets with 1 cell.xlsx'));
        $this->assertSame(9, $this->sut->countSheets());

        // Test wrong values
        // Not necessary

        // Test exceptions
        // Not necessary
    }


    /**
     * isXlsx
     *
     * @return void
     */
    public function testIsXlsx(){

        // Test empty values
        $this->assertFalse(XlsxObject::isXlsx(null));
        $this->assertTrue(XlsxObject::isXlsx(''));
        $this->assertFalse(XlsxObject::isXlsx(0));
        $this->assertFalse(XlsxObject::isXlsx([]));
        $this->assertFalse(XlsxObject::isXlsx(new stdClass()));
        $this->assertTrue(XlsxObject::isXlsx('     '));
        $this->assertTrue(XlsxObject::isXlsx("\n\n\n"));

        // Test ok values
        $this->assertFalse(XlsxObject::isXlsx('value'));
        $this->assertTrue(XlsxObject::isXlsx(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'empty.xlsx')));
        $this->assertTrue(XlsxObject::isXlsx(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'1 sheet with 1 cell.xlsx')));
        $this->assertTrue(XlsxObject::isXlsx(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'1 sheet with 4 cells.xlsx')));
        $this->assertTrue(XlsxObject::isXlsx(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'2 sheets with 2 and 4 cells each.xlsx')));
        $this->assertTrue(XlsxObject::isXlsx(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'2 sheets with 4 cells each.xlsx')));
        $this->assertTrue(XlsxObject::isXlsx(self::$filesManager->readFile(self::$basePath.DIRECTORY_SEPARATOR.'9 sheets with 1 cell.xlsx')));

        // Test wrong values
        $this->assertFalse(XlsxObject::isXlsx(12));
        $this->assertFalse(XlsxObject::isXlsx([1,4,5,6]));
        $this->assertFalse(XlsxObject::isXlsx(['  ']));
        $this->assertFalse(XlsxObject::isXlsx(new Exception()));
        $this->assertFalse(XlsxObject::isXlsx(-1909));

        // Test exceptions
        // Not necessary
    }
}