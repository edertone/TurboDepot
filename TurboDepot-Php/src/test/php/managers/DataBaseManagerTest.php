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
	 * testConnect
	 *
	 * @return void
	 */
	public function testConnect(){

		// We have a DB-Setup xml file containing the database connection parameters we will use for our tests.
		$dbSetup = SerializationUtils::stringToXml(FilesManager::getInstance()->readFile(__DIR__.'/../resources/DB-Setup.xml'))->MySql;

		$db = new DataBaseManager();

		// Test correct case
		$this->assertTrue(!$db->isConnected());
		$this->assertTrue($db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password'], $dbSetup['dataBaseName']));
		$this->assertTrue($db->isConnected());
		$this->assertTrue($db->getLastError() === '');
		$this->assertTrue($db->disconnect());
		$this->assertTrue(!$db->isConnected());
		$this->assertTrue($db->getLastError() === '');

		// test incorrect cases
		$exceptionMessage = '';

		try {
			$db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password'], 'nonexistantdatabaseblablabla');
			$exceptionMessage = 'nonexistantdatabaseblablabla did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$db->connectMysql($dbSetup['host'], 'userthatdoesnotexist', $dbSetup['password'], $dbSetup['dataBaseName']);
			$exceptionMessage = 'userthatdoesnotexist did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		try {
			$db->connectMysql($dbSetup['host'], $dbSetup['userName'], 'passwordthatdoesnotexist', $dbSetup['dataBaseName']);
			$exceptionMessage = 'passwordthatdoesnotexist did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		if($exceptionMessage != ''){

			$this->fail($exceptionMessage);
		}

		$this->assertTrue($db->getLastError() === '');
		$this->assertTrue(!$db->isConnected());
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

		// We have a DB-Setup xml file containing the database connection parameters we will use for our tests.
		$dbSetup = SerializationUtils::stringToXml(FilesManager::getInstance()->readFile(__DIR__.'/../resources/DB-Setup.xml'))->MySql;

		$db = new DataBaseManager();

		$this->assertTrue(!$db->isConnected());
		$this->assertTrue($db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password'], $dbSetup['dataBaseName']));
		$this->assertTrue($db->isConnected());
		$this->assertTrue($db->disconnect());
		$this->assertTrue(!$db->isConnected());
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

		// We have a DB-Setup xml file containing the database connection parameters we will use for our tests.
		$dbSetup = SerializationUtils::stringToXml(FilesManager::getInstance()->readFile(__DIR__.'/../resources/DB-Setup.xml'))->MySql;

		$db = new DataBaseManager();

		$this->assertTrue($db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password'], $dbSetup['dataBaseName']));

		// Drop test db in case it is on db due to a previous failed execution of tests
		$db->dataBaseDelete('testDataBaseCreate');

		$this->assertTrue(!$db->dataBaseExists('testDataBaseCreate'));
		$db->dataBaseCreate('testDataBaseCreate');
		$this->assertTrue($db->dataBaseExists('testDataBaseCreate'));

		// Drop the database
		$db->dataBaseDelete('testDataBaseCreate');
		$this->assertTrue(!$db->dataBaseExists('testDataBaseCreate'));

		// Disconnect the database
		$this->assertTrue($db->disconnect());
	}


	/**
	 * testDataBaseSelect
	 *
	 * @return void
	 */
	public function testDataBaseSelect(){

		// We have a DB-Setup xml file containing the database connection parameters we will use for our tests.
		$dbSetup = SerializationUtils::stringToXml(FilesManager::getInstance()->readFile(__DIR__.'/../resources/DB-Setup.xml'))->MySql;

		$db = new DataBaseManager();

		$this->assertTrue($db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password']));

		$this->assertTrue($db->getSelectedDataBase() == '');

		// Drop test dbs in case they are on db due to a previous failed execution of tests
		$db->dataBaseDelete('testdatabaseselect_tempdb');
		$db->dataBaseDelete('testdatabaseselect_tempdb2');

		// Create two temporary databases
		$this->assertTrue($db->dataBaseCreate('testdatabaseselect_tempdb'));
		$this->assertTrue($db->dataBaseCreate('testdatabaseselect_tempdb2'));

		// test database selection
		$this->assertTrue($db->dataBaseSelect('testdatabaseselect_tempdb'));
		$this->assertTrue($db->getSelectedDataBase() == 'testdatabaseselect_tempdb');
		$this->assertTrue($db->dataBaseSelect('testdatabaseselect_tempdb2'));
		$this->assertTrue($db->getSelectedDataBase() == 'testdatabaseselect_tempdb2');

		// Drop the databases
		$db->dataBaseDelete('testdatabaseselect_tempdb');
		$db->dataBaseDelete('testdatabaseselect_tempdb2');
		$this->assertTrue(!$db->dataBaseExists('testdatabaseselect_tempdb'));
		$this->assertTrue(!$db->dataBaseExists('testdatabaseselect_tempdb2'));

		$this->assertTrue($db->getSelectedDataBase() == '');

		// Disconnect the database
		$this->assertTrue($db->disconnect());
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

		// We have a DB-Setup xml file containing the database connection parameters we will use for our tests.
		$dbSetup = SerializationUtils::stringToXml(FilesManager::getInstance()->readFile(__DIR__.'/../resources/DB-Setup.xml'))->MySql;

		$db = new DataBaseManager();

		$this->assertTrue($db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password'], $dbSetup['dataBaseName']));

		// Drop test table in case it is on db due to a previous failed execution of tests
		$db->query('DROP TABLE MyGuests');

		// test creating a table
		$this->assertTrue($db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(30) NOT NULL)'));
		$this->assertTrue($db->tableExists('MyGuests'));
		$this->assertTrue($db->isLastQuerySucceeded());
		$this->assertTrue(count($db->getQueryHistory()) == 2);

		// Test inserting data to the table
		$this->assertTrue($db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('john','rambo')"));
		$this->assertTrue($db->isLastQuerySucceeded());
		$this->assertTrue($db->getLastInsertId() == 1);
		$this->assertTrue($db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('albert','einstein')"));
		$this->assertTrue($db->isLastQuerySucceeded());
		$this->assertTrue($db->getLastInsertId() == 2);
		$this->assertTrue($db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('elen','ripley')"));
		$this->assertTrue($db->isLastQuerySucceeded());
		$this->assertTrue($db->getLastInsertId() == 3);
		$this->assertTrue(count($db->getQueryHistory()) == 5);

		// test reading values from the created table
		$data = $db->query('SELECT * FROM MyGuests');
		$this->assertTrue($db->isLastQuerySucceeded());
		$this->assertTrue(count($data) == 3);
		$this->assertTrue($data[0]['firstname'] == 'john');
		$this->assertTrue($data[1]['lastname'] == 'einstein');
		$this->assertTrue($data[2]['lastname'] == 'ripley');

		// test deleting the table
		$this->assertTrue($db->query('DROP TABLE MyGuests'));
		$this->assertTrue(!$db->tableExists('MyGuests'));
		$this->assertTrue($db->isLastQuerySucceeded());
		$this->assertTrue(count($db->getQueryHistory()) == 7);
		$this->assertTrue(!$db->query('DROP TABLE MyGuests'));
		$this->assertTrue($db->getLastError() != '');
		$this->assertTrue(!$db->isLastQuerySucceeded());
		$this->assertTrue(count($db->getQueryHistory()) == 8);

		// Test disconnect the database
		$this->assertTrue($db->disconnect());

		// test that queries do not work after db disconnected
		$this->assertTrue(!$db->query('DROP TABLE MyGuests'));
		$this->assertTrue(!$db->isLastQuerySucceeded());
		$this->assertTrue(count($db->getQueryHistory()) == 1);
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

		// Tested by testDataBaseSelect method
	}


	/**
	 * testGetTableColumnValues
	 *
	 * @return void
	 */
	public function testGetTableColumnValues(){

		// We have a DB-Setup xml file containing the database connection parameters we will use for our tests.
		$dbSetup = SerializationUtils::stringToXml(FilesManager::getInstance()->readFile(__DIR__.'/../resources/DB-Setup.xml'))->MySql;

		$db = new DataBaseManager();

		$this->assertTrue($db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password'], $dbSetup['dataBaseName']));

		// Drop test table in case it is on db due to a previous failed execution of tests
		$db->query('DROP TABLE MyGuests');

		// Create the table and required data
		$db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(30) NOT NULL)');
		$this->assertTrue($db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('john','rambo')"));
		$this->assertTrue($db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('albert','einstein')"));
		$this->assertTrue($db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('elen','ripley')"));
		$this->assertTrue($db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('elen2','ripley')"));
		$this->assertTrue($db->query("INSERT INTO MyGuests (firstname, lastname) VALUES ('elen3','ripley')"));
		$this->assertTrue(count($db->getQueryHistory()) == 7);

		// Test values are ok
		$this->assertTrue(ArrayUtils::isEqualTo($db->getTableColumnValues('MyGuests', 'firstname'), ['john', 'albert', 'elen', 'elen2', 'elen3']));
		$this->assertTrue(ArrayUtils::isEqualTo($db->getTableColumnValues('MyGuests', 'lastname'), ['rambo', 'einstein', 'ripley']));

		// Test incorrect values
		$this->assertTrue(!$db->getTableColumnValues('MyGuests', 'unexistant'));
		$this->assertTrue(!$db->getTableColumnValues('unexistant', 'lastname'));

		// Delete created table
		$this->assertTrue($db->query('DROP TABLE MyGuests'));
		$this->assertTrue(!$db->tableExists('MyGuests'));

		// Disconnect the database
		$this->assertTrue($db->disconnect());
	}


	/**
	 * testGetTableColumnNames
	 *
	 * @return void
	 */
	public function testGetTableColumnNames(){

		// We have a DB-Setup xml file containing the database connection parameters we will use for our tests.
		$dbSetup = SerializationUtils::stringToXml(FilesManager::getInstance()->readFile(__DIR__.'/../resources/DB-Setup.xml'))->MySql;

		$db = new DataBaseManager();

		$this->assertTrue($db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password'], $dbSetup['dataBaseName']));

		// Drop test table in case it is on db due to a previous failed execution of tests
		$db->query('DROP TABLE MyGuests');

		// Create the table
		$db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(30) NOT NULL, city VARCHAR(30) NOT NULL, country VARCHAR(30) NOT NULL)');

		// Test values are ok
		$this->assertTrue(ArrayUtils::isEqualTo($db->getTableColumnNames('MyGuests'), ['id', 'firstname', 'lastname', 'city', 'country']));

		// Test incorrect values
		$this->assertTrue(!$db->getTableColumnNames('unexistant'));

		// Delete created table
		$this->assertTrue($db->query('DROP TABLE MyGuests'));
		$this->assertTrue(!$db->tableExists('MyGuests'));

		// Disconnect the database
		$this->assertTrue($db->disconnect());
	}


	/**
	 * testGetTableColumnMaxValue
	 *
	 * @return void
	 */
	public function testGetTableColumnMaxValue(){

		// We have a DB-Setup xml file containing the database connection parameters we will use for our tests.
		$dbSetup = SerializationUtils::stringToXml(FilesManager::getInstance()->readFile(__DIR__.'/../resources/DB-Setup.xml'))->MySql;

		$db = new DataBaseManager();

		$this->assertTrue($db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password'], $dbSetup['dataBaseName']));

		// Drop test table in case it is on db due to a previous failed execution of tests
		$db->query('DROP TABLE MyGuests');

		// Create the table
		$db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, val INT(6) NOT NULL)');
		$this->assertTrue($db->query('INSERT INTO MyGuests (val) VALUES (1)'));
		$this->assertTrue($db->query('INSERT INTO MyGuests (val) VALUES (8)'));
		$this->assertTrue($db->query('INSERT INTO MyGuests (val) VALUES (90)'));
		$this->assertTrue($db->query('INSERT INTO MyGuests (val) VALUES (4)'));
		$this->assertTrue($db->query('INSERT INTO MyGuests (val) VALUES (27)'));

		// Test values are ok
		$this->assertTrue($db->getTableColumnMaxValue('MyGuests', 'val') == 90);

		// Test incorrect values
		$this->assertTrue(!$db->getTableColumnMaxValue('MyGuests', 'unexistant'));

		// Delete created table
		$this->assertTrue($db->query('DROP TABLE MyGuests'));
		$this->assertTrue(!$db->tableExists('MyGuests'));

		// Disconnect the database
		$this->assertTrue($db->disconnect());
	}


	/**
	 * testGetTableRowCount
	 *
	 * @return void
	 */
	public function testGetTableRowCount(){

		// We have a DB-Setup xml file containing the database connection parameters we will use for our tests.
		$dbSetup = SerializationUtils::stringToXml(FilesManager::getInstance()->readFile(__DIR__.'/../resources/DB-Setup.xml'))->MySql;

		$db = new DataBaseManager();

		$this->assertTrue($db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password'], $dbSetup['dataBaseName']));

		// Drop test table in case it is on db due to a previous failed execution of tests
		$db->query('DROP TABLE MyGuests');

		// Create the table
		$db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, val INT(6) NOT NULL)');

		for ($i = 0; $i < 23; $i++) {

			$this->assertTrue($db->query('INSERT INTO MyGuests (val) VALUES (1)'));
		}

		// Test values are ok
		$this->assertTrue($db->getTableRowCount('MyGuests') == 23);

		// Test incorrect values
		$this->assertTrue(!$db->getTableRowCount('unexistant'));

		// Delete created table
		$this->assertTrue($db->query('DROP TABLE MyGuests'));
		$this->assertTrue(!$db->tableExists('MyGuests'));

		// Disconnect the database
		$this->assertTrue($db->disconnect());
	}


	/**
	 * testTableExists
	 *
	 * @return void
	 */
	public function testTableExists(){

		// Intensively tested in other test cases here
	}


	/**
	 * testTransactions
	 *
	 * @return void
	 */
	public function testTransactions(){

		// We have a DB-Setup xml file containing the database connection parameters we will use for our tests.
		$dbSetup = SerializationUtils::stringToXml(FilesManager::getInstance()->readFile(__DIR__.'/../resources/DB-Setup.xml'))->MySql;

		$db = new DataBaseManager();

		$this->assertTrue($db->connectMysql($dbSetup['host'], $dbSetup['userName'], $dbSetup['password'], $dbSetup['dataBaseName']));

		// Drop test table in case it is on db due to a previous failed execution of tests
		$db->query('DROP TABLE MyGuests');

		// Create the table
		$db->query('CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, val INT(6) NOT NULL)');

		// Start transaction
		$this->assertTrue($db->transactionBegin());

		$this->assertTrue($db->query('INSERT INTO MyGuests (val) VALUES (1)'));
		$this->assertTrue($db->query('INSERT INTO MyGuests (val) VALUES (1)'));

		$this->assertTrue($db->transactionRollback());

		// Test values are ok
		$this->assertTrue($db->getTableRowCount('MyGuests') == 0);

		// Start transaction
		$this->assertTrue($db->transactionBegin());

		$this->assertTrue($db->query('INSERT INTO MyGuests (val) VALUES (1)'));
		$this->assertTrue($db->query('INSERT INTO MyGuests (val) VALUES (1)'));

		$this->assertTrue($db->transactionCommit());

		// Test values are ok
		$this->assertTrue($db->getTableRowCount('MyGuests') == 2);

		// Delete created table
		$this->assertTrue($db->query('DROP TABLE MyGuests'));
		$this->assertTrue(!$db->tableExists('MyGuests'));

		// Disconnect the database
		$this->assertTrue($db->disconnect());
	}
}

?>