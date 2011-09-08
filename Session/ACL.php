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

require_once 'Sketch/Object.php';
require_once 'Sketch/Session/ACL.php';

/**
 * SketchSessionACL
 */
class SketchSessionACL extends SketchObject {
    /** @var array */
    private $attributes = array();

    /** @var array */
    private $roles = array();

    /**
     * Get attribute
     *
     * @param $key
     * @return bool
     */
    function getAttribute($key) {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        } else return false;
    }

    /**
     * Set attribute
     *
     * @param $key
     * @param $value
     * @return void
     */
    function setAttribute($key, $value) {
        if ($value != null) {
            $this->attributes[$key] = $value;
        } else unset($this->attributes[$key]);
    }

    /**
     * Add role
     *
     * @param $role
     * @return void
     */
    function addRole($role) {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
    }

    /**
     * Add rule
     *
     * This method is now deprecated and only supported for backward compatibility.
     *
     * @deprecated Use addRole
     * @param $rule
     * @return void
     */
    function addRule($rule) {
        $this->addRole($rule);
    }

    /**
     * Check roles
     *
     * @param $mixed
     * @return bool
     */
    function check($mixed) {
        if (is_array($mixed)) foreach ($mixed as $role) {
            if (in_array($role, $this->roles)) return true;
        } else {
            return ($mixed != null) ? (in_array($mixed, $this->roles)) : true;
        } return false;
    }
}