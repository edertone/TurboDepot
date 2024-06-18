'use strict';


/**
 * FilesManager tests
 */

const path = require('path');
const os = require('os');
const { StringUtils, ArrayUtils } = require('turbocommons-ts');
const { FilesManager } = require(path.resolve('target/turbodepot-node/dist/ts/index'));


describe('FilesManager', function() {
    
    
    beforeEach(function() {
        
        this.basePath = path.resolve('src/test/resources/managers/filesManager');

        this.sut = new FilesManager();

        // Create a temporary folder
        this.tempFolder = this.sut.createTempDirectory('TurboDepot-FilesManagerTest');    
        expect(this.tempFolder).toContain('TurboDepot-FilesManagerTest');
        expect(this.sut.isDirectoryEmpty(this.tempFolder)).toBe(true);
        expect(this.sut.isFile(this.tempFolder)).toBe(false);
    });

    
    afterEach(function() {
  
        // Delete temporary folder
        this.sut.deleteDirectory(this.tempFolder);
    });
    
    
    it('should have a correctly implemented construct method', function() {

        // Test empty values
        expect(() => {
            this.sut = new FilesManager( null);
        }).toThrowError(Error, /rootPath must be a string/);
        
        expect((new FilesManager()).dirSep()).toBe(path.sep);
        expect((new FilesManager('')).dirSep()).toBe(path.sep);

        expect(() => {
            this.sut = new FilesManager('              ');
        }).toThrowError(Error, /Specified rootPath does not exist/);
        
        expect(() => {
            this.sut = new FilesManager({});
        }).toThrowError(Error, /rootPath must be a string/);

        // Test ok values
        expect((new FilesManager(this.tempFolder)).dirSep()).toBe(path.sep);

        // Test wrong values
        expect(() => {
            this.sut = new FilesManager('nonexistant path');
        }).toThrowError(Error, /Specified rootPath does not exist: nonexistant path/);
        
        // Test exceptions
        // Already tested      
    });
    
    
    it('should have a correctly implemented dirSep method', function() {

        expect(this.sut.dirSep()).toBe(path.sep);        
    });
    
    
    it('should have a correctly implemented isPathAbsolute method', function() {

        // Test empty values
        expect(() => {
            this.sut.isPathAbsolute(null);
        }).toThrowError(Error, /path must be a string/);
        
        expect(this.sut.isPathAbsolute('')).toBe(false);
        expect(this.sut.isPathAbsolute('            ')).toBe(false);   
        
        expect(() => {
            this.sut.isPathAbsolute(0);
        }).toThrowError(Error, /path must be a string/);
        
        expect(() => {
            this.sut.isPathAbsolute([]);
        }).toThrowError(Error, /path must be a string/);
        
        expect(() => {
            this.sut.isPathAbsolute({});
        }).toThrowError(Error, /path must be a string/);
        
        // Test ok values

        // Windows absolute paths
        expect(this.sut.isPathAbsolute('\\')).toBe(true);
        expect(this.sut.isPathAbsolute('\\\\')).toBe(true);
        expect(this.sut.isPathAbsolute('c:')).toBe(true);
        expect(this.sut.isPathAbsolute('d:')).toBe(true);
        expect(this.sut.isPathAbsolute('f:')).toBe(true);
        expect(this.sut.isPathAbsolute('c:\\')).toBe(true);
        expect(this.sut.isPathAbsolute('d:\\')).toBe(true);
        expect(this.sut.isPathAbsolute('f:\\')).toBe(true);
        expect(this.sut.isPathAbsolute('C:\\temp\\')).toBe(true);
        expect(this.sut.isPathAbsolute('C:\\Documents\\Newsletters\\Summer2018.pdf')).toBe(true);
        expect(this.sut.isPathAbsolute('\\Program Files\\Custom Utilities\\StringFinder.exe')).toBe(true);
        expect(this.sut.isPathAbsolute('C:\\Projects\\apilibrary\\apilibrary.sln')).toBe(true);
        expect(this.sut.isPathAbsolute('\\\\Server2\\Share\\Test\\Foo.txt')).toBe(true);
        expect(this.sut.isPathAbsolute('\\\\system07\\C$\\')).toBe(true);
        expect(this.sut.isPathAbsolute('\\var')).toBe(true);
        expect(this.sut.isPathAbsolute('\\utilities\\dir')).toBe(true);
        expect(this.sut.isPathAbsolute('/')).toBe(true);
        expect(this.sut.isPathAbsolute('//')).toBe(true);
        expect(this.sut.isPathAbsolute('c:/')).toBe(true);
        expect(this.sut.isPathAbsolute('d:/')).toBe(true);
        expect(this.sut.isPathAbsolute('C:/temp/')).toBe(true);
        expect(this.sut.isPathAbsolute('C:/Documents/Newsletters/Summer2018.pdf')).toBe(true);
        expect(this.sut.isPathAbsolute('/Program Files/Custom Utilities/StringFinder.exe')).toBe(true);
        expect(this.sut.isPathAbsolute('C:/Projects/apilibrary/apilibrary.sln')).toBe(true);
        expect(this.sut.isPathAbsolute('//Server2/Share/Test/Foo.txt')).toBe(true);
        expect(this.sut.isPathAbsolute('//system07/C$/')).toBe(true);
        expect(this.sut.isPathAbsolute('/var')).toBe(true);
        expect(this.sut.isPathAbsolute('/utilities/dir')).toBe(true);

        // Windows relative paths
        expect(this.sut.isPathAbsolute('')).toBe(false);
        expect(this.sut.isPathAbsolute('2018\\January.xlsx')).toBe(false);
        expect(this.sut.isPathAbsolute('..\\Publications\\TravelBrochure.pdf')).toBe(false);
        expect(this.sut.isPathAbsolute('C:Projects\\apilibrary\\apilibrary.sln')).toBe(false);
        expect(this.sut.isPathAbsolute('var')).toBe(false);
        expect(this.sut.isPathAbsolute('utilities\\dir')).toBe(false);
        expect(this.sut.isPathAbsolute('..\\Landuse')).toBe(false);
        expect(this.sut.isPathAbsolute('..\\..\\Data\\Final\\Infrastructure.gdb\\Streets')).toBe(false);
        expect(this.sut.isPathAbsolute('2018/January.xlsx')).toBe(false);
        expect(this.sut.isPathAbsolute('../Publications/TravelBrochure.pdf')).toBe(false);
        expect(this.sut.isPathAbsolute('C:Projects/apilibrary/apilibrary.sln')).toBe(false);
        expect(this.sut.isPathAbsolute('utilities/dir')).toBe(false);
        expect(this.sut.isPathAbsolute('../Landuse')).toBe(false);
        expect(this.sut.isPathAbsolute('../../Data/Final/Infrastructure.gdb/Streets')).toBe(false);

        // Linux absolute paths
        expect(this.sut.isPathAbsolute('/')).toBe(true);
        expect(this.sut.isPathAbsolute('//')).toBe(true);
        expect(this.sut.isPathAbsolute('/var')).toBe(true);
        expect(this.sut.isPathAbsolute('/utilities/dir')).toBe(true);
        expect(this.sut.isPathAbsolute('/export/home/heden/rhost')).toBe(true);

        // Linux relative paths
        expect(this.sut.isPathAbsolute('')).toBe(false);
        expect(this.sut.isPathAbsolute('2018/January.xlsx')).toBe(false);
        expect(this.sut.isPathAbsolute('../Publications/TravelBrochure.pdf')).toBe(false);
        expect(this.sut.isPathAbsolute('Projects/apilibrary/apilibrary.sln')).toBe(false);
        expect(this.sut.isPathAbsolute('var')).toBe(false);
        
        // Test wrong values
        // Test exceptions
        expect(() => {
            this.sut.isPathAbsolute(123253565);
        }).toThrowError(Error, /path must be a string/);
        
        expect(() => {
            this.sut.isPathAbsolute([1,2,3,4,5,7]);
        }).toThrowError(Error, /path must be a string/);
    });
    
    
    it('should have a correctly implemented isFile method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented isFileEqualTo method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented isDirectory method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented isDirectoryEqualTo method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented isDirectoryEmpty method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented countDirectoryItems method', function() {

        // Test empty values
        expect(() => {
            this.sut.countDirectoryItems(null);
        }).toThrowError(Error, /Path must be a string/);

        expect(() => {
            this.sut.countDirectoryItems(0);
        }).toThrowError(Error, /Path must be a string/);
        
        // Test ok values
        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'files', 0)).toBe(2);
        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'files', 1)).toBe(3);
        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'files', 2)).toBe(4);
        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'files')).toBe(4);

        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'folders', 0)).toBe(2);
        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'folders', 1)).toBe(3);
        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'folders', 2)).toBe(3);
        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'folders')).toBe(3);

        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'both', 0)).toBe(4);
        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'both', 1)).toBe(6);
        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'both', 2)).toBe(7);
        expect(this.sut.countDirectoryItems(this.basePath + '/countDirectoryItems/test-1', 'both')).toBe(7);

        // Test wrong values
        // Not necessary

        // Test exceptions
        // Not necessary    
    });
    
    
    it('should have a correctly implemented findDirectoryItems method', function() {

        // Test empty values
        expect(() => { this.sut.findDirectoryItems(null); }).toThrowError(Error, /Path must be a string/);
        expect(() => { this.sut.findDirectoryItems(0); }).toThrowError(Error, /Path must be a string/);
        expect(() => { this.sut.findDirectoryItems(''); }).toThrowError(Error, /Path does not exist/);
        expect(() => { this.sut.findDirectoryItems('       '); }).toThrowError(Error, /Path does not exist/);
        
        // Test ok values
        expect(ArrayUtils.isEqualTo(this.sut.findDirectoryItems(this.tempFolder, '/file/'), [])).toBe(true);
        expect(ArrayUtils.isEqualTo(this.sut.findDirectoryItems(this.tempFolder, '/.*/'), [])).toBe(true);
        expect(ArrayUtils.isEqualTo(this.sut.findDirectoryItems(this.tempFolder, '/^name$/'), [])).toBe(true);
        expect(ArrayUtils.isEqualTo(this.sut.findDirectoryItems(this.tempFolder, '/file/', 'relative', 'files'), [])).toBe(true);
        expect(ArrayUtils.isEqualTo(this.sut.findDirectoryItems(this.tempFolder, '/.*/', 'relative', 'files'), [])).toBe(true);
        expect(ArrayUtils.isEqualTo(this.sut.findDirectoryItems(this.tempFolder, '/^name$/', 'relative', 'files'), [])).toBe(true);
        expect(ArrayUtils.isEqualTo(this.sut.findDirectoryItems(this.tempFolder, '/file/', 'relative', 'folders'), [])).toBe(true);
        expect(ArrayUtils.isEqualTo(this.sut.findDirectoryItems(this.tempFolder, '/.*/', 'relative', 'folders'), [])).toBe(true);
        expect(ArrayUtils.isEqualTo(this.sut.findDirectoryItems(this.tempFolder, '/^name$/', 'relative', 'folders'), [])).toBe(true);

        // TODO - translate all missing tests from php      
    });
    
    
    it('should have a correctly implemented findUniqueDirectoryName method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented findUniqueFileName method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented createDirectory method', function() {

        // Test empty values
        expect(() => { this.sut.createDirectory(null); }).toThrowError(Error, /Path must be a non empty string/);
        expect(() => { this.sut.createDirectory(''); }).toThrowError(Error, /Path must be a non empty string/);
        expect(() => { this.sut.createDirectory('     '); }).toThrowError(Error, /Path must be a non empty string/);
        expect(() => { this.sut.createDirectory("\n\n\n"); }).toThrowError(Error, /Path must be a non empty string/);

        // Test ok values
        expect(this.sut.createDirectory(this.tempFolder + '/' + 'test1')).toBe(true);
        expect(this.sut.isDirectory(this.tempFolder + '/' + 'test1')).toBe(true);
        
        expect(this.sut.createDirectory(this.tempFolder + '/' + '1234')).toBe(true);
        expect(this.sut.isDirectory(this.tempFolder + '/' + '1234')).toBe(true);

        expect(this.sut.createDirectory(this.tempFolder + '/' + '-go-')).toBe(true);
        expect(this.sut.isDirectory(this.tempFolder + '/' + '-go-')).toBe(true);

        // Test already existing folders
        expect(!this.sut.createDirectory(this.tempFolder + '/' + 'test1')).toBe(true);
        expect(!this.sut.createDirectory(this.tempFolder + '/' + '1234')).toBe(true);
        expect(!this.sut.createDirectory(this.tempFolder + '/' + '-go-')).toBe(true);

        // Test already existing files
        this.sut.saveFile(this.tempFolder + '/' + '3', 'hello baby');
        expect(() => { this.sut.createDirectory(this.tempFolder + '/' + '3'); }).toThrowError(Error, /specified path is an existing/);

        // Test creating recursive folders
        let recursive1 = this.tempFolder + '/' + 'test55' + '/' + 'test' + '/' + 'tes5' + '/' + 't5';
        expect(() => { this.sut.createDirectory(recursive1); }).toThrowError(Error, /file or directory/);

        expect(this.sut.isDirectory(recursive1)).toBe(false);
        expect(this.sut.createDirectory(recursive1, true)).toBe(true);
        expect(this.sut.isDirectory(recursive1)).toBe(true);

        let recursive2 = this.tempFolder + '/' + 'a' + '/' + 'a' + '/' + 'a' + '/' + 'a';
        expect(() => { this.sut.createDirectory(recursive2); }).toThrowError(Error, /file or directory/); 

        expect(this.sut.isDirectory(recursive2)).toBe(false);
        expect(this.sut.createDirectory(recursive2, true)).toBe(true);
        expect(this.sut.isDirectory(recursive2)).toBe(true);

        let sut2 = new FilesManager(this.tempFolder);
        expect(sut2.isDirectory('subfolder-tocreate')).toBe(false);
        expect(sut2.createDirectory('subfolder-tocreate', true)).toBe(true);
        expect(sut2.isDirectory('subfolder-tocreate')).toBe(true);
        expect(sut2.isDirectory(this.tempFolder + '/' + 'subfolder-tocreate')).toBe(true);

        // Test wrong values
        // Test exceptions
        expect(() => { this.sut.createDirectory(this.tempFolder + '/' + 'wrongchars????'); }).toThrowError(Error, /Forbidden .* chars found/);
        expect(() => { this.sut.createDirectory(this.tempFolder + '/' + 'wrongchars*'); }).toThrowError(Error, /Forbidden .* chars found/);
        expect(() => { this.sut.createDirectory('\\345\\ertert'); }).toThrowError(Error, /file or directory/);
        expect(() => { this.sut.createDirectory(['\\345\\ertert', 1]); }).toThrowError(Error, /Path must be a non empty string/);
    });
    
    
    it('should have a correctly implemented getOSTempDirectory method', function() {

        expect(this.sut.getOSTempDirectory()).toBe(StringUtils.formatPath(os.tmpdir(), path.sep));
    });
    
    
    it('should have a correctly implemented createTempDirectory method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented getDirectoryList method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented getDirectorySize method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented copyDirectory method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented mirrorDirectory method', function() {

        // Test empty values
        expect(() => {
            this.sut.mirrorDirectory(null, null);
        }).toThrowError(Error, /Path must be a string/);
        
        expect(() => {
            this.sut.mirrorDirectory('', '');
        }).toThrowError(Error, /Path does not exist/);

        expect(() => {
            this.sut.mirrorDirectory('       ', '       ');
        }).toThrowError(Error, /Path does not exist/);
        
        expect(this.sut.createDirectory(this.tempFolder + '/test-1')).toBe(true);
        expect(this.sut.mirrorDirectory(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);
        
        // Test ok values

        // Alter contents on one file from the previously mirrored temp folder and make sure the mirror process restores it
        expect(this.sut.saveFile(this.tempFolder + '/test-1/c/d', 'modified')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(false);
        expect(this.sut.mirrorDirectory(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);

        // Rename one file from the previously mirrored temp folder and make sure the mirror process restores it
        expect(this.sut.renameFile(this.tempFolder + '/test-1/a', this.tempFolder + '/test-1/a-renamed')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(false);
        expect(this.sut.mirrorDirectory(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);

        // Delete one file from the previously mirrored temp folder and make sure the mirror process restores it
        expect(this.sut.deleteFile(this.tempFolder + '/test-1/c/d')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(false);
        expect(this.sut.mirrorDirectory(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);

        // Add one file to the previously mirrored temp folder and make sure the mirror process restores it
        expect(this.sut.saveFile(this.tempFolder + '/test-1/c/e', 'e')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(false);
        expect(this.sut.mirrorDirectory(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);

        // Rename one folder from the previously mirrored temp folder and make sure the mirror process restores it
        expect(this.sut.renameDirectory(this.tempFolder + '/test-1/c', this.tempFolder + '/test-1/c-modified')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(false);
        expect(this.sut.mirrorDirectory(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);
        
        // Delete one folder from the previously mirrored temp folder and make sure the mirror process restores it
        expect(this.sut.deleteDirectory(this.tempFolder + '/test-1/c')).toBe(1);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(false);
        expect(this.sut.mirrorDirectory(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);

        // Add one empty folder to the previously mirrored temp folder and make sure the mirror process restores it
        expect(this.sut.createDirectory(this.tempFolder + '/test-1/c/e')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(false);
        expect(this.sut.mirrorDirectory(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);
        expect(this.sut.isDirectoryEqualTo(this.basePath + '/mirrorDirectory/test-1', this.tempFolder + '/test-1')).toBe(true);

        // Test wrong values
        expect(() => {
            this.sut.mirrorDirectory(this.tempFolder + '/test-1', this.tempFolder + '/test-1');
        }).toThrowError(Error, /cannot mirror a directory into itself/);
        
        expect(() => {
            this.sut.mirrorDirectory(this.tempFolder + '/test-1', this.tempFolder.DIRECTORY_SEPARATOR + 'nonexistant');
        }).toThrowError(Error, /Path does not exist.*nonexistant/);
        
        // Test exceptions
        expect(() => {
            this.sut.mirrorDirectory('wrtwrtyeyery');
        }).toThrowError(Error, /Path does not exist.*wrtwrtyeyery/);
        
        expect(() => {
            this.sut.mirrorDirectory([1,2,3,4], [1,2,3,4]);
        }).toThrowError(Error, /Path must be a string/);
    });
    
    
    it('should have a correctly implemented syncDirectories method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented renameDirectory method', function() {

        // Test empty values
        expect(() => {
            this.sut.renameDirectory(null, null);
        }).toThrowError(Error, /Path must be a string/);

        expect(() => {
            this.sut.renameDirectory({}, {});
        }).toThrowError(Error, /Path must be a string/);

        expect(() => {
            this.sut.renameDirectory(0, 0);
        }).toThrowError(Error, /Path must be a string/);

        expect(() => {
            this.sut.renameDirectory('', '');
        }).toThrowError(Error, /Path does not exist: /);
        
        expect(() => {
            this.sut.renameDirectory('          ', '          ');
        }).toThrowError(Error, /Path does not exist: /);

        // Test ok values
        let dir = this.tempFolder + this.sut.dirSep() + 'dir1';
        expect(this.sut.createDirectory(dir)).toBe(true);
        expect(this.sut.renameDirectory(dir, dir + '_renamed')).toBe(true);
        expect(this.sut.isDirectory(dir)).toBe(false);
        expect(this.sut.isDirectory(dir + '_renamed')).toBe(true);

        // Test wrong values
        // Test exceptions
        expect(() => {
            this.sut.renameDirectory('nonexistant-path', dir);
        }).toThrowError(Error, /Path does not exist: nonexistant-path/);

        dir = this.tempFolder + this.sut.dirSep() + 'dir2';
        expect(this.sut.createDirectory(dir)).toBe(true);

        expect(() => {
            this.sut.renameDirectory(dir, dir);
        }).toThrowError(Error, /Invalid destination:.*dir2/);

        expect(() => {
            this.sut.renameDirectory(dir, 'nonexistant-path');
        }).toThrowError(Error, /Source and dest must be on the same path/);

        expect(() => {
            this.sut.renameDirectory(dir, dir + '_renamed' + this.sut.dirSep() + 'subrename');
        }).toThrowError(Error, /Source and dest must be on the same path/);
    });
    
    
    it('should have a correctly implemented deleteDirectory method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented saveFile method', function() {

        // Test empty values
        // TODO - the tests from the php version that pass null values to boolean parameters are skipped here
        expect(() => { this.sut.saveFile(null, null, null); }).toThrowError(Error, /Path must be a string/);
        expect(() => { this.sut.saveFile('', null, null); }).toThrowError(Error, /Could not write to file/);
        expect(() => { this.sut.saveFile('', '', null); }).toThrowError(Error, /Could not write to file/);
        
        // Test ok values
        expect(this.sut.isFile(this.tempFolder + '/empty.txt')).toBe(false);
        expect(this.sut.saveFile(this.tempFolder + '/empty.txt')).toBe(true);
        expect(this.sut.readFile(this.tempFolder + '/empty.txt')).toBe('');

        expect(this.sut.isFile(this.tempFolder + '/file.txt')).toBe(false);
        expect(this.sut.saveFile(this.tempFolder + '/file.txt', 'test')).toBe(true);
        expect(this.sut.readFile(this.tempFolder + '/file.txt')).toBe('test');

        expect(this.sut.saveFile(this.tempFolder + '/file.txt', 'test', true)).toBe(true);
        expect(this.sut.readFile(this.tempFolder + '/file.txt')).toBe('testtest');

        expect(this.sut.saveFile(this.tempFolder + '/file.txt', 'replaced', false)).toBe(true);
        expect(this.sut.readFile(this.tempFolder + '/file.txt')).toBe('replaced');

        expect(this.sut.isFile(this.tempFolder + '/file2.txt')).toBe(false);
        expect(this.sut.saveFile(this.tempFolder + '/file2.txt', 'file2', true)).toBe(true);
        expect(this.sut.readFile(this.tempFolder + '/file2.txt')).toBe('file2');

        let sut2 = new FilesManager(this.tempFolder);
        expect(sut2.isFile('file3.txt')).toBe(false);
        expect(sut2.saveFile('file3.txt', 'file3')).toBe(true);
        expect(sut2.readFile('file3.txt')).toBe('file3');

        expect(this.sut.isDirectory(this.tempFolder + '/dir1')).toBe(false);
        expect(this.sut.isDirectory(this.tempFolder + '/dir1/dir2')).toBe(false);
        expect(this.sut.isFile(this.tempFolder + '/dir1/dir2/file.txt')).toBe(false);
        expect(this.sut.saveFile(this.tempFolder + '/dir1/dir2/file.txt', 'test', false, true)).toBe(true);
        expect(this.sut.readFile(this.tempFolder + '/dir1/dir2/file.txt')).toBe('test');

        // Test wrong values
        expect(() => { this.sut.saveFile('nonexistantpath/nonexistantfile'); }).toThrowError(Error, /Could not write to file/);
        expect(() => { this.sut.saveFile([1,2,3,4,5]); }).toThrowError(Error, /Path must be a string/);
        
        expect(this.sut.saveFile(this.tempFolder + '/dir1/file')).toBe(true);
        expect(() => { this.sut.saveFile(this.tempFolder + '/dir1/file/file.txt', 'test', false, true); }).toThrowError(Error, /specified path is an existing file/);

        // Test exceptions
        expect(this.sut.isDirectory(this.tempFolder + '/dir')).toBe(false);
        expect(this.sut.createDirectory(this.tempFolder + '/dir')).toBe(true);

        expect(() => { this.sut.saveFile(this.tempFolder + '/dir'); }).toThrowError(Error, /Could not write to file/);
    });
    
    
    it('should have a correctly implemented createTempFile method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented mergeFiles method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented getFileSize method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented getFileModificationTime method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented readFile method', function() {

        // TODO - translate from php
    });
    
    
    it('should have a correctly implemented readFileAsBase64 method', function() {

        // Test empty values
        // TODO - copy from php  

        // Test ok values
        expect(this.sut.readFileAsBase64(this.basePath + '/readFile/excel-sheet.xlsx'))
            .toBe('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');
        // TODO - more cases - copy from php

        // Test wrong values
        // TODO - copy from php

        // Test exceptions
        // TODO - copy from php
    });
    
    
    it('should have a correctly implemented readFileBuffered method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented copyFile method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented renameFile method', function() {

        // Test empty values
        expect(() => {
            this.sut.renameFile(null, null);
        }).toThrowError(Error, /Path must be a string/);

        expect(() => {
            this.sut.renameFile({}, {});
        }).toThrowError(Error, /Path must be a string/);

        expect(() => {
            this.sut.renameFile(0, 0);
        }).toThrowError(Error, /Path must be a string/);

        expect(() => {
            this.sut.renameFile('', '');
        }).toThrowError(Error, /File does not exist: /);

        expect(() => {
            this.sut.renameFile('          ', '          ');
        }).toThrowError(Error, /File does not exist: /);

        // Test ok values
        let file = this.tempFolder + this.sut.dirSep() + 'file1';
        expect(this.sut.saveFile(file, 'data')).toBe(true);
        expect(this.sut.renameFile(file, file + '_renamed')).toBe(true);
        expect(this.sut.isFile(file)).toBe(false);
        expect(this.sut.isFile(file + '_renamed')).toBe(true);

        // Test wrong values
        // Test exceptions
        expect(() => {
            this.sut.renameFile('nonexistant-path', file);
        }).toThrowError(Error, /File does not exist: nonexistant-path/);

        file = this.tempFolder + this.sut.dirSep() + 'dir2';
        expect(this.sut.saveFile(file, 'data')).toBe(true);

        expect(() => {
            this.sut.renameFile(file, file);
        }).toThrowError(Error, /Invalid destination:.*dir2/);

        expect(() => {
            this.sut.renameFile(file, 'nonexistant-path');
        }).toThrowError(Error, /Source and dest must be on the same path/);

        expect(() => {
            this.sut.renameFile(file, file + '_renamed' + this.sut.dirSep() + 'subrename');
        }).toThrowError(Error, /Source and dest must be on the same path/);
    });
});

// TODO - write all missing tests
