#!/usr/bin/env node

'use strict';


/**
 * FilesManager tests
 */

const path = require('path');
const { FilesManager } = require(path.resolve('target/turbodepot-node/dist/ts/index'));


describe('FilesManager', function() {
    
    
    beforeEach(function() {
        
        this.basePath = path.resolve('src/test/js/resources/managers/filesManager');

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
    
    
    it('should have a correctly implemented findDirectoryItems method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented findUniqueDirectoryName method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented findUniqueFileName method', function() {

        // TODO - translate from php      
    });
    
    
    it('should have a correctly implemented createDirectory method', function() {

        // TODO - translate from php      
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
        expect(this.sut.deleteDirectory(this.tempFolder + '/test-1/c')).toBe(true);
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
});

// TODO - write all missing tests
