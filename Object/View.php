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
 * SketchObjectView
 *
 * @package Sketch
 */
abstract class SketchObjectView extends SketchObject {
    /**
     *
     * @var boolean
     */
    private $useSessionObject = false;

    /**
     *
     * @var string
     */
    private $viewId;

    /**
     *
     * @var string
     */
    private $id;

    /**
     *
     * @return boolean
     */
    function getUseSessionObject() {
        return $this->useSessionObject;
    }

    /**
     *
     * @param boolean $use_session_object
     */
    function setUseSessionObject($use_session_object) {
        $this->useSessionObject = $use_session_object;
    }

    /**
     *
     * @param string $key
     * @param string $default
     * @param bool $global
     * @return string
     */
    protected final function getSessionObjectAttribute($key, $default, $global = false) {
        $session = $this->getSession();
        $data = $session->getAttribute('__list');
        $view_name = ($global) ? '__global' : $this->getViewName();
        if (is_array($data) && array_key_exists($view_name, $data)) {
            if (array_key_exists($key, $data[$view_name])) {
                return $data[$view_name][$key];
            }
        }
        return $default;
    }

    /**
     *
     * @param $key
     * @param $value
     * @param bool $global
     * @return void
     */
    protected final function setSessionObjectAttribute($key, $value, $global = false) {
        $session = $this->getSession();
        $data = $session->getAttribute('__list');
        $view_name = ($global) ? '__global' : $this->getViewName();
        $data[$view_name][$key] = $value;
        $session->setAttribute('__list', $data);
    }

    final function clearOtherSessionObjectAttributes() {
        $session = $this->getSession();
        $data = $session->getAttribute('__list');
        $current_view_name = $this->getViewName();
        foreach (array_keys($data) as $view_name) {
            if ($view_name != $current_view_name) {
                unset($data[$view_name]);
            }
        }
        $session->setAttribute('__list', $data);
    }

    /**
     *
     * @param bool $default
     * @return string
     */
    final function getViewId($default = false) {
        return ($this->viewId != null) ? $this->viewId : $this->getId($default);
    }

    /**
     *
     * @param mixed $view_id
     */
    final function setViewId($view_id) {
        $this->viewId = $view_id;
    }

    /**
     *
     * @return mixed
     */
    final function getViewName() {
        return md5(get_class($this).'|'.$this -> getViewId());
    }

    /**
     *
     * @param bool $default
     * @return mixed
     */
    final function getId($default = false) {
        return ($this->id != null) ? $this->id : $default;
    }

    /**
     *
     * @param mixed $id
     */
    final function setId($id) {
        $this->id = $id;
    }
}