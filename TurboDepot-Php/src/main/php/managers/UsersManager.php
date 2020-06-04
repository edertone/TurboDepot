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
use DateTime;
use DateTimeZone;
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
     * Defines the number of seconds that user tokens will be active since they are created. After the number of seconds has passed,
     * tokens will expire and a login will be necessary to obtain a new token. (Note that  tokenLifeTimeReinit will affect this value)
     */
    public $tokenLifeTime = 3600;


    /**
     * If set to true, every time a token is validated the lifetime will be restarted to the configured token lifetime. So the token lifetime
     * will start counting again after the last token validation has been performed. So with a 10 minutes token lifetime if we perform 2 token
     * validations in 5 minutes, the time will still be 10 minutes after the last validation's been performed.
     */
    public $isTokenLifeTimeRecycled = true;


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
     * Check if the specified userName is stored on database for the specified domain
     *
     * @param string $userName The user name that we want to check
     * @param string $domain The domain for which we want to check the user
     *
     * @return boolean True if the user exists on the specified domain, false otherwise
     */
    public function isUser(string $userName, string $domain = ''){

        return count($this->_databaseObjectsManager->getByPropertyValues(User::class,
            ['userName' => $userName, 'domain' => $domain])) === 1;
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
     * Save an email account to the specified user. If the email account already exists linked to the user it will be updated.
     * All new email accounts that are added to a user will be stored with a non verified status. We must set the verification
     * status of an email account with the method setUserMailValidStatus()
     *
     * @param string $userName The username for the user to which whe want to add the email account
     * @param string $domain The domain for the user
     * @param string $mail The email account that we want to add or update
     * @param string $comments Comments that will be stored with the email account
     * @param string $data Any extra data which we may need to store related to the email account (normally we use a json encoded string).
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True if the save succeeded
     */
    public function saveUserMail(string $userName, string $domain, string $mail, string $comments = '', string $data = ''){

        // User Must exist on database to add an email account
        if(!$this->isUser($userName, $domain)){

            throw new UnexpectedValueException('Trying to add an email account to a non existing user: '.$userName.' on domain '.$domain);
        }

        if(StringUtils::isEmpty($mail)){

            throw new UnexpectedValueException('Invalid mail');
        }

        $db = $this->_databaseObjectsManager->getDataBaseManager();
        $tableName = $this->_databaseObjectsManager->tablesPrefix.'user_mail';
        $userDbId = $this->_databaseObjectsManager->getByPropertyValues(User::class, ['userName' => $userName, 'domain' => $domain])[0]->getDbId();

        $rowToAdd = ['userdbid' => $userDbId, 'mail' => $mail, 'isverified' => 0, 'comments' => $comments, 'data' => $data];

        try {

            if(count($db->tableGetRows($tableName, ['userdbid' => $userDbId, 'mail' => $mail])) === 1){

                $db->tableUpdateRow($tableName, ['userdbid' => $userDbId, 'mail' => $mail], $rowToAdd);

            }else{

                $db->tableAddRows($tableName, [$rowToAdd]);
            }

        } catch (Throwable $e) {

            if(!$db->tableExists($tableName) && $db->tableCreate($tableName,
                ['userdbid bigint NOT NULL', 'mail varchar(250) NOT NULL', 'isverified tinyint(1) NOT NULL', 'comments varchar(5000) NOT NULL',
                 'data varchar(25000) NOT NULL'], ['userdbid', 'mail'])){

                 return $this->saveUserMail($userName, $domain, $mail, $comments, $data);
            }

            throw new UnexpectedValueException('Could not add mail accounts to '.$userName.' on domain '.$domain.': '.$e->getMessage());
        }

        return true;
    }


    /**
     * TODO
     */
    public function sendUserMailValidation(User $user, $mail, $subject, $message){

        // TODO - Send an email to the provided user email account so he can click to mark that account as valid
    }


    /**
     * Check if the provided email account is verified for the specified user
     *
     * @param string $userName The username for the user to which whe want to check the mail account
     * @param string $domain The domain for the user
     * @param string $mail The email account that we want to check
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True if the provided mail is verified for the provided user, false otherwise
     */
    public function isUserMailValid(string $userName, string $domain, string $mail){

        if(StringUtils::isEmpty($mail)){

            throw new UnexpectedValueException('Invalid mail');
        }

        $db = $this->_databaseObjectsManager->getDataBaseManager();
        $tableName = $this->_databaseObjectsManager->tablesPrefix.'user_mail';

        $user = $this->_databaseObjectsManager->getByPropertyValues(User::class, ['userName' => $userName, 'domain' => $domain]);

        if(count($user) < 1){

            throw new UnexpectedValueException('Non existing user: '.$userName.' on domain '.$domain);
        }

        $userMail = $db->tableGetRows($tableName, ['userdbid' =>  $user[0]->getDbId(), 'mail' => $mail]);

        if(count($userMail) < 1){

            throw new UnexpectedValueException('Non existing mail: '.$mail.' on user: '.$userName.' on domain '.$domain);
        }

        return $userMail[0]['isverified'] === '1';
    }


    /**
     * TODO
     */
    public function setUserMailValidStatus(string $userName, string $domain, string $mail, bool $isValid){

        // TODO - Given a user and a mail account, this method will set the status of that accont as valid or invalid
    }


    /**
     * TODO
     */
    public function getUserMails(User $user, $getValidOnes = true, $getInvalidOnes = true){

        // TODO - Get a list with the required email accounts that are linked to the provided user
    }


    public function deleteUserMails(User $user, array $mails){

        // TODO - Delete from db all the email accounts that are linked to the provided user, so they are not anymore
    }


    /**
     * Perform the login for the specified username and password, and generate a new token to be used for subsequent logins
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

        $user = $this->_databaseObjectsManager->getByPropertyValues(User::class,
            ['userName' => $userName, 'password' => $password, 'domain' => $domain]);

        if(count($user) === 1){

            return [$this->createToken($user[0]), $user[0]];
        }

        return [];
    }


    /**
     * Generate a token string for the provided user and stores it on database so it can be later verified
     *
     * @param User $user An instance of the user fo which we want to create a token.
     *
     * @return string
     */
    private function createToken(User $user){

        $expiryDate = (new DateTime('+'.$this->tokenLifeTime.' seconds', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $token = base64_encode(StringUtils::generateRandom(75, 75).
            StringUtils::limitLen(md5($user->userName).md5($expiryDate), 25));

        $db = $this->_databaseObjectsManager->getDataBaseManager();
        $tableName = $this->_databaseObjectsManager->tablesPrefix.'token';

        // If a token for the given user already exists, we will simply recycle it and return it
        $existingTokens = $db->tableGetRows($tableName, ['userdbid' => $user->getDbId()]);

        if($existingTokens !== false && count($existingTokens) === 1 && $this->isTokenValid($existingTokens[0]['token'])){

            return $existingTokens[0]['token'];
        }

        try {

            $db->tableAddRows($tableName, [['token' => $token, 'userdbid' => $user->getDbId(), 'expires' => $expiryDate]]);

        } catch (Throwable $e) {

            if(!$db->tableExists($tableName) && $db->tableCreate($tableName,
               ['token varchar(150) NOT NULL', 'userdbid bigint NOT NULL', 'expires '.$db->getSQLDateTimeType(false)])){

                return $this->createToken($user);
            }

            throw new UnexpectedValueException('Could not create '.$tableName.' table: '.$e->getMessage());
        }

        return $token;
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
     * @return User[]|string[] See UsersManager::login()
     */
    public function loginFromEncodedCredentials(string $encodedCredentials, string $domain = ''){

        return $this->login($this->decodeUserName($encodedCredentials), $this->decodePassword($encodedCredentials));
    }


    /**
     * Test that the current token is active and valid, and that it is allowed to perform the specified list of user operations
     *
     * @param string $token An active and valid user token
     * @param array $operations TODO
     *
     * @return True if the token is valid for the provided list of operations or false otherwise.
     */
    public function isTokenValid(string $token, array $operations = []){

        if(StringUtils::isEmpty($token)){

            throw new UnexpectedValueException('token must have a value');
        }

        $db = $this->_databaseObjectsManager->getDataBaseManager();
        $tableName = $this->_databaseObjectsManager->tablesPrefix.'token';
        $tokenData = $db->tableGetRows($tableName, ['token' => $token]);

        if(count($tokenData) === 1){

            if(new DateTime($tokenData[0]['expires'], new DateTimeZone('UTC')) > new DateTime(null, new DateTimeZone('UTC'))){

                if($this->isTokenLifeTimeRecycled === true){

                    $newExpiryDate = (new DateTime('+'.$this->tokenLifeTime.' seconds', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

                    $db->tableUpdateRow($tableName, ['token' => $tokenData[0]['token']], ['expires' => $newExpiryDate]);
                }

                return true;
            }

            if($db->tableDeleteRows($tableName, ['token' => $tokenData[0]['token']]) === 0){

                throw new UnexpectedValueException('Could not delete expired token from db');
            }
        }

        return false;
    }


    /**
     * Perform the logout to destroy the specified user token
     *
     * @param string $token The token that represents the user we want to logout
     *
     * @return boolean True on success, false on failure. Note that this method will always silently fail,
     *         cause it is designed to give the less possible information
     */
    public function logout($token){

        if(StringUtils::isEmpty($token)){

            return false;
        }

        $db = $this->_databaseObjectsManager->getDataBaseManager();
        $tableName = $this->_databaseObjectsManager->tablesPrefix.'token';

        // Purge the provided token
        if($db->tableDeleteRows($tableName, ['token' => $token]) === 0){

            return false;
        }

        // Purge all the other possibly expired tokens
        $this->_databaseObjectsManager->getDataBaseManager()->query('DELETE FROM '
            .$this->_databaseObjectsManager->tablesPrefix.'token WHERE expires < NOW()');

        return true;
    }
}

?>