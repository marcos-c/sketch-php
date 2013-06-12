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

namespace Sketch;

abstract class LocaleTranslatorDriver extends Object {
    /**
     * @var string
     */
    private $localeString;

    /**
     * @return string
     */
    function getLocaleString() {
        return $this->localeString;
    }

    /**
     * @param string $locale_string
     */
    function setLocaleString($locale_string) {
        $this->localeString = $locale_string;
    }

    /**
     * @abstract
     * @return array
     */
    abstract function getAvailableLanguages();

    /**
     * @param $text
     * @param $in_domain
     * @return mixed
     */
    abstract function translate($text, $in_domain);

    /**
     * @param $singular
     * @param $plural
     * @param $number
     * @param null $in_domain
     * @return mixed
     */
    abstract function translate_plural($singular, $plural, $number, $in_domain);

    /**
     * @param $text
     * @param $context
     * @param $in_domain
     * @return mixed
     */
    abstract function translate_with_context($text, $context, $in_domain);
}