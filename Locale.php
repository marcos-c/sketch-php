<?php
/**
 * This file is part of the Sketch library
 *
 * @author Marcos Cooper <marcos@releasepad.com>
 * @version 2.0.12
 * @copyright 2007 Marcos Cooper
 * @link http://releasepad.com/sketch
 * @package Sketch
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, you can get a copy from the
 * following link: http://opensource.org/licenses/lgpl-2.1.php
 */

class SketchLocale extends SketchObject {
    /**
     *
     * @var SketchLocaleTranslator
     */
    private $translator;

    /**
     *
     * @var string
     */
    private $language;

    /**
     *
     * @var string
     */
    private $country;

    /**
     *
     * @return string
     */
    function getLanguage() {
        return $this->language;
    }

    /**
     *
     * @param string $language
     */
    function setLanguage($language) {
        $language = strtolower($language);
        if (in_array($language, array_keys(SketchLocaleISO::getLanguages()))) {
            $this->language = $language;
        } else throw new Exception('Unsupported language');
    }
    
    /**
     *
     * @return string
     */
    function getCountry() {
        return $this->country;
    }

    /**
     *
     * @param string $country
     */
    function setCountry($country) {
        if ($country != null) {
            $country = strtolower($country);
            if (in_array($country, array_keys(SketchLocaleISO::getCountries()))) {
                $this->country = $country;
            } else throw new Exception('Unsupported country');
        }
    }

    /**
     *
     * @param string $language
     * @param string $country
     */
    function __construct($language, $country = null) {
        $this->setLanguage($language);
        $this->setCountry($country);
    }

    /**
     *
     * @param string $locale_string
     * @return SketchLocale 
     */
    static function fromString($locale_string) {
        $r = explode('_', $locale_string);
        return new SketchLocale(array_shift($r), array_shift($r));
    }

    /**
     *
     * @return string
     */
    function toString() {
        $language = $this->getLanguage();
        $country = strtoupper($this->getCountry());
        if ($language != null && $country != null) {
            return "${language}_${country}";
        } else if ($language != null) {
            return "${language}";
        } else if ($country != null) {
            return "_${country}";
        } else throw new Exception('Unsupported locale');
    }

    /**
     *
     * @param string $reference
     * @return SketchLocaleTranslator
     * @throws Exception
     */
    function getTranslator($reference = 'default') {
        if ($this->translator == null) {
            $this->translator = array();
            $drivers = $this->getContext()->query("//driver[@type='SketchLocaleTranslatorDriver']");
            foreach ($drivers as $driver) {
                $ref = $driver->getAttribute('reference');
                $ref = $ref != null ? $ref : 'default';
                $type = $driver->getAttribute('type');
                $class = $driver->getAttribute('class');
                $source = $driver->getAttribute('source');
                if (SketchUtils::Readable("Sketch/Locale/Translator/Driver/$source")) {
                    require_once "Sketch/Locale/Translator/Driver/$source";
                    try {
                        if (class_exists($class)) {
                            eval('$instance = new '.$class.'(\''.$this->toString().'\', $driver);');
                            if ($instance instanceof $type) {
                                $this->translator[$ref] = new SketchLocaleTranslator($instance);
                            } else throw new Exception(sprinf("Driver %s does not extend or implement %s", $class, $type));
                        } else throw new Exception(sprintf("Can't instantiate class %s", $class));
                    } catch (Exception $e) {
                        $this->translator[$ref] = new SketchLocaleTranslator(new DummyLocaleTranslatorDriver());
                    }
                } else throw new Exception(sprintf("File %s can't be found", $source));
            }
        }
        if (array_key_exists($reference, $this->translator)) {
            return $this->translator[$reference];
        } else {
            return new SketchLocaleTranslator(new DummyLocaleTranslatorDriver());
        }
    }

    /**
     *
     * @return SketchLocaleFormatter 
     */
    function getFormatter() {
        return new SketchLocaleFormatter($this->toString());
    }
}