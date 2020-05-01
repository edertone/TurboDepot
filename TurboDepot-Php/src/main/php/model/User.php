<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\main\php\model;

use org\turbodepot\src\main\php\managers\DataBaseObjectsManager;
use org\turbodepot\src\main\php\model\DataBaseObject;


/**
 * User
 */
final class User extends DataBaseObject{


    protected function setup(){

        $this->_types['userName'] = [100, DataBaseObjectsManager::NOT_NULL, DataBaseObjectsManager::STRING];
        $this->_types['password'] = [100, DataBaseObjectsManager::NOT_NULL, DataBaseObjectsManager::STRING];
        $this->_types['domain'] = [250, DataBaseObjectsManager::NOT_NULL, DataBaseObjectsManager::STRING];
        $this->_types['data'] = [5000, DataBaseObjectsManager::NOT_NULL, DataBaseObjectsManager::STRING];
    }


    /**
     * The username that is used for login
     * @var string
     */
    public $userName = '';


    /**
     * The password that is used for login
     * @var string
     */
    public $password = '';
    
    
    /**
     * Any extra data which is custom to the user in our application (normally stored as a json encoded string).
     *
     * @var string
     */
    public $data = '';
    

    /**
     * The domain in which the user resides.
     * Domains are like "folders" or "zones" that let us keep some users isolated from others. Users from
     * one domain won't be related to users of anoter domain in any way. We can use this feature to store different application
     * users on the same database for example.
     *
     * Note that the empty "" domain is also a valid domain
     *
     * @var string
     */
    public $domain = '';
}

?>