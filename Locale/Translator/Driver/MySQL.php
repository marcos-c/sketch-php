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

require_once 'Sketch/Locale/Translator/Driver.php';

/**
 * MySQLLocaleTranslatorDriver
 *
 * @package Sketch
 */
class MySQLLocaleTranslatorDriver extends SketchLocaleTranslatorDriver {
    /**
     *
     * @var array
     */
    private $data = array();

    private function readData() {
        $connection = $this->getConnection();
        if ($connection instanceof SketchResourceConnection) {
            $prefix = $connection->getTablePrefix();
            $locale_string = $this->getLocaleString();
            $q = $connection->query("SELECT md5, translated_text FROM ${prefix}_locale WHERE locale_string = '$locale_string'");
            foreach ($q as $r) {
                if ($r['translated_text'] != '') {
                    $this->data[$r['md5']] = $r['translated_text'];
                }
            }
        }
    }

    /**
     *
     * @param string $locale_string
     * @param SketchResourceXML $resource
     */
    function  __construct($locale_string, SketchResourceXML $resource) {
        $this->setLocaleString($locale_string);
        $this->readData();
    }
    
    /**
     *
     * @param string $text
     * @return string
     */
    function translate($text) {
        $md5 = md5($text);
        return (array_key_exists($md5, $this->data)) ? $this->data[$md5] : $text;
    }


    /**
     *
     * @param $a
     * @param $b
     * @return int
     */
    private function sortAvailableLanguages($a, $b) {
        if ($a == $b) {
            return 0;
        }
        return ($a == $this->getLocaleString()) ? -1 : (($b == $this->getLocaleString()) ? 1 : (($a < $b) ? -1 : 1));
    }

    /**
     *
     * @return array
     */
    function getAvailableLanguages() {
        $connection = $this->getConnection();
        if ($connection instanceof SketchResourceConnection) {
            $prefix = $connection->getTablePrefix();
            $available_languages = $connection->queryArray("SELECT DISTINCT locale_string FROM ${prefix}_locale ORDER BY locale_string");
            usort($available_languages, array($this, 'sortAvailableLanguages'));
            return $available_languages;
        } else {
            return array('en');
        }
    }
}