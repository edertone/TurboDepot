/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */


declare let console: any;
declare let process: any;


/**
 * ConsoleManager class
 *
 * @see constructor()
 */
export class ConsoleManager {
    
    
    /** 
     * Defines the color to be used on the title styled console texts
     */
    colorTitle = '\x1b[36m%s\x1b[0m';
    
    
    /** 
     * Defines the color to be used on the success styled console texts
     */
    colorSuccess = '\x1b[32m%s\x1b[0m';
    
    
    /** 
     * Defines the color to be used on the warning styled console texts
     */
    colorWarning = '\x1b[33m%s\x1b[0m';
    
    
    /** 
     * Defines the color to be used on the error styled console texts
     */
    colorError = '\x1b[31m%s\x1b[0m';
    
    
    /**
     * An improved console class that implements useful methods for the console output
     * 
     * @return A ConsoleManager instance
     */
    constructor() {
        
    }
    
    
    /**
     * Show a standard message to the user with the default console raw text style
     */
    text(message:string) {
        
        console.log(message);
    }
    
    
    /**
     * Show a title message to the user
     */
    title(message:string) {
        
        console.log(this.colorTitle, message);
    }
    
    
    /**
     * Show a success to the user
     * If quit parameter is true, the application will also exit with success code 0 (which means exit without error)
     */
    success(message: string, quit = false) {
        
        console.log(this.colorSuccess, message);
        
        if(quit){
            
            process.exit(0);
        }
    }
    
    
    /**
     * Show a warning to the user
     * If quit parameter is true, the application will also exit with error code 1 (which means exit with error)
     */
    warning(message:string, quit = false) {
        
        console.log(this.colorWarning, message);
        
        if(quit){
            
            process.exit(1);
        }
    }
    
    
    /**
     * Show a multiple list of warnings to the user, one after the other on a new line.
     * 
     * @param messages The list of texts to show as warnings
     * @param quit False by default, the application will die with error code 1 (which means exit with error) after the last warning is displayed 
     * 
     * @return void
     */
    warnings(messages:string[], quit = false) {
        
        if(messages.length > 0){

            for(const element of messages){
                
                console.log(this.colorWarning, element);
            }
            
            if(quit){
                
                process.exit(1);
            }
        }    
    }
    
    
    /**
     * Show an error to the user
     *
     * @param message The text to show as an error
     * @param quit True by default, the application will die when the error message is shown with error code 1 (which means exit with error)
     * 
     * @return void
     */
    error(message:string, quit = true) {
        
        console.log(this.colorError, message);
        
        if(quit){
            
            process.exit(1);
        }
    }
    
    
    /**
     * Show a multiple list of errors to the user, one after the other on a new line.
     * 
     * @param messages The list of texts to show as errors
     * @param quit True by default, the application will die with error code 1 (which means exit with error) after the last error is displayed 
     * 
     * @return void
     */
    errors(messages:string[], quit = true) {
        
        if(messages.length > 0){

            for(const element of messages){
                            
                console.log(this.colorError, element);
            }
            
            if(quit){
                
                process.exit(1);
            }
        }    
    }
}
