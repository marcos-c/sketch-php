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

require_once 'Sketch/Logger.php';

/**
 * SketchLoggerSimple
 *
 * @package Sketch
 */
class SketchLoggerSimple extends SketchLogger {
    /**
     *
     * @var array
     */
    static private $messages = array();

    /**
     *
     * @var array
     */
    static private $md5 = array();

    function __construct() {
        $session = $this->getSession();
        $messages = $session->getAttribute('__log');
        if (is_array($messages)) {
            foreach ($messages as $message) {
                $this->log($message);
            }
            $session->setAttribute('__log', null);
        }
    }

    /**
     *
     * @param string $message
     */
    function log($message, $level = 5) {
        if ($level >= self::$level) {
            $md5 = md5($message);
            if (array_key_exists($md5, self::$md5)) {
                self::$messages[] = $message.' (x'.self::$md5[$md5]++.')';
            } else {
                self::$md5[$md5] = 1;
                self::$messages[] = $message;
            }
        }
    }

    /**
     *
     * @return array
     */
    function getMessages() {
        return self::$messages;
    }
}