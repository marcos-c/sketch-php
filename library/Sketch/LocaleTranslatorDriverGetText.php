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

class LocaleTranslatorDriverGetText extends LocaleTranslatorDriver {
    /**
     * @var array
     */
    private $data = array();

    /**
     * @var array
     */
    private $availableLanguages = array();

    /**
     * @var string
     */
    private $domain = 'default';

    /**
     * @param $locale_string
     * @param $filename
     * @param $domain
     * @throws \Exception
     */
    private function readFileData($locale_string, $filename, $domain) {
        $file = fopen($filename, 'rb');
        if (!$file) throw new \Exception();
        if (filesize($filename) < 10) throw new \Exception();
        $byte_order = 'N';
        $input = unpack($byte_order.'1', fread($file, 4));
        if (strtolower(substr(dechex($input[1]), -8)) == "950412de") {
            $byte_order = 'N';
        } else if (strtolower(substr(dechex($input[1]), -8)) == "de120495") {
            $byte_order = 'V';
        } else {
            throw new \Exception();
        }
        // Revision
        unpack($byte_order.'1', fread($file, 4));
        // Number of bytes
        $input = unpack($byte_order.'1', fread($file, 4));
        $total = $input[1];
        // Number of original strings
        $input = unpack($byte_order.'1', fread($file, 4));
        $original_offset = $input[1];
        // Number of translation strings
        $input = unpack($byte_order.'1', fread($file, 4));
        $tranlation_offset = $input[1];
        // Fill the original table
        fseek($file, $original_offset);
        $original_strings = unpack($byte_order.(2 * $total), fread($file, 8 * $total));
        fseek($file, $tranlation_offset);
        $translation_strings = unpack($byte_order.(2 * $total), fread($file, 8 * $total));
        for($count = 0; $count < $total; ++$count) {
            if ($original_strings[$count * 2 + 1] != 0) {
                fseek($file, $original_strings[$count * 2 + 2]);
                $original = fread($file, $original_strings[$count * 2 + 1]);
                if ($translation_strings[$count * 2 + 1] != 0) {
                    fseek($file, $translation_strings[$count * 2 + 2]);
                    // Remove adapter information
                    $this->data[$locale_string][$domain][md5($original)] = fread($file, $translation_strings[$count * 2 + 1]);
                }
            }
        }
    }

    /**
     * @param $folder
     * @param $domain
     * @throws Exception
     */
    private function readData($folder, $domain) {
        $locale_string = $this->getLocaleString();
        $parsed_url = parse_url($folder);
        if ($parsed_url['scheme'] == 'gs') {
            $filename = $folder.'/'.$locale_string.'/LC_MESSAGES/'.$domain.'.mo';
        } else {
            $filename = dirname($this->getApplication()->getDocumentRoot()).$folder.'/'.$this->getLocaleString().'/LC_MESSAGES/'.$domain.'.mo';
        }
        if (class_exists('\Memcache')) {
            $memcache = new \Memcache();
            $this->data[$locale_string][$domain] = $memcache->get('gettext-data-'.$locale_string.'-'.$domain);
            if ($this->data[$locale_string][$domain] === false || $this->getRequest()->getAttribute('memcache') == 'clear') {
                try {
                    $this->readFileData($locale_string, $filename, $domain);
                    // Cache locale data for one hour
                    $memcache->set('gettext-data-'.$locale_string.'-'.$domain, $this->data[$locale_string][$domain]);
                    if ($this->getContext()->getLayerName() != 'production') {
                        $this->getLogger()->log("Locale: ".$filename);
                    }
                } catch (\Exception $e) {
                    unset($this->data[$locale_string][$domain]);
                }
            } elseif ($this->getContext()->getLayerName() != 'production') {
                $this->getLogger()->log("Locale: ".$filename." (memcache)");
            }
        } else {
            $this->readFileData($locale_string, $filename, $domain);
            if ($this->getContext()->getLayerName() != 'production') {
                $this->getLogger()->log("Locale: ".$filename);
            }
        }
    }

    /**
     * @param string $locale_string
     * @param ResourceXML $resource
     */
    function  __construct($locale_string, ResourceXML $resource) {
        $folder = $resource->queryCharacterData('//folder');
        if ($this->getContext()->getLayerName() != 'production') {
            $this->getLogger()->log("Locale: $locale_string");
        }
        foreach ($resource->query('//domain') as $domain) {
            $domain = $domain->getCharacterData();
            try {
                $this->setLocaleString($locale_string);
                $this->readData($folder, $domain);
            } catch (\Exception $e) {
                // If there's any problem to read a full locale_string try with only the language
                list($language) = explode('_', $locale_string);
                if ($language != $locale_string) {
                    $this->setLocaleString($language);
                    $this->readData($folder, $domain);
                }
            }
            if ($domain == 'default') {
                $this->setAvailableLanguages($folder);
            }
        }
    }

    /**
     * @param string $folder
     */
    private function setAvailableLanguages($folder) {
        $this->availableLanguages = array();
        $path = dirname($this->getApplication()->getDocumentRoot()).$folder;
        if (is_dir($path)) {
            if ($r = opendir($path)) {
                while (($s = readdir($r)) !== false) {
                    if (is_dir($path.'/'.$s) && !in_array($s, array('.', '..'))) {
                        list($language) = explode('_', $s);
                        $this->availableLanguages[$language] = $language;
                    }
                }
                closedir($r);
            }
        }
    }

    /**
     * @return string[]
     */
    function getAvailableLanguages() {
        return $this->availableLanguages;
    }

    /**
     * @param string $text
     * @param $in_domain
     * @return string
     */
    function translate($text, $in_domain = null) {
        $md5 = md5($text);
        $locale_string = $this->getLocaleString();
        $domain = ($in_domain == null) ? $this->domain : $in_domain;
        return (
            array_key_exists($locale_string, $this->data) &&
            array_key_exists($domain, $this->data[$locale_string]) &&
            array_key_exists($md5, $this->data[$locale_string][$domain])) ? $this->data[$locale_string][$domain][$md5] : $text;
    }

    /**
     * @param $singular
     * @param $plural
     * @param $number
     * @param null $in_domain
     * @return string
     */
    function translate_plural($singular, $plural, $number, $in_domain = null) {
        $md5 = md5($singular.chr(0).$plural);
        $locale_string = $this->getLocaleString();
        $domain = ($in_domain == null) ? $this->domain : $in_domain;
        if (
            array_key_exists($locale_string, $this->data) &&
            array_key_exists($domain, $this->data[$locale_string]) &&
            array_key_exists($md5, $this->data[$locale_string][$domain])) {
            list($singular, $plural) = explode(chr(0), $this->data[$locale_string][$domain][$md5]);
        }
        return sprintf(($number > 1) ? $plural : $singular, $number);
    }

    /**
     * @param $text
     * @param $context
     * @param null $in_domain
     * @return mixed
     */
    function translate_with_context($text, $context, $in_domain = null) {
        $md5 = md5($context.chr(4).$text);
        $locale_string = $this->getLocaleString();
        $domain = ($in_domain == null) ? $this->domain : $in_domain;
        return (
            array_key_exists($locale_string, $this->data) &&
            array_key_exists($domain, $this->data[$locale_string]) &&
            array_key_exists($md5, $this->data[$locale_string][$domain])) ? $this->data[$locale_string][$domain][$md5] : $text;
    }
}