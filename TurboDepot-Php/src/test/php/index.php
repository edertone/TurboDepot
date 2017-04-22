<?php

/**
 * TurboDepot is a cross language ORM library that allows saving, retrieving, listing, filtering and more with complex class data instances
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2017 Edertone Advanded Solutions (Barcelona). http://www.edertone.com
 */


require_once __DIR__.'/AutoLoader.php';


$phpunit = new PHPUnit_TextUI_TestRunner();

// Run all the tests inside the current folder or subfolders for all the files ending with Test.php
if(!$phpunit->dorun($phpunit->getTest(__DIR__, '', 'Test.php'))->wasSuccessful()){

	throw new Exception();
}

?>