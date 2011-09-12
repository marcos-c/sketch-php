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
 * SketchRequest
 */
class SketchRequest extends SketchObject {
    /** @var bool */
    private $json;

    /** @var null|string */
    private $onForwardReturn = null;

    /** @var string */
    private $method;

    /** @var bool */
    private $redirect;

    /** @var string */
    private $serverProtocol;

    /** @var string */
    private $serverName;

    /** @var int */
    private $serverPort;

    /** @var string */
    private $documentRoot;

    /** @var string */
    private $uri;

    /** @var string */
    private $resolvedURI;

    /** @var array */
    private $attributes = array();

    /**
     * Encode
     *
     * @static
     * @throws Exception
     * @param $string
     * @return string
     */
    private static function encode($string) {
        if (function_exists('mb_detect_encoding')) {
            switch (mb_detect_encoding($string, 'UTF-8, ISO-8859-1')) {
                case 'UTF-8': return $string;
                case 'ISO-8859-1': return iconv('ISO-8859-1', 'UTF-8', $string);
            }
        } else {
            $list = array('UTF-8', 'ISO-8859-1');
            foreach ($list as $item) {
                $sample = iconv($item, $item, $string);
                if (md5($sample) == md5($string)) {
                    switch ($item) {
                        case 'UTF-8': return $string;
                        case 'ISO-8859-1': return iconv('ISO-8859-1', 'UTF-8', $string);
                    }
                }
            }
        }
        throw new Exception('Wrong ENCODING. Sketch recomends UTF-8 but it can sort of work around ISO-8859-1, please provide a valid ENCODING');
    }

    /**
     * Constructor
     */
    function __construct() {
        $this->json = strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->redirect = isset($_SERVER['REDIRECT_URL']);
        $this->serverProtocol = ($_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        $this->serverName = $_SERVER['SERVER_NAME'];
        /**
         * Can't rely on $_SERVER['DOCUMENT_ROOT'] because it doesn't return what you would expect on all situations
         * (symbolic links, server configuration, etc.)
         */
        $this->serverPort = $_SERVER['SERVER_PORT'];
        $this->documentRoot = str_replace($_SERVER['SCRIPT_NAME'], '', realpath(basename($_SERVER['SCRIPT_NAME'])));
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->resolvedURI = $_SERVER['PHP_SELF'].(($_SERVER['QUERY_STRING'] != null) ? '?'.$_SERVER['QUERY_STRING'] : '');
        $this->attributes = array_merge($_COOKIE, $_GET, $_POST);
        foreach ($this->attributes as $key => $value) {
            if (is_array($value)) {
                array_walk_recursive($value, array('SketchRequest', 'encode'));
            } else {
                $value = SketchRequest::encode($value);
            }
            $this->attributes[$key] = $value;
        }
        if (is_array($_FILES)) foreach ($_FILES as $file) {
            foreach ($file['name'] as $form_name => $attributes) {
                foreach ($attributes as $attribute => $value) {
                    $descriptor = new SketchResourceFolderDescriptor();
                    $descriptor->setReference(base64_decode($attribute));
                    $descriptor->setFileName($file['tmp_name'][$form_name][$attribute]);
                    $descriptor->setSourceFileName($value);
                    $descriptor->setFileType($file['type'][$form_name][$attribute]);
                    $descriptor->setFileSize($file['size'][$form_name][$attribute]);
                    if ($descriptor->isImage()) {
                        list($width, $height) = getimagesize($descriptor->getFileName());
                        $descriptor->setImageWidth($width);
                        $descriptor->setImageHeight($height);
                    }
                    $this->attributes[$form_name]['attributes'][$attribute] = $descriptor;
                }
            }
        }
    }

    /**
     * Is JSON
     *
     * @return bool
     */
    function isJSON() {
        return $this->json;
    }

    /**
     * Get on forward return
     *
     * @return bool|null|string
     */
    function getOnForwardReturn() {
        return ($this->onForwardReturn != null) ? $this->onForwardReturn : false;
    }

    /**
     * Set on forward return
     *
     * @param $on_forward_return
     * @return void
     */
    function setOnForwardReturn($on_forward_return) {
        $this->onForwardReturn = $on_forward_return;
    }

    /**
     * Get method
     *
     * @return string
     */
    function getMethod() {
        return $this->method;
    }

    /**
     * Is redirect
     *
     * @return bool
     */
    function isRedirect() {
        return $this->redirect;
    }

    /**
     * Get server protocol
     *
     * @return string
     */
    function getServerProtocol() {
        return $this->serverProtocol;
    }

    /**
     * Get server name
     *
     * @return string
     */
    function getServerName() {
        return $this->serverName;
    }

    /**
     * Get server port
     *
     * @return int
     */
    function getServerPort() {
        return $this->serverPort;
    }

    /**
     * Get document root
     *
     * @return mixed|string
     */
    function getDocumentRoot() {
        return $this->documentRoot;
    }

    /**
     * Get URI
     *
     * @return string
     */
    function getURI() {
        return $this->uri;
    }

    /**
     * Get resolved URI
     *
     * @return string
     */
    function getResolvedURI() {
        return $this->resolvedURI;
    }

    /**
     * Get attributes
     *
     * @return array
     */
    function getAttributes() {
        return $this->attributes;
    }

    /**
     * Get attribute
     *
     * @param $key
     * @return bool
     */
    function getAttribute($key) {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        } else return false;
    }
}