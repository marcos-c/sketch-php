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

namespace Sketch\Locale\Translator\Driver;

class MockDriver extends Driver {
    function  __construct() {
        $this->setLocaleString('en');
    }

    /**
     * @return array
     */
    function getAvailableLanguages() {
        return array('es', 'en', 'de');
    }

    /**
     * @param $text
     * @param $in_domain
     * @return mixed
     */
    function translate($text, $in_domain) {
        return $text;
    }

    /**
     * @param $singular
     * @param $plural
     * @param $number
     * @param null $in_domain
     * @return mixed
     */
    function translate_plural($singular, $plural, $number, $in_domain) {
        return sprintf(($number > 1) ? $plural : $singular, $number);
    }

    /**
     * @param $text
     * @param $context
     * @param $in_domain
     * @return mixed
     */
    function translate_with_context($text, $context, $in_domain) {
        return $text;
    }
}