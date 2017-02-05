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
use org\turbodepot\src\main\php\managers\TurboDepotManager;


/**
 * FilesManager tests
 *
 * @return void
 */
class TurboDepotManagerTest extends PHPUnit_Framework_TestCase {


	/**
	 * testConnect
	 *
	 * @return void
	 */
	public function testConnect(){

		$turboDepotManager = TurboDepotManager::getInstance();

		$turboDepotManager->connect();

		//$this->assertTrue($filesManager->isFile('https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js'), 'Could not load url. Internet connection must be available');
	}
}

?>