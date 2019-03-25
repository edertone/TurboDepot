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
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbodepot\src\main\php\model\DepotFile;
use org\turbodepot\src\main\php\model\DepotObject;


/**
 * Depot Manager class
 */
class DepotManager extends BaseStrictClass{


    /**
     * Contains the turbodepot setup data that's been loaded by this class
     * @var \stdClass
     */
    private $_setup = null;


    /**
     * Contains the setup data specific for the depot instance that's been loaded by this class
     * @var \stdClass
     */
    private $_loadedDepotSetup = null;


    /**
     * A files manager instance that is used by this class
     * @var FilesManager
     */
    private $_filesManager = null;


    /**
     * A logs manager instance that is used by this class
     * @var LogsManager
     */
    private $_logsManager = null;


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
        if(is_string($setup) && is_file($setup)){

            $this->_setup = json_decode($this->_filesManager->readFile($setup));

        }else if (is_object($setup) && property_exists($setup, '$schema')){

            $this->_setup = $setup;

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

        // Initialize the logs manager to the folder that is defined on the depot setup
        $logsSource = $this->_getSourceSetup($this->_loadedDepotSetup->logs->source);

        if($logsSource !== null){

            $this->_logsManager = new LogsManager($logsSource->path);
        }

        // TODO - initialize the users manager class
        // $this->usersManager = new UsersManager($setupPath);
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
     * Obtain the logs manager instance that is available through this depot manager.
     * The logs root path is already defined by the turbodepot.json setup
     *
     * @return LogsManager
     */
    public function getLogsManager(){

        return $this->_logsManager;
    }


    /**
     * TODO
     */
    public function getUsersManager(){

        return $this->_usersManager;
    }


    /**
     * TODO
     */
    public function isFile(string $path, string $fileName){


    }


    /**
     * TODO
     */
    public function isObject(string $path, string $id){


    }


    /**
     * TODO
     */
    public function getFile(string $path, string $fileName){


    }


    /**
     * TODO
     */
    public function getObject(string $path, string $id){


    }


    /**
     * TODO
     */
    public function saveFile(string $path, DepotFile $file){


    }


    /**
     * TODO
     */
    public function saveObject(string $path, DepotObject $object){


    }


    /**
     * TODO
     */
    public function deleteFile(string $path, string $fileName){


    }


    /**
     * TODO
     */
    public function deleteObject(string $path, string $id){


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