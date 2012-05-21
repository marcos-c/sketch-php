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
 * SketchLocaleTranslatorDriver
 */
abstract class SketchLocaleTranslatorDriver extends SketchObject {
    /** @var string */
    private $localeString;

    /**
     * Get locale string
     *
     * @return string
     */
    function getLocaleString() {
        return $this->localeString;
    }

    /**
     * Set locale string
     *
     * @param $locale_string
     * @return void
     */
    function setLocaleString($locale_string) {
        $this->localeString = $locale_string;
    }

    /**
     * Translate
     *
     * @abstract
     * @param $text
     * @return string
     */
    abstract function translate($text);
}