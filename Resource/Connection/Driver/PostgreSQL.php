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

require_once 'Sketch/Object/Iterator.php';
require_once 'Sketch/Resource/Connection/Driver.php';

/**
 * PostgreSQLResultSet.
 *
 * @package Drivers
 */
class PostgreSQLResultSet extends SketchObjectIterator {
    function rows() {
        return pg_num_rows($this->result);
    }

    function fetch($key) {
        return pg_fetch_assoc($this->result, $key);
    }

    protected function free() {
        // Ignore thrown exceptions
        try {
            pg_free_result($this->result);
        } catch (Exception $e) {}
    }
}

/**
 * PostgreSQLConnectionDriver
 *
 * @package Sketch
 */
class PostgreSQLConnectionDriver extends SketchConnectionDriver {
    private $connection;

    protected function connect($host, $database, $user, $password, $encoding) {
        if (function_exists('pg_connect')) {
            $connection = pg_connect("dbname='$database' user='$user' password='$password'");
            if ($connection) {
                $this->connection = $connection;
                pg_set_client_encoding($this->connection, $encoding);
            } else {
                throw new Exception($this->getTranslator()->_("Couldn't open a connection to $host and $database"));
            }
        } else {
            throw new Exception($this->getTranslator()->_("PostgreSQL functions are not available"));
        }
    }

    protected function close() {
        pg_close($this->connection);
    }

    function getTables($do_not_show = null) {
        return array();
    }

    function getTableDefinition($expression) {
        preg_match('/^(\w+)(?:\.(\w+))?$/', $expression, $matches);
        $nspname = (array_key_exists(2, $matches)) ? $matches[1] : 'public';
        $relnamespace = $this->queryFirst("SELECT oid FROM pg_namespace WHERE nspname = '$nspname'");
        $relname = (array_key_exists(2, $matches)) ? $matches[2] : $matches[1];
        $o = array('fields' => array(), 'templates' => array(
            'constructor' => "\t\t\t\$mixed = \$this->getConnection()->queryRow(\"SELECT * FROM `${table_name}` WHERE %1\$s = \".intval(\$mixed));\n",
            'insert' => "\t\t\t\t\$this->setId(\$connection->queryFirst(\"SELECT nextval('${table_name}_%1\$s_seq')\"));\n\t\t\t\treturn \$connection->executeUpdate(\"INSERT INTO `${table_name}` (%2\$s) VALUES (%3\$s)\");\n",
            'update' => "\t\t\t\treturn \$connection->executeUpdate(\"UPDATE `${table_name}` SET %2\$s WHERE %1\$s = \$id\");\n",
            'delete' => "\t\t\treturn \$connection->executeUpdate(\"DELETE FROM `${table_name}` WHERE %1\$s = \$id\");\n"
        )); foreach ($this->executeQuery("SELECT oid, relnatts FROM pg_class WHERE relnamespace = '$relnamespace' AND relname = '$relname'") as $row) {
            foreach ($this->executeQuery("SELECT a.attname, t.typname, a.attnotnull FROM pg_type AS t JOIN pg_attribute AS a ON t.oid = a.atttypid WHERE a.attrelid = '".$row['oid']."' AND a.attnum > 0") as $column) {
                // TODO Add default value
                $o['fields'][$column['attname']] = array('type' => $column['typname'], 'null' => ($column['attnotnull'] == 't'), 'options' => QUOTED_IDENTIFIERS);
            }
        } return $o;
    }

    function escapeString($string) {
        return pg_escape_string($this->connection, trim($string));
    }

    function createStatement($expression) {
        return new PostgreSQLStatement($this->connection, $expression);
    }

    function executeQuery($expression) {
        if (!pg_connection_busy($this->connection)) {
            pg_send_query($this->connection, $expression);
        } $result = pg_get_result($this->connection);
        $error = pg_result_error($result);
        if ($error) {
            throw new Exception($error.' '.$expression);
        } return new PostgreSQLResultSet($result);
    }

    function executeUpdate($expression) {
        if (!pg_connection_busy($this->connection)) {
            pg_send_query($this->connection, $expression);
        } $result = pg_get_result($this->connection);
        $error = pg_result_error($result);
        if ($error) {
            throw new Exception($error.' '.$expression);
        } return true;
    }
}