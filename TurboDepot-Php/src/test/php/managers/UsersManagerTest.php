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
        $user->mails = ['test@test.com', 'user2@user.com'];
        $this->sut->save($user);
        $this->assertSame(['user'], $this->db->tableGetColumnValues('usr_user', 'username'));
        $this->assertSame(['psw'], $this->db->tableGetColumnValues('usr_user', 'password'));
        $this->assertSame(['test@test.com', 'user2@user.com'], $this->db->tableGetColumnValues('usr_user_mails', 'value'));

        $user = new User();
        $user->userName = 'user2';
        $user->password = 'psw2';
        $this->sut->save($user);
        $this->assertSame(['user', 'user2'], $this->db->tableGetColumnValues('usr_user', 'username'));
        $this->assertSame(['psw', 'psw2'], $this->db->tableGetColumnValues('usr_user', 'password'));

        // Test wrong values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        AssertUtils::throwsException(function() use ($user) { $this->sut->save($user); }, '/Duplicate entry \'user\'/');

        $user = new User();
        $user->domain = 'different domain';
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->save('string'); }, '/Argument 1 passed to .* must be an instance of .*User/');
        AssertUtils::throwsException(function() { $this->sut->save(1345345); }, '/Argument 1 passed to .* must be an instance of .*User/');
        AssertUtils::throwsException(function() { $this->sut->save([1,2,3,4,5]); }, '/Argument 1 passed to .* must be an instance of .*User/');
    }


    /**
     * test
     */
    public function testIsUser(){

        // Test empty values
        // TODO

        // Test ok values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);
        $this->assertTrue($this->sut->isUser('user'));
        // TODO

        // Test wrong values
        $this->assertFalse($this->sut->isUser('non existant'));
        // TODO

        // Test exceptions
        // TODO

        $this->markTestIncomplete('This test has not been implemented yet.');
    }


    /**
     * test
     */
    public function testLogin(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->login(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->login(null, null); }, '/Argument 1 passed to .*login.* must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->login([], []); }, '/Argument 1 passed to .*login.* must be of the type string, array given/');

        $this->assertSame([], $this->sut->login('', ''));

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

        // Validate that a token is reused and recycled when performing several logins for the same user
        $this->sut->tokenLifeTime = 2;
        $token1 = $this->sut->login('user', 'psw')[0];
        $this->assertSame($token1, $login1Result[0]);
        $this->assertSame([$login1Result[0], $login2Result[0]], $this->db->tableGetColumnValues('usr_token', 'token'));
        sleep(1);
        $token1 = $this->sut->login('user', 'psw')[0];
        $this->assertSame($token1, $login1Result[0]);
        sleep(1);
        $token1 = $this->sut->login('user', 'psw')[0];
        $this->assertSame($token1, $login1Result[0]);
        sleep(1);
        $token1 = $this->sut->login('user', 'psw')[0];
        $this->assertSame($token1, $login1Result[0]);
        $this->assertTrue($this->sut->isTokenValid($token1));
        $token2 = $this->sut->login('user2', 'psw2')[0];
        $this->assertSame($token2, $login2Result[0]);
        $this->assertSame([$login1Result[0], $login2Result[0]], $this->db->tableGetColumnValues('usr_token', 'token'));

        // Test wrong values
        $this->assertSame([], $this->sut->login('invalid user', 'invalid token'));
        // TODO

        // Test exceptions
        // TODO
    }


    /**
     * test
     */
    public function testLoginFromEncodedCredentials(){

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
    public function testIsTokenValid(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->isTokenValid(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->isTokenValid(''); }, '/token must have a value/');
        AssertUtils::throwsException(function() { $this->sut->isTokenValid(null); }, '/Argument 1 passed to .*isTokenValid.* must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->isTokenValid([]); }, '/Argument 1 passed to .*isTokenValid.* must be of the type string, array given/');

        // Test ok values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);
        $token1 = $this->sut->login('user', 'psw')[0];
        $this->assertTrue($this->sut->isTokenValid($token1));

        $user = new User();
        $user->userName = 'user2';
        $user->password = 'psw2';
        $this->sut->save($user);
        $token2 = $this->sut->login('user2', 'psw2')[0];
        $this->assertTrue($this->sut->isTokenValid($token1));
        $this->assertTrue($this->sut->isTokenValid($token2));

        $this->assertSame([$token1, $token2], $this->db->tableGetColumnValues('usr_token', 'token'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues('usr_token', 'userdbid'));
        $this->assertRegExp('/....-..-.. ..:..:../', $this->db->tableGetColumnValues('usr_token', 'expires')[1]);
        // TODO

        // Test wrong values
        $this->assertFalse($this->sut->isTokenValid('invalid token'));

        // Validate that when a token expires it gets removed from db
        $this->sut->tokenLifeTime = 2;
        $this->assertSame($token1, $this->sut->login('user', 'psw')[0]);
        $this->assertTrue($this->sut->isTokenValid($token1));
        $this->assertSame($token1, $this->db->tableGetColumnValues('usr_token', 'token')[0]);
        sleep(2);
        $this->assertSame($token1, $this->db->tableGetColumnValues('usr_token', 'token')[0]);
        $this->assertFalse($this->sut->isTokenValid($token1));
        $this->assertSame([$token2], $this->db->tableGetColumnValues('usr_token', 'token'));

        // Validate that a token expiry time gets recycled when it gets correctly validated, and then when it
        // expires it gets removed from db
        $token3 = $this->sut->login('user', 'psw')[0];
        $this->assertNotSame($token1, $token3);
        $this->assertTrue($this->sut->isTokenValid($token3));
        $this->assertSame($token3, $this->db->tableGetColumnValues('usr_token', 'token')[1]);
        sleep(1);
        $this->assertTrue($this->sut->isTokenValid($token3));
        $this->assertSame($token3, $this->db->tableGetColumnValues('usr_token', 'token')[1]);
        sleep(1);
        $this->assertTrue($this->sut->isTokenValid($token3));
        $this->assertSame($token3, $this->db->tableGetColumnValues('usr_token', 'token')[1]);
        sleep(2);
        $this->assertSame($token3, $this->db->tableGetColumnValues('usr_token', 'token')[1]);
        $this->assertFalse($this->sut->isTokenValid($token3));
        $this->assertSame([$token2], $this->db->tableGetColumnValues('usr_token', 'token'));

        // Validate that a token expiry time does not get recycled when it gets correctly validated if token recycle is false
        $this->sut->isTokenLifeTimeRecycled = false;
        $token4 = $this->sut->login('user', 'psw')[0];
        $this->assertTrue($this->sut->isTokenValid($token4));
        $this->assertSame($token4, $this->db->tableGetColumnValues('usr_token', 'token')[1]);
        sleep(1);
        $this->assertTrue($this->sut->isTokenValid($token4));
        $this->assertSame($token4, $this->db->tableGetColumnValues('usr_token', 'token')[1]);
        sleep(1);
        $this->assertSame($token4, $this->db->tableGetColumnValues('usr_token', 'token')[1]);
        $this->assertFalse($this->sut->isTokenValid($token4));
        $this->assertSame([$token2], $this->db->tableGetColumnValues('usr_token', 'token'));

        // TODO

        // Test exceptions
        // TODO
    }


    /**
     * test
     */
    public function testLogout(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->logout(); }, '/Too few arguments to function/');
        $this->assertFalse($this->sut->logout(null));
        $this->assertFalse($this->sut->logout(''));
        $this->assertFalse($this->sut->logout([]));

        // Test ok values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);

        $user = new User();
        $user->userName = 'user2';
        $user->password = 'psw2';
        $this->sut->save($user);

        $token = $this->sut->login('user', 'psw')[0];
        $this->assertSame([$token], $this->db->tableGetColumnValues('usr_token', 'token'));
        $this->assertTrue($this->sut->logout($token));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_token', 'token'));

        $this->sut->tokenLifeTime = 2;

        $token1 = $this->sut->login('user', 'psw')[0];
        $token2 = $this->sut->login('user2', 'psw2')[0];

        $this->assertSame([$token1, $token2], $this->db->tableGetColumnValues('usr_token', 'token'));
        sleep(3);

        $this->assertTrue($this->sut->logout($token1));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_token', 'token'));

        // Test wrong values
        $this->assertFalse($this->sut->logout('invalidtoken'));

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->logout(123123); }, '/value is not a string/');
        AssertUtils::throwsException(function() { $this->sut->logout([1,2,3]); }, '/value is not a string/');
    }
}

?>