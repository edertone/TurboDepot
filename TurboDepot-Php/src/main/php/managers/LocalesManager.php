<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */


namespace org\turbodepot\src\main\php\managers;


use UnexpectedValueException;
use stdClass;
use org\turbocommons\src\main\php\model\JavaPropertiesObject;
use org\turbocommons\src\main\php\utils\ArrayUtils;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * LocalesManager class
 */
class LocalesManager {


    /**
     * if the class has been correctly initialized and translations have been correctly loaded
     */
    private $_isInitialized = false;


    /**
     * @see LocalesManager::getLocales()
     */
    private $_locales = [];


    /**
     * @see LocalesManager::getLanguages()
     */
    private $_languages = [];


    /**
     * A files manager instance that is used by this class
     * @var FilesManager
     */
    private $_filesManager = null;


    /**
     * Stores all the loaded localization data by library name, bundle name, key and locales
     */
    private $_loadedTranslations = [];


    /**
     * Stores a memory cache to improve performance when outputing translations
     * @var array
     */
    private $_keyValuesCache = [];


    /**
     * @see LocalesManager::setWildCardsFormat()
     * @var string
     */
    private $_wildCardsFormat = '{N}';


    /**
     * @see LocalesManager::setMissingKeyFormat()
     * @var string
     */
    private $_missingKeyFormat = '$exception';


    /**
     * Stores a hash value that is used to improve the performance for translation t() methods.
     * This is computed based on _wildCardsFormat plus _missingKeyFormat plus the current primary locale
     * Methods that change these values will recalculate the hash string, so when calling translation methods, the
     * performance will be as fast as possible.
     *
     * @var string
     */
    private $_cacheHashBaseString = '';


    /**
     * Fully featured translation manager to be used with any application that requires text internationalization.
     */
    public function __construct(){

        $this->_filesManager = new FilesManager();
    }


    /**
     * Wildcards are string fragments that are placed inside the translated texts. Their main purpose is to be replaced at
     * runtime by custom values like for example a user name, a date, a numeric value, etc..
     *
     * This class helps with this process by including a parameter called 'toReplace' on all ->t methods which allows us
     * to specify a string or list of strings that will replace the respective wildcards on the translated text. Each wildcard
     * must follow the format specified here, and contain a numeric digit that will be used to find the replacement text at the
     * 'toReplace' list. For example, if we define $N as the wildcard format, and we have a translation that contains $0, $1, $2,
     * $0 will be replaced with the first element on toReplace, $1 with the second and so.
     *
     * We usually set this before initializing the class translation data
     *
     * Notice that N is mandayory on the wildcards format and the first index value is 0.
     *
     * @param string $value The wildcards format we want to set
     *
     * @return string The value that's been set
     */
    public function setWildCardsFormat(string $value) {

        if(StringUtils::countStringOccurences($value, 'N') <= 0){

            throw new UnexpectedValueException("N is mandatory to replace wildcards");
        }

        $this->_cacheHashBaseString = $value.$this->_missingKeyFormat.(isset($this->_locales[0]) ? $this->_locales[0] : '');

         $this->_wildCardsFormat = $value;

         return $value;
    }


    /**
     * Defines the behaviour for get(), getStartCase(), etc... methods when a key is not found on
     * a bundle or the bundle does not exist
     *
     * If missingKeyFormat is an empty string, all missing keys will return an empty value (not recommended)
     *
     * If missingKeyFormat contains a string, that string will be always returned for missing keys
     *
     * If missingKeyFormat contains a string with one of the following predefined wildcards:<br>
     *    - $key will be replaced with key name. Example: get("NAME") will output [NAME] if key is not found and missingKeyFormat = '[$key]'<br>
     *    - $exception (default value) will throw an exception with the problem cause description.
     *
     * @param string $value The missing key format we want to set
     *
     * @return string The value that's been set
     */
    public function setMissingKeyFormat(string $value) {

        $this->_cacheHashBaseString = $this->_wildCardsFormat.$value.(isset($this->_locales[0]) ? $this->_locales[0] : '');

        $this->_missingKeyFormat = $value;

        return $value;
    }


    /**
     * @see LocalesManager::setMissingKeyFormat()
     * @var string
     */
    public function getMissingKeyFormat() {

        return $this->_missingKeyFormat;
    }


    /**
     * Initializes the translation system by loading and parsing bundle files for the specified source paths
     *
     * This method performs the following operations:
     * 1. Sets up the initial translation structure for each locale
     * 2. Scans provided paths for .properties files
     * 3. Organizes translations by library and bundle
     * 4. Loads and parses all property files for each library/bundle/locale combination
     *
     * @param array $locales An array of locale codes (e.g., ['en_US', 'es_ES', 'fr_FR']) to load
     * @param array $localesPaths An array of filesystem paths where translations are stored
     *
     * @throws UnexpectedValueException When something failed during the process
     *
     * @example
     * $translator->initialize(['en', 'es'], ['/path/to/locales', '/another/path']);
     *
     * @return void
     */
    public function initialize(array $locales, array $localesPaths) {

        ArrayUtils::forceNonEmptyArray($locales, 'locales');
        ArrayUtils::forceNonEmptyArray($localesPaths, 'localesPaths');

        // Validate received locales are correct
        foreach ($locales as $locale) {

            $this->_validateLocaleString($locale);
        }

        $this->_isInitialized = false;
        $this->_keyValuesCache = [];
        $this->_loadedTranslations = [];

        // Detect bundle structure and load translations
        foreach ($localesPaths as $localePath) {

            if(!is_dir($localePath)) {

                throw new UnexpectedValueException("Invalid path: $localePath");
            }

            $propertyFiles = $this->_filesManager->findDirectoryItems($localePath, '/.*\.properties$/i', 'absolute', 'files');

            if(empty($propertyFiles)) {

                throw new UnexpectedValueException("Path does not contain locales: $localePath");
            }

            // Build initial structure from property files
            foreach ($propertyFiles as $filePath) {

                $basePath = StringUtils::getPath($filePath);
                $library = StringUtils::getPathElement($filePath, -3);
                $bundle = StringUtils::getPathElement($filePath, -2);

                $this->_loadedTranslations[$library] ??= [];
                $this->_loadedTranslations[$library][$bundle] ??= [];

                // Load translations for each locale
                foreach ($locales as $locale) {

                    $bundleFilePath = $basePath.'/'.$bundle.'_'.$locale.'.properties';

                    if (is_file($bundleFilePath)) {

                        $translations = $this->parseProperties($this->_filesManager->readFile($bundleFilePath));

                        foreach ($translations as $key => $value) {

                            $this->_loadedTranslations[$library][$bundle][$locale][$key] = $value;
                        }
                    }
                }
            }
        }

        // Finalize initialization
        $this->_isInitialized = true;
        $this->_locales = $locales;
        $this->_languages = array_map(fn($l) => substr($l, 0, 2), $locales);
        $this->_cacheHashBaseString = $this->_wildCardsFormat.$this->_missingKeyFormat.$this->_locales[0];
    }


    /**
     * Auxiliary method that can be overriden when extending this class to customize the parsing of Java properties
     * formatted resource bundles
     *
     * @param string $propertiesString A string containing the read resourcebundle java properties format string
     */
    protected function parseProperties(string $propertiesString){

        $result = new stdClass();

        $javaPropertiesObject = new JavaPropertiesObject($propertiesString);

        foreach ($javaPropertiesObject->getKeys() as $key) {

            $result->$key = $javaPropertiesObject->get($key);
        }

        return $result;
    }


    /**
     * Check if the class has been correctly initialized and translations have been correctly loaded
     */
    public function isInitialized(){

        return $this->_isInitialized;
    }


    /**
     * Aux method to verify that this class is correctly initialized with translation data
     */
    private function _validateInitialized(){

        if(!$this->_isInitialized){

            throw new UnexpectedValueException('LocalesManager not initialized');
        }
    }


    /**
     * Obtain a json structure containing all the translation data that is currently loaded on this class
     * Notice that class must be initialized
     *
     * @return string The textual json representation of the loaded translations
     */
    public function getLoadedLocalesAsJson(){

        $this->_validateInitialized();

        // Generate a json structure with all the loaded translations
        $result = json_encode($this->_loadedTranslations);

        if($result === false){

            throw new UnexpectedValueException('Could not generate Json from loaded translations');
        }

        return $result;
    }


    /**
     * Checks if the specified locale is currently loaded for the currently defined bundles and paths.
     *
     * @param string $locale A locale to check. For example 'en_US'
     *
     * @return boolean True if the locale is currently loaded on the class, false if not.
     */
    public function isLocaleLoaded(string $locale){

        $this->_validateLocaleString($locale);

        return in_array($locale, $this->_locales);
    }


    /**
     * Aux method to validate that a locale string is correctly formatted
     *
     * @param string $locale A locale string
     */
    private function _validateLocaleString(string $locale){

        if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale)) {

            throw new UnexpectedValueException('locale must be a valid xx_XX value');
        }
    }


    /**
     * Checks if the specified 2 digit language is currently loaded for the currently defined bundles and paths.
     *
     * @param string $language A language to check. For example 'en'
     *
     * @return boolean True if the language is currently loaded on the class, false if not.
     */
    public function isLanguageLoaded(string $language){

        $this->_validateLanguageString($language);

        return in_array($language, $this->_languages);
    }


    /**
     * Aux method to validate that a language string is correctly formatted
     *
     * @param string $language A 2 digit language string
     */
    private function _validateLanguageString(string $language){

        if (!preg_match('/^[a-z]{2}$/', $language)) {

            throw new UnexpectedValueException('language must be a valid 2 digit value');
        }
    }


    /**
     * Get the translation to the current primary locale for the given key, library and bundle
     *
     * @param string $key The key we want to read from the specified resource bundle
     * @param string $bundlePath A string with the format 'library_name/bundle_name' that is used to locate the bundle were the key to translate is found
     * @param array $replaceWildcards A list of values that will replace wildcards that may be found on the translated text. Each wildcard
     *        will be replaced with the element whose index on $replaceWildcards matches it. Check the documentation for this.setWildCardsFormat
     *        property to know more about how to setup wildcards on your translations.
     *
     * @see LocalesManager::setWildCardsFormat()
     *
     * @return string The translated text
     */
    public function t($key, $bundlePath, array $replaceWildcards = []) {

        $this->_validateInitialized();

        // Create a cache key to improve performance when requesting the same key translation several times
        $cacheKey = $this->_cacheHashBaseString.$key.$bundlePath.implode('', $replaceWildcards);

        if(!isset($this->_keyValuesCache[$cacheKey])){

            StringUtils::forceNonEmptyString($key, '', 'key must be non empty string');
            StringUtils::forceNonEmptyString($bundlePath, '', 'bundlePath must be non empty string');

            list($library, $bundle) = explode('/', $bundlePath);

            StringUtils::forceNonEmptyString($library, '', 'no library specified on bundlePath');
            StringUtils::forceNonEmptyString($bundle, '', 'no bundle specified on bundlePath');

            $replacementsCount = count($replaceWildcards);

            // Loop all the locales to find the first one with a value for the specified key
            foreach ($this->_locales as $locale) {

                if(isset($this->_loadedTranslations[$library][$bundle][$locale][$key])){

                    $result = $this->_loadedTranslations[$library][$bundle][$locale][$key];

                    // Replace all wildcards on the text with the specified replacements if any
                    for ($i = 0; $i < $replacementsCount; $i++) {

                        $result = StringUtils::replace($result, StringUtils::replace($this->_wildCardsFormat, 'N', $i), $replaceWildcards[$i]);
                    }

                    $this->_keyValuesCache[$cacheKey] = $result;

                    return $result;
                }
            }

            // Check if an exception needs to be thrown if the specified key is not found on this bundle
            if (strpos($this->_missingKeyFormat, '$exception') !== false) {

                throw new UnexpectedValueException("key <$key> not found on $bundlePath");
            }

            $this->_keyValuesCache[$cacheKey] = StringUtils::replace($this->_missingKeyFormat, '$key', $key);
        }

        return $this->_keyValuesCache[$cacheKey];
    }


    /**
     * Get the translation for the given key and bundle as a string with all words first character capitalized
     * and all the rest of the word with lower case
     *
     * @see LocalesManager::t
     * @see StringUtils::formatCase
     *
     * @return string The localized and case formatted text
     */
    public function tStartCase($key, $bundlePath, array $replaceWildcards = []) {

        return mb_convert_case($this->t($key, $bundlePath, $replaceWildcards), MB_CASE_TITLE);
    }


    /**
     * Get the translation for the given key and bundle as an all upper case string
     *
     * @see LocalesManager::t
     * @see StringUtils::formatCase
     *
     * @return string The localized and case formatted text
     */
    public function tAllUpperCase($key, $bundlePath, array $replaceWildcards = []) {

        return mb_strtoupper($this->t($key, $bundlePath, $replaceWildcards));
    }


    /**
     * Get the translation for the given key and bundle as an all lower case string
     *
     * @see LocalesManager::t
     * @see StringUtils::formatCase
     *
     * @return string The localized and case formatted text
     */
    public function tAllLowerCase($key, $bundlePath, array $replaceWildcards = []) {

        return mb_strtolower($this->t($key, $bundlePath, $replaceWildcards));
    }


    /**
     * Get the translation for the given key and bundle as a string with the first character as Upper case
     * and all the rest as lower case
     *
     * @see LocalesManager::t
     * @see StringUtils::formatCase
     *
     * @return string The localized and case formatted text
     */
    public function tFirstUpperRestLower($key, $bundlePath, array $replaceWildcards = []){

        $string = $this->t($key, $bundlePath, $replaceWildcards);

        return mb_strtoupper(mb_substr($string, 0, 1)).mb_substr(mb_strtolower($string), 1);
    }


    /**
     * A list of strings containing the locales that are used by this class to translate the given keys, sorted by preference.
     * Each string is formatted as a standard locale code with language and country joined by an underscore, like: en_US, fr_FR
     *
     * When a key and bundle are requested for translation, the class will check on the first language of this
     * list for a translated text. If missing, the next one will be used, and so. This list is constructed after initialize
     * methods is called.
     *
     * @example: After loading the following list of locales ['en_US', 'es_ES', 'fr_FR'] if we call LocalesManager.t('HELLO', 'lib1/greetings')
     * the localization manager will try to locate the en_US value for the HELLO tag on the greetings bundle for the library lib1.
     * If the tag is not found for the specified locale and bundle, the same search will be performed for the es_ES locale, and so, till a
     * value is found or no more locales are defined.
     */
    public function getLocales(){

        return $this->_locales;
    }


    /**
     * A list of strings containing the languages that are used by this class to translate the given keys, sorted by preference.
     * Each string is formatted as a 2 digit language code, like: en, fr
     *
     * This list is the same as the locales() one, but containing only the language part of each locale (the first two digits)
     *
     * @see LocalesManager::getLocales()
     */
    public function getLanguages(){

        return $this->_languages;
    }


    /**
     * Get the first locale from the list of loaded locales, which is the currently used to search for translated texts.
     *
     * @return string The locale that is defined as the primary one. For example: en_US, es_ES, ..
     */
    public function getPrimaryLocale(){

        $this->_validateInitialized();

        return $this->_locales[0];
    }


    /**
     * Get the first language from the list of loaded locales, which is the currently used to search for translated texts.
     *
     * @return string The 2 digit language code that is defined as the primary one. For example: en, es, ..
     */
    public function getPrimaryLanguage(){

        $this->_validateInitialized();

        return $this->_languages[0];
    }


    /**
     * Define the locale that will be placed at the front of the currently loaded locales list (moving all the others one position to the right).
     *
     * This will be the first locale to use when trying to get a translation.
     *
     * @param string $locale A currently loaded locale that will be moved to the first position of the loaded locales list. If the specified locale
     *        is not currently loaded, an exception will happen.
     *
     * @return void
     */
    public function setPrimaryLocale(string $locale){

        $this->_validateInitialized();

        if(!$this->isLocaleLoaded($locale)){

            throw new UnexpectedValueException($locale.' not loaded');
        }

        $result = [$locale];

        foreach ($this->_locales as $l) {

            if($l !== $locale){

                $result[] = $l;
            }
        }

        $this->_locales = $result;
        $this->_languages = array_map(function ($l) {return substr($l, 0, 2);}, $this->_locales);
        $this->_cacheHashBaseString = $this->_wildCardsFormat.$this->_missingKeyFormat.$this->_locales[0];
    }


    /**
     * Moves the specified locales to the beginning of the locales list. This also alters the translation priority by setting the first
     * provided locale as the most prioritary, the second as the next one and so.
     *
     * This method basically works exactly the same way as setPrimaryLocale but letting us add many locales at once.
     *
     * @see LocalesManager::setPrimaryLocale()
     *
     * @param array $locales A list of locales to be moved to the beginning of the translation priority. First locales item will be the prefered
     *        locale for translation, second will be the next one in case some key is not translated for the first one and so. If any of the
     *        specified locales is not currently loaded, an exception will happen.
     *
     * @return void
     */
    public function setPrimaryLocales(array $locales){

        if(!ArrayUtils::isArray($locales) ||
            ArrayUtils::hasDuplicateElements($locales) ||
            empty($locales)){

                throw new UnexpectedValueException('locales must be non empty string array with no duplicate elements');
        }

        for ($i = count($locales) - 1; $i >= 0; $i--) {

            $this->setPrimaryLocale($locales[$i]);
        }
    }


    /**
     * Define the 2 digit language that will be placed at the front of the currently loaded locales list (moving all the others one position to the right).
     *
     * This will be the first language to use when trying to get a translation.
     *
     * @param string $language A 2 digit language code that matches with any of the currently loaded locales, which will
     *        be moved to the first position of the loaded locales list. If the specified language does not match with
     *        a locale that is currently loaded, an exception will happen.
     *
     * @return void
     */
    public function setPrimaryLanguage(string $language){

        foreach ($this->_locales as $locale) {

            if(substr($locale, 0, 2) === $language){

                $this->setPrimaryLocale($locale);

                return;
            }
        }

        throw new UnexpectedValueException($language.' not loaded');
    }


    /**
     * Moves the locales that match the specified languages to the beginning of the locales list.
     * Works the same as setPrimaryLocales() but with a list of the 2 digit language codes that match the respective locales.
     *
     * @see LocalesManager::setPrimaryLocale()
     * @see LocalesManager::setPrimaryLanguage()
     *
     * @param array $languages A list of 2 digit language codes to be moved to the beginning of the translation priority. If any of the
     *        specified languages does not match with a locale that is currently loaded, an exception will happen.
     *
     * @return void
     */
    public function setPrimaryLanguages(array $languages){

        if(!ArrayUtils::isArray($languages) ||
            ArrayUtils::hasDuplicateElements($languages) ||
            empty($languages)){

            throw new UnexpectedValueException('languages must be non empty string array with no duplicate elements');
        }

        for ($i = count($languages) - 1; $i >= 0; $i--) {

            $this->setPrimaryLanguage($languages[$i]);
        }
    }


    /**
     * Change the loaded locales translation preference order. The same locales that are currently loaded must be passed
     * but with a different order to change the translation priority.
     *
     * @param array $locales A list with the new locales translation priority
     *
     * @return void
     */
    public function setLocalesOrder(array $locales){

        if(count($locales) !== count($this->_locales)){

            throw new UnexpectedValueException('locales must contain all the currently loaded locales');
        }

        $this->_validateInitialized();

        foreach ($locales as $locale) {

            if(!$this->isLocaleLoaded($locale)){

                throw new UnexpectedValueException($locale.' not loaded');
            }
        }

        $this->_locales = $locales;
        $this->_languages = array_map(function ($l) {return substr($l, 0, 2);}, $this->_locales);
        $this->_cacheHashBaseString = $this->_wildCardsFormat.$this->_missingKeyFormat.$this->_locales[0];
    }
}
