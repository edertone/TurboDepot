<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */


namespace org\turbodepot\src\main\php\managers;

use org\turbocommons\src\main\php\model\BaseStrictClass;


/**
 * MarkDownDocsManager class
 */
class MarkDownDocsManager extends BaseStrictClass{


    /**
     * Contains functionalities to manage a lightweight documentation structure based on markdown .md files, using the standard file system
     * as the docs storage.
     *
     * To publish documentation, the following folder structure must be used:
     * $_rootPath/folder-1/subfolder-2/subfolder-n/text.md
     *
     * @param string $rootPath A full filesystem path to the root of the folder where the documentation structure is located.
     */
    public function __construct(string $rootPath){

        // TODO - implement this class and tests
    }
}

?>