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

class ResponseJSON extends Response {
    /**
     * @var string
     */
    public $html;

    /**
     * @var array
     */
    public $fragment;

    /**
     * @var string
     */
    public $forward = "";

    /**
     * @var string
     */
    public $forwardLocation = "";

    /**
     * @var array
     */
    public $log;

    /**
     * @param \DOMDocument $document
     */
    function setDocument(\DOMDocument $document) {
        $this->document = $document;
        if ($this->isXHTML()) {
            $context = new \DOMXPath($this->document);
            $context->registerNamespace('j', 'http://kunyomi.com/sketch/json');
            $q = $context->query('//j:response');
            if ($q instanceof \DOMNodeList) foreach ($q as $json_response) {
                /* @var $json_respone \DOMElement */
                $document = new \DOMDocument();
                $document->preserveWhiteSpace = false;
                $document->resolveExternals = false;
                if ($json_response->hasChildNodes()) foreach ($json_response->childNodes as $node) {
                    $document->appendChild($document->importNode($node, true));
                }
                if ($id = $json_response->getAttribute('id')) {
                    $this->fragment[$id] = $document->saveXML();
                } else {
                    $this->html = $document->saveXML();
                }
            }
        } else {
            $context = new \DOMXPath($this->document);
            $q = $context->query('//response');
            if ($q instanceof \DOMNodeList) foreach ($q as $json_response) {
                /* @var $json_respone \DOMElement */
                $document = new \DOMDocument();
                $document->preserveWhiteSpace = false;
                $document->resolveExternals = false;
                if ($json_response->hasChildNodes()) foreach ($json_response->childNodes as $node) {
                    $document->appendChild($document->importNode($node, true));
                }
                if ($id = $json_response->getAttribute('id')) {
                    $this->fragment[$id] = $document->saveHTML();
                } else {
                    $this->html = $document->saveHTML();
                }
            }
        }
        // Log with memory usage and response time
        $memory_usage = ceil(memory_get_usage() / 1024);
        $response_time = number_format(microtime(true) - $this->getApplication()->getStartTime(), 3);
        $this->log = array(
            'Memory usage: '.$memory_usage.'kb',
            'Response time: '.$response_time.'seg'
        );
        foreach ($this->getLogger()->getMessages() as $message) {
            $this->log[] = $message;
        }
    }
}