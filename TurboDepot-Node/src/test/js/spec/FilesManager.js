#!/usr/bin/env node

'use strict';


/**
 * FilesManager tests
 */

const fs = require('fs');
const os = require('os');
const path = require('path');
const crypto = require('crypto');
const { FilesManager } = require('./../../../../target/turbodepot-node/dist/ts/index');


describe('FilesManager', function() {
    
    
    beforeEach(function() {
        
        this.sut = new FilesManager(fs, os, path, process, crypto);

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
    
    
    it('should have a correctly implemented NODE specific construct method', function() {

        // NOTE: TEST SPECIFIC FOR NODEJS!!
        // This test is specific for the node js version of the FilesManager class.
        // It will check that the constructor fails if any of the required injected node js modules is missing.
        
        expect(() => {
            this.sut = new FilesManager();
        }).toThrowError(Error, 'Node objects (fs, os, path, process, crypto) must be passed to constructor');
        
        expect(() => {
            this.sut = new FilesManager(fs);
        }).toThrowError(Error, 'Node objects (fs, os, path, process, crypto) must be passed to constructor');
        
        expect(() => {
            this.sut = new FilesManager(fs, os);
        }).toThrowError(Error, 'Node objects (fs, os, path, process, crypto) must be passed to constructor');
        
        expect(() => {
            this.sut = new FilesManager(fs, os, path);
        }).toThrowError(Error, 'Node objects (fs, os, path, process, crypto) must be passed to constructor');
        
        expect(() => {
            this.sut = new FilesManager(fs, os, path, process);
        }).toThrowError(Error, 'Node objects (fs, os, path, process, crypto) must be passed to constructor');
        
       expect((new FilesManager(fs, os, path, process, crypto)).dirSep()).toBe(path.sep);
    });  
    
    
    it('should have a correctly implemented construct method', function() {

        // Test empty values
        expect(() => {
            this.sut = new FilesManager(fs, os, path, process, crypto, null);
        }).toThrowError(Error, /rootPath must be a string/);
        
        expect((new FilesManager(fs, os, path, process, crypto)).dirSep()).toBe(path.sep);
        expect((new FilesManager(fs, os, path, process, crypto, '')).dirSep()).toBe(path.sep);

        expect(() => {
            this.sut = new FilesManager(fs, os, path, process, crypto, '              ');
        }).toThrowError(Error, /Specified rootPath does not exist/);
        
        expect(() => {
            this.sut = new FilesManager(fs, os, path, process, crypto, {});
        }).toThrowError(Error, /rootPath must be a string/);

        // Test ok values
        expect((new FilesManager(fs, os, path, process, crypto, this.tempFolder)).dirSep()).toBe(path.sep);

        // Test wrong values
        expect(() => {
            this.sut = new FilesManager(fs, os, path, process, crypto, 'nonexistant path');
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
});

// TODO - write all missing tests
