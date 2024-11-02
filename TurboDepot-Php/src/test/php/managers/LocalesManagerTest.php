<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> https://turboframework.org/en/libs/turbodepot
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */

namespace org\turbodepot\src\test\php\managers;


use PHPUnit\Framework\TestCase;
use org\turbodepot\src\main\php\managers\LocalesManager;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbocommons\src\main\php\utils\ArrayUtils;


/**
 * test
 */
class LocalesManagerTest extends TestCase {


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(){

        // Create some pointers to useful paths
        $this->basePath = __DIR__.'/../../resources/managers/localesManager/';

        $this->sut = new LocalesManager();
    }


    /**
     * test
     * @return void
     */
    public function testConstruct(){

        $this->assertTrue($this->sut instanceof LocalesManager);
    }


    /**
     * test
     * @return void
     */
    public function testSetWildCardsFormat(){

        AssertUtils::throwsException(function() { $this->sut->setWildCardsFormat(''); }, '/N is mandatory to replace wildcards/');
        AssertUtils::throwsException(function() { $this->sut->setWildCardsFormat('hello'); }, '/N is mandatory to replace wildcards/');

        $this->assertSame('{N}', $this->sut->setWildCardsFormat('{N}'));
        $this->assertSame('--N--', $this->sut->setWildCardsFormat('--N--'));
    }


    /**
     * test
     * @return void
     */
    public function testSetMissingKeyFormat(){

        $this->assertSame('', $this->sut->setMissingKeyFormat(''));
        $this->assertSame('$key', $this->sut->setMissingKeyFormat('$key'));
        $this->assertSame('$exception', $this->sut->setMissingKeyFormat('$exception'));
    }


    /**
     * test
     * @return void
     */
    public function testGetMissingKeyFormat(){

        $this->assertSame('', $this->sut->setMissingKeyFormat(''));
        $this->assertSame('', $this->sut->getMissingKeyFormat());

        $this->assertSame('$key', $this->sut->setMissingKeyFormat('$key'));
        $this->assertSame('$key', $this->sut->getMissingKeyFormat());

        $this->assertSame('$exception', $this->sut->setMissingKeyFormat('$exception'));
        $this->assertSame('$exception', $this->sut->getMissingKeyFormat());
    }


    /**
     * test
     * @return void
     */
    public function testInitialize(){

        // Test empty parameters
        AssertUtils::throwsException(function() { $this->sut->initialize([], []); }, '/locales must be a non empty array/');
        AssertUtils::throwsException(function() { $this->sut->initialize(['es_ES'], []); }, '/localesPaths must be a non empty array/');

        // Loading a path that does not contain translations must throw an exception
        AssertUtils::throwsException(function() { $this->sut->initialize(['es_ES'], [$this->basePath.'invalid-lib']); }, '/Path does not contain locales.*invalid-lib/');

        // Test loading one locale from a single valid locales library
        $this->sut->initialize(['es_ES'], [$this->basePath.'libname-1']);
        $this->assertSame('Fecha', $this->sut->t('DATE', 'libname-1/calendar'));
        $this->assertSame('Acerca de', $this->sut->t('ABOUT', 'libname-1/user-interface'));

        // Test loading two locales from a single valid locales library
        $this->sut->initialize(['en_US', 'es_ES'], [$this->basePath.'libname-1']);
        $this->assertSame('Date', $this->sut->t('DATE', 'libname-1/calendar'));
        $this->assertSame('About', $this->sut->t('ABOUT', 'libname-1/user-interface'));

        // Test loading two locales from two different valid locales library
        $this->sut->initialize(['en_US', 'es_ES'], [$this->basePath.'libname-1', $this->basePath.'libname-2']);
        $this->assertSame('Date', $this->sut->t('DATE', 'libname-1/calendar'));
        $this->assertSame('About', $this->sut->t('ABOUT', 'libname-1/user-interface'));
        $this->assertSame('Access', $this->sut->t('ACCESS', 'libname-2/users'));

        // Test loading all libraries from the root of libraries path
        $this->sut->initialize(['es_ES', 'en_US'], [$this->basePath]);
        $this->assertSame('Fecha', $this->sut->t('DATE', 'libname-1/calendar'));
        $this->assertSame('Acerca de', $this->sut->t('ABOUT', 'libname-1/user-interface'));
        $this->assertSame('Acceder', $this->sut->t('ACCESS', 'libname-2/users'));

        // Wrong values
        AssertUtils::throwsException(function() { $this->sut->initialize(['invalid'], [$this->basePath.'libname-1']); }, '/locale must be a valid xx_XX value/');
        AssertUtils::throwsException(function() { $this->sut->initialize([24], [$this->basePath.'libname-1']); }, '/locale must be a valid xx_XX value/');
        AssertUtils::throwsException(function() { $this->sut->initialize(['es_ES'], ['invalid path']); }, '/Invalid path: invalid path/');
        AssertUtils::throwsException(function() { $this->sut->initialize([234], [234]); }, '/locale must be a valid xx_XX value/');
    }


    /**
     * test
     * @return void
     */
    public function testInitialize_secondth_time_resets_state(){

        // Init a first time with es language
        $this->sut->initialize(['es_ES'], [$this->basePath]);
        $this->assertSame('es_ES', $this->sut->getPrimaryLocale());

        // Init again with en language
        $this->sut->initialize(['en_US'], [$this->basePath]);
        $this->assertSame('en_US', $this->sut->getPrimaryLocale());

    }


    public function testIsInitialized(){

        $this->assertFalse($this->sut->isInitialized());

        $this->sut->initialize(['es_ES', 'en_US'], [$this->basePath]);

        $this->assertTrue($this->sut->isInitialized());

    }


    public function testGetLoadedLocalesAsJson(){

        AssertUtils::throwsException(function() { $this->sut->getLoadedLocalesAsJson(); }, '/LocalesManager not initialized/');

        // Test converting data to json for a single locale
        $this->sut->initialize(['es_ES'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);
        $this->assertSame('{"lib-multilocales":{"multi":{"es_ES":{"LOGIN":"Acceder"}}}}', $this->sut->getLoadedLocalesAsJson());

        // Test converting data to json for two locales
        $this->sut->initialize(['es_ES', 'en_US'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);
        $this->assertSame('{"lib-multilocales":{"multi":{"es_ES":{"LOGIN":"Acceder"},"en_US":{"LOGIN":"Access US"}}}}', $this->sut->getLoadedLocalesAsJson());

        // Test converting data to json for three locales
        $this->sut->initialize(['es_ES', 'en_US', 'fr_FR'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);
        $this->assertSame('{"lib-multilocales":{"multi":{"es_ES":{"LOGIN":"Acceder"},"en_US":{"LOGIN":"Access US"},"fr_FR":{"LOGIN":"Accedua"}}}}', $this->sut->getLoadedLocalesAsJson());
    }


    public function testIsLocaleLoaded(){

        AssertUtils::throwsException(function() { $this->sut->isLocaleLoaded(''); }, '/locale must be a valid xx_XX value/');

        $this->assertFalse($this->sut->isLocaleLoaded('es_ES'));
        $this->assertFalse($this->sut->isLocaleLoaded('en_US'));
        $this->assertFalse($this->sut->isLocaleLoaded('es_AS'));

        $this->sut->initialize(['es_ES', 'en_US'], [$this->basePath]);

        AssertUtils::throwsException(function() { $this->sut->isLocaleLoaded(''); }, '/locale must be a valid xx_XX value/');

        $this->assertTrue($this->sut->isLocaleLoaded('es_ES'));
        $this->assertTrue($this->sut->isLocaleLoaded('en_US'));
        $this->assertFalse($this->sut->isLocaleLoaded('es_AS'));
    }


    public function testIsLanguageLoaded(){

        AssertUtils::throwsException(function() { $this->sut->isLanguageLoaded(''); }, '/language must be a valid 2 digit value/');

        $this->assertFalse($this->sut->isLanguageLoaded('es'));
        $this->assertFalse($this->sut->isLanguageLoaded('en'));
        $this->assertFalse($this->sut->isLanguageLoaded('as'));

        $this->sut->initialize(['es_ES', 'en_US'], [$this->basePath]);

        AssertUtils::throwsException(function() { $this->sut->isLanguageLoaded(''); }, '/language must be a valid 2 digit value/');

        $this->assertTrue($this->sut->isLanguageLoaded('es'));
        $this->assertTrue($this->sut->isLanguageLoaded('en'));
        $this->assertFalse($this->sut->isLanguageLoaded('as'));
    }


    public function testT(){

        $this->sut->initialize(['es_ES'], [$this->basePath]);

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->t(null, null, []); }, '/key must be non empty string/');
        AssertUtils::throwsException(function() { $this->sut->t('', '', []); }, '/key must be non empty string/');
        AssertUtils::throwsException(function() { $this->sut->t(0, 0, []); }, '/key must be non empty string/');

        // Test ok values
        $this->sut->initialize(['es_ES'], [$this->basePath]);
        $this->assertSame('Agosto', $this->sut->t('AUGUST', 'libname-1/calendar'));

        $this->sut->initialize(['en_US'], [$this->basePath]);
        $this->assertSame('August', $this->sut->t('AUGUST', 'libname-1/calendar'));

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->t('AUGUST', 'nonexistantlib/calendar'); }, '/key <AUGUST> not found on nonexistantlib.calendar/');
        AssertUtils::throwsException(function() { $this->sut->t('AUGUST', 'libname-1/nonexistant'); }, '/key <AUGUST> not found on libname-1.nonexistant/');
        AssertUtils::throwsException(function() { $this->sut->t('NOTTOBEFOUND', 'libname-1/calendar'); }, '/key <NOTTOBEFOUND> not found on libname-1.calendar/');
    }


    /**
     * test
     *
     * @return void
     */
    public function testT_non_initialized(){

        $this->assertSame('$exception', $this->sut->getMissingKeyFormat());

        AssertUtils::throwsException(function() { $this->sut->t('KEY', 'bla/bla'); }, '/LocalesManager not initialized/');

        $this->sut->missingKeyFormat = '';
        AssertUtils::throwsException(function() { $this->sut->t('KEY', 'bla/bla'); }, '/LocalesManager not initialized/');

        $this->sut->missingKeyFormat = '--$key--';
        AssertUtils::throwsException(function() { $this->sut->t('KEY', 'bla/bla'); }, '/LocalesManager not initialized/');


        $this->sut->missingKeyFormat = '<$key>';
        AssertUtils::throwsException(function() { $this->sut->t('KEY', 'bla/bla'); }, '/LocalesManager not initialized/');
    }


    /**
     * test
     *
     * @return void
     */
    public function testT_initialized_missing_values(){

        $this->sut->initialize(['en_US'], [$this->basePath]);

        // Test missingKeyFormat with $exception wildcard
        $this->assertSame('$exception', $this->sut->getMissingKeyFormat());

        AssertUtils::throwsException(function() { $this->sut->t("MISSINGKEY", 'libname-1/calendar'); }, '/key <MISSINGKEY> not found/');
        AssertUtils::throwsException(function() { $this->sut->t("MISSINGKEY", 'libname-1/missingbundle'); }, '/key <MISSINGKEY> not found/');


        // Test empty missingKeyFormat
        $this->sut->setMissingKeyFormat('');
        $this->assertSame('', $this->sut->t("MISSINGKEY", 'libname-1/calendar'));
        $this->assertSame('', $this->sut->t("MISSINGKEY", 'libname-1/missingbundle'));

        // Test missingKeyFormat with some text
        $this->sut->setMissingKeyFormat('sometext');
        $this->assertSame('sometext', $this->sut->t("MISSINGKEY", 'libname-1/calendar'));
        $this->assertSame('sometext', $this->sut->t("MISSINGKEY", 'libname-1/missingbundle'));

        // Test missingKeyFormat with $key wildcard
        $this->sut->setMissingKeyFormat('--$key--');
        $this->assertSame('--MISSINGKEY--', $this->sut->t("MISSINGKEY", 'libname-1/calendar'));
        $this->assertSame('--MISSINGKEY--', $this->sut->t("MISSINGKEY", 'libname-1/missingbundle'));

        $this->sut->setMissingKeyFormat('<$key>');
        $this->assertSame('<MISSINGKEY>', $this->sut->t("MISSINGKEY", 'libname-1/calendar'));
        $this->assertSame('<MISSINGKEY>', $this->sut->t("MISSINGKEY", 'libname-1/missingbundle'));
    }


    /**
     * test
     *
     * @return void
     */
    public function testT_initialized_correct_values_with_single_locale_loaded(){

        $this->sut->initialize(['en_US'], [$this->basePath]);

        $this->assertSame('Missing tag', $this->sut->t("TAG_NOT_EXISTING_ON_ES_ES", 'lib-incomplete/multi'));
        $this->assertSame('Password US', $this->sut->t("PASSWORD", 'lib-incomplete/multi'));

        $this->sut->initialize(['en_US'], [$this->basePath.DIRECTORY_SEPARATOR.'libname-1']);
        $this->assertSame('December', $this->sut->t("DECEMBER", 'libname-1/calendar'));
        $this->assertSame('Minute', $this->sut->t("MINUTE", 'libname-1/calendar'));

        $this->sut->initialize(['es_ES'], [$this->basePath.DIRECTORY_SEPARATOR.'libname-1']);
        $this->assertSame('Diciembre', $this->sut->t("DECEMBER", 'libname-1/calendar'));
        $this->assertSame('Minuto', $this->sut->t("MINUTE", 'libname-1/calendar'));
    }


    /**
     * test
     *
     * @return void
     */
    public function testT_initialized_keys_from_another_bundle_fail(){

        $this->sut->initialize(['en_US'], [$this->basePath.DIRECTORY_SEPARATOR.'libname-2']);

        AssertUtils::throwsException(function() { $this->sut->t("MINUTE", 'libname-1/calendar'); }, '/key <MINUTE> not found/');

        // Dummy assert to avoid phpunit warnings
        $this->assertTrue(true);
    }


    /**
     * test
     *
     * @return void
     */
    public function testT_initialized_values_for_multiple_locales(){

        $this->sut->initialize(['es_ES', 'en_US'], [$this->basePath]);

        $this->assertSame('Missing tag', $this->sut->t("TAG_NOT_EXISTING_ON_ES_ES", 'lib-incomplete/multi'));
        $this->assertSame('Contraseña', $this->sut->t("PASSWORD", 'lib-incomplete/multi'));
    }


    /**
     * test
     *
     * @return void
     */
    public function testT_initialized_keys_from_multiple_libs_bundles_and_locales(){

        $this->sut->initialize(['es_ES', 'en_US'], [$this->basePath]);

        $this->assertSame('Missing tag', $this->sut->t("TAG_NOT_EXISTING_ON_ES_ES", 'lib-incomplete/multi'));
        $this->assertSame('Minuto', $this->sut->t("MINUTE", 'libname-1/calendar'));
        $this->assertSame('Usuario', $this->sut->t("USER", 'libname-2/users'));
    }


    /**
     * test
     *
     * @return void
     */
    public function testTStartCase(){

        $this->sut->initialize(['en_US'], [$this->basePath]);

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->tStartCase(null, null, []); }, '/key must be non empty string/');
        AssertUtils::throwsException(function() { $this->sut->tStartCase('', '', []); }, '/key must be non empty string/');
        AssertUtils::throwsException(function() { $this->sut->tStartCase(0, 0, []); }, '/key must be non empty string/');

        // Test ok values
        $this->assertSame('H', $this->sut->tStartCase("H", 'lib-strange-chars/bundle'));
        $this->assertSame('Hello', $this->sut->tStartCase("HELLO", 'lib-strange-chars/bundle'));
        $this->assertSame('Helló. Únder Ü??', $this->sut->tStartCase("HELLO_UNDER", 'lib-strange-chars/bundle'));
        $this->assertSame('Hello People', $this->sut->tStartCase("MIXED_CASE", 'lib-strange-chars/bundle'));
        $this->assertSame('Word1 Word2 Word3 Word4 Word5', $this->sut->tStartCase("MULTIPLE_WORDS", 'lib-strange-chars/bundle'));
        $this->assertSame('Óyeà!!! Üst??', $this->sut->tStartCase("SOME_ACCENTS", 'lib-strange-chars/bundle'));
    }


    /**
     * test
     *
     * @return void
     */
    public function testTAllUpperCase(){

        $this->sut->initialize(['en_US'], [$this->basePath]);

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->tAllUpperCase(null, null, []); }, '/key must be non empty string/');
        AssertUtils::throwsException(function() { $this->sut->tAllUpperCase('', '', []); }, '/key must be non empty string/');
        AssertUtils::throwsException(function() { $this->sut->tAllUpperCase(0, 0, []); }, '/key must be non empty string/');

        // Test ok values
        $this->assertSame('H', $this->sut->tAllUpperCase("H", 'lib-strange-chars/bundle'));
        $this->assertSame('HELLO', $this->sut->tAllUpperCase("HELLO", 'lib-strange-chars/bundle'));
        $this->assertSame('HELLÓ. ÚNDER Ü??', $this->sut->tAllUpperCase("HELLO_UNDER", 'lib-strange-chars/bundle'));
        $this->assertSame('HELLO PEOPLE', $this->sut->tAllUpperCase("MIXED_CASE", 'lib-strange-chars/bundle'));
        $this->assertSame('WORD1 WORD2 WORD3 WORD4 WORD5', $this->sut->tAllUpperCase("MULTIPLE_WORDS", 'lib-strange-chars/bundle'));
        $this->assertSame('ÓYEÀ!!! ÜST??', $this->sut->tAllUpperCase("SOME_ACCENTS", 'lib-strange-chars/bundle'));
    }


    /**
     * test
     *
     * @return void
     */
    public function testTAllLowerCase(){

        $this->sut->initialize(['en_US'], [$this->basePath]);

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->tAllLowerCase(null, null, []); }, '/key must be non empty string/');
        AssertUtils::throwsException(function() { $this->sut->tAllLowerCase('', '', []); }, '/key must be non empty string/');
        AssertUtils::throwsException(function() { $this->sut->tAllLowerCase(0, 0, []); }, '/key must be non empty string/');

        // Test ok values
        $this->assertSame('h', $this->sut->tAllLowerCase("H", 'lib-strange-chars/bundle'));
        $this->assertSame('hello', $this->sut->tAllLowerCase("HELLO", 'lib-strange-chars/bundle'));
        $this->assertSame('helló. únder ü??', $this->sut->tAllLowerCase("HELLO_UNDER", 'lib-strange-chars/bundle'));
        $this->assertSame('hello people', $this->sut->tAllLowerCase("MIXED_CASE", 'lib-strange-chars/bundle'));
        $this->assertSame('word1 word2 word3 word4 word5', $this->sut->tAllLowerCase("MULTIPLE_WORDS", 'lib-strange-chars/bundle'));
        $this->assertSame('óyeà!!! üst??', $this->sut->tAllLowerCase("SOME_ACCENTS", 'lib-strange-chars/bundle'));
    }


    /**
     * test
     *
     * @return void
     */
    public function testTFirstUpperRestLower(){

        $this->sut->initialize(['en_US'], [$this->basePath]);

        // Test empty values
        AssertUtils::throwsException(function() { $this->sut->tFirstUpperRestLower(null, null, []); }, '/key must be non empty string/');
        AssertUtils::throwsException(function() { $this->sut->tFirstUpperRestLower('', '', []); }, '/key must be non empty string/');
        AssertUtils::throwsException(function() { $this->sut->tFirstUpperRestLower(0, 0, []); }, '/key must be non empty string/');

        // Test ok values
        $this->assertSame('H', $this->sut->tFirstUpperRestLower("H", 'lib-strange-chars/bundle'));
        $this->assertSame('Hello', $this->sut->tFirstUpperRestLower("HELLO", 'lib-strange-chars/bundle'));
        $this->assertSame('Helló. únder ü??', $this->sut->tFirstUpperRestLower("HELLO_UNDER", 'lib-strange-chars/bundle'));
        $this->assertSame('Hello people', $this->sut->tFirstUpperRestLower("MIXED_CASE", 'lib-strange-chars/bundle'));
        $this->assertSame('Word1 word2 word3 word4 word5', $this->sut->tFirstUpperRestLower("MULTIPLE_WORDS", 'lib-strange-chars/bundle'));
        $this->assertSame('Óyeà!!! üst??', $this->sut->tFirstUpperRestLower("SOME_ACCENTS", 'lib-strange-chars/bundle'));
    }


    /**
     * test
     *
     * @return void
     */
    public function testT_with_wildcards(){

        $this->sut->initialize(['en_US', 'es_ES'], [$this->basePath]);

        $this->assertSame('this has no wildcards', $this->sut->t("TAG_1", 'lib-wildcards/wildcards'));
        $this->assertSame('this has no wildcards', $this->sut->t("TAG_1", 'lib-wildcards/wildcards', []));
        $this->assertSame('this has no wildcards', $this->sut->t("TAG_1", 'lib-wildcards/wildcards', ['test']));

        $this->sut->setPrimaryLocale('es_ES');
        $this->assertSame($this->sut->getLocales(), ['es_ES', 'en_US']);

        $this->assertSame('ésta no tiene wildcards', $this->sut->t("TAG_1", 'lib-wildcards/wildcards'));
        $this->assertSame('ésta no tiene wildcards', $this->sut->t("TAG_1", 'lib-wildcards/wildcards', []));
        $this->assertSame('ésta no tiene wildcards', $this->sut->t("TAG_1", 'lib-wildcards/wildcards', ['test']));

        $this->sut->setPrimaryLocale('en_US');
        $this->assertSame($this->sut->getLocales(), ['en_US', 'es_ES']);

        $this->assertSame('this has {0}', $this->sut->t("TAG_2", 'lib-wildcards/wildcards'));
        $this->assertSame('this has {0}', $this->sut->t("TAG_2", 'lib-wildcards/wildcards', []));
        $this->assertSame('this has replace', $this->sut->t("TAG_2", 'lib-wildcards/wildcards', ['replace']));
        $this->assertSame('this has 1', $this->sut->t("TAG_2", 'lib-wildcards/wildcards', ['1', '2', '3']));

        $this->sut->setPrimaryLocale('es_ES');
        $this->assertSame($this->sut->getLocales(), ['es_ES', 'en_US']);

        $this->assertSame('ésta tiene {0}', $this->sut->t("TAG_2", 'lib-wildcards/wildcards'));
        $this->assertSame('ésta tiene {0}', $this->sut->t("TAG_2", 'lib-wildcards/wildcards', []));
        $this->assertSame('ésta tiene replace', $this->sut->t("TAG_2", 'lib-wildcards/wildcards', ['replace']));
        $this->assertSame('ésta tiene 1', $this->sut->t("TAG_2", 'lib-wildcards/wildcards', ['1', '2', '3']));

        $this->sut->setPrimaryLocale('en_US');
        $this->assertSame($this->sut->getLocales(), ['en_US', 'es_ES']);

        $this->assertSame('this has {0} {1} {2}', $this->sut->t("TAG_3", 'lib-wildcards/wildcards'));
        $this->assertSame('this has {0} {1} {2}', $this->sut->t("TAG_3", 'lib-wildcards/wildcards', []));
        $this->assertSame('this has replace {1} {2}', $this->sut->t("TAG_3", 'lib-wildcards/wildcards', ['replace']));
        $this->assertSame('this has replace replace {2}', $this->sut->t("TAG_3", 'lib-wildcards/wildcards', ['replace', 'replace']));
        $this->assertSame('this has 1 2 3', $this->sut->t("TAG_3", 'lib-wildcards/wildcards', ['1', '2', '3']));
        $this->assertSame('this has 1 2 3', $this->sut->t("TAG_3", 'lib-wildcards/wildcards', ['1', '2', '3', '4']));
        $this->assertSame('this has 1  3', $this->sut->t("TAG_3", 'lib-wildcards/wildcards', ['1', '', '3']));

        $this->assertSame('some $2 custom $0 format $1', $this->sut->t("TAG_4", 'lib-wildcards/wildcards'));
        $this->assertSame('some $2 custom $0 format $1', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['1', '2', '3']));
        $this->assertSame('some $2 custom $0 format $1', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['1', '2', '3', '4']));

        $this->sut->setWildCardsFormat('$N');

        $this->assertSame('some $2 custom $0 format $1', $this->sut->t("TAG_4", 'lib-wildcards/wildcards'));
        $this->assertSame('some $2 custom $0 format $1', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', []));
        $this->assertSame('some $2 custom a format $1', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['a']));
        $this->assertSame('some $2 custom a format b', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['a', 'b']));
        $this->assertSame('some c custom a format b', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['a', 'b', 'c']));
        $this->assertSame('some c custom a format b', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['a', 'b', 'c', 'd']));
        $this->assertSame('some c custom a format ', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['a', '', 'c']));

        $this->sut->setPrimaryLocale('es_ES');
        $this->assertSame($this->sut->getLocales(), ['es_ES', 'en_US']);

        $this->assertSame('algun $2 personalizado $0 formato $1', $this->sut->t("TAG_4", 'lib-wildcards/wildcards'));
        $this->assertSame('algun $2 personalizado $0 formato $1', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', []));
        $this->assertSame('algun $2 personalizado a formato $1', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['a']));
        $this->assertSame('algun $2 personalizado a formato b', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['a', 'b']));
        $this->assertSame('algun c personalizado a formato b', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['a', 'b', 'c']));
        $this->assertSame('algun c personalizado a formato b', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['a', 'b', 'c', 'd']));
        $this->assertSame('algun c personalizado a formato ', $this->sut->t("TAG_4", 'lib-wildcards/wildcards', ['a', '', 'c']));

        $this->sut->setPrimaryLocale('en_US');
        $this->assertSame($this->sut->getLocales(), ['en_US', 'es_ES']);

        $this->assertSame('missing _1_ wildcard _3_ indices _5_', $this->sut->t("TAG_5", 'lib-wildcards/wildcards'));
        $this->assertSame('missing _1_ wildcard _3_ indices _5_', $this->sut->t("TAG_5", 'lib-wildcards/wildcards', ['1', '2', '3']));
        $this->assertSame('missing _1_ wildcard _3_ indices _5_', $this->sut->t("TAG_5", 'lib-wildcards/wildcards', ['1', '2', '3', '4']));

        $this->sut->setWildCardsFormat('_N_');

        $this->assertSame('missing _1_ wildcard _3_ indices _5_', $this->sut->t("TAG_5", 'lib-wildcards/wildcards'));
        $this->assertSame('missing _1_ wildcard _3_ indices _5_', $this->sut->t("TAG_5", 'lib-wildcards/wildcards', []));
        $this->assertSame('missing _1_ wildcard _3_ indices _5_', $this->sut->t("TAG_5", 'lib-wildcards/wildcards', ['a']));
        $this->assertSame('missing b wildcard _3_ indices _5_', $this->sut->t("TAG_5", 'lib-wildcards/wildcards', ['a', 'b']));
        $this->assertSame('missing b wildcard _3_ indices _5_', $this->sut->t("TAG_5", 'lib-wildcards/wildcards', ['a', 'b', 'c']));
        $this->assertSame('missing b wildcard d indices _5_', $this->sut->t("TAG_5", 'lib-wildcards/wildcards', ['a', 'b', 'c', 'd']));
        $this->assertSame('missing  wildcard d indices f', $this->sut->t("TAG_5", 'lib-wildcards/wildcards', ['a', '', 'c', 'd', 'e', 'f', 'g']));
    }


    /**
     * test
     *
     * @return void
     */
    public function testGetLocales(){

        $this->sut->initialize(['es_ES', 'en_US', 'fr_FR'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'en_US', 'fr_FR']));

        $this->sut->setLocalesOrder(['en_US', 'fr_FR', 'es_ES']);

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['en_US', 'fr_FR', 'es_ES']));
    }


    /**
     * test
     *
     * @return void
     */
    public function testGetLanguages(){

        $this->sut->initialize(['es_ES', 'en_US', 'fr_FR'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'en', 'fr']));

        $this->sut->setLocalesOrder(['en_US', 'fr_FR', 'es_ES']);

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['en', 'fr', 'es']));
    }


    /**
     * test
     *
     * @return void
     */
    public function testGetPrimaryLocale(){

        $this->sut->initialize(['es_ES', 'en_US', 'fr_FR'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);

        $this->assertSame($this->sut->getPrimaryLocale(), 'es_ES');

        $this->sut->setLocalesOrder(['en_US', 'es_ES', 'fr_FR']);

        $this->assertSame($this->sut->getPrimaryLocale(), 'en_US');
    }


    /**
     * test
     *
     * @return void
     */
    public function testGetPrimaryLanguage(){

        $this->sut->initialize(['es_ES', 'en_US', 'fr_FR'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);

        $this->assertSame($this->sut->getPrimaryLanguage(), 'es');

        $this->sut->setLocalesOrder(['en_US', 'es_ES', 'fr_FR']);

        $this->assertSame($this->sut->getPrimaryLanguage(), 'en');
    }


    /**
     * test
     *
     * @return void
     */
    public function testSetPrimaryLocale(){

        $this->sut->initialize(['es_ES', 'en_US', 'fr_FR'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'en_US', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'en', 'fr']));

        $this->sut->setPrimaryLocale('en_US');
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['en_US', 'es_ES', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['en', 'es', 'fr']));

        $this->sut->setPrimaryLocale('fr_FR');
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['fr_FR', 'en_US', 'es_ES']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['fr', 'en', 'es']));

        $this->sut->setPrimaryLocale('es_ES');
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'fr_FR', 'en_US']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'fr', 'en']));

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLocale(123); }, '/locale must be a valid xx_XX value/');
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLocale(["LOGIN"]); }, '/must be of the type string, array given/');
    }


    /**
     * test
     *
     * @return void
     */
    public function testSetPrimaryLocales(){

        AssertUtils::throwsException(function() { $this->sut->setPrimaryLocales('en_US'); }, '/must be of the type array, string given/');

        $this->sut->initialize(['es_ES', 'en_US', 'fr_FR'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'en_US', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'en', 'fr']));

        $this->sut->setPrimaryLocales(['en_US']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['en_US', 'es_ES', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['en', 'es', 'fr']));

        $this->sut->setPrimaryLocales(['fr_FR']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['fr_FR', 'en_US', 'es_ES']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['fr', 'en', 'es']));

        $this->sut->setPrimaryLocales(['es_ES']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'fr_FR', 'en_US']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'fr', 'en']));

        $this->sut->setPrimaryLocales(['en_US', 'fr_FR']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['en_US', 'fr_FR', 'es_ES']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['en', 'fr', 'es']));

        $this->sut->setPrimaryLocales(['es_ES', 'en_US', 'fr_FR']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'en_US', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'en', 'fr']));

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLocales([]); }, '/locales must be non empty string array with no duplicate elements/');
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLocales([1]); }, '/locale must be a valid xx_XX value/');
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLocales(123); }, '/must be of the type array, int given/');
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLocales(['es_ES', 'nothing']); }, '/locale must be a valid xx_XX value/');
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLocales(['es_ES', 'es_ES']); }, '/locales must be non empty string array with no duplicate elements/');
    }


    /**
     * test
     *
     * @return void
     */
    public function testSetPrimaryLanguage(){

        AssertUtils::throwsException(function() { $this->sut->setPrimaryLanguage('en'); }, '/en not loaded/');

        $this->sut->initialize(['es_ES', 'en_US', 'fr_FR'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'en_US', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'en', 'fr']));

        $this->sut->setPrimaryLanguage('en');
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['en_US', 'es_ES', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['en', 'es', 'fr']));

        $this->sut->setPrimaryLanguage('fr');
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['fr_FR', 'en_US', 'es_ES']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['fr', 'en', 'es']));

        $this->sut->setPrimaryLanguage('es');
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'fr_FR', 'en_US']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'fr', 'en']));

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLanguage(123); }, '/123 not loaded/');
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLanguage(["LOGIN"]); }, '/must be of the type string, array given/');
    }


    /**
     * test
     *
     * @return void
     */
    public function testSetPrimaryLanguage_repeated_languages(){

        $this->sut->initialize(['es_ES', 'en_GB', 'en_US'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'en_GB', 'en_US']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'en', 'en']));
        $this->assertSame('Acceder', $this->sut->t('LOGIN', 'lib-multilocales/multi'));

        $this->sut->setPrimaryLanguage('en');
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['en_GB', 'es_ES', 'en_US']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['en', 'es', 'en']));
        $this->assertSame('Access GB', $this->sut->t('LOGIN', 'lib-multilocales/multi'));

        $this->sut->setPrimaryLocale('en_US');
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['en_US', 'en_GB', 'es_ES']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['en', 'en', 'es']));
        $this->assertSame('Access US', $this->sut->t('LOGIN', 'lib-multilocales/multi'));

        $this->sut->setPrimaryLanguage('es');
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'en_US', 'en_GB']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'en', 'en']));
        $this->assertSame('Acceder', $this->sut->t('LOGIN', 'lib-multilocales/multi'));
    }


    /**
     * test
     *
     * @return void
     */
    public function testSetPrimaryLanguages(){

        AssertUtils::throwsException(function() { $this->sut->setPrimaryLanguages('en'); }, '/must be of the type array, string given/');

        $this->sut->initialize(['es_ES', 'en_US', 'fr_FR'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'en_US', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'en', 'fr']));

        $this->sut->setPrimaryLanguages(['en']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['en_US', 'es_ES', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['en', 'es', 'fr']));

        $this->sut->setPrimaryLanguages(['fr']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['fr_FR', 'en_US', 'es_ES']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['fr', 'en', 'es']));

        $this->sut->setPrimaryLanguages(['es']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'fr_FR', 'en_US']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'fr', 'en']));

        $this->sut->setPrimaryLanguages(['en', 'fr']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['en_US', 'fr_FR', 'es_ES']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['en', 'fr', 'es']));

        $this->sut->setPrimaryLanguages(['es', 'en', 'fr']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'en_US', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'en', 'fr']));

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLanguages([]); }, '/languages must be non empty string array with no duplicate elements/');
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLanguages([1]); }, '/1 not loaded/');
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLanguages(123); }, '/must be of the type array, int given/');
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLanguages(['es', 'nothing']); }, '/nothing not loaded/');
        AssertUtils::throwsException(function() { $this->sut->setPrimaryLanguages(['es', 'es']); }, '/languages must be non empty string array with no duplicate elements/');
    }


    /**
     * test
     *
     * @return void
     */
    public function testSetLocalesOrder(){

        $this->sut->initialize(['es_ES', 'en_US', 'fr_FR'], [$this->basePath.DIRECTORY_SEPARATOR.'lib-multilocales']);

        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['es_ES', 'en_US', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['es', 'en', 'fr']));
        $this->assertSame('Acceder', $this->sut->t('LOGIN', 'lib-multilocales/multi'));

        $this->sut->setLocalesOrder(['en_US', 'es_ES', 'fr_FR']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['en_US', 'es_ES', 'fr_FR']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['en', 'es', 'fr']));
        $this->assertSame('Access US', $this->sut->t('LOGIN', 'lib-multilocales/multi'));

        $this->sut->setLocalesOrder(['fr_FR', 'en_US', 'es_ES']);
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLocales(), ['fr_FR', 'en_US', 'es_ES']));
        $this->assertTrue(ArrayUtils::isEqualTo($this->sut->getLanguages(), ['fr', 'en', 'es']));
        $this->assertSame('Accedua', $this->sut->t('LOGIN', 'lib-multilocales/multi'));

        // Test exceptions
        AssertUtils::throwsException(function() { $this->sut->setLocalesOrder(['fr_FR']); }, '/locales must contain all the currently loaded locales/');
        AssertUtils::throwsException(function() { $this->sut->setLocalesOrder(['fr_FR', 'en_US', 'es_ES', 'en_GB']); }, '/locales must contain all the currently loaded locales/');
        AssertUtils::throwsException(function() { $this->sut->setLocalesOrder(['fr_FR', 'en_US', 'en_GB']); }, '/en_GB not loaded/');
        AssertUtils::throwsException(function() { $this->sut->setLocalesOrder(123); }, '/must be of the type array, int given/');
    }
}
