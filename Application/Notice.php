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

define('A_ERROR', 1);
define('A_WARNING', 2);
define('A_NOTICE', 4);

/**
 * ApplicationNotice.
 */
class SketchApplicationNotice {
    /** @var string */
    private $message;

    /** @var int */
    private $noticeType;

    /**
     * Constructor
     *
     * @param $message
     * @param int $type
     */
    function  __construct($message, $type = A_NOTICE) {
        $this->setMessage($message);
        $this->setNoticeType($type);
    }

    /**
     * Returns string representation of object
     *
     * @return string
     */
    function __toString() {
        return $this->getMessage();
    }

    /**
     * Get message
     *
     * @return string
     */
    function getMessage() {
        return $this->message;
    }

    /**
     * Set message
     *
     * @param $message
     * @return void
     */
    function setMessage($message) {
        $this->message = $message;
    }

    /**
     * Get notice type
     *
     * A_ERROR, A_WARNING, A_NOTICE.
     *
     * @return int
     */
    function getNoticeType() {
        return $this->noticeType;
    }

    /**
     * Set notice type
     *
     * A_ERROR, A_WARNING, A_NOTICE.
     *
     * @param $type
     * @return void
     */
    function setNoticeType($type) {
        $this -> noticeType = $type;
    }
}