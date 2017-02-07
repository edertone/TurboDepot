<?php

/**
 * TurboDepot is a cross language ORM library that allows saving, listing and retrieving multiple kinds of objects
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2017 Edertone Advanded Solutions (Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\test\php\managers;

use PHPUnit_Framework_TestCase;
use org\turbodepot\src\main\php\managers\DataBaseManager;


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

		$db = new DataBaseManager();

		// TODO
		// $db->connectMysql('localhost', 'test', 'root', '1234');
	}
}

?>