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
     * @var \Google_Client
     */
    private $_client = null;


    /**
     * Contains an instance of the google drive service
     * @var \Google_Service_Drive
     */
    private $_service = null;


    /**
     * A cache manager instance that is used by this class
     * @var CacheManager
     */
    private $_cacheManager = null;


    /**
     * This class is an abstraction of the Google drive api. It allows us to interact with a google drive account like we interact
     * with a standard file system.
     *
     * How to use this class:
     *
     * - Before creating an instance of GoogleDriveManager, the google-api-php-client library must be downloaded from github and deployed
     *   into our project (We can use composer or download it directly).
     *
     * - We must login on the google api console and make sure we have a service account and a service account key.
     *   Service accounts are a special type of google user accounts that represent non human applications. The service account key is a file containing
     *   the account credentials that allow the related application to login into the google api. We must save this file so it is accessible by our project.
     *
     * - We must share with the service account all the google drive files or folders we want to access with GoogleDriveManager. Google drive resources are
     *   shared with service accounts exactly the same way as with normal users. Service accounts are identified with an email, exactly like normal user accounts.
     *   We can use that email to grant access to google drive resources.
     *
     * @throws UnexpectedValueException
     *
     * @see CacheManager::__construct
     *
     * @param string $googleApiPhpCLientRoot A full file system path to the root of the downloaded google-api-php-client library, that must be accessible by our
     *        project. A "vendor" folder must exist at the root of the provided folder.
     * @param string $cacheRootPath If we want to cache all the results of the google drive api requests, we must specify here the root of a folder where
     *        all the cached data will be stored. If we leave this parameter empty, no caching will happen
     * @param string $cacheZone To isolate all the cached data from any other elements that may exist on the cache folder, we must define a cache zone name.
     *        we can leave here the default name or use any other we want.
     * @param int $cacheTimeToLive Defines the number of seconds after which the whole zone cache data will be deleted. Set it to -1 if you want to
     *        manually manage the lifetime of the zone cached data. (1 hour = 3600 seconds, 1 day = 86400 seconds)
     */
    public function __construct($googleApiPhpCLientRoot, $cacheRootPath = '', $cacheZone = 'google-drive', $cacheTimeToLive = -1){

        if(!is_file($googleApiPhpCLientRoot.'/vendor/autoload.php')){

            throw new UnexpectedValueException('Specified googleApiPhpCLientRoot folder is not valid. Could not find /vendor/autoload.php file on '.$googleApiPhpCLientRoot);
        }

        require_once $googleApiPhpCLientRoot.'/vendor/autoload.php';

        if($cacheRootPath !== ''){

            $this->_cacheManager = new CacheManager($cacheRootPath, $cacheZone, $cacheTimeToLive);
        }
    }


    /**
     * Perform the login to the google drive api with the specified credentials
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
     * Get a list with all the items under the specified google drive folder.
     *
     * @param string $parentId The google drive identifier for the directory that contains the elements we want to list.
     *
     * @return \stdClass[] An array with one object for each one of the child elements found. Each object will have three
     *         properties: id, with the id of the child element, isDirectory which will be true if the child element is a directory,
     *         and name which will contain the child element name
     *
     */
    public function getDirectoryList($parentId = ''){

        if($this->_cacheManager !== null &&
           ($cachedList = $this->_cacheManager->get('getDirectoryList', $parentId)) !== null){

            return json_decode($cachedList);
        }

        // Request the list to the google drive API
        $query = StringUtils::isEmpty($parentId) ? 'sharedWithMe=true' : "'".$parentId."' in parents";

        $itemList = $this->_service->files->listFiles([
            'q' => $query,
            'pageSize' => 1000,
            'fields' => 'nextPageToken, files(id,mimeType,name,parents)',
            "orderBy" => "name"
        ]);

        $result = [];

        foreach ($itemList->getFiles() as $item) {

            $itemStd = new stdClass();
            $itemStd->id = $item->getId();
            $itemStd->isDirectory = $item->getMimeType() === 'application/vnd.google-apps.folder';
            $itemStd->name = $item->getName();

            $result[] = $itemStd;
        }

        if($this->_cacheManager !== null){

            $this->_cacheManager->add('getDirectoryList', $parentId, json_encode($result));
        }

        return $result;
    }


    /**
     * Force a removal for all the locally cached google drive requests and files
     */
    public function clearCache(){

        if($this->_cacheManager === null){

            throw new UnexpectedValueException('Cache is not enabled for this instance');
        }

        return $this->_cacheManager->clearZone();
    }
}

?>