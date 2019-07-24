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
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * Cache manager class
 */
class CacheManager extends BaseStrictClass{


    /**
     * The full filesystem path to the root of the folder where cache files are stored
     * @var string
     */
    private $_zoneRoot = '';


    /**
     * A character that is used to sepparate values inside the metadata files
     * @var string
     */
    private $_metadataDelimiter = '|';


    /**
     * A files manager instance that is used by this class
     * @var FilesManager
     */
    private $_filesManager = null;


    /**
     * Defines a general purpose file-system based storage cache. Use this to fetch data that requires important amounts of time to be
     * calculated or generated, and improve the time that's necessary to get it on successive requests.
     *
     * This cache system is divided into zones, which are different folders inside the cache root folder. Each zone is independent, and can
     * be named and used for whatever purpose we want. Inside each of the zones, we can store cache "sections" which are another independent
     * cache areas which can be also used and named for anything we want. Each section contains all the cached data that we generate.
     *
     * All the data is stored on plain file system, so take it into consideration when fast response times are cryitcal.
     *
     * The expiration of the cache data is managed entirely by this class, so no cron or scheduled tasks should be required to clear outdated
     * cache items.
     *
     * @param string $rootPath The full absolute filesystem path to the root of the folder where all the cache data files will be stored
     * @param string $zone To allow different types of cache data to be stored without colliding, the cache root folder is divided into
     *        several zones. We can define here the name for a zone we want to store our cached data, and it will be used. If the zone does
     *        not exist, it will be automatically created.
     *
     * @throws UnexpectedValueException
     */
    public function __construct(string $rootPath, string $zone){

        $this->_filesManager = new FilesManager();

        if(!StringUtils::isString($zone) || StringUtils::isEmpty($zone)){

            throw new UnexpectedValueException('zone must be a non empty string');
        }

        if(!$this->_filesManager->isDirectory($rootPath)){

            throw new UnexpectedValueException('Invalid rootPath received: '.$rootPath);
        }

        $this->_zoneRoot = $rootPath.DIRECTORY_SEPARATOR.$zone;

        // Clear the current zone if expired
        $this->isZoneExpired();
    }


    /**
     * Obtain the name for the current cache zone
     *
     * @return string
     */
    public function getZoneName(){

        return StringUtils::getPathElement($this->_zoneRoot);
    }


    /**
     * Define the expiration time limit for all the zone cached data.
     *
     * @param string $timeToLive Defines the number of seconds after which the whole zone cache data will be deleted.
     *        (1 hour = 3600 seconds, 1 day = 86400 seconds, 1 month = 2592000, 1 year = 31536000).
     *        Set it to 0 for an infinite timeout.
     *
     * @return void
     */
    public function setZoneTimeToLive($timeToLive){

        $this->_updateMetadataFile($this->_zoneRoot, false, $timeToLive);
    }


    /**
     * Define the expiration time limit for all the cached data on the specified section
     *
     * @param string $timeToLive Defines the number of seconds after which the whole section cached data will be deleted.
     *        (1 hour = 3600 seconds, 1 day = 86400 seconds, 1 month = 2592000, 1 year = 31536000).
     *        Set it to 0 for an infinite timeout.
     *
     * @return void
     */
    public function setSectionTimeToLive($section, $timeToLive){

        $this->_updateMetadataFile($this->_zoneRoot.DIRECTORY_SEPARATOR.$section, false, $timeToLive);
    }


    /**
     * Tells if the currently active zone is expired or not. See this class constructor for info on zone expiration setup
     *
     * @throws UnexpectedValueException
     *
     * @see CacheManager::__construct
     *
     * @return boolean True if the zone expiration time has been exceeded (and data is no longer available). False otherwise
     */
    public function isZoneExpired(){

        $result = $this->_isMetadataFileExpired($this->_zoneRoot.DIRECTORY_SEPARATOR.'metadata');

        if($result){

            // Loop all the zone sections and clear all the ones that are expired
            foreach ($this->_filesManager->getDirectoryList($this->_zoneRoot) as $section) {

                if($this->_filesManager->isDirectory($this->_zoneRoot.DIRECTORY_SEPARATOR.$section)){

                    $sectionMetadata = $this->_zoneRoot.DIRECTORY_SEPARATOR.$section.DIRECTORY_SEPARATOR.'metadata';

                    if(!$this->_filesManager->isFile($sectionMetadata) || $this->_isMetadataFileExpired($sectionMetadata)){

                        $this->clearSection($section);
                    }
                }
            }

            $this->resetMetadataFile($this->_zoneRoot.DIRECTORY_SEPARATOR.'metadata');
        }

        return $result;
    }


    /**
     * Tells if the specified section data is expired or not. See the setSectionTimeToLive() method for more info on section time to live
     *
     * @throws UnexpectedValueException
     *
     * @see CacheManager::setSectionTimeToLive
     *
     * @return boolean True if the section expiration time has been exceeded (and data is no longer available). False otherwise
     */
    public function isSectionExpired($section){

        if(!$this->_filesManager->isDirectory($this->_zoneRoot.DIRECTORY_SEPARATOR.$section)){

            throw new UnexpectedValueException('section <'.$section.'> does not exist');
        }

        // Zone expiration will be checked but section expiration has preference
        if(!$this->_filesManager->isFile($this->_zoneRoot.DIRECTORY_SEPARATOR.$section.DIRECTORY_SEPARATOR.'metadata') &&
           $this->isZoneExpired()){

           return true;
        }

        $result = $this->_isMetadataFileExpired($this->_zoneRoot.DIRECTORY_SEPARATOR.$section.DIRECTORY_SEPARATOR.'metadata');

        if($result){

            $this->clearSection($section);
        }

        return $result;
    }


    /**
     * Auxiliary method to check if a metadata file is expired
     *
     * @param string $metadataPath Full file system path to the metadata file we want to check
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True if the metadata file is expired, false otherwise
     */
    private function _isMetadataFileExpired($metadataPath){

        $isMetadataExpired = false;

        if($this->_filesManager->isFile($metadataPath)){

            $metadataContents = file_get_contents($metadataPath, true);

            if($metadataContents === false){

                throw new UnexpectedValueException('could not read cache metadata file: '.$metadataPath);
            }

            $metadataContents = explode($this->_metadataDelimiter, $metadataContents);

            $isMetadataExpired = $metadataContents[0] !== '0' && $metadataContents[1] !== '' && (time() >= (float)$metadataContents[1]);
        }

        return $isMetadataExpired;
    }


    /**
     * Save arbitrary information into the cache under a specific section and identifier (overwirte without warning if already exist).
     *
     * @param string $section The name for a cache section (under the current zone) where we want to store the data.
     * @param string $id A unique identifier that we want to use for the stored data. This will be required to obtain it later
     * @param string $data The data we want to store.
     *
     * @throws UnexpectedValueException
     *
     * @return string The same data
     */
    public function save($section, $id, $data){

        if(!StringUtils::isString($data)){

            throw new UnexpectedValueException('data must be a string but was '.gettype($data));
        }

        $fullPath = $this->_getFullPathToId($section, $id);
        $basePath = StringUtils::getPath($fullPath);

        if($this->_filesManager->isDirectory($this->_zoneRoot.DIRECTORY_SEPARATOR.$section)){

            $this->isSectionExpired($section);
        }

        if(!$this->_filesManager->isDirectory($basePath)){

            $this->_filesManager->createDirectory($basePath, true);
        }

        $this->_filesManager->saveFile($fullPath, $data);


        // Update expiration times if necessary
        if($this->_filesManager->isFile($this->_zoneRoot.DIRECTORY_SEPARATOR.'metadata')){

            $this->_updateMetadataFile($this->_zoneRoot, true);
        }

        if($this->_filesManager->isFile($this->_zoneRoot.DIRECTORY_SEPARATOR.$section.DIRECTORY_SEPARATOR.'metadata')){

            $this->_updateMetadataFile($this->_zoneRoot.DIRECTORY_SEPARATOR.$section, true);
        }

        return $data;
    }


    /**
     * Read data which was previously stored on the cache
     *
     * @param string $section The name for a cache section (under the current zone) where the data is stored
     * @param string $id The identifier we previously used to store the data we want to retrieve
     *
     * @return string|null The requested data or null if data, id or section were not found
     */
    public function get($section, $id){

        $fullPath = $this->_getFullPathToId($section, $id);

        if($this->isSectionExpired($section)){

            return null;
        }

        if($this->_filesManager->isFile($fullPath)){

            try {

                if(($content = file_get_contents($fullPath, true)) !== false){

                    return $content;
                }

            } catch (Throwable $e) {

                return null;
            }
        }

        return null;
    }


    /**
     * Gives the full OS filesystem path to the file which contains the specified cached data.
     * Normally useful when the cached data is so big that should be streamed, or when we need to perform some kind of
     * extra file operations with it.
     *
     * @param string $section The name for a cache section (under the current zone) where the data is stored
     * @param string $id The identifier we previously used to store the data
     *
     * @return string|null The requested path or null if id or section were not found
     */
    public function getPath($section, $id){

        $fullPath = $this->_getFullPathToId($section, $id);

        return $this->_filesManager->isFile($fullPath) ? $fullPath : null;
    }


    /**
     * Totally delete all the contents of the actual cache zone and reset its metadata file if it exists.
     *
     * NOTE: All cached data will be deleted even if the zone is not expired yet!
     *
     * @return boolean True on success
     */
    public function clearZone(){

        // We use a folder rename trick to free the zone as fast as possible: We create a temporary folder with the empty
        // zone structure, and perform a fast rename of the folders so we can then calmly delete the full old zone folder

        $zoneParent = StringUtils::getPath($this->_zoneRoot).DIRECTORY_SEPARATOR;

        $emptyZoneTmpRoot = $zoneParent.$this->_filesManager->findUniqueDirectoryName(
            $zoneParent, 'tmp'.StringUtils::generateRandom(8, 8)).DIRECTORY_SEPARATOR;

        $this->_filesManager->createDirectory($emptyZoneTmpRoot);

        // Copy zone metadata if exists
        if($this->_filesManager->isFile($this->_zoneRoot.DIRECTORY_SEPARATOR.'metadata')){

            $this->_filesManager->copyFile($this->_zoneRoot.DIRECTORY_SEPARATOR.'metadata', $emptyZoneTmpRoot.'metadata');

            $this->resetMetadataFile($emptyZoneTmpRoot.'metadata');
        }

        // Create all empty sections and copy metadata files if exist
        foreach ($this->_filesManager->getDirectoryList($this->_zoneRoot) as $section) {

            if($this->_filesManager->isDirectory($this->_zoneRoot.DIRECTORY_SEPARATOR.$section)){

                $this->_filesManager->createDirectory($emptyZoneTmpRoot.$section);

                if($this->_filesManager->isFile($this->_zoneRoot.DIRECTORY_SEPARATOR.$section.DIRECTORY_SEPARATOR.'metadata')){

                    $this->_filesManager->copyFile($this->_zoneRoot.DIRECTORY_SEPARATOR.$section.DIRECTORY_SEPARATOR.'metadata',
                        $emptyZoneTmpRoot.$section.DIRECTORY_SEPARATOR.'metadata');

                    $this->resetMetadataFile($emptyZoneTmpRoot.$section.DIRECTORY_SEPARATOR.'metadata');
                }
            }
        }

        // Perform fast rename of old zone and new tmp empty one
        $zoneToDeleteTmpRoot = $zoneParent.$this->_filesManager->findUniqueDirectoryName(
            $zoneParent, 'tmp'.StringUtils::generateRandom(8, 8)).DIRECTORY_SEPARATOR;

        $this->_filesManager->renameDirectory($this->_zoneRoot, $zoneToDeleteTmpRoot);
        $this->_filesManager->renameDirectory($emptyZoneTmpRoot, $this->_zoneRoot);

        // Delete all the old data
        $this->_filesManager->deleteDirectory($zoneToDeleteTmpRoot);

        return true;
    }


    /**
     * Totally delete all the contents of the specified cache section and reset its metadata file if it exists.
     *
     * NOTE: All cached data will be deleted even if the section is not expired yet!
     *
     * @param string $section The name for a cache section (under the current zone) that will be deleted
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True on success
     */
    public function clearSection($section){

        $sectionRoot = $this->_zoneRoot.DIRECTORY_SEPARATOR.$section.DIRECTORY_SEPARATOR;

        if(!$this->_filesManager->isDirectory($sectionRoot)){

            throw new UnexpectedValueException('section <'.$section.'> does not exist');
        }

        // We use a folder rename trick to free the section as fast as possible: We create a temporary folder with the empty
        // section structure, and perform a fast rename of the folders so we can then calmly delete the full old section folder

        $newEmptySectionTmpRoot = $this->_zoneRoot.DIRECTORY_SEPARATOR.$this->_filesManager->findUniqueDirectoryName(
            $this->_zoneRoot, 'tmp'.StringUtils::generateRandom(8, 8)).DIRECTORY_SEPARATOR;

        $this->_filesManager->createDirectory($newEmptySectionTmpRoot);

        // Copy the metadata file if it exists
        if($this->_filesManager->isFile($sectionRoot.'metadata')){

            $this->_filesManager->copyFile($sectionRoot.'metadata', $newEmptySectionTmpRoot.'metadata');

            $this->resetMetadataFile($newEmptySectionTmpRoot.'metadata');
        }

        // Perform fast rename of old section and new tmp empty one
        $sectionToDeleteTmpRoot = $this->_zoneRoot.DIRECTORY_SEPARATOR.$this->_filesManager->findUniqueDirectoryName(
            $this->_zoneRoot, 'tmp'.StringUtils::generateRandom(8, 8)).DIRECTORY_SEPARATOR;

        $this->_filesManager->renameDirectory($sectionRoot, $sectionToDeleteTmpRoot);
        $this->_filesManager->renameDirectory($newEmptySectionTmpRoot, $sectionRoot);

        // Delete all the old data
        $this->_filesManager->deleteDirectory($sectionToDeleteTmpRoot);

        return true;
    }


    /**
     * Totally delete all the cache data contents for the specified Id under the specified section
     *
     * @param string $section The name for a cache section (under the current zone) which id data will be deleted
     * @param string $id The identifier we previously used to store the data
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True on success
     */
    public function clearId($section, $id){

        $fullPath = $this->_getFullPathToId($section, $id);

        if(!$this->_filesManager->isFile($fullPath)){

            throw new UnexpectedValueException('Id <'.$id.'> does not contain data for the specified section');
        }

        $this->_filesManager->deleteFile($fullPath);

        return true;
    }


    /**
     * Auxiliary method to restart the expiration counter on the specified metadata file
     *
     * @param string $metadataFilePath Path to a metadata file. If the file does not exist, nothing will be done
     */
    private function resetMetadataFile($metadataFilePath){

        if($this->_filesManager->isFile($metadataFilePath)){

            $metadataContents = explode($this->_metadataDelimiter, file_get_contents($metadataFilePath, true));

            $this->_filesManager->saveFile($metadataFilePath, $metadataContents[0].$this->_metadataDelimiter);
        }
    }


    /**
     * Generates a valid file system path to the specified cache element.
     *
     * @param string $section The name for a cache section (under the current zone) where the data is or will be stored
     * @param string $id The unique data identifier
     *
     * @throws UnexpectedValueException
     *
     * @return string A full file system path to the cache element. It may or may not exist
     */
    private function _getFullPathToId($section, $id){

        if(!StringUtils::isString($section) || StringUtils::isEmpty($section)){

            throw new UnexpectedValueException('section must be a non empty string');
        }

        if(!StringUtils::isString($id)){

            throw new UnexpectedValueException('id must be a string');
        }

        $idPath = implode(DIRECTORY_SEPARATOR, str_split(base64_encode($id), 4));

        return StringUtils::formatPath($this->_zoneRoot.DIRECTORY_SEPARATOR.$section.DIRECTORY_SEPARATOR.$idPath.'.cache');
    }


    /**
     * Update the contents for the specified metadata file
     *
     * @param string $rootPath Filesystem path to the root folder of the metadata file we want to update
     * @param boolean $calculateExpiration If set to true, the metadata file expiration time will be calculated only if not already defined
     * @param string $timeToLive The value that will be stored as the time to live of the metadata file
     */
    private function _updateMetadataFile($rootPath, $calculateExpiration = false, $timeToLive = null){

        if(!$this->_filesManager->isDirectory($rootPath)){

            $this->_filesManager->createDirectory($rootPath, true);
        }

        $metadataContents = $this->_filesManager->isFile($rootPath.DIRECTORY_SEPARATOR.'metadata') ?
            explode($this->_metadataDelimiter, file_get_contents($rootPath.DIRECTORY_SEPARATOR.'metadata', true)) :
            ['', ''];

        // Time to live must have changed in order to effectively update the metadata file
        if($timeToLive !== null &&
           $metadataContents[0] !== '' &&
           $metadataContents[0] === (string)$timeToLive){

            return;
        }

        if($timeToLive !== null){

            $metadataContents[0] = (string)$timeToLive;
        }

        if($calculateExpiration){

            if($metadataContents[0] !== '' && $metadataContents[1] === ''){

                $metadataContents[1] = (string)(time() + $metadataContents[0]);
            }

        }else{

            $metadataContents[1] = '';
        }

        $this->_filesManager->saveFile($rootPath.DIRECTORY_SEPARATOR.'metadata', implode($this->_metadataDelimiter, $metadataContents));
    }
}

?>