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

define('XML_HTML_UNKNOWN_TAG', 801);
define('XML_ERR_NAME_REQUIRED', 68);
define('XML_ERR_ENTITYREF_SEMICOL_MISSING', 23);

require_once 'Sketch/Resource.php';

/**
 * SketchResourceXML
 *
 * @package Sketch
 */
class SketchResourceXML extends SketchResource {
    /**
     *
     * @var DOMDocument
     */
    protected $document;

    /**
     *
     * @param DOMDocument $document 
     */
    function __construct(DOMDocument $document) {
        $this->document = $document;
    }

    /**
     *
     * @param string $attribute
     * @return string
     */
    function getAttribute($attribute) {
        return $this->document->documentElement->getAttribute($attribute);
    }

    /**
     *
     * @return array
     */
    function getAttributes() {
        $attributes = array();
        foreach ($this->document->documentElement->attributes as $attribute) {
            $attributes[$attribute->name] = $attribute->value;
        }
        return $attributes;
    }

    /**
     *
     * @return string
     */
    function getCharacterData() {
        $element = $this->document->documentElement;
        $o = null; if ($element->hasChildNodes()) {
            foreach ($element->childNodes as $node) {
                $document = new	DOMDocument('1.0');
                $document->appendChild($document->importNode($node, true));
                $o .= trim($document->saveHTML());
            }
        } return $o;
    }

    /**
     *
     * @param string $expression
     * @return array
     */
    function query($expression) {
        $context = new DOMXPath($this->document);
        $o = array(); foreach ($context->query($expression) as $node) {
            if ($node instanceof DOMElement) {
                $document = new DOMDocument();
                $document->preserveWhiteSpace = false;
                $document->resolveExternals = false;
                $document->appendChild($document->importNode($node, true));
                $o[] = new SketchResourceXML($document);
            } else if ($node instanceof DOMAttribute) {
                $o[] = $node->nodeValue;
            }
        } return $o;
    }

    /**
     *
     * @param string $expression
     * @return mixed
     */
    function queryFirst($expression) {
        return current($this->query($expression));
    }

    /**
     *
     * @param string $query_string
     * @return string
     */
    function queryCharacterData($query_string, $default = null) {
        $node = $this->queryFirst($query_string);
        return ($node) ? $node->getCharacterData() : $default;
    }
}