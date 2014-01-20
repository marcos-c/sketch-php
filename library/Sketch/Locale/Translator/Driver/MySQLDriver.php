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

use Sketch\Resource\Database\Database;
use Sketch\Resource\XML;

/**
 * MySQL locale translator driver
 *
 * @package Sketch\Locale\Translator\Driver
 */
class MySQL extends Driver {
    /**
     * @var array
     */
    private $data = array();

    /**
     * @var string
     */
    private $domain = 'default';

    private function readData() {
        $connection = $this->getConnection();
        if ($connection instanceof Database) {
            $prefix = $connection->getTablePrefix();
            $locale_string = $this->getLocaleString();
            $q = $this->getConnection()->query("SELECT md5, translated_text FROM ${prefix}_locale WHERE locale_string = '$locale_string'");
            $this->data = array($this->domain => array());
            foreach ($q as $r) {
                if ($r['translated_text'] != '') {
                    $this->data[$this->domain][$r['md5']] = $r['translated_text'];
                }
            }
        }
    }

    /**
     * @param string $locale_string
     * @param XML $resource
     */
    function  __construct($locale_string, XML $resource) {
        $this->setLocaleString($locale_string);
        $this->readData();
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
        $md5 = md5($text);
        $domain = ($in_domain == null) ? $this->domain : $in_domain;
        return (array_key_exists($domain, $this->data) && array_key_exists($md5, $this->data[$domain])) ? $this->data[$domain][$md5] : $text;
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
        return $this->translate($text, $in_domain);
    }
}