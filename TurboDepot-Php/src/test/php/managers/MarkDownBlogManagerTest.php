<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbosite\src\test\php\managers;

use PHPUnit\Framework\TestCase;
use org\turbodepot\src\main\php\managers\MarkDownBlogManager;
use org\turbodepot\src\main\php\managers\FilesManager;


/**
 * MarkDownBlogManagerTest
 *
 * @return void
 */
class MarkDownBlogManagerTest extends TestCase {


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

        $this->filesManager = new FilesManager();
        $this->tempFolder = $this->filesManager->createTempDirectory('TurboSitePhp-MarkDownBlogManagerTest');
        $this->sut = new MarkDownBlogManager($this->tempFolder);
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){

        $this->filesManager->deleteDirectory($this->tempFolder);
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

        $this->assertTrue($this->sut instanceof MarkDownBlogManager);
    }


    /**
     * testGetPost
     *
     * @return void
     */
    public function testGetPost(){

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
     * testGetLatestPosts
     *
     * @return void
     */
    public function testGetLatestPosts(){

        $this->filesManager->copyDirectory(__DIR__.'/../../resources/managers/markdownBlogManager', $this->tempFolder);

        // Test empty values
        // TODO

        // Test ok values
        $latestPosts = $this->sut->getLatestPosts('en', 10);

        $this->assertSame(4, count($latestPosts));

        $this->assertSame('Convert string to CamelCase, UpperCamelCase or LowerCamelCase in Javascript, typescript and Php',
            $latestPosts[0]->title);

        $this->assertSame('Blog post test 2', $latestPosts[1]->title);

        $this->assertSame('Blog post test 1', $latestPosts[2]->title);

        $this->assertSame('Blog post test 18/9/2014', $latestPosts[3]->title);
        // TODO - more tests

        // Test wrong values
        // TODO

        // Test exceptions
        // TODO
    }
}

?>