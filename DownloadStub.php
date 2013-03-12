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
$locale = SketchLocale::fromString('en');
if (!defined('CONTEXT_PREFIX')) {
    if (in_array($application->getRequest()->getAcceptLanguage(), $locale->getTranslator()->getDriver()->getAvailableLanguages())) {
        $locale = SketchLocale::fromString($application->getRequest()->getAcceptLanguage());
    }
} elseif ($context) {
    $locale = SketchLocale::fromString($context->getAttribute('locale'));
}
$application->setDefaultLocale($locale);
// Initialize logger
$application->setLogger(new SketchLoggerSimple());
// Initialize connection
$application->setConnection(
    SketchResourceFactory::getConnection($application->getContext())
);