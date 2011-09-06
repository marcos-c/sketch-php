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
 * SketchSessionSaveHandlerMySQL
 *
 * @package Sketch
 */
class SketchSessionSaveHandlerMySQL extends SketchObject {
    /**
     *
     * @var string
     */
    static private $savePath;

    /**
     *
     * @var string
     */
    static private $sessionName;

    /**
     *
     * @var string
     */
    static private $prefix;

    /**
     *
     * @var resource
     */
    static private $connection;

    /**
     *
     * @param string $save_path
     * @param string $session_name
     */
    static function open($save_path, $session_name) {
        self::$savePath = $save_path;
        self::$sessionName = $session_name;
        $driver = SketchApplication::getInstance()->getContext()->queryFirst("//driver[@type='SketchConnectionDriver']");
        self::$prefix = $driver->queryCharacterData('//table-prefix');
        $host = $driver->queryCharacterData('//host', 'localhost');
        $user = $driver->queryCharacterData('//user');
        $password = $driver->queryCharacterData('//password');
        $database = $driver->queryCharacterData('//database', $user);
        $encoding = $driver->queryCharacterData('//encoding', 'utf8');
        self::$connection = mysql_connect($host, $user, $password);
        if (self::$connection) {
            if (mysql_select_db($database)) {
                mysql_query("set names '$encoding'", self::$connection);
                return true;
            } else {
                die("Couldn't connect to database $database");
            }
        } else {
            die("Couldn't open a connection to $host");
        }
    }

    static function close() {
        mysql_close(self::$connection);
        return true;
    }

    static function read($id) {
        $id = "'".mysql_escape_string($id)."'";
        $save_path = "'".mysql_escape_string(self::$savePath)."'";
        $session_name = "'".mysql_escape_string(self::$sessionName)."'";
        $q = mysql_query("SELECT data FROM gbook_session WHERE id = $id AND save_path = $save_path AND session_name = $session_name", self::$connection);
        $r = mysql_fetch_array($q, MYSQL_ASSOC);
        return (string) $r['data'];
    }

    static function write($id, $data) {
        $id = "'".mysql_escape_string($id)."'";
        $data = "'".mysql_escape_string($data)."'";
        $save_path = "'".mysql_escape_string(self::$savePath)."'";
        $session_name = "'".mysql_escape_string(self::$sessionName)."'";
        mysql_query("REPLACE INTO gbook_session (id, data, save_path, session_name) VALUES ($id, $data, $save_path, $session_name)", self::$connection);
        return true;
    }

    static function destroy($id) {
        $id = "'".mysql_escape_string($id)."'";
        $save_path = "'".mysql_escape_string(self::$savePath)."'";
        $session_name = "'".mysql_escape_string(self::$sessionName)."'";
        mysql_query("DELETE FROM gbook_session WHERE id = $id AND save_path = $save_path AND session_name = $session_name", self::$connection);
        return true;
    }

    static function gc($maxlifetime) {
        $save_path = "'".mysql_escape_string(self::$savePath)."'";
        $session_name = "'".mysql_escape_string(self::$sessionName)."'";
        mysql_query("DELETE FROM gbook_session WHERE save_path = $save_path AND session_name = $session_name AND DATE_ADD(creation_timestamp, INTERVAL ".SESSION_LIFETIME." SECOND) < CURRENT_TIMESTAMP()", self::$connection);
        return true;
    }
}