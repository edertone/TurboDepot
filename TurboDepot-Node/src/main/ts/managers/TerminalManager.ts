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


/**
 * TerminalManager class
 *
 * @see constructor()
 */
export class TerminalManager {
    
    
    /**
     * If this value is set, all the executed terminal commands will be appended to it.
     * For example, if we defined baseCommand = 'copy' and we call exec('-a c:/tmp'), the effectively executed command
     * will be 'copy -a c:/tmp'
     */
    baseCommand = '';
    
    
    /**
     * A files manager instance used by this class
     */
    private filesManager: FilesManager;

    
    /**
     * Class that helps with the process of testing command line applications and executions through the OS terminal
     * 
     * This constructor requires some node modules to work, which are passed as dependencies
     *  
     * @param execSync A node execSync module instance (const { execSync } = require('child_process');)
     * @param process An instance for the global process node object
     * @param fs A node fs module instance (const fs = require('fs'))
     * @param os A node os module instance (const os = require('os'))
     * @param path A node path module instance (const path = require('path'))
     * @param crypto A node crypto module instance (const crypto = require('crypto'))
     * 
     * @return A TerminalManager instance
     */
    constructor(private execSync:any,
                private process:any,
                fs:any,
                os:any,
                path:any,
                crypto: any) {

        this.filesManager = new FilesManager(fs, os, path, process, crypto);
    }
    
    
    /**
     * Move the current terminal working directory to the specified path
     * 
     * @param path A full file system route to the location where the subsequent terminal commands must be executed
     * 
     * @return The new working directory full path
     */
    switchWorkDirTo(path: string) {
    
        this.process.chdir(path);
        
        return path;
    }
    
    
    /**
     * Create a new temporary directory on the temporary files location defined by the OS. If folder does not exist,
     * it will be created.
     * 
     * When the current application exits, the folder will be automatically deleted (if possible).
     * 
     * @param desiredName A name we want for the new directory to be created. If name is not available, a unique one
     *        (based on the provided desired name) will be generated automatically.
     * @param switchWorkDirToIt If set to true, when the new temporary folder is created, it will be defined as the
     *        current terminal working directory.
     * 
     * @return The full path to the newly created temporary directory
     */
    createTempDirectory(desiredName: string, switchWorkDirToIt = true) {
    
        let tmp = this.filesManager.createTempDirectory(desiredName);
        
        if(switchWorkDirToIt){
            
            this.switchWorkDirTo(tmp);
        }
        
        return tmp;
    }
    
    
    /**
     * Execute an arbitrary terminal cmd command on the currently active work directory and capture all of its console
     * output.
     * 
     * This method does not show any command output on the main console
     * 
     * @param command Some cmd operation to execute on the current working directory
     * 
     * @return The full terminal output that was generated for the given command
     */
    exec(command: string) {
        
        let finalCommand = command;
        
        if(!StringUtils.isEmpty(this.baseCommand)){
            
            finalCommand += ' ' + this.baseCommand;
        }
        
        try{
            
            return this.execSync(finalCommand, {stdio : 'pipe'}).toString();
            
        }catch(e){
            
            if(!StringUtils.isEmpty(e.stderr.toString())){
                
                return e.stderr.toString();
            }
            
            return e.stdout.toString();
        }  
    }
    
    
    /**
     * Execute an arbitrary terminal cmd command on the currently active work directory, and show the command
     * output in real time on the console.
     * 
     * @param command Some cmd operation to execute on the current working directory
     * 
     * @return True if the command was successfully executed, false if not
     */
    execLive(command: string) {
        
        let finalCommand = command;
        
        if(!StringUtils.isEmpty(this.baseCommand)){
            
            finalCommand += ' ' + this.baseCommand;
        }
        
        try{
            
            this.execSync(finalCommand, {stdio:[0,1,2]});
            
            return true;
            
        }catch(e){

            return false;
        }
    }
}