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
use org\turbodepot\src\main\php\managers\UsersManager;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbodepot\src\main\php\model\User;


/**
 * CacheManagerTest tests
 *
 * @return void
 */
class UsersManagerTest extends TestCase {


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

        $this->dbObjectsManager = DataBaseManagerTest::createAndConnectToTestingMariaDb();
        $this->dbObjectsManager->tablesPrefix = 'usr_';

        $this->db = $this->dbObjectsManager->getDataBaseManager();
        $this->sut = new UsersManager($this->dbObjectsManager);
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){

        DataBaseManagerTest::deleteAndDisconnectFromTestingMariaDb($this->dbObjectsManager);
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
     * test
     */
    public function testConstruct(){

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
     */
    public function testEncodeUserAndPassword(){

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
     */
    public function testDecodeUserName(){

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
     */
    public function testDecodePassword(){

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
     */
    public function testSave(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->save(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->save(null); }, '/Argument 1 passed to .* must be an instance of .*User/');
        AssertUtils::throwsException(function() { $this->sut->save(''); }, '/Argument 1 passed to .* must be an instance of .*User/');
        AssertUtils::throwsException(function() { $this->sut->save([]); }, '/Argument 1 passed to .* must be an instance of .*User/');

        // Test ok values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);
        $this->assertSame(['user'], $this->db->tableGetColumnValues('usr_user', 'username'));
        $this->assertSame(['psw'], $this->db->tableGetColumnValues('usr_user', 'password'));

        $user = new User();
        $user->userName = 'user2';
        $user->password = 'psw2';
        $this->sut->save($user);
        $this->assertSame(['user', 'user2'], $this->db->tableGetColumnValues('usr_user', 'username'));
        $this->assertSame(['psw', 'psw2'], $this->db->tableGetColumnValues('usr_user', 'password'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->save('string'); }, '/Argument 1 passed to .* must be an instance of .*User/');
        AssertUtils::throwsException(function() { $this->sut->save(1345345); }, '/Argument 1 passed to .* must be an instance of .*User/');
        AssertUtils::throwsException(function() { $this->sut->save([1,2,3,4,5]); }, '/Argument 1 passed to .* must be an instance of .*User/');
    }


    /**
     * test
     */
    public function testLogin(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->login(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->login('', ''); }, '/userName and password must have a value/');
        AssertUtils::throwsException(function() { $this->sut->login(null, null); }, '/Argument 1 passed to .*login.* must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->login([], []); }, '/Argument 1 passed to .*login.* must be of the type string, array given/');

        // Test ok values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);
        $login1Result = $this->sut->login('user', 'psw');
        $this->assertTrue(strlen($login1Result[0]) > 100);
        $this->assertSame('user', $login1Result[1]->userName);
        $this->assertSame('psw', $login1Result[1]->password);
        $this->assertSame(1, $login1Result[1]->getDbId());

        $user = new User();
        $user->userName = 'user2';
        $user->password = 'psw2';
        $this->sut->save($user);
        $login2Result = $this->sut->login('user2', 'psw2');
        $this->assertTrue(strlen($login2Result[0]) > 100);
        $this->assertSame('user2', $login2Result[1]->userName);
        $this->assertSame('psw2', $login2Result[1]->password);
        $this->assertSame(2, $login2Result[1]->getDbId());

        $this->assertSame([$login1Result[0], $login2Result[0]], $this->db->tableGetColumnValues('usr_token', 'token'));

        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues('usr_token', 'userdbid'));
        // TODO

        // Test wrong values
        $this->assertSame([], $this->sut->login('invalid user', 'invalid token'));
        // TODO

        // Test exceptions
        // TODO
    }


    /**
     * test
     */
    public function testLoginByToken(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->loginByToken(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->loginByToken(''); }, '/token must have a value/');
        AssertUtils::throwsException(function() { $this->sut->loginByToken(null); }, '/Argument 1 passed to .*loginByToken.* must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->loginByToken([]); }, '/Argument 1 passed to .*loginByToken.* must be of the type string, array given/');

        // Test ok values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);
        $token = $this->sut->login('user', 'psw')[0];
        $login1Result = $this->sut->loginByToken($token);
        $this->assertSame($login1Result[0], $token);
        $this->assertSame('user', $login1Result[1]->userName);
        $this->assertSame('psw', $login1Result[1]->password);
        $this->assertSame(1, $login1Result[1]->getDbId());

        $user = new User();
        $user->userName = 'user2';
        $user->password = 'psw2';
        $this->sut->save($user);
        $token = $this->sut->login('user2', 'psw2')[0];
        $login2Result = $this->sut->loginByToken($token);
        $this->assertSame($login2Result[0], $token);
        $this->assertSame('user2', $login2Result[1]->userName);
        $this->assertSame('psw2', $login2Result[1]->password);
        $this->assertSame(2, $login2Result[1]->getDbId());

        $this->assertSame([$login1Result[0], $login2Result[0]], $this->db->tableGetColumnValues('usr_token', 'token'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues('usr_token', 'userdbid'));
        // TODO

        // Test wrong values
        $this->assertSame([], $this->sut->loginByToken('invalid token'));
        // TODO

        // Test exceptions
        // TODO
    }


    // TODO - implement all missing tests
}

?>