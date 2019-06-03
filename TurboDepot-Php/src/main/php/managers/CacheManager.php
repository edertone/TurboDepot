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
    private $_rootPath = '';


    /**
     * A files manager instance that is used by this class
     * @var FilesManager
     */
    private $_filesManager = null;


    /**
     * Defines a general purpose file-system based storage cache.
     *
     * Use this to fetch data that requires important amounts of time to be calculated or generated,
     * and improve the time that's necessary to get it on successive requests.
     *
     * This cache stores all the data on plain file system, so take it into consideration when requiring
     * faster response times.
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

        if(!is_dir($rootPath)){

            throw new UnexpectedValueException('CacheManager received an invalid rootPath: '.$rootPath);
        }

        if(!StringUtils::isString($zone) || StringUtils::isEmpty($zone)){

            throw new UnexpectedValueException('zone must be a non empty string');
        }

        if(!is_dir($rootPath.DIRECTORY_SEPARATOR.$zone)){

            $this->_filesManager->createDirectory($rootPath.DIRECTORY_SEPARATOR.$zone);
        }

        $this->_rootPath = $rootPath.DIRECTORY_SEPARATOR.$zone;
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
     * @return string The received data
     */
    public function add($section, $id, $data){

        if(!StringUtils::isString($data)){

            throw new UnexpectedValueException('data must be a string');
        }

        $fullPath = $this->_idToFullPath($section, $id);
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

        $fullPath = $this->_idToFullPath($section, $id);

        try {

            if(($content = file_get_contents($fullPath, true)) !== false){

                return $content;
            }

        } catch (Throwable $e) {

            return null;
        }

        return null;
    }


    /**
     * TODO
     */
    public function clear($section = '', $id = ''){

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
    private function _idToFullPath($section, $id){

        if(!StringUtils::isString($section) || StringUtils::isEmpty($section)){

            throw new UnexpectedValueException('section must be a non empty string');
        }

        if(!StringUtils::isString($id) || StringUtils::isEmpty($id)){

            throw new UnexpectedValueException('id must be a non empty string');
        }

        $idPath = implode(DIRECTORY_SEPARATOR, str_split(base64_encode($id), 4));

        return StringUtils::formatPath($this->_rootPath.DIRECTORY_SEPARATOR.$section.DIRECTORY_SEPARATOR.$idPath.'.cache');
    }
}

?>