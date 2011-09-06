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

require_once 'Sketch/Application.php';
require_once 'Sketch/Resource.php';
require_once 'Sketch/Resource/Connection.php';
require_once 'Sketch/Controller.php';
require_once 'Sketch/Request.php';
require_once 'Sketch/Session.php';
require_once 'Sketch/Locale.php';

/**
 * SketchObject
 *
 * @package Sketch
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
     * @return SketchLocaleTranslator
     */
    function getTranslator($reference = 'default') {
        return $this->getApplication()->getLocale()->getTranslator($reference);
    }

    /**
     *
     * @return SketchLocalFormatter
     */
    function getFormatter() {
        return $this->getApplication()->getLocale()->getFormatter();
    }
}