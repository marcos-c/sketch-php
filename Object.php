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

abstract class SketchObject {
    /**
     *
     * @return SketchApplication
     */
    function getApplication() {
        return SketchApplication::getInstance();
    }

    /**
     *
     * @return SketchResourceContext
     */
    function getContext() {
        return $this->getApplication()->getContext();
    }

    /**
     *
     * @return SketchLogger
     */
    function getLogger() {
        return $this->getApplication()->getLogger();
    }

    /**
     *
     * @return SketchResourceConnection
     */
    function getConnection() {
        return $this->getApplication()->getConnection();
    }

    /**
     *
     * @return SketchController
     */
    function getController() {
        return $this->getApplication()->getController();
    }

    /**
     *
     * @return SketchRequest
     */
    function getRequest() {
        return $this->getApplication()->getRequest();
    }

    /**
     *
     * @return SketchSession
     */
    function getSession() {
        return $this->getApplication()->getSession();
    }

    /**
     *
     * @return SketchLocale
     */
    function getLocale() {
        return $this->getApplication()->getLocale();
    }

    /**
     *
     * @param string $reference
     * @return SketchLocaleTranslator
     */
    function getTranslator($reference = 'default') {
        return $this->getApplication()->getLocale()->getTranslator($reference);
    }

    /**
     *
     * @return SketchLocaleFormatter
     */
    function getFormatter() {
        return $this->getApplication()->getLocale()->getFormatter();
    }

    /**
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
}