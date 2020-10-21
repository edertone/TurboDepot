'use strict';


/**
 * TerminalManager tests
 */

const path = require('path');
const { FilesManager, TerminalManager } = require(path.resolve('target/turbodepot-node/dist/ts/index'));


describe('TerminalManagerTest', function() {
    
    
    beforeAll(function() {
        
        this.resourcesPath = path.resolve('src/test/resources/managers/terminalManager');
        this.initialDir = path.resolve('./');
        
        expect(this.initialDir).not.toContain('TurboDepot-TerminalManagerTest');
    });
    
    
    beforeEach(function() {
        
        this.filesManager = new FilesManager();
        this.sut = new TerminalManager(this.initialDir);

        // Create a temporary folder, set it as the work dir and make sure the system is pointing there
        expect(path.resolve('./')).not.toContain('TurboDepot-TerminalManagerTest');
        expect(path.resolve(process.cwd())).not.toContain('TurboDepot-TerminalManagerTest');
        
        this.tempFolder = this.sut.createTempDirectory('TurboDepot-TerminalManagerTest');
        
        expect(this.tempFolder).toContain('TurboDepot-TerminalManagerTest');
        expect(path.resolve('./')).toContain('TurboDepot-TerminalManagerTest');
        expect(path.resolve(process.cwd())).toContain('TurboDepot-TerminalManagerTest');
    });

    
    afterEach(function() {
  
        process.chdir(this.initialDir);
        expect(path.resolve('./')).toBe(this.initialDir);
        
        this.filesManager.deleteDirectory(this.tempFolder);
    });
    
    
    it('should have a correctly implemented construct method when linkSystemWorkDir is set to true', function() {

        // Test empty values
        expect(() => {
            this.sut = new TerminalManager(null);
        }).toThrowError(Error, /workDir must be a string/);
        
        expect((new TerminalManager()).getWorkDir()).toBe(path.resolve('./'));
        expect(path.resolve(process.cwd())).toContain('TurboDepot-TerminalManagerTest');
        
        expect((new TerminalManager('')).getWorkDir()).toBe(path.resolve('./'));
        expect(path.resolve(process.cwd())).toContain('TurboDepot-TerminalManagerTest');
        
        expect(() => {
            this.sut = new TerminalManager('              ');
        }).toThrowError(Error, /Specified rootPath does not exist/);
        
        expect(() => {
            this.sut = new TerminalManager({});
        }).toThrowError(Error, /workDir must be a string/);

        // Test ok values
        expect((new TerminalManager(this.tempFolder)).getWorkDir()).toBe(path.resolve(this.tempFolder));
        expect(path.resolve(process.cwd())).toContain('TurboDepot-TerminalManagerTest');
        
        expect(this.filesManager.createDirectory(this.tempFolder + this.filesManager.dirSep() + 'subfolder-for-test')).toBe(true);
        
        expect((new TerminalManager(this.tempFolder + this.filesManager.dirSep() + 'subfolder-for-test')).getWorkDir()).toContain('subfolder-for-test');
        expect(path.resolve(process.cwd())).toContain('subfolder-for-test');
        
        // Test wrong values
        expect(() => {
            this.sut = new TerminalManager('nonexistant path');
        }).toThrowError(Error, /Specified rootPath does not exist:.*nonexistant path/);
        
        // Test exceptions
        // Already tested         
    });
    
    
    it('should have a correctly implemented construct method when linkSystemWorkDir is set to false', function() {

        process.chdir(this.initialDir);

        // Test empty values
        expect(() => {
            this.sut = new TerminalManager(null, false);
        }).toThrowError(Error, /workDir must be a string/);
        
        expect((new TerminalManager('', false)).getWorkDir()).toBe(this.initialDir);
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        expect(() => {
            this.sut = new TerminalManager('              ', false);
        }).toThrowError(Error, /Specified rootPath does not exist/);
        
        expect(() => {
            this.sut = new TerminalManager({}, false);
        }).toThrowError(Error, /workDir must be a string/);

        // Test ok values
        expect((new TerminalManager(this.tempFolder, false)).getWorkDir()).toContain('TurboDepot-TerminalManagerTest');
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        expect(this.filesManager.createDirectory(this.tempFolder + this.filesManager.dirSep() + 'subfolder-for-test')).toBe(true);
        
        expect((new TerminalManager(this.tempFolder + this.filesManager.dirSep() + 'subfolder-for-test', false)).getWorkDir()).toContain('subfolder-for-test');
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        // Test wrong values
        expect(() => {
            this.sut = new TerminalManager('nonexistant path', false);
        }).toThrowError(Error, /Specified rootPath does not exist:.*nonexistant path/);
        
        // Test exceptions
        // Already tested       
    });
    
    
    it('should have a correctly implemented construct method when two independent TerminalManager instances are used', function() {

        process.chdir(this.initialDir);
        expect(path.resolve(process.cwd())).toBe(this.initialDir);

        expect(this.filesManager.createDirectory(this.tempFolder + this.filesManager.dirSep() + 't1')).toBe(true);
        expect(this.filesManager.createDirectory(this.tempFolder + this.filesManager.dirSep() + 't2')).toBe(true);
        
        let t1 = new TerminalManager(this.tempFolder + this.filesManager.dirSep() + 't1');
        let t2 = new TerminalManager(this.tempFolder + this.filesManager.dirSep() + 't2', false);
        
        expect(path.resolve(process.cwd())).toBe(this.tempFolder + this.filesManager.dirSep() + 't1');
        
        t2.setWorkDir(this.initialDir);        
        expect(path.resolve(process.cwd())).toBe(this.tempFolder + this.filesManager.dirSep() + 't1');
        
        t1.setWorkDir(this.tempFolder + this.filesManager.dirSep() + 't2');        
        expect(path.resolve(process.cwd())).toBe(this.tempFolder + this.filesManager.dirSep() + 't2');
    });
    
    
    it('should have a correctly implemented setWorkDir method', function() {

        process.chdir(this.initialDir);
        this.sut = new TerminalManager();
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        // Test empty values
        expect(() => {
            this.sut.setWorkDir(null);
        }).toThrowError(Error, /path must be a string/);
        
        expect(() => {
            this.sut.setWorkDir(0);
        }).toThrowError(Error, /path must be a string/);
        
        expect(this.sut.setWorkDir('')).toBe(this.initialDir);
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        expect(() => {
            this.sut.setWorkDir('                    ');
        }).toThrowError(Error, /Invalid path:       /);

        // Test ok values
        expect(this.filesManager.createDirectory(this.tempFolder + '/a/b/c/d', true)).toBe(true);
        expect(path.resolve(this.sut.setWorkDir(this.tempFolder + '/a/b/c/d'))).toBe(path.resolve(this.tempFolder + '/a/b/c/d'));
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a/b/c/d'));
        
        expect(path.resolve(this.sut.setWorkDir(this.tempFolder + '/a/b'))).toBe(path.resolve(this.tempFolder + '/a/b'));
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a/b'));
        
        // Test exceptions
        // Test wrong values
        expect(() => {
            this.sut.setWorkDir('thisfolderdoesnotexist');
        }).toThrowError(Error, /Invalid path: thisfolderdoesnotexist/);
        
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a/b'));
    });
    
    
    it('should have a correctly implemented setWorkDir method when linkSystemWorkDir is set to false', function() {

        process.chdir(this.initialDir);
        this.sut = new TerminalManager('', false);
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        // Test empty values
        expect(() => {
            this.sut.setWorkDir(null);
        }).toThrowError(Error, /path must be a string/);
        
        expect(() => {
            this.sut.setWorkDir(0);
        }).toThrowError(Error, /path must be a string/);
        
        expect(this.sut.setWorkDir('')).toBe(this.initialDir);
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        expect(() => {
            this.sut.setWorkDir('                    ');
        }).toThrowError(Error, /Invalid path:       /);

        // Test ok values
        expect(this.sut.setWorkDir(this.tempFolder)).toBe(this.tempFolder);
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        expect(this.filesManager.createDirectory(this.tempFolder + '/a/b/c/d', true)).toBe(true);
        expect(path.resolve(this.sut.setWorkDir(this.tempFolder + '/a/b/c/d'))).toBe(path.resolve(this.tempFolder + '/a/b/c/d'));
        expect(path.resolve(process.cwd())).toBe(this.initialDir);

        expect(path.resolve(this.sut.setWorkDir(this.tempFolder + '/a/b'))).toBe(path.resolve(this.tempFolder + '/a/b'));
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        // Test exceptions
        // Test wrong values
        expect(() => {
            this.sut.setWorkDir('thisfolderdoesnotexist');
        }).toThrowError(Error, /Invalid path: thisfolderdoesnotexist/);
        
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
    });
        
    
    it('should have a correctly implemented getWorkDir method', function() {

        // Test empty values
        expect(this.sut.getWorkDir()).toBe(this.tempFolder);
        
        // Test ok values
        expect(this.filesManager.createDirectory(this.tempFolder + '/a/b/c/d', true)).toBe(true);
        expect(path.resolve(this.sut.setWorkDir(this.tempFolder + '/a/b/c/d'))).toBe(path.resolve(this.tempFolder + '/a/b/c/d'));
        expect(this.sut.getWorkDir()).toBe(path.resolve(this.tempFolder + '/a/b/c/d'));

        expect(path.resolve(this.sut.setWorkDir(this.tempFolder + '/a/b'))).toBe(path.resolve(this.tempFolder + '/a/b'));
        expect(this.sut.getWorkDir()).toBe(path.resolve(this.tempFolder + '/a/b'));

        // Test wrong values
        // Test exceptions
        // Not necessary     
    });
    
    
    it('should have a correctly implemented setPreviousWorkDir method', function() {

        process.chdir(this.initialDir);
        this.sut = new TerminalManager();
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        // Test empty values
        // Not necessary

        // Test ok values
        expect(this.filesManager.createDirectory(this.tempFolder + '/a/b/c/d', true)).toBe(true);
        this.sut.setWorkDir(this.tempFolder + '/a');
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a'));
        this.sut.setWorkDir(this.tempFolder + '/a/b');
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a/b'));
        this.sut.setWorkDir(this.tempFolder + '/a/b/c');
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a/b/c'));
        this.sut.setWorkDir(this.tempFolder + '/a/b/c/d');
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a/b/c/d'));
        
        expect(path.resolve(this.sut.setPreviousWorkDir())).toBe(path.resolve(this.tempFolder + '/a/b/c'));
        expect(path.resolve(this.sut.setPreviousWorkDir())).toBe(path.resolve(this.tempFolder + '/a/b'));
        expect(path.resolve(this.sut.setPreviousWorkDir())).toBe(path.resolve(this.tempFolder + '/a'));
        expect(path.resolve(this.sut.setPreviousWorkDir())).toBe(path.resolve(this.initialDir));
        
        // Test wrong values
        // Test exceptions
        expect(() => {
            this.sut.setPreviousWorkDir();
        }).toThrowError(Error, /Requesting previous work dir but none available/);
    });
    
    
    it('should have a correctly implemented setInitialWorkDir method', function() {

        process.chdir(this.initialDir);
        this.sut = new TerminalManager();
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        // Test empty values
        // Not necessary

        // Test ok values
        expect(this.filesManager.createDirectory(this.tempFolder + '/a/b/c/d', true)).toBe(true);
        this.sut.setWorkDir(this.tempFolder + '/a');
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a'));
        this.sut.setWorkDir(this.tempFolder + '/a/b');
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a/b'));
        this.sut.setWorkDir(this.tempFolder + '/a/b/c');
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a/b/c'));
        
        expect(path.resolve(this.sut.setInitialWorkDir())).toBe(path.resolve(this.initialDir));
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.initialDir));
        expect(path.resolve(this.sut.setInitialWorkDir())).toBe(path.resolve(this.initialDir));
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.initialDir));
        
        expect(() => {
            this.sut.setPreviousWorkDir();
        }).toThrowError(Error, /Requesting previous work dir but none available/); 
        
        this.sut = new TerminalManager(this.tempFolder);
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder));
        this.sut.setWorkDir(this.tempFolder + '/a/b');
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a/b'));
        this.sut.setWorkDir(this.tempFolder + '/a/b/c/d');
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder + '/a/b/c/d'));
        
        expect(path.resolve(this.sut.setInitialWorkDir())).toBe(path.resolve(this.tempFolder));
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder));
        expect(path.resolve(this.sut.setInitialWorkDir())).toBe(path.resolve(this.tempFolder));
        expect(path.resolve(process.cwd())).toBe(path.resolve(this.tempFolder));
        
        // Test wrong values
        // Test exceptions
        expect(() => {
            this.sut.setPreviousWorkDir();
        }).toThrowError(Error, /Requesting previous work dir but none available/);     
    });
    
    
    it('should have a correctly implemented setInitialWorkDir method when linkSystemWorkDir is set to false', function() {

        process.chdir(this.initialDir);
        this.sut = new TerminalManager('', false);
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        // Test empty values
        // Not necessary

        // Test ok values
        expect(this.filesManager.createDirectory(this.tempFolder + '/a/b/c/d', true)).toBe(true);
        this.sut.setWorkDir(this.tempFolder + '/a');
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        this.sut.setWorkDir(this.tempFolder + '/a/b');
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        this.sut.setWorkDir(this.tempFolder + '/a/b/c');
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        expect(path.resolve(this.sut.setInitialWorkDir())).toBe(path.resolve(this.initialDir));
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        expect(path.resolve(this.sut.setInitialWorkDir())).toBe(path.resolve(this.initialDir));
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        expect(() => {
            this.sut.setPreviousWorkDir();
        }).toThrowError(Error, /Requesting previous work dir but none available/); 
        
        this.sut = new TerminalManager(this.tempFolder, false);
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        this.sut.setWorkDir(this.tempFolder + '/a/b');
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        this.sut.setWorkDir(this.tempFolder + '/a/b/c/d');
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        expect(path.resolve(this.sut.setInitialWorkDir())).toBe(path.resolve(this.tempFolder));
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        expect(path.resolve(this.sut.setInitialWorkDir())).toBe(path.resolve(this.tempFolder));
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        // Test wrong values
        // Test exceptions
        expect(() => {
            this.sut.setPreviousWorkDir();
        }).toThrowError(Error, /Requesting previous work dir but none available/);      
    });
    
    
    it('should have a correctly implemented createTempDirectory method', function() {

        process.chdir(this.initialDir);
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        expect(this.sut.getWorkDir()).toBe(this.tempFolder);

        // Test empty values
        expect(() => {
            this.sut.createTempDirectory(null, null);
        }).toThrowError(Error, /desiredName must be a string/);
        
        expect(() => {
            this.sut.createTempDirectory(0, 0);
        }).toThrowError(Error, /desiredName must be a string/);
        
        expect(() => {
            this.sut.createTempDirectory('', '');
        }).toThrowError(Error, /setWorkDirToIt must be a boolean value/);
        
        // Test ok values
        let tempDir = this.sut.createTempDirectory('desired-directory-name', false);
        expect(tempDir).toContain(this.filesManager.getOSTempDirectory());
        expect(tempDir).toContain('desired-directory-name');
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        expect(this.sut.getWorkDir()).toBe(this.tempFolder);
        
        tempDir = this.sut.createTempDirectory('another-directory-name');
        expect(tempDir).toContain(this.filesManager.getOSTempDirectory());
        expect(tempDir).toContain('another-directory-name');
        expect(tempDir).not.toContain('desired-directory-name');
        expect(path.resolve(process.cwd())).toBe(tempDir);
        expect(this.sut.getWorkDir()).toBe(tempDir);
        
        // Test wrong values
        // Test exceptions
        expect(() => { this.sut.createTempDirectory('invalid?chars*'); }).toThrowError(Error, /invalid argument/);        
    });
    
    
    it('should have a correctly implemented createTempDirectory method when linkSystemWorkDir is set to false', function() {

        process.chdir(this.initialDir);
        this.sut = new TerminalManager('', false);
        expect(path.resolve(process.cwd())).toBe(this.initialDir);

        // Test empty values
        expect(() => {
            this.sut.createTempDirectory(null, null);
        }).toThrowError(Error, /desiredName must be a string/);
        
        expect(() => {
            this.sut.createTempDirectory(0, 0);
        }).toThrowError(Error, /desiredName must be a string/);
        
        expect(() => {
            this.sut.createTempDirectory('', '');
        }).toThrowError(Error, /setWorkDirToIt must be a boolean value/);
        
        // Test ok values
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        
        let tempDir = this.sut.createTempDirectory('desired-directory-name', false);
        expect(tempDir).toContain(this.filesManager.getOSTempDirectory());
        expect(tempDir).toContain('desired-directory-name');
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        expect(this.sut.getWorkDir()).toBe(this.initialDir);
        
        tempDir = this.sut.createTempDirectory('another-directory-name');
        expect(tempDir).toContain(this.filesManager.getOSTempDirectory());
        expect(tempDir).toContain('another-directory-name');
        expect(tempDir).not.toContain('desired-directory-name');
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
        expect(this.sut.getWorkDir()).toBe(tempDir);
        
        // Test wrong values
        // Test exceptions
        expect(() => { this.sut.createTempDirectory('invalid?chars*'); }).toThrowError(Error, /invalid argument/); 
        
        expect(path.resolve(process.cwd())).toBe(this.initialDir);
    });
    
    
    it('should have a correctly implemented exec method', function() {

        // Test empty values
        expect(() => {
            this.sut.exec(null, null);
        }).toThrowError(Error, /command must be a string/);
        
        expect(() => {
            this.sut.exec(0, 0);
        }).toThrowError(Error, /command must be a string/);
        
        expect(() => {
            this.sut.exec('', '');
        }).toThrowError(Error, /no command to execute/);
        // TODO

        // Test ok values
        let execResult = this.sut.exec('node -v');
        expect(execResult.failed).toBe(false);
        expect(execResult.output).toMatch(/[0-9][0-9]\.[0-9][0-9]/);
        
        this.sut.baseCommand = 'node';
        execResult = this.sut.exec('-v');
        expect(execResult.failed).toBe(false);
        expect(execResult.output).toMatch(/[0-9][0-9]\.[0-9][0-9]/);

        execResult = this.sut.exec('-h');
        expect(execResult.failed).toBe(false);
        expect(execResult.output).toContain('NODE_PATH');

        // Test wrong values
        // Test exceptions
        this.sut.baseCommand = '';
        execResult = this.sut.exec('?????');
        expect(execResult.failed).toBe(true);
        expect(execResult.output).toContain('?????');
         
        execResult = this.sut.exec('node -r 345345');
        expect(execResult.failed).toBe(true);
        expect(execResult.output).toContain("Cannot find module '345345'"); 
    });
    
});