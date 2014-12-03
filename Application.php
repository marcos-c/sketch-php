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
require_once 'Sketch/Application/Notice.php';
require_once 'Sketch/Resource/Factory.php';
require_once 'Sketch/Logger/Dummy.php';
require_once 'Sketch/Controller.php';
require_once 'Sketch/Request.php';
require_once 'Sketch/Session.php';
require_once 'Sketch/Form.php';
require_once 'Sketch/Form/Notice.php';

define('WITH_PROTOCOL_AND_DOMAIN', 1);

/**
 * SketchApplication
 *
 * @package Sketch
 */
class SketchApplication {
    /**
     *
     * @var SketchApplication
     */
    private static $instance = null;

    /**
     *
     * @var float
     */
    private $startTime;

    /**
     *
     * @var SketchResourceContext
     */
    private $context;

    /**
     *
     * @var SketchLogger
     */
    private $logger;

    /**
     *
     * @var SketchResourceConnection
     */
    private $connection;

    /**
     *
     * @var SketchController
     */
    private $controller;

    /**
     *
     * @var SketchRequest
     */
    private $request;

    /**
     *
     * @var SketchSession
     */
    private $session;

    /**
     *
     * @var array
     */
    private $sessionNotices = array();

    /**
     *
     * @var array
     */
    private $notices = array();

    /**
     *
     * @var SketchLocale
     */
    private $locale;

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
     * @return SketchApplication
     */
    static function getInstance() {
        if (self::$instance == null) {
            $class = __CLASS__;
            self::$instance = new $class;
        } return self::$instance;
    }

    /**
     *
     * @param <type> $errno
     * @param <type> $errstr
     * @param <type> $errfile
     * @param <type> $errline
     */
    static function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
        if (version_compare(PHP_VERSION, '5.3') === 1) {
            if (!in_array($errno, array(E_NOTICE, E_STRICT, E_DEPRECATED))) {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        } else {
            if (!in_array($errno, array(E_NOTICE, E_STRICT))) {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        }
    }

    /**
     *
     */
    private function __construct() {
        date_default_timezone_set('UCT');
        if (date_default_timezone_get() != 'UCT') {
            exit('Error during initialization, can\'t change default timezone to UCT');
        }
        $this->setLogger(new SketchLoggerDummy());
    }

    /**
     *
     * @return float
     */
    function getStartTime() {
        return $this->startTime;
    }

    /**
     *
     * @param float $start_time
     */
    function setStartTime($start_time) {
        $this->startTime = $start_time;
    }

    /**
     *
     * @return SketchResourceContext
     */
    function getContext() {
        return $this->context;
    }

    /**
     *
     * @param SketchResourceContext $context
     */
    function setContext(SketchResourceContext $context) {
        $this->context = $context;
    }

    /**
     *
     * @return SketchLogger
     */
    function getLogger() {
        return $this->logger;
    }

    /**
     *
     * @param SketchLogger $logger 
     */
    function setLogger(SketchLogger $logger) {
        $this->logger = $logger;
    }

    /**
     *
     * @return SketchResourceConnection
     */
    function getConnection() {
        return $this->connection;
    }

    /**
     *
     * @param SketchResourceConnection $connection 
     */
    function setConnection(SketchResourceConnection $connection = null) {
        $this->connection = $connection;
    }

    /**
     *
     * @return SketchController
     */
    function getController() {
        return $this->controller;
    }

    /**
     *
     * @param SketchController $controller
     */
    function setController(SketchController $controller) {
        $this->controller = $controller;
    }

    /**
     *
     * @return SketchRequest
     */
    function getRequest() {
        return $this->request;
    }

    /**
     *
     * @param SketchRequest $request
     */
    function setRequest(SketchRequest $request) {
        $this->request = $request;
    }

    /**
     *
     * @return SketchSession
     */
    function getSession() {
        return $this->session;
    }

    /**
     *
     * @param SketchSession $session
     */
    function setSession(SketchSession $session) {
        $this->session = $session;
        if (is_array($session->getAttribute('__notices'))) {
            $this->sessionNotices = $session->getAttribute('__notices');
            $session->setAttribute('__notices', null);
        }
    }

    /**
     *
     * @return array
     */
    function getNotices($skipFormNotices = true) {
        if (is_array($this->notices)) {
            if (is_array($this->sessionNotices)) {
                $all_notices = $this->sessionNotices + $this -> notices;
            } else {
                $all_notices = $this->notices;
            }
            if ($skipFormNotices) {
                $notices = array();
                foreach ($all_notices as $notice) {
                    if (!($notice instanceof FormNotice)) array_push($notices, $notice);
                }
                return $notices;
            } else {
                return $all_notices;
            }
        } else {
            return array();
        }
    }

    /**
     *
     * @param SketchApplicationNotice $notice 
     */
    function addNotice(SketchApplicationNotice $notice) {
        if ($notice->getNoticeType() == A_ERROR) {
            die($notice->getMessage());
        } else {
            $this->notices[] = $notice;
        }
    }

    /**
     *
     * @return SketchLocale 
     */
    function getLocale() {
        return $this->locale;
    }

    /**
     *
     * @param SketchLocale $locale 
     */
    function setLocale(SketchLocale $locale) {
        $this->locale = $locale;
        $this->getSession()->setAttribute('__locale', $locale->toString());
    }

    /**
     *
     * @param SketchLocale $default_locale
     */
    function setDefaultLocale(SketchLocale $default_locale) {
        try {
            $this->setLocale(new SketchLocale($this->getRequest()->getAttribute('language')));
        } catch (Exception $e) {
            try {
                $this->locale = SketchLocale::fromString($this->getSession()->getAttribute('__locale'));
            } catch (Exception $e) {
               $this->setLocale($default_locale);
            }
        }
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
     * @param string $document_root
     */
    function setDocumentRoot($document_root) {
        $this->documentRoot = $document_root;
        // Can't rely on $_SERVER['DOCUMENT_ROOT'] because it doesn't return what you would
        // expect on all situations (symbolic links, server configuration, etc.)
        $server_document_root = str_replace($_SERVER['SCRIPT_NAME'], '', realpath(basename($_SERVER['SCRIPT_NAME'])));
        $this->uri = str_replace($server_document_root, '', $document_root);
    }
    
    /**
     *
     * @return string
     */
    function getURI($options = null) {
        if ($options == WITH_PROTOCOL_AND_DOMAIN) {
            $server_protocol = $this->getRequest()->getServerProtocol();
            $server_name = $this->getRequest()->getServerName();
            $server_port = $this->getRequest()->getServerPort();
            $server_port = ($server_port != 80) ? ":$server_port" : "";
            return $server_protocol.'://'.$server_name.$server_port.$this->uri;
        } else return $this->uri;
    }
}