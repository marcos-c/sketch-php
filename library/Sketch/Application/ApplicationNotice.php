<?php
/**
 * This file is part of the Sketch library
 *
 * @author Marcos Cooper <marcos@releasepad.com>
 * @version 3.0
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

namespace Sketch\Application;

/**
 * Application notice class
 *
 * @package Sketch\Application
 */
class ApplicationNotice {
    const A_ERROR = 1;

    const A_WARNING = 2;

    const A_NOTICE = 4;

    /**
     * @var string
     */
    private $message;

    /**
     * @var integer
     */
    private $noticeType;

    /**
     * @param string $message
     * @param integer $type
     */
    function  __construct($message, $type = self::A_NOTICE) {
        $this->setMessage($message);
        $this->setNoticeType($type);
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getMessage();
    }

    /**
     * @return string
     */
    function getMessage() {
        return $this->message;
    }

    /**
     * @param string $message
     */
    function setMessage($message) {
        $this->message = $message;
    }

    /**
     * @return string
     */
    function getNoticeType() {
        return $this->noticeType;
    }

    /**
     * @param string $type
     */
    function setNoticeType($type) {
        $this -> noticeType = $type;
    }
}