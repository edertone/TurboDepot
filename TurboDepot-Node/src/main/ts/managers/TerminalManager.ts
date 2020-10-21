/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */


import { StringUtils } from 'turbocommons-ts';
import { FilesManager } from './FilesManager';

declare let process: any;
declare function require(name: string): any;


/**
 * TerminalManager class
 *
 * @see constructor()
 */
export class TerminalManager {
    
    
    /**
     * see class constructor for docs
     */
    private _linkSystemWorkDir = true;

    
    /**
     * If this value is set, all the executed terminal commands will be appended to it.
     * For example, if we defined baseCommand = 'copy' and we call exec('-a c:/tmp'), the effectively executed command
     * will be 'copy -a c:/tmp'
     */
    baseCommand = '';
    
    
    /**
     * The list of workdir values that have been defined since the class was constructed.
     * We will be able to navigate this back at any time
     */
    private workDirHistory:string[] = [];
    
    
    /**
     * A files manager instance used by this class
     */
    private filesManager: FilesManager;

    
    /**
     * Stores the NodeJs path instance
     */
    private path: any;

    
    /**
     * Stores the NodeJs execSync instance
     */
    private execSync: any;
    
    
    /**
     * Class that helps with the process of interacting with command line applications and executions through the OS terminal.
     * 
     * @param workDir Defines the directory where the class points which will be the base path for all the executed commands. If not specified,
     *        The current system work directory will be used. Note that if we specify a work dir that is different than the main application one and
     *        linkSystemWorkDir is true, both work directories will be automatically set to the same value.
     *        
     * @param linkSystemWorkDir If set to true, any change that is performed on this class workDir will be reflected to the active application work dir.
     *        If set to false, this class will handle a totally independent workdir and the main application will have its own one that won't change when this
     *        class one is modified. It is true by default
     * 
     * @return A TerminalManager instance
     */
    constructor(workDir: string = '', linkSystemWorkDir = true) {

        if(!StringUtils.isString(workDir)){
            
            throw new Error('workDir must be a string');
        }
        
        this.path = require('path');
        this.execSync = require('child_process').execSync;
        this._linkSystemWorkDir = linkSystemWorkDir;
        
        workDir = workDir === '' ? this.path.resolve('./') : this.path.resolve(workDir);
        
        this.filesManager = new FilesManager(workDir);
        
        if(this._linkSystemWorkDir) {
            
            process.chdir(workDir);
        }
        
        this.workDirHistory = [workDir];
    }
    
    
    /**
     * Move the current terminal working directory to the specified path.
     * If linkSystemWorkDir flag has been enabled, the main application work dir will also be pointed to the same path.
     * 
     * @param path A full file system route to the location where the subsequent terminal commands will be executed
     * 
     * @return The new working directory full path
     */
    setWorkDir(path: string) {
    
        if(!this.filesManager.isDirectory(path)){
            
            throw new Error('Invalid path: ' + path);
        }
        
        path = this.path.resolve(path);
        
        if(this._linkSystemWorkDir) {
            
            process.chdir(path);
        }
        
        // TODO - filesmanager must have a setRootPath method
        this.filesManager = new FilesManager(path);
        
        this.workDirHistory.push(path);
        
        return path;
    }
    
    
    /**
     * Get the directory that is currently being used by this class as the base path for commands execution
     */
    getWorkDir(){
        
        return this.workDirHistory[this.workDirHistory.length - 1];
    }
    
    
    /**
     * Move the current work dir one step backward to the one that was previously defined.
     */
    setPreviousWorkDir(){
        
        if(this.workDirHistory.length <= 1){
            
            throw new Error('Requesting previous work dir but none available');
        }
        
        if(this._linkSystemWorkDir) {
        
            process.chdir(this.workDirHistory[this.workDirHistory.length - 1]);
        }
        
        this.workDirHistory.pop();
        
        return this.getWorkDir();
    }
    
    
    /**
     * Move the current work dir to the first one that was defined when this class was created
     */
    setInitialWorkDir(){
        
        this.workDirHistory = [this.workDirHistory[0]];
        
        if(this._linkSystemWorkDir) {

            process.chdir(this.workDirHistory[0]);
        }
        
        return this.workDirHistory[0];
    }
    
    
    /**
     * Create a new temporary directory on the temporary files location defined by the OS. If folder does not exist,
     * it will be created.
     * 
     * When the current application exits, the folder will be automatically deleted (if possible).
     * 
     * @param desiredName see FilesManager.createTempDirectory() method
     * @param setWorkDirToIt If set to true, when the new temporary folder is created it will be defined as the
     *        current active terminal working directory. It is true by default
     *        
     * @param deleteOnExecutionEnd see FilesManager.createTempDirectory() method
     * 
     * @return The full path to the newly created temporary directory
     */
    createTempDirectory(desiredName: string, setWorkDirToIt = true, deleteOnExecutionEnd = true) {
    
        let tmp = this.filesManager.createTempDirectory(desiredName, deleteOnExecutionEnd);
        
        if(typeof (setWorkDirToIt) !== 'boolean'){
            
            throw new Error('setWorkDirToIt must be a boolean value');
        }
        
        if(setWorkDirToIt){
            
            this.setWorkDir(tmp);
        }
        
        return tmp;
    }
    
    
    /**
     * Execute an arbitrary terminal cmd command on the currently active work directory and return all relevant data
     * 
     * @param command Some cmd operation to execute on the current working directory
     * @param liveOutput (false by default) Set it to true to show the execution stdout in real time on the main console 
     * 
     * @return An object with two properties:
     *         - failed: False if the command finished successfully, true if any error happened
     *         - output: The full terminal output that was generated by the executed command or the full error message if the execution failed
     */
    exec(command: string, liveOutput = false) {
    
        if(!StringUtils.isString(command)){
        
            throw new Error('command must be a string');
        }
        
        let output = '';
        let failed = false;        
        let finalCommand = StringUtils.isEmpty(this.baseCommand) ? command : this.baseCommand + ' ' + command;
        
        if(StringUtils.isEmpty(finalCommand)){
            
            throw new Error('no command to execute');
        }
        
        // Aux method that tries to capture the command error
        let captureError = (e:any) => {
            
            let errorMessage = '';
            
            if(e.stderr && !StringUtils.isEmpty(e.stderr.toString())){
                
                errorMessage = e.stderr.toString();
            }
            
            if(e.stdout && !StringUtils.isEmpty(e.stdout.toString())){
                
                errorMessage += '\n\n' + e.stdout.toString();
            }
            
            if(StringUtils.isEmpty(errorMessage)){
                
                errorMessage = e.toString();
            }
                
            return errorMessage;
        };
        
        if(liveOutput){
            
            try{
                
                output = this.execSync(finalCommand, {stdio:[0,1,2]});
                
            }catch(e){

                failed = true;
                output = captureError(e);
            }
            
        }else{
            
            try{
                
                output = this.execSync(finalCommand, {stdio : 'pipe'}).toString();
                
            }catch(e){
                
                failed = true;                
                output = captureError(e);
            }
        }
        
        return {
            failed: failed,
            output: output
        };
    }
}