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
 * SketchUtils
 *
 * All JSON related methods are based in Services_JSON by Michal Migurski <mike-json@teczno.com>
 * 
 * Unicode based in the following documentation: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
 * 
 * @package Sketch
 */
class SketchUtils extends SketchObject {
    /**
     *
     * @param string $file
     * @return boolean
     */
    static function Readable($file) {
        $includes = explode(PATH_SEPARATOR, get_include_path());
        foreach ($includes as $path) {
            $path = realpath($path.DIRECTORY_SEPARATOR.$file);
            if (is_readable($path)) return true;
        } return false;
    }

    /**
     *
     * @param string $file
     * @return boolean
     */
    static function Writable($file) {
        $includes = explode(PATH_SEPARATOR, get_include_path());
        foreach ($includes as $path) {
            $path = realpath($path.DIRECTORY_SEPARATOR.$file);
            if (is_writable($path)) return $path;
        } return false;
    }

    static function Format($text) {
        $text = preg_replace('/\[\[(.*)\]\]/', '<a href="word.php?value=\\1">\\1</a>', $text);
        $text = preg_replace('/{{(.*);(.*)}}/', '<ruby><rb>\\1</rb><rp>(</rp><rt>\\2</rt><rp>)</rp></ruby>', $text);
        return nl2br($text);
    }

    private static function name_value($name, $value) {
        $encoded_value = self::encodeJSON($value);
        return self::encodeJSON(strval($name)).':'.$encoded_value;
    }

    static function encodeJSONString($var) {
        $ascii = '';
        $strlen_var = strlen($var);
        for ($c = 0; $c < $strlen_var; ++$c) {
            $ord_var_c = ord($var{$c});
            switch (true) {
                case $ord_var_c == 0x08:
                    $ascii .= '\b';
                    break;
                case $ord_var_c == 0x09:
                    $ascii .= '\t';
                    break;
                case $ord_var_c == 0x0A:
                    $ascii .= '\n';
                    break;
                case $ord_var_c == 0x0C:
                    $ascii .= '\f';
                    break;
                case $ord_var_c == 0x0D:
                    $ascii .= '\r';
                    break;
                case $ord_var_c == 0x22:
                case $ord_var_c == 0x2F:
                case $ord_var_c == 0x5C:
                    $ascii .= '\\'.$var{$c};
                    break;
                case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                    $ascii .= $var{$c};
                    break;
                case (($ord_var_c & 0xE0) == 0xC0):
                    // characters U-00000080 - U-000007FF, mask 110XXXXX
                    $char = pack('C*', $ord_var_c, ord($var{$c + 1}));
                    $c += 1;
                    $utf16 = mb_convert_encoding($char, 'UTF-16', 'UTF-8');
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;
                case (($ord_var_c & 0xF0) == 0xE0):
                    // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    $char = pack('C*', $ord_var_c, ord($var{$c + 1}), ord($var{$c + 2}));
                    $c += 2;
                    $utf16 = mb_convert_encoding($char, 'UTF-16', 'UTF-8');
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;
                case (($ord_var_c & 0xF8) == 0xF0):
                    // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    $char = pack('C*', $ord_var_c, ord($var{$c + 1}), ord($var{$c + 2}), ord($var{$c + 3}));
                    $c += 3;
                    $utf16 = mb_convert_encoding($char, 'UTF-16', 'UTF-8');
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;
                case (($ord_var_c & 0xFC) == 0xF8):
                    // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    $char = pack('C*', $ord_var_c, ord($var{$c + 1}), ord($var{$c + 2}), ord($var{$c + 3}), ord($var{$c + 4}));
                    $c += 4;
                    $utf16 = mb_convert_encoding($char, 'UTF-16', 'UTF-8');
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;
                case (($ord_var_c & 0xFE) == 0xFC):
                    // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                    $char = pack('C*', $ord_var_c, ord($var{$c + 1}), ord($var{$c + 2}), ord($var{$c + 3}), ord($var{$c + 4}), ord($var{$c + 5}));
                    $c += 5;
                    $utf16 = mb_convert_encoding($char, 'UTF-16', 'UTF-8');
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;
            }
        }
        return '"'.$ascii.'"';
    }

    static function encodeJSONArray($var) {
        if (is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) {
            $properties = array_map(array('SketchUtils', 'name_value'), array_keys($var), array_values($var));
            return '{'.join(',', $properties).'}';
        }
        $elements = array_map(array('SketchUtils', 'encodeJSON'), $var);
        return '[' . join(',', $elements) . ']';
    }

    static function encodeJSONObject($var) {
        $vars = get_object_vars($var);
        $properties = array_map(array('SketchUtils', 'name_value'), array_keys($vars), array_values($vars));
        return '{' . join(',', $properties) . '}';
    }

    static function encodeJSON($var) {
        switch (gettype($var)) {
            case 'boolean': return ($var) ? 'true' : 'false';
            case 'integer': return intval($var);
            case 'double':
            case 'float': return floatval($var);
            case 'string': return self::encodeJSONString($var);
            case 'array': return self::encodeJSONArray($var);
            case 'object': return self::encodeJSONObject($var);
            default: return 'null';
        }
    }
}