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

require_once 'Sketch/Object.php';

/**
 * SketchRequest
 *
 * @package Sketch
 */
class SketchRequest extends SketchObject {
    /**
     *
     * @var boolean
     */
    private $json;

    /**
     *
     * @var boolean
     */
    private $fileUpload;

    /**
     *
     * @var string
     */
    private $onForwardReturn = null;

    /**
     *
     * @var string
     */
    private $method;

    /**
     *
     * @var boolean
     */
    private $redirect;

    /**
     *
     * @var string
     */
    private $serverProtocol;

    /**
     *
     * @var string
     */
    private $serverName;

    /**
     *
     * @var integer
     */
    private $serverPort;

    /**
     *
     * @var string
     */
    private $acceptLanguage;

    /**
     *
     * @var string
     */
    private $documentRoot;

    /**
     *
     * @var string
     */
    private $uri;

    /**
     *
     * @var string
     */
    private $resolvedURI;

    /**
     *
     * @var array
     */
    private $attributes = array();

    function __construct() {
        $this->json = strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->redirect = isset($_SERVER['REDIRECT_URL']);
        $this->serverProtocol = ($_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        $this->serverName = $_SERVER['SERVER_NAME'];
        // Can't rely on $_SERVER['DOCUMENT_ROOT'] because it doesn't return what you would
        // expect on all situations (symbolic links, server configuration, etc.)
        $this->serverPort = $_SERVER['SERVER_PORT'];
        $this->acceptLanguage = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
        $this->documentRoot = str_replace($_SERVER['SCRIPT_NAME'], '', realpath(basename($_SERVER['SCRIPT_NAME'])));
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->resolvedURI = $_SERVER['PHP_SELF'].(($_SERVER['QUERY_STRING'] != null) ? '?'.$_SERVER['QUERY_STRING'] : '');
        $this->attributes = array_merge($_COOKIE, $_GET, $_POST);
        if (is_array($_FILES) && count($_FILES) > 0) {
            $this->fileUpload = true;
            foreach ($_FILES as $file) {
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
        } else {
            $this->fileUpload = false;
        }
    }

    /**
     *
     * @return boolean
     */
    function isJSON() {
        return $this->json;
    }

    /**
     *
     * @return boolean
     */
    function isFileUpload() {
        return $this->fileUpload;
    }

    /**
     *
     * @return string
     */
    function getOnForwardReturn() {
        return ($this->onForwardReturn != null) ? $this->onForwardReturn : false;
    }

    /**
     *
     * @param string $on_forward_return
     */
    function setOnForwardReturn($on_forward_return) {
        $this->onForwardReturn = $on_forward_return;
    }

    /**
     *
     * @return string
     */
    function getMethod() {
        return $this->method;
    }

    /**
     *
     * @return boolean
     */
    function isRedirect() {
        return $this->redirect;
    }

    /**
     *
     * @return string
     */
    function getServerProtocol() {
        return $this->serverProtocol;
    }

    /**
     *
     * @return string
     */
    function getServerName() {
        return $this->serverName;
    }

    /**
     *
     * @return integer
     */
    function getServerPort() {
        return $this->serverPort;
    }

    /**
     *
     * @return string
     */
    function getAcceptLanguage() {
        return $this->acceptLanguage;
    }

    /**
     *
     * @param string $accept_language
     */
    function setAcceptLanguage($accept_language) {
        $this->acceptLanguage = $accept_language;
    }

    /**
     *
     * @return string
     */
    function getDocumentRoot() {
        return $this->documentRoot;
    }

    /**
     *
     * @return string
     */
    function getURI() {
        return $this->uri;
    }

    /**
     *
     * @return string
     */
    function getResolvedURI() {
        return $this->resolvedURI;
    }

    /**
     *
     * @return array
     */
    function getAttributes() {
        return $this->attributes;
    }

    /**
     *
     * @param $key
     * @param bool $default
     * @return bool
     */
    function getAttribute($key, $default = false) {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        } else {
            return $default;
        }
    }

    /**
     *
     * @param $key
     * @param $value
     */
    function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
    }
}