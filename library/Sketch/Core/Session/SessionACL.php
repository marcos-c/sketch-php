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

namespace Sketch\Core\Session;

use Sketch\Core\Object;

/**
 * Core session ACL (Access Control List) class
 *
 * @package Sketch\Core
 */
class SessionACL extends Object {
    /**
     * @var array
     */
    private $attributes = array();

    /**
     * @var array
     */
    private $roles = array();

    /**
     * @param string $key
     * @return string
     */
    function getAttribute($key) {
        if (array_key_exists($key, $this -> attributes)) {
            return $this -> attributes[$key];
        } else return false;
    }

    /**
     * @param string $key
     * @param string $value
     */
    function setAttribute($key, $value) {
        if ($value != null) {
            $this -> attributes[$key] = $value;
        } else unset($this -> attributes[$key]);
    }

    /**
     * @param string $role
     */
    function addRole($role) {
        if (!in_array($role, $this -> roles)) {
            $this -> roles[] = $role;
        }
    }

    /**
     * @param mixed $mixed
     * @return boolean
     */
    function check($mixed) {
        if (is_array($mixed)) foreach ($mixed as $role) {
            if (in_array($role, $this -> roles)) return true;
        } else {
            return ($mixed != null) ? (in_array($mixed, $this -> roles)) : true;
        } return false;
    }
}