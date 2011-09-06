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
 * MYSQLResultSet.
 *
 * @package Drivers
 */
class MySQLResultSet extends SketchObjectIterator {
    function rows() {
        return mysql_num_rows($this -> result);
    }

    function fetch($key) {
        mysql_data_seek($this -> result, $key);
        return mysql_fetch_assoc($this -> result);
    }

    protected function free() {
        // Ignore thrown exceptions
        try {
            mysql_free_result($this -> result);
        } catch (Exception $e) {}
    }
}

/**
 * MySQLConnectionDriver
 *
 * @package Sketch
 */
class MySQLConnectionDriver extends SketchConnectionDriver {
    private $databaseName;

    protected function connect($host, $database, $user, $password, $encoding) {
        $connection = mysql_connect($host, $user, $password);
        if ($connection) {
            if (mysql_select_db($database)) {
                $this -> databaseName = $database;
                $this -> connection = $connection;
                $this -> executeUpdate("set names '$encoding'");
            } else {
                throw new Exception($this->getTranslator()->_("Couldn't connect to database $database"));
            }
        } else {
            throw new Exception($this->getTranslator()->_("Couldn't open a connection to $host"));
        }
    }

    protected function close() {
        @mysql_close($this -> connection);
    }

    function getTables($do_not_show = null) {
        $do_not_show = (is_array($do_not_show)) ? array_merge(array('WB_cache'), $do_not_show) : array('WB_cache');
        $output = array();
        foreach ($this -> queryArray("SHOW TABLES FROM `$this->databaseName`") as $table) {
            if (!in_array($table, $do_not_show)) {
                $output[$table] = $table;
            }
        }
        return $output;
    }

    function getTableDefinition($table_name) {
        $result_set = $this -> executeQuery("SHOW COLUMNS FROM `$table_name`");
        $o = array('fields' => array(), 'templates' => array(
            'constructor' => "\t\t\t\$mixed = \$this->getConnection()->queryRow(\"SELECT * FROM `${table_name}` WHERE %1\$s = \".intval(\$mixed));\n",
            'insert' => "\t\t\t\t\$test = \$connection->executeUpdate(\"INSERT INTO `${table_name}` (%2\$s) VALUES (%3\$s)\");\n\t\t\t\tif (\$test) \$this->setId(\$connection->queryFirst(\"SELECT LAST_INSERT_ID()\"));\n\t\t\t\treturn \$test;\n",
            'update' => "\t\t\t\treturn \$connection->executeUpdate(\"UPDATE `${table_name}` SET %2\$s WHERE %1\$s = \$id\");\n",
            'delete' => "\t\t\treturn \$connection->executeUpdate(\"DELETE FROM `${table_name}` WHERE %1\$s = \$id\");\n"
        )); foreach ($result_set as $r) {
            $o['fields'][$r['Field']] = array(
                'type' => $r['Type'],
                'default' => $r['Default'],
                'null' => ($r['Null'] == 'YES')
            );
        } return $o;
    }

    function escapeString($string) {
        return mysql_real_escape_string(trim($string), $this->connection);
    }

    function executeQuery($expression) {
        if ($this->getContext()->getLayerName() == 'development') {
            $this->getLogger()->log(trim($expression).' ('.number_format(microtime(true) - $this->getApplication()->getStartTime(), 3).')', 4);
        }
        $result = mysql_query($expression, $this -> connection);
        $error = mysql_error($this -> connection);
        if ($error) {
            throw new Exception($error.' '.$expression);
        } return new MySQLResultSet($result);
    }

    function executeUpdate($expression) {
        if ($this->getContext()->getLayerName() == 'development') {
            $this->getLogger()->log($expression, 3);
        }
        mysql_query($expression, $this -> connection);
        $error = mysql_error($this -> connection);
        if ($error) {
            throw new Exception($error.' '.$expression);
        } else return true;
    }
}
