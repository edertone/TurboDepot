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
use org\turbodepot\src\main\php\managers\MarkDownBlogManager;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbodepot\src\main\php\model\MarkDownBlogPostObject;
use org\turbotesting\src\main\php\utils\AssertUtils;


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

        // Copy all the test blog files to the created temporary folder
        $this->filesManager->copyDirectory(__DIR__.'/../../resources/managers/markdownBlogManager', $this->tempFolder);

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

        AssertUtils::throwsException(function() { $this->sut->unexistantProp = 1; }, '/property unexistantProp does not exist/');

        AssertUtils::throwsException(function() { new MarkDownBlogManager($this->tempFolder.'/nonexistant'); }, '/rootPath is not a valid directory:.*nonexistant/');
    }


    /**
     * testGetPost
     *
     * @return void
     */
    public function testGetPost(){

        // Test empty values
        $emptyTempFolder = $this->filesManager->createTempDirectory('TurboSitePhp-MarkDownBlogManagerTest-getPost');
        $sut2 = new MarkDownBlogManager($emptyTempFolder);
        AssertUtils::throwsException(function() use ($sut2) { $sut2->getPost('', '', ''); }, '/Invalid date/');
        AssertUtils::throwsException(function() use ($sut2) { $sut2->getPost('2018-10-25', '', ''); }, '/Could not find a blog post with the specified criteria/');
        AssertUtils::throwsException(function() use ($sut2) { $sut2->getPost('2018-10-25', 'en', 'conver'); }, '/Could not find a blog post with the specified criteria/');
        AssertUtils::throwsException(function() use ($sut2) { $sut2->getPost('2018-10-25', 'en', 'convert-string-to-camelcase-javascript-typescript-php'); }, '/Could not find a blog post with the specified criteria/');
         // TODO

        // Test ok values
        $this->assertTrue($this->sut->getPost('2018-10-25', 'en', 'convert-string-to-camelcase-javascript-typescript-php') instanceof MarkDownBlogPostObject);
        // TODO

        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut->getPost('1018-10-25', 'en', 'text'); }, '/Could not find a blog post with the specified criteria/');
        AssertUtils::throwsException(function() { $this->sut->getPost('1018-10-25', 'en', 'text', 0); }, '/Could not find a blog post with the specified criteria/');
        AssertUtils::throwsException(function() { $this->sut->getPost('1018345341025', 'en', 'text'); }, '/Invalid date/');
        AssertUtils::throwsException(function() { $this->sut->getPost('-10', 'en', 'text'); }, '/Invalid date/');
        AssertUtils::throwsException(function() { $this->sut->getPost('10183453410-25', 'en', 'text'); }, '/Invalid date/');
        AssertUtils::throwsException(function() { $this->sut->getPost('10183453410-25--', 'en', 'text'); }, '/Invalid date/');
        // TODO

        // Test exceptions
        // TODO
    }


    /**
     * testGetPost_metatitle_and_metadescription_are_correctly_computed
     *
     * @return void
     */
    public function testGetPost_metatitle_and_metadescription_are_correctly_computed(){

        $post = $this->sut->getPost('2018-10-25', 'en', 'convert-string-to-camelcase-javascript-typescript-php');
        $this->assertSame('Convert string to CamelCase, UpperCamelCase or LowerCamelCase in Javascript, typescript and Php', $post->title);
        $this->assertSame('convert string to camelcase javascript typescript php', $post->metaTitle);
        $this->assertSame('What is Camel Case Camel case conversion in TurboCommons library Convert a string to Camel Case in Php Convert a string to Camel Case in Javascrip ...',
            $post->metaDescription);

        $post = $this->sut->getPost('2020-06-05', 'en', 'pad-string-with-characters-javascript-typescript-php');
        $this->assertSame('Pad a string to a certain length with another string on Javascript, Typescript and Php', $post->title);
        $this->assertSame('Pad string certain length with another string Javascript, Typescript and Php', $post->metaTitle);
    }


    /**
     * testGetLatestPosts
     *
     * @return void
     */
    public function testGetLatestPosts(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->getLatestPosts('en', 0); }, '/count must be a positive integer/');

        $emptyTempFolder = $this->filesManager->createTempDirectory('TurboSitePhp-MarkDownBlogManagerTest-getLatestPosts');
        $sut2 = new MarkDownBlogManager($emptyTempFolder);
        $this->assertSame(0, count($sut2->getLatestPosts('en', 10)));
        // TODO - more empty values

        // Test ok values
        $latestPosts = $this->sut->getLatestPosts('en', 1);
        $this->assertSame(1, count($latestPosts));

        $this->assertSame('Pad a string to a certain length with another string on Javascript, Typescript and Php',
            $latestPosts[0]->title);

        $latestPosts = $this->sut->getLatestPosts('en', 10);
        $this->assertSame(6, count($latestPosts));

        $this->assertSame('Pad a string to a certain length with another string on Javascript, Typescript and Php',
            $latestPosts[0]->title);

        $this->assertSame('Convert string to CamelCase, UpperCamelCase or LowerCamelCase in Javascript, typescript and Php', $latestPosts[1]->title);

        $this->assertSame('another-blog-post-on-the-same-day', $latestPosts[2]->title);

        $this->assertSame('Blog post test 2', $latestPosts[3]->title);

        $this->assertSame('Blog post test 1', $latestPosts[4]->title);

        $this->assertSame('Blog post test 18/9/2014', $latestPosts[5]->title);
        // TODO - more ok tests

        // Test wrong values
        $this->filesManager->createDirectory($emptyTempFolder.'/2020/10/22', true);
        $this->assertSame(0, count($sut2->getLatestPosts('en', 10)));
        // TODO

        // Test exceptions
        // TODO
    }
}

?>