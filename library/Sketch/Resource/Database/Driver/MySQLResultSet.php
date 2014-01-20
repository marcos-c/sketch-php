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

namespace Sketch\Resource\Database\Driver;

use Sketch\Core\ObjectIterator;

/**
 * MySQL resource set
 *
 * @package Sketch\Resource\Database\Driver
 */
class MySQLResultSet extends ObjectIterator {
    function rows() {
        return mysql_num_rows($this->result);
    }

    function fetch($key) {
        mysql_data_seek($this->result, $key);
        return mysql_fetch_assoc($this->result);
    }

    protected function free() {
        // Ignore thrown exceptions
        try {
            mysql_free_result($this->result);
        } catch (\Exception $e) {}
    }
}