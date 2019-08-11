<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */


namespace org\turbodepot\src\main\php\managers;


use UnexpectedValueException;
use org\turbocommons\src\main\php\utils\NumericUtils;


/**
 * PdfFilesManager class
 */
class PdfFilesManager {


    /**
     * see class constructor
     */
    private $_ghostScriptPath = '';


    /**
     * see class constructor
     */
    private $_pdfInfoPath = '';


    /**
     * Manager class that contains several pdf files manipulation tools.
     *
     * @param string $ghostScriptPath Full path to the ghostscript executable. For example: 'gs' if we are using the version installed on our machine, or its full file system path if we have
     *        it somewhere else (It is recommended to use always the latest version).<br>
     *        GhostScript is an open source library to manipulate PS and PDF files, that normally comes bundled with linux distributions.<br>
     *        If not natively available we must install it on our machine, or better download (http://www.ghostscript.com/download/gsdnld.html) the pre compiled binaries and place them on a
     *        location that is reachable by this class.
     * @param string $pdfInfoPath Full path to the pdfinfo executable. PdfInfo is a command line tool that is required on some of this class methods. It is free and can be downloaded from:
     *        http://www.foolabs.com/xpdf/download.html (note that the download is a bundle with several other tools)<br>
     *        VERY IMPORTANT:<br>
     *            1. pdfinfo execute permission MUST be enabled at least for the file owner<br>
     *            2. Make sure you are using the binary executable that fits your OS (centos, windows...) and processor (32bit / 64bit) or it may not work
     */
    public function __construct($ghostScriptPath = 'gs', $pdfInfoPath = ''){

        // Check that ghostscript is enabled on the current machine
        if($ghostScriptPath === 'gs'){

            $checkGhostScript = 1;

            system('which gs > /dev/null', $checkGhostScript);

            if($checkGhostScript !== 0) {

                throw new UnexpectedValueException('Ghostscript is not installed on the system');
            }

        }else if(!is_executable($ghostScriptPath)){

            throw new UnexpectedValueException('Specified Ghostscript binary does not exist or execute permisions are disabled: '.$ghostScriptPath);
        }

        $this->_ghostScriptPath = $ghostScriptPath;

        // Check that the cmd pdfinfo tool exists and is executable
        if($pdfInfoPath !== '' && !is_executable($pdfInfoPath)){

            throw new UnexpectedValueException('Specified pdfinfo binary does not exist or execute permisions are disabled: '.$pdfInfoPath);
        }

        $this->_pdfInfoPath = $pdfInfoPath;
    }


    /**
     * Check if the specified file is a valid PDF file
     *
     * @param string $pdfFilePath Full filesystem path to a valid pdf file
     *
     * @return boolean True if the file is a valid PDF, false otherwise
     */
    public function isValidDocument($pdfFilePath){

        // TODO - create this method

        return true;
    }


    /**
     * Obtain the full binary contents of the specified pdf file
     *
     * @param string $pdfFilePath Full filesystem path to a valid pdf file
     *
     * @return string The full binary contents of the specified pdf file
     */
    public function getDocumentBinaryData($pdfFilePath){

        if(!$this->isValidDocument($pdfFilePath)){

            throw new UnexpectedValueException('Specified document is not a valid PDF file');
        }

        return file_get_contents($pdfFilePath, true);
    }


    /**
     * Count the number of pages on a PDF document (the pdfinfo executable must be available and already defined at this class constructor)
     *
     * @param string $pdfFilePath Full filesystem path to a valid pdf file
     *
     * @return int The total number of calculated pages
     */
    public function countPages($pdfFilePath){

        if($this->_pdfInfoPath === ''){

            throw new UnexpectedValueException('pdinfo executable path is not defined and is required to count pdf pages');
        }

        // Check that the specified pdf file exists
        if(!is_file($pdfFilePath)){

            throw new UnexpectedValueException('Specified PDF file does not exist: '.$pdfFilePath);
        }

        // Execute the pdfinfo tool that gives us the information we need
//         $output = 1;
//         $pdfInfoResult = 1;

//         exec($this->_pdfInfoPath.' "'.$pdfFilePath.'"', $output, $pdfInfoResult);

//         // Check any problem on pdfinfo execution
//         if ($pdfInfoResult !== 0) {

//             throw new UnexpectedValueException('countPages pdfinfo failed :'.implode("\n", $output));
//         }

//         // Get the number of pages from the pdfinfo command line output, by using a regular expression.
//         $matches = [];

//         foreach($output as $op){

//             if(preg_match('/Pages:\s*(\d+)/i', $op, $matches) === 1){

//                 return intval($matches[1]);
//             }
//         }

//         return 0;
    }


    /**
     * Given a PDF document, this method will generate a picture for the specified page.
     *
     * @param string $pdfFilePath Full filesystem path to a valid pdf file
     * @param string $page The number of the page we want to get (0 is always the first page)
     * @param number $jpgQuality 90 by default. Specifies the jpg quality for the generated picture
     * @param string $dpi 150 by default, defines the pixel density for the generated picture. This will in fact affect the final
     *        resolution for the generated image. The bigger dpi we set, the bigger resolution image we will get.
     *
     * @return string A binary string containing the generated picture
     */
    public function getPageAsJpg($pdfFilePath, $page, $jpgQuality = 90, $dpi = '150'){

        // Check that page value is ok
        if(!NumericUtils::isInteger($page) || $page < 0){

            throw new UnexpectedValueException('Specified page must be a positive integer');
        }

        // Check that the specified pdf file exists
        if(!is_file($pdfFilePath)){

            throw new UnexpectedValueException('Specified PDF file does not exist: '.$pdfFilePath);
        }

        // Generate the ghostscript command line call
        $gsQuery  = $this->_ghostScriptPath.' -dNOPAUSE -sDEVICE=jpeg -dUseCIEColor -dDOINTERPOLATE -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -sOutputFile=- ';
        $gsQuery .= '-dFirstPage='.($page + 1).' -dLastPage='.($page + 1).' ';
        $gsQuery .= '-r'.$dpi.' ';
        $gsQuery .= '-dJPEGQ='.$jpgQuality.' ';
        $gsQuery .= '-q '.$pdfFilePath;

        // Get the processed image directly to stdOut
        ob_start();

        $gsQueryResult = 1;

        passthru($gsQuery, $gsQueryResult);

        $imageData = ob_get_contents();

        ob_end_clean();

        // Check any problem on ghostscript execution
        if ($gsQueryResult !== 0) {

            throw new UnexpectedValueException('Ghostscript failed '.$gsQueryResult);
        }

        // Check that
        if(strlen($imageData) < 500 && strpos($imageData, 'No pages will be processed') !== false){

            throw new UnexpectedValueException('Ghostscript failed '.$imageData);
        }

        return $imageData;
    }


    /**
     * Given a PDF document, this method will generate a picture for each one of the document pages.
     *
     * Requires GhostScript, that is an open source library to manipulate PS and PDF files, that normally comes bundled with linux distributions.
     * If not available, we must install it on our machine, or better download (http://www.ghostscript.com/download/gsdnld.html) the pre compiled binaries and place it on storage/binary
     *
     * @param string $ghostScriptPath  Full executable path to the ghostscript tool. For example: 'gs' if we are using the version installed on our machine or  $fileStorageManager->binaryGetAppPath('gs') if we have placed it on our storage binary folder. It is recommended to user always the latest version, so we better download it and place it on storage/binary
     * @param string $pdfFilePath  Full path to the pdf source file. Example: ProjectPaths::RESOURCES.'/pdf/mypdf.pdf'
     * @param string $outputPath Full path to a file system EMPTY folder where all the generated pictures will be stored. If the specified folder is not empty or does not exist, an exception will happen.
     * @param number $jpgQuality 90 by default. Specifies the jpg quality for all the generated pictures
     * @param string $dpi 200 by default, defines the pixel density for all the generated pictures. This will in fact affect the final resolution of the images.
     * @param string $outFileMask '/%d.jpg' by default. Allows us to define a pattern for the generated file names (%d will be replaced by the page number). Example: '%05d.jpg' will generate a jpg file with 5 digits, like '00012.jpg'. More info on the GostScript manual for the option -o
     *
     * @return number The total number of generated pages or -1 if an error happened
     */
    public function generateDocumentJpgPictures($ghostScriptPath, $pdfFilePath, $outputPath, $jpgQuality = 90, $dpi = '200', $outFileMask = '/%d.jpg'){

//         // Check that the specified pdf file exists
//         if(!is_file($pdfFilePath)){

//             trigger_error('PdfUtils::generateDocumentJpgPictures Error: Specified PDF file ('.$pdfFilePath.') does not exist', E_USER_WARNING);

//             return -1;
//         }

//         // Check that the specified output folder exists
//         if(!is_dir($outputPath)){

//             trigger_error('PdfUtils::generateDocumentJpgPictures Error: Specified output folder ('.$outputPath.') does not exist', E_USER_WARNING);

//             return -1;
//         }

//         // Make sure that the output folder is empty
//         if(count(FileSystemUtils::getDirectoryList($outputPath)) > 0){

//             trigger_error('PdfUtils::generateDocumentJpgPictures Error: Specified output folder ('.$outputPath.') must be empty', E_USER_WARNING);

//             return -1;
//         }

//         // Push the script time limit to 20 minutes, as this operation may be cpu intensive
//         $timeLimit = ini_get('max_execution_time');

//         set_time_limit(1200);

//         // Generate the ghostscript command line call from the received parameters
//         $gsQuery  = $ghostScriptPath.' -dNOPAUSE -sDEVICE=jpeg -dUseCIEColor -dDOINTERPOLATE -dTextAlphaBits=4 -dGraphicsAlphaBits=4 ';
//         $gsQuery .= '-o'.$outputPath.$outFileMask.' ';
//         $gsQuery .= '-r'.$dpi.' ';
//         $gsQuery .= '-dJPEGQ='.$jpgQuality.' ';
//         $gsQuery .= "'".$pdfFilePath."'";

//         exec($gsQuery, $output, $return);

//         // Restore the previous time limit value as we have finished processing
//         set_time_limit($timeLimit);

//         // Check any problem on ghostscript execution
//         if ($return != 0) {

//             trigger_error('PdfUtils::generateDocumentJpgPictures Ghostscript failed :'.implode("\n", $output), E_USER_WARNING);

//             return -1;
//         }

//         // Verify that the output folder contains the generated pictures, and count their number
//         return count(FileSystemUtils::getDirectoryList($outputPath));
    }


    /**
     * Performs maximum possible optimization to a specified pdf document, by appliyng the pdftk command line tool. We should place this tool on our project storage/binary folder
     * pdftk is free and can be downloaded from: https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/
     *
     * VERY IMPORTANT:
     * 1. pdftk execute permission MUST be enabled at least for the file owner
     * 2. Make sure you are using the binary executable of pdftk that fits your OS (centos, windows...) and processor (32bit / 64bit) or it may not work
     *
     * @param string $pdftkPath Full executable path to the pdftk tool. For example:  $fileStorageManager->binaryGetAppPath('pdftk')
     * @param string $pdfFilePath Full path to the pdf source file. Example: ProjectPaths::RESOURCES.'/pdf/mypdf.pdf'
     * @param string $outputPath Leave it empty (default value) to override the source pdf document or specify a full system path (including the destination filename) where the compressed result will be stored.
     *
     * @return boolean True if compression was performed or false if something failed
     */
    public function compressDocument($pdftkPath, $pdfFilePath, $outputPath = ''){

        // Check that the specified pdf file exists
//         if(!is_file($pdfFilePath)){

//             trigger_error('PdfUtils::compressDocument Error: Specified PDF file ('.$pdfFilePath.') does not exist', E_USER_WARNING);

//             return false;
//         }

//         // Check that the cmd pdftk tool exists and is executable
//         if(!is_executable($pdftkPath)){

//             trigger_error('PdfUtils::compressDocument Error: Specified pdftk CMD binary ('.$pdftkPath.') does not exist or execute permisions are disabled', E_USER_WARNING);

//             return false;
//         }

//         // Process the received pdf with pdftk
//         ob_start();

//         // We are using output - so the result of the pdftk command is shown directly on stdout.
//         // We then capture it with the php passthru method
//         passthru($pdftkPath.' '.$pdfFilePath.' output - compress');

//         $processedPdf = ob_get_contents();

//         ob_end_clean();

//         // Check that we have gained size improvements by applying the pdftk app
//         $originalSize = filesize($pdfFilePath);
//         $processedSize = strlen($processedPdf);

//         if($originalSize > $processedSize && $processedSize > 0){

//             // Store the compressed file
//             if($outputPath == ''){

//                 file_put_contents($pdfFilePath, $processedPdf);

//             }else{

//                 file_put_contents($outputPath, $processedPdf);
//             }

//         }else{

//             if($outputPath != ''){

//                 copy($pdfFilePath, $outputPath);
//             }
//         }

//         return true;
    }


    /**
     * Extract all the possible text from the given pdf document
     *
     * @param string $pdfFilePath Full path to the pdf source file. Example: ProjectPaths::RESOURCES.'/pdf/mypdf.pdf'
     *
     * @return string All the text that could be extracted from the pdf
     */
    public function extractDocumentText($pdfFilePath) {

        // TODO: fer aixo
//         return 'TODO';
    }
}

?>