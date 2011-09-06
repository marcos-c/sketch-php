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

define('A_ERROR', 1);
define('A_WARNING', 2);
define('A_NOTICE', 4);

/**
 * ApplicationNotice.
 *
 * @package System
 */
class SketchApplicationNotice {
    /**
     *
     * @var string
     */
    private $message;

    /**
     *
     * @var integer
     */
    private $noticeType;

    /**
     *
     * @param string $message
     * @param integer $type
     */
    function  __construct($message, $type = A_NOTICE) {
        $this->setMessage($message);
        $this->setNoticeType($type);
    }

    /**
     *
     * @return string
     */
    function __toString() {
        return $this->getMessage();
    }

    /**
     *
     * @return string
     */
    function getMessage() {
        return $this->message;
    }

    /**
     *
     * @param string $message
     */
    function setMessage($message) {
        $this->message = $message;
    }

    /**
     *
     * @return string
     */
    function getNoticeType() {
        return $this->noticeType;
    }

    /**
     *
     * @param string $type
     */
    function setNoticeType($type) {
        $this -> noticeType = $type;
    }
}