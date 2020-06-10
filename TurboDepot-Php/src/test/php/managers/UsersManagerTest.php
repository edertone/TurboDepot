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
    public function testSaveUserMail(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->saveUserMail(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail(null, null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('', ''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('', '', ''); }, '/Trying to add an email account to a non existing user:  on domain $/');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('', '', []); }, '/must be of the type string, array given/');

        // Test ok values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);
        $this->sut->saveUserMail('user', '', 'test@email.com');
        $this->assertSame([['userdbid' => '1', 'mail' => 'test@email.com', 'isverified' => '0', 'comments' => '', 'data' => '']],
            $this->db->tableGetRows('usr_user_mail', ['userdbid' => 1]));

        $this->sut->saveUserMail('user', '', 'test@email.com', '', 'data1');
        $this->assertSame(1, count($this->db->tableGetRows('usr_user_mail', ['userdbid' => 1])));

        $this->sut->saveUserMail('user', '', 'test2@email2.com', 'comments2');
        $this->assertSame([
            ['userdbid' => '1', 'mail' => 'test2@email2.com', 'isverified' => '0', 'comments' => 'comments2', 'data' => ''],
            ['userdbid' => '1', 'mail' => 'test@email.com', 'isverified' => '0', 'comments' => '', 'data' => 'data1']
        ], $this->db->tableGetRows('usr_user_mail', ['userdbid' => 1]));

        $this->sut->saveUserMail('user', '', 'test2@email2.com');
        $this->assertSame(2, count($this->db->tableGetRows('usr_user_mail', ['userdbid' => 1])));

        $this->sut->saveUserMail('user', '', 'test3@email3.com', 'comments3', 'data3');
        $this->assertSame([
            ['userdbid' => '1', 'mail' => 'test2@email2.com', 'isverified' => '0', 'comments' => '', 'data' => ''],
            ['userdbid' => '1', 'mail' => 'test3@email3.com', 'isverified' => '0', 'comments' => 'comments3', 'data' => 'data3'],
            ['userdbid' => '1', 'mail' => 'test@email.com', 'isverified' => '0', 'comments' => '', 'data' => 'data1']
        ], $this->db->tableGetRows('usr_user_mail', ['userdbid' => 1]));

        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('nonexistantuser', '', 'test@email.com', 'comments', 'data'); }, '/Trying to add an email account to a non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('user', 'nonexistantdomain', 'test@email.com'); }, '/Trying to add an email account to a non existing user: user on domain nonexistantdomain/');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('user', '', ''); }, '/Invalid mail/');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('user', '', '         '); }, '/Invalid mail/');
        // TODO

        // Test exceptions
        // TODO
    }


    /**
     * test
     */
    public function testSendUserMailVerification(){

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
    public function testIsUserMailVerified(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified(null, null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified('', ''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified('', '', ''); }, '/Invalid mail/');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified('', '', []); }, '/must be of the type string, array given/');

        // Test ok values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);
        $this->sut->saveUserMail('user', '', 'test@email.com');
        $this->assertFalse($this->sut->isUserMailVerified('user', '', 'test@email.com'));
        // TODO

        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified('nonexistantuser', '', 'nonexistantmail'); }, '/Non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified('', '', 'nonexistantmail'); }, '/Non existing user:  on domain /');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified('user', '', 'nonexistantmail'); }, '/Non existing mail: nonexistantmail on user: user on domain /');
        // TODO

        // Test exceptions
        // TODO
    }


    /**
     * test
     */
    public function testSetUserMailVerified(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified(null, null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified('', ''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified('', '', '', false); }, '/Invalid mail/');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified('', '', []); }, '/must be of the type string, array given/');

        // Test ok values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);
        $this->sut->saveUserMail('user', '', 'test@email.com');

        $this->assertFalse($this->sut->isUserMailVerified('user', '', 'test@email.com'));
        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test@email.com', true));
        $this->assertTrue($this->sut->isUserMailVerified('user', '', 'test@email.com'));

        $user = new User();
        $user->userName = 'user2';
        $user->password = 'psw2';
        $this->sut->save($user);
        $this->sut->saveUserMail('user2', '', 'test2@email2.com');

        $this->assertFalse($this->sut->isUserMailVerified('user2', '', 'test2@email2.com'));
        $this->assertTrue($this->sut->setUserMailVerified('user2', '', 'test2@email2.com', true));
        $this->assertTrue($this->sut->isUserMailVerified('user', '', 'test@email.com'));
        $this->assertTrue($this->sut->isUserMailVerified('user2', '', 'test2@email2.com'));

        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test@email.com', false));
        $this->assertTrue($this->sut->setUserMailVerified('user2', '', 'test2@email2.com', false));
        $this->assertFalse($this->sut->isUserMailVerified('user', '', 'test@email.com'));
        $this->assertFalse($this->sut->isUserMailVerified('user2', '', 'test2@email2.com'));

        $this->assertTrue($this->sut->setUserMailVerified('user2', '', 'test2@email2.com', false));
        $this->assertFalse($this->sut->isUserMailVerified('user2', '', 'test2@email2.com'));

        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified('nonexistantuser', '', 'nonexistantmail', true); }, '/Non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified('', '', 'nonexistantmail', true); }, '/Non existing user:  on domain /');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified('user', '', 'nonexistantmail', true); }, '/Non existing mail: nonexistantmail on user: user on domain /');
        // TODO

        // Test exceptions
        // TODO
    }


    /**
     * test
     */
    public function testGetUserMails(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->getUserMails(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->getUserMails(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->getUserMails(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->getUserMails(null, null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->getUserMails(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->getUserMails('', ''); }, '/Non existing user:  on domain /');
        AssertUtils::throwsException(function() { $this->sut->getUserMails('', '', ''); }, '/filter must be VERIFIED, NONVERIFIED or ALL/');
        AssertUtils::throwsException(function() { $this->sut->getUserMails('', '', []); }, '/filter must be VERIFIED, NONVERIFIED or ALL/');

        // Test ok values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);
        $this->sut->saveUserMail('user', '', 'test@email.com');
        $this->sut->saveUserMail('user', '', 'test2@email2.com');
        $this->sut->saveUserMail('user', '', 'test3@email3.com');

        // Get verified and non verified emails
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => false], ['mail' => 'test3@email3.com', 'isverified' => false],
            ['mail' => 'test@email.com', 'isverified' => false]], $this->sut->getUserMails('user', ''));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => false], ['mail' => 'test3@email3.com', 'isverified' => false],
            ['mail' => 'test@email.com', 'isverified' => false]], $this->sut->getUserMails('user', '', 'NONVERIFIED'));

        // get only verified emails
        $this->assertSame([], $this->sut->getUserMails('user', '', 'VERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test2@email2.com', true));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true]], $this->sut->getUserMails('user', '', 'VERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test3@email3.com', true));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true], ['mail' => 'test3@email3.com', 'isverified' => true]],
            $this->sut->getUserMails('user', '', 'VERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test@email.com', true));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true], ['mail' => 'test3@email3.com', 'isverified' => true],
            ['mail' => 'test@email.com', 'isverified' => true]], $this->sut->getUserMails('user', '', 'VERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test@email.com', false));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true], ['mail' => 'test3@email3.com', 'isverified' => true]],
            $this->sut->getUserMails('user', '', 'VERIFIED'));

        // Get only non verified emails
        $this->assertSame([['mail' => 'test@email.com', 'isverified' => false]], $this->sut->getUserMails('user', '', 'NONVERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test2@email2.com', false));
        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test3@email3.com', false));
        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test@email.com', false));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => false], ['mail' => 'test3@email3.com', 'isverified' => false],
            ['mail' => 'test@email.com', 'isverified' => false]], $this->sut->getUserMails('user', '', 'NONVERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test2@email2.com', true));
        $this->assertSame([['mail' => 'test3@email3.com', 'isverified' => false], ['mail' => 'test@email.com', 'isverified' => false]],
            $this->sut->getUserMails('user', '', 'NONVERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test3@email3.com', true));
        $this->assertSame([['mail' => 'test@email.com', 'isverified' => false]], $this->sut->getUserMails('user', '', 'NONVERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', '', 'test@email.com', true));
        $this->assertSame([], $this->sut->getUserMails('user', '', 'NONVERIFIED'));

        // Check that emails are not mixed between users
        $user = new User();
        $user->userName = 'user2';
        $user->password = 'psw2';
        $this->sut->save($user);
        $this->sut->saveUserMail('user2', '', 'user@email.com');
        $this->sut->saveUserMail('user2', '', 'user2@email2.com');
        $this->sut->saveUserMail('user2', '', 'user3@email3.com');
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true], ['mail' => 'test3@email3.com', 'isverified' => true],
            ['mail' => 'test@email.com', 'isverified' => true]], $this->sut->getUserMails('user', ''));
        $this->assertSame([['mail' => 'user2@email2.com', 'isverified' => false], ['mail' => 'user3@email3.com', 'isverified' => false],
            ['mail' => 'user@email.com', 'isverified' => false]], $this->sut->getUserMails('user2', ''));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->getUserMails('', '', 'INVALIDFILTER'); }, '/filter must be VERIFIED, NONVERIFIED or ALL/');
        AssertUtils::throwsException(function() { $this->sut->getUserMails('nonexistantuser', ''); }, '/Non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->getUserMails('', '', 'ALL'); }, '/Non existing user:  on domain /');
    }


    /**
     * test
     */
    public function testDeleteUserMails(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails(null, null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('', ''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('', '', ''); }, '/must be of the type array, string given/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('', '', []); }, '/Non existing user:  on domain /');

        // Test ok values
        $user = new User();
        $user->userName = 'user';
        $user->password = 'psw';
        $this->sut->save($user);
        $this->sut->saveUserMail('user', '', 'test@email.com');
        $this->sut->saveUserMail('user', '', 'test2@email2.com');
        $this->sut->saveUserMail('user', '', 'test3@email3.com');
        $this->assertSame(3, count($this->sut->getUserMails('user', '')));
        $this->assertSame(1, $this->sut->deleteUserMails('user', '', ['test@email.com']));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => false], ['mail' => 'test3@email3.com', 'isverified' => false]],
            $this->sut->getUserMails('user', ''));
        $this->assertSame(1, $this->sut->deleteUserMails('user', '', ['test2@email2.com']));
        $this->assertSame([['mail' => 'test3@email3.com', 'isverified' => false]], $this->sut->getUserMails('user', ''));
        $this->assertSame(1, $this->sut->deleteUserMails('user', '', ['test3@email3.com']));
        $this->assertSame([], $this->sut->getUserMails('user', ''));

        $this->sut->saveUserMail('user', '', 'test@email.com');
        $this->sut->saveUserMail('user', '', 'test2@email2.com');
        $this->sut->saveUserMail('user', '', 'test3@email3.com');
        $this->assertSame(3, count($this->sut->getUserMails('user', '')));
        $this->assertSame(3, $this->sut->deleteUserMails('user', '', ['test2@email2.com', 'test3@email3.com', 'test@email.com']));
        $this->assertSame([], $this->sut->getUserMails('user', ''));

        // Test wrong values
        $this->assertSame(0, $this->sut->deleteUserMails('user', '', ['nonexistant@mail.com']));
        $this->assertSame(0, $this->sut->deleteUserMails('user', '', ['nonexistant@mail.com', 'nomail']));
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('user', '', [1234, 'nonexistant@mail.com', 'nomail']); }, '/Invalid mail: 1234/');

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('', '', 123); }, '/must be of the type array, int given/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('nonexistantuser', '', []); }, '/Non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('', '', []); }, '/Non existing user:  on domain /');
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
        $this->sut->tokenLifeTime = 3;
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