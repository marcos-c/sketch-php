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
require_once 'Sketch/Response/Part.php';

/**
 * SketchResponse
 */
class SketchResponse extends SketchObject {
    /** @var bool */
    private $isXHTML = false;

    /** @var bool */
    private $forceEncoding = true;

    /** @var DOMDocument */
    protected $document;

    /**
     * HTML
     *
     * @static
     * @return SketchResponse
     */
    static function HTML() {
        $response = new SketchResponse();
        $response->setIsXHTML(false);
        return $response;
    }

    /**
     * XHTML
     *
     * @static
     * @return SketchResponse
     */
    static function XHTML() {
        return new SketchResponse();
    }

    /**
     * To string
     *
     * @return string
     */
    function  __toString() {
        if ($this->isXHTML()) {
            return $this->document->saveXML();
        } else {
            return $this->document->saveHTML();
        }
    }

    /**
     * Get is XHTML
     *
     * @return bool
     */
    function getIsXHTML() {
        return $this->isXHTML;
    }

    /**
     * Is XHTML
     *
     * @return bool
     */
    function isXHTML() {
        return $this->getIsXHTML();
    }

    /**
     * Get force encoding
     *
     * @return bool
     */
    function getForceEncoding() {
        return $this->forceEncoding;
    }

    /**
     * Set force encoding
     *
     * @param $force_encoding
     * @return void
     */
    function setForceEncoding($force_encoding) {
        $this->forceEncoding = $force_encoding;
    }

    /**
     * Set is XHTML
     *
     * @param $is_xhtml
     * @return void
     */
    function setIsXHTML($is_xhtml) {
        $this->isXHTML = $is_xhtml;
    }

    /**
     * Get DOMDocument
     *
     * @return DOMDocument
     */
    function getDocument() {
        return $this->document;
    }

    /**
     * Set DOMDocument
     *
     * @param DOMDocument $document
     * @return void
     */
    function setDocument(DOMDocument $document) {
        $this->document = $document;
    }
}