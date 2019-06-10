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
     * See this class constructor for documentation
     * @var string
     */
    private $_zoneTimeToLive = -1;


    /**
     * The full filesystem path to the metadata file that stores information about the current zone
     * @var string
     */
    private $_metadataFilePath = '';


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
     * @param string $timeToLive Defines the number of seconds after which the whole zone cache data will be deleted. Set it to -1 if you want to
     *        manually manage the lifetime of the zone cached data. (1 hour = 3600 seconds, 1 day = 86400 seconds)
     *
     * @throws UnexpectedValueException
     */
    public function __construct(string $rootPath, string $zone, int $timeToLive = -1){

        if(!StringUtils::isString($zone) || StringUtils::isEmpty($zone)){

            throw new UnexpectedValueException('zone must be a non empty string');
        }

        $this->_zoneTimeToLive = $timeToLive;
        $this->_zoneRoot = $rootPath.DIRECTORY_SEPARATOR.$zone;
        $this->_metadataFilePath = $this->_zoneRoot.DIRECTORY_SEPARATOR.'metadata';

        $this->_filesManager = new FilesManager();

        // Make sure the metadata file for the current zone exists
        if(!is_file($this->_metadataFilePath)){

            if(!is_dir($rootPath)){

                throw new UnexpectedValueException('Invalid rootPath Received: '.$rootPath);
            }

            if(!is_dir($this->_zoneRoot)){

                $this->_filesManager->createDirectory($this->_zoneRoot);
            }

            // Add the timeToLive value to the current timestamp and store it on the metadata file.
            // If timeToLive is disabled, an empty string will be stored
            $this->_filesManager->saveFile($this->_metadataFilePath,
                $this->_zoneTimeToLive === -1 ? '' : (string)(time() + $this->_zoneTimeToLive));
        }

        if($this->isZoneExpired()){

            $this->clearZone();
        }
    }


    /**
     * Tells if the currently active zone is expired or not.
     * Expiration is based on the timeToLive setup and defines when the cache data needs to be renewed.
     *If the current zone is expired, we must call to clearZone() to empty the zone cache files.
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True if the zone expiration time has been exceeded. False otherwise
     */
    public function isZoneExpired(){

        $zoneExpirationTime = file_get_contents($this->_metadataFilePath, true);

        if($zoneExpirationTime === false){

            throw new UnexpectedValueException('could not read cache metadata file: '.$this->_metadataFilePath);
        }

        return $zoneExpirationTime !== '' && (time() > (float)$zoneExpirationTime);
    }


    /**
     * Save arbitrary information into the cache under a specific section and identifier.
     *
     * @param string $section The name for a cache section (under the current zone) where we want to store the data.
     * @param string $id A unique identifier that we want to use for the stored data. This will be required to obtain it later
     * @param string $data The data we want to store.
     *
     * @throws UnexpectedValueException
     *
     * @return string The same data
     */
    public function add($section, $id, $data, int $timeToLive = -1){

        // TODO - implement time to live that is specific only to this data
        // (The zone timeToLive value will have preference over this one)

        if(!StringUtils::isString($data)){

            throw new UnexpectedValueException('data must be a string');
        }

        $fullPath = $this->_getFullPathToId($section, $id);
        $basePath = StringUtils::getPath($fullPath);

        if(!$this->_filesManager->isDirectory($basePath)){

            $this->_filesManager->createDirectory($basePath, true);
        }

        $this->_filesManager->saveFile($fullPath, $data);

        return $data;
    }


    /**
     * Read data which was previously stored on the cache
     *
     * @param string $section The name for a cache section (under the current zone) where the data is stored
     * @param string $id The identifier we previously used to store the data we want to retrieve
     *
     * @return string|null The requested data or null if id or section were not found
     */
    public function get($section, $id){

        $fullPath = $this->_getFullPathToId($section, $id);

        if(is_file($fullPath)){

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
     * Totally delete all the contents of the actual cache zone.
     *
     * @return boolean True on success
     */
    public function clearZone(){

        // First of all, update the zone file with the new expiration time
        $this->_filesManager->saveFile($this->_metadataFilePath,
            $this->_zoneTimeToLive === -1 ? '' : (string)(time() + $this->_zoneTimeToLive));

        // Delete all the other elements inside the current zone
        $zoneItems = $this->_filesManager->getDirectoryList($this->_zoneRoot);

        foreach ($zoneItems as $zoneItem) {

            if(is_dir($this->_zoneRoot.DIRECTORY_SEPARATOR.$zoneItem)){

                $this->_filesManager->deleteDirectory($this->_zoneRoot.DIRECTORY_SEPARATOR.$zoneItem);
            }
        }

        return true;
    }


    /**
     * Totally delete all the contents of the specified cache section
     *
     * @param string $section The name for a cache section (under the current zone) that will be deleted
     *
     * @throws UnexpectedValueException
     *
     * @return boolean True on success
     */
    public function clearSection($section){

        if(!is_dir($this->_zoneRoot.DIRECTORY_SEPARATOR.$section)){

            throw new UnexpectedValueException('section <'.$section.'> does not exist');
        }

        $this->_filesManager->deleteDirectory($this->_zoneRoot.DIRECTORY_SEPARATOR.$section);

        return true;
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
}

?>