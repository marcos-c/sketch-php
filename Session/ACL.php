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

class SketchSessionACL extends SketchObject {
    /**
     *
     * @var array
     */
    private $attributes = array();

    /**
     *
     * @var array
     */
    private $rules = array();

    /**
     *
     * @param string $key
     * @return string
     */
    function getAttribute($key) {
        if (array_key_exists($key, $this -> attributes)) {
            return $this -> attributes[$key];
        } else return false;
    }

    /**
     *
     * @param string $key
     * @param string $value
     */
    function setAttribute($key, $value) {
        if ($value != null) {
            $this -> attributes[$key] = $value;
        } else unset($this -> attributes[$key]);
    }

    /**
     *
     * @param string $rule
     */
    function addRule($rule) {
        if (!in_array($rule, $this -> rules)) {
            $this -> rules[] = $rule;
        }
    }

    /**
     *
     * @param mixed $mixed
     * @return boolean
     */
    function check($mixed) {
        if (is_array($mixed)) foreach ($mixed as $rule) {
            if (in_array($rule, $this -> rules)) return true;
        } else {
            return ($mixed != null) ? (in_array($mixed, $this -> rules)) : true;
        } return false;
    }
}