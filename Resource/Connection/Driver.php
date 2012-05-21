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

require_once 'Sketch/Resource/Connection.php';
require_once 'Sketch/Resource/Connection/ResultSet.php';
require_once 'Sketch/Resource/Connection/Exception.php';


/**
 * SketchResource
 */
abstract class SketchConnectionDriver extends SketchResource {
    /** @var string */
    private $tablePrefix;

    /**
     * Constructor
     *
     * @throws SketchResourceConnectionException
     * @param SketchResource $resource
     */
    final function __construct(SketchResource $resource) {
        try {
            $this->setTablePrefix($resource->queryCharacterData('//table-prefix'));
            $host = $resource->queryCharacterData('//host', 'localhost');
            $user = $resource->queryCharacterData('//user');
            $password = $resource->queryCharacterData('//password');
            $database = $resource->queryCharacterData('//database', $user);
            $encoding = $resource->queryCharacterData('//encoding', 'utf8');
            $this->connect($host, $database, $user, $password, $encoding);
        } catch (Exception $e) {
            throw new SketchResourceConnectionException($e->getMessage());
        }
    }

    /**
     * Destructor
     */
    final function __destruct() {
        $this->close();
    }

    /**
     * Connect
     *
     * @abstract
     * @param $host
     * @param $user
     * @param $password
     * @param $database
     * @param $encoding
     * @return void
     */
    abstract protected function connect($host, $user, $password, $database, $encoding);

    /**
     * Close
     *
     * @abstract
     * @return void
     */
    abstract protected function close();

    /**
     * Get table prefix
     *
     * @param $default
     * @return string
     */
    function getTablePrefix($default) {
        return $this->tablePrefix != null ? $this->tablePrefix : $default;
    }

    /**
     * Set table prefix
     *
     * @param $table_prefix
     * @return void
     */
    function setTablePrefix($table_prefix) {
        $this->tablePrefix = $table_prefix;
    }
    
    /**
     * Get tables
     *
     * @abstract
     * @param null $do_not_show
     * @return void
     */
    abstract function getTables($do_not_show = null);
    
    /**
     * Get table definition
     *
     * @abstract
     * @param $table_name
     * @return void
     */
    abstract function getTableDefinition($table_name);

    /**
     * Escape string
     *
     * @abstract
     * @param $string
     * @return void
     */
    abstract function escapeString($string);
    
    /**
     * Execute query
     *
     * @abstract
     * @param $expression
     * @return SketchResourceConnectionResultSet
     */
    abstract function executeQuery($expression);

    /**
     * Execute update
     *
     * @abstract
     * @param $expression
     * @return bool
     */
    abstract function executeUpdate($expression);

    /**
     * Begin transaction
     *
     * @abstract
     * @return bool
     */
    abstract function beginTransaction();

    /**
     * Commit transaction
     *
     * @abstract
     * @return bool
     */
    abstract function commitTransaction();

    /**
     * Rollback transaction
     *
     * @abstract
     * @return bool
     */
    abstract function rollbackTransaction();

    /**
     * Shorter alias for executeQuery
     *
     * @param $expression
     * @return void
     */
    function query($expression) {
        return $this->executeQuery($expression);
    }

    /**
     * Query row
     *
     * @param $expression
     * @return array
     */
    function queryRow($expression) {
        return $this->executeQuery($expression)->current();
    }

    /**
     * Query first
     *
     * @param $expression
     * @return bool|mixed
     */
    function queryFirst($expression) {
        $row = $this->queryRow($expression);
        return ($row) ? current($row) : false;
    }

    /**
     * Query array
     *
     * @param $expression
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
     * Supports
     *
     * @abstract
     * @param $attribute
     * @return bool
     */
    abstract function supports($attribute);
}