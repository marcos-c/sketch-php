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
 * SketchRouter
 */
abstract class SketchLogger extends SketchObject {
    /** @var int */
    static protected $level = 5;

    /**
     * Get level
     *
     * @return int
     */
    function getLevel() {
        return self::$level;
    }

    /**
     * Set level
     *
     * @param $level
     * @return void
     */
    function setLevel($level) {
        self::$level = $level;
    }

    /**
     * Log
     *
     * @abstract
     * @param $message
     * @param int $level
     * @return void
     */
    abstract function log($message, $level = 5);

    /**
     * Get messages
     *
     * @abstract
     * @return void
     */
    abstract function getMessages();
}