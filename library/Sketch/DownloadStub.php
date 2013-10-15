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

// Initialize application and context
$application = Application::getInstance();
header("Content-Type: text/html; charset=UTF-8");
set_error_handler(array('SketchApplication', 'exceptionErrorHandler'));
$application->setStartTime(microtime(true));
$application->setDocumentRoot(APPLICATION_PATH);
$application->setContext(
    ResourceFactory::getContext(defined('CONTEXT_XML') ? CONTEXT_XML : APPLICATION_PATH.'/config/context.xml')
);
// Initialize request
$application->setRequest(new Request());
// Initialize session and ACL
$application->setSession(new Session());
if (!($application->getSession()->getACL() instanceof SessionACL)) {
    $acl = new SessionACL();
    $acl->addRole('guest');
    $application->getSession()->setACL($acl);
}
// Initialize default locale
$context = $application->getContext()->queryFirst('//context');
$locale_string = ($context) ? $context->getAttribute('locale') : false;
$application->setDefaultLocale(
    ($locale_string) ? Locale::fromString($locale_string) : new Locale('en')
);
// Initialize logger
$application->setLogger(new LoggerSimple());
// Initialize connection
$application->setConnection(
    ResourceFactory::getConnection($application->getContext())
);