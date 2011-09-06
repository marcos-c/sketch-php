<?php
/**
 * This file is part of the Sketch Framework
 * (http://code.google.com/p/sketch-framework/)
 *
 * Copyright (C) 2010 Marcos Albaladejo Cooper
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
 * SketchLocaleFormatter
 *
 * @package Sketch
 */
class SketchLocaleFormatter extends SketchObject {
    /**
     *
     * @var string
     */
    private $localeString;

    /**
     *
     * @param string $locale_string
     */
    function  __construct($locale_string) {
        $this->setLocaleString($locale_string);
    }

    /**
     *
     * @return string
     */
    function getLocaleString() {
        return $this->localeString;
    }

    /**
     *
     * @param string $locale_string
     */
    function setLocaleString($locale_string) {
        $this->localeString = $locale_string;
    }

    /**
     *
     * @param float $number
     * @return string
     */
    function formatNumber($number) {
        if ($this->localeString == 'es_ES') {
            return number_format($number, 2, ',', '.');
        } else {
            return number_format($number, 2, '.', ',');
        }
    }

    /**
     *
     * @param SketchDateTime $date
     * @return string
     */
    function formatDate(SketchDateTime $date) {
        return $date->toString('d/m/Y');
    }

    /**
     *
     * @param SketchDateTime $date
     * @return string
     */
    function formatDateAndTime(SketchDateTime $date) {
        return $date->toString('d/m/Y H:i');
    }
}