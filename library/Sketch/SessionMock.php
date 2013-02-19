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

namespace Sketch;

class SessionMock extends Session {
    function __construct($session_name = 'sketch_session') {
        $context_name = strtr($this->getContext()->getAttribute('name'), '.', '_');
        if (defined('CONTEXT_PREFIX')) {
            $this->setContextName(CONTEXT_PREFIX.'_'.$context_name);
        } else {
            $this->setContextName($context_name);
        }
        /* session_name($session_name);
        session_set_cookie_params(self::SESSION_LIFETIME);
        session_cache_expire(self::SESSION_LIFETIME / 60);
        session_cache_limiter('nocache'); */
        $drivers = $this->getContext()->query("//driver[@type='SketchSessionSaveHandler']");
        foreach ($drivers as $driver) {
            $class = $driver->getAttribute('class');
            if (class_exists($class)) {
                /* session_set_save_handler(array($class, 'open'), array($class, 'close'), array($class, 'read'), array($class, 'write'), array($class, 'destroy'), array($class, 'gc')); */
            } else {
                throw new \Exception(sprintf("Can't instantiate class %s", $class));
            }
        }
        /* if (session_start() != false) {
            header("Cache-Control: private, max-age=0, post-check=0, pre-check=0");
            $this->attributes = &$_SESSION;
            if (isset($_COOKIE[$session_name])) {
                setcookie($session_name, $_COOKIE[$session_name], self::SESSION_LIFETIME > 0 ? time() + self::SESSION_LIFETIME : 0, "/");
            }
        } else {
            throw new \Exception();
        } */
    }
}