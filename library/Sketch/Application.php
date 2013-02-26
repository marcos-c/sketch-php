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

class Application {
    const WITH_PROTOCOL_AND_DOMAIN = 1;

    /**
     * @var Application
     */
    private static $instance = null;

    /**
     * @var float
     */
    private $startTime;

    /**
     * @var ResourceContext
     */
    private $context;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var Controller
     */
    private $controller;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var array
     */
    private $sessionNotices = array();

    /**
     * @var array
     */
    private $notices = array();

    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var string
     */
    private $documentRoot;

    /**
     * @var string
     */
    private $uri;

    /**
     * @return Application
     */
    static function getInstance() {
        if (self::$instance == null) {
            $class = __CLASS__;
            self::$instance = new $class;
        } return self::$instance;
    }

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @throws \ErrorException
     */
    static function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
        if (!in_array($errno, array(E_NOTICE, E_STRICT, E_DEPRECATED))) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
    }

    private function __construct() {
        date_default_timezone_set('UCT');
        if (date_default_timezone_get() != 'UCT') {
            exit('Error during initialization, can\'t change default timezone to UCT');
        }
        $this->setLogger(new LoggerDummy());
    }

    function load($document_root, $test = false) {
        // Initialize application and context
        if (!$test) {
            header("Content-Type: text/html; charset=UTF-8");
        }
        set_error_handler(array('\Sketch\Application', 'exceptionErrorHandler'));
        $this->setStartTime(microtime(true));
        $this->setDocumentRoot($document_root);
        $this->setContext(
            ResourceFactory::getContext(defined('CONTEXT_XML') ? CONTEXT_XML : $document_root.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'context.xml')
        );
        // Initialize request
        $this->setRequest(new Request());
        // Initialize session and ACL
        $this->setSession($test ? new SessionMock() : new Session());
        if (!($this->getSession()->getACL() instanceof SessionACL)) {
            $acl = new SessionACL();
            $acl->addRule('guest');
            $this->getSession()->setACL($acl);
        }
        // Initialize default locale
        $context = $this->getContext()->queryFirst('//context');
        $locale_string = ($context) ? $context->getAttribute('locale') : false;
        $this->setDefaultLocale(
            ($locale_string) ? Locale::fromString($locale_string) : new Locale('en')
        );
        // Initialize logger
        $this->setLogger(new LoggerSimple());
        // Initialize connection
        try {
            $this->setConnection(
                ResourceFactory::getConnection($this->getContext())
            );
        } catch (ResourceConnectionException $e) {

        }
        // Initialize controller
        $this->setController(new Controller());
        $this->getController()->setRouter(
            RouterFactory::getRouter($this->getRequest(), $test)
        );
         // Output response
        if ($this->getRequest()->isJSON()) {
            $this->getController()->setResponse(new ResponseJSON());
            print json_encode($this->getController()->getResponse());
        } else {
            $this->getController()->setResponse(new Response());
            $this->getController()->setResponseFilters($this->getContext());
            print $this->getController()->getResponse();
        }
    }

    /**
     * @return float
     */
    function getStartTime() {
        return $this->startTime;
    }

    /**
     * @param float $start_time
     */
    function setStartTime($start_time) {
        $this->startTime = $start_time;
    }

    /**
     * @return ResourceContext
     */
    function getContext() {
        return $this->context;
    }

    /**
     * @param ResourceContext $context
     */
    function setContext(ResourceContext $context) {
        $this->context = $context;
    }

    /**
     * @return Logger
     */
    function getLogger() {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     */
    function setLogger(Logger $logger) {
        $this->logger = $logger;
    }

    /**
     * @return ResourceConnection
     */
    function getConnection() {
        return $this->connection;
    }

    /**
     * @param ResourceConnection $connection
     */
    function setConnection(ResourceConnection $connection = null) {
        $this->connection = $connection;
    }

    /**
     * @return Controller
     */
    function getController() {
        return $this->controller;
    }

    /**
     * @param Controller $controller
     */
    function setController(Controller $controller) {
        $this->controller = $controller;
    }

    /**
     * @return Request
     */
    function getRequest() {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    function setRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * @return Session
     */
    function getSession() {
        return $this->session;
    }

    /**
     * @param Session $session
     */
    function setSession(Session $session) {
        $this->session = $session;
        if (is_array($session->getAttribute('__notices'))) {
            $this->sessionNotices = $session->getAttribute('__notices');
            $session->setAttribute('__notices', null);
        }
    }

    /**
     * @param boolean $skipFormNotices
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
     * @param ApplicationNotice $notice
     */
    function addNotice(ApplicationNotice $notice) {
        if ($notice->getNoticeType() == ApplicationNotice::A_ERROR) {
            die($notice->getMessage());
        } else {
            $this->notices[] = $notice;
        }
    }

    /**
     * @return Locale
     */
    function getLocale() {
        return $this->locale;
    }

    /**
     * @param Locale $locale
     */
    function setLocale(Locale $locale) {
        $this->locale = $locale;
        $this->getSession()->setAttribute('__locale', $locale->toString());
    }

    /**
     * @param Locale $default_locale
     */
    function setDefaultLocale(Locale $default_locale) {
        try {
            try {
                $this->setLocale(new Locale($this->getRequest()->getAttribute('language')));
            } catch (\Exception $e) {
                $this->locale = Locale::fromString($this->getSession()->getAttribute('__locale'));
            }
        } catch (\Exception $e) {
            $this->setLocale($default_locale);
        }
    }

    /**
     * @return string
     */
    function getDocumentRoot() {
        return $this->documentRoot;
    }

    /**
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
     * @param null $options
     * @return string
     */
    function getURI($options = null) {
        if ($options == self::WITH_PROTOCOL_AND_DOMAIN) {
            $server_protocol = $this->getRequest()->getServerProtocol();
            $server_name = $this->getRequest()->getServerName();
            $server_port = $this->getRequest()->getServerPort();
            $server_port = ($server_port != 80) ? ":$server_port" : "";
            return $server_protocol.'://'.$server_name.$server_port.$this->uri;
        } else return $this->uri;
    }
}