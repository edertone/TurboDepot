/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */
 

import { StringUtils, ArrayUtils } from 'turbocommons-ts';

declare let process: any;
declare function require(name: string): any;


/**
 * Files manager class
 */
export class FilesManager{

    
    /**
     * @see FilesManager.constructor
     */
    private _rootPath = '';
    
    
    /**
     * Stores the NodeJs fs instance
     */
    private fs: any;
    
    
    /**
     * Stores the NodeJs os instance
     */
    private os: any;
    
    
    /**
     * Stores the NodeJs path instance
     */
    private path: any;
    
    
    /**
     * Stores the NodeJs crypto instance
     */
    private crypto: any;
    
    
    /**
     * Manager class that contains the most common file system interaction functionalities
     * 
     * @param rootPath If we want to use an existing directory as the base path for all the methods on this class, we can define here
     *        a full OS filesystem path to it. Setting this value means all the file operations will be based on that directory.
     * 
     * @return A FilesManager instance
     */
    constructor(rootPath = '') {
        
        this.fs = require('fs');
        this.os = require('os');
        this.path = require('path');
        this.crypto = require('crypto');

        if (!StringUtils.isString(rootPath)){

            throw new Error('rootPath must be a string');
        }
        
        this._rootPath = StringUtils.formatPath(rootPath);

        if(this._rootPath !== '' && !this.isDirectory(this._rootPath)){

            throw new Error('Specified rootPath does not exist: ' + rootPath);
        }
    }
    

    /**
     * Gives us the current OS directory separator character, so we can build cross platform file paths
     *
     * @return The current OS directory separator character
     */
    dirSep(){

        return this.path.sep;
    }
    
    
    /**
     * Tells if the provided string represents a relative or absolute file system path (Windows or Linux).
     *
     * Note that this method doesn't check if the path is valid or points to an existing file or directory.
     *
     * @return True if the provided path is absolute, false if it is relative
     */
    isPathAbsolute(path: string){

        if (StringUtils.isString(path)){
            
            let len = path.length;
            let startsWithAlpha = len > 0 && /^[a-zA-Z]+$/.test(path.charAt(0));

            return (path.indexOf('/') === 0 || path.indexOf('\\') === 0) ||
                (len === 2 && startsWithAlpha && ':' === path.charAt(1)) ||
                (len > 2 && startsWithAlpha && ':' === path.charAt(1) && (path.indexOf('/') === 2 || path.indexOf('\\') === 2));
        }

        throw new Error('path must be a string');
    }
    
    
    /**
     * Check if the specified path is a file or not.
     *
     * @param path An Operating system absolute or relative path to test
     *
     * @return true if the path exists and is a file, false otherwise.
     */
    isFile(path: string){
        
        if (!StringUtils.isString(path)){

            throw new Error('path must be a string');
        }
        
        try {
            
            return this.fs.lstatSync(this._composePath(path)).isFile();
            
        } catch (e) {

            return false;
        }
    }
    
    
    /**
     * Check if two provided files are identical
     *
     * @param pathToFile1 Absolute or relative path to the first file to compare
     * @param pathToFile2 Absolute or relative path to the second file to compare
     *
     * @throws Error
     *
     * @return True if both files are identical, false otherwise
     */
    isFileEqualTo(pathToFile1: string, pathToFile2: string){

        pathToFile1 = this._composePath(pathToFile1, false, true);
        pathToFile2 = this._composePath(pathToFile2, false, true);

        if (this.getFileSize(pathToFile1) === this.getFileSize(pathToFile2)){

            // TODO - This method (hashes) is not 100% effective and has to loop through all the file, so is less performant.
            // We must use the same method as the PHP version is using now
            let file1Hash = this.crypto.createHash('md5').update(this.readFile(pathToFile1), 'utf8').digest('hex');
            let file2Hash = this.crypto.createHash('md5').update(this.readFile(pathToFile2), 'utf8').digest('hex');
            
            return file1Hash === file2Hash;
        }

        return false;
    }
    
    
    /**
     * Check if the specified path is a directory or not.
     *
     * @param path An Operating system absolute or relative path to test
     *
     * @return true if the path exists and is a directory, false otherwise.
     */
    isDirectory(path: any){

        if (!StringUtils.isString(path)){

            throw new Error('path must be a string');
        }
        
        path = this._composePath(path);
        
        if(StringUtils.isEmpty(path)){
            
            return false;
        }
        
        try {
            
            return this.fs.lstatSync(this.fs.realpathSync(path)).isDirectory();
            
        } catch (e) {

            return false;
        }
    }
    
    
    /**
     * Check if two directories contain exactly the same folder structure and files.
     *
     * @param path1 Absolute or relative path to the first directory to compare
     * @param path2 Absolute or relative path to the second directory to compare
     *
     * @return true if both paths are valid directories and contain exactly the same files and folders tree.
     */
    isDirectoryEqualTo(path1: string, path2: string){

        path1 = this._composePath(path1);
        path2 = this._composePath(path2);

        let path1Items = this.getDirectoryList(path1, 'nameAsc');
        let path2Items = this.getDirectoryList(path2, 'nameAsc');

        // Both paths must be exactly the same
        if(!ArrayUtils.isEqualTo(path1Items, path2Items)){

            return false;
        }

        for (let i = 0; i < path1Items.length; i++) {

            let item1Path = path1 + this.dirSep() + path1Items[i];
            let item2Path = path2 + this.dirSep() + path2Items[i];
            let isItem1ADir = this.isDirectory(item1Path);

            if(isItem1ADir && !this.isDirectoryEqualTo(item1Path, item2Path)){

                return false;
            }

            if(!isItem1ADir && !this.isFileEqualTo(item1Path, item2Path)){

                return false;
            }
        }

        return true;
    }
    
    
    /**
     * Checks if the specified folder is empty
     *
     * @param path Absolute or relative path to the directory we want to check
     *
     * @return True if directory is empty, false if not. If it does not exist or cannot be read, an exception will be generated
     */
    isDirectoryEmpty(path: string) {

        return this.getDirectoryList(path).length <= 0;
    }
    
    
    /**
     * Count elements on the specified directory based on their type or specific match with regular expressions.
     * With this method you can count files, directories, both or any items that match more complex regular expressions.
     *
     * @see FilesManager.findDirectoryItems
     *
     * @param path Absolute or relative path where the counting will be performed
     *
     * @param searchItemsType Defines the type for the directory elements to count: 'files' to count only files, 'folders'
     *        to count only folders, 'both' to count on all the directory contents
     *
     * @param depth Defines the maximum number of subfolders where the count will be performed:<br>
     *        - If set to -1 the count will be performed on the whole folder contents<br>
     *        - If set to 0 the count will be performed only on the path root elements<br>
     *        - If set to 2 the count will be performed on the root, first and second depth level of subfolders
     *
     * @param searchRegexp A regular expression that files or folders must match to be included
     *        into the results. See findDirectoryItems() docs for pattern examples<br>
     *
     * @param excludeRegexp A regular expression that will exclude all the results that match it from the count
     *
     * @return The total number of elements that match the specified criteria inside the specified path
     */
    countDirectoryItems(path: string,
                        searchItemsType = 'both',
                        depth = -1,
                        searchRegexp = /.*/,
                        excludeRegexp = ''){

        return this.findDirectoryItems(path, searchRegexp, 'relative', searchItemsType, depth, excludeRegexp).length;
    }
    
    
    /**
     * Find all the elements on a directory that match a specific regexp pattern
     *
     * @param path Absolute or relative path where the search will be performed
     *
     * @param searchRegexp A regular expression that files or folders must match to be included
     *        into the results (Note that search is dependant on the searchMode parameter to search only in the item name or the full path).
     *        Here are some useful patterns:<br>
     *        /.*\.txt$/i - Match all items which end with '.txt' (case insensitive)<br>
     *        /^some.*./ - Match all items which start with 'some'<br>
     *        /text/ - Match all items which contain 'text'<br>
     *        /^file\.txt$/ - Match all items which are exactly 'file.txt'<br>
     *        /^.*\.(jpg|jpeg|png|gif)$/i - Match all items which end with .jpg,.jpeg,.png or .gif (case insensitive)<br>
     *        /^(?!.*\.(jpg|png|gif)$)/i - Match all items that do NOT end with .jpg, .png or .gif (case insensitive)
     *
     * @param returnFormat Defines how the array of results will be returned. 4 values are possible:<br>
     *        'relative' - Each result element will contain the path relative to the search root including the file (with extension) or folder name<br>
     *        'absolute' - Each result element will contain the full OS absolute path including the file (with extension) or folder name<br>
     *        'name' - Each result element will contain its file (with extension) or folder name<br>
     *        'name-noext' - Each result element will contain its file (without extension) or folder name
     *
     * @param searchItemsType Defines the type for the directory elements to search: 'files' to search only files, 'folders'
     *        to search only folders, 'both' to search on all the directory contents
     *
     * @param depth Defines the maximum number of subfolders where the search will be performed:<br>
     *        - If set to -1 (default) the search will be performed on the whole folder contents<br>
     *        - If set to 0 the search will be performed only on the path root elements<br>
     *        - If set to N the search will be performed on the root, first and N depth level of subfolders
     *
     * @param excludeRegexp A regular expression that will exclude all the results that match when tested against the item full OS absolute path
     *
     * @param searchMode Defines how searchRegexp will be used to find matches:
     *        - If set to 'name' (default) The regexp will be tested only against the file or folder name<br>
     *        - If set to 'absolute' The regexp will be tested against the full OS absolute path of the file or folder<br>
     *
     * @return A list formatted as defined in returnFormat, with all the elements that meet the search criteria, sorted ascending
     */
    findDirectoryItems(path: string,
                       searchRegexp: string|RegExp,
                       returnFormat = 'relative',
                       searchItemsType = 'both',
                       depth = -1,
                       excludeRegexp: string|RegExp = '',
                       searchMode = 'name'): string[]{

        path = this._composePath(path, true);
        
        const result: string[] = [];        
        const stack: [string, number][] = [[path, 0]];
        const searchRegex = new RegExp(searchRegexp);
        const excludeRegex = excludeRegexp ? new RegExp(excludeRegexp) : null;

        // Depth-first search using a stack
        while(stack.length > 0){
            
            const [currentPath, currentDepth] = stack.pop()!;

            // Check if we've reached the maximum depth
            if(depth !== -1 && currentDepth > depth){
                
                continue;
            }
                
            // Read the contents of the current directory
            const items = this.fs.readdirSync(currentPath, { withFileTypes: true });

            for(const item of items){
                
                const itemPath = this.path.join(currentPath, item.name);
                
                // Determine whether to search on the full path or just the name
                const searchOn = searchMode === 'absolute' ? itemPath : item.name;

                // Check if the item should be excluded
                if(excludeRegex?.test(itemPath)){
                    
                    continue;
                }

                if(item.isDirectory()){
                    
                    // If searching for folders or both, and the item matches the search regex, add it to the result
                    if(searchItemsType !== 'files' && searchRegex.test(searchOn)){
                        
                        result.push(itemPath);
                    }
                    
                    // If we haven't reached the maximum depth, add this directory to the stack
                    if(depth === -1 || currentDepth < depth){
                        
                        stack.push([itemPath, currentDepth + 1]);
                    }
                    
                }else if(item.isFile() && searchItemsType !== 'folders' && searchRegex.test(searchOn)){
                    
                    // If searching for files or both, and the item matches the search regex, add it to the result
                    result.push(itemPath);
                }
            }
        }
        
        // Format results based on returnFormat
        let formattedResult: string[] = [];
                    
        if(result.length !== 0){
            
            switch(returnFormat){
                
                case 'relative':
                    formattedResult = result.map(item => this.path.relative(path, item));
                    break;
                    
                case 'name':
                    formattedResult = result.map(item => this.path.basename(item));
                    break;
                    
                case 'name-noext':
                    formattedResult = result.map(item => this.path.basename(item, this.path.extname(item)));
                    break;
                    
                case 'absolute':
                    formattedResult = result;
                    break;
                    
                default:
                    throw new Error('Invalid returnFormat: ' + returnFormat);
            }
        }

        // Sort the results alphabetically
        return formattedResult.sort((a, b) => a.localeCompare(b));
    }


    /**
     * Search for a folder name that does not exist on the provided path.
     *
     * If we want to create a new folder inside another one without knowing for sure what does it contain, this method will
     * guarantee us that we have a unique directory name that does not collide with any other folder or file that currently
     * exists on the path.
     *
     * NOTE: This method does not create any folder or alter the given path in any way.
     *
     * @param path Absolute or relative path to the directoy we want to check for a non existant folder name
     * @param desiredName This is the folder name that we would like to be available on the provided path. This method will verify
     *        that it does not exist, or otherwise give us a name based on the desired one that is available on the path. If we provide
     *        here an empty value, the method will take care of providing the non existant directory name we need.
     * @param text Text that will be appended to the suggested name in case it already exists.
     *             For example: text='copy' will generate a result like 'NewFolder-copy' or 'NewFolder-copy-1' if a folder named 'NewFolder' exists
     * @param separator String that will be used to join the suggested name with the text and the numeric file counter.
     *                  For example: separator='---' will generate a result like 'NewFolder---copy---1' if a folder named 'NewFolder' already exists
     * @param isPrefix Defines if the extra text that will be appended to the desired name will be placed after or before the name on the result.
     *                 For example: isPrefix=true will generate a result like 'copy-1-NewFolder' if a folder named 'NewFolder' already exists
     *
     * @return A directory name that can be safely created on the specified path, cause no one exists with the same name
     *         (No path is returned by this method, only a directory name. For example: 'folder-1', 'directoryName-5', etc..).
     */
    findUniqueDirectoryName(path: string,
                            desiredName = '',
                            text = '',
                            separator = '-',
                            isPrefix = false){

        if(!StringUtils.isString(path)){

            throw new Error('path must be a string');
        }

        if(!StringUtils.isString(desiredName)){

            throw new Error('desiredName must be a string');
        }

        if(!StringUtils.isString(text)){

            throw new Error('text must be a string');
        }

        if(!StringUtils.isString(separator)){

            throw new Error('separator must be a string');
        }
       
        path = this._composePath(path, true);

        let i = 1;
        let result = StringUtils.isEmpty(desiredName) ? String(i) : desiredName;
        
        while(this.isDirectory(path + this.dirSep() + result) ||
              this.isFile(path + this.dirSep() + result)){

            result = this._generateUniqueNameAux(i, desiredName, text, separator, isPrefix);

            i++;
        }

        return result;
    }


    /**
     * Search for a file name that does not exist on the provided path.
     *
     * If we want to create a new file inside a folder without knowing for sure what does it contain, this method will
     * guarantee us that we have a unique file name that does not collide with any other folder or file that currently
     * exists on the path.
     *
     * NOTICE: This method does not create any file or alter the given path in any way.
     *
     * @param path Absolute or relative path to the directoy we want to check for a unique file name
     * @param desiredName We can specify a suggested name for the unique file. This method will verify that it
     *                    does not exist, or otherwise give us a name based on our desired one that is unique for the path
     * @param text Text that will be appended to the suggested name in case it already exists.
     *             For example: text='copy' will generate a result like 'NewFile-copy' or 'NewFile-copy-1' if a file named 'NewFile' exists
     * @param separator String that will be used to join the suggested name with the text and the numeric file counter.
     *                  For example: separator='---' will generate a result like 'NewFile---copy---1' if a file named 'NewFile' already exists
     * @param isPrefix Defines if the extra text that will be appended to the desired name will be placed after or before the name on the result.
     *                 For example: isPrefix=true will generate a result like 'copy-1-NewFile' if a file named 'NewFile' already exists
     *
     * @return A file name that can be safely created on the specified path, cause no one exists with the same name
     *         (No path is returned by this method, only a file name. For example: 'file-1', 'fileName-5', etc..).
     */
    findUniqueFileName(path: string,
                       desiredName = '',
                       text = '',
                       separator = '-',
                       isPrefix = false){

        let i = 1;
        let result = (desiredName == '' ? i : desiredName);
        
        path = this._composePath(path, true);
        
        let extension = StringUtils.getPathExtension(desiredName);

        if(extension !== ''){

            extension = '.' + extension;
        }

        while(this.isDirectory(path + this.dirSep() + result) ||
              this.isFile(path + this.dirSep() + result)){

            result = this._generateUniqueNameAux(i, StringUtils.getPathElementWithoutExt(desiredName), text, separator, isPrefix) + extension;
            
            i++;
        }

        return result;
    }


    /**
     * Create a directory at the specified filesystem path
     *
     * @param path Absolute or relative path to the directoy we want to create. For example: c:\apps\my_new_folder
     * @param recursive Allows the creation of nested directories specified in the path. Defaults to false.
     *
     * @throws An exception will be thrown if a file exists with the same name or folder cannot be created (If the folder already
     *         exists, no exception will be thrown).
     *
     * @return True on success or false if the folder already exists.
     */
    createDirectory(path: string, recursive = false){

        if(!StringUtils.isString(path) || StringUtils.isEmpty(path)){

            throw new Error('Path must be a non empty string');
        }

        // Test for not allowed chars * " < > | ?
        if(/[*"<>|?\r\n]/.test(path)) {

            throw new Error('Forbidden * " < > | ? chars found in path: ' + path);
        }
        
        path = this._composePath(path);

        // If folder already exists we won't create it
        if(this.isDirectory(path)){

            return false;
        }

        // If specified folder exists as a file, exception will happen
        if(this.isFile(path)){

            throw new Error('specified path is an existing file ' + path);
        }

        // Create the requested folder
        try{

            if(!recursive){
            
                this.fs.mkdirSync(path);
            
            }else{
            
                this.fs.mkdirSync(path, { recursive: true });
            }
            
        }catch(e){

            // It is possible that multiple concurrent calls create the same folder at the same time.
            // We will ignore those exceptions cause there's no problen with this situation, the first of the calls creates it and we are ok with it.
            // But if the folder to create does not exist at the time of catching the exception, we will throw it, cause it will be another kind of error.
            if(!this.isDirectory(path)){

                throw new Error(e.message + ' ' + path);
            }

            return false;
        }

        return true;
    }
    
    
    /**
     * Obtain the full path to the current operating system temporary folder location.
     * It will be correctly formated and without any trailing separator character.
     */
    getOSTempDirectory(){
        
        return StringUtils.formatPath(this.os.tmpdir(), this.dirSep());
    }


    /**
     * Create a TEMPORARY directory on the operating system tmp files location, and get us the full path to access it.
     * OS should take care of its removal but it is not assured, so it is recommended to make sure all the tmp data is deleted after
     * using it (This is specially important if the tmp folder contains sensitive data).
     *
     * @param desiredName A name we want for the new directory to be created. If name exists on the system temporary folder, a unique one
     *                    (based on the desired one) will be generated automatically. We can also leave this value empty to let the method
     *                    calculate it.
     * @param deleteOnExecutionEnd True by default. Defines if the generated temp folder must be deleted after the current application execution finishes.
     *                             Note that when files inside the folder are locked used by the app or OS, exceptions or problems may happen
     *                             and it is not 100% guaranteed that the folder will be always deleted. So it is a good idea to leave this flag
     *                             to true and also handle the temporary folder removal in our code by ourselves. There won't be any problem if we
     *                             delete the folder before a delete is attempted on execution end.
     *
     * @return The full path to the newly created temporary directory, including the directory itself (without a trailing slash).
     *         For example: C:\Users\Me\AppData\Local\Temp\MyDesiredName
     */
    createTempDirectory(desiredName: string, deleteOnExecutionEnd = true) {

        let tempRoot = this.getOSTempDirectory();

        let tempDirectory = tempRoot + this.dirSep() + this.findUniqueDirectoryName(tempRoot, desiredName);

        if(!this.createDirectory(tempDirectory)){

            throw new Error('Could not create TMP directory ' + tempDirectory);
        }

        // Add a shutdown function to try to delete the file when the current script execution ends
        if(deleteOnExecutionEnd){

            FilesManager._tempDirectoriesToDelete.push(tempDirectory);
            
            // Note that as _tempDirectoriesToDelete is a static property shared by all the FilesManager instances,
            // Only one event listener will be attached to the 'exit' event. This way we prevent possible memory leaks by
            // Letting only the first FilesManager instance to be the responsible of cleaning the temporary folders at application end.
            if(FilesManager._tempDirectoriesToDelete.length < 2){
              
                process.once('exit', () => {

                    for (let temp of FilesManager._tempDirectoriesToDelete) {

                        if(this.isDirectory(temp)){
                        
                            this.deleteDirectory(temp);
                        }
                    }
                });
            }
        }

        return tempDirectory;
    }
    
    
    /**
     * Aux property that globally stores the list of all paths to temporary folders that must be removed when application execution ends.
     * This is defined static so only one shared property exists for all the FilesManager instances, and therefore we prevent memory leaks
     * by using also a single process 'exit' event listener
     */
    private static _tempDirectoriesToDelete: string[] = [];


    /**
     * Gives the list of items that are stored on the specified folder. It will give files and directories, and each element will be the item name, without the path to it.
     * The contents of any subfolder will not be listed. We must call this method for each child folder if we want to get it's list.
     * (The method ignores the . and .. items if exist).
     *
     * @param path Absolute or relative path to the directory we want to list
     * @param sort Specifies the sort for the result:<br>
     * &emsp;&emsp;'' will not sort the result.<br>
     * &emsp;&emsp;'nameAsc' will sort the result by filename ascending.
     * &emsp;&emsp;'nameDesc' will sort the result by filename descending.
     * &emsp;&emsp;'mDateAsc' will sort the result by modification date ascending.
     * &emsp;&emsp;'mDateDesc' will sort the result by modification date descending.
     *
     * @return The list of item names inside the specified path sorted as requested, or an empty array if no items found inside the folder.
     */
    getDirectoryList(path: string, sort = ''): string[]{

        path = this._composePath(path, true);

        // Get all the folder contents
        let result:any[] = [];
        // TODO let sortRes = [];

        for (let fileInfo of this.fs.readdirSync(path)) {

            if(fileInfo !== '.' && fileInfo !== '..'){

                switch(sort) {

                    case 'mDateAsc':
                    case 'mDateDesc':
                        // TODO - Date sort is not implemented. Translate from php
                        break;

                    default:
                        result.push(fileInfo);
                        break;
                }
            }
        }

        // Apply result sorting as requested
        switch(sort) {

            case 'nameAsc':
                result.sort();
                break;

            case 'nameDesc':
                result.sort();
                result.reverse();
                break;

            case 'mDateAsc':
                // TODO - Date sort is not implemented. Translate from php
                break;

            case 'mDateDesc':
                // TODO - Date sort is not implemented. Translate from php
                break;

            default:
                if(sort !== ''){

                    throw new Error('Unknown sort method');
                }
        }

        return result; 
    }


    /**
     * Calculate the full size in bytes for a specified folder and all its contents.
     *
     * @param path Absolute or relative path to the directory we want to calculate its size
     *
     * @return the size of the file in bytes. An exception will be thrown if value cannot be obtained
     */
    getDirectorySize(path: string){

        path = this._composePath(path);

        let result = 0;

        for (let fileOrDir of this.getDirectoryList(path)) {

            let fileOrDirPath = path + this.dirSep() + fileOrDir;

            result += this.isDirectory(fileOrDirPath) ?
                    this.getDirectorySize(fileOrDirPath) :
                    this.getFileSize(fileOrDirPath);
        }

        return result;
    }
    
    
    /**
     * Copy all the contents from a source directory to a destination one (Both source and destination paths must exist).
     *
     * Any source files that exist on destination will be overwritten without warning.
     * Files that exist on destination but not on source won't be modified, removed or altered in any way.
     *
     * @param sourcePath Absolute or relative path to the source directory where files and folders to copy exist
     * @param destPath Absolute or relative path to the destination directory where files and folders will be copied
     * @param destMustBeEmpty if set to true, an exception will be thrown if the destination directory is not empty.
     *
     * @throws Error
     *
     * @return True if copy was successful, false otherwise
     */
    copyDirectory(sourcePath: string, destPath: string, destMustBeEmpty = true){

        sourcePath = this._composePath(sourcePath);
        destPath = this._composePath(destPath);

        if(sourcePath === destPath){

            throw new Error('cannot copy a directory into itself: ' + sourcePath);
        }

        if(destMustBeEmpty && !this.isDirectoryEmpty(destPath)){

            throw new Error('destPath must be empty');
        }

        for (let sourceItem of this.getDirectoryList(sourcePath)) {

            let sourceItemPath = sourcePath + this.dirSep() + sourceItem;
            let destItemPath = destPath + this.dirSep() + sourceItem;

            if(this.isDirectory(sourceItemPath)){

                if(!this.isDirectory(destItemPath) && !this.createDirectory(destItemPath)){

                    return false;
                }

                if(!this.copyDirectory(sourceItemPath, destItemPath, destMustBeEmpty)){

                    return false;
                }

            }else{

                if(!this.copyFile(sourceItemPath, destItemPath)){

                    return false;
                }
            }
        }

        return true;
    }
    
    
    /**
     * This method performs a one way sync process which consists in applying the minimum modifications to the destination path
     * that will guarantee that it is an exact copy of the source path. Any files or folders that are identical on both provided paths
     * will be left untouched
     *
     * @param sourcePath Absolute or relative path to the source directory where files and folders to mirror exist
     * @param destPath Absolute or relative path to the destination directory that will be modified to exactly match the source one
     * @param timeout The amount of seconds that this method will be trying to delete or modify a file in case it is blocked
     *        by the OS or temporarily not accessible. If the file can't be deleted after the given amount of seconds, an exception
     *        will be thrown.
     *
     * @throws Error in case any of the necessary file operations fail
     *
     * @return True on success
     */
    mirrorDirectory(sourcePath: string, destPath: string, timeout = 15){

        sourcePath = this._composePath(sourcePath, true);
        destPath = this._composePath(destPath, true);

        if(sourcePath === destPath){

            throw new Error('cannot mirror a directory into itself: ' + sourcePath);
        }
        
        // Get the full list of source items to mirror
        const sourceItems = this.getDirectoryList(sourcePath);

        // Loop all source Items. If not found on destination, we will mirror them.
        for (let sourceItem of sourceItems) {

            const sourceItemPath = sourcePath + this.dirSep() + sourceItem;
            const destItemPath = destPath + this.dirSep() + sourceItem;
            
            if(this.isDirectory(sourceItemPath)){

                // If a file exists with the same name, it will be removed
                if(this.isFile(destItemPath)){

                    this.deleteFile(destItemPath, timeout);
                }

                // If a folder exists with the same name, we must verify that it is equal by calling mirror inside it.
                // Otherwise, we will directly copy the source directory
                if(this.isDirectory(destItemPath)){

                    this.mirrorDirectory(sourceItemPath, destItemPath, timeout);
                
                }else{
                    
                    this.createDirectory(destItemPath);
                    this.copyDirectory(sourceItemPath, destItemPath, true);
                }

            }else{

                // If a dir exists with the same name, it will be removed
                if(this.isDirectory(destItemPath)){

                    this.deleteDirectory(destItemPath, true, timeout);
                }

                // If no file exists or it contains different data, the source file will be copied to destination
                if((!this.isFile(destItemPath) || !this.isFileEqualTo(sourceItemPath, destItemPath)) &&
                    !this.copyFile(sourceItemPath, destItemPath)){

                    throw new Error('Could not copy file from source <' + sourceItemPath + '> to destination <' + destItemPath + '>');
                }
            }
        }

        // get all destination items, and substract the source items.
        // Any element that still appears on the list must be removed
        const destinationItemsToRemove = this.getDirectoryList(destPath).filter(item => sourceItems.indexOf(item) < 0);

        // Delete items in destination that don't exist in source
        for (let destItem of destinationItemsToRemove) {
            
            const destItemPath = destPath + this.dirSep() + destItem;
            
            if (this.isDirectory(destItemPath)) {
                
                this.deleteDirectory(destItemPath, true, timeout);
                
            } else {
                
                this.deleteFile(destItemPath, timeout);
            }
        }

        return true;
    }


    /**
     * TODO - translate from php
     */
    syncDirectories(){

        // TODO - translate from php
    }
    
    
    /**
     * Renames a directory.

     *
     * @param sourcePath Absolute or relative path to the source directory that must be renamed (including the directoy itself).
     * @param destPath Absolute or relative path to the new directoy name (including the directoy itself). It must not exist.
     * @param timeout The amount of seconds that this method will be trying to rename the specified directory in case it is blocked
     *        by the OS or temporarily not accessible. If the directory can't be renamed after the given amount of seconds, an exception
     *        will be thrown.
     *
     * @return boolean True on success
     */
    renameDirectory(sourcePath:string, destPath:string, timeout = 15){

        return this._renameFSResource(this._composePath(sourcePath, true), this._composePath(destPath), timeout);
    }
    
    
    /**
     * Aux method that is used by renameFile and renameDirectory to rename a file or folder after their specific checks have been performed
     * 
     * @param sourcePath Source path for the resource to rename
     * @param destPath Dest path for the resource to rename
     * @param timeout Amount of seconds to wait if not possible
     */
    private _renameFSResource(sourcePath:string, destPath:string, timeout: number){
        
        if(this.isDirectory(destPath) || this.isFile(destPath)){

            throw new Error('Invalid destination: ' + destPath);
        }

        if(this.path.resolve(StringUtils.getPath(sourcePath)) !== this.path.resolve(StringUtils.getPath(destPath))){

            throw new Error('Source and dest must be on the same path');
        }
        
        let lastError = '';
        let passedTime = 0;
        let startTime = Math.floor(Date.now() / 1000);
        
        do {
            
            try {
                
                this.fs.renameSync(sourcePath, destPath);
                
                return true;
                
            } catch (e) {
    
                lastError = e.toString();
            }
        
            passedTime = Math.floor(Date.now() / 1000) - startTime;
            
        } while (passedTime < timeout);
        
        throw new Error(`Error renaming (${passedTime} seconds timeout):\n${sourcePath}\n${lastError}`);
    }


    /**
     * Delete a directory from the filesystem and all its contents (folders and files).
     *
     * @param path Absolute or relative path to the directory that will be removed
     * @param deleteDirectoryItself Set it to true if the specified directory must also be deleted.
     * @param timeout The amount of seconds that this method will be trying to perform a delete operation in case it is blocked
 *            by the OS or temporarily not accessible. If the operation can't be performed after the given amount of seconds,
     *        an exception will be thrown.
     *
     * @return int The number of files that have been deleted as part of the directory removal process. If directory is empty or ContainsElement
     *         only folders, 0 will be returned even if many directories are deleted. If directory does not exist or it could not be deleted,
     *         an exception will be thrown
     */
    deleteDirectory(path: string, deleteDirectoryItself = true, timeout = 15){

        let deletedFilesCount = 0;
        path = this._composePath(path, true);

        for (let file of this.getDirectoryList(path)) {
  
            if(this.isDirectory(path + this.dirSep() + file)){

                deletedFilesCount += this.deleteDirectory(path + this.dirSep() + file, true, timeout);

            }else{

                this.deleteFile(path + this.dirSep() + file, timeout);

                deletedFilesCount ++;
            }
        }

        if(deleteDirectoryItself) {

            let lastError = '';
            let passedTime = 0;
            let deleteStartTime = Math.floor(Date.now() / 1000);

            do {

                try {

                    this.fs.rmdirSync(path);
                    
                    return deletedFilesCount;
                    
                } catch (e) {
                    
                    lastError = e.toString();
                }
                
                passedTime = Math.floor(Date.now() / 1000) - deleteStartTime;
                
            } while(passedTime < timeout);

            throw new Error(`Could not delete directory itself (${passedTime} seconds timeout):\n${path}\n${lastError}`);
        }   

        return deletedFilesCount;
    }


    /**
     * Writes the specified data to a physical file, which will be created (if it does not exist) or overwritten without warning.
     * This method can be used to create a new empty file, a new file with any contents or to overwrite an existing one.
     *
     * We must check for file existence before executing this method if we don't want to inadvertently replace existing files.
     *
     * @see FilesManager.isFile
     *
     * @param pathToFile Absolute or relative path including full filename where data will be saved. File will be created or overwritten without warning.
     * @param data Any information to save on the file.
     * @param append Set it to true to append the data to the end of the file instead of overwritting it. File will be created if it does
     *        not exist, even with append set to true.
     * @param createDirectories If set to true, all necessary non existant directories on the provided file path will be also created.
     *
     * @return True on success or false on failure.
     */
    saveFile(pathToFile: string, data = '', append = false, createDirectories = false){

        pathToFile = this._composePath(pathToFile);

        if(createDirectories){

            this.createDirectory(StringUtils.getPath(pathToFile), true);
        }
        
        // TODO : we should lock the file before writting to it.
        // This feature is implemented on the PHP version of this library, but as it is not
        // yet available on Nodejs, we cannot implement it here
        
        try {

            if(append){
                
                this.fs.appendFileSync(pathToFile, data);
                
            }else{
                
                this.fs.writeFileSync(pathToFile, data);
            }
            
            return true;
            
        } catch (e) {

            throw new Error('Could not write to file: ' + pathToFile);
        }
    }


    /**
     * TODO - translate from php
     */
    createTempFile(){

        // TODO - translate from php
    }

    
    /**
     * Concatenate all the provided files, one after the other, into a single destination file.
     *
     * @param sourcePaths A list with the absolute or relative paths to the files we want to join. The result will be generated in the same order.
     * @param destFile The full path where the merged file will be stored, including the full file name (will be overwitten if exists).
     * @param separator An optional string that will be concatenated between each file content. We can for example use "\n\n" to
     *        create some empty space between each file content
     *
     * @return True on success or false on failure.
     */
    mergeFiles(sourcePaths: string[], destFile: string, separator = ''){

        let mergedData = '';

        for (var i = 0; i < sourcePaths.length; i++) {

            mergedData += this.readFile(this._composePath(sourcePaths[i]));

            // Place separator string on all files except the last one
            if(i < sourcePaths.length - 1 && separator !== ''){

                mergedData += separator;
            }
        }

        return this.saveFile(destFile, mergedData);
    }
    
    
    /**
     * Get the size from a file
     *
     * @param pathToFile Absolute or relative file path, including the file name and extension
     *
     * @return int the size of the file in bytes. An exception will be thrown if value cannot be obtained
     */
    getFileSize(pathToFile: string){

        pathToFile = this._composePath(pathToFile, false, true);

        try {

            return this.fs.statSync(pathToFile).size;

        } catch (e) {
            
            throw new Error('Error reading file size');
        }
    }
    
    
    /**
     * TODO - adapt from PHP 
     */
    getFileModificationTime(pathToFile: string){

        // TODO - adapt from PHP
        return pathToFile;
    }


    /**
     * Read and return the content of a file. Not suitable for big files (More than 5 MB) cause the script memory
     * may get full and the execution fail
     *
     * @param pathToFile An Operating system absolute or relative path containing some file
     *
     * @return The file contents (binary or string). If the file is not found or cannot be read, an exception will be thrown.
     */
    readFile(pathToFile: string){

        pathToFile = this._composePath(pathToFile, false, true);

        return this.fs.readFileSync(pathToFile, "utf8");
    }
    
    
    /**
     * Read and return the content of a file encoded as a base 64 string. Not suitable for big files (More than 5 MB) 
     * cause the script memory may get full and the execution fail
     *
     * @param pathToFile An Operating system absolute or relative path containing some file
     *
     * @return The file contents as a base64 string. If the file is not found or cannot be read, an exception will be thrown.
     */
    readFileAsBase64(pathToFile: string){

        pathToFile = this._composePath(pathToFile, false, true);

        return this.fs.readFileSync(pathToFile, {encoding: 'base64'});
    }


    /**
     * Reads a file and performs a buffered output to the browser, by sending it as small fragments.<br>
     * This method is mandatory with big files, as reading the whole file to memory will cause the script or RAM to fail.<br><br>
     *
     * Adapted from code suggested at: http://php.net/manual/es/function.readfile.php
     *
     * @param pathToFile An Operating system absolute or relative path containing some file
     * @param downloadRateLimit If we want to limit the download rate of the file, we can do it by setting this value to > 0. For example: 20.5 will set the file download rate to 20,5 kb/s
     *
     * @return the number of bytes read from the file.
     */
    readFileBuffered(){

        // TODO - translate from php
    }


    /**
     * Copies a file from a source location to the defined destination
     * If the destination file already exists, it will be overwritten. 
     * 
     * @param sourceFilePath Absolute or relative path to the source file that must be copied (including the filename itself).
     * @param destFilePath Absolute or relative path to the destination where the file must be copied (including the filename itself).
     *
     * @return Returns true on success or false on failure.
     */
    copyFile(sourceFilePath: string, destFilePath: string){

        sourceFilePath = this._composePath(sourceFilePath);
        destFilePath = this._composePath(destFilePath);

        try{
            
            this.fs.copyFileSync(sourceFilePath, destFilePath);

            return true;
            
        }catch(e){
        
            return false;
        }
    }
    
    
    /**
     * Renames a file.
     *
     * @param sourceFilePath Absolute or relative path to the source file that must be renamed (including the filename itself).
     * @param destFilePath Absolute or relative path to the new file name (including the filename itself). It must not exist.
     * @param timeout The amount of seconds that this method will be trying to rename the specified file in case it is blocked
     *            by the OS or temporarily not accessible. If the file can't be renamed after the given amount of seconds, an exception
     *            will be thrown.
     *
     * @return boolean True on success
     */
    renameFile(sourceFilePath: string, destFilePath: string, timeout = 15){

        return this._renameFSResource(this._composePath(sourceFilePath, false, true), this._composePath(destFilePath), timeout);
    }


    /**
     * Delete a filesystem file.
     *
     * @param pathToFile Absolute or relative path to the file we want to delete
     * @param timeout The amount of seconds that this method will be trying to delete the specified file in case it is blocked
     *        by the OS or temporarily not accessible. If the file can't be deleted after the given amount of seconds, an exception
     *        will be thrown.
     *
     * @throws Error If the file cannot be deleted or does not exist
     *
     * @return True on success ONLY if the file existed and was deleted.
     */
    deleteFile(pathToFile: string, timeout = 15){

        pathToFile = this._composePath(pathToFile, false, true);

        let lastError = '';
        let passedTime = 0;
        let deleteStartTime = Date.now();

        if(this.fs.existsSync(pathToFile)){

            do {
                
                try {
    
                    this.fs.unlinkSync(pathToFile);
                    
                    return true;
                    
                } catch (e) {
                    
                    lastError = e.toString();
                }
                
                // TODO - should be interesting to use some kind of sleep here
                
                passedTime = Date.now() - deleteStartTime;
                
            } while (passedTime < timeout * 1000);

        }else{
            
            lastError = 'File does not exist';
        }       

        throw new Error(`Error deleting file (${passedTime} seconds timeout):\n${pathToFile}\n${lastError}`);
    }
    
    
    /**
     * Delete a list of filesystem files.
     *
     * @param pathsToFiles A list of filesystem absolute or relative paths to files to delete
     * @param timeout The amount of seconds that this method will be trying to delete a file in case it is blocked
     *        by the OS or temporarily not accessible. If the file can't be deleted after the given amount of seconds, an exception
     *        will be thrown.
     *
     * @throws Error if any of the files cannot be deleted, an exception will be thrown
     *
     * @return True on success
     */
    deleteFiles(pathsToFiles: string[], timeout = 15){

        for (let pathToFile of pathsToFiles) {

            this.deleteFile(pathToFile, timeout);
        }

        return true;
    }
    
    
    /**
     * Auxiliary method that is used by the findUniqueFileName and findUniqueDirectoryName methods
     *
     * @param i Current index for the name generation
     * @param desiredName Desired name as used on the parent method
     * @param text text name as used on the parent method
     * @param separator separator name as used on the parent method
     * @param isPrefix isPrefix name as used on the parent method
     *
     * @return The generated name
     */
    private _generateUniqueNameAux(i: number, desiredName: string, text: string, separator: string, isPrefix: boolean){

        let result: string[] = [];

        if(isPrefix){

            if(text !== ''){

                result.push(text);
            }

            result.push(String(i));

            if(desiredName !== ''){

                result.push(desiredName);
            }

        }else{

            if(desiredName !== ''){

                result.push(desiredName);
            }

            if(text !== ''){

                result.push(text);
            }

            result.push(String(i));
        }

        return result.join(separator);
    }
    
    
    /**
     * Auxiliary method to generate a full path from a relative one and the configured root path
     *
     * If an absolute path is passed to the relativePath variable, the result of this method will be that value, ignoring
     * any possible value on _rootPath.
     */
    private _composePath(relativePath: string, testIsDirectory = false, testIsFile = false){

        if (!StringUtils.isString(relativePath)){

            throw new Error('Path must be a string');
        }
        
        let composedPath = '';

        if (StringUtils.isEmpty(this._rootPath) ||
            this.isPathAbsolute(relativePath)) {

            composedPath = relativePath;

        } else {

            composedPath = this._rootPath + this.dirSep() + relativePath;
        }

        let path = StringUtils.formatPath(composedPath, this.dirSep());

        if (testIsDirectory && !this.isDirectory(path)){

            throw new Error('Path does not exist: ' + path);
        }

        if(testIsFile && !this.isFile(path)){

            throw new Error('File does not exist: ' + path);
        }

        return path;
    }
}