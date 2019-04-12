/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */
 

/**
 * ConsoleManager class
 *
 * @see constructor()
 */
export class ConsoleManager {
    
    
    /**
     * An improved console class that implements useful methods for the console output
     * 
     * This constructor requires some node modules to work, which are passed as dependencies
     *  
     * @param console An instance for the console process node object
     * @param process An instance for the global process node object
     * 
     * @return A ConsoleManager instance
     */
    constructor(private console:any,
                private process:any) {
        
    }
    
    
    /**
     * Show a standard message to the user
     */
    log(message:string) {
        
        this.console.log(message);
    }
    
    
    /**
     * Show a success to the user
     * If quit parameter is true, the application will also exit with success code 0 (which means exit without error)
     */
    success(message: string, quit = false) {
        
        this.console.log('\x1b[32m%s\x1b[0m', message);
        
        if(quit){
            
            this.process.exit(0);
        }
    }
    
    
    /**
     * Show a warning to the user
     * If quit parameter is true, the application will also exit with error code 1 (which means exit with error)
     */
    warning(message:string, quit = false) {
        
        this.console.log('\x1b[33m%s\x1b[0m', message);
        
        if(quit){
            
            this.process.exit(1);
        }
    }
    
    
    /**
     * Show a multiple list of warnings to the user
     * If quit parameter is true, the application will also exit with error code 1 after all errors are output (which
     * means exit with error)
     */
    warnings(messages:string[], quit = false) {
        
        if(messages.length > 0){

            for(let i = 0; i < messages.length; i++){
                
                this.console.log('\x1b[33m%s\x1b[0m', messages[i]);
            }
            
            if(quit){
                
                this.process.exit(1);
            }
        }    
    }
    
    
    /**
     * Show an error to the user
     * If quit parameter is true, the application will also die with error code 1 (which means exit with error)
     */
    error(message:string, quit = false) {
        
        this.console.log('\x1b[31m%s\x1b[0m', message);
        
        if(quit){
            
            this.process.exit(1);
        }
    }
    
    
    /**
     * Show a multiple list of errors to the user
     * If quit parameter is true, the application will also exit with error code 1 after all errors are output (which
     * means exit with error)
     */
    errors(messages:string[], quit = false) {
        
        if(messages.length > 0){

            for(let i = 0; i < messages.length; i++){
                
                this.console.log('\x1b[31m%s\x1b[0m', messages[i]);
            }
            
            if(quit){
                
                this.process.exit(1);
            }
        }    
    }
}
