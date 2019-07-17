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

use UnexpectedValueException;
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * Depot Manager class
 */
class DepotManager extends BaseStrictClass{


    /**
     * Contains all the turbodepot setup data that's been loaded by this class
     * @var \stdClass
     */
    private $_setup = null;


    /**
     * Contains the setup data specific for the depot instance that's been loaded by this class
     * @var \stdClass
     */
    private $_loadedDepotSetup = null;


    /**
     * A storage folder manager instance that is used by this class
     * @var StorageFolderManager
     */
    private $_storageFolderManager = null;


    /**
     * A files manager instance that is used by this class
     * @var FilesManager
     */
    private $_filesManager = null;


    /**
     * A tmp files manager instance that is used by this class
     * @var TmpFilesManager
     */
    private $_tmpFilesManager = null;


    /**
     * A files manager instance that is used by this class
     * @var LocalizedFilesManager
     */
    private $_localizedFilesManager = null;


    /**
     * A logs manager instance that is used by this class
     * @var LogsManager
     */
    private $_logsManager = null;


/**
     * A google drive manager instance that is used by this class
     * @var GoogleDriveManager
     */
    private $_googleDriveManager = null;


    /**
     * A users manager instance that is used by this class
     * @var UsersManager
     */
    private $_usersManager = null;


    /**
     * Main class to interact with a turbo depot instance and create, delete or manipulate the elements which it can store.
     *
     * @param \stdClass|string $setup Full or relative path to the turbodepot.json file that contains the turbodepot setup or
     *        an stdclass instance with a setup file data decoded from json
     * @param string $depotName The name for the depot instance we want to connect. If not specified, the first one that is found
     *        on the provided setup will be used.
     */
    public function __construct($setup, string $depotName = ''){

        $this->_filesManager = new FilesManager();

        // Load the provided setup data
        if(is_object($setup) && property_exists($setup, '$schema')){

            $this->_setup = $setup;

        }else if (is_string($setup) && is_file($setup)){

            $this->_setup = json_decode($this->_filesManager->readFile($setup));

        }else{

            throw new UnexpectedValueException('DepotManager constructor expects a valid path to users setup or an stdclass instance with the setup data');
        }

        // Search for the requested depot. If none specified, the first one will be used
        if($depotName === ''){

            $this->_loadedDepotSetup = $this->_setup->depots[0];

        }else{

            foreach ($this->_setup->depots as $depotSetup) {

                if($depotName === $depotSetup->name){

                    $this->_loadedDepotSetup = $depotSetup;
                }
            }
        }

        // TODO - initialize the users manager class
        // $this->usersManager = new UsersManager($setupPath);
    }


    /**
     * Obtain the storage folder manager instance that is available through this depot manager
     *
     * @return StorageFolderManager
     */
    public function getStorageFolderManager(){

        if($this->_storageFolderManager === null){

            // Initialize the storage folder manager to the folder that is defined on the depot setup
            if(StringUtils::isEmpty($this->_loadedDepotSetup->storageFolderPath)){

                throw new UnexpectedValueException('storageFolderManager not available. Check it is correctly configured on turbodepot setup');
            }

            $this->_storageFolderManager = new StorageFolderManager($this->_loadedDepotSetup->storageFolderPath);
        }

        return $this->_storageFolderManager;
    }


    /**
     * Obtain the files manager instance that is available through this depot manager
     *
     * @return FilesManager
     */
    public function getFilesManager(){

        return $this->_filesManager;
    }


    /**
     * TODO
     */
    public function getTmpFilesManager(){

        if($this->_tmpFilesManager === null){

            // Initialize the tmp files manager to the folder that is defined on the depot setup
            if(($tmpFilesSource = $this->_getSourceSetup($this->_loadedDepotSetup->tmpFiles->source)) === null){

                throw new UnexpectedValueException('tmpFilesManager not available. Check it is correctly configured on turbodepot setup');
            }

            $this->_tmpFilesManager = new TmpFilesManager($tmpFilesSource->path);
        }

        return $this->_tmpFilesManager;
    }


    /**
     * Obtain the localized files manager instance that is available through this depot manager.
     *
     * @return LocalizedFilesManager
     */
    public function getLocalizedFilesManager(){

        if($this->_localizedFilesManager === null){

            // Initialize the localized files manager to the folder that is defined on the depot setup
            if(($localizedFilesSource = $this->_getSourceSetup($this->_loadedDepotSetup->localizedFiles->source)) === null){

                throw new UnexpectedValueException('localizedFilesManager not available. Check it is correctly configured on turbodepot setup');
            }

            $this->_localizedFilesManager = new LocalizedFilesManager($localizedFilesSource->path,
                $this->_loadedDepotSetup->localizedFiles->locales, $this->_loadedDepotSetup->localizedFiles->locations,
                $this->_loadedDepotSetup->localizedFiles->failIfKeyNotFound);
        }

        return $this->_localizedFilesManager;
    }


    /**
     * Obtain the logs manager instance that is available through this depot manager.
     *
     * @return LogsManager
     */
    public function getLogsManager(){

        if($this->_logsManager === null){

            // Initialize the logs manager to the folder that is defined on the depot setup
            if(($logsSource = $this->_getSourceSetup($this->_loadedDepotSetup->logs->source)) === null){

                throw new UnexpectedValueException('logsManager not available. Check it is correctly configured on turbodepot setup');
            }

            $this->_logsManager = new LogsManager($logsSource->path);
        }

        return $this->_logsManager;
    }


    /**
     * Obtain the google drive manager instance that is available through this depot manager.
     *
     * @return GoogleDriveManager
     */
    public function getGoogleDriveManager(){

        if($this->_googleDriveManager === null){

            // Initialize the google drive manager to the folder that is defined on the depot setup
            if(StringUtils::isEmpty($this->_loadedDepotSetup->googleDrive->apiClientRoot)){

                throw new UnexpectedValueException('googleDriveManager not available. Check it is correctly configured on turbodepot setup');
            }

            $this->_googleDriveManager = new GoogleDriveManager($this->_loadedDepotSetup->googleDrive->apiClientRoot);

            if(!StringUtils::isEmpty($this->_loadedDepotSetup->googleDrive->cacheRootPath)){

                $this->_googleDriveManager->enableCache($this->_loadedDepotSetup->googleDrive->cacheRootPath,
                    $this->_loadedDepotSetup->googleDrive->cacheZone,
                    $this->_loadedDepotSetup->googleDrive->listsTimeToLive,
                    $this->_loadedDepotSetup->googleDrive->filesTimeToLive);
            }

            $this->_googleDriveManager->setServiceAccountCredentials($this->_loadedDepotSetup->googleDrive->accountCredentialsPath);
        }

        return $this->_googleDriveManager;
    }


    /**
     * TODO
     */
    public function getUsersManager(){

        if($this->_usersManager === null){

            throw new UnexpectedValueException('usersManager not available. Check it is correctly configured on turbodepot setup');
        }

        return $this->_usersManager;
    }


    /**
     * Given a source name, this method will search for its setup data on all sources for the currently specified turbodepot setup
     *
     * @param string $name The name for a source on the turbodepot setup
     *
     * @return /stdClass The setup data for the specified source or null if the source was not found
     */
    private function _getSourceSetup(string $name){

        foreach ($this->_setup->sources->fileSystem as $source) {

            if($source->name === $name){

                return $source;
            }
        }

        // TODO - loop all other sources: mysql, sqlite...

        return null;
    }
}

?>