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

require_once 'Sketch/Resource/Connection.php';
require_once 'Sketch/Resource/Connection/Exception.php';

/**
 * SketchResource
 *
 * @package Sketch
 */
abstract class SketchConnectionDriver extends SketchResource {
    /**
     *
     * @var string
     */
    private $tablePrefix;

    /**
     *
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

    final function __destruct() {
        $this->close();
    }

    /**
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param stirng $encoding
     */
    abstract protected function connect($host, $user, $password, $database, $encoding);

    abstract protected function close();

    /**
     *
     * @return string
     */
    function getTablePrefix($default) {
        return $this->tablePrefix != null ? $this->tablePrefix : $default;
    }

    /**
     *
     * @param string $table_prefix
     */
    function setTablePrefix($table_prefix) {
        $this->tablePrefix = $table_prefix;
    }
    
    /**
     *
     * @param mixed $do_not_show
     * @return array
     */
    abstract function getTables($do_not_show = null);
    
    /**
     *
     * @param string $table
     * @return array
     */
    abstract function getTableDefinition($table);

    /**
     *
     * @param string $string
     * @return string
     */
    abstract function escapeString($string);
    
    /**
     *
     * @param string $expression
     * @return SketchObjectIterator
     */
    abstract function executeQuery($expression);

    /**
     *
     * @param string $expression
     * @return boolean
     */
    abstract function executeUpdate($expression);

    /**
     *
     * @param string $expression
     * @return SketchObjectIterator
     */
    function query($expression) {
        return $this->executeQuery($expression);
    }

    /**
     *
     * @param string $expression
     * @return array
     */
    function queryRow($expression) {
        return $this->executeQuery($expression)->current();
    }

    /**
     *
     * @param string $expression
     * @return mixed
     */
    function queryFirst($expression) {
        $row = $this->queryRow($expression);
        return ($row) ? current($row) : false;
    }

    /**
     *
     * @param string $expression
     * @return array
     */
    function queryArray($expression) {
        $array = array(); foreach ($this->executeQuery($expression) as $r) {
            $key = array_shift($r); $value = array_shift($r);
            if ($value != null) $array[$key] = $value; else $array[] = $key;
        } return $array;
    }
}