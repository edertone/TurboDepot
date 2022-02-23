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
 * MarkDownManager class
 */
class MarkDownManager extends BaseStrictClass{


    /**
     * A ParseDown class instance. It is a library to convert MarkDown data to html
     *
     * @var \Parsedown
     */
    private $_parseDown;


    /**
     * Contains functionalities to operate with the markdown format
     */
    public function __construct(){

       require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'libs'
           .DIRECTORY_SEPARATOR.'parsedown'.DIRECTORY_SEPARATOR.'Parsedown.php';

       $this->_parseDown = new \Parsedown();
    }


    /**
     * TODO
     */
    public function validate(string $string){

        // TODO Process the received MarkDown string and throw exceptions for any incorrect format found
    }


    /**
     * TODO
     */
    public function isValid(string $string){

        // TODO - use the validate method to tell if a received MarkDown string is valid
    }


    /**
     * Convert the received MarkDown text to its HTML equivalent
     *
     * @param string $string A valid MarkDown text that will be parsed and converted to HTML
     *
     * @return string A valid HTML text
     */
    public function toHtml(string $string){

        return $this->_parseDown->text($string);
    }
}

?>