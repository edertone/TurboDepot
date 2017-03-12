<?php

/**
 * TurboDepot is a cross language ORM library that allows saving, retrieving, listing, filtering and more with complex class data instances
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2017 Edertone Advanded Solutions (Barcelona). http://www.edertone.com
 */


require_once __DIR__.'/../../main/php/AutoLoader.php';
require_once __DIR__.'/libs/phpunit-5.7.14.phar';
require_once __DIR__.'/libs/TurboCommons-Php-0.3.1250.phar';


// Register the autoload method that will locate and automatically load the library classes
spl_autoload_register(function($className){

	// Replace all slashes to the correct OS directory separator
	$classPath = str_replace('\\', DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $className));

	// Remove unwanted classname path parts
	$classPath = explode('src'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR, $classPath);
	$classPath = array_pop($classPath).'.php';

	if(file_exists(__DIR__.DIRECTORY_SEPARATOR.$classPath)){

		require_once __DIR__.DIRECTORY_SEPARATOR.$classPath;
	}
});

?>