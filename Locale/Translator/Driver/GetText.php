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

class GetTextLocaleTranslatorDriver extends SketchLocaleTranslatorDriver {
    /**
     *
     * @var array
     */
    private $data = array();

    /**
     *
     * @var array
     */
    private $availableLanguages = array();

    /**
     *
     * @var string
     */
    private $domain = 'default';

    /**
     *
     * @param string $filename
     */
    private function readData($folder, $domain) {
        $filename = $this->getApplication()->getDocumentRoot().$folder.'/'.$this->getLocaleString().'/LC_MESSAGES/'.$domain.'.mo';
        $file = fopen($filename, 'rb');
        if (!$file) throw new Exception();
        if (filesize($filename) < 10) throw new Exception();
        $byte_order = 'N';
        $input = unpack($byte_order.'1', fread($file, 4));
        if (strtolower(substr(dechex($input[1]), -8)) == "950412de") {
            $byte_order = 'N';
        } else if (strtolower(substr(dechex($input[1]), -8)) == "de120495") {
            $byte_order = 'V';
        } else {
            throw new Exception();
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
        $this->data[$domain] = array();
        for($count = 0; $count < $total; ++$count) {
            if ($original_strings[$count * 2 + 1] != 0) {
                fseek($file, $original_strings[$count * 2 + 2]);
                $original = fread($file, $original_strings[$count * 2 + 1]);
                if ($translation_strings[$count * 2 + 1] != 0) {
                    fseek($file, $translation_strings[$count * 2 + 2]);
                    // Remove adapter information
                    $this->data[$domain][md5($original)] = fread($file, $translation_strings[$count * 2 + 1]);
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
        $folder = $resource->queryCharacterData('//folder');
        foreach ($resource->query('//domain') as $domain) {
            $domain = $domain->getCharacterData();
            try {
                $this->setLocaleString($locale_string);
                $this->readData($folder, $domain);
            } catch (Exception $e) {
                // If there's any problem to read a full locale_string try with only the language
                list($locale_string) = explode('_', $locale_string);
                $this->setLocaleString($locale_string);
                $this->readData($folder, $domain);
            }
            $this->setAvailableLanguages($folder);
        }
    }

    /**
     *
     * @param $folder
     */
    private function setAvailableLanguages($folder) {
        $this->availableLanguages = array();
        if (is_dir($this->getApplication()->getDocumentRoot().$folder)) {
            if ($r = opendir($this->getApplication()->getDocumentRoot().$folder)) {
                while (($s = readdir($r)) !== false) {
                    if (is_dir($this->getApplication()->getDocumentRoot().$folder.'/'.$s) && !in_array($s, array('.', '..'))) {
                        list($language) = explode('_', $s);
                        $this->availableLanguages[$language] = $language;
                    }
                }
                closedir($r);
            }
        }
    }

    /**
     *
     * @param string $text
     * @return string
     */
    function translate($text) {
        $md5 = md5($text);
        return (array_key_exists($md5, $this->data[$this->domain])) ? $this->data[$this->domain][$md5] : $text;
    }

    /**
     *
     * @return array
     */
    function getAvailableLanguages() {
        return $this->availableLanguages;
    }
}