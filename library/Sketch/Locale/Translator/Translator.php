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

namespace Sketch\Locale\Translator;

use Sketch\Locale\Translator\Driver\Driver;
use Sketch\Core\Object;

/**
 * Locale translator class
 *
 * @package Sketch\Locale
 */
class Translator extends Object {
    /**
     * @var \Sketch\Locale\Translator\Driver\Driver
     */
    private $driver;

    /**
     * @param \Sketch\Locale\Translator\Driver\Driver $driver
     */
    function  __construct(Driver $driver) {
        $this->setDriver($driver);
    }

    /**
     * @return \Sketch\Locale\Translator\Driver\Driver
     */
    function getDriver() {
        return $this->driver;
    }

    /**
     * @param \Sketch\Locale\Translator\Driver\Driver $driver
     */
    function setDriver(Driver $driver) {
        $this->driver = $driver;
    }

    /**
     *
     * @param $text
     * @return string
     */
    function _a($text) {
        return $this->driver->translate($text, 'default');
    }

    /**
     *
     * @param $singular
     * @param $plural
     * @param $number
     * @return string
     */
    function _na($singular, $plural, $number) {
        return $this->driver->translate_plural($singular, $plural, $number, 'default');
    }

    /**
     *
     * @param $text
     * @param $context
     * @return string
     */
    function _xa($text, $context) {
        return $this->driver->translate_with_context($text, $context, 'default');
    }

    /**
     *
     * @param $text
     * @return string
     */
    function _b($text) {
        return $this->driver->translate($text, 'support');
    }

    /**
     *
     * @param $singular
     * @param $plural
     * @param $number
     * @return string
     */
    function _nb($singular, $plural, $number) {
        return $this->driver->translate_plural($singular, $plural, $number, 'support');
    }

    /**
     *
     * @param $text
     * @param $context
     * @return string
     */
    function _xb($text, $context) {
        return $this->driver->translate_with_context($text, $context, 'support');
    }

    /**
     *
     * @param $text
     * @return string
     */
    function _c($text) {
        return $this->driver->translate($text, 'extra');
    }

    /**
     *
     * @param $singular
     * @param $plural
     * @param $number
     * @return string
     */
    function _nc($singular, $plural, $number) {
        return $this->driver->translate_plural($singular, $plural, $number, 'extra');
    }

    /**
     *
     * @param $text
     * @param $context
     * @return string
     */
    function _xc($text, $context) {
        return $this->driver->translate_with_context($text, $context, 'extra');
    }

    /**
     *
     * @param $text
     * @return string
     */
    function _s($text) {
        return $this->driver->translate($text, 'system');
    }

    /**
     *
     * @param $singular
     * @param $plural
     * @param $number
     * @return string
     */
    function _ns($singular, $plural, $number) {
        return $this->driver->translate_plural($singular, $plural, $number, 'system');
    }

    /**
     *
     * @param $text
     * @param $context
     * @return string
     */
    function _xs($text, $context) {
        return $this->driver->translate_with_context($text, $context, 'system');
    }
}