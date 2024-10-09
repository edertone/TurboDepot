<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\main\php\model;


/**
 * UserObject
 */
final class UserObject extends DataBaseObject{


    const TYPES = [

        'domain' => [250, self::NOT_NULL, self::STRING],
        'roles' => [250, self::NOT_NULL, self::ARRAY, self::STRING],
        'userName' => [100, self::NOT_NULL, self::STRING],
        'description' => [2000, self::NOT_NULL, self::STRING],
        'data' => [5000, self::NOT_NULL, self::STRING]
    ];


    const UNIQUEINDICES = [

        ['domain', 'userName']
    ];


    /**
     * The domain in which the user resides.
     * Domains are like "folders" or "zones" that let us keep some users isolated from others. Users from
     * one domain won't be related to users of anoter domain in any way. We can use this feature to store different application
     * users on the same database for example.
     *
     * Note that the empty "" domain is also a valid domain which exists by default
     *
     * @var string
     */
    public $domain = '';


    /**
     * List of roles that are applied to this user, sorted by preference
     * @var string[]
     */
    public $roles = [];


    /**
     * The username that is used for login.
     * NOTICE: User name values cannot be repeated on the same domain.
     *
     * @var string
     */
    public $userName = '';


    /**
     * Some description that we would like to write for the user
     * @var string
     */
    public $description = '';


    // TODO - implement this flag that should completely disable this user when set to false
    // public $isEnabled = true;


    /**
     * Any extra data which is custom to the user in our application (normally stored as a json encoded string).
     *
     * @var string
     */
    public $data = '';
}
