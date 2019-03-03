<?php

/**
 * TurboDepot is a cross language ORM: Save, read, list, filter and easily perform any storage operation with your application objects
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */


namespace org\turbodepot\src\main\php\managers;

use UnexpectedValueException;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbocommons\src\main\php\utils\ConversionUtils;


/**
 * Manages all the user system features and operations
 */
class UsersManager extends BaseStrictClass{


    /**
     * Contains the turbo users setup data that's been loaded by this class
     */
    private $_setup = null;


    /**
     * Manages all the user system features and operations
     *
     * @param /stdClass|string $setup Full or relative path to the turbodepot.json file that contains the turbodepot setup or
     *        an stdclass instance with the setup file data decoded from json
     */
    public function __construct($setup){

        // Get the setup data from the received parameter
        if(is_string($setup) && is_file($setup)){

            $this->_setup = json_decode((new FilesManager())->readFile($setup));

        }else if (is_object($setup) && property_exists($setup, '$schema'){

            $this->_setup = $setup;

        }else{

            throw new UnexpectedValueException('UsersManager constructor expects a valid path to users setup or an stdclass instance with the setup data');
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
     * @return string The username value from the encoded string
     */
    public function decodeUserName(string $encodedUserAndPsw){

        return base64_decode(explode(',', ConversionUtils::base64ToString($encodedUserAndPsw))[0]);
    }


    /**
     * Get the original password value from a string containing both user and password which was generated via the
     * encodeUserAndPassword() method.
     *
     * @param string $encodedUserAndPsw A string that was encoded with encodeUserAndPassword()
     *
     * @see UsersManager::encodeUserAndPassword
     *
     * @return string The password value from the encoded string
     */
    public function decodePassword(string $encodedUserAndPsw){

        return base64_decode(explode(',', ConversionUtils::base64ToString($encodedUserAndPsw))[1]);
    }


    /**
     * TODO
     */
    public function login(string $userName, string $password, string $domain = ''){

        $loginOk = true;

        if($loginOk){

            return $this->saveToken($this->encodeToken($userName, $password));
        }

        return '';
    }


    /**
     * TODO
     */
    public function loginFromEncodedCredentials(string $encodedCredentials, string $domain = ''){

        $userName = $usersManager->decodeUserName($encodedCredentials);
        $psw = $usersManager->decodePassword($encodedCredentials);


    }


    /**
     * TODO
     */
    public function createToken(string $userName, string $domain = ''){

        return base64_encode(md5($userName).md5($password));
    }


    /**
     * TODO
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


    /**
     * TODO
     */
    private function encodeToken(string $userName, string $password){

        return base64_encode(md5($userName).md5($password));
    }


    /**
     * TODO
     */
    private function saveToken(string $token){

        $token = new DepotFile();

        $token->setName($token);
        $token->setContent(curddate() + $this->_setup->tokenLifetime);

        $this->_depotManager->saveFile('tokens', $token);

        return $token;
    }


    /**
     * TODO
     */
    private function createDomain(string $name, string $description){

        // TODO
    }
}

?>