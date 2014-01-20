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

use Sketch\MySQLResultSet;
use Sketch\Resource;

/**
 * MySQL database resource driver
 *
 * @package Sketch\Resource\Database\Driver
 */
class MySQLDriver extends Driver {
    private $databaseName;

    private $connection;

    protected function connect($host, $database, $user, $password, $encoding) {
        $connection = mysql_connect($host, $user, $password);
        if ($connection) {
            if (mysql_select_db($database)) {
                $this->databaseName = $database;
                $this->connection = $connection;
                $this->executeUpdate("set names '$encoding'");
            } else {
                throw new \Exception($this->getTranslator()->_s("Couldn't connect to database $database"));
            }
        } else {
            throw new \Exception($this->getTranslator()->_s("Couldn't open a connection to $host"));
        }
    }

    protected function close() {
        if ($this->connection != null) {
            mysql_close($this->connection);
        }
    }

    function getTables($do_not_show = null) {
        $do_not_show = (is_array($do_not_show)) ? array_merge(array('WB_cache'), $do_not_show) : array('WB_cache');
        $output = array();
        foreach ($this->queryArray("SHOW TABLES FROM `$this->databaseName`") as $table) {
            if (!in_array($table, $do_not_show)) {
                $output[$table] = $table;
            }
        }
        return $output;
    }

    /**
     * @param string $table_name
     * @return array
     */
    function getTableDefinition($table_name) {
        $result_set = $this->executeQuery("SHOW COLUMNS FROM `$table_name`");
        $o = array(
            'fields' => array(),
            'templates' => array(
                'constructor' => "\t\t\t\$mixed = \$this->getConnection()->queryRow(\"SELECT * FROM `${table_name}` WHERE %1\$s = \".intval(\$mixed));\n",
                'insert' => "\t\t\t\$test = \$connection->executeUpdate(\"INSERT INTO `${table_name}` (%2\$s) VALUES (%3\$s)\");\n\t\t\tif (\$test) \$this->setId(\$connection->queryFirst(\"SELECT LAST_INSERT_ID()\"));\n\t\t\treturn \$test;\n",
                'update' => "\t\t\treturn \$connection->executeUpdate(\"UPDATE `${table_name}` SET %2\$s WHERE %1\$s = \$id\");\n",
                'delete' => "\t\t\treturn \$connection->executeUpdate(\"DELETE FROM `${table_name}` WHERE %1\$s = \$id\");\n"
            )
        );
        foreach ($result_set as $r) {
            $o['fields'][$r['Field']] = array(
                'type' => $r['Type'],
                'default' => $r['Default'],
                'null' => ($r['Null'] == 'YES')
            );
        }
        return $o;
    }

    /**
     * @param string $string
     * @return string
     */
    function escapeString($string) {
        return mysql_real_escape_string(trim($string), $this->connection);
    }

    /**
     * Execute query expression and return result set
     *
     * @param string $expression
     * @throws \Exception
     * @return \Sketch\Resource\Database\Driver\MySQLResultSet
     */
    function executeQuery($expression) {
        if ($this->getContext()->getLayerName() == 'development') {
            $this->getLogger()->log(trim($expression).' ('.number_format(microtime(true) - $this->getApplication()->getStartTime(), 3).')', 4);
        }
        $result = mysql_query($expression, $this->connection);
        $error = mysql_error($this->connection);
        if ($error) {
            throw new \Exception($error.' '.$expression);
        }
        return new Resource\Database\Driver\MySQLResultSet($result);
    }

    /**
     * Execute update expression and return true on success
     *
     * @param string $expression
     * @throws \Exception
     * @return boolean
     */
    function executeUpdate($expression) {
        if ($this->getContext()->getLayerName() == 'development') {
            $this->getLogger()->log($expression, 3);
        }
        mysql_query($expression, $this->connection);
        $error = mysql_error($this->connection);
        if ($error) {
            throw new \Exception($error.' '.$expression);
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
        return false;
    }
}
