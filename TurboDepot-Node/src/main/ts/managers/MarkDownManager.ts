/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */


import { StringUtils } from 'turbocommons-ts';

declare function require(name: string): any;


/**
 * MarkDownManager class
 *
 * @see constructor()
 */
export class MarkDownManager {
   
    /**
     * A showdown class instance. It is a library to convert MarkDown data to html
     */
    private _showdown:any;


    /**
     * Contains functionalities to operate with the markdown format
     */
    constructor(){
        
        let showDown = require('showdown');
        
        showDown.setOption('noHeaderId', true);
        
        this._showdown = new showDown.Converter();
    }


    /**
     * TODO
     */
    validate(string:string){

        // TODO Convert from PHP
        return string;
    }


    /**
     * TODO
     */
    isValid(string:string){

        // TODO Convert from PHP
        return string;
    }


    /**
     * Convert the received MarkDown text to its HTML equivalent
     *
     * @param string $string A valid MarkDown text that will be parsed and converted to HTML
     *
     * @return string A valid HTML text
     */
    toHtml(string:string){
        
        StringUtils.forceString(string, 'string');

        return this._showdown.makeHtml(string);
    }
}
