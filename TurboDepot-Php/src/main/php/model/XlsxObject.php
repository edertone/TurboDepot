<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\main\php\model;

use SimpleXMLElement;
use Throwable;
use UnexpectedValueException;
use org\turbocommons\src\main\php\model\TableObject;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * XLSX data sheet abstraction
 */
class XlsxObject extends TableObject{


    /**
     * Contains the index for the xlsx sheet that is currently active on this class
     * @var integer
     */
    private $_activeSheet = -1;


    /**
     * Array containing table objects that represent each xlsx sheet
     *
     * @var TableObject[]
     */
    private $_sheets = [];


    /**
     * XlsxObject is a class to operate with excel xlsx data sheets.
     *
     * @param string $binary A string containing valid xlsx data. By default the XlsxObject will contain values for the first sheet.
     *
     * The php zip extension must be enabled in order for this class to work
     */
    public function __construct($binary = ''){

        parent::__construct();

        if(!StringUtils::isString($binary)){

            throw new UnexpectedValueException('constructor expects a string value');
        }

        // If no data is specified, the xls object will be letf as empty
        if(StringUtils::isEmpty($binary)){

            $this->_activeSheet = 0;
            return;
        }

        // Process the xlsx data as zip compressed
        $zip = new ZipObject();

        try {

            $zip->loadBinary($binary);

        } catch (Throwable $e) {

            throw new UnexpectedValueException('Not a valid xlsx file: '.$e->getMessage());
        }

        // Load the zip file contents
        $workBook = simplexml_load_string($zip->readEntry('xl/workbook.xml'));

        try {

            $strings = simplexml_load_string($zip->readEntry('xl/sharedStrings.xml'));

        } catch (Throwable $e) {

            $strings = null;
        }

        for ($i = 0; $i < count($workBook->sheets->sheet); $i++) {

            $sheet = simplexml_load_string($zip->readEntry('xl/worksheets/sheet'.($i + 1).'.xml'));
            $this->_loadSheet($sheet, $strings);
        }

        $this->setActiveSheet(0);

        $zip->close();
    }


    /**
     * Sets the index for the xlsx sheet that is currently active on this class
     *
     * @param int $index The index of the sheet to be set as active.
     *
     * @return True on success
     *
     * @throws UnexpectedValueException If the provided index is out of bounds.
     */
    public function setActiveSheet(int $index){

        if($index === $this->_activeSheet){

            return true;
        }

        if($index > count($this->_sheets)){

            throw new UnexpectedValueException('Index '.$index.' exceeds number of sheets');
        }

        // Reset the current xlsx table underlying data
        parent::__construct();

        $sheetRows = $this->_sheets[$index]->countRows();
        $sheetCols = $this->_sheets[$index]->countColumns();

        // Copy all the data on the sheet at the specified index to this actual xlsx instance
        if($sheetRows > 0 && $sheetCols > 0){

            parent::addRows($sheetRows);
            parent::addColumns($sheetCols);

            for ($i = 0; $i < $sheetRows; $i++) {

                for ($j = 0; $j < $sheetCols; $j++) {

                    parent::setCell($i, $j, $this->_sheets[$index]->getCell($i, $j));
                }
            }
        }

        $this->_activeSheet = $index;
        return true;
    }


    /**
     * Counts the number of sheets in the XLSX object.
     *
     * @return int The number of sheets in the XLSX object.
     */
    public function countSheets(){

        return count($this->_sheets);
    }


    /**
     * Check if the provided value contains valid CSV information.
     *
     * @param mixed $value Object to test for valid CSV data. Accepted values are: Strings containing CSV data or CSVObject elements
     *
     * @return boolean True if the received object represent valid CSV data. False otherwise.
     */
    public static function isXlsx($value){

        try {

            $c = new XlsxObject($value);

            return $c->countCells() >= 0;

        } catch (Throwable $e) {

            try {

                return ($value !== null) && (get_class($value) === 'org\\turbodepot\\src\\main\\php\\model\\XlsxObject');

            } catch (Throwable $e) {

                return false;
            }
        }
    }


    /**
     * Auxiliary method to load an XLSX sheet into the XlsxObject instance.
     *
     * @param SimpleXMLElement $sheet The XML representation of the XLSX sheet.
     * @param SimpleXMLElement $strings The XML representation of the shared strings in the XLSX file.
     *
     * @throws UnexpectedValueException If the provided sheet or strings XML is not valid.
     */
    private function _loadSheet(SimpleXMLElement $sheet, SimpleXMLElement $strings = null){

        $table = new TableObject();

        // Parse the rows
        foreach ($sheet->sheetData->row as $xlrow) {

            // Calculate current row index by removing all letters from the sheet row index (for example A1)
            $xlRowIndex = preg_replace("/^\D+/", "", (string)$xlrow->c['r']) - 1;

            if($xlRowIndex >= $table->countRows()){

                $table->addRows($xlRowIndex - $table->countRows() + 1);
            }

            // In each row, grab it's value
            foreach ($xlrow->c as $cell) {

                $xlColIndex = $this->_columnNameToIndex($cell['r']);

                if($xlColIndex >= $table->countColumns()){

                    $table->addColumns($xlColIndex - $table->countColumns() + 1);
                }

                $cellValue = (string) $cell->v;

                // If it has a "t" (type?) of "s" (string?), use the value to look up string value
                if (isset($cell['t']) && $cell['t'] == 's') {

                    $s  = [];
                    $si = $strings->si[(int) $cellValue];

                    // Register & alias the default namespace or you'll get empty results in the xpath query
                    $si->registerXPathNamespace('n', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

                    // Cat together all of the 't' (text?) node values
                    foreach($si->xpath('.//n:t') as $t) {

                        $s[] = (string) $t;
                    }

                    $cellValue = implode($s);
                }

                $table->setCell($xlRowIndex, $xlColIndex, $cellValue);
            }
        }

        $this->_sheets[] = $table;
    }


    /**
     * Converts a column name in XLSX format to its corresponding index.
     * Notice that the first index value is 0, different from xlsx that considers 1 to be the first index
     *
     * @param string $colName The column name in XLSX format (e.g., A, B, C, etc.)
     *
     * @return int The index of the column in the XLSX sheet.
     */
    private function _columnNameToIndex(string $colName){

        $nameProcessed = preg_replace('/\d+/u', '', $colName);

        $value = 0;
        $len = (strlen($nameProcessed)-1);

        for ($i = 0; $i <= $len; $i++) {

            $delta = intval(ord($nameProcessed[$i])  - 64);
            $value = intval($value*26)+ intval($delta);
        }

        return max($value - 1, 0);
    }
}