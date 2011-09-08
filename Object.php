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

require_once 'Sketch/Application.php';
require_once 'Sketch/Resource.php';
require_once 'Sketch/Resource/Connection.php';
require_once 'Sketch/Controller.php';
require_once 'Sketch/Request.php';
require_once 'Sketch/Session.php';
require_once 'Sketch/Locale.php';

/**
 * SketchObject
 */
abstract class SketchObject {
    /**
     * Get the application instance
     *
     * @return SketchApplication
     */
    function getApplication() {
        return SketchApplication::getInstance();
    }

    /**
     * Get the resource context instance
     *
     * @return SketchResourceContext
     */
    function getContext() {
        return $this->getApplication()->getContext();
    }

    /**
     * Get the logger instance
     *
     * @return SketchLogger
     */
    function getLogger() {
        return $this->getApplication()->getLogger();
    }

    /**
     * Get the connection instance
     *
     * @return SketchResourceConnection
     */
    function getConnection() {
        return $this->getApplication()->getConnection();
    }

    /**
     * Get the controller instance
     *
     * @return SketchController
     */
    function getController() {
        return $this->getApplication()->getController();
    }

    /**
     * Get the request instance
     *
     * @return SketchRequest
     */
    function getRequest() {
        return $this->getApplication()->getRequest();
    }

    /**
     * Get the session instance
     *
     * @return SketchSession
     */
    function getSession() {
        return $this->getApplication()->getSession();
    }

    /**
     * Get the locale instance
     *
     * @return SketchLocale
     */
    function getLocale() {
        return $this->getApplication()->getLocale();
    }

    /**
     * Get the locale translator instance
     *
     * @param string $reference
     * @return SketchLocaleTranslator
     */
    function getTranslator($reference = 'default') {
        return $this->getApplication()->getLocale()->getTranslator($reference);
    }

    /**
     * Get the locale formatter instance
     *
     * @return SketchLocaleFormatter
     */
    function getFormatter() {
        return $this->getApplication()->getLocale()->getFormatter();
    }

    /**
     * Extend attributes method
     *
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
     *
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

    /**
     * Expand attributes method
     *
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