<?php
/**
 * This file is part of the Sketch Framework
 * (http://code.google.com/p/sketch-framework/)
 *
 * Copyright (C) 2011 Marcos Albaladejo Cooper
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
 *
 * @package Sketch
 */

require_once 'Sketch/Object.php';

/**
 * SketchLocaleTranslator
 */
class SketchLocaleTranslator extends SketchObject {
    /** @var SketchLocaleTranslatorDriver */
    private $driver;

    /**
     * Constructor
     *
     * @param SketchLocaleTranslatorDriver $driver
     */
    function  __construct(SketchLocaleTranslatorDriver $driver) {
        $this->setDriver($driver);
    }

    /**
     * Get driver
     *
     * @return SketchLocaleTranslatorDriver
     */
    function getDriver() {
        return $this->driver;
    }

    /**
     * Set driver
     *
     * @param SketchLocaleTranslatorDriver $driver
     * @return void
     */
    function setDriver(SketchLocaleTranslatorDriver $driver) {
        $this->driver = $driver;
    }

    /**
     * Translate
     *
     * @param $text
     * @return string
     */
    function translate($text) {
        return $this->driver->translate($text);
    }

    /**
     * Shorter alias for translate
     *
     * @param $text
     * @return string
     */
    function _($text) {
        return $this->translate($text);
    }
}