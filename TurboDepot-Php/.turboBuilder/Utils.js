"use strict";

/**
 * --------------------------------------------------------------------------------------------------------------------------------------
 * Utility methods used by TurboBuilder
 * --------------------------------------------------------------------------------------------------------------------------------------
 */


/**
 * Check that the specified value is found inside an array
 */
function inArray(value, array){
	
	for(var i = 0; i < array.length; i++){
		
		if(array[i] === value){
			
			return true;
		}
	}
	
	return false;
}


/**
 * Check if the specified file or folder exists or not
 */
function fileExists(path){

	try{
	
		var f = new java.io.File(path);
	    
		return f.exists();
		
	}catch(e){

		// Nothing to do
	}
	
	return false;
}


/**
 * Load all the file contents and return it as a string
 */
function loadFileAsString(path, replaceWhiteSpaces){

	var file = new java.io.File(path);
	var fr = new java.io.FileReader(file);
	var br = new java.io.BufferedReader(fr);

	var line;
	var lines = "";

	while((line = br.readLine()) != null){

		if(replaceWhiteSpaces){

			lines = lines + line.replace(" ", "");

		}else{

			lines = lines + line;
		}
	}

	return lines;
}


/**
 * Get a list with all the first level folders inside the specified path.
 * 
 * @param path A full file system path from which we want to get the list of first level folders
 * 
 * @returns An array containing all the first level folders inside the given path. Each array element will be 
 * relative to the provided path. For example, if we provide "src/main" as path, 
 * resulting folders may be like "php", "css", ... and so.
 */
function getFoldersList(path){
	
	var ds = project.createDataType("dirset");
	
	ds.setDir(new java.io.File(path));
	ds.setIncludes("*");
	
	var srcFolders = ds.getDirectoryScanner(project).getIncludedDirectories();
    
    var result = [];
    
    for (var i = 0; i<srcFolders.length; i++){
        
    	result.push(srcFolders[i]);
    }
    
    return result;
}


/**
 * Get a list with all the files inside the specified path and all of its subfolders.
 * 
 * @param path A full file system path from which we want to get the list of files
 * @param includes comma- or space-separated list of patterns of files that must be included; all files are included when omitted.
 * @param excludes comma- or space-separated list of patterns of files that must be excluded; no files (except default excludes) are excluded when omitted.
 * 
 * @returns An array containing all the matching files inside the given path and subfolders. Each array element will be 
 * the full filename plus the relative path to the provided path. For example, if we provide "src/main" as path, 
 * resulting files may be like "php/managers/BigManager.php", ... and so.
 */
function getFilesList(path, includes, excludes){
	
	// Init default vars values
	includes = (includes === undefined || includes == null || includes == '') ? "**" : includes;
	excludes = (excludes === undefined || excludes == null || excludes == '') ? "" : excludes;
	
	var fs = project.createDataType("fileset");
	
	fs.setDir(new java.io.File(path));
    
	if(includes != ""){
	
		fs.setIncludes(includes);
	}	
    
    if(excludes != ""){
    
    	fs.setExcludes(excludes);
    }    

    var srcFiles = fs.getDirectoryScanner(project).getIncludedFiles();
    
    var result = [];
    
    for (var i = 0; i<srcFiles.length; i++){
        
    	result.push(srcFiles[i]);
    }
    
    return result;
}