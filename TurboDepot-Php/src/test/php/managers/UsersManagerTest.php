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
use Throwable;
use stdClass;
use org\turbodepot\src\main\php\managers\UsersManager;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbodepot\src\main\php\model\UserObject;
use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;
use org\turbodepot\src\test\resources\managers\usersManager\Test1CustomFieldsObject;


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
    public static function setUpBeforeClass(): void{

        // Nothing necessary here
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(): void{

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
    protected function tearDown(): void{

        DataBaseManager_MariaDb_Test::deleteAndDisconnectFromTestingMariaDb($this->dbObjectsManager);
    }


    /**
     * @see TestCase::tearDownAfterClass()
     *
     * @return void
     */
    public static function tearDownAfterClass(): void{

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
        AssertUtils::throwsException(function() { $this->sut->saveDomain(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->saveDomain([], []); }, '/must be of the type string, array given/');
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

        $this->assertTrue($this->sut->saveDomain('見 in 見る', ''));
        $this->assertSame(['', 'domain1', 'domain2', 'domain3', '見 in 見る'], $this->db->tableGetColumnValues('usr_domain', 'name'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->saveDomain('   ', ''); }, '/domainName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveDomain([1,2,3,4,5], ''); }, '/must be of the type string, array given/');
        AssertUtils::throwsException(function() { $this->sut->saveDomain('domain1', [1,2,3,4,5]); }, '/must be of the type string, array given/');
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
        AssertUtils::throwsException(function() { $this->sut->saveRole(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->saveRole([], []); }, '/must be of the type string, array given/');
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

        $this->assertTrue($this->sut->saveRole('見 in 見る', '見 in 見る'));
        $this->assertSame(['role1', 'role2', '見 in 見る'], $this->db->tableGetColumnValues('usr_role', 'name'));

        //         // Test wrong values
        //         // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->saveRole('   ', ''); }, '/roleName must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveRole([1,2,3,4,5], ''); }, '/must be of the type string, array given/');
        AssertUtils::throwsException(function() { $this->sut->saveRole('domain1', [1,2,3,4,5]); }, '/must be of the type string, array given/');
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
    public function testIsUserAssignedToRole(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->isUserAssignedToRole(null, null); }, '/null given/');
        AssertUtils::throwsException(function() { $this->sut->isUserAssignedToRole(0, 0); }, '/Invalid or non existant role specified/');
        AssertUtils::throwsException(function() { $this->sut->isUserAssignedToRole('', ''); }, '/roleName must be a non empty string/');

        // Test ok values
        $this->sut->saveRole('admin', '');

        $user = new UserObject();
        $user->userName = 'user';
        $user->roles = ['admin'];
        $this->sut->saveUser($user);
        $this->assertTrue($this->sut->isUserAssignedToRole('user', 'admin'));

        $this->sut->saveRole('specialRole', '');
        $user->roles = ['admin', 'specialRole'];
        $this->sut->saveUser($user);
        $this->assertTrue($this->sut->isUserAssignedToRole('user', 'admin'));
        $this->assertTrue($this->sut->isUserAssignedToRole('user', 'specialRole'));

        $this->sut->saveRole('nottobefound', '');
        $user->roles = ['specialRole'];
        $this->sut->saveUser($user);
        $this->assertFalse($this->sut->isUserAssignedToRole('user', 'admin'));
        $this->assertFalse($this->sut->isUserAssignedToRole('user', 'nottobefound'));

        // Test exceptions
        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut->isUserAssignedToRole('', 'admin'); }, '/username must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->isUserAssignedToRole('234234', 'admin'); }, '/Invalid user/');
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
        AssertUtils::throwsException(function() { $this->sut->setDomain('different domain'); }, '/Domain does not exist different domain/');

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


    /**
     * test
     */
    public function testSaveUser_inject_problematic_sql_characters(){

       // Test ok values
        $user = new UserObject();
        $user->userName = "user'on";
        $this->sut->saveUser($user);
        $this->assertSame(["user'on"], $this->db->tableGetColumnValues('usr_userobject', 'username'));
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
    public function testSaveUserCustomFields(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->saveUserCustomFields(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->saveUserCustomFields(null); }, '/must be of the type string/');
        AssertUtils::throwsException(function() { $this->sut->saveUserCustomFields(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->saveUserCustomFields('', ''); }, '/Argument 2 .*DataBaseObject/');
        AssertUtils::throwsException(function() { $this->sut->saveUserCustomFields([], []); }, '/must be of the type string, array given/');

        // Test ok values
        // Create user add some custom fields with default values at the UserCustomFieldsObject class and test are correctly saved on bd
        $user1 = new UserObject();
        $user1->userName = 'user';
        $this->sut->saveUser($user1);
        $this->sut->saveUserCustomFields($user1->userName, new Test1CustomFieldsObject());
        $this->assertSame(['user'], $this->db->tableGetColumnValues('usr_userobject', 'username'));
        $this->assertSame(['some name'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'name'));
        $this->assertSame([''], $this->db->tableGetColumnValues('usr_userobject_customfields', 'surnames'));
        $this->assertSame(['23434534'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'phone'));
        $this->assertSame(['barcelona'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'city'));
        $this->assertSame([''], $this->db->tableGetColumnValues('usr_userobject_customfields', 'district'));

        // Modify saved values for the user and test new values are correctly saved to bd
        $updatedCustomFields = new Test1CustomFieldsObject();
        $updatedCustomFields->name = 'new name';
        $updatedCustomFields->surnames = 'new surname';
        $updatedCustomFields->phone = '111222333';
        $updatedCustomFields->city = 'madrid';
        $updatedCustomFields->district = 'center';

        $this->sut->saveUserCustomFields($user1->userName, $updatedCustomFields);
        $this->assertSame(['new name'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'name'));
        $this->assertSame(['new surname'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'surnames'));
        $this->assertSame(['111222333'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'phone'));
        $this->assertSame(['madrid'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'city'));
        $this->assertSame(['center'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'district'));
        $this->assertSame(1, $this->db->tableCountRows('usr_userobject_customfields'));

        // Save a second user and add custom fields, make sure vales are also correct and independent
        $user2 = new UserObject();
        $user2->userName = 'user2';
        $this->sut->saveUser($user2);

        $customFieldsUser2 = new Test1CustomFieldsObject();
        $customFieldsUser2->name = 'user2 name';
        $customFieldsUser2->surnames = 'user2 surname';
        $customFieldsUser2->phone = '222333444';
        $customFieldsUser2->city = 'valencia';
        $customFieldsUser2->district = 'west';

        $this->sut->saveUserCustomFields($user2->userName, $customFieldsUser2);
        $this->assertSame(['1','2'], $this->db->tableGetColumnValues('usr_userobject', 'dbid'));
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'dbid'));
        $this->assertSame(['user', 'user2'], $this->db->tableGetColumnValues('usr_userobject', 'username'));
        $this->assertSame(['new name', 'user2 name'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'name'));
        $this->assertSame(['new surname', 'user2 surname'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'surnames'));
        $this->assertSame(['111222333', '222333444'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'phone'));
        $this->assertSame(['madrid', 'valencia'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'city'));
        $this->assertSame(['center', 'west'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'district'));

        // Delete user 1 and make sure only user 2 data exists
        $this->sut->deleteUser($user1->userName);
        $this->assertSame(['2'], $this->db->tableGetColumnValues('usr_userobject', 'dbid'));
        $this->assertSame(['user2'], $this->db->tableGetColumnValues('usr_userobject', 'username'));
        $this->assertSame(['2'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'dbid'));
        $this->assertSame(['user2 name'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'name'));
        $this->assertSame(['user2 surname'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'surnames'));
        $this->assertSame(['222333444'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'phone'));
        $this->assertSame(['valencia'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'city'));
        $this->assertSame(['west'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'district'));

        // Delete user 2 and make sure all tables are empty
        $this->sut->deleteUser($user2->userName);
        $this->assertSame([], $this->db->tableGetColumnValues('usr_userobject', 'dbid'));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_userobject', 'username'));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_userobject_customfields', 'dbid'));

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->saveUserCustomFields('string', new stdClass()); }, '/must be an instance of .*DataBaseObject/');
        AssertUtils::throwsException(function() { $this->sut->saveUserCustomFields([1,2,3], new Test1CustomFieldsObject()); }, '/must be of the type string, array given/');
    }

    /** test */
    public function testSaveUserCustomFields_inject_problematic_sql_characters(){

        // Create a custom fields object with problematic characters
        $custom = new Test1CustomFieldsObject();
        $custom->address = "som'e address";
        $custom->city = 'hell"o';
        $custom->company = '"1\' OR \'1\'=\'1"';
        $custom->district = "'; DROP TABLE users; --";
        $custom->occupation = "試してみる";

        $user1 = new UserObject();
        $user1->userName = 'user';
        $this->sut->saveUser($user1);

        $this->sut->saveUserCustomFields($user1->userName, $custom);
        $this->assertSame(["som'e address"], $this->db->tableGetColumnValues('usr_userobject_customfields', 'address'));
        $this->assertSame(['hell"o'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'city'));
        $this->assertSame(['"1\' OR \'1\'=\'1"'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'company'));
        $this->assertSame(["'; DROP TABLE users; --"], $this->db->tableGetColumnValues('usr_userobject_customfields', 'district'));
        $this->assertSame(["試してみる"], $this->db->tableGetColumnValues('usr_userobject_customfields', 'occupation'));
    }


    /** test */
    public function testSaveUserCustomFields_verify_concurrentAccess(){

        $customFields1 = new Test1CustomFieldsObject();
        $customFields1->name = 'John Doe';

        $customFields2 = new Test1CustomFieldsObject();
        $customFields2->name = 'Jane Smith';

        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);

        // Simulate concurrent modification
        $this->sut->saveUserCustomFields($user->userName, $customFields1);
        $this->sut->saveUserCustomFields($user->userName, $customFields2);

        $this->assertSame(['1'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'dbid'));
        $this->assertSame(['Jane Smith'], $this->db->tableGetColumnValues('usr_userobject_customfields', 'name'));
    }


    /** test */
    public function testSaveUserCustomFields_verify_transaction_rolls_back_correctly(){

        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);

        // Set an invalid field to custom fields
        $customFields = new Test1CustomFieldsObject();
        $customFields->name = 'valid name';
        $customFields->surnames = ['invalid array while expecting string'];

        try {

            $this->sut->saveUserCustomFields($user->userName, $customFields);

        } catch (Throwable $e) {

            // Ensure no partial changes are made
            $this->assertContains('Could not save user custom fields', $e->getMessage());
            $this->assertSame(0, $this->db->tableCountRows('usr_userobject_customfields'));
        }
    }


    /** test */
    public function testSaveUserCustomFields_verify_transaction_rolls_back_correctly_for_multiple_tables(){

        // TODO - start a transaction, create a user, try to add invalid custom fields and verify nothing has been saved
        $this->markTestIncomplete('This test has not been implemented yet.');
    }


    /** test */
    public function testSaveUserCustomFields_verify_table_gets_correctly_modified_after_trying_to_save_different_set_of_custom_fields(){

        // TODO
        $this->markTestIncomplete('This test has not been implemented yet.');
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

        $tableUserPswName = $this->dbObjectsManager->tablesPrefix.'userobject_password';

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
        $this->assertSame(2, $this->db->tableCountRows($tableUserPswName));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->setUserPassword('nonexistantuser', 'psw'); }, '/Non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->setUserPassword(12341234, 'psw'); }, '/Non existing user: 12341234 on domain/');
        AssertUtils::throwsException(function() { $this->sut->setUserPassword('user', [345345]); }, '/Argument 2 .* must be of the type string, array given/');

        // Create a table with missing columns for the user passwords and verify that all missing are created
        $this->db->tableDelete($tableUserPswName);
        $this->db->tableCreate($tableUserPswName, ['dbid bigint unsigned NOT NULL'], ['dbid']);
        $this->assertFalse(in_array('password', $this->db->tableGetColumnNames($tableUserPswName), true));

        $this->sut->setUserPassword($user->userName, 'psw');
        $this->assertSame(2, count($this->db->tableGetColumnNames($tableUserPswName)));
        $this->assertTrue(in_array('password', $this->db->tableGetColumnNames($tableUserPswName), true));

        // Create an extra user with some data and make sure the password is stored
        $this->sut->saveRole('role', '');

        $user = new UserObject();
        $user->userName = 'user3';
        $user->data = '{"a":"value"}';
        $user->roles = ['role'];
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');
        $this->assertSame(2, $this->db->tableCountRows($tableUserPswName));
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
        $this->assertSame([['dbid' => '1', 'mail' => 'test@email.com', 'isverified' => '0',
            'verificationhash' => $this->sut->getUserMailVerificationHash('user', 'test@email.com'),
            'comments' => '', 'data' => '']],
            $this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1]));

        $this->sut->saveUserMail('user', 'test@email.com', '', 'data1');
        $this->assertSame(1, count($this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1])));

        $this->sut->saveUserMail('user', 'test2@email2.com', 'comments2');
        $this->assertSame([
            ['dbid' => '1', 'mail' => 'test@email.com', 'isverified' => '0',
                'verificationhash' => $this->sut->getUserMailVerificationHash('user', 'test@email.com'), 'comments' => '', 'data' => 'data1'],
            ['dbid' => '1', 'mail' => 'test2@email2.com', 'isverified' => '0',
                'verificationhash' => $this->sut->getUserMailVerificationHash('user', 'test2@email2.com'), 'comments' => 'comments2', 'data' => '']
        ], $this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1], 'mail ASC'));

        $this->sut->saveUserMail('user', 'test2@email2.com');
        $this->assertSame(2, count($this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1])));

        $this->sut->saveUserMail('user', 'test3@email3.com', 'comments3', 'data3');
        $this->assertSame([
            ['dbid' => '1', 'mail' => 'test@email.com', 'isverified' => '0',
                'verificationhash' => $this->sut->getUserMailVerificationHash('user', 'test@email.com'), 'comments' => '', 'data' => 'data1'],
            ['dbid' => '1', 'mail' => 'test2@email2.com', 'isverified' => '0',
                'verificationhash' => $this->sut->getUserMailVerificationHash('user', 'test2@email2.com'), 'comments' => '', 'data' => ''],
            ['dbid' => '1', 'mail' => 'test3@email3.com', 'isverified' => '0',
                'verificationhash' => $this->sut->getUserMailVerificationHash('user', 'test3@email3.com'), 'comments' => 'comments3', 'data' => 'data3']
        ], $this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1], 'mail ASC'));

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
    public function testIsUserMail(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->isUserMail(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->isUserMail(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->isUserMail(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->isUserMail(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->isUserMail('', ''); }, '/Invalid mail/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user', 'test@email.com');
        $this->sut->saveUserMail('user', 'test1@email.com');
        $this->sut->saveUserMail('user', 'test2@email.com');

        $this->assertTrue($this->sut->isUserMail('user', 'test@email.com'));
        $this->assertTrue($this->sut->isUserMail('user', 'test1@email.com'));
        $this->assertTrue($this->sut->isUserMail('user', 'test2@email.com'));
        $this->assertFalse($this->sut->isUserMail('user', 'test3@email.com'));
        $this->assertFalse($this->sut->isUserMail('user', 'test4@email.com'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->isUserMail('nonexistantuser', 'test@email.com'); }, '/Non existing user: nonexistantuser on domain/');
        AssertUtils::throwsException(function() { $this->sut->isUserMail('user', ''); }, '/Invalid mail/');
        AssertUtils::throwsException(function() { $this->sut->isUserMail('user', '         '); }, '/Invalid mail/');
    }


    /**
     * test
     */
    public function testGetUserMailVerificationHash(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationHash(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationHash(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationHash(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationHash(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationHash('', ''); }, '/Invalid mail/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user', 'test@email.com');
        $this->sut->saveUserMail('user', 'test1@email.com');
        $this->sut->saveUserMail('user', 'test2@email.com');

        $this->assertSame(20, strlen($this->sut->getUserMailVerificationHash('user', 'test@email.com')));
        $this->assertSame(20, strlen($this->sut->getUserMailVerificationHash('user', 'test1@email.com')));
        $this->assertSame(20, strlen($this->sut->getUserMailVerificationHash('user', 'test2@email.com')));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationHash('user', 'test3@email.com'); }, '/Mail test3@email.com does not belong to user user/');
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationHash('user34', 'test3@email.com'); }, '/Non existing user: user34 on domain/');

        $this->assertSame(0, $this->sut->verifyUserMail('user', 'test@email.com', $this->sut->getUserMailVerificationHash('user', 'test@email.com')));
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationHash('user', 'test@email.com'); }, '/Mail test@email.com is already verified/');
    }


    /**
     * test
     */
    public function testGetUserMailVerificationURI(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationURI(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationURI(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationURI(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationURI(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationURI('', ''); }, '/Invalid mail/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user', 'test@email.com');
        $this->sut->saveUserMail('user', 'test1@email.com');
        $this->sut->saveUserMail('user', 'test2@email.com');

        $this->assertSame(58, strlen($this->sut->getUserMailVerificationURI('user', 'test@email.com')));
        $this->assertSame(58, strlen($this->sut->getUserMailVerificationURI('user', 'test1@email.com')));
        $this->assertSame(58, strlen($this->sut->getUserMailVerificationURI('user', 'test2@email.com')));
        $this->assertSame(63, strlen($this->sut->getUserMailVerificationURI('user', 'test2@email.com', 'a')));
        $this->assertSame(75, strlen($this->sut->getUserMailVerificationURI('user', 'test2@email.com', 'customtext')));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationURI('user', 'test3@email.com'); }, '/Mail test3@email.com does not belong to user user/');
        AssertUtils::throwsException(function() { $this->sut->getUserMailVerificationURI('user34', 'test3@email.com'); }, '/Non existing user: user34 on domain/');
    }


    /**
     * test
     */
    public function testVerifyUserMail(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->verifyUserMail(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->verifyUserMail(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->verifyUserMail(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->verifyUserMail(''); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->verifyUserMail('', ''); }, '/Too few arguments to function/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user', 'test@email.com');
        $this->sut->saveUserMail('user', 'test1@email.com');
        $this->sut->saveUserMail('user', 'test2@email.com');

        $this->assertFalse($this->sut->isUserMailVerified('user', 'test@email.com'));
        $this->assertFalse($this->sut->isUserMailVerified('user', 'test1@email.com'));
        $this->assertFalse($this->sut->isUserMailVerified('user', 'test2@email.com'));
        $this->assertSame(20, strlen($this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1])[0]['verificationhash']));
        $this->assertSame(20, strlen($this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1])[1]['verificationhash']));
        $this->assertSame(20, strlen($this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1])[2]['verificationhash']));

        $this->assertSame(0, $this->sut->verifyUserMail('user', 'test@email.com', $this->sut->getUserMailVerificationHash('user', 'test@email.com')));
        $this->assertTrue($this->sut->isUserMailVerified('user', 'test@email.com'));
        $this->assertFalse($this->sut->isUserMailVerified('user', 'test1@email.com'));

        $userTest1Hash = $this->sut->getUserMailVerificationHash('user', 'test1@email.com');
        $this->assertSame(-1, $this->sut->verifyUserMail('user', 'test1@email.com', 'invalidhash'));
        $this->assertSame(0, $this->sut->verifyUserMail('user', 'test1@email.com', $userTest1Hash));
        $this->assertSame(1, $this->sut->verifyUserMail('user', 'test1@email.com', $userTest1Hash));
        $this->assertTrue($this->sut->isUserMailVerified('user', 'test@email.com'));
        $this->assertTrue($this->sut->isUserMailVerified('user', 'test1@email.com'));

        $this->assertSame('', $this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1, 'mail' => 'test@email.com'])[0]['verificationhash']);
        $this->assertSame('', $this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1, 'mail' => 'test1@email.com'])[0]['verificationhash']);
        $this->assertSame(20, strlen($this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1, 'mail' => 'test2@email.com'])[0]['verificationhash']));

        // Test wrong values
        // Test exceptions
        $this->assertSame(-1, $this->sut->verifyUserMail('user', 'test2@email.com', 'asdfasdf'));

        AssertUtils::throwsException(function() { $this->sut->verifyUserMail('user1', 'test5@email.com', 'asdfasdf'); },
            '/Non existing user: user1 on domain/');

        AssertUtils::throwsException(function() { $this->sut->verifyUserMail('user', 'test5@email.com', 'asdfasdf'); },
            '/Mail test5@email.com does not belong to user user/');

        $this->assertFalse($this->sut->isUserMailVerified('user', 'test2@email.com'));
        $this->assertSame(20, strlen($this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1, 'mail' => 'test2@email.com'])[0]['verificationhash']));
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
        $this->assertSame(20, strlen($this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1, 'mail' => 'test@email.com'])[0]['verificationhash']));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test@email.com', true));
        $this->assertTrue($this->sut->isUserMailVerified('user', 'test@email.com'));
        $this->assertSame('', $this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1, 'mail' => 'test@email.com'])[0]['verificationhash']);

        $user = new UserObject();
        $user->userName = 'user2';
        $this->sut->saveUser($user);
        $this->sut->saveUserMail('user2', 'test2@email2.com');

        $this->assertFalse($this->sut->isUserMailVerified('user2', 'test2@email2.com'));
        $this->assertSame(20, strlen($this->db->tableGetRows('usr_userobject_mails', ['dbid' => 2, 'mail' => 'test2@email2.com'])[0]['verificationhash']));
        $this->assertTrue($this->sut->setUserMailVerified('user2', 'test2@email2.com', true));
        $this->assertTrue($this->sut->isUserMailVerified('user', 'test@email.com'));
        $this->assertTrue($this->sut->isUserMailVerified('user2', 'test2@email2.com'));
        $this->assertSame('', $this->db->tableGetRows('usr_userobject_mails', ['dbid' => 1, 'mail' => 'test@email.com'])[0]['verificationhash']);
        $this->assertSame('', $this->db->tableGetRows('usr_userobject_mails', ['dbid' => 2, 'mail' => 'test2@email2.com'])[0]['verificationhash']);

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
        $this->assertSame([
                            ['mail' => 'test@email.com', 'isverified' => false],
                            ['mail' => 'test2@email2.com', 'isverified' => false],
                            ['mail' => 'test3@email3.com', 'isverified' => false]
                          ], $this->sut->getUserMails('user'));

        $this->assertSame([
                            ['mail' => 'test@email.com', 'isverified' => false],
                            ['mail' => 'test2@email2.com', 'isverified' => false],
                            ['mail' => 'test3@email3.com', 'isverified' => false]
                          ], $this->sut->getUserMails('user', 'NONVERIFIED'));

        // get only verified emails
        $this->assertSame([], $this->sut->getUserMails('user', 'VERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test2@email2.com', true));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true]], $this->sut->getUserMails('user', 'VERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test3@email3.com', true));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true], ['mail' => 'test3@email3.com', 'isverified' => true]],
            $this->sut->getUserMails('user', 'VERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test@email.com', true));

        $this->assertSame([
                            ['mail' => 'test@email.com', 'isverified' => true],
                            ['mail' => 'test2@email2.com', 'isverified' => true],
                            ['mail' => 'test3@email3.com', 'isverified' => true]
                          ], $this->sut->getUserMails('user', 'VERIFIED'));

        $this->assertTrue($this->sut->setUserMailVerified('user', 'test@email.com', false));
        $this->assertSame([['mail' => 'test2@email2.com', 'isverified' => true], ['mail' => 'test3@email3.com', 'isverified' => true]],
            $this->sut->getUserMails('user', 'VERIFIED'));

        // Get only non verified emails
        $this->assertSame([['mail' => 'test@email.com', 'isverified' => false]], $this->sut->getUserMails('user', 'NONVERIFIED'));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test2@email2.com', false));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test3@email3.com', false));
        $this->assertTrue($this->sut->setUserMailVerified('user', 'test@email.com', false));

        $this->assertSame([
                            ['mail' => 'test@email.com', 'isverified' => false],
                            ['mail' => 'test2@email2.com', 'isverified' => false],
                            ['mail' => 'test3@email3.com', 'isverified' => false]

                          ], $this->sut->getUserMails('user', 'NONVERIFIED'));

        $this->assertTrue($this->sut->setUserMailVerified('user', 'test2@email2.com', true));

        $this->assertSame([
                            ['mail' => 'test@email.com', 'isverified' => false],
                            ['mail' => 'test3@email3.com', 'isverified' => false]
                          ], $this->sut->getUserMails('user', 'NONVERIFIED'));

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

        $this->assertSame([
                            ['mail' => 'test@email.com', 'isverified' => true],
                            ['mail' => 'test2@email2.com', 'isverified' => true],
                            ['mail' => 'test3@email3.com', 'isverified' => true]
                          ], $this->sut->getUserMails('user'));

        $this->assertSame([
                            ['mail' => 'user@email.com', 'isverified' => false],
                            ['mail' => 'user2@email2.com', 'isverified' => false],
                            ['mail' => 'user3@email3.com', 'isverified' => false]
                          ], $this->sut->getUserMails('user2'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->getUserMails('', 'INVALIDFILTER'); }, '/filter must be VERIFIED, NONVERIFIED or ALL/');
        AssertUtils::throwsException(function() { $this->sut->getUserMails('nonexistantuser'); }, '/Non existing user: nonexistantuser on domain /');
        AssertUtils::throwsException(function() { $this->sut->getUserMails('', 'ALL'); }, '/Non existing user:  on domain /');
    }


    /**
     * test
     */
    public function testGetUserOperations(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->getUserOperations(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->getUserOperations(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->getUserOperations(''); }, '/userName must be a non empty string/');

        // Test ok values
        $this->sut->saveRole('role1', '');
        $this->sut->saveRole('role2', '');
        $this->sut->saveOperation('op1');
        $this->sut->saveOperation('op2');
        $this->sut->saveOperation('op3');

        $user = new UserObject();
        $user->userName = 'user';
        $user->roles = ['role1'];
        $this->sut->saveUser($user);

        // No operations still
        $this->assertSame([], $this->sut->getUserOperations($user->userName));

        // Add all roles to op1
        $this->sut->setOperationEnabledForRoles('op1', []);
        $this->assertSame(['op1'], $this->sut->getUserOperations($user->userName));

        // Add role1 to op2 and op3
        $this->sut->setOperationEnabledForRoles('op2', ['role1']);
        $this->sut->setOperationEnabledForRoles('op3', ['role1']);
        $this->assertSame(['op1', 'op2', 'op3'], $this->sut->getUserOperations($user->userName));

        // Add now op1 also to role1
        $this->sut->setOperationEnabledForRoles('op1', ['role1']);
        $this->assertSame(['op1', 'op2', 'op3'], $this->sut->getUserOperations($user->userName));

        // Create a new user and test only op1 is available
        $user2 = new UserObject();
        $user2->userName = 'user2';
        $user2->roles = ['role2'];
        $this->sut->saveUser($user2);
        $this->assertSame(['op1'], $this->sut->getUserOperations($user2->userName));

        // Add op3 to user2
        $this->sut->setOperationEnabledForRoles('op3', ['role2']);
        $this->assertSame(['op1', 'op3'], $this->sut->getUserOperations($user2->userName));

        // Change the domain and check list is empty
        $this->sut->saveDomain('d2', '');
        $this->sut->setDomain('d2');
        AssertUtils::throwsException(function() use ($user) { $this->sut->getUserOperations($user->userName); }, '/User user does not exist on domain: d2/');

        // Create a new user on this domain
        $user3 = new UserObject();
        $user3->userName = 'user3';
        $user3->domain = 'd2';
        $this->sut->saveUser($user3);
        $this->assertSame([], $this->sut->getUserOperations($user3->userName));

        // Create one operation and set it to all roles
        $this->sut->saveOperation('op4');
        $this->sut->setOperationEnabledForRoles('op4', []);
        $this->assertSame(['op4'], $this->sut->getUserOperations($user3->userName));

        // Back to default domain and test other users again
        $this->sut->setDomain('');
        $this->assertSame(['op1', 'op2', 'op3'], $this->sut->getUserOperations($user->userName));
        $this->assertSame(['op1', 'op3'], $this->sut->getUserOperations($user2->userName));
    }


    /** test */
    public function testfindUserByToken(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->findUserByToken(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->findUserByToken(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->findUserByToken(''); }, '/token must have a value/');
        AssertUtils::throwsException(function() { $this->sut->findUserByToken('', ''); }, '/token must have a value/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');
        $token = $this->sut->login('user', 'psw')->token;

        $userRead = $this->sut->findUserByToken($token);
        $this->assertSame($user->userName, $userRead->userName);
        $this->assertSame($user->getDbId(), $userRead->getDbId());

        $user2 = new UserObject();
        $user2->userName = 'user2';
        $this->sut->saveUser($user2);
        $this->sut->setUserPassword($user2->userName, 'psw2');
        $token2 = $this->sut->login('user2', 'psw2')->token;

        $userRead = $this->sut->findUserByToken($token2);
        $this->assertSame($user2->userName, $userRead->userName);
        $this->assertSame($user2->getDbId(), $userRead->getDbId());

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->findUserByToken('nonexistanttoken'); }, '/Invalid token: nonexistanttoken/');
    }


    public function testfindUserByToken_calling_two_times_for_a_single_use_token_causes_error(){

        // Create a user
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');

        // Obtain a single use token for that user
        $token = $this->sut->createToken($user->userName, ['useCount' => 1]);

        // Call the find method two times, and verify it fails the second one
        $userRead = $this->sut->findUserByToken($token);
        $this->assertSame($user->userName, $userRead->userName);
        $this->assertSame($user->getDbId(), $userRead->getDbId());

        AssertUtils::throwsException(function() use ($token) { $this->sut->findUserByToken($token); }, "/Invalid token: $token/");
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
        $this->assertSame(['1'], $this->db->tableGetColumnValues($this->dbObjectsManager->tablesPrefix.'token', 'userdbid'));
        $this->assertSame(['1'], $this->db->tableGetColumnValues($tableName.'_mails', 'dbid'));

        $this->assertTrue($this->sut->deleteUser($user->userName));
        $this->assertSame([], $this->db->tableGetColumnValues($tableName, 'username'));
        $this->assertSame([], $this->db->tableGetColumnValues($tableName.'_password', 'dbid'));
        $this->assertSame([], $this->db->tableGetColumnValues($tableName.'_roles', 'dbid'));
        $this->assertSame([], $this->db->tableGetColumnValues($this->dbObjectsManager->tablesPrefix.'token', 'userdbid'));
        $this->assertSame([], $this->db->tableGetColumnValues($tableName.'_mails', 'dbid'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->deleteUser(new UserObject()); }, '/userName must be a non empty string/');
        AssertUtils::throwsException(function() use ($user) { $this->sut->deleteUser($user->userName); }, '/Non existing user: user on domain/');
        AssertUtils::throwsException(function() { $this->sut->deleteUser('nonexistantuser'); }, '/Non existing user: nonexistantuser on domain/');
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
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($this->dbObjectsManager->tablesPrefix.'token', 'userdbid'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($tableName.'_mails', 'dbid'));

        // Call the method with a non existant user and make sure that none of the provided is deleted
        AssertUtils::throwsException(function() use ($user1, $user2) { $this->sut->deleteUsers([$user1->userName, $user2->userName, new UserObject()]); },
            '/Error deleting objects: userName must be a non empty string/');

        $this->assertSame(['user1', 'user2', 'user3'], $this->db->tableGetColumnValues($tableName, 'username'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($tableName.'_password', 'dbid'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($tableName.'_roles', 'dbid'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($this->dbObjectsManager->tablesPrefix.'token', 'userdbid'));
        $this->assertSame(['1', '2', '3'], $this->db->tableGetColumnValues($tableName.'_mails', 'dbid'));

        $this->assertSame(2, $this->sut->deleteUsers([$user1->userName, $user2->userName]));
        $this->assertSame(['user3'], $this->db->tableGetColumnValues($tableName, 'username'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($tableName.'_password', 'dbid'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($tableName.'_roles', 'dbid'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($this->dbObjectsManager->tablesPrefix.'token', 'userdbid'));
        $this->assertSame(['3'], $this->db->tableGetColumnValues($tableName.'_mails', 'dbid'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->deleteUsers([new UserObject()]); }, '/Error deleting objects: userName must be a non empty string/');
        AssertUtils::throwsException(function() use ($user1) { $this->sut->deleteUsers([$user1->userName]); }, '/Error deleting objects: Non existing user: user1 on domain/');
    }


    /** test */
    public function testDeleteOperation(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->deleteOperation(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->deleteOperation(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->deleteOperation([], []); }, '/must be of the type string, array given/');
        AssertUtils::throwsException(function() { $this->sut->deleteOperation('', ''); }, '/operation must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->deleteOperation('   ', ''); }, '/operation must be a non empty string/');

        // Test ok values
        $this->sut->saveRole('r1', '');

        // Save 3 operations, check that exist, delete one and check it does not exist
        $this->sut->saveOperation('op1');
        $this->sut->saveOperation('op2');
        $this->sut->saveOperation('op3');

        $this->assertTrue($this->sut->isOperation('op1'));
        $this->assertTrue($this->sut->isOperation('op2'));
        $this->assertTrue($this->sut->isOperation('op3'));

        $this->assertTrue($this->sut->deleteOperation('op3'));
        $this->assertTrue($this->sut->isOperation('op1'));
        $this->assertTrue($this->sut->isOperation('op2'));
        $this->assertFalse($this->sut->isOperation('op3'));

        // Add role to op2, check all exist, delete op2, check everything is deleted
        $this->sut->setOperationEnabledForRoles('op2', ['r1']);
        $this->assertSame(['op2'], $this->db->tableGetColumnValues('usr_operation_roles', 'operation'));
        $this->assertSame(['r1'], $this->db->tableGetColumnValues('usr_operation_roles', 'role'));
        $this->assertTrue($this->sut->deleteOperation('op2'));
        $this->assertFalse($this->sut->isOperation('op2'));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_operation_roles', 'operation'));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_operation_roles', 'role'));

        // Create new domain, create new role, create operation, add role to operation
        // Delete the role and check link to operation is removed
        $this->sut->saveDomain('d1');
        $this->sut->setDomain('d1');
        $this->sut->saveRole('rd1');
        $this->sut->saveOperation('od1');
        $this->sut->setOperationEnabledForRoles('od1', ['rd1']);
        $this->assertSame(['d1'], $this->db->tableGetColumnValues('usr_operation_roles', 'domain'));
        $this->assertSame(['od1'], $this->db->tableGetColumnValues('usr_operation_roles', 'operation'));
        $this->assertSame(['rd1'], $this->db->tableGetColumnValues('usr_operation_roles', 'role'));
        $this->sut->deleteRole('rd1');
        $this->assertSame([], $this->db->tableGetColumnValues('usr_operation_roles', 'domain'));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_operation_roles', 'operation'));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_operation_roles', 'role'));
        $this->assertTrue($this->sut->isOperation('od1'));

        // Create same role on both domains, assign it to different operations on different domains,
        // delete the role on one domain and check that only the operation on that domain gets unlinked
        $this->sut->saveRole('rd1');
        $this->sut->setOperationEnabledForRoles('od1', ['rd1']);
        $this->sut->setDomain('');
        $this->sut->saveRole('rd1');
        $this->sut->setOperationEnabledForRoles('op1', ['rd1']);
        $this->assertSame(['', 'd1'], $this->db->tableGetColumnValues('usr_operation_roles', 'domain'));
        $this->assertSame(['op1', 'od1'], $this->db->tableGetColumnValues('usr_operation_roles', 'operation'));
        $this->assertSame(['rd1', 'rd1'], $this->db->tableGetColumnValues('usr_operation_roles', 'role'));
        $this->sut->setDomain('d1');
        $this->sut->deleteRole('rd1');
        $this->assertSame([''], $this->db->tableGetColumnValues('usr_operation_roles', 'domain'));
        $this->assertSame(['op1'], $this->db->tableGetColumnValues('usr_operation_roles', 'operation'));
        $this->assertSame(['rd1'], $this->db->tableGetColumnValues('usr_operation_roles', 'role'));

        // Delete the empty domain and check that operation gets also unlinked
        $this->db->tableDeleteRows('usr_domain', ['name' => '']);
        $this->assertSame([], $this->db->tableGetColumnValues('usr_operation_roles', 'domain'));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_operation_roles', 'operation'));
        $this->assertSame([], $this->db->tableGetColumnValues('usr_operation_roles', 'role'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->deleteOperation('nonexistant'); }, '/Error removing nonexistant/');
    }


    /** test */
    public function testSaveOperation(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->saveOperation(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->saveOperation(null, null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->saveOperation([], []); }, '/must be of the type string, array given/');
        AssertUtils::throwsException(function() { $this->sut->saveOperation('', ''); }, '/operation must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveOperation('   ', ''); }, '/operation must be a non empty string/');

        // Test ok values
        $this->assertFalse($this->db->tableExists('usr_operation'));
        $this->assertTrue($this->sut->saveOperation('operation1', ''));
        $this->assertTrue($this->db->tableExists('usr_operation'));
        $this->assertSame([''], $this->db->tableGetColumnValues('usr_operation', 'domain'));
        $this->assertSame(['operation1'], $this->db->tableGetColumnValues('usr_operation', 'name'));
        $this->assertSame([''], $this->db->tableGetColumnValues('usr_operation', 'description'));

        $this->sut->saveDomain('new domain', '');
        $this->sut->setDomain('new domain');
        $this->assertTrue($this->sut->saveOperation('operation2', 'description 2'));
        $this->assertSame(['', 'new domain'], $this->db->tableGetColumnValues('usr_operation', 'domain'));
        $this->assertSame(['operation1', 'operation2'], $this->db->tableGetColumnValues('usr_operation', 'name'));
        $this->assertSame(['', 'description 2'], $this->db->tableGetColumnValues('usr_operation', 'description'));

        $this->assertTrue($this->sut->saveOperation('operation2', 'description 2 edited'));
        $this->assertSame(['', 'description 2 edited'], $this->db->tableGetColumnValues('usr_operation', 'description'));

        $this->assertTrue($this->sut->saveOperation('見 in 見る', '見 in 見る'));
        $this->assertSame(['operation1', 'operation2', '見 in 見る'], $this->db->tableGetColumnValues('usr_operation', 'name'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->saveOperation('   ', ''); }, '/operation must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->saveOperation([1,2,3,4,5], ''); }, '/must be of the type string, array given/');
        AssertUtils::throwsException(function() { $this->sut->saveOperation('domain1', [1,2,3,4,5]); }, '/must be of the type string, array given/');
    }


    /** test */
    public function testSaveOperation_update_already_existing_operation_to_add_a_description_and_roles(){

        // Create a new operation
        $this->assertTrue($this->sut->saveOperation('operation1', ''));
        $this->assertSame(['operation1'], $this->db->tableGetColumnValues('usr_operation', 'name'));
        $this->assertSame([''], $this->db->tableGetColumnValues('usr_operation', 'description'));

        // Update the description for the operation
        $this->assertTrue($this->sut->saveOperation('operation1', 'new descrition'));
        $this->assertSame(['operation1'], $this->db->tableGetColumnValues('usr_operation', 'name'));
        $this->assertSame(['new descrition'], $this->db->tableGetColumnValues('usr_operation', 'description'));

        // Create a new role and assign it to the operation
        $this->sut->saveRole('role1');
        $this->assertTrue($this->sut->setOperationEnabledForRoles('operation1', ['role1']));

        // Update the description for the operation again
        $this->assertTrue($this->sut->saveOperation('operation1', 'new descrition 2'));
        $this->assertSame(['operation1'], $this->db->tableGetColumnValues('usr_operation', 'name'));
        $this->assertSame(['new descrition 2'], $this->db->tableGetColumnValues('usr_operation', 'description'));

        // Create a new role and assign it to the operation
        $this->sut->saveRole('role2');
        $this->assertTrue($this->sut->setOperationEnabledForRoles('operation1', ['role2']));

        // Verify both roles are allowed for the operation
        $this->assertSame(2, $this->db->tableCountRows('usr_operation_roles'));
        $this->assertSame(['role1', 'role2'], $this->db->tableGetColumnValues('usr_operation_roles', 'role'));

        // Verify only one operation exists on database
        $this->assertSame(1, $this->db->tableCountRows('usr_operation'));
    }


    /** test */
    public function testIsOperation(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->saveOperation(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->saveOperation(null); }, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->saveOperation(''); }, '/operation must be a non empty string/');

        // Test ok values
        $this->assertTrue($this->sut->saveOperation('operation1'));
        $this->assertTrue($this->sut->isOperation('operation1'));

        $this->sut->saveDomain('domain2', '');
        $this->sut->setDomain('domain2');
        $this->assertTrue($this->sut->saveOperation('operation2'));
        $this->assertTrue($this->sut->isOperation('operation2'));
        $this->assertFalse($this->sut->isOperation('operation1'));

        $this->sut->setDomain('');
        $this->assertFalse($this->sut->isOperation('operation2'));
        $this->assertTrue($this->sut->isOperation('operation1'));

        // Test wrong values
        // Test exceptions´
        $this->assertFalse($this->sut->isOperation('blabla'));

        AssertUtils::throwsException(function() { $this->sut->saveOperation([1,2,3]); }, '/must be of the type string, array given/');
    }


    /** test */
    public function testSetOperationEnabledForRoles(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->setOperationEnabledForRoles(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->setOperationEnabledForRoles(null); }, '/must be of the type string/');
        AssertUtils::throwsException(function() { $this->sut->setOperationEnabledForRoles(null, null); }, '/must be of the type string/');
        AssertUtils::throwsException(function() { $this->sut->setOperationEnabledForRoles('', null); }, '/must be of the type array/');
        AssertUtils::throwsException(function() { $this->sut->setOperationEnabledForRoles('', []); }, '/operation must be a non empty string/');

        // Test ok values

        // Test single operation allowed for single role
        $this->assertTrue($this->sut->saveRole('role1', ''));
        $this->assertTrue($this->sut->saveOperation('operation1'));
        $this->assertTrue($this->sut->setOperationEnabledForRoles('operation1', ['role1']));
        $this->assertSame([''], $this->db->tableGetColumnValues('usr_operation_roles', 'domain'));
        $this->assertSame(['operation1'], $this->db->tableGetColumnValues('usr_operation_roles', 'operation'));
        $this->assertSame(['role1'], $this->db->tableGetColumnValues('usr_operation_roles', 'role'));

        // Test single operation allowed for all roles
        $this->assertTrue($this->sut->saveOperation('operation2'));
        $this->assertTrue($this->sut->setOperationEnabledForRoles('operation2', []));
        $this->assertSame(['', ''], $this->db->tableGetColumnValues('usr_operation_roles', 'domain'));
        $this->assertSame(['operation1', 'operation2'], $this->db->tableGetColumnValues('usr_operation_roles', 'operation'));
        $this->assertSame(['role1', ''], $this->db->tableGetColumnValues('usr_operation_roles', 'role'));


        // Create an operation without any permission
        $this->assertTrue($this->sut->saveOperation('operation3'));

        // Check user1 is allowed for the created operations
        $user1 = new UserObject();
        $user1->userName = 'user1';
        $user1->roles = ['role1'];
        $this->sut->saveUser($user1);
        $this->assertTrue($this->sut->isUserAllowedTo($user1->userName, 'operation1'));
        $this->assertTrue($this->sut->isUserAllowedTo($user1->userName, 'operation2'));
        $this->assertFalse($this->sut->isUserAllowedTo($user1->userName, 'operation3'));

        // Check user2 is NOT allowed for the created operations
        $user2 = new UserObject();
        $user2->userName = 'user2';
        $this->sut->saveUser($user2);
        $this->assertFalse($this->sut->isUserAllowedTo($user2->userName, 'operation1'));
        $this->assertTrue($this->sut->isUserAllowedTo($user2->userName, 'operation2'));
        $this->assertFalse($this->sut->isUserAllowedTo($user2->userName, 'operation3'));

        // Assign role to user2 and check it is now allowed for all operations except the one
        // that has no permisions assigned
        $user2->roles = ['role1'];
        $this->sut->saveUser($user2);
        $this->assertTrue($this->sut->isUserAllowedTo($user2->userName, 'operation1'));
        $this->assertTrue($this->sut->isUserAllowedTo($user2->userName, 'operation2'));
        $this->assertFalse($this->sut->isUserAllowedTo($user2->userName, 'operation3'));

        // Test wrong values
        AssertUtils::throwsException(function() { $this->sut->setOperationEnabledForRoles('blabla', ['role1']); }, '/is not an operation for the current domain/');
        AssertUtils::throwsException(function() { $this->sut->setOperationEnabledForRoles('operation1', ['role1']); }, '/Could not set operation operation1 as allowed for roles: .role1./');
        AssertUtils::throwsException(function() { $this->sut->setOperationEnabledForRoles('operation2', []); }, '/Could not set operation operation2 as allowed for roles/');
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

        $encoded = $this->sut->encodeUserAndPassword('user1', 'psw');
        $this->assertSame('user1', $this->sut->decodeUserName($encoded));

        AssertUtils::throwsException(function() { $this->sut->decodeUserName('invalid encoded string'); }, '/Incorrectly encoded credentials/');
    }


    /** test */
    public function testDecodePassword(){

        $encoded = $this->sut->encodeUserAndPassword('user1', 'psw');
        $this->assertSame('psw', $this->sut->decodePassword($encoded));

        AssertUtils::throwsException(function() { $this->sut->decodePassword('invalid encoded string'); }, '/Incorrectly encoded credentials/');
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
        $this->assertTrue($this->sut->isMultipleUserSessionsAllowed);

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

        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues('usr_token', 'userdbid'));

        // Validate that all tokens are deleted once a new login is performed, and a new fresh one is generated
        $tokensOnDB = $this->db->tableGetColumnValues('usr_token', 'token');
        $this->assertSame(2, count($tokensOnDB));
        $this->assertContains($login1Result->token, $tokensOnDB);
        $this->assertContains($login2Result->token, $tokensOnDB);

        $newToken1 = $this->sut->login('user', 'psw')->token;
        $this->assertNotSame($newToken1, $login1Result->token);
        $tokensOnDB = $this->db->tableGetColumnValues('usr_token', 'token');
        $this->assertSame(3, count($tokensOnDB));
        $this->assertContains($newToken1, $tokensOnDB);
        $this->assertContains($login2Result->token, $tokensOnDB);

        $newToken2 = $this->sut->login('user2', 'psw2')->token;
        $this->assertNotSame($newToken2, $login2Result->token);
        $tokensOnDB = $this->db->tableGetColumnValues('usr_token', 'token');
        $this->assertSame(4, count($tokensOnDB));
        $this->assertContains($newToken1, $tokensOnDB);
        $this->assertContains($newToken2, $tokensOnDB);

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->login('invalid user', 'invalid token'); }, '/Authentication failed/');
        AssertUtils::throwsException(function() { $this->sut->login('user2', 'psw'); }, '/Authentication failed/');
        AssertUtils::throwsException(function() { $this->sut->login('User', 'psw'); }, '/Authentication failed/');
        AssertUtils::throwsException(function() { $this->sut->login('user', 'Psw'); }, '/Authentication failed/');
        AssertUtils::throwsException(function() { $this->sut->login('User', 'Psw'); }, '/Authentication failed/');

        $user = new UserObject();
        $user->userName = 'user3';
        $this->sut->saveUser($user);
        AssertUtils::throwsException(function() { $this->sut->login('user3', 'psw'); }, '/Specified user does not have a stored password: user3/');
    }


    /**
     * test
     */
    public function testLogin_with_multiple_user_sessions_disabled(){

        // Disable multi session login
        $this->sut->isMultipleUserSessionsAllowed = false;

        // Create 2 test users
        $user1 = new UserObject();
        $user1->userName = 'user1';
        $this->sut->saveUser($user1);
        $this->sut->setUserPassword($user1->userName, 'psw');

        $user2 = new UserObject();
        $user2->userName = 'user2';
        $this->sut->saveUser($user2);
        $this->sut->setUserPassword($user2->userName, 'psw');

        // Login first user two consecutive times and make sure session is destroyed
        $usr1Token1 = $this->sut->login($user1->userName, 'psw')->token;
        $this->assertSame([$usr1Token1], $this->db->tableGetColumnValues('usr_token', 'token'));

        $usr1Token2 = $this->sut->login($user1->userName, 'psw')->token;
        $this->assertNotSame($usr1Token1, $usr1Token2);
        $this->assertSame([$usr1Token2], $this->db->tableGetColumnValues('usr_token', 'token'));

        // Login second user two consecutive times, creating two different tokkens before second login and make sure
        // all first session tokens are destroyed after second login
        $usr2Token1 = $this->sut->login($user2->userName, 'psw')->token;
        $this->assertSame([$usr1Token2, $usr2Token1], $this->db->tableGetColumnValues('usr_token', 'token'));

        $usr2Token2 = $this->sut->createToken($user2->userName);
        $usr2Token3 = $this->sut->createToken($user2->userName);
        $tokensOnDb = $this->db->tableGetColumnValues('usr_token', 'token');
        $this->assertSame(4, count($tokensOnDb));
        $this->assertContains($usr1Token2, $tokensOnDb);
        $this->assertContains($usr2Token1, $tokensOnDb);
        $this->assertContains($usr2Token2, $tokensOnDb);
        $this->assertContains($usr2Token3, $tokensOnDb);

        $usr2Token4 = $this->sut->login($user2->userName, 'psw')->token;
        $this->assertNotSame($usr2Token1, $usr2Token4);
        $this->assertSame([$usr1Token2, $usr2Token4], $this->db->tableGetColumnValues('usr_token', 'token'));
    }


    /**
     * test
     */
    public function testLogin_with_multiple_user_sessions_disabled_check_expirations_work_by_separate(){

        // Create 2 test users
        $user1 = new UserObject();
        $user1->userName = 'user1';
        $this->sut->saveUser($user1);
        $this->sut->setUserPassword($user1->userName, 'psw');

        $user2 = new UserObject();
        $user2->userName = 'user2';
        $this->sut->saveUser($user2);
        $this->sut->setUserPassword($user2->userName, 'psw');

        // Globally disable multi session login, disable token recycle
        $this->sut->isMultipleUserSessionsAllowed = false;
        $this->sut->isTokenLifeTimeRecycled = false;

        // Login to both users with different token life times
        $this->sut->tokenLifeTime = 1;
        $usr1Token1 = $this->sut->login($user1->userName, 'psw')->token;

        $this->sut->tokenLifeTime = 3;
        $usr2Token1 = $this->sut->login($user2->userName, 'psw')->token;

        // Wait 1 second and check that user 2 token is still alive
        sleep(1);
        $this->assertFalse($this->sut->isTokenValid($usr1Token1));
        $this->assertTrue($this->sut->isTokenValid($usr2Token1));

        sleep(2);
        $this->assertFalse($this->sut->isTokenValid($usr1Token1));
        $this->assertFalse($this->sut->isTokenValid($usr2Token1));
    }


    /** test */
    public function testLoginFromEncodedCredentials(){

        // Create test user
        $user1 = new UserObject();
        $user1->userName = 'user1';
        $this->sut->saveUser($user1);
        $this->sut->setUserPassword($user1->userName, 'psw');

        $encoded = $this->sut->encodeUserAndPassword($user1->userName, 'psw');
        $this->sut->loginFromEncodedCredentials($encoded);

        $wrong = $this->sut->encodeUserAndPassword($user1->userName, 'invalidpsw');
        AssertUtils::throwsException(function() use ($wrong) { $this->sut->loginFromEncodedCredentials($wrong); }, '/Authentication failed/');

        // Dummy assert to avoid phpunit warnings
        $this->assertTrue(true);
    }


    /** test */
    public function testCreateToken(){

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->createToken(); }, '/Too few arguments to function/');
        AssertUtils::throwsException(function() { $this->sut->createToken(''); }, '/username must be a non empty string/');
        AssertUtils::throwsException(function() { $this->sut->createToken(null); }, '/Argument 1 .* must be of the type string, null given/');
        AssertUtils::throwsException(function() { $this->sut->createToken([]); }, '/Argument 1 passed .* must be of the type string, array given/');

        // Test ok values
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');

        // Test a valid token is created with default values
        $token1 = $this->sut->createToken($user->userName);
        $this->assertTrue($this->sut->isTokenValid($token1));
        $this->assertSame([$token1], $this->db->tableGetColumnValues('usr_token', 'token'));

        // test a valid token is created with 2 seconds life time and expires after 3 seconds pass
        $token2 = $this->sut->createToken($user->userName, ['lifeTime' => 2]);
        $expectedTokens = [$token1, $token2];
        $savedTokens = $this->db->tableGetColumnValues('usr_token', 'token');
        sort($expectedTokens);
        sort($savedTokens);
        $this->assertSame($expectedTokens, $savedTokens);
        $this->assertTrue($this->sut->isTokenValid($token2));
        sleep(2);
        $this->assertFalse($this->sut->isTokenValid($token2));
        $this->assertSame([$token1], $this->db->tableGetColumnValues('usr_token', 'token'));

        // test a valid token is created with only one use count
        $token3 = $this->sut->createToken($user->userName, ['useCount' => 1]);
        $this->assertTrue($this->sut->isTokenValid($token3));
        $this->assertFalse($this->sut->isTokenValid($token3));

        // test a valid token is created with 3 use count
        $token4 = $this->sut->createToken($user->userName, ['useCount' => 3]);
        $this->assertTrue($this->sut->isTokenValid($token4));
        $this->assertTrue($this->sut->isTokenValid($token4));
        $this->assertTrue($this->sut->isTokenValid($token4));
        $this->assertFalse($this->sut->isTokenValid($token4));

        // test a valid token is created with infinite use count
        $token5 = $this->sut->createToken($user->userName, ['lifeTime' => 8000, 'useCount' => null]);

        for ($i = 0; $i < 500; $i++) {

            $this->assertTrue($this->sut->isTokenValid($token5));
        }

        // Test a valid token is created with recycle time disabled
        $token6 = $this->sut->createToken($user->userName, ['lifeTime' => 2, 'isLifeTimeRecycled' => false]);

        $this->assertTrue($this->sut->isTokenValid($token6));
        sleep(2);
        $this->assertFalse($this->sut->isTokenValid($token6));

        // Test three tokens are correctly created and valid for the same user
        $token71 = $this->sut->createToken($user->userName, ['lifeTime' => 2]);
        $token72 = $this->sut->createToken($user->userName, ['useCount' => 1]);
        $token73 = $this->sut->createToken($user->userName, ['isLifeTimeRecycled' => false]);
        $this->assertTrue($this->sut->isTokenValid($token71));
        $this->assertTrue($this->sut->isTokenValid($token72));
        $this->assertTrue($this->sut->isTokenValid($token73));
        $this->assertFalse($this->sut->isTokenValid($token72));
        sleep(2);
        $this->assertFalse($this->sut->isTokenValid($token71));
        $this->assertTrue($this->sut->isTokenValid($token73));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() use ($user) { $this->sut->createToken($user->userName, 'astring'); }, '/must be of the type array, string given/');
        AssertUtils::throwsException(function() use ($user) { $this->sut->createToken($user->userName, ['lifeTime' => 'a']); }, '/Invalid lifeTime value: a/');
        AssertUtils::throwsException(function() use ($user) { $this->sut->createToken($user->userName, ['lifeTime' => null]); }, '/Invalid lifeTime value:/');
        AssertUtils::throwsException(function() use ($user) { $this->sut->createToken($user->userName, ['useCount' => 'a']); }, '/Invalid useCount value: a/');
        AssertUtils::throwsException(function() use ($user) { $this->sut->createToken($user->userName, ['isLifeTimeRecycled' => 'a']); }, '/Invalid isLifeTimeRecycled value: a/');
        AssertUtils::throwsException(function() use ($user) { $this->sut->createToken($user->userName, ['isLifeTimeRecycled' => null]); }, '/Invalid isLifeTimeRecycled value:/');
        AssertUtils::throwsException(function() use ($user) { $this->sut->createToken($user->userName, ['nonexistant' => 'a']); }, '/Invalid option: nonexistant/');
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
        $this->assertSame(['1', '2'], $this->db->tableGetColumnValues('usr_token', 'userdbid'));
        $this->assertRegExp('/....-..-.. ..:..:../', $this->db->tableGetColumnValues('usr_token', 'expires')[1]);

        // Test that token is reused when set an expiry time of 3 seconds and token reuse is enabled,
        // and then 3 seconds after last token verification it becomes invalid
        $this->sut->tokenLifeTime = 3;
        $this->assertTrue($this->sut->isTokenLifeTimeRecycled);

        $token = $this->sut->login('user', 'psw')->token;
        sleep(1);
        $this->assertTrue($this->sut->isTokenValid($token));
        sleep(1);
        $this->assertTrue($this->sut->isTokenValid($token));
        sleep(1);
        $this->assertTrue($this->sut->isTokenValid($token));
        sleep(3);
        $this->assertFalse($this->sut->isTokenValid($token));

        // Test wrong values
        // Test exceptions
        $this->assertFalse($this->sut->isTokenValid('invalid token'));
    }


    /** test */
    public function testIsTokenValid_called_when_no_database_created_yet(){

        $this->assertFalse($this->sut->isTokenValid('sometoken'));
    }


    /** test */
    public function testIsTokenValid_removed_from_db_when_expired(){

        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');

        $token = $this->sut->login('user', 'psw')->token;
        $this->assertTrue($this->sut->isTokenValid($token));
        $tokensOnDb = $this->db->tableGetColumnValues('usr_token', 'token');
        $this->assertSame(1, count($tokensOnDb));
        $this->assertSame($token, $tokensOnDb[0]);

        // Validate that when a token expires it gets removed from db
        $this->sut->tokenLifeTime = 2;
        $this->sut->isMultipleUserSessionsAllowed = false;
        $newToken = $this->sut->login('user', 'psw')->token;
        $this->assertNotSame($token, $newToken);
        $tokensOnDb = $this->db->tableGetColumnValues('usr_token', 'token');
        $this->assertSame(1, count($tokensOnDb));
        $this->assertSame($newToken, $tokensOnDb[0]);
        $this->assertFalse($this->sut->isTokenValid($token));
        $this->assertTrue($this->sut->isTokenValid($newToken));
        sleep(2);
        $this->assertFalse($this->sut->isTokenValid($token));
        $this->assertSame($newToken, $this->db->tableGetColumnValues('usr_token', 'token')[0]);
        $this->assertFalse($this->sut->isTokenValid($newToken));
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
    public function testDeleteAllExpiredTokens(){

        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');

        // Create 3 tokens with different expiration settings and make sure 2 of them are deleted once expired
        $token1 = $this->sut->createToken($user->userName);
        $token2 = $this->sut->createToken($user->userName, ['useCount' => 1]);
        $token3 = $this->sut->createToken($user->userName, ['lifeTime' => 2, 'isLifeTimeRecycled' => false]);

        $this->assertSame(0, $this->sut->deleteAllExpiredTokens());

        $this->assertTrue($this->sut->isTokenValid($token1));
        $this->assertTrue($this->sut->isTokenValid($token2));
        $this->assertTrue($this->sut->isTokenValid($token3));

        sleep(3);

        $this->assertSame(3, count($this->db->tableGetColumnValues('usr_token', 'token')));

        $this->assertSame(2, $this->sut->deleteAllExpiredTokens());

        $this->assertSame([$token1], $this->db->tableGetColumnValues('usr_token', 'token'));
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
        $this->assertSame([$token2], $this->db->tableGetColumnValues('usr_token', 'token'));

        // Test wrong values
        $this->assertFalse($this->sut->logout('invalidtoken'));

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->logout(123123); }, '/value is not a string/');
        AssertUtils::throwsException(function() { $this->sut->logout([1,2,3]); }, '/value is not a string/');
    }


    /**
     * test
     */
    public function testLogout_multiple_sessions_disabled(){

        $this->sut->isMultipleUserSessionsAllowed = false;

        // Multiple user tokens are all destroyed when logout is performed if multiple sessions is disabled
        $user = new UserObject();
        $user->userName = 'user';
        $this->sut->saveUser($user);
        $this->sut->setUserPassword($user->userName, 'psw');

        $token = $this->sut->login($user->userName, 'psw')->token;
        $this->sut->createToken($user->userName);
        $this->sut->createToken($user->userName, ['useCount' => 1]);

        $this->assertSame(3, count($this->db->tableGetColumnValues('usr_token', 'token')));

        $this->assertTrue($this->sut->logout($token));

        $this->assertSame(0, count($this->db->tableGetColumnValues('usr_token', 'token')));
    }
}
