<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\test\php\managers;

use stdClass;
use PHPUnit\Framework\TestCase;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbodepot\src\main\php\managers\DepotManager;
use org\turbotesting\src\main\php\utils\AssertUtils;


/**
 * DepotManagerTest tests
 *
 * @return void
 */
class DepotManagerTest extends TestCase {


    /**
     * @see TestCase::setUpBeforeClass()
     *
     * @return void
     */
    public static function setUpBeforeClass(){

        // Nothing necessary here
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(){

        $this->filesManager = new FilesManager();

        // Create a temporary folder
        $this->tempFolder = $this->filesManager->createTempDirectory('TurboDepot-DepotManagerTest');
        $this->assertTrue(strpos($this->tempFolder, 'TurboDepot-DepotManagerTest') !== false);
        $this->assertTrue($this->filesManager->isDirectoryEmpty($this->tempFolder));
        $this->assertFalse($this->filesManager->isFile($this->tempFolder));

        $this->setup = json_decode($this->filesManager->readFile(__DIR__.'/../../resources/managers/depotManager/empty-turbodepot.json'));

        $this->sut = new DepotManager($this->setup);
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){

        // Delete temporary folder
        $this->filesManager->deleteDirectory($this->tempFolder);
    }


    /**
     * @see TestCase::tearDownAfterClass()
     *
     * @return void
     */
    public static function tearDownAfterClass(){

        // Nothing necessary here
    }


    /**
     * testConstruct
     *
     * @return void
     */
    public function testConstruct(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut = new DepotManager(null, ''); }, '/expects a valid path to turbodepot setup or an stdclass instance with the setup data/');
        AssertUtils::throwsException(function(){ $this->sut = new DepotManager('', ''); }, '/expects a valid path to turbodepot setup or an stdclass instance with the setup data/');
        AssertUtils::throwsException(function(){ $this->sut = new DepotManager('              ', ''); }, '/expects a valid path to turbodepot setup or an stdclass instance with the setup data/');
        AssertUtils::throwsException(function(){ $this->sut = new DepotManager(new stdClass(), ''); }, '/expects a valid path to turbodepot setup or an stdclass instance with the setup data/');

        // Test ok values
        $this->assertSame('org\turbodepot\src\main\php\managers\DepotManager',
            get_class(new DepotManager(__DIR__.'/../../resources/managers/depotManager/empty-turbodepot.json')));

        $setup = json_decode($this->filesManager->readFile(__DIR__.'/../../resources/managers/depotManager/empty-turbodepot.json'));

        $this->assertSame('stdClass', get_class($setup));

        $this->assertSame('org\turbodepot\src\main\php\managers\DepotManager', get_class(new DepotManager($setup)));

        // Test wrong values
        $this->assertTrue($this->filesManager->saveFile($this->tempFolder.DIRECTORY_SEPARATOR.'testfile.txt', 'somecontent'));

        AssertUtils::throwsException(function(){ $this->sut = new DepotManager($this->tempFolder.DIRECTORY_SEPARATOR.'testfile.txt'); },
            '/expects a valid path to turbodepot setup or an stdclass instance with the setup data/');

        // Test exceptions
        // Already tested
    }


    /**
     * testGetStorageFolderManager
     *
     * @return void
     */
    public function testGetStorageFolderManager(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->getStorageFolderManager(); }, '/storageFolderManager not available. Check it is correctly configured on turbodepot setup/');

        // Test ok values
        $this->assertTrue($this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'custom', true));

        $this->setup->depots[0]->storageFolderPath = $this->tempFolder.DIRECTORY_SEPARATOR.'storage';

        $this->sut = new DepotManager($this->setup);

        $this->assertSame('org\turbodepot\src\main\php\managers\StorageFolderManager',
            get_class($this->sut->getStorageFolderManager()));

        // Test wrong values
        // Test exceptions

        $this->setup->depots[0]->storageFolderPath = 'nonexistantpath';

        $this->sut = new DepotManager($this->setup);

        AssertUtils::throwsException(function(){ $this->sut->getStorageFolderManager(); }, '/Could not find storage folder based on: nonexistantpath/');
    }


    /**
     * testGetFilesManager
     *
     * @return void
     */
    public function testGetFilesManager(){

        // Test empty values
        // Not necessary

        // Test ok values
        $this->assertSame('org\turbodepot\src\main\php\managers\FilesManager',
            get_class($this->sut->getFilesManager()));

        // Test wrong values
        // Not necessary

        // Test exceptions
        // Not necessary
    }


    /**
     * testGetTmpFilesManager
     *
     * @return void
     */
    public function testGetTmpFilesManager(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->getTmpFilesManager(); }, '/Could not find a valid fileSystem source for tmpFilesManager on turbodepot setup/');

        // Test ok values
        $this->assertTrue($this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'tmp'));

        $tmpFilesSource = new stdClass();
        $tmpFilesSource->name = "tmp_files_source";
        $tmpFilesSource->path = $this->tempFolder.DIRECTORY_SEPARATOR.'tmp';
        $this->setup->sources->fileSystem[] = $tmpFilesSource;

        $this->setup->depots[0]->tmpFiles->source = "tmp_files_source";

        $this->sut = new DepotManager($this->setup);

        $this->assertSame('org\turbodepot\src\main\php\managers\TmpFilesManager',
            get_class($this->sut->getTmpFilesManager()));

        // Test wrong values
        // Test exceptions
        // Not necessary
    }


    /**
     * testGetLocalizedFilesManager
     *
     * @return void
     */
    public function testGetLocalizedFilesManager(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->getLocalizedFilesManager(); }, '/Could not find a valid fileSystem source for localizedFilesManager on turbodepot setup/');

        // Test ok values
        $this->assertTrue($this->filesManager->createDirectory($this->tempFolder.DIRECTORY_SEPARATOR.'localized'));

        $localizedFilesSource = new stdClass();
        $localizedFilesSource->name = "localized_files_source";
        $localizedFilesSource->path = $this->tempFolder.DIRECTORY_SEPARATOR.'localized';

        $this->setup->sources->fileSystem[] = $localizedFilesSource;

        $this->setup->depots[0]->localizedFiles->source = "localized_files_source";
        $this->setup->depots[0]->localizedFiles->locales = ['en_US', 'es_ES'];
        $this->setup->depots[0]->localizedFiles->locations = [[
            'label' => 'test-json',
            'path' => __DIR__.'/../../resources/managers/localizedFilesManager/locales/test-json/$locale/$bundle.json',
            'bundles' => ['Locales']
        ]];

        $this->sut = new DepotManager($this->setup);

        $this->assertSame('org\turbodepot\src\main\php\managers\LocalizedFilesManager',
            get_class($this->sut->getLocalizedFilesManager()));

        // Test wrong values
        // Test exceptions
        // Not necessary
    }


    /**
     * testGetLogsManager
     *
     * @return void
     */
    public function testGetLogsManager(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->getLogsManager(); }, '/Could not find a valid fileSystem source for logsManager on turbodepot setup/');

        // Test ok values
        $this->setup->sources->fileSystem[] = new stdclass();

        $this->setup->sources->fileSystem[0]->name = 'tmp_source';
        $this->setup->sources->fileSystem[0]->path = $this->tempFolder;

        $this->setup->depots[0]->logs->source = 'tmp_source';

        $this->sut = new DepotManager($this->setup);

        $this->assertSame('org\turbodepot\src\main\php\managers\LogsManager',
            get_class($this->sut->getLogsManager()));

        $this->assertFalse($this->filesManager->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'somelog'));

        $this->sut->getLogsManager()->write('some text', 'somelog');

        $this->assertTrue($this->filesManager->isFile($this->tempFolder.DIRECTORY_SEPARATOR.'somelog'));
        $this->assertRegExp('/....-..-.. ..:..:......... some text\n/', $this->filesManager->readFile($this->tempFolder.DIRECTORY_SEPARATOR.'somelog'));

        // Test wrong values
        // Not necessary

        // Test exceptions
        $this->setup->sources->fileSystem[0]->path = '/invalidPath';

        $this->sut = new DepotManager($this->setup);

        AssertUtils::throwsException(function(){ $this->sut->getLogsManager(); }, '/LogsManager received an invalid rootPath: \/invalidPath/');
    }


    /**
     * testGetGoogleDriveManager
     *
     * @return void
     */
    public function testGetGoogleDriveManager(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->getGoogleDriveManager(); }, '/googleDriveManager not available. Check it is correctly configured on turbodepot setup/');

        // Test ok values
        // Can't be tested, cause a connection to google drive must be performed.

        // Test wrong values
        // Not necessary

        // Test exceptions
        $this->setup->depots[0]->googleDrive->apiClientRoot = 'invalid path';
        $this->sut = new DepotManager($this->setup);

        AssertUtils::throwsException(function(){ $this->sut->getGoogleDriveManager(); }, '/Specified googleApiPhpCLientRoot folder is not valid/');

        // Set a valid api client root and check missing serviceAccountCredentials file
        $this->setup->depots[0]->googleDrive->apiClientRoot = __DIR__.'/../../resources/managers/depotManager/fake-api-root';
        $this->sut = new DepotManager($this->setup);

        AssertUtils::throwsException(function(){ $this->sut->getGoogleDriveManager(); }, '/Could not find serviceAccountCredentials file/');

        // Set a valid accountCredentialsPath and check that cache is not enabled
        $this->setup->depots[0]->googleDrive->accountCredentialsPath = __DIR__.'/../../resources/managers/depotManager/fake-service-account-credentials.json';
        $this->sut = new DepotManager($this->setup);

        AssertUtils::throwsException(function(){ $this->sut->getGoogleDriveManager()->getCacheZoneName(); }, '/Cache is not enabled for this instance/');

        // Set a valid cache setup and make sure it is correctly enabled
        $this->setup->depots[0]->googleDrive->cacheRootPath = $this->tempFolder;
        $this->sut = new DepotManager($this->setup);

        AssertUtils::throwsException(function(){ $this->sut->getGoogleDriveManager(); }, '/zone must be a non empty string/');

        $this->setup->depots[0]->googleDrive->cacheZone = 'test-zone';
        $this->sut = new DepotManager($this->setup);

        $this->assertSame('test-zone', $this->sut->getGoogleDriveManager()->getCacheZoneName());
    }


    /**
     * testGetUsersManager
     *
     * @return void
     */
    public function testGetUsersManager(){

        // Test empty values
        AssertUtils::throwsException(function(){ $this->sut->getUsersManager(); }, '/Could not find a valid database source for usersManager on turbodepot setup/');

        // Test ok values
        $this->setup->sources->mariadb[] = new stdclass();

        // TODO - should be interesting to extract test database connection info to a common file for all tests
        $this->setup->sources->mariadb[0]->name = 'tmp_db_source';
        $this->setup->sources->mariadb[0]->host = 'localhost';
        $this->setup->sources->mariadb[0]->database = '';
        $this->setup->sources->mariadb[0]->user = 'root';
        $this->setup->sources->mariadb[0]->password = '';

        $this->setup->depots[0]->users->source = 'tmp_db_source';

        $this->sut = new DepotManager($this->setup);

        $this->assertSame('org\turbodepot\src\main\php\managers\UsersManager',
            get_class($this->sut->getUsersManager()));

        // TODO - Test some basic operations with saving and reading some users

        // Test wrong values
        // Not necessary

        // Test exceptions

        // Set an invalid database connection values and check it throws the expected exception
        $this->setup->sources->mariadb[0]->user = 'invalid user';

        $this->sut = new DepotManager($this->setup);

        AssertUtils::throwsException(function(){ $this->sut->getUsersManager(); }, '/Access denied for user.*invalid user/');

        $this->setup->depots[0]->users->source = 'invalid source';
        $this->sut = new DepotManager($this->setup);

        AssertUtils::throwsException(function(){ $this->sut->getUsersManager(); }, '/Could not find a valid database source for usersManager on turbodepot setup/');
    }


    // TODO - implement all missing tests


    /**
     * testTodo
     *
     * @return void
     */
    public function testTodo(){

        // Test empty values
        // TODO

        // Test ok values
        // TODO

        // Test wrong values
        // TODO

        // Test exceptions
        // TODO

        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}

?>