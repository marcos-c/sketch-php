<?php
/**
 * This file is part of the Sketch library
 *
 * @author Marcos Cooper <marcos@releasepad.com>
 * @version 3.0
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

namespace Sketch\Core;

use Sketch\Application\Application;

/**
 * Core job scheduler class
 *
 * @package Sketch\Core
 */
class JobScheduler extends Object {
    static function execute() {
        $application = Application::getInstance();
        $connection = $application->getConnection();
        $now = DateTime::Now();
        $date_time = $now->addInterval('-6 hours');
        $document_root = $application->getDocumentRoot();
        $uri = $application->getURI(Application::WITH_PROTOCOL_AND_DOMAIN);
        foreach ($connection -> query("SELECT * FROM job_scheduler WHERE last_execution_date_time IS NULL OR last_execution_date_time < '".$date_time->toString()."'") as $r) {
            exec("php ${document_root}/wget.php ${uri}$r[exec] &");
            $connection -> query("UPDATE job_scheduler SET last_execution_date_time = '".$now->toString()."' WHERE id = '$r[id]]'");
        }
    }
}