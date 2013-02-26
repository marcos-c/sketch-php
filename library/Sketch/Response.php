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

class Response extends Object {
    /**
     * @var \DOMDocument
     */
    protected $document;

    /**
     * @return string
     */
    function  __toString() {
        return $this->document->saveHTML();
    }

    /**
     * @return \DOMDocument
     */
    function getDocument() {
        return $this->document;
    }

    /**
     * @param \DOMDocument $document
     */
    function setDocument(\DOMDocument $document) {
        $this->document = $document;
    }
}