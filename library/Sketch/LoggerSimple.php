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

class LoggerSimple extends Logger {
    /**
     * @var array
     */
    static private $messages = array();

    /**
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
     * @param string $message
     * @param int $level
     * @return mixed|void
     */
    function log($message, $level = 5) {
        if ($level >= self::$level) {
            if (!is_string($message)) {
                $message = print_r($message, true);
            }
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
     * @return array
     */
    function getMessages() {
        return self::$messages;
    }
}