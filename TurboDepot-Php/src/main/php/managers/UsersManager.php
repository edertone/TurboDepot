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

use Throwable;
use stdClass;
use DateTime;
use DateTimeZone;
use UnexpectedValueException;
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbocommons\src\main\php\utils\ConversionUtils;
use org\turbodepot\src\main\php\model\UserObject;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbocommons\src\main\php\utils\ArrayUtils;
use org\turbodepot\src\main\php\model\DataBaseObject;


/**
 * Users management class
 */
class UsersManager extends BaseStrictClass{


    /**
     * 3600 by default
     *
     * Defines the number of seconds that user tokens will be active since they are created. After the number of seconds has passed,
     * tokens will expire and it will be necessary to obtain a new token. (Note that isTokenLifeTimeRecycled will affect this value)
     *
     * @var int
     */
    public $tokenLifeTime = 3600;


    /**
     * null by default
     *
     * Defines the number of times that user tokens will be allowed to be used since they are created. After a token is validated or used
     * this number of times, the token will be considered as expired and it will be necessary to obtain a new token.
     *
     * Setting this value to null means there's no limit on number of token uses
     *
     * @var int
     */
    public $tokenUseCount = null;


    /**
     * True by default
     *
     * If set to true, every time a token is validated the lifetime will be restarted to the configured token lifetime. So the token lifetime
     * will start counting again after the last token validation has been performed. So with a 10 minutes token lifetime if we perform 2 token
     * validations in 5 minutes, the time will still be 10 minutes after the last validation's been performed.
     *
     * @var boolean
     */
    public $isTokenLifeTimeRecycled = true;


    /**
     * Flag that configures how does the class behave when multiple user logins and logouts are performed in parallel.
     *
     * If set to true, performing several consecutive logins and logouts will not delete previously created tokens, so in fact
     * the same user may be able to start different parallel user sessions.
     *
     * If set to false, any new login or logout operation will cause all the previous user tokens to be destroyed, effectively
     * invalidating all previous sessions.
     *
     * Default value is true
     *
     * @var boolean
     */
    public $isMultipleUserSessionsAllowed = true;


    /**
     * The users domain on which this instance is currently operating.
     * A domain is like a folder to store users and roles. Each domain is independent from the others allowing us to store isolated
     * groups of users. Default domain name is empty and behaves as the "root folder"
     *
     * @var string
     */
    private $_domain = '';


    /**
     * An instance of the DataBaseObjectsManager class that is used by this class to interact with db objects
     *
     * @var DataBaseObjectsManager
     */
    private $_databaseObjectsManager = null;


    /**
     * An instance of the DataBaseManager class that is used by this class to interact with db
     * @var DataBaseManager
     */
    private $_db;


    /**
     * Stores the sql data type that can be used anywhere on this class to reference a big unsigned integer SQL type on object tables.
     * It is stored globally to improve performance instead of calculating it every time.
     * @var string
     */
    private $_unsignedBigIntSqlTypeDef = '';


    /**
     * The name of the domains table
     * @var string
     */
    private $_tableDomain;


    /**
     * The name of the roles table
     * @var string
     */
    private $_tableRole;


    /**
     * The name of the operations table
     * @var string
     */
    private $_tableOperation;


    /**
     * The name of the table that contains which roles are allowed for operations
     * @var string
     */
    private $_tableOperationRole;


    /**
     * The name of the user object table
     * @var string
     */
    private $_tableUserObject;


    /**
     * The name of the table that contains user extra custom fields
     * @var string
     */
    private $_tableUserCustomFieldsObject;


    /**
     * The name of the password table
     * @var string
     */
    private $_tableUserPsw;


    /**
     * The name of the mail table
     * @var string
     */
    private $_tableUserMail;


    /**
     * The name of the token table
     * @var string
     */
    private $_tableToken;


    /**
     * Text to be used as the description for the default empty users domain the first time it is created by this class
     * @var string
     */
    private $_emptyDomainDescription = 'The default root users domain';


    /**
     * Manages a fully featured user system engine
     *
     * @param DataBaseObjectsManager $databaseObjectsManager A DataBaseObjectsManager instance which is fully initialized against a valid database and ready
     *        to operate. This instance will be used by this class to store and read users info.
     */
    public function __construct(DataBaseObjectsManager $databaseObjectsManager, string $domain = ''){

        $this->_databaseObjectsManager = $databaseObjectsManager;
        $this->_db = $this->_databaseObjectsManager->getDataBaseManager();

        if(!$this->_db->isConnected()){

            throw new UnexpectedValueException('No active connection to database available for the provided DataBaseObjectsManager');
        }

        $this->_unsignedBigIntSqlTypeDef = $this->_db->getSQLTypeFromValue(999999999999999, false, true);

        $this->_tableDomain = $this->_databaseObjectsManager->tablesPrefix.'domain';
        $this->_tableRole = $this->_databaseObjectsManager->tablesPrefix.'role';
        $this->_tableOperation = $this->_databaseObjectsManager->tablesPrefix.'operation';
        $this->_tableOperationRole = $this->_databaseObjectsManager->tablesPrefix.'operation_roles';
        $this->_tableUserObject = $this->_databaseObjectsManager->tablesPrefix.'userobject';
        $this->_tableUserPsw = $this->_databaseObjectsManager->tablesPrefix.'userobject_password';
        $this->_tableUserMail = $this->_databaseObjectsManager->tablesPrefix.'userobject_mails';
        $this->_tableUserCustomFieldsObject = $this->_databaseObjectsManager->tablesPrefix.'userobject_customfields';
        $this->_tableToken = $this->_databaseObjectsManager->tablesPrefix.'token';

        try {

            $this->setDomain($domain);

        } catch (Throwable $e) {

            if($domain === ''){

                $this->saveDomain($domain, $this->_emptyDomainDescription);

            }else{

                throw $e;
            }
        }
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
    public function saveDomain(string $domainName, string $description = ''){

        if($domainName !== ''){

            StringUtils::forceNonEmptyString($domainName, 'domainName');
        }

        StringUtils::forceString($description, 'description');

        try {

            $this->_db->tableAddOrUpdateRow($this->_tableDomain, ['name' => $domainName],
                ['name' => $domainName, 'description' => $description]);

        } catch (Throwable $e) {

            if($this->_db->tableAlterToFitDefinition($this->_tableDomain, [
                'columns' => ['name varchar(250) NOT NULL', 'description varchar(5000) NOT NULL'],
                'primaryKey' => ['name']])){

                if($domainName !== ''){

                    $this->_db->tableAddRows($this->_tableDomain, [['name' => '', 'description' => $this->_emptyDomainDescription]]);
                }

                return $this->saveDomain($domainName, $description);
            }

            throw new UnexpectedValueException('Could not save domain '.$domainName.' to db: '.$e->getMessage());
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

        if($domainName !== ''){

            StringUtils::forceNonEmptyString($domainName, 'domainName');
        }

        try {

            return count($this->_db->tableGetRows($this->_tableDomain, ['name' => $domainName])) === 1;

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
     * Save (create) to database the specified users role for the currently active domain or update it if already exists.
     *
     * @param string $roleName The name for the role we want to save or update
     * @param string $description The description we want to set to the role
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True if the role was correctly saved
     */
    public function saveRole(string $roleName, string $description = ''){

        StringUtils::forceNonEmptyString($roleName, 'roleName');
        StringUtils::forceString($description, 'description');

        try {

            $this->_db->tableAddOrUpdateRow($this->_tableRole, ['domain' => $this->_domain, 'name' => $roleName],
                ['domain' => $this->_domain, 'name' => $roleName, 'description' => $description]);

        } catch (Throwable $e) {

            if($this->_db->tableAlterToFitDefinition($this->_tableRole, [
                'columns' => ['domain varchar(250) NOT NULL', 'name varchar(250) NOT NULL', 'description varchar(5000) NOT NULL'],
                'primaryKey' => ['domain', 'name'],
                'foreignKey' => [[$this->_tableRole.'_'.$this->_tableDomain.'_fk', ['domain'], $this->_tableDomain, ['name']]]])){

                return $this->saveRole($roleName, $description);
            }

            throw new UnexpectedValueException('Could not save role '.$roleName.' to db: '.$e->getMessage());
        }

        return true;
    }


    /**
     * Check if the specified role name is stored on database for the current domain
     *
     * @param string $roleName The role name that we want to check
     *
     * @return boolean True if the role exists on the current domain, false otherwise
     */
    public function isRole(string $roleName){

        StringUtils::forceNonEmptyString($roleName, 'roleName');

        try {

            return count($this->_db->tableGetRows($this->_tableRole, ['name' => $roleName, 'domain' => $this->_domain])) === 1;

        } catch (Throwable $e) {

            return false;
        }
    }


    /**
     * Check if the specified user has the specified role assigned.
     * Users may have more than one roles, this method tests if a specific user has the given role assigned or not.
     *
     * @param string $userName   The name for the user that we want to check
     * @param string $roleName The role name that we want to check
     *
     * @throws UnexpectedValueException If the role does not exist
     *
     * @return boolean True if the role is assigned to the user, false otherwise
     */
    public function isUserAssignedToRole(string $userName, string $roleName){

        if(!$this->isRole($roleName)){

            throw new UnexpectedValueException('Invalid or non existant role specified');
        }

        return in_array($roleName, $this->findUserByUserName($userName)->roles);
    }


    /**
     * Remove the specified role for the current domain
     *
     * @param string $roleName The name for an existing role on the actual domain
     *
     * @throws UnexpectedValueException
     *
     * @return bool True on success
     */
    public function deleteRole(string $roleName){

        StringUtils::forceNonEmptyString($roleName, 'roleName');

        // TODO - After deleting a role, we must clear it from all the users that use it, but without deleting the users
        // TODO - check any impact that deleting the role would have. Maybe if it is the only one that exists, we cannot leave
        // users without a role?

        try {

            $this->_db->transactionBegin();

            // Delete all operations containing this role
            if($this->_db->tableDeleteRows($this->_tableOperationRole, ['domain' => $this->_domain, 'role' => $roleName]) !== 1){

                throw new UnexpectedValueException('Error removing '.$roleName.': unexpected error');
            }

            // Delete the role from the table itself
            if($this->_db->tableDeleteRows($this->_tableRole, ['domain' => $this->_domain, 'name' => $roleName]) !== 1){

                throw new UnexpectedValueException('Error removing '.$roleName.': unexpected error');
            }

            $this->_db->transactionCommit();

        } catch (Throwable $e) {

            $this->_db->transactionRollback();

            throw $e;
        }

        return true;
    }


    /**
     * Save to database the provided user instance or update it if it already exists.
     *
     * Notice that to update the user password you must call an independent method: setUserPassword()
     *
     * @see UsersManager::setUserPassword
     *
     * @param UserObject $user The User instance that we want to save
     *
     * @return int An int containing the dbId value for the user that's been saved.
     */
    public function saveUser(UserObject $user){

        StringUtils::forceNonEmptyString($user->userName, '', 'no user name specified');

        if($user->domain !== $this->_domain){

            throw new UnexpectedValueException('Saving a user with a domain ('.$user->domain.') that doesn\'t match the current one ('.$this->_domain.')');
        }

        // make sure the provided roles exist on the current domain
        if(is_array($user->roles)){

            foreach ($user->roles as $role) {

                if(!$this->isRole($role)){

                    throw new UnexpectedValueException('role '.$role.' does not exist on domain '.$this->_domain);
                }
            }
        }

        try {

            return $this->_databaseObjectsManager->save($user);

        } catch (Throwable $e) {

            if($user->getDbId() === null && $this->isUser($user->userName)){

                throw new UnexpectedValueException('User '.$user->userName.' already exists on domain '.$this->_domain);
            }

            throw $e;
        }
    }


    /**
     * Check if the specified userName is stored on database for the current domain
     *
     * @param string $userName The user name that we want to check
     *
     * @return boolean True if the user exists on the current domain, false otherwise
     */
    public function isUser(string $userName){

        return count($this->_databaseObjectsManager->findByPropertyValues(UserObject::class,
            ['userName' => $userName, 'domain' => $this->_domain])) === 1;
    }


    /**
     * Save custom information (extra arbitrary fields) for the provided user.
     * Any existing custom info previously saved will be overwritten.
     *
     * @param string $userName The name for an existing user to which we want to set the custom fields.
     * @param DataBaseObject $customFields An extended DataBaseObject containing the values for all the extra properties we want
     *        to store for this user. This will be used to define the data types and value for each custom field we want to store
     *
     * @throws UnexpectedValueException If the custom fields cannot be saved.
     *
     * @return int The dbId of the user if the custom fields were successfully saved.
     */
    public function saveUserCustomFields(string $userName, DataBaseObject $customFields){

        // Validate inputs
        $userDbId = $this->_getUserDBId($userName);
        $this->_databaseObjectsManager->validateObject($customFields);

        try {

            $this->_db->transactionBegin();

            // Generate a table definition for the custom fields to save, and alter the database if necessary to fit it
            $tableDef = [
                'primaryKey' => ['dbid'],
                'foreignKey' => [[$this->_tableUserCustomFieldsObject.'_'.$this->_tableUserObject.'_fk', ['dbid'], $this->_tableUserObject, ['dbid']]],
                'columns' => ['dbid '.$this->_unsignedBigIntSqlTypeDef]
            ];

            // Store the values for each column on the table data structure
            $tableData = ['dbid' => $userDbId];

            foreach ($this->_databaseObjectsManager->getBasicProperties($customFields) as $property) {

                $tableDef['columns'][] = strtolower($property).' '.$this->_databaseObjectsManager->getSQLTypeFromObjectProperty($customFields, $property);
                $tableData[strtolower($property)] = $customFields->{$property};
            }

            $this->_db->tableAlterToFitDefinition($this->_tableUserCustomFieldsObject, $tableDef);
            $this->_db->tableAddOrUpdateRow($this->_tableUserCustomFieldsObject, ['dbid' => $userDbId], $tableData);
            $this->_db->transactionCommit();

            return $userDbId;

        } catch (Throwable $e) {

            $this->_db->transactionRollback();

            throw new UnexpectedValueException('Could not save user custom fields: '.$e->getMessage());
        }
    }


    /**
     * TODO
     * @param string $userName
     * @param DataBaseObject $customFields
     */
    public function getUserCustomFields(string $userName, DataBaseObject $customFields){

    }


    /**
     * Save (create or update) the provided password for the specified user. It will be stored with a one way encryption method so
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

        $userDbId = $this->_getUserDBId($userName);

        try {

            $this->_db->tableAddOrUpdateRow($this->_tableUserPsw, ['dbid' => $userDbId],
                ['dbid' => $userDbId, 'password' => password_hash($password, PASSWORD_BCRYPT)]);

        } catch (Throwable $e) {

            if($this->_db->tableAlterToFitDefinition($this->_tableUserPsw, [
                'columns' => ['dbid '.$this->_unsignedBigIntSqlTypeDef, 'password varchar(500) NOT NULL'],
                'primaryKey' => ['dbid'],
                'foreignKey' => [[$this->_tableUserPsw.'_'.$this->_tableUserObject.'_fk', ['dbid'], $this->_tableUserObject, ['dbid']]]
            ])){

                return $this->setUserPassword($userName, $password);
            }

            throw new UnexpectedValueException('Could not set user password: '.$e->getMessage());
        }

        return true;
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

        $user = $this->_databaseObjectsManager->findByPropertyValues(UserObject::class, ['userName' => $userName, 'domain' => $this->_domain]);

        if(empty($user)){

            throw new UnexpectedValueException('Non existing user: '.$userName.' on domain '.$this->_domain);
        }

        return $user[0]->getDbId();
    }


    /**
     * Save an email account to the specified user. It will be updated if it already exists on the provided user.
     * All new email accounts that are added to a user will be stored with a non verified status. To verify the email we must generate a verification
     * hash with getUserMailVerificationHash() and then verify it with verifyUserMail() after sending it to the user email account via a link.
     * We can also use setUserMailVerified() directly to skip all the hash verification process.
     *
     * @param string $userName The username on the currently active domain to which whe want to add the email account
     * @param string $mail The email account that we want to add or update
     * @param string $comments Comments that will be stored with the email account
     * @param string $data Any extra data which we may need to store related to the email account (a json encoded string for example).
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

        $userDbId = $this->_getUserDBId($userName);

        try {

            // Generate a verification hash that will be used later to mark this account as verified
            $verificationHash = StringUtils::generateRandom(20, 20, ['0-9', 'a-z', 'A-Z']);

            $this->_db->tableAddOrUpdateRow($this->_tableUserMail, ['dbid' => $userDbId, 'mail' => $mail],
                ['dbid' => $userDbId, 'mail' => $mail, 'isverified' => 0, 'verificationHash' => $verificationHash, 'comments' => $comments, 'data' => $data]);

        } catch (Throwable $e) {

            if($this->_db->tableAlterToFitDefinition($this->_tableUserMail, [
                'columns' => ['dbid '.$this->_unsignedBigIntSqlTypeDef, 'mail varchar(250) NOT NULL', 'isverified tinyint(1) NOT NULL',
                              'verificationhash varchar(20)', 'comments varchar(1000) NOT NULL', 'data TEXT NOT NULL'],
                'primaryKey' => ['dbid', 'mail'],
                'foreignKey' => [[$this->_tableUserMail.'_'.$this->_tableUserObject.'_fk', ['dbid'], $this->_tableUserObject, ['dbid']]]])){

                return $this->saveUserMail($userName, $mail, $comments, $data);
            }

            throw new UnexpectedValueException('Could not add mail accounts to '.$userName.' on domain '.$this->_domain.': '.$e->getMessage());
        }

        return true;
    }


    /**
     * Check if the specified email account exists for the provided username
     *
     * @param string $userName The username for the user to which we want to check the mail account
     * @param string $userMail The email account that we want to check
     *
     * @throws UnexpectedValueException If the provided parameters are not valid
     *
     * @return boolean True if the provided mail is owned by the provided user, false otherwise
     */
    public function isUserMail(string $userName, string $userMail){

        StringUtils::forceNonEmptyString($userMail, '', 'Invalid mail');

        $mails = $this->_db->tableGetRows($this->_tableUserMail,
            ['dbid' => $this->_getUserDBId($userName), 'mail' => $userMail]);

        return count($mails) === 1;
    }


    /**
     * Obtain the hash string that is used to verify the provided email account
     *
     * @param string $userName The username for the user
     * @param string $userMail The email account for which we want to obtain the verification hash
     *
     * @throws UnexpectedValueException If the provided parameters are not valid
     *
     * @return string The 20 digit hash string that must be used to verify the provided mail
     */
    public function getUserMailVerificationHash(string $userName, string $userMail){

        StringUtils::forceNonEmptyString($userMail, '', 'Invalid mail');

        // Search for the mail verification hash
        $mailRows = $this->_db->tableGetRows($this->_tableUserMail, ['dbid' => $this->_getUserDBId($userName), 'mail' => $userMail]);

        if(count($mailRows) !== 1){

            throw new UnexpectedValueException('Mail '.$userMail.' does not belong to user '.$userName);
        }

        if(StringUtils::isEmpty($mailRows[0]['verificationhash'])){

            throw new UnexpectedValueException('Mail '.$userMail.' is already verified');
        }

        return $mailRows[0]['verificationhash'];
    }


    /**
     * Obtain an URI that can be used to fully verify a user mail account.
     *
     * @param string $userName The username for the user
     * @param string $userMail The email account for which we want to obtain the verification URI
     * @param string $extra optionally we can append also an extra string at the end of the URI, for any custom purpose.
     *
     * @throws UnexpectedValueException If the provided parameters are not valid
     *
     * @return string A URI ready to be used with any url to send the mail verification info. Format will be: username/userMail/verificationHash/extra
     *         (each value between the / will be base64 encoded)
     */
    public function getUserMailVerificationURI(string $userName, string $userMail, string $extra = ''){

        $uri = base64_encode($userName).'/'.base64_encode($userMail).'/'.base64_encode($this->getUserMailVerificationHash($userName, $userMail));

        if(!StringUtils::isEmpty($extra)){

            $uri .= '/'.base64_encode($extra);
        }

        return $uri;
    }


    /**
     * Execute the verification for the mail related to the specified user.
     * If the verification hash matches the one on the email, that email account will be marked as verified.
     *
     * @param string $userName The username for the user
     * @param string $userMail The email account that we want to verify
     * @param string $verificationHash The hash that must match the one on the mail account for it to be verified. You
     *        can obtain this hash via the getUserMailVerificationHash() method
     *
     * @throws UnexpectedValueException If the provided parameters are not valid
     *
     * @return int Three possible values: -1 If mail could not be verified, 0 if mail was correctly verified and 1 if mail was already verified before
     */
    public function verifyUserMail(string $userName, string $userMail, string $verificationHash){

        StringUtils::forceNonEmptyString($userName, '', 'Invalid mail');
        StringUtils::forceNonEmptyString($userMail, '', 'Subject cannot be empty');
        StringUtils::forceNonEmptyString($verificationHash, '', 'Body cannot be empty');

        if(!$this->isUserMail($userName, $userMail)){

            throw new UnexpectedValueException('Mail '.$userMail.' does not belong to user '.$userName);
        }

        // Already verified email will return true
        if($this->isUserMailVerified($userName, $userMail)){

            return 1;
        }

        // Look for the mail that matches the verification hash and set it as verified
        try {

            $this->_db->tableUpdateRow($this->_tableUserMail,
                    ['dbid' => $this->_getUserDBId($userName),
                     'mail' => $userMail,
                     'verificationhash' => $verificationHash
                    ], ['isverified' => '1', 'verificationhash' => '']);

        } catch (Throwable $e) {

            return -1;
        }

        return 0;
    }


    /**
     * Manually set the verified status for the specified user email.
     * This bypasses the mail verification process, so it is recommended to use the verifyUserMail method via the provided mail hash.
     *
     * @param string $userName The username for the user to which whe want to update the email verification status
     * @param string $mail The email account that we want to update
     * @param bool $isVerified True to set the email as verified, false to set it as non verified
     *
     * @return boolean True if the provided mail is correctly updated for the provided user
     */
    public function setUserMailVerified(string $userName, string $mail, bool $isVerified){

        if($this->isUserMailVerified($userName, $mail) !== $isVerified){

            // Generate a verification hash if the account is set as not verified
            $verificationHash = $isVerified ? '' : StringUtils::generateRandom(20, 20, ['0-9', 'a-z', 'A-Z']);

            $this->_db->tableUpdateRow($this->_tableUserMail, ['dbid' => $this->_getUserDBId($userName), 'mail' => $mail],
                ['isverified' => $isVerified ? 1 : 0, 'verificationhash' => $verificationHash]);
        }

        return true;
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

        $result = [];

        foreach ($this->_db->tableGetRows($this->_tableUserMail, ['dbid' => $this->_getUserDBId($userName)]) as $row) {

            if($filter === 'ALL' ||
               ($filter === 'VERIFIED' && $row['isverified'] === '1') ||
               ($filter === 'NONVERIFIED' && $row['isverified'] === '0')){

                $result[] = ['mail' => $row['mail'], 'isverified' => $row['isverified'] === '1'];
            }
        }

        return $result;
    }


    /**
     * Obtain a list of all the operations that are allowed for the provided user on the current domain
     *
     * @param string $userName The username we want to check
     *
     * @return array List of allowed operations alphabetically sorted
     */
    public function getUserOperations(string $userName){

        StringUtils::forceNonEmptyString($userName, 'userName');

        if(!$this->isUser($userName)){

            throw new UnexpectedValueException("User $userName does not exist on domain: $this->_domain");
        }

        $list = [];
        $tableUser = $this->_tableUserObject;
        $tableUserRoles = $this->_tableUserObject."_roles";
        $tableOperationRole = $this->_tableOperationRole;

        if (!$this->_db->tableExists($tableOperationRole)) {

            return [];
        }

        // Find all operations that are assigned for all roles
        $queryResult = $this->_db->query("SELECT operation FROM $tableOperationRole WHERE domain='$this->_domain' AND role=''");

        foreach ($queryResult as $result) {

            $list[] = $result['operation'];
        }

        // Find all the operations that are specific to the provided user
        $q  = "SELECT DISTINCT o.operation FROM $tableUser u, $tableUserRoles rs, $tableOperationRole o ";
        $q .= "WHERE u.domain='$this->_domain' AND u.username='$userName' AND u.dbid = rs.dbid AND rs.value = o.role";
        $queryResult = $this->_db->query($q);

        foreach ($queryResult as $result) {

            $list[] = $result['operation'];
        }

        $list = array_values(array_unique($list));

        sort($list);

        return $list;
    }


    /**
     * Get a user instance from the database using the provided token.
     *
     * @param string $token The previously obtained token that represents the user we want to retrieve
     *
     * @throws UnexpectedValueException If the token is not valid
     *
     * @return UserObject The user instance if the token is valid
     */
    public function findUserByToken(string $token){

        StringUtils::forceNonEmptyString($token, 'token');

        $tokenData = $this->_db->tableGetRows($this->_tableToken, ['token' => $token]);

        if(empty($tokenData)){

            throw new UnexpectedValueException('Invalid token: '.$token);
        }

        return $this->_databaseObjectsManager->findByDbId(UserObject::class, $tokenData[0]['userdbid']);
    }


    /**
     * Get from database a user instance on the current users domain given a valid username string.
     *
     * @param string $userName A valid user name
     *
     * @throws UnexpectedValueException If the user name is not valid
     *
     * @return UserObject The user instance if the user name is valid
     */
    public function findUserByUserName(string $userName){

        StringUtils::forceNonEmptyString($userName, 'username');

        $user = $this->_databaseObjectsManager->findByPropertyValues(UserObject::class,
            ['userName' => $userName, 'domain' => $this->_domain]);

        if(count($user) === 1){

            return $user[0];
        }

        throw new UnexpectedValueException('Invalid user');
    }


    /**
     * Delete the provided list of emails from the specified User (on the currently active domain)
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
        $userDBId = $this->_getUserDBId($userName);

        if(empty($mails)){

            return $this->_db->tableDeleteRows($this->_tableUserMail, ['dbid' => $userDBId]);
        }

        foreach ($mails as $mail) {

            if(!StringUtils::isString($mail) || strlen($mail) < 3){

                throw new UnexpectedValueException('Invalid mail: '.$mail);
            }

            $result += $this->_db->tableDeleteRows($this->_tableUserMail, ['dbid' => $userDBId, 'mail' => $mail]);
        }

        return $result;
    }


    /**
     * Delete a user from database on the currently active domain
     *
     * @param string $userName The userName for whom the related instance will be deleted from database
     *
     * @throws UnexpectedValueException If the user cannot be deleted
     *
     * @return boolean true if the delete was successful
     */
    public function deleteUser($userName){

        StringUtils::forceNonEmptyString($userName, 'userName');

        $this->_databaseObjectsManager->deleteByDbIds(UserObject::class, [$this->_getUserDBId($userName)]);

        return true;
    }


    /**
     * Delete a list of users from database on the currently active domain.
     * Method is transactional so if any of the objects can't be deleted, none will be.
     * All data related to the users will be deleted in cascade on the database.
     *
     * @param array $userNames An array of usernames for whom the related instances will be deleted from database
     *
     * @throws UnexpectedValueException If any of the users cannot be deleted
     *
     * @return int The number of deleted users
     */
    public function deleteUsers($userNames){

        ArrayUtils::forceNonEmptyArray($userNames, 'userNames');

        $deletedObjects = 0;
        $this->_db->transactionBegin();

        foreach ($userNames as $userName) {

            try {

                $this->deleteUser($userName);

                $deletedObjects ++;

            } catch (Throwable $e) {

                $this->_db->transactionRollback();

                throw new UnexpectedValueException('Error deleting objects: '.$e->getMessage());
            }
        }

        $this->_db->transactionCommit();

        return $deletedObjects;
    }


    /**
     * Remove the specified operation for the current domain
     *
     * @param string $operation The name for an existing operation on the actual domain
     *
     * @throws UnexpectedValueException
     *
     * @return bool True on success
     */
    public function deleteOperation(string $operation){

        StringUtils::forceNonEmptyString($operation, 'operation');

        $result = $this->_db->tableDeleteRows($this->_tableOperation, ['domain' => $this->_domain, 'name' => $operation]);

        if($result !== 1){

            throw new UnexpectedValueException('Error removing '.$operation.': Expected to remove 1 element but was '.$result);
        }

        return true;
    }


    /**
     * Save (create) to database the specified operation for the currently active domain or update it if already exists.
     * operations are actions that can be performed by users and are subject to access restrictions. They is used to
     * define all the things that are allowed or disallowed for the users.
     *
     * This only creates an operation, we will need then to grant permissions to roles or specific users.
     *
     * @param string $operation The name for the operation we want to save or update
     * @param string $description The description we want to set to the operation
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True if the operation was correctly saved
     */
    public function saveOperation(string $operation, string $description = ''){

        StringUtils::forceNonEmptyString($operation, 'operation');
        StringUtils::forceString($description, 'description');

        try {

            $this->_db->tableAddOrUpdateRow($this->_tableOperation, ['domain' => $this->_domain, 'name' => $operation],
                ['domain' => $this->_domain, 'name' => $operation, 'description' => $description]);

        } catch (Throwable $e) {

            if($this->_db->tableAlterToFitDefinition($this->_tableOperation, [
                'columns' => ['domain varchar(250) NOT NULL', 'name varchar(250) NOT NULL', 'description varchar(250) NOT NULL'],
                'primaryKey' => ['domain', 'name'],
                'foreignKey' => [[$this->_tableOperation.'_'.$this->_tableDomain.'_fk', ['domain'], $this->_tableDomain, ['name']]]])){

                return $this->saveOperation($operation, $description);
            }

            throw new UnexpectedValueException('Could not save operation '.$operation.' to db: '.$e->getMessage());
        }

        return true;
    }


    /**
     * Check if the specified operation is stored on database for the current domain
     *
     * @param string $operation The name for the operation that we want to check
     *
     * @return boolean True if the operation exists on the current domain, false otherwise
     */
    public function isOperation(string $operation){

        StringUtils::forceNonEmptyString($operation, 'operation');

        try {

            return count($this->_db->tableGetRows($this->_tableOperation, ['name' => $operation, 'domain' => $this->_domain])) === 1;

        } catch (Throwable $e) {

            return false;
        }
    }


    /**
     * Specify which user roles are allowed to perform the specified operation.
     *
     * @param string $operation The name for the operation
     * @param array $roles A list of user roles that will be allowed to perform the specified operation. An empty array will allow any role
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True on success
     */
    public function setOperationEnabledForRoles(string $operation, array $roles){

        if(!$this->isOperation($operation)){

            throw new UnexpectedValueException($operation.' is not an operation for the current domain');
        }

        try {

            $rows = [];

            if(empty($roles)){

                $rows[] = ['domain' => $this->_domain, 'operation' => $operation, 'role' => ''];

            }else{

                foreach ($roles as $role) {

                    $rows[] = ['domain' => $this->_domain, 'operation' => $operation, 'role' => $role];
                }
            }

            $this->_db->tableAddRows($this->_tableOperationRole, $rows);

        } catch (Throwable $e) {

            if($this->_db->tableAlterToFitDefinition($this->_tableOperationRole, [
                'columns' => ['domain varchar(250) NOT NULL', 'operation varchar(250) NOT NULL', 'role varchar(250) NOT NULL'],
                'primaryKey' => ['domain', 'operation', 'role'],
                'foreignKey' => [
                    [$this->_tableOperationRole.'_'.$this->_tableDomain.'_fk', ['domain'], $this->_tableDomain, ['name']],
                    [$this->_tableOperationRole.'_'.$this->_tableOperation.'_fk', ['domain', 'operation'], $this->_tableOperation, ['domain', 'name']],
                ]])){

                return $this->setOperationEnabledForRoles($operation, $roles);
            }

            throw new UnexpectedValueException('Could not set operation '.$operation.' as allowed for roles: ['.implode(', ', $roles).']: '.$e->getMessage());
        }

        return true;
    }


    /**
     * TODO
     */
    public function setOperationEnabledForUsers(string $operation, array $users){

        // TODO
    }


    /**
     * Check if the specified user is allowed to perform the specified operation
     *
     * @param string $userName The username for the user we want to check
     * @param string $operation The name for the operation we want to check against the user
     *
     * @return boolean True if the user can do the provided operation, false otherwise
     */
    public function isUserAllowedTo(string $userName, string $operation){

        $tableUser = $this->_tableUserObject;
        $tableUserRoles = $this->_tableUserObject."_roles";
        $tableOperationRole = $this->_tableOperationRole;

        // Check if the operation is marked for all roles
        $result = $this->_db->query("SELECT role FROM $tableOperationRole WHERE domain='$this->_domain' AND operation='$operation'");

        if(!empty($result) && $result[0]['role'] === ''){

            return true;
        }

        // Check if the operation is allowed for any of the specific roles assigned to the user
        $q  = "SELECT o.operation FROM $tableUser u, $tableUserRoles rs, $tableOperationRole o ";
        $q .= "WHERE u.domain='$this->_domain' AND u.username='$userName' AND u.dbid = rs.dbid AND rs.value = o.role AND o.operation = '$operation' LIMIT 1";
        $result = $this->_db->query($q);

        return !empty($result);
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

            throw new UnexpectedValueException('Incorrectly encoded credentials');
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

            throw new UnexpectedValueException('Incorrectly encoded credentials');
        }

        return base64_decode(explode(',', $base64Decoded)[1]);
    }


    /**
     * Perform the login for the specified username and password, and generate a new token to be used for subsequent logins.
     * The created token will be defined using the currently specified class values for tokenLifeTime, useCount, isTokenLifeTimeRecycled, etc
     *
     * IMPORTANT: Performing a new login will destroy any existing previous tokens that exist for that user and give us a new fresh one,
     * which will be then the only one valid.
     *
     * @param string $userName The username for the user we want to login
     * @param string $password The password for the user we want to login
     *
     * @throws UnexpectedValueException If login failed
     *
     * @return stdClass An instance with the following properties if login succeeded:<br>
     *         ->token Will contain a string with the user token<br>
     *         ->user Will contain the UserObject instance for the logged user<br>
     *         ->operations Will contain an array with the names for all the operations that are allowed to the logged user
     */
    public function login(string $userName, string $password){

        $user = $this->_databaseObjectsManager->findByPropertyValues(UserObject::class,
            ['userName' => $userName, 'domain' => $this->_domain]);

        if(count($user) === 1){

            try {

                $dbPassword = $this->_db->tableGetRows($this->_databaseObjectsManager
                    ->tablesPrefix.'userobject_password', ['dbid' => $user[0]->getDbId()]);

            } catch (Throwable $e) {

                throw new UnexpectedValueException('Specified user does not have a stored password: '.$userName);
            }

            if(empty($dbPassword)){

                throw new UnexpectedValueException('Specified user does not have a stored password: '.$userName);
            }

            if(count($dbPassword) === 1 && password_verify($password, $dbPassword[0]['password'])){

                // Clear any previously existing tokens if necessary
                if(!$this->isMultipleUserSessionsAllowed){

                    $this->_deleteAllUserTokens($user[0]->userName);
                }

                $result = new stdClass();
                $result->token = $this->createToken($user[0]->userName);
                $result->user = $user[0];
                $result->operations = $this->getUserOperations($userName);

                return $result;
            }
        }

        throw new UnexpectedValueException('Authentication failed');
    }


    /**
     * Perform a login obtaining the user and password from the encoded credentials
     *
     * @see UsersManager::login
     * @see UsersManager::encodeUserAndPassword
     *
     * @param string $encodedCredentials The user and password credentials as they are encoded by UsersManager::encodeUserAndPassword() method
     *
     * @return stdClass See UsersManager::login()
     */
    public function loginFromEncodedCredentials(string $encodedCredentials){

        return $this->login($this->decodeUserName($encodedCredentials), $this->decodePassword($encodedCredentials));
    }


    /**
     * Generates a token string for the provided user and stores it on database so it can be later verified to identify that user.
     *
     * A token is basically a temporary user credential. The most common user token is the one that is created once we call to the login method to
     * start a user session. But we may want to create other tokens for other specific purposes: We can generate multiple tokens for the same user
     * and they will be all valid but with different expiration caracteristics. This can be useful if we need to provide different expiration
     * times or reusability for each token on different application contexts. Any created token will have exactly the same access permisions
     * as the user it relates to.
     *
     * @param string $userName A valid user name for the current domain
     * @param array $options An associative array with the token setup. Each key must contain one of the following:<br>
     *              lifeTime, useCount, isLifeTimeRecycled (Check "See Also" section for docs
     *              on each option meaning and usage)<br>
     *              All options values which are not specified on this array will be obtained from the class defaults.
     *
     * @see UsersManager::$tokenLifeTime
     * @see UsersManager::$tokenUseCount
     * @see UsersManager::$isTokenLifeTimeRecycled
     *
     * @return string The generated token string
     */
    public function createToken(string $userName, array $options = []){

        $user = $this->findUserByUserName($userName);

        // Check if we need to use the custom values or the class defaults
        $lifeTime = $this->tokenLifeTime;
        $useCount = $this->tokenUseCount;
        $isLifeTimeRecycled = $this->isTokenLifeTimeRecycled;

        foreach (array_keys($options) as $option) {

            switch ($option) {

                case 'lifeTime':
                    $lifeTime = $options[$option];
                    break;

                case 'useCount':
                    $useCount = $options[$option];
                    break;

                case 'isLifeTimeRecycled':
                    $isLifeTimeRecycled = $options[$option];
                    break;

                default:
                    throw new UnexpectedValueException("Invalid option: $option");
            }
        }

        // Verify options are all valid
        if(!is_int($lifeTime)){

            throw new UnexpectedValueException("Invalid lifeTime value: $lifeTime");
        }

        if(!is_int($useCount) && $useCount !== null){

            throw new UnexpectedValueException("Invalid useCount value: $useCount");
        }

        if(!is_bool($isLifeTimeRecycled)){

            throw new UnexpectedValueException("Invalid isLifeTimeRecycled value: $isLifeTimeRecycled");
        }

        $expiryDate = (new DateTime('+'.$lifeTime.' seconds', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $token = base64_encode(StringUtils::generateRandom(75, 75).
            StringUtils::limitLen(md5($userName).md5($expiryDate), 25));

        try {

            $this->_db->tableAddRows($this->_tableToken,
                [['token' => $token, 'userdbid' => $user->getDbId(), 'expires' => $expiryDate,
                  'lifetime' => $lifeTime, 'islifetimerecycled' => $isLifeTimeRecycled ? 1 : 0, 'useCount' => $useCount]]);

        } catch (Throwable $e) {

            if($this->_db->tableAlterToFitDefinition($this->_tableToken, [
                'columns' => ['token VARCHAR(150) NOT NULL',
                    'userdbid '.$this->_unsignedBigIntSqlTypeDef,
                    'expires '.$this->_db->getSQLDateTimeType(false),
                    'lifetime INT UNSIGNED NOT NULL',
                    'islifetimerecycled tinyint(1) NOT NULL',
                    'usecount INT'
                ],
                'primaryKey' => ['token'],
                'foreignKey' => [[$this->_tableToken.'_'.$this->_tableUserObject.'_fk', ['userdbid'], $this->_tableUserObject, ['dbid']]]])){

                return $this->createToken($userName);
            }

            throw new UnexpectedValueException('Could not create '.$this->_tableToken.' table: '.$e->getMessage());
        }

        return $token;
    }


    /**
     * Test that the current token is active and valid.
     * This won't give us any other info like which user or which access level this token has. It is only used
     * to verify that a token exists and is active.
     *
     * @param string $token An active and valid user token
     *
     * @see UsersManager::isTokenAllowedTo
     * @see UsersManager::findUserByToken
     *
     * @return True if the token is valid and currently active. False if not found or expired
     */
    public function isTokenValid(string $token){

        StringUtils::forceNonEmptyString($token, '', 'token must have a value');

        try {

            $tokenData = $this->_db->tableGetRows($this->_tableToken, ['token' => $token]);

        } catch (Throwable $e) {

            return false;
        }

        // The token was found on db, so we will check if it is still valid
        if(count($tokenData) === 1){

            $tokenValue = $tokenData[0]['token'];
            $tokenUseCount = $tokenData[0]['usecount'];
            $dateTimeZone = new DateTimeZone('UTC');

            // If the token expiration date is after the current system date, and use count is null or avobe zero, token will be valid
            if(new DateTime($tokenData[0]['expires'], $dateTimeZone) > new DateTime(null, $dateTimeZone) &&
               ($tokenUseCount === null || $tokenUseCount > 0)){

                $tableValuesToUpdate = [];

                // Check if we must recycle the token expiration date
                if((int)$tokenData[0]['islifetimerecycled'] === 1){

                    $tableValuesToUpdate['expires'] = (new DateTime('+'.$tokenData[0]['lifetime'].' seconds', $dateTimeZone))->format('Y-m-d H:i:s');
                }

                // Check if we must reduce the usecount value
                if($tokenUseCount !== null){

                    $tableValuesToUpdate['usecount'] = $tokenUseCount - 1;
                }

                // Update the token table if expiry date was recycled or use count reduced
                if(!empty($tableValuesToUpdate)){

                    $this->_db->tableUpdateRow($this->_tableToken, ['token' => $tokenValue], $tableValuesToUpdate);
                }

                return true;
            }

            // Reaching here means the token is not valid. We will remove it from database
            if($this->_db->tableDeleteRows($this->_tableToken, ['token' => $tokenValue]) === 0){

                throw new UnexpectedValueException('Could not delete expired token from db');
            }
        }

        return false;
    }


    /**
     * TODO
     * @param string $token
     * @param array $operations
     */
    public function isTokenAllowedTo(string $token, array $operations){

    }


    /**
     * @see DataBaseManager::transactionBegin
     */
    public function transactionBegin(){

        $this->_db->transactionBegin();
    }


    /**
     * @see DataBaseManager::transactionCommit
     */
    public function transactionCommit(){

        $this->_db->transactionCommit();
    }


    /**
     * @see DataBaseManager::transactionRollback
     */
    public function transactionRollback(){

        $this->_db->transactionRollback();
    }


    /**
     * Delete any existing tokens that are expired
     *
     * This method should be periodically called via a cron task to clean all unused and expired tokens from database.
     *
     * @return int The number of deleted tokens
     */
    public function deleteAllExpiredTokens(){

        return $this->_db->query('DELETE FROM '.$this->_tableToken.' WHERE (expires < NOW()) OR (usecount IS NOT NULL AND usecount <= 0)');
    }


    /**
     * Aux method to clean all the tokens that are linked to the specified user on the current domain.
     *
     * @param string $userName The user name on the current domain for which we want to purge all tokens
     */
    private function _deleteAllUserTokens(string $userName){

        $user = $this->findUserByUserName($userName);

        try {

            return $this->_db->query('DELETE FROM '.$this->_tableToken.' WHERE userdbid = '.$user->getDbId());

        } catch (Throwable $e) {

            // We will ignore errors trying to delete the tokens cause it will be probably due to table still not existing
        }

        return 0;
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

        if($this->isMultipleUserSessionsAllowed){

            // Purge the provided token
            $result = $this->_db->tableDeleteRows($this->_tableToken, ['token' => $token]) === 1;

        }else{

            // Clear previously existing user tokens
            $result = $this->_deleteAllUserTokens($this->findUserByToken($token)->userName) > 0;
        }

        return $result;
    }
}
