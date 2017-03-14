<?php

/**
 * TurboDepot is a cross language ORM library that allows saving, retrieving, listing, filtering and more with complex class data instances
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2017 Edertone Advanded Solutions (Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\test\php\managers;

use Exception;
use PHPUnit_Framework_TestCase;
use org\turbodepot\src\main\php\managers\DataBaseManager;
use org\turbocommons\src\main\php\utils\SerializationUtils;
use org\turbocommons\src\main\php\managers\FilesManager;
use org\turbocommons\src\main\php\utils\ArrayUtils;


/**
 * DataBaseManagerTest
 *
 * @return void
 */
class DataBaseManagerTest extends PHPUnit_Framework_TestCase {


	/**
	 * @see PHPUnit_Framework_TestCase::setUpBeforeClass()
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(){

		// Nothing necessary here
	}


	/**
	 * @see PHPUnit_Framework_TestCase::setUp()
	 *
	 * @return void
	 */
	protected function setUp(){

		$filesManager = new FilesManager();

		// We have a TurboDepot.xml file containing the database connection parameters we will use for our tests.
		$this->dbSetup = SerializationUtils::stringToXml($filesManager->readFile(__DIR__.'/../resources/managers/dataBaseManager/TurboDepot.xml'))->DataBase->MySql;

		$this->db = new DataBaseManager();

		$this->assertTrue($this->db->connectMysql($this->dbSetup['host'], $this->dbSetup['userName'], $this->dbSetup['password']));

		$i = 0;

		// Find a non existant database name
		while($this->db->dataBaseExists($this->dataBaseName = 'data_base_manager_test_'.$i)){

			$i++;
		}

		// Create the database
		$this->db->dataBaseCreate($this->dataBaseName);
		$this->db->dataBaseSelect($this->dataBaseName);
	}


	/**
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 *
	 * @return void
	 */
	protected function tearDown(){

		if(!$this->db->isConnected()){

			$this->assertTrue($this->db->connectMysql($this->dbSetup['host'], $this->dbSetup['userName'], $this->dbSetup['password']));
		}

		// Delete the test temporary database
		$this->assertTrue($this->db->dataBaseDelete($this->dataBaseName));

		// Check it does not exist
		$this->assertTrue(!$this->db->dataBaseExists($this->dataBaseName));

		// Disconnect the database
		$this->assertTrue($this->db->disconnect());
	}


	/**
	 * @see PHPUnit_Framework_TestCase::tearDownAfterClass()
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(){

		// Nothing necessary here
	}


	/**
	 * testConnect
	 *
	 * @return void
	 */
	public function testConnect(){

		$this->db->disconnect();

		// Test correct case
		$this->assertTrue(!$this->db->isConnected());
		$this->assertTrue($this->db->connectMysql($this->dbSetup['host'], $this->dbSetup['userName'], $this->dbSetup['password'], $this->dataBaseName));
		$this->assertTrue($this->db->isConnected());
		$this->assertTrue($this->db->getLastError() === '');
		$this->assertTrue($this->db->disconnect());
		$this->assertTrue(!$this->db->isConnected());
		$this->assertTrue($this->db->getLastError() === '');

		// test incorrect cases
		$exceptionMessage = '';

		try {
			$this->db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password'], 'nonexistantdatabaseblablabla');
			$exceptionMessage = 'nonexistantdatabaseblablabla did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$this->db->connectMysql($dbSetup['host'], 'userthatdoesnotexist', $dbSetup['password'], $this->dataBaseName);
			$exceptionMessage = 'userthatdoesnotexist did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$this->db->connectMysql($dbSetup['host'], $dbSetup['userName'], 'passwordthatdoesnotexist', $this->dataBaseName);
			$exceptionMessage = 'passwordthatdoesnotexist did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		if($exceptionMessage != ''){

			$this->fail($exceptionMessage);
		}

		$this->assertTrue($this->db->getLastError() === '');
		$this->assertTrue(!$this->db->isConnected());
	}


	/**
	 * testConnectPostgreSql
	 *
	 * @return void
	 */
	public function testConnectPostgreSql(){

		// TODO
	}


	/**
	 * testIsConnected
	 *
	 * @return void
	 */
	public function testIsConnected(){

		$this->assertTrue($this->db->disconnect());

		$this->assertTrue(!$this->db->isConnected());
		$this->assertTrue($this->db->connectMysql($this->dbSetup['host'], $this->dbSetup['userName'], $this->dbSetup['password'], $this->dataBaseName));
		$this->assertTrue($this->db->isConnected());
		$this->assertTrue($this->db->disconnect());
		$this->assertTrue(!$this->db->isConnected());
	}


	/**
	 * testIsLastQuerySucceeded
	 *
	 * @return void
	 */
	public function testIsLastQuerySucceeded(){

		// Tested by testQuery method
	}


	/**
	 * testDataBaseCreate
	 *
	 * @return void
	 */
	public function testDataBaseCreate(){

		// Drop test db in case it is on db due to a previous failed execution of tests
		$this->db->dataBaseDelete('testDataBaseCreate');

		$this->assertTrue(!$this->db->dataBaseExists('testDataBaseCreate'));
		$this->db->dataBaseCreate('testDataBaseCreate');
		$this->assertTrue($this->db->dataBaseExists('testDataBaseCreate'));

		// Drop the database
		$this->db->dataBaseDelete('testDataBaseCreate');
		$this->assertTrue(!$this->db->dataBaseExists('testDataBaseCreate'));
	}


	/**
	 * testDataBaseSelect
	 *
	 * @return void
	 */
	public function testDataBaseSelect(){

		$this->assertTrue($this->db->getSelectedDataBase() == $this->dataBaseName);

		// Drop test dbs in case they are on db due to a previous failed execution of tests
		$this->db->dataBaseDelete('testdatabaseselect_tempdb');
		$this->db->dataBaseDelete('testdatabaseselect_tempdb2');

		// Create two temporary databases
		$this->assertTrue($this->db->dataBaseCreate('testdatabaseselect_tempdb'));
		$this->assertTrue($this->db->dataBaseCreate('testdatabaseselect_tempdb2'));

		// test database selection
		$this->assertTrue($this->db->dataBaseSelect('testdatabaseselect_tempdb'));
		$this->assertTrue($this->db->getSelectedDataBase() == 'testdatabaseselect_tempdb');
		$this->assertTrue($this->db->dataBaseSelect('testdatabaseselect_tempdb2'));
		$this->assertTrue($this->db->getSelectedDataBase() == 'testdatabaseselect_tempdb2');

		// Drop the databases
		$this->db->dataBaseDelete('testdatabaseselect_tempdb');
		$this->db->dataBaseDelete('testdatabaseselect_tempdb2');
		$this->assertTrue(!$this->db->dataBaseExists('testdatabaseselect_tempdb'));
		$this->assertTrue(!$this->db->dataBaseExists('testdatabaseselect_tempdb2'));

		// Test invalid values
		$exceptionMessage = '';

		try {
			$this->db->dataBaseSelect('');
			$exceptionMessage = '"" did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$this->db->dataBaseSelect(345345);
			$exceptionMessage = '345345 did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$this->db->dataBaseSelect([56,9]);
			$exceptionMessage = '[56,9] did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$this->db->dataBaseSelect(new DataBaseManager());
			$exceptionMessage = 'new DataBaseManager() did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		if($exceptionMessage != ''){

			$this->fail($exceptionMessage);
		}

		$this->assertTrue($this->db->getSelectedDataBase() == '');
	}


	/**
	 * testDataBaseDelete
	 *
	 * @return void
	 */
	public function testDataBaseDelete(){

		// Tested by testDataBaseCreate method
	}


	/**
	 * testDataBaseExists
	 *
	 * @return void
	 */
	public function testDataBaseExists(){

		// Tested by testDataBaseCreate method
	}


	/**
	 * testQuery
	 *
	 * @return void
	 */
	public function testQuery(){

		// Drop test table in case it is on db due to a previous failed execution of tests
		$this->db->tableDelete('MyGuests');

		// test creating a table
		$this->assertTrue($this->db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(30) NOT NULL)'));
		$this->assertTrue($this->db->tableExists('MyGuests'));
		$this->assertTrue($this->db->isLastQuerySucceeded());
		$this->assertTrue(count($this->db->getQueryHistory()) == 3);

		// Test inserting data to the table
		$this->assertTrue($this->db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('john','rambo')"));
		$this->assertTrue($this->db->isLastQuerySucceeded());
		$this->assertTrue($this->db->getLastInsertId() == 1);
		$this->assertTrue($this->db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('albert','einstein')"));
		$this->assertTrue($this->db->isLastQuerySucceeded());
		$this->assertTrue($this->db->getLastInsertId() == 2);
		$this->assertTrue($this->db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('elen','ripley')"));
		$this->assertTrue($this->db->isLastQuerySucceeded());
		$this->assertTrue($this->db->getLastInsertId() == 3);
		$this->assertTrue(count($this->db->getQueryHistory()) == 6);

		// test reading values from the created table
		$data = $this->db->query('SELECT * FROM MyGuests');
		$this->assertTrue($this->db->isLastQuerySucceeded());
		$this->assertTrue(count($data) == 3);
		$this->assertTrue($data[0]['firstname'] == 'john');
		$this->assertTrue($data[1]['lastname'] == 'einstein');
		$this->assertTrue($data[2]['lastname'] == 'ripley');

		// test deleting the table
		$this->assertTrue($this->db->tableDelete('MyGuests'));
		$this->assertTrue(!$this->db->tableExists('MyGuests'));
		$this->assertTrue($this->db->isLastQuerySucceeded());
		$this->assertTrue(count($this->db->getQueryHistory()) == 8);
		$this->assertTrue(!$this->db->tableDelete('MyGuests'));
		$this->assertTrue($this->db->getLastError() != '');
		$this->assertTrue(!$this->db->isLastQuerySucceeded());
		$this->assertTrue(count($this->db->getQueryHistory()) == 9);

		// Test disconnect the database
		$this->assertTrue($this->db->disconnect());

		// test that queries do not work after db disconnected
		$exceptionMessage = '';

		try {
			$this->db->query('DROP TABLE MyGuests');
			$exceptionMessage = 'DROP TABLE did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$this->db->tableDelete('MyGuests');
			$exceptionMessage = 'tableDelete did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		if($exceptionMessage != ''){

			$this->fail($exceptionMessage);
		}

		$this->assertTrue(!$this->db->isLastQuerySucceeded());
		$this->assertTrue(count($this->db->getQueryHistory()) == 0);
	}


	/**
	 * testGetLastInsertId
	 *
	 * @return void
	 */
	public function testGetLastInsertId(){

		// Tested by testQuery method
	}


	/**
	 * testGetLastError
	 *
	 * @return void
	 */
	public function testGetLastError(){

		// Tested by testQuery method
	}


	/**
	 * testGetQueryHistory
	 *
	 * @return void
	 */
	public function testGetQueryHistory(){

		// TODO - Query history should be intensively tested
	}


	/**
	 * testGetSelectedDataBase
	 *
	 * @return void
	 */
	public function testGetSelectedDataBase(){

		// Validate created database is selected
		$this->assertTrue($this->db->getSelectedDatabase() == $this->dataBaseName);

		// Validate with non selected db
		$this->db->disconnect();
		$this->assertTrue(!$this->db->isConnected());
		$this->assertTrue($this->db->connectMysql($this->dbSetup['host'], $this->dbSetup['userName'], $this->dbSetup['password']));
		$this->assertTrue($this->db->isConnected());
		$this->assertTrue($this->db->getSelectedDatabase() == '');

		// Validate selecting db again
		$this->assertTrue($this->db->dataBaseSelect($this->dataBaseName));
		$this->assertTrue($this->db->getSelectedDatabase() == $this->dataBaseName);

		// Validate exception when no connection available
		$this->db->disconnect();

		$exceptionMessage = '';

		try {
			$this->db->getSelectedDatabase();
			$exceptionMessage = 'disconnected db did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		if($exceptionMessage != ''){

			$this->fail($exceptionMessage);
		}

	}


	/**
	 * testGetTableColumnValues
	 *
	 * @return void
	 */
	public function testGetTableColumnValues(){

		// Drop test table in case it is on db due to a previous failed execution of tests
		$this->db->tableDelete('MyGuests');

		// Create the table and required data
		$this->db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(30) NOT NULL)');
		$this->assertTrue($this->db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('john','rambo')"));
		$this->assertTrue($this->db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('albert','einstein')"));
		$this->assertTrue($this->db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('elen','ripley')"));
		$this->assertTrue($this->db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('elen2','ripley')"));
		$this->assertTrue($this->db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('elen3','ripley')"));
		$this->assertTrue(count($this->db->getQueryHistory()) == 8);

		// Test values are ok
		$this->assertTrue(ArrayUtils::isEqualTo($this->db->getTableColumnValues('MyGuests', 'firstname'), ['john', 'albert', 'elen', 'elen2', 'elen3']));
		$this->assertTrue(ArrayUtils::isEqualTo($this->db->getTableColumnValues('MyGuests', 'lastname'), ['rambo', 'einstein', 'ripley']));

		// Test incorrect values
		$this->assertTrue(!$this->db->getTableColumnValues('MyGuests', 'unexistant'));
		$this->assertTrue(!$this->db->getTableColumnValues('unexistant', 'lastname'));

		// Delete created table
		$this->assertTrue($this->db->tableDelete('MyGuests'));
		$this->assertTrue(!$this->db->tableExists('MyGuests'));

		// Disconnect the database
		$this->assertTrue($this->db->disconnect());
	}


	/**
	 * testGetTableColumnNames
	 *
	 * @return void
	 */
	public function testGetTableColumnNames(){

		// Drop test table in case it is on db due to a previous failed execution of tests
		$this->db->tableDelete('MyGuests');

		// Create the table
		$this->db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(30) NOT NULL, city VARCHAR(30) NOT NULL, country VARCHAR(30) NOT NULL)');

		// Test values are ok
		$this->assertTrue(ArrayUtils::isEqualTo($this->db->getTableColumnNames('MyGuests'), ['id', 'firstname', 'lastname', 'city', 'country']));

		// Test incorrect values
		$this->assertTrue(!$this->db->getTableColumnNames('unexistant'));

		// Delete created table
		$this->assertTrue($this->db->tableDelete('MyGuests'));
		$this->assertTrue(!$this->db->tableExists('MyGuests'));
	}


	/**
	 * testGetTableColumnMaxValue
	 *
	 * @return void
	 */
	public function testGetTableColumnMaxValue(){

		// Drop test table in case it is on db due to a previous failed execution of tests
		$this->db->tableDelete('MyGuests');

		// Create the table
		$this->db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, val INT(6) NOT NULL)');
		$this->assertTrue($this->db->query('INSERT INTO MyGuests (val) VALUES (1)'));
		$this->assertTrue($this->db->query('INSERT INTO MyGuests (val) VALUES (8)'));
		$this->assertTrue($this->db->query('INSERT INTO MyGuests (val) VALUES (90)'));
		$this->assertTrue($this->db->query('INSERT INTO MyGuests (val) VALUES (4)'));
		$this->assertTrue($this->db->query('INSERT INTO MyGuests (val) VALUES (27)'));

		// Test values are ok
		$this->assertTrue($this->db->getTableColumnMaxValue('MyGuests', 'val') == 90);

		// Test incorrect values
		$this->assertTrue(!$this->db->getTableColumnMaxValue('MyGuests', 'unexistant'));

		// TODO - What happens if the column contains non numeric values?

		// Delete created table
		$this->assertTrue($this->db->tableDelete('MyGuests'));
		$this->assertTrue(!$this->db->tableExists('MyGuests'));
	}


	/**
	 * testGetTableRowCount
	 *
	 * @return void
	 */
	public function testGetTableRowCount(){

		// Drop test table in case it is on db due to a previous failed execution of tests
		$this->db->tableDelete('MyGuests');

		// Create the table
		$this->db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, val INT(6) NOT NULL)');

		for ($i = 0; $i < 23; $i++) {

			$this->assertTrue($this->db->query('INSERT INTO MyGuests (val) VALUES (1)'));
		}

		// Test values are ok
		$this->assertTrue($this->db->getTableRowCount('MyGuests') == 23);

		// Test incorrect values
		$this->assertTrue(!$this->db->getTableRowCount('unexistant'));

		// Delete created table
		$this->assertTrue($this->db->tableDelete('MyGuests'));
		$this->assertTrue(!$this->db->tableExists('MyGuests'));
	}


	/**
	 * testTableExists
	 *
	 * @return void
	 */
	public function testTableExists(){

		// Test table does not exist
		$this->assertTrue(!$this->db->tableExists('test_table_exists'));

		// Create table and test it exists
		$this->db->query('CREATE TABLE test_table_exists (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, val INT(6) NOT NULL)');
		$this->assertTrue($this->db->tableExists('test_table_exists'));

		// Test tables that do not exist
		$this->assertTrue(!$this->db->tableExists('1'));
		$this->assertTrue(!$this->db->tableExists('test'));

		// test invalid values
		$exceptionMessage = '';

		try {
			$this->db->tableExists('');
			$exceptionMessage = '"" did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$this->db->tableExists(123123);
			$exceptionMessage = '123123 did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$this->db->tableExists([1,4,5]);
			$exceptionMessage = '[1,4,5] did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		// Test delete created table
		$this->assertTrue($this->db->tableDelete('test_table_exists'));
		$this->assertTrue(!$this->db->tableExists('test_table_exists'));

		// Test exception if disconnected
		$this->db->disconnect();

		try {
			$this->db->tableExists('test_table_exists');
			$exceptionMessage = 'disconnected did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		if($exceptionMessage != ''){

			$this->fail($exceptionMessage);
		}
	}

	/**
	 * testTableDelete
	 *
	 * @return void
	 */
	public function testTableDelete(){

		// Test delete for non existant tables
		$this->assertTrue(!$this->db->tableDelete('non_existant'));
		$this->assertTrue(!$this->db->tableDelete('blabla'));
		$this->assertTrue(!$this->db->tableDelete('123'));

		$this->assertTrue(!$this->db->isLastQuerySucceeded());

		// Create a temporary table and delete it
		$this->db->query('CREATE TABLE test_table_exists (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, val INT(6) NOT NULL)');
		$this->assertTrue($this->db->tableExists('test_table_exists'));
		$this->assertTrue($this->db->tableDelete('test_table_exists'));
		$this->assertTrue(!$this->db->tableExists('test_table_exists'));

		$this->assertTrue($this->db->isLastQuerySucceeded());

		// Test invalid and empty values
		$exceptionMessage = '';

		try {
			$this->db->tableDelete(null);
			$exceptionMessage = 'null did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$this->db->tableDelete('');
			$exceptionMessage = '"" did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$this->db->tableDelete(123);
			$exceptionMessage = '123 did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$this->db->tableDelete([123, 898]);
			$exceptionMessage = '[123, 898] did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		// Test delete with disconnected db throws exception
		$this->db->disconnect();

		try {
			$this->db->tableDelete('test_table_exists');
			$exceptionMessage = 'test_table_exists did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		// Test delete with non selected db throws exception
		$this->assertTrue($this->db->connectMysql($this->dbSetup['host'], $this->dbSetup['userName'], $this->dbSetup['password']));
		$this->assertTrue($this->db->isConnected());
		$this->assertTrue($this->db->getSelectedDatabase() == '');

		try {
			$this->db->tableDelete('test_table_exists');
			$exceptionMessage = 'test_table_exists did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		$this->assertTrue(count($this->db->getQueryHistory()) == 0);

		if($exceptionMessage != ''){

			$this->fail($exceptionMessage);
		}
	}


	/**
	 * testTransactions
	 *
	 * @return void
	 */
	public function testTransactions(){

		// Drop test table in case it is on db due to a previous failed execution of tests
		$this->db->tableDelete('MyGuests');

		// Create the table
		$this->db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, val INT(6) NOT NULL)');

		// Start transaction
		$this->assertTrue($this->db->transactionBegin());

		$this->assertTrue($this->db->query('INSERT INTO MyGuests (val) VALUES (1)'));
		$this->assertTrue($this->db->query('INSERT INTO MyGuests (val) VALUES (1)'));

		$this->assertTrue($this->db->transactionRollback());

		// Test values are ok
		$this->assertTrue($this->db->getTableRowCount('MyGuests') == 0);

		// Start transaction
		$this->assertTrue($this->db->transactionBegin());

		$this->assertTrue($this->db->query('INSERT INTO MyGuests (val) VALUES (1)'));
		$this->assertTrue($this->db->query('INSERT INTO MyGuests (val) VALUES (1)'));

		$this->assertTrue($this->db->transactionCommit());

		// Test values are ok
		$this->assertTrue($this->db->getTableRowCount('MyGuests') == 2);

		// Delete created table
		$this->assertTrue($this->db->tableDelete('MyGuests'));
		$this->assertTrue(!$this->db->tableExists('MyGuests'));
	}
}

?>