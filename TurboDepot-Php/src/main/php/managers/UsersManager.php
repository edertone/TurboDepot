<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */


namespace org\turbodepot\src\main\php\managers;

use Throwable;
use UnexpectedValueException;
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbocommons\src\main\php\utils\ConversionUtils;
use org\turbodepot\src\main\php\model\User;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * Users management class
 */
class UsersManager extends BaseStrictClass{


    /**
     * An instance of the DataBaseObjectsManager class that is used by this class to interact with db
     *
     * @var DataBaseObjectsManager
     */
    private $_databaseObjectsManager = null;


    /**
     * Manages a fully featured user system engine
     *
     * @param DataBaseObjectsManager $databaseObjectsManager A DataBaseObjectsManager instance which is fully initialized against a valid database and ready
     *        to operate. This instance will be used by this class to store and read users info.
     */
    public function __construct(DataBaseObjectsManager $databaseObjectsManager){

        $this->_databaseObjectsManager = $databaseObjectsManager;

        if(!$this->_databaseObjectsManager->getDataBaseManager()->isConnected()){

            throw new UnexpectedValueException('No active connection to database available for the provided DataBaseObjectsManager');
        }
    }


    /**
     * Generate an encoded string containing a username and password, without any kind of encryption.
     * This string is normally sent only the first time to login to the system and obtain a token which will
     * be used later for all the required authentications.
     *
     * This is not a secure way to encode user and password values. It must only be used on the first login
     * operation. The generated token must be always used!.
     *
     * @param string $userName A user name to encode
     * @param string $password A password to encode
     *
     * @return string A raw string containing the encoded user and password values
     */
    public function encodeUserAndPassword($userName, $password){

        // Base64 characters are all numbers, uppercase, lowercase, forward slash plus and equal sign,
        // so we join both encoded user and psw with a coma, so we can later split them again
        return base64_encode(ConversionUtils::stringToBase64($userName).','.ConversionUtils::stringToBase64($password));
    }


    /**
     * Get the original user name value from a string containing both user and password which was generated via the
     * encodeUserAndPassword() method.
     *
     * @param string $encodedUserAndPsw A string that was encoded with encodeUserAndPassword()
     *
     * @see UsersManager::encodeUserAndPassword
     *
     * @return string The username value from the encoded string or an empty string if the value could not be read
     */
    public function decodeUserName(string $encodedUserAndPsw){

        $base64Decoded = ConversionUtils::base64ToString($encodedUserAndPsw);

        if (substr_count($base64Decoded, ',') !== 1) {

            return '';
        }

        return base64_decode(explode(',', $base64Decoded)[0]);
    }


    /**
     * Get the original password value from a string containing both user and password which was generated via the
     * encodeUserAndPassword() method.
     *
     * @param string $encodedUserAndPsw A string that was encoded with encodeUserAndPassword()
     *
     * @see UsersManager::encodeUserAndPassword
     *
     * @return string The password value from the encoded string or an empty string if the value could not be read
     */
    public function decodePassword(string $encodedUserAndPsw){

        $base64Decoded = ConversionUtils::base64ToString($encodedUserAndPsw);

        if (substr_count($base64Decoded, ',') !== 1) {

            return '';
        }

        return base64_decode(explode(',', $base64Decoded)[1]);
    }


    /**
     * Save to database the provided user instance or update it if it already exists
     *
     * @param User $user The User instance that we want to save
     *
     * @return int An int containing the dbId value for the user that's been saved.
     */
    public function save(User $user){

        return $this->_databaseObjectsManager->save($user);
    }


    /**
     * Perform the login for the specified username and password.
     *
     * @param string $userName The username for the user we want to login
     * @param string $password The password for the user we want to login
     * @param string $domain The domain to which the user will be logged in
     *
     * @return User[]|string[] An empty array if login failed or an array with two elements if
     *         the login succeeded: First element will be a string with the user token and second element will be the User instance for the
     *         requested user.
     */
    public function login(string $userName, string $password, string $domain = ''){

        if(StringUtils::isEmpty($userName.$password)){

            throw new UnexpectedValueException('userName and password must have a value');
        }

        $user = $this->_databaseObjectsManager->getByPropertyValues(User::class,
            ['userName' => $userName, 'password' => $password, 'domain' => $domain]);

        if(count($user) === 1){

            return [$this->createToken($user[0]), $user[0]];
        }

        return [];
    }


    /**
     * TODO
     *
     * @param string $token
     */
    public function loginByToken(string $token){

    }


    /**
     * Perform a login obtaining the user and password from the encoded credentials
     *
     * @see UsersManager::login
     * @see UsersManager::encodeUserAndPassword
     *
     * @param string $encodedCredentials The user and password credentials as they are encoded by UsersManager::encodeUserAndPassword() method
     * @param string $domain The domain to which the user will be logged in
     *
     * @return See UsersManager::login()
     */
    public function loginFromEncodedCredentials(string $encodedCredentials, string $domain = ''){

        return $this->login($this->decodeUserName($encodedCredentials), $this->decodePassword($encodedCredentials));
    }


    /**
     * Generate a token string for the provided user and stores it on database so it can be later verified
     *
     * @param User $user An instance of the user fo which we want to create a token.
     *
     * @return string
     */
    private function createToken(User $user){

        $token = base64_encode(StringUtils::generateRandom(75, 75).
            StringUtils::limitLen(md5($user->userName).md5($user->password), 25));

        $db = $this->_databaseObjectsManager->getDataBaseManager();
        $tableName = $this->_databaseObjectsManager->tablesPrefix.'token';

        try {

            $db->tableAddRows($tableName, [['token' => $token, 'userdbid' => $user->getDbId()]]);

        } catch (Throwable $e) {

            if(!$db->tableExists($tableName) && $db->tableCreate($tableName,
                ['token varchar(150) NOT NULL', 'userdbid bigint NOT NULL'])){

                return $this->createToken($user);
            }

            throw new UnexpectedValueException('Could not create '.$tableName.' table');
        }

        return $token;
    }


    /**
     * TODO
     *
     * @param string $token
     * @return boolean
     */
    private function isTokenValid(string $token){

        if($this->_depotManager->isFile('tokens', $token)){

            // TODO -. farem servir ja el turbodepot per guardar en FS

            $tokenExpiration = $this->_depotManager->getFile('tokens', $token);

            if(!$tokenExpiration){

                return true;
            }

            // Borrar token
            $this->_depotManager->deleteFile('tokens', $token);
        }

        return false;
    }
}

?>