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

define('SESSION_LIFETIME', 0);

/**
 * SketchSession
 */
class SketchSession extends SketchObject {
    /** @var array */
    private $attributes = array();

    /** @var string */
    private $contextName;

    /**
     * Constructor
     *
     * @throws Exception
     * @param string $session_name
     */
    function __construct($session_name = 'sketch_session') {
        $context_name = strtr($this->getContext()->getAttribute('name'), '.', '_');
        if (defined('CONTEXT_PREFIX')) {
            $this->setContextName(CONTEXT_PREFIX.'_'.$context_name);
        } else {
            $this->setContextName($context_name);
        }
        session_name($session_name);
        session_set_cookie_params(SESSION_LIFETIME);
        session_cache_expire(SESSION_LIFETIME / 60);
        session_cache_limiter('nocache');
        $drivers = $this->getContext()->query("//driver[@type='SketchSessionSaveHandler']");
        foreach ($drivers as $driver) {
            $type = $driver->getAttribute('type');
            $class = $driver->getAttribute('class');
            $source = $driver->getAttribute('source');
            if (SketchUtils::Readable("Sketch/Session/SaveHandler/$source")) {
                require_once "Sketch/Session/SaveHandler/$source";
                if (class_exists($class)) {
                    session_set_save_handler(array($class, 'open'), array($class, 'close'), array($class, 'read'), array($class, 'write'), array($class, 'destroy'), array($class, 'gc'));
                } else throw new Exception(sprintf("Can't instantiate class %s", $class));
            } else throw new Exception(sprintf("File %s can't be found", $source));
        }
        if (session_start() != false) {
            header("Cache-Control: private, max-age=0, post-check=0, pre-check=0");
            $this->attributes = &$_SESSION;
            if (isset($_COOKIE[$session_name])) {
                setcookie($session_name, $_COOKIE[$session_name], SESSION_LIFETIME > 0 ? time() + SESSION_LIFETIME : 0, "/");
            }
        } else {
            throw new Exception();
        }
    }

    /**
     * Get id
     *
     * @return string
     */
    function getId() {
        return session_id();
    }

    /**
     * Get name
     *
     * @return string
     */
    function getName() {
        return session_name();
    }

    /**
     * Get context name
     *
     * @return string
     */
    function getContextName() {
        return $this->contextName;
    }

    /**
     * Set context name
     *
     * @param $context_name
     * @return void
     */
    function setContextName($context_name) {
        $this->contextName = $context_name;
    }

    /**
     * Get attributes
     *
     * @return array
     */
    function getAttributes() {
        if (is_array($this->attributes) && array_key_exists($this->contextName, $this->attributes)) {
            return $this->attributes[$this->contextName];
        } else return array();
    }

    /**
     * Get attribute
     *
     * @param $key
     * @return bool
     */
    function getAttribute($key) {
        if (is_array($this->attributes) && array_key_exists($this->contextName, $this->attributes) && array_key_exists($key, $this->attributes[$this->contextName])) {
            return $this->attributes[$this->contextName][$key];
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
            $this->attributes[$this->contextName][$key] = $value;
        } else unset($this->attributes[$this->contextName][$key]);
    }

    /**
     * Get ACL
     *
     * @return SketchSessionACL
     */
    function getACL() {
        return $this -> getAttribute('__acl');
    }

    /**
     * Set ACL
     *
     * @param SketchSessionACL $acl
     * @return void
     */
    function setACL(SketchSessionACL $acl) {
        $this -> setAttribute('__acl', $acl);
    }

    /**
     * Set remember me
     *
     * @param $remember
     * @return void
     */
    function setRememberMe($remember) {
        if ($remember) {
            setcookie(session_name(), $_COOKIE[session_name()], time() + 604800);
        } else {
            setcookie(session_name(), $_COOKIE[session_name()]);
        }
    }

    /**
     * Invalidate
     *
     * @return void
     */
    function invalidate() {
        $this->attributes = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), null, time() - 3600, '/');
        }
        session_destroy();
    }
}
