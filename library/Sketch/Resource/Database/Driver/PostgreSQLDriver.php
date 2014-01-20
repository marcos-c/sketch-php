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

use Sketch\Core\Factory;
use Sketch\Resource;

/**
 * PostgreSQL database resource driver
 *
 * @package Sketch\Resource\Database\Driver
 */
class PostgreSQL extends Driver {
    private $connection;

    protected function connect($host, $database, $user, $password, $encoding) {
        if (function_exists('pg_connect')) {
            $connection = pg_connect("dbname='$database' user='$user' password='$password'");
            if ($connection) {
                $this->connection = $connection;
                if (pg_set_client_encoding($this->connection, $encoding) === -1) {
                    throw new \Exception($this->getTranslator()->_s("Unsupported encoding to $host and $database"));
                }
            } else {
                throw new \Exception($this->getTranslator()->_s("Couldn't open a connection to $host and $database"));
            }
        } else {
            throw new \Exception($this->getTranslator()->_s("PostgreSQL functions are not available"));
        }
    }

    protected function close() {
        pg_close($this->connection);
    }

    function getTables($do_not_show = null) {
        return array();
    }

    /**
     * @param string $table_name
     * @return array
     */
    function getTableDefinition($table_name) {
        preg_match('/^(\w+)(?:\.(\w+))?$/', $table_name, $matches);
        $nspname = (array_key_exists(2, $matches)) ? $matches[1] : 'public';
        $relnamespace = $this->queryFirst("SELECT oid FROM pg_namespace WHERE nspname = '$nspname'");
        $relname = (array_key_exists(2, $matches)) ? $matches[2] : $matches[1];
        $o = array(
            'fields' => array(),
            'templates' => array(
                'constructor' => "\t\t\t\$mixed = \$this->getConnection()->queryRow(\"SELECT * FROM ${table_name} WHERE %1\$s = \".intval(\$mixed));\n",
                'insert' => "\t\t\t\$this->setId(\$connection->queryFirst(\"SELECT nextval('${table_name}_%1\$s_seq')\"));\n\t\t\treturn \$connection->executeUpdate(sprintf(\"INSERT INTO ${table_name} (%1\$s, %2\$s) VALUES (%%s, %3\$s)\", \$this->getId()));\n",
                'update' => "\t\t\treturn \$connection->executeUpdate(\"UPDATE ${table_name} SET %2\$s WHERE %1\$s = \$id\");\n",
                'delete' => "\t\t\treturn \$connection->executeUpdate(\"DELETE FROM ${table_name} WHERE %1\$s = \$id\");\n"
            )
        );
        foreach ($this->executeQuery("SELECT oid, relnatts FROM pg_class WHERE relnamespace = '$relnamespace' AND relname = '$relname'") as $row) {
            foreach ($this->executeQuery("SELECT a.attnum, a.attname, t.typname, a.attnotnull, a.atthasdef FROM pg_type AS t JOIN pg_attribute AS a ON t.oid = a.atttypid WHERE a.attrelid = '".$row['oid']."' AND a.attnum > 0") as $column) {
                $default = null;
                if ($column['atthasdef'] == 't') {
                    foreach ($this->executeQuery("SELECT * FROM pg_attrdef WHERE adrelid = '".$row['oid']."' AND adnum = '".$column['attnum']."'") as $attrdef) {
                        switch ($attrdef['adsrc']) {
                            case 'true': $default = 't'; break;
                            case 'false': $default = 'f'; break;
                        }
                    }
                }
                $o['fields'][$column['attname']] = array(
                    'type' => $column['typname'],
                    'default' => $default,
                    'null' => ($column['attnotnull'] != 't'),
                    'options' => Factory::QUOTED_IDENTIFIERS
                );
            }
        }
        return $o;
    }

    /**
     * @param string $string
     * @return string
     */
    function escapeString($string) {
        // I get one extrange error if I pass the connection
        return pg_escape_string(trim($string));
    }

    /**
     * Execute query expression and return result set
     *
     * This version uses pg_result_error_field to get better error states so requires PHP v5.1 or later.
     *
     * @param string $expression
     * @throws \Exception
     * @return \Sketch\Resource\Database\Driver\PostgreSQLResultSet
     */
    function executeQuery($expression) {
        if (!pg_connection_busy($this->connection)) {
            @pg_send_query($this->connection, $expression);
        }
        $result = pg_get_result($this->connection);
        $error = pg_result_error_field($result, PGSQL_DIAG_SQLSTATE);
        if ($error) {
            pg_connection_reset($this->connection); // Reseting the connection fixes transaction problems
            throw new \Exception(pg_result_error_field($result, PGSQL_DIAG_MESSAGE_PRIMARY).' '.$expression, $error);
        }
        return new Resource\Database\Driver\PostgreSQLResultSet($result);
    }

    /**
     * Execute update expression and return true on success
     *
     * This version uses pg_result_error_field to get better error states so requires PHP v5.1 or later.
     *
     * @param string $expression
     * @throws \Exception
     * @return boolean
     */
    function executeUpdate($expression) {
        if (!pg_connection_busy($this->connection)) {
            @pg_send_query($this->connection, $expression);
        }
        $result = pg_get_result($this->connection);
        $error = pg_result_error_field($result, PGSQL_DIAG_SQLSTATE);
        if ($error) {
            pg_connection_reset($this->connection); // Reseting the connection fixes transaction problems
            throw new \Exception(pg_result_error_field($result, PGSQL_DIAG_MESSAGE_PRIMARY).' '.$expression, $error);
        }
        return true;
    }

    /**
     * @return boolean
     */
    function beginTransaction() {
        return $this->executeUpdate("BEGIN");
    }

    /**
     * @return boolean
     */
    function commitTransaction() {
        return $this->executeUpdate("COMMIT");
    }

    /**
     * @return boolean
     */
    function rollbackTransaction() {
        return $this->executeUpdate("ROLLBACK");
    }

    /**
     * @param string $attribute
     * @return boolean
     */
    function supports($attribute) {
        switch ($attribute) {
            case 'nextval': return true;
        }
        return false;
    }
}