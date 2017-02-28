<?php


require_once __DIR__.'/AutoLoader.php';


$phpunit = new PHPUnit_TextUI_TestRunner();

// Run all the tests inside the current folder or subfolders for all the files ending with Test.php
if(!$phpunit->dorun($phpunit->getTest(__DIR__, '', 'Test.php'))->wasSuccessful()){

	throw new Exception();
}

?>