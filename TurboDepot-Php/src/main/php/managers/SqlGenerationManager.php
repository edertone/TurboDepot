<?php

/**
 * TurboDepot is a cross language ORM library that allows saving, retrieving, listing, filtering and more with complex class data instances
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2017 Edertone Advanded Solutions (Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\main\php\managers;

use UnexpectedValueException;
use org\turbocommons\main\model\BaseStrictClass;
use org\turbocommons\main\utils\StringUtils;


/**
 * Abstraction layer that is used to connect and operate with multiple database engines.
 * Each instance of this class is independant and works with a single database connection, so multiple instances
 * can be used to operate with different databases or connections at the same time.
 *
 * Common operations that can be performed by this class include: Connect to a database engine, execute queries,
 * count elements, list table columns and lot more.
 */
class SqlGenerationManager extends BaseStrictClass {


	/**
	 * Defines the mysql database engine name
	 */
	const MYSQL = 'mysql';

}

?>
