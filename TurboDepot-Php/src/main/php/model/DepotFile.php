<?php

/**
 * TurboDepot is a cross language ORM: Save, read, list, filter and easily perform any storage operation with your application objects
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\main\php\model;

use org\turbocommons\src\main\php\model\BaseStrictClass;


/**
 * TODO
 */
class DepotFile extends BaseStrictClass{


    private $_name;


    private $_content;


    /**
     * TODO
     */
    public function setName(string $name){

        // TODO - validate name is correct

        // Prohibits caracters raros
        // buscar minima longitut validat per tots els OS i sistemes de bdd

        $this->_name = $name;
    }


    /**
     * TODO
     */
    public function setContent(string $content){

        // TODO - validate content is correct

        $this->_content = $content;
    }
}

?>