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


use stdClass;
use UnexpectedValueException;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * GoogleDriveManager class
 */
class GoogleDriveManager {


    /**
     * Contains an instance of the google api client class
     * @var Google_Client
     */
    private $_client = null;


    /**
     * Contains an instance of the google drive service
     * @var Google_Service_Drive
     */
    private $_service = null;


    /**
     * This class is an abstraction of the Google drive api. It allows us to interact with a google drive account like we interact
     * with a standard file system.
     *
     * How to use this class:
     *
     * - Before creating an instance of GoogleDriveManager, the google-api-php-client library must be downloaded from github and deployed
     *   into our project (We can use composer or download it directly).
     *
     * - We must browse to the google api console and make sure we have a service account and a service account key.
     *   Service accounts are a special type of google user accounts that represent non human applications. The service account key is a file containing
     *   the account credentials that allow the related application to login into the google api. We must save this file so it is accessible by our project.
     *
     * - We must share with the service account all the google drive files or folders we want to access with GoogleDriveManager. Google drive resources are
     *   shared with service accounts exactly the same way as with normal users. Service accounts are identified with an email, exactly like normal user accounts.
     *   We can use that email to grant access to google drive resources.
     *
     * @param string $googleApiPhpCLientRoot A full file system path to the root of the downloaded google-api-php-client library, that must be accessible by our
     *        project. A "vendor" folder must exist at the root of the provided folder.
     *
     * @throws UnexpectedValueException
     */
    public function __construct($googleApiPhpCLientRoot){

        if(!is_file($googleApiPhpCLientRoot.'/vendor/autoload.php')){

            throw new UnexpectedValueException('Specified googleApiPhpCLientRoot folder is not valid. Could not find /vendor/autoload.php file on '.$googleApiPhpCLientRoot);
        }

        require_once $googleApiPhpCLientRoot.'/vendor/autoload.php';
    }


    /**
     * TODO
     *
     * @param string $serviceAccountCredentials A full file system path to the json file that contains the service account credentials that will be used to
     *        authenticate with the google drive api (See this class constructor for more info on service accounts).
     *
     * @throws UnexpectedValueException
     */
    public function authenticateWithServiceAccount($serviceAccountCredentials){

        if(!is_file($serviceAccountCredentials)){

            throw new UnexpectedValueException('Could not find serviceAccountCredentials file. Make sure you download the generated service account key json file and specify it here');
        }

        $this->_client = new \Google_Client();
        $this->_client->setScopes(['https://www.googleapis.com/auth/drive']);
        $this->_client->setAuthConfig($serviceAccountCredentials);
        $this->_client->useApplicationDefaultCredentials();

        $this->_service = new \Google_Service_Drive($this->_client);
    }


    /**
     *
     * @return array
     */
    public function getDirectoryList($parentId = ''){

        if(StringUtils::isEmpty($parentId)){

            $itemList = $this->_service->files->listFiles([
                'q' => 'sharedWithMe=true',
                'pageSize' => 1000,
                'fields' => 'nextPageToken, files(id,mimeType,name)',
                "orderBy" => "name"
            ]);

        } else {

            $itemList = $this->_service->files->listFiles([
                'q' => "'".$parentId."' in parents",
                'pageSize' => 1000,
                'fields' => 'nextPageToken, files(id,mimeType,name,parents)',
                "orderBy" => "name"
            ]);
        }

        $result = [];

        foreach ($itemList->getFiles() as $item) {

            $itemStd = new stdClass();
            $itemStd->id = $item->getId();
            $itemStd->isDirectory = $item->getMimeType() === 'application/vnd.google-apps.folder';
            $itemStd->name = $item->getName();

            $result[] = $itemStd;
        }

        return $result;
    }
}

?>