<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbosite\src\test\php\managers;

use PHPUnit\Framework\TestCase;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbodepot\src\main\php\managers\MarkDownManager;
use org\turbotesting\src\main\php\utils\AssertUtils;


/**
 * MarkDownManagerTest
 *
 * @return void
 */
class MarkDownManagerTest extends TestCase {


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(): void{

        $this->filesManager = new FilesManager();
        $this->sut = new MarkDownManager();
    }


    /**
     * test
     *
     * @return void
     */
    public function testConstruct(){

        $this->assertTrue($this->sut instanceof MarkDownManager);
    }


    /**
     * test
     *
     * @return void
     */
    public function testValidate(){

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
     * test
     *
     * @return void
     */
    public function testIsValid(){

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
     * test
     *
     * @return void
     */
    public function testToHtml(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->toHtml(null); }, '/must be of the type string, null given/');
        $this->assertSame('', $this->sut->toHtml(''));
        $this->assertSame('', $this->sut->toHtml('      '));
        $this->assertSame('', $this->sut->toHtml("\n\n\n\n"));

        // Test ok values
        $this->assertSame('<p>hello</p>', $this->sut->toHtml('hello'));
        $this->assertSame('<h1>hello</h1>', $this->sut->toHtml('# hello'));
        $this->assertSame('<h2>hello</h2>', $this->sut->toHtml('## hello'));
        // TODO - more cases

        // Test wrong values
        // Test exceptions
        // TODO
    }
}

?>