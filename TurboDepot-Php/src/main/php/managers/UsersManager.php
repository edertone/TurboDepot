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
use org\turbodepot\src\main\php\model\UserObject;
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
     * The users domain on which this instance is currently operating
     *
     * @var string
     */
    private $_domain = '';


    /**
     * Manages a fully featured user system engine
     *
     * @param DataBaseObjectsManager $databaseObjectsManager A DataBaseObjectsManager instance which is fully initialized against a valid database and ready
     *        to operate. This instance will be used by this class to store and read users info.
     */
    public function __construct(DataBaseObjectsManager $databaseObjectsManager, string $domain = ''){

        $this->_databaseObjectsManager = $databaseObjectsManager;
        $this->_domain = $domain;

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
     * Save to database the provided users domain or update it if already exists
     *
     * @param string $domainName The name for the domain to save or update
     * @param string $description The domain description
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True if the domain was correctly saved
     */
    public function saveDomain($domainName, $description = ''){

        StringUtils::forceNonEmptyString($domainName, 'domainName');
        StringUtils::forceString($description, 'description');

        $db = $this->_databaseObjectsManager->getDataBaseManager();
        $tableName = $this->_databaseObjectsManager->tablesPrefix.'domain';

        try {

            $db->tableAddOrUpdateRow($tableName, ['name' => $domainName],
                ['name' => $domainName, 'description' => $description]);

        } catch (Throwable $e) {

            if(!$db->tableExists($tableName) && $db->tableCreate($tableName,
                ['name varchar(250) NOT NULL', 'description varchar(5000) NOT NULL'], ['name'])){

                    $db->tableAddRows($tableName, [['name' => '', 'description' => 'The default root users domain']]);

                    return $this->saveDomain($domainName, $description);
                }

                throw new UnexpectedValueException('Could not add domain '.$domainName.' to db');
        }

        return true;
    }


    /**
     * Check if the provided domain exists on database
     *
     * @param string $domainName The name for the domain we want to check
     *
     * @return boolean True if the domain exists and false otherwise
     */
    public function isDomain($domainName){

        if($domainName === ''){

            return true;
        }

        StringUtils::forceNonEmptyString($domainName, 'domainName');

        try {

            return count($this->_databaseObjectsManager->getDataBaseManager()
                ->tableGetRows($this->_databaseObjectsManager->tablesPrefix.'domain', ['name' => $domainName])) === 1;

        } catch (Throwable $e) {

            return false;
        }
    }


    /**
     * Set the specified domain as the currently active one for this class
     *
     * @param string $domainName The name for the domain we want to currently use
     *
     * @throws UnexpectedValueException
     *
     * @return string The domain name
     */
    public function setDomain($domainName){

        if(!$this->isDomain($domainName)){

            throw new UnexpectedValueException('Domain does not exist '.$domainName);
        }

        return $this->_domain = $domainName;
    }


    /**
     * Save to database the provided user instance or update it if it already exists
     *
     * @param UserObject $user The User instance that we want to save
     *
     * @return int An int containing the dbId value for the user that's been saved.
     */
    public function saveUser(UserObject $user){

        if($user->domain !== $this->_domain){

            throw new UnexpectedValueException('Saving a user with a domain ('.$user->domain.') that doesn\'t match the current one ('.$this->_domain.')');
        }

        return $this->_databaseObjectsManager->save($user);
    }


    /**
     * Check if the specified userName is stored on database for the current domain
     *
     * @param string $userName The user name that we want to check
     *
     * @return boolean True if the user exists on the current domain, false otherwise
     */
    public function isUser(string $userName){

        return count($this->_databaseObjectsManager->getByPropertyValues(UserObject::class,
            ['userName' => $userName, 'domain' => $this->_domain])) === 1;
    }


    /**
     * Save the provided password for the specified user. It will be stored with a one way encryption method so
     * after this method is called the password won't be able to be recovered any more from db.
     *
     * @param string $userName The name for an existing user to which we want to set the password value
     * @param string $password The non encrypted password string to save for the user. It will be one way encrypted on db
     *
     * @throws UnexpectedValueException
     *
     * @return boolean true if the password was correctly set
     */
    public function setUserPassword(string $userName, string $password){

        $db = $this->_databaseObjectsManager->getDataBaseManager();
        $tableName = $this->_databaseObjectsManager->tablesPrefix.'userobject_password';
        $userDbId = $this->_getUserDBId($userName);

        try {

            $db->tableAddOrUpdateRow($tableName, ['userdbid' => $userDbId],
                ['userdbid' => $userDbId, 'password' => password_hash($password, PASSWORD_BCRYPT)]);

        } catch (Throwable $e) {

            if(!$db->tableExists($tableName) && $db->tableCreate($tableName,
                ['userdbid bigint NOT NULL', 'password varchar(500) NOT NULL'], ['userdbid'])){

                return $this->setUserPassword($userName, $password);
            }

            throw new UnexpectedValueException('Could not set user password: '.$e->getMessage());
        }

        return true;
    }


    /**
     * TODO
     */
    public function saveRole($name, $description = ''){

        // TODO
    }


    /**
     * TODO
     */
    public function setRoleToUsers($roleName, array $userNames){

        // TODO
    }


    /**
     * TODO
     */
    public function deleteRole(string $roleName){

        // TODO
    }


    /**
     * Save an email account to the specified user. It will be updated if it already exists on the provided user.
     * All new email accounts that are added to a user will be stored with a non verified status. We must set the verification
     * status of an email account with the method setUserMailVerified(), normally after sending a verification email to the user with
     * the sendUserMailVerification() method.
     *
     * @param string $userName The username for the user to which whe want to add the email account
     * @param string $mail The email account that we want to add or update
     * @param string $comments Comments that will be stored with the email account
     * @param string $data Any extra data which we may need to store related to the email account (normally we use a json encoded string).
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True if the save succeeded
     */
    public function saveUserMail(string $userName, string $mail, string $comments = '', string $data = ''){

        // User Must exist on database to add an email account
        if(!$this->isUser($userName)){

            throw new UnexpectedValueException('Trying to add an email account to a non existing user: '.$userName.' on domain '.$this->_domain);
        }

        StringUtils::forceNonEmptyString($mail, '', 'Invalid mail');

        $db = $this->_databaseObjectsManager->getDataBaseManager();
        $tableName = $this->_databaseObjectsManager->tablesPrefix.'userobject_mail';
        $userDbId = $this->_getUserDBId($userName);

        try {

            $db->tableAddOrUpdateRow($tableName, ['userdbid' => $userDbId, 'mail' => $mail],
                ['userdbid' => $userDbId, 'mail' => $mail, 'isverified' => 0, 'comments' => $comments, 'data' => $data]);

        } catch (Throwable $e) {

            if(!$db->tableExists($tableName) && $db->tableCreate($tableName,
                ['userdbid bigint NOT NULL', 'mail varchar(250) NOT NULL', 'isverified tinyint(1) NOT NULL', 'comments varchar(5000) NOT NULL',
                 'data varchar(25000) NOT NULL'], ['userdbid', 'mail'])){

                 return $this->saveUserMail($userName, $mail, $comments, $data);
            }

            throw new UnexpectedValueException('Could not add mail accounts to '.$userName.' on domain '.$this->_domain.': '.$e->getMessage());
        }

        return true;
    }


    /**
     * TODO
     */
    public function sendUserMailVerification(string $userName, string $mail, string $subject, string $message){

        // TODO - Send an email to the provided user email account so he can click to mark that account as verified
    }


    /**
     * Check if the provided email account is verified for the specified user
     *
     * @param string $userName The username for the user to which whe want to check the mail account
     * @param string $mail The email account that we want to check
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True if the provided mail is verified for the provided user, false otherwise
     */
    public function isUserMailVerified(string $userName, string $mail){

        StringUtils::forceNonEmptyString($mail, '', 'Invalid mail');

        foreach ($this->getUserMails($userName) as $userMail) {

            if($userMail['mail'] === $mail){

                return $userMail['isverified'] === true;
            }
        }

        throw new UnexpectedValueException('Non existing mail: '.$mail.' on user: '.$userName.' on domain '.$this->_domain);
    }


    /**
     * Set the verified status for the specified user email
     *
     * @param string $userName The username for the user to which whe want to update the email verification status
     * @param string $mail The email account that we want to update
     * @param bool $isVerified True to set the email as verified, false to set it as non verified
     *
     * @return boolean True if the provided mail is correctly updated for the provided user
     */
    public function setUserMailVerified(string $userName, string $mail, bool $isVerified){

        if($this->isUserMailVerified($userName, $mail) !== $isVerified){

            $db = $this->_databaseObjectsManager->getDataBaseManager();
            $tableName = $this->_databaseObjectsManager->tablesPrefix.'userobject_mail';

            $db->tableUpdateRow($tableName, ['userdbid' => $this->_getUserDBId($userName), 'mail' => $mail],
                ['isverified' => $isVerified ? 1 : 0]);
        }

        return true;
    }


    /**
     * Get a list with all the email accounts that are linked to the specified user
     *
     * @param string $userName The username for the user from which we want to obtain the emails
     * @param string $filter Set to ALL to get all the user emails, VERIFIED to get only the verified emails and NONVERIFIED to get only the non verified ones
     *
     * @throws UnexpectedValueException In case the provided parameters are not valid
     *
     * @return array Associative array with the list of the user emails or empty array if no emails found. Each array element will have the following keys:
     *         'mail' Containing the email address
     *         'isverified' Containing true if the mail is verified and false if not
     */
    public function getUserMails(string $userName, $filter = 'ALL'){

        if($filter !== 'ALL' && $filter !== 'VERIFIED' &&  $filter !== 'NONVERIFIED'){

            throw new UnexpectedValueException('filter must be VERIFIED, NONVERIFIED or ALL');
        }

        $db = $this->_databaseObjectsManager->getDataBaseManager();
        $tableName = $this->_databaseObjectsManager->tablesPrefix.'userobject_mail';

        $result = [];

        foreach ($db->tableGetRows($tableName, ['userdbid' => $this->_getUserDBId($userName)]) as $row) {

            if($filter === 'ALL' ||
               ($filter === 'VERIFIED' && $row['isverified'] === '1') ||
               ($filter === 'NONVERIFIED' && $row['isverified'] === '0')){

                $result[] = ['mail' => $row['mail'], 'isverified' => $row['isverified'] === '1'];
            }
        }

        return $result;
    }


    /**
     * Delete the provided list of emails for the specified User
     *
     * @param string $userName The username for the user from which we want to delete the mail accounts
     * @param array $mails List of emails to delete. If an empty array is provided, ALL the emails linked to the user will be deleted
     *
     * @throws UnexpectedValueException
     *
     * @return int Number of email accounts that have been deleted
     */
    public function deleteUserMails(string $userName, array $mails){

        $result = 0;
        $db = $this->_databaseObjectsManager->getDataBaseManager();
        $tableName = $this->_databaseObjectsManager->tablesPrefix.'userobject_mail';
        $userDBId = $this->_getUserDBId($userName);

        if(count($mails) <= 0){

            return $db->tableDeleteRows($tableName, ['userdbid' => $userDBId]);
        }

        foreach ($mails as $mail) {

            if(!StringUtils::isString($mail) || strlen($mail) < 3){

                throw new UnexpectedValueException('Invalid mail: '.$mail);
            }

            $result += $db->tableDeleteRows($tableName, ['userdbid' => $userDBId, 'mail' => $mail]);
        }

        return $result;
    }


    /**
     * Aux method to obtain the user dbid from a username at the current domain
     *
     * @param string $userName The user name
     *
     * @throws UnexpectedValueException
     *
     * @return int The user dbid
     */
    private function _getUserDBId(string $userName){

        $user = $this->_databaseObjectsManager->getByPropertyValues(UserObject::class, ['userName' => $userName, 'domain' => $this->_domain]);

        if(count($user) < 1){

            throw new UnexpectedValueException('Non existing user: '.$userName.' on domain '.$this->_domain);
        }

        return $user[0]->getDbId();
    }


    /**
     * TODO
     */
    public function saveOperation($operation){

        // TODO
    }


    /**
     * Perform the login for the specified username and password, and generate a new token to be used for subsequent logins
     *
     * @param string $userName The username for the user we want to login
     * @param string $password The password for the user we want to login
     *
     * @return UserObject[]|string[] An empty array if login failed or an array with two elements if
     *         the login succeeded: First element will be a string with the user token and second element will be the User instance for the
     *         requested user.
     */
    public function login(string $userName, string $password){

        $user = $this->_databaseObjectsManager->getByPropertyValues(UserObject::class,
            ['userName' => $userName, 'domain' => $this->_domain]);

        if(count($user) === 1){

            $dbPassword = $this->_databaseObjectsManager->getDataBaseManager()
                ->tableGetRows($this->_databaseObjectsManager->tablesPrefix.'userobject_password', ['userdbid' => $user[0]->getDbId()]);

            if(!$dbPassword){

                throw new UnexpectedValueException('Specified user does not have a stored password: '.$userName);
            }

            if(count($dbPassword) === 1 && password_verify($password, $dbPassword[0]['password'])){

                return [$this->createToken($user[0]), $user[0]];
            }
        }

        return [];
    }


    /**
     * Generate a token string for the provided user and stores it on database so it can be later verified
     *
     * @param UserObject $user An instance of the user fo which we want to create a token.
     *
     * @return string
     */
    private function createToken(UserObject $user){

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
     *
     * @return UserObject[]|string[] See UsersManager::login()
     */
    public function loginFromEncodedCredentials(string $encodedCredentials){

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

        StringUtils::forceNonEmptyString($token, '', 'token must have a value');

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