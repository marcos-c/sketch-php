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

require_once 'Sketch/Application.php';
require_once 'Sketch/Resource/Factory.php';
require_once 'Sketch/Resource/Context.php';
require_once 'Sketch/Logger/Simple.php';
require_once 'Sketch/Resource/Connection.php';
require_once 'Sketch/Request.php';
require_once 'Sketch/Controller.php';
require_once 'Sketch/Router/Factory.php';
require_once 'Sketch/Response.php';
require_once 'Sketch/Response/JSON.php';
require_once 'Sketch/Response/Exception.php';
require_once 'Sketch/Session.php';
require_once 'Sketch/Form.php';
require_once 'Sketch/DateTime.php';
require_once 'Sketch/DateTime/Iterator.php';
require_once 'Sketch/Mail/Message.php';
require_once 'Sketch/Mail/Transport.php';

try {
    // Initialize application and context
    $application = SketchApplication::getInstance();
    header("Content-Type: text/html; charset=UTF-8");
    set_error_handler(array('SketchApplication', 'exceptionErrorHandler'));
    $application->setStartTime(microtime(true));
    $application->setDocumentRoot(APPLICATION_PATH);
    $application->setContext(
        SketchResourceFactory::getContext(defined('CONTEXT_XML') ? CONTEXT_XML : APPLICATION_PATH.'/config/context.xml')
    );
    // Initialize request
    $application->setRequest(new SketchRequest());
    // Initialize session and ACL
    $application->setSession(new SketchSession());
    if (!($application->getSession()->getACL() instanceof SketchSessionACL)) {
        $acl = new SketchSessionACL();
        $acl->addRule('guest');
        $application->getSession()->setACL($acl);
    }
    // Initialize default locale
    $context = $application->getContext()->queryFirst('//context');
    $locale_string = ($context) ? $context->getAttribute('locale') : false;
    $application->setDefaultLocale(
        ($locale_string) ? SketchLocale::fromString($locale_string) : new SketchLocale('en')
    );
    // Initialize logger
    $application->setLogger(new SketchLoggerSimple());
    // Initialize connection
    $application->setConnection(
        SketchResourceFactory::getConnection($application->getContext())
    );
    // Initialize controller
    $application->setController(new SketchController());
    $application->getController()->setRouter(
        SketchRouterFactory::getRouter($application->getRequest())
    );
    // Output response
    if ($application->getRequest()->isJSON()) {
        $application->getController()->setResponse(new SketchResponseJSON());
        print SketchUtils::encodeJSON($application->getController()->getResponse());
    } else {
        $application->getController()->setResponse(new SketchResponse());
        $application->getController()->setResponseFilters($application->getContext());
        print $application->getController()->getResponse();
    }
} catch (Exception $e) {
    // Can't rely on $_SERVER['DOCUMENT_ROOT'] because it doesn't return what you would
    // expect on all situations (symbolic links, server configuration, etc.)
    $server_document_root = str_replace($_SERVER['SCRIPT_NAME'], '', realpath(basename($_SERVER['SCRIPT_NAME'])));
    $file_name = str_replace($server_document_root, '', $e->getFile());
    if ($application->getContext()->getLayerName() != 'production') {
        print '<pre>';
        print "<b>You have an exception!</b>\n".trim($e->getMessage())."\n<i>Thrown on line ".$e->getLine()." ($file_name)</i>\n";
        print "<b>Trace</b>\n";
        foreach ($e->getTrace() as $r) {
            if (array_key_exists('class', $r) && $r['function'] != 'exceptionErrorHandler') {
                print $r['class'].'.'.$r['function']."()\n";
            }
        }
        print '</pre>';
        if ($e instanceof SketchResponseException) {
            print "<pre style=\"padding: 10px; background-color: #ccc; overflow: auto;\"><b>Source</b>";
            foreach ($e->getStack() as $r) {
                $file_name = str_replace($server_document_root, '', $r->getFile());
                print $r->getMessage()." on line ".$r->getLine()." (".$file_name.")\n";
            }
            foreach ($e->getDebugInfo() as $r) {
                print $r;
            }
            print "</pre>";
        }
    } else {
        $parameters = $application->getContext()->getParametersFor('library', 'Stub.php');
        if ($parameters['send-exceptions-to']['email-address'] != null) {
            ob_start();
            print '<pre>';
            print "<b>You have an exception!</b>\n".trim($e->getMessage())."\n<i>Thrown on line ".$e->getLine()." ($file_name)</i>\n";
            print "<b>Trace</b>\n";
            foreach ($e->getTrace() as $r) {
                if (array_key_exists('class', $r) && $r['function'] != 'exceptionErrorHandler') {
                    print $r['class'].'.'.$r['function']."()\n";
                }
            }
            print '</pre>';
            if ($e instanceof SketchResponseException) {
                print "<pre style=\"padding: 10px; background-color: #ccc; overflow: auto;\"><b>Source</b>";
                foreach ($e->getStack() as $r) {
                    $file_name = str_replace($server_document_root, '', $r->getFile());
                    print $r->getMessage()." on line ".$r->getLine()." (".$file_name.")\n";
                }
                foreach ($e->getDebugInfo() as $r) {
                    print $r;
                }
                print "</pre>";
            }
            $message = new SketchMailMessage();
            $reply_to = new SketchMailAddress($parameters['send-exceptions-to']['email-address']);
            $message->setReplyTo($reply_to);
            $message->setFrom($reply_to);
            $message->addRecipient(MESSAGE_TO, $reply_to);
            if ($parameters['send-exceptions-to']['email-address-2'] != null) {
                $message->addRecipient(MESSAGE_CC, new SketchMailAddress($parameters['send-exceptions-to']['email-address-2']));
            }
            if ($parameters['send-exceptions-to']['email-address-3'] != null) {
                $message->addRecipient(MESSAGE_CC, new SketchMailAddress($parameters['send-exceptions-to']['email-address-3']));
            }
            if ($parameters['send-exceptions-to']['email-address-4'] != null) {
                $message->addRecipient(MESSAGE_CC, new SketchMailAddress($parameters['send-exceptions-to']['email-address-4']));
            }
            if ($parameters['send-exceptions-to']['email-address-5'] != null) {
                $message->addRecipient(MESSAGE_CC, new SketchMailAddress($parameters['send-exceptions-to']['email-address-5']));
            }
            $message->setSubject('Exception in Sketch');
            $message->setContent(ob_get_clean());
            SketchMailTransport::sendMessage($message);
        }
        print "An error has occurred, please try again later.";
    }
}
exit();