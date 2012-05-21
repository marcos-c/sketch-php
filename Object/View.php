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

/**
 * SketchObjectView
 */
abstract class SketchObjectView extends SketchObject {
    /** @var bool */
    private $useSessionObject = false;

    /** @var string */
    private $viewId;

    /** @var string */
    private $id;

    /**
     * Get use session object
     *
     * @return bool
     */
    function getUseSessionObject() {
        return $this->useSessionObject;
    }

    /**
     * Set use session object
     *
     * @param $use_session_object
     * @return void
     */
    function setUseSessionObject($use_session_object) {
        $this->useSessionObject = $use_session_object;
    }

    /**
     * Get session object attribute
     *
     * @param $key
     * @param $default
     * @return string
     */
    protected final function getSessionObjectAttribute($key, $default) {
        $session = $this->getSession();
        $data = $session->getAttribute('__list');
        if (is_array($data) && array_key_exists($this->getViewName(), $data)) {
            if (array_key_exists($key, $data[$this->getViewName()])) {
                return $data[$this->getViewName()][$key];
            }
        }
        return $default;
    }

    /**
     * Set session object attribute
     *
     * @param $key
     * @param $value
     * @return void
     */
    protected final function setSessionObjectAttribute($key, $value) {
        $session = $this->getSession();
        $data = $session->getAttribute('__list');
        $data[$this->getViewName()][$key] = $value;
        $session->setAttribute('__list', $data);
    }

    /**
     * Get view id
     *
     * @param bool $default
     * @return mixed|string
     */
    final function getViewId($default = false) {
        return ($this->viewId != null) ? $this->viewId : $this->getId($default);
    }

    /**
     * Set view id
     *
     * @param $view_id
     * @return void
     */
    final function setViewId($view_id) {
        $this->viewId = $view_id;
    }

    /**
     * Get view name
     *
     * @return string
     */
    final function getViewName() {
        return md5(get_class($this).'|'.$this -> getViewId());
    }

    /**
     * Get id
     *
     * @param bool $default
     * @return bool|string
     */
    final function getId($default = false) {
        return ($this->id != null) ? $this->id : $default;
    }

    /**
     * Set id
     *
     * @param $id
     * @return void
     */
    final function setId($id) {
        $this->id = $id;
    }
}