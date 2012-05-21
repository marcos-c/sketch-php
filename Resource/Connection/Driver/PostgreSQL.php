<?php
/**
 * This file is part of the Sketch Framework
 * (http://code.google.com/p/sketch-framework/)
 *
 * Copyright (C) 2011 Marcos Albaladejo Cooper
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
 */
class PostgreSQLResultSet extends SketchResourceConnectionResultSet {
    /**
     * Rows
     *
     * @return int
     */
    function rows() {
        return pg_num_rows($this->result);
    }

    /**
     * Fetch
     *
     * @param $key
     * @return array
     */
    function fetch($key) {
        return pg_fetch_assoc($this->result, $key);
    }

    /**
     * Free
     *
     * @return void
     */
    protected function free() {
        // Ignore thrown exceptions
        try {
            pg_free_result($this->result);
        } catch (Exception $e) {}
    }
}

/**
 * PostgreSQLConnectionDriver
 */
class PostgreSQLConnectionDriver extends SketchConnectionDriver {
    /** @var resource */
    private $connection;

    /**
     * Constructor
     *
     * @throws SketchResourceConnectionException
     * @param $host
     * @param $database
     * @param $user
     * @param $password
     * @param $encoding
     * @return void
     */
    protected function connect($host, $database, $user, $password, $encoding) {
        if (function_exists('pg_connect')) {
            $connection = pg_connect("dbname='$database' user='$user' password='$password'");
            if ($connection) {
                $this->connection = $connection;
                if (pg_set_client_encoding($this->connection, $encoding) === -1) {
                    throw new SketchResourceConnectionException($this->getTranslator()->_("Unsupported encoding to $host and $database"));
                }
            } else {
                throw new SketchResourceConnectionException($this->getTranslator()->_("Couldn't open a connection to $host and $database"));
            }
        } else {
            throw new SketchResourceConnectionException($this->getTranslator()->_("PostgreSQL functions are not available"));
        }
    }

    /**
     * Close
     *
     * @return void
     */
    protected function close() {
        pg_close($this->connection);
    }

    /**
     * Get tables
     *
     * @param null $do_not_show
     * @return array
     */
    function getTables($do_not_show = null) {
        return array();
    }

    /**
     * Get table definition
     *
     * @param $table_name
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
                    'options' => QUOTED_IDENTIFIERS
                );
            }
        }
        return $o;
    }

    /**
     * Escape string
     *
     * @param $string
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
     * @throws SketchResourceConnectionException
     * @param $expression
     * @return PostgreSQLResultSet
     */
    function executeQuery($expression) {
        if ($this->getContext()->getLayerName() == 'development') {
            $this->getLogger()->log(trim($expression).' ('.number_format(microtime(true) - $this->getApplication()->getStartTime(), 3).')', 4);
        }
        if (!pg_connection_busy($this->connection)) {
            @pg_send_query($this->connection, $expression);
        }
        $result = pg_get_result($this->connection);
        $error = pg_result_error_field($result, PGSQL_DIAG_SQLSTATE);
        if ($error) {
            // Reseting the connection fixes transaction problems
            pg_connection_reset($this->connection);
            throw new SketchResourceConnectionException(pg_result_error_field($result, PGSQL_DIAG_MESSAGE_PRIMARY).' '.$expression, $error);
        }
        return new PostgreSQLResultSet($result);
    }

    /**
     * Execute update expression and return true on success
     *
     * This version uses pg_result_error_field to get better error states so requires PHP v5.1 or later.
     *
     * @throws SketchResourceConnectionException
     * @param $expression
     * @return bool
     */
    function executeUpdate($expression) {
        if ($this->getContext()->getLayerName() == 'development') {
            $this->getLogger()->log(trim($expression).' ('.number_format(microtime(true) - $this->getApplication()->getStartTime(), 3).')', 4);
        }
        if (!pg_connection_busy($this->connection)) {
            @pg_send_query($this->connection, $expression);
        }
        $result = pg_get_result($this->connection);
        $error = pg_result_error_field($result, PGSQL_DIAG_SQLSTATE);
        if ($error) {
            // Reseting the connection fixes transaction problems
            pg_connection_reset($this->connection);
            throw new SketchResourceConnectionException(pg_result_error_field($result, PGSQL_DIAG_MESSAGE_PRIMARY).' '.$expression, $error);
        }
        return true;
    }

    /**
     * Begin transaction
     *
     * @return bool
     */
    function beginTransaction() {
        return $this->executeUpdate("BEGIN");
    }

    /**
     * Commit transaction
     *
     * @return bool
     */
    function commitTransaction() {
        return $this->executeUpdate("COMMIT");
    }

    /**
     * Rollback transaction
     *
     * @return bool
     */
    function rollbackTransaction() {
        return $this->executeUpdate("ROLLBACK");
    }

    /**
     * Supports
     *
     * @param $attribute
     * @return bool
     */
    function supports($attribute) {
        switch ($attribute) {
            case 'nextval': return true;
        }
        return false;
    }
}