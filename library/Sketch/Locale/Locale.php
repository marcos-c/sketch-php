<?php
/**
 * This file is part of the Sketch library
 *
 * @author Marcos Cooper <marcos@releasepad.com>
 * @version 3.0
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

namespace Sketch\Locale;

use Sketch\Locale\Translator\Driver\MockDriver;
use Sketch\Core\Object;

/**
 * Locale class
 *
 * @package Sketch\Locale
 */
class Locale extends Object {
    /**
     * @var \Sketch\Locale\Translator\Translator
     */
    private $translator;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $country;

    /**
     * @return string
     */
    function getLanguage() {
        return $this->language;
    }

    /**
     * @param string $language
     * @throws \Exception
     */
    function setLanguage($language) {
        $language = strtolower($language);
        if (in_array($language, array_keys(LocaleISO::getLanguages()))) {
            $this->language = $language;
        } else throw new \Exception('Unsupported language');
    }
    
    /**
     * @return string
     */
    function getCountry() {
        return $this->country;
    }

    /**
     * @param string $country
     * @throws \Exception
     */
    function setCountry($country) {
        if ($country != null) {
            $country = strtolower($country);
            if (in_array($country, array_keys(LocaleISO::getCountries()))) {
                $this->country = $country;
            } else throw new \Exception('Unsupported country');
        }
    }

    /**
     * @param string $language
     * @param string $country
     */
    function __construct($language, $country = null) {
        $this->setLanguage($language);
        $this->setCountry($country);
    }

    /**
     * @param string $locale_string
     * @return Locale
     */
    static function fromString($locale_string) {
        $r = explode('_', $locale_string);
        return new Locale(array_shift($r), array_shift($r));
    }

    /**
     * @throws \Exception
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
        } else throw new \Exception('Unsupported locale');
    }

    /**
     * @param string $reference
     * @return \Sketch\Locale\Translator\Translator
     * @throws \Exception
     */
    function getTranslator($reference = 'default') {
        if ($this->translator == null) {
            $this->translator = array();
            $drivers = $this->getContext()->query("//driver[@type='LocaleTranslatorDriver']");
            foreach ($drivers as $driver) {
                $ref = $driver->getAttribute('reference');
                $ref = $ref != null ? $ref : 'default';
                $class = $driver->getAttribute('class');
                try {
                    if (class_exists($class)) {
                        $reflection = new \ReflectionClass($class);
                        if ($reflection->isSubclassOf('Sketch\LocaleTranslatorDriver')) {
                            /** @var \Sketch\Locale\Driver\\Sketch\Locale\Translator\Driver\Driver $instance */
                            $instance = $reflection->newInstance($this->toString(), $driver);
                            $this->translator[$ref] = new Translator\Translator($instance);
                        } else {
                            throw new \Exception(sprintf("Driver %s does not extend or implement LocaleTranslatorDriver", $class));
                        }
                    } else {
                        throw new \Exception(sprintf("Can't instantiate class %s", $class));
                    }
                } catch (\Exception $e) {
                    $this->translator[$ref] = new Translator\Translator(new MockDriver());
                }
            }
        }
        if (array_key_exists($reference, $this->translator)) {
            return $this->translator[$reference];
        } else {
            return new Translator\Translator(new MockDriver());
        }
    }

    /**
     * @return Formatter
     */
    function getFormatter() {
        return new Formatter($this->toString());
    }
}