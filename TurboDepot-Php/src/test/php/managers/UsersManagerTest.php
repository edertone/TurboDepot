<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\test\php\managers;

use PHPUnit\Framework\TestCase;
use org\turbodepot\src\main\php\managers\UsersManager;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbodepot\src\main\php\model\UserObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;


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

        $this->dbObjectsManager = DataBaseManager_MariaDb_Test::createAndConnectToTestingMariaDb();
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

        DataBaseManager_MariaDb_Test::deleteAndDisconnectFromTestingMariaDb($this->dbObjectsManager);
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
        $this->assertSame(1, $this->db->tableCountRows('usr_domain'));
        $this->assertSame(['The default root users domain'], $this->db->tableGetColumnValues('usr_domain', 'description'));

        $this->sut->saveDomain('domain1', '');
        $this->sut = new UsersManager($this->dbObjectsManager, 'domain1');

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { new UsersManager($this->dbObjectsManager, 'nonexistantdomain'); }, '/Domain does not exist nonexistantdomain/');
        AssertUtils::throwsException(function() { new UsersManager(new DataBaseObjectsManager()); }, '/No active connection to database available for the provided DataBaseObjectsManager/');
        // TODO
    }


    /** test */
    public function testSaveDomain(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->saveDomain(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->saveDomain(null, null); }, '/domainName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveDomain([], []); }, '/domainName must be a non empty string/');
        $this->assertTrue($this->sut->saveDomain('', 'default domain'));

        // Test ok values
        $this->assertSame(1, $this->db->tableCountRows('usr_domain'));
        $this->assertTrue($this->db->tableExists('usr_domain'));
        $this->assertTrue($this->sut->saveDomain('domain1', ''));
        $this->assertTrue($this->db->tableExists('usr_domain'));
        $this->assertSame(['', 'domain1'], $this->db->tableGetColumnValues('usr_domain', 'name'));
        $this->assertSame(['default domain', ''], $this->db->tableGetColumnValues('usr_domain', 'description'));

        $this->assertTrue($this->sut->saveDomain('domain1', 'description1'));
        $this->assertSame(['', 'domain1'], $this->db->tableGetColumnValues('usr_domain', 'name'));
        $this->assertSame(['default domain', 'description1'], $this->db->tableGetColumnValues('usr_domain', 'description'));

        $this->assertTrue($this->sut->saveDomain('domain1', 'description1-edited'));
        $this->assertSame(['default domain', 'description1-edited'], $this->db->tableGetColumnValues('usr_domain', 'description'));

        $this->assertTrue($this->sut->saveDomain('domain2', ''));
        $this->assertTrue($this->sut->saveDomain('domain3', ''));
        $this->assertSame(['', 'domain1', 'domain2', 'domain3'], $this->db->tableGetColumnValues('usr_domain', 'name'));
        $this->assertSame(['default domain', 'description1-edited', '', ''], $this->db->tableGetColumnValues('usr_domain', 'description'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->saveDomain('   ', ''); }, '/domainName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveDomain(1345345, ''); }, '/domainName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveDomain([1,2,3,4,5], ''); }, '/domainName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveDomain('domain1', 12345); }, '/description must be a string/');
        AssertUtils::throwsException(function() { $this->sut->saveDomain('domain1', [1,2,3,4,5]); }, '/description must be a string/');
    }


    /** test */
    public function testIsDomain(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->isDomain(); }, '/Too few arguments to function/');
        $this->assertTrue($this->sut->isDomain(''));
        AssertUtils::throwsException(function() { $this->sut->isDomain(null); }, '/domainName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->isDomain([]); }, '/domainName must be a non empty string/');

        // Test ok values
        $this->assertTrue($this->sut->saveDomain('D1', ''));
        $this->assertTrue($this->sut->saveDomain('D2', ''));
        $this->assertTrue($this->sut->saveDomain('D3', ''));
        $this->assertTrue($this->sut->saveDomain('D3/sub', ''));
        $this->assertTrue($this->sut->isDomain(''));
        $this->assertTrue($this->sut->isDomain('D1'));
        $this->assertTrue($this->sut->isDomain('D2'));
        $this->assertTrue($this->sut->isDomain('D3'));
        $this->assertTrue($this->sut->isDomain('D3/sub'));

        // Test wrong values
        $this->assertFalse($this->sut->isDomain('nonexistant1'));
        $this->assertFalse($this->sut->isDomain('nonexistant2'));

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->isDomain(1234); }, '/domainName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->isDomain([1234]); }, '/domainName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->isDomain('         '); }, '/domainName must be a non empty string/');
    }


    /** test */
    public function testSetDomain(){

        // Test empty values
        $this->assertSame('', $this->sut->setDomain(''));
        AssertUtils::throwsException(function() { $this->sut->setDomain(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->setDomain(null); }, '/domainName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->setDomain([]); }, '/domainName must be a non empty string/');

        // Test ok values
        $this->assertTrue($this->sut->saveDomain('D1', ''));
        $this->assertTrue($this->sut->saveDomain('D2', ''));
        $this->assertTrue($this->sut->saveDomain('D3', ''));
        $this->assertTrue($this->sut->saveDomain('D3/sub', ''));
        $this->assertSame('', $this->sut->setDomain(''));
        $this->assertSame('D1', $this->sut->setDomain('D1'));
        $this->assertSame('D2', $this->sut->setDomain('D2'));
        $this->assertSame('D3', $this->sut->setDomain('D3'));
        $this->assertSame('D3/sub', $this->sut->setDomain('D3/sub'));

        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut->setDomain('nonexistant1'); }, '/Domain does not exist nonexistant1/');
        AssertUtils::throwsException(function() { $this->sut->setDomain('nonexistant2'); }, '/Domain does not exist nonexistant2/');

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->setDomain(1234); }, '/domainName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->setDomain([1234]); }, '/domainName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->setDomain('         '); }, '/domainName must be a non empty string/');
    }


    /** test */
    public function testSaveRole(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->saveRole(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->saveRole(null, null); }, '/roleName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveRole([], []); }, '/roleName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveRole('', ''); }, '/roleName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveRole('   ', ''); }, '/roleName must be a non empty string/');

        // Test ok values
        $this->assertFalse($this->db->tableExists('usr_role'));
        $this->assertTrue($this->sut->saveRole('role1', ''));
        $this->assertTrue($this->db->tableExists('usr_role'));
        $this->assertSame([''], $this->db->tableGetColumnValues('usr_role', 'domain'));
        $this->assertSame(['role1'], $this->db->tableGetColumnValues('usr_role', 'name'));
        $this->assertSame([''], $this->db->tableGetColumnValues('usr_role', 'description'));

        $this->sut->saveDomain('new domain', '');
        $this->sut->setDomain('new domain');
        $this->assertTrue($this->sut->saveRole('role2', 'description 2'));
        $this->assertSame(['', 'new domain'], $this->db->tableGetColumnValues('usr_role', 'domain'));
        $this->assertSame(['role1', 'role2'], $this->db->tableGetColumnValues('usr_role', 'name'));
        $this->assertSame(['', 'description 2'], $this->db->tableGetColumnValues('usr_role', 'description'));

        $this->assertTrue($this->sut->saveRole('role2', 'description 2 edited'));
        $this->assertSame(['', 'description 2 edited'], $this->db->tableGetColumnValues('usr_role', 'description'));

        //         // Test wrong values
        //         // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->saveRole('   ', ''); }, '/roleName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveRole(1345345, ''); }, '/roleName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveRole([1,2,3,4,5], ''); }, '/roleName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveRole('domain1', 12345); }, '/description must be a string/');
        AssertUtils::throwsException(function() { $this->sut->saveRole('domain1', [1,2,3,4,5]); }, '/description must be a string/');
    }


    /** test */
    public function testIsRole(){

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


    /** test */
    public function testDeleteRole(){

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
    public function testSaveUser(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->saveUser(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->saveUser(null); }, '/Argument 1 passed to .* must be an instance of .*User/');
        AssertUtils::throwsException(function() { $this->sut->saveUser(''); }, '/Argument 1 passed to .* must be an instance of .*User/');
        AssertUtils::throwsException(function() { $this->sut->saveUser([]); }, '/Argument 1 passed to .* must be an instance of .*User/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->assertSame(['user'], $this->db->tableGetColumnValues('usr_userobject', 'username'));

        $user = new UserObject();
        $user->userName = 'user2';
        $this->sut->saveUser($user);
        $this->assertSame(['user', 'user2'], $this->db->tableGetColumnValues('usr_userobject', 'username'));

        // Test wrong values
        $user = new UserObject();
        AssertUtils::throwsException(function() use ($user) { $this->sut->saveUser($user); }, '/no user name specified/');

        $user = new UserObject();
        $user->userName = 'user';
        AssertUtils::throwsException(function() use ($user) { $this->sut->saveUser($user); }, '/User user already exists on domain/');

        $user = new UserObject();
        $user->domain = 'different domain';
        $user->userName = 'user';
        $user->description = 'user description';
        AssertUtils::throwsException(function() use ($user) { $this->sut->saveUser($user); }, '/Saving a user with a domain .different domain. that doesn\'t match the current one ../');
        AssertUtils::throwsException(function() use ($user) { $this->sut->setDomain('different domain'); }, '/Domain does not exist different domain/');

        $this->sut->saveDomain('different domain', '');
        $this->sut->setDomain('different domain');
        $this->sut->saveUser($user);

        $this->sut->setDomain('');
        $user = new UserObject();
        $user->userName = 'user3';
        $user->roles = 'hello';
        AssertUtils::throwsException(function() use ($user) { $this->sut->saveUser($user); }, '/roles must be an array/');

        $user->roles = ['hello'];
        AssertUtils::throwsException(function() use ($user) { $this->sut->saveUser($user); }, '/role hello does not exist on domain /');

        $user = new UserObject();
        $user->userName = 'user';
        AssertUtils::throwsException(function() use ($user) { $this->sut->saveUser($user); }, '/User user already exists on domain /');

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->saveUser('string'); }, '/Argument 1 passed to .* must be an instance of .*User/');
        AssertUtils::throwsException(function() { $this->sut->saveUser(1345345); }, '/Argument 1 passed to .* must be an instance of .*User/');
        AssertUtils::throwsException(function() { $this->sut->saveUser([1,2,3,4,5]); }, '/Argument 1 passed to .* must be an instance of .*User/');
    }


    /** test */
    public function testSaveUser_table_does_not_exist_and_is_created(){

        $this->assertFalse($this->db->tableExists('usr_userobject'));

        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->assertTrue($this->db->tableExists('usr_userobject'));
    }


    /** test */
    public function testSaveUser_table_exists_with_one_column_and_missing_columns_are_created(){

        AssertUtils::throwsException(function() { $this->db->tableCreate('usr_userobject', []); }, '/at least one column is expected/');
        $this->db->tableCreate('usr_userobject', ['dbid bigint unsigned NOT NULL AUTO_INCREMENT'], ['dbid']);
        $this->assertTrue($this->db->tableExists('usr_userobject'));
        $this->assertFalse(in_array('username', $this->db->tableGetColumnNames('usr_userobject'), true));

        $user = new UserObject();
        $user->userName = 'user';
        $this->assertSame(1, $this->sut->saveUser($user));
        $this->assertTrue($this->db->tableExists('usr_userobject'));
        $this->assertTrue(in_array('username', $this->db->tableGetColumnNames('usr_userobject'), true));
        $this->assertSame(9, count($this->db->tableGetColumnNames('usr_userobject')));
    }


    /** test */
    public function testSaveUser_table_exists_with_more_columns_than_expected_and_error_is_thrown(){

        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->assertTrue($this->db->tableExists('usr_userobject'));
        $this->assertTrue($this->db->tableAddColumn('usr_userobject', 'extraone', $this->db->getSQLTypeFromValue('hello')));

        AssertUtils::throwsException(function() use ($user) { $this->sut->saveUser($user); },
            '/<usr_userobject> table contains a column which must exist as a basic property on object being saved: .*<extraone> exists on <usr_userobject> but not on provided tableDef.*/');
    }


    /** test */
    public function testIsUser(){

        // Test empty values
        // TODO

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->assertTrue($this->sut->isUser('user'));
        // TODO

        // Test wrong values
        $this->assertFalse($this->sut->isUser('non existant'));
        // TODO

        // Test exceptions
        // TODO
    }


    /** test */
    public function testSetUserPassword(){

        $tableName = $this->dbObjectsManager->tablesPrefix.'userobject_password';

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->setUserPassword(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->setUserPassword(null); }, '/Argument 1 passed to .* must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->setUserPassword(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->setUserPassword([]); }, '/Argument 1 passed to .* must be of the type string, array given/');
        AssertUtils::throwsException(function() { $this->sut->setUserPassword('', null); }, '/Argument 2 passed to .* must be of the type string, null given/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->assertTrue($this->sut->setUserPassword($user->userName, 'psw'));

        $user = new UserObject();
        $user->userName = 'user2';
        $this->sut->saveUser($user);
        $this->assertTrue($this->sut->setUserPassword($user->userName, 'psw2'));
        $this->assertSame(2, $this->db->tableCountRows($tableName));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->setUserPassword('nonexistantuser', 'psw'); }, '/Non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->setUserPassword(12341234, 'psw'); }, '/Non existing user: 12341234 on domain/');
        AssertUtils::throwsException(function() { $this->sut->setUserPassword('user', [345345]); }, '/Argument 2 .* must be of the type string, array given/');

        // Create a table with missing columns for the user passwords and verify that all missing are created
        $this->db->tableDelete($tableName);
        $this->db->tableCreate($tableName, ['dbid bigint unsigned NOT NULL'], ['dbid']);
        $this->assertFalse(in_array('password', $this->db->tableGetColumnNames($tableName), true));

        $this->sut->setUserPassword($user->userName, 'psw');
        $this->assertSame(2, count($this->db->tableGetColumnNames($tableName)));
        $this->assertTrue(in_array('password', $this->db->tableGetColumnNames($tableName), true));
    }


    /** test */
    public function testSetUserPassword_with_empy_db_and_without_having_saved_the_user(){

        $user = new UserObject();
        $user->userName = 'user';
        AssertUtils::throwsException(function() use ($user) { $this->sut->setUserPassword($user->userName, 'psw'); }, '/Non existing user: user on domain/');

        $this->sut->saveUser($user);
        $this->assertTrue($this->sut->setUserPassword($user->userName, 'psw'));
    }


    /** test */
    public function testSetUserPassword_with_a_table_that_only_contains_a_userdbid_column_instead_of_dbid(){

        // Create a table with missing the wrong userdbid column
        $tableName = $this->dbObjectsManager->tablesPrefix.'userobject_password';
        $this->db->tableCreate($tableName, ['userdbid bigint unsigned NOT NULL'], ['userdbid']);

        $user = new UserObject();
        $user->userName = 'user';
        AssertUtils::throwsException(function() use ($user) { $this->sut->setUserPassword($user->userName, 'psw'); }, '/Non existing user: user on domain/');

        $this->assertSame(1, $this->sut->saveUser($user));

        AssertUtils::throwsException(function() use ($user) { $this->assertTrue($this->sut->setUserPassword($user->userName, 'psw')); },
            '/<userdbid> exists on <usr_userobject_password> but not on provided tableDef/');
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
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('', ''); }, '/Trying to add an email account to a non existing user:  on domain /');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('', '', ''); }, '/Trying to add an email account to a non existing user:  on domain $/');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('', '', []); }, '/must be of the type string, array given/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user', 'test@email.com');
        $this->assertSame([['dbid' => '1', 'mail' => 'test@email.com', 'isverified' => '0', 'comments' => '', 'data' => '']],
            $this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1]));

        $this->sut->saveUserMail('user', 'test@email.com', '', 'data1');
        $this->assertSame(1, count($this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1])));

        $this->sut->saveUserMail('user', 'test2@email2.com', 'comments2');
        $this->assertSame([
            ['dbid' => '1', 'mail' => 'test2@email2.com', 'isverified' => '0', 'comments' => 'comments2', 'data' => ''],
            ['dbid' => '1', 'mail' => 'test@email.com', 'isverified' => '0', 'comments' => '', 'data' => 'data1']
        ], $this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1]));

        $this->sut->saveUserMail('user', 'test2@email2.com');
        $this->assertSame(2, count($this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1])));

        $this->sut->saveUserMail('user', 'test3@email3.com', 'comments3', 'data3');
        $this->assertSame([
            ['dbid' => '1', 'mail' => 'test2@email2.com', 'isverified' => '0', 'comments' => '', 'data' => ''],
            ['dbid' => '1', 'mail' => 'test3@email3.com', 'isverified' => '0', 'comments' => 'comments3', 'data' => 'data3'],
            ['dbid' => '1', 'mail' => 'test@email.com', 'isverified' => '0', 'comments' => '', 'data' => 'data1']
        ], $this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1]));

        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('nonexistantuser', 'test@email.com', 'comments', 'data'); }, '/Trying to add an email account to a non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('user', ''); }, '/Invalid mail/');
        AssertUtils::throwsException(function() { $this->sut->saveUserMail('user', '         '); }, '/Invalid mail/');
        AssertUtils::throwsException(function() { (new UsersManager($this->dbObjectsManager, 'nonexistantdomain'))->saveUserMail('user', 'test@email.com'); }, '/Domain does not exist nonexistantdomain/');
        AssertUtils::throwsException(function() { $this->sut->saveDomain('nonexistantdomain', ''); (new UsersManager($this->dbObjectsManager, 'nonexistantdomain'))->saveUserMail('user', 'test@email.com'); }, '/Trying to add an email account to a non existing user: user on domain nonexistantdomain/');

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
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified('', ''); }, '/Invalid mail/');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified('', []); }, '/must be of the type string, array given/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user', 'test@email.com');
        $this->assertFalse($this->sut->isUserMailVerified('user', 'test@email.com'));
        // TODO

        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified('nonexistantuser', 'nonexistantmail'); }, '/Non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified('', 'nonexistantmail'); }, '/Non existing user:  on domain /');
        AssertUtils::throwsException(function() { $this->sut->isUserMailVerified('user', 'nonexistantmail'); }, '/Non existing mail: nonexistantmail on user: user on domain /');
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
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified('', '', false); }, '/Invalid mail/');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified('', []); }, '/must be of the type string, array given/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user', 'test@email.com');

        $this->assertFalse($this->sut->isUserMailVerified('user', 'test@email.com'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test@email.com', true));
        $this->assertTrue($this->sut->isUserMailVerified('user', 'test@email.com'));

        $user = new UserObject();
        $user->userName = 'user2';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user2', 'test2@email2.com');

        $this->assertFalse($this->sut->isUserMailVerified('user2', 'test2@email2.com'));
        $this->assertTrue($this->sut->setUserMailVerified('user2', 'test2@email2.com', true));
        $this->assertTrue($this->sut->isUserMailVerified('user', 'test@email.com'));
        $this->assertTrue($this->sut->isUserMailVerified('user2', 'test2@email2.com'));

        $this->assertTrue($this->sut->setUserMailVerified('user', 'test@email.com', false));
        $this->assertTrue($this->sut->setUserMailVerified('user2', 'test2@email2.com', false));
        $this->assertFalse($this->sut->isUserMailVerified('user', 'test@email.com'));
        $this->assertFalse($this->sut->isUserMailVerified('user2', 'test2@email2.com'));

        $this->assertTrue($this->sut->setUserMailVerified('user2', 'test2@email2.com', false));
        $this->assertFalse($this->sut->isUserMailVerified('user2', 'test2@email2.com'));

        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified('nonexistantuser', 'nonexistantmail', true); }, '/Non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified('', 'nonexistantmail', true); }, '/Non existing user:  on domain /');
        AssertUtils::throwsException(function() { $this->sut->setUserMailVerified('user', 'nonexistantmail', true); }, '/Non existing mail: nonexistantmail on user: user on domain /');
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
        AssertUtils::throwsException(function() { $this->sut->getUserMails(''); }, '/Non existing user:  on domain/');
        AssertUtils::throwsException(function() { $this->sut->getUserMails('', ''); }, '/filter must be VERIFIED, NONVERIFIED or ALL/');
        AssertUtils::throwsException(function() { $this->sut->getUserMails('', []); }, '/filter must be VERIFIED, NONVERIFIED or ALL/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user', 'test@email.com');
        $this->sut->saveUserMail('user', 'test2@email2.com');
        $this->sut->saveUserMail('user', 'test3@email3.com');

        // Get verified and non verified emails
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => false], ['mail' => 'test3@email3.com', 'isverified' => false],
            ['mail' => 'test@email.com', 'isverified' => false]], $this->sut->getUserMails('user'));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => false], ['mail' => 'test3@email3.com', 'isverified' => false],
            ['mail' => 'test@email.com', 'isverified' => false]], $this->sut->getUserMails('user', 'NONVERIFIED'));

        // get only verified emails
        $this->assertSame([], $this->sut->getUserMails('user', 'VERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test2@email2.com', true));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true]], $this->sut->getUserMails('user', 'VERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test3@email3.com', true));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true], ['mail' => 'test3@email3.com', 'isverified' => true]],
            $this->sut->getUserMails('user', 'VERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test@email.com', true));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true], ['mail' => 'test3@email3.com', 'isverified' => true],
            ['mail' => 'test@email.com', 'isverified' => true]], $this->sut->getUserMails('user', 'VERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test@email.com', false));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true], ['mail' => 'test3@email3.com', 'isverified' => true]],
            $this->sut->getUserMails('user', 'VERIFIED'));

        // Get only non verified emails
        $this->assertSame([['mail' => 'test@email.com', 'isverified' => false]], $this->sut->getUserMails('user', 'NONVERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test2@email2.com', false));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test3@email3.com', false));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test@email.com', false));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => false], ['mail' => 'test3@email3.com', 'isverified' => false],
            ['mail' => 'test@email.com', 'isverified' => false]], $this->sut->getUserMails('user', 'NONVERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test2@email2.com', true));
        $this->assertSame([['mail' => 'test3@email3.com', 'isverified' => false], ['mail' => 'test@email.com', 'isverified' => false]],
            $this->sut->getUserMails('user', 'NONVERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test3@email3.com', true));
        $this->assertSame([['mail' => 'test@email.com', 'isverified' => false]], $this->sut->getUserMails('user', 'NONVERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test@email.com', true));
        $this->assertSame([], $this->sut->getUserMails('user', 'NONVERIFIED'));

        // Check that emails are not mixed between users
        $user = new UserObject();
        $user->userName = 'user2';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user2', 'user@email.com');
        $this->sut->saveUserMail('user2', 'user2@email2.com');
        $this->sut->saveUserMail('user2', 'user3@email3.com');
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true], ['mail' => 'test3@email3.com', 'isverified' => true],
            ['mail' => 'test@email.com', 'isverified' => true]], $this->sut->getUserMails('user'));
        $this->assertSame([['mail' => 'user2@email2.com', 'isverified' => false], ['mail' => 'user3@email3.com', 'isverified' => false],
            ['mail' => 'user@email.com', 'isverified' => false]], $this->sut->getUserMails('user2'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->getUserMails('', 'INVALIDFILTER'); }, '/filter must be VERIFIED, NONVERIFIED or ALL/');
        AssertUtils::throwsException(function() { $this->sut->getUserMails('nonexistantuser'); }, '/Non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->getUserMails('', 'ALL'); }, '/Non existing user:  on domain /');
    }


    /** test */
    public function testGetUserFromToken(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->getUserFromToken(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->getUserFromToken(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->getUserFromToken(''); }, '/token must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->getUserFromToken('', ''); }, '/token must be a non empty string/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');
        $token = $this->sut->login('user', 'psw')->token;

        $userRead = $this->sut->getUserFromToken($token);
        $this->assertSame($user->userName, $userRead->userName);
        $this->assertSame($user->getDbId(), $userRead->getDbId());

        $user2 = new UserObject();
        $user2->userName = 'user2';
        $this->sut->saveUser($user2);
        $this->sut->setUserPassword($user2->userName, 'psw2');
        $token2 = $this->sut->login('user2', 'psw2')->token;

        $userRead = $this->sut->getUserFromToken($token2);
        $this->assertSame($user2->userName, $userRead->userName);
        $this->assertSame($user2->getDbId(), $userRead->getDbId());

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->getUserFromToken('nonexistanttoken'); }, '/Invalid token: nonexistanttoken/');
    }


    /** test */
    public function testDeleteUserMails(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('', ''); }, '/must be of the type array, string given/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('', []); }, '/Non existing user:  on domain /');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user', 'test@email.com');
        $this->sut->saveUserMail('user', 'test2@email2.com');
        $this->sut->saveUserMail('user', 'test3@email3.com');
        $this->assertSame(3, count($this->sut->getUserMails('user')));
        $this->assertSame(1, $this->sut->deleteUserMails('user', ['test@email.com']));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => false], ['mail' => 'test3@email3.com', 'isverified' => false]],
            $this->sut->getUserMails('user'));
        $this->assertSame(1, $this->sut->deleteUserMails('user', ['test2@email2.com']));
        $this->assertSame([['mail' => 'test3@email3.com', 'isverified' => false]], $this->sut->getUserMails('user'));
        $this->assertSame(1, $this->sut->deleteUserMails('user', ['test3@email3.com']));
        $this->assertSame([], $this->sut->getUserMails('user'));

        $this->sut->saveUserMail('user', 'test@email.com');
        $this->sut->saveUserMail('user', 'test2@email2.com');
        $this->sut->saveUserMail('user', 'test3@email3.com');
        $this->assertSame(3, count($this->sut->getUserMails('user')));
        $this->assertSame(3, $this->sut->deleteUserMails('user', ['test2@email2.com', 'test3@email3.com', 'test@email.com']));
        $this->assertSame([], $this->sut->getUserMails('user'));

        $this->sut->saveUserMail('user', 'test@email.com');
        $this->sut->saveUserMail('user', 'test2@email2.com');
        $this->sut->saveUserMail('user', 'test3@email3.com');
        $this->assertSame(3, count($this->sut->getUserMails('user')));
        $this->assertSame(3, $this->sut->deleteUserMails('user', []));
        $this->assertSame([], $this->sut->getUserMails('user'));

        // Test wrong values
        $this->assertSame(0, $this->sut->deleteUserMails('user', ['nonexistant@mail.com']));
        $this->assertSame(0, $this->sut->deleteUserMails('user', ['nonexistant@mail.com', 'nomail']));
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('user', [1234, 'nonexistant@mail.com', 'nomail']); }, '/Invalid mail: 1234/');

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('', 123); }, '/must be of the type array, int given/');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('nonexistantuser', []); }, '/Non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->deleteUserMails('', []); }, '/Non existing user:  on domain /');
    }


    /** test */
    public function testDeleteUser(){

        $tableName = $this->dbObjectsManager->tablesPrefix.'userobject';

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->deleteUser(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->deleteUser(null); }, '/userName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->deleteUser(0); }, '/userName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->deleteUser(''); }, '/userName must be a non empty string/');

        // Test ok values
        $this->sut->saveRole('role1', 'test role');

        $user = new UserObject();
        $user->userName = 'user';
        $user->description = 'a single user that will be deleted';
        $user->roles = ['role1'];
        $this->assertSame(1, $this->sut->saveUser($user));
        $this->assertTrue($this->sut->setUserPassword($user->userName, 'psw'));
        $this->assertTrue($this->sut->saveUserMail($user->userName, 'user@mail.com', ''));
        $this->sut->login($user->userName, 'psw');

        $this->assertSame(['user'], $this->db->tableGetColumnValues($tableName, 'username'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($tableName.'_password', 'dbid'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($tableName.'_roles', 'dbid'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($this->dbObjectsManager->tablesPrefix.'token', 'dbid'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($tableName.'_mails', 'dbid'));

        $this->assertTrue($this->sut->deleteUser($user->userName));
        $this->assertSame([], $this->db->tableGetColumnValues($tableName, 'username'));
        $this->assertSame([], $this->db->tableGetColumnValues($tableName.'_password', 'dbid'));
        $this->assertSame([], $this->db->tableGetColumnValues($tableName.'_roles', 'dbid'));
        $this->assertSame([], $this->db->tableGetColumnValues($this->dbObjectsManager->tablesPrefix.'token', 'dbid'));
        $this->assertSame([], $this->db->tableGetColumnValues($tableName.'_mails', 'dbid'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->deleteUser(new UserObject()); }, '/userName must be a non empty string/');
        AssertUtils::throwsException(function() use ($user) { $this->sut->deleteUser($user->userName); }, '/Trying to delete non existant user: user/');
        AssertUtils::throwsException(function() { $this->sut->deleteUser('nonexistantuser'); }, '/Trying to delete non existant user: nonexistantuser/');
    }


    /** test */
    public function testDeleteUsers(){

        $tableName = $this->dbObjectsManager->tablesPrefix.'userobject';

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->deleteUsers(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->deleteUsers(null); }, '/userNames must be a non empty array/');
        AssertUtils::throwsException(function() { $this->sut->deleteUsers(0); }, '/userNames must be a non empty array/');
        AssertUtils::throwsException(function() { $this->sut->deleteUsers(''); }, '/userNames must be a non empty array/');
        AssertUtils::throwsException(function() { $this->sut->deleteUsers([]); }, '/userNames must be a non empty array/');

        // Test ok values
        $this->sut->saveRole('role1', 'test role1');
        $this->sut->saveRole('role2', 'test role2');
        $this->sut->saveRole('role3', 'test role3');

        $user1 = new UserObject();
        $user1->userName = 'user1';
        $user1->roles = ['role1'];
        $this->assertSame(1, $this->sut->saveUser($user1));
        $this->assertTrue($this->sut->setUserPassword($user1->userName, 'psw1'));
        $this->assertTrue($this->sut->saveUserMail($user1->userName, 'user1@mail.com', ''));
        $this->sut->login($user1->userName, 'psw1');

        $user2 = new UserObject();
        $user2->userName = 'user2';
        $user2->roles = ['role2'];
        $this->assertSame(2, $this->sut->saveUser($user2));
        $this->assertTrue($this->sut->setUserPassword($user2->userName, 'psw2'));
        $this->assertTrue($this->sut->saveUserMail($user2->userName, 'user2@mail.com', ''));
        $this->sut->login($user2->userName, 'psw2');

        $user3 = new UserObject();
        $user3->userName = 'user3';
        $user3->roles = ['role3'];
        $this->assertSame(3, $this->sut->saveUser($user3));
        $this->assertTrue($this->sut->setUserPassword($user3->userName, 'psw3'));
        $this->assertTrue($this->sut->saveUserMail($user3->userName, 'user3@mail.com', ''));
        $this->sut->login($user3->userName, 'psw3');

        $this->assertSame(['user1', 'user2', 'user3'], $this->db->tableGetColumnValues($tableName, 'username'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($tableName.'_password', 'dbid'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($tableName.'_roles', 'dbid'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($this->dbObjectsManager->tablesPrefix.'token', 'dbid'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($tableName.'_mails', 'dbid'));

        // Call the method with a non existant user and make sure that none of the provided is deleted
        AssertUtils::throwsException(function() use ($user1, $user2) { $this->sut->deleteUsers([$user1->userName, $user2->userName, new UserObject()]); },
            '/Error deleting objects: userName must be a non empty string/');

        $this->assertSame(['user1', 'user2', 'user3'], $this->db->tableGetColumnValues($tableName, 'username'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($tableName.'_password', 'dbid'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($tableName.'_roles', 'dbid'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($this->dbObjectsManager->tablesPrefix.'token', 'dbid'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($tableName.'_mails', 'dbid'));

        $this->assertSame(2, $this->sut->deleteUsers([$user1->userName, $user2->userName]));
        $this->assertSame(['user3'], $this->db->tableGetColumnValues($tableName, 'username'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($tableName.'_password', 'dbid'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($tableName.'_roles', 'dbid'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($this->dbObjectsManager->tablesPrefix.'token', 'dbid'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($tableName.'_mails', 'dbid'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->deleteUsers([new UserObject()]); }, '/Error deleting objects: userName must be a non empty string/');
        AssertUtils::throwsException(function() use ($user1) { $this->sut->deleteUsers([$user1->userName]); }, '/Error deleting objects: Trying to delete non existant user: user1/');
    }


    /** test */
    public function testSaveOperation(){

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


    /** test */
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


    /** test */
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
    public function testLogin(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->login(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->login(null, null); }, '/Argument 1 passed to .*login.* must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->login([], []); }, '/Argument 1 passed to .*login.* must be of the type string, array given/');

        AssertUtils::throwsException(function() { $this->sut->login('', ''); }, '/Authentication failed/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');
        $login1Result = $this->sut->login('user', 'psw');
        $this->assertTrue(strlen($login1Result->token) > 100);
        $this->assertSame('user', $login1Result->user->userName);
        $this->assertSame(1, $login1Result->user->getDbId());

        $this->sut->saveRole('admin', '');

        $user = new UserObject();
        $user->userName = 'user2';
        $user->roles = ['admin'];
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw2');
        $login2Result = $this->sut->login('user2', 'psw2');
        $this->assertTrue(strlen($login2Result->token) > 100);
        $this->assertSame('user2', $login2Result->user->userName);
        $this->assertSame(['admin'], $login2Result->user->roles);
        $this->assertSame(2, $login2Result->user->getDbId());

        $this->assertSame([$login1Result->token, $login2Result->token], $this->db->tableGetColumnValues('usr_token', 'token'));

        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues('usr_token', 'dbid'));

        // Validate that a token is reused and recycled when performing several logins for the same user
        $this->sut->tokenLifeTime = 3;
        $token1 = $this->sut->login('user', 'psw')->token;
        $this->assertSame($token1, $login1Result->token);
        $this->assertSame([$login1Result->token, $login2Result->token], $this->db->tableGetColumnValues('usr_token', 'token'));
        sleep(1);
        $token1 = $this->sut->login('user', 'psw')->token;
        $this->assertSame($token1, $login1Result->token);
        sleep(1);
        $token1 = $this->sut->login('user', 'psw')->token;
        $this->assertSame($token1, $login1Result->token);
        sleep(1);
        $token1 = $this->sut->login('user', 'psw')->token;
        $this->assertSame($token1, $login1Result->token);
        sleep(1);
        $token1 = $this->sut->login('user', 'psw')->token;
        $this->assertSame($token1, $login1Result->token);
        $this->assertTrue($this->sut->isTokenValid($token1));
        $token2 = $this->sut->login('user2', 'psw2')->token;
        $this->assertSame($token2, $login2Result->token);
        $this->assertSame([$login1Result->token, $login2Result->token], $this->db->tableGetColumnValues('usr_token', 'token'));

        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut->login('invalid user', 'invalid token'); }, '/Authentication failed/');

        $user = new UserObject();
        $user->userName = 'user3';
        $this->sut->saveUser($user);
        AssertUtils::throwsException(function() { $this->sut->login('user3', 'psw'); }, '/Specified user does not have a stored password: user3/');
        // TODO

        // Test exceptions
        // TODO
    }


    /** test */
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


    /** test */
    public function testIsTokenValid(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->isTokenValid(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->isTokenValid(''); }, '/token must have a value/');
        AssertUtils::throwsException(function() { $this->sut->isTokenValid(null); }, '/Argument 1 passed to .*isTokenValid.* must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->isTokenValid([]); }, '/Argument 1 passed to .*isTokenValid.* must be of the type string, array given/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');
        $token1 = $this->sut->login('user', 'psw')->token;
        $this->assertTrue($this->sut->isTokenValid($token1));

        $user = new UserObject();
        $user->userName = 'user2';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw2');
        $token2 = $this->sut->login('user2', 'psw2')->token;
        $this->assertTrue($this->sut->isTokenValid($token1));
        $this->assertTrue($this->sut->isTokenValid($token2));

        $this->assertSame([$token1, $token2], $this->db->tableGetColumnValues('usr_token', 'token'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues('usr_token', 'dbid'));
        $this->assertRegExp('/....-..-.. ..:..:../', $this->db->tableGetColumnValues('usr_token', 'expires')[1]);
        // TODO

        // Test wrong values
        $this->assertFalse($this->sut->isTokenValid('invalid token'));
        // TODO

        // Test exceptions
        // TODO
    }


    /** test */
    public function testIsTokenValid_removed_from_db_when_expired(){

        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');
        $token = $this->sut->login('user', 'psw')->token;
        $this->assertTrue($this->sut->isTokenValid($token));

        // Validate that when a token expires it gets removed from db
        $this->sut->tokenLifeTime = 2;
        $this->assertSame($token, $this->sut->login('user', 'psw')->token);
        $this->assertTrue($this->sut->isTokenValid($token));
        $this->assertSame($token, $this->db->tableGetColumnValues('usr_token', 'token')[0]);
        sleep(2);
        $this->assertSame($token, $this->db->tableGetColumnValues('usr_token', 'token')[0]);
        $this->assertFalse($this->sut->isTokenValid($token));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_token', 'token'));
    }


    /** test */
    public function testIsTokenValid_recycles_expiry_time_after_token_correctly_validated(){

        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');

        // Validate that a token expiry time gets recycled when it gets correctly validated, and then when it
        // expires it gets removed from db
        $this->sut->tokenLifeTime = 3;

        $token = $this->sut->login('user', 'psw')->token;
        $this->assertSame([$token], $this->db->tableGetColumnValues('usr_token', 'token'));

        $this->assertTrue($this->sut->isTokenValid($token));
        $this->assertSame([$token], $this->db->tableGetColumnValues('usr_token', 'token'));
        sleep(1);
        $this->assertTrue($this->sut->isTokenValid($token));
        $this->assertSame([$token], $this->db->tableGetColumnValues('usr_token', 'token'));
        sleep(1);
        $this->assertTrue($this->sut->isTokenValid($token));
        $this->assertSame([$token], $this->db->tableGetColumnValues('usr_token', 'token'));
        sleep(1);
        $this->assertTrue($this->sut->isTokenValid($token));
        $this->assertSame([$token], $this->db->tableGetColumnValues('usr_token', 'token'));
        sleep(1);
        $this->assertTrue($this->sut->isTokenValid($token));
        $this->assertSame([$token], $this->db->tableGetColumnValues('usr_token', 'token'));
        sleep(3);
        $this->assertSame([$token], $this->db->tableGetColumnValues('usr_token', 'token'));
        $this->assertFalse($this->sut->isTokenValid($token));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_token', 'token'));
    }


    /** test */
    public function testIsTokenValid_does_not_recycle_expiry_time_after_token_correctly_validated_if_token_recycle_is_false(){

        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');

        // Validate that a token expiry time does not get recycled when it gets correctly validated if token recycle is false
        $this->sut->tokenLifeTime = 3;
        $this->sut->isTokenLifeTimeRecycled = false;

        $token4 = $this->sut->login('user', 'psw')->token;
        $this->assertTrue($this->sut->isTokenValid($token4));
        $this->assertSame([$token4], $this->db->tableGetColumnValues('usr_token', 'token'));
        sleep(1);
        $this->assertTrue($this->sut->isTokenValid($token4));
        $this->assertSame([$token4], $this->db->tableGetColumnValues('usr_token', 'token'));
        sleep(2);
        $this->assertSame([$token4], $this->db->tableGetColumnValues('usr_token', 'token'));
        $this->assertFalse($this->sut->isTokenValid($token4));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_token', 'token'));
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
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');

        $user = new UserObject();
        $user->userName = 'user2';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw2');

        $token = $this->sut->login('user', 'psw')->token;
        $this->assertSame([$token], $this->db->tableGetColumnValues('usr_token', 'token'));
        $this->assertTrue($this->sut->logout($token));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_token', 'token'));

        $this->sut->tokenLifeTime = 2;

        $token1 = $this->sut->login('user', 'psw')->token;
        $token2 = $this->sut->login('user2', 'psw2')->token;

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