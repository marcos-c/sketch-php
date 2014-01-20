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

namespace Sketch\Core;

use Sketch\Application\Application;
use Sketch\Application\Controller;
use Sketch\HTTP\Request;
use Sketch\Locale\Locale;
use Sketch\Locale\Formatter;
use Sketch\Locale\Translator\Translator;
use Sketch\Logger\Logger;
use Sketch\Resource\Database\Database;
use Sketch\Resource\Context;
use Sketch\Core\Session\Session;

/**
 * Core object class definition
 *
 * @package Sketch\Core
 */
abstract class Object {
    /**
     * @return Application
     */
    function getApplication() {
        return Application::getInstance();
    }

    /**
     * @return Context
     */
    function getContext() {
        return $this->getApplication()->getContext();
    }

    /**
     * @return Logger
     */
    function getLogger() {
        return $this->getApplication()->getLogger();
    }

    /**
     * @return Database
     */
    function getConnection() {
        return $this->getApplication()->getConnection();
    }

    /**
     * @return Controller
     */
    function getController() {
        return $this->getApplication()->getController();
    }

    /**
     * @return Request
     */
    function getRequest() {
        return $this->getApplication()->getRequest();
    }

    /**
     * @return Session
     */
    function getSession() {
        return $this->getApplication()->getSession();
    }

    /**
     * @return Locale
     */
    function getLocale() {
        return $this->getApplication()->getLocale();
    }

    /**
     * @param string $reference
     * @return Translator
     */
    function getTranslator($reference = 'default') {
        return $this->getApplication()->getLocale()->getTranslator($reference);
    }

    /**
     * @return Formatter
     */
    function getFormatter() {
        return $this->getApplication()->getLocale()->getFormatter();
    }

    /**
     * @return array
     */
    function extend() {
        $o = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            $t = func_get_arg($i);
            if (is_array($t)) {
                foreach ($t as $k1 => $v1) {
                    if (is_array($v1)) {
                        foreach ($v1 as $k2 => $v2) {
                            $o[$k1][$k2] = $v2;
                        }
                    } else {
                        $o[$k1] = $v1;
                    }
                }
            }
        }
        foreach ($o as $key => $value) {
            if (is_array($value)) {
                $final_value = "";
                foreach ($value as $key2 => $value2) {
                    if ($value2 != null) {
                        $final_value .= " $key2=\"$value2\"";
                    }
                }
                $o[$key] = $final_value;
            }
        }
        return $o;
    }

    /**
     * @return array
     */
    function expand() {
        $o = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            $t = func_get_arg($i);
            if (is_array($t)) {
                foreach ($t as $k1 => $v1) {
                    if (is_array($v1)) {
                        foreach ($v1 as $k2 => $v2) {
                            $o[$k1][$k2] = $v2;
                        }
                    } else {
                        $o[$k1] = $v1;
                    }
                }
            }
        }
        return $o;
    }
}