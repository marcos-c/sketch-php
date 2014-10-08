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

abstract class ResourceConnectionDriver extends Resource {
    /**
     * @var string
     */
    private $tablePrefix;

    /**
     * @var string
     */
    private $database;

    /**
     * @param Resource $resource
     * @throws ResourceConnectionException
     */
    final function __construct(Resource $resource) {
        try {
            $this->tablePrefix = $resource->queryCharacterData('//table-prefix');
            $host = $resource->queryCharacterData('//host', 'localhost');
            $user = $resource->queryCharacterData('//user');
            $password = $resource->queryCharacterData('//password');
            $this->database = $resource->queryCharacterData('//database', $user);
            $encoding = $resource->queryCharacterData('//encoding', 'utf8');
            $this->connect($host, $this->database, $user, $password, $encoding);
        } catch (\Exception $e) {
            throw new ResourceConnectionException($e->getMessage());
        }
    }

    final function __destruct() {
        $this->close();
    }

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param string $encoding
     */
    abstract protected function connect($host, $user, $password, $database, $encoding);

    abstract protected function close();

    /**
     * @param $default
     * @return string
     */
    function getTablePrefix($default) {
        return $this->tablePrefix != null ? $this->tablePrefix : $default;
    }

    /**
     * @return string
     */
    function getDatabase() {
        return $this->database;
    }
    
    /**
     * @param mixed $do_not_show
     * @return array
     */
    abstract function getTables($do_not_show = null);
    
    /**
     * @param string $table_name
     * @return array
     */
    abstract function getTableDefinition($table_name);

    /**
     * @param string $string
     * @return string
     */
    abstract function escapeString($string);
    
    /**
     * @param string $expression
     * @return ObjectIterator
     */
    abstract function executeQuery($expression);

    /**
     * @param string $expression
     * @return boolean
     */
    abstract function executeUpdate($expression);

    /**
     * @return boolean
     */
    abstract function beginTransaction();

    /**
     * @return boolean
     */
    abstract function commitTransaction();

    /**
     * @return boolean
     */
    abstract function rollbackTransaction();

    /**
     * @param string $expression
     * @return ObjectIterator
     */
    function query($expression) {
        return $this->executeQuery($expression);
    }

    /**
     * @param string $expression
     * @return array
     */
    function queryRow($expression) {
        return $this->executeQuery($expression)->current();
    }

    /**
     * @param string $expression
     * @return mixed
     */
    function queryFirst($expression) {
        $row = $this->queryRow($expression);
        return ($row) ? current($row) : false;
    }

    /**
     * @param string $expression
     * @return array
     */
    function queryArray($expression) {
        $array = array();
        foreach ($this->executeQuery($expression) as $r) {
            $key = array_shift($r);
            if (count($r) > 0) {
                $value = array_shift($r);
                $array[$key] = $value;
            } else {
                $array[] = $key;
            }
        }
        return $array;
    }

    /**
     * @param string $attribute
     * @return boolean
     */
    abstract function supports($attribute);
}