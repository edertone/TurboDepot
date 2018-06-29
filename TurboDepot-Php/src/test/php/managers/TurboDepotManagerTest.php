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
use PHPUnit\Framework\TestCase;
use org\turbocommons\src\main\php\utils\SerializationUtils;
use org\turbocommons\src\main\php\managers\FilesManager;
use org\turbodepot\src\main\php\managers\TurboDepotManager;
use org\turbodepot\src\main\php\managers\DataBaseManager;
use org\turbodepot\src\test\php\resources\managers\turboDepotManager\MyCustomer1;


/**
 * TurboDepotManagerTest
 *
 * @return void
 */
class TurboDepotManagerTest extends TestCase {


	/**
	 * @see TestCase::setUpBeforeClass()
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(){

		// Nothing necessary here
	}


	/**
	 * Before each test we will create a new empty database and a turbodepot instance
	 * connected to it.
	 *
	 * @see TestCase::setUp()
	 *
	 * @return void
	 */
	protected function setUp(){

		$filesManager = new FilesManager();

		$setupPath = __DIR__.'/../resources/managers/turboDepotManager/TurboDepot.xml';

		// We have a TurboDepot.xml file containing the database connection parameters we will use for our tests.
		$this->setupData = SerializationUtils::stringToXml($filesManager->readFile($setupPath))->DataBase->MySql;

		$this->db = new DataBaseManager();

		$this->assertTrue($this->db->connectMysql($this->setupData['host'], $this->setupData['userName'], $this->setupData['password']));

		$i = 0;

		// Find a non existant database name
		while($this->db->dataBaseExists($this->dataBaseName = 'databasemanagertest_'.$i)){

			$i++;
		}

		// Create the database
		$this->db->dataBaseCreate($this->dataBaseName);

		// Select the database
		$this->assertTrue($this->db->dataBaseSelect($this->dataBaseName));

		// Create a turbo depot instance and connect it to the database
		$this->turboDepotManager = new TurboDepotManager();

		$this->assertTrue($this->turboDepotManager->loadSetup($setupPath));
		$this->assertTrue($this->turboDepotManager->connectMysql('', '', '', $this->dataBaseName));
	}


	/**
	 * After each test is complete, we will disconnect the turbo depot instance
	 * and destroy the temporary database
	 *
	 * @see TestCase::tearDown()
	 *
	 * @return void
	 */
	protected function tearDown(){

		// Disconnect the turbo depot instance
		$this->assertTrue($this->turboDepotManager->disconnect());

		// Destroy the temporary database
		$this->assertTrue($this->db->dataBaseDelete($this->dataBaseName));
		$this->assertTrue(!$this->db->dataBaseExists($this->dataBaseName));
		$this->assertTrue($this->db->disconnect());
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
	 * testConnect
	 *
	 * @return void
	 */
	public function testLoadSetup(){

		// TODO
	}


	/**
	 * testConnectMysql
	 *
	 * @return void
	 */
	public function testConnectMysql(){

		$this->assertTrue($this->turboDepotManager->disconnect());

		// Test wrong connect values
		$exceptionMessage = '';

		try {
			$this->turboDepotManager->connectMysql('localhost', 'ert', '234', 'ert');
			$exceptionMessage = 'ert did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		// Test ok connect values
		$this->assertTrue($this->turboDepotManager->connectMysql($this->setupData['host'], $this->setupData['userName'], $this->setupData['password'], $this->dataBaseName));

		// Test ok connect values when defined in TurboDepot.xml
		$this->turboDepotManager->loadSetup(__DIR__.'/../resources/managers/turboDepotManager/TurboDepot.xml');

		$this->assertTrue($this->turboDepotManager->disconnect());
		$this->assertTrue($this->turboDepotManager->connectMysql('', '', '', $this->dataBaseName));

		// Test wrong values throw exception after setup is loaded
		$this->assertTrue($this->turboDepotManager->disconnect());

		try {
			$this->turboDepotManager->connectMysql('localhost', 'value2', '234', 'ert');
			$exceptionMessage = 'value2 did not cause exception';
		} catch (Exception $e) {
			// We expect an exception to happen
		}

		if($exceptionMessage != ''){

			$this->fail($exceptionMessage);
		}
	}


	/**
	 * testSave
	 *
	 * @return void
	 */
	public function testSave(){

		$this->assertTrue(!$this->db->tableExists('my_customer1'));

		$this->assertTrue(!$this->turboDepotManager->save(new MyCustomer1()));

		$this->assertTrue($this->db->tableExists('my_customer1'));

		// TODO . Que passa si el tipus d'una property cambia respecte al que ja existia?

		// TODO - Que pasa si eliminem una property que ja té dades guardades a db? - Configurable que es guardin les velles o que es borrin

		// TODO - que passa si llegim una entity i les propietats no cuadren amb la bd? Configurable que peti o que s'adapti a lo que hi hagi i ignori les que faltin

	}
}

?>