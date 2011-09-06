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

require_once 'Sketch/Resource.php';

/**
 * SketchResourceConnection
 *
 * @package Sketch
 */
class SketchResourceConnection extends SketchResource {
    /**
     *
     * @var SketchConnectionDriver
     */
    private $driver;

    function  __construct(SketchConnectionDriver $driver) {
        $this->setDriver($driver);
    }

    /**
     *
     * @return SketchConnectionDriver
     */
    function getDriver() {
        return $this->driver;
    }

    /**
     *
     * @param SketchConnectionDriver $driver
     */
    function setDriver(SketchConnectionDriver $driver) {
        $this->driver = $driver;
    }

    /**
     *
     * @param string $default
     * @return string
     */
    function getTablePrefix($default = null) {
        return $this->driver->getTablePrefix($default);
    }
    
    /**
     *
     * @param mixed $do_not_show
     * @return array
     */
    function getTables($do_not_show) {
        return $this->driver->getTables($do_not_show);
    }

    /**
     *
     * @param string $table
     * @return array
     */
    function getTableDefinition($table) {
        return $this->driver->getTableDefinition($table);
    }

    /**
     *
     * @param string $string
     * @return string
     */
    function escapeString($string) {
        return $this->driver->escapeString($string);
    }

    /**
     *
     * @param string $expression
     */
    function executeQuery($expression) {
        return $this->driver->executeQuery($expression);
    }

    /**
     *
     * @param string $expression
     * @return boolean
     */
    function executeUpdate($expression) {
        return $this->driver->executeUpdate($expression);
    }

    /**
     *
     * @param string $expression
     * @return SketchObjectIterator
     */
    function query($expression) {
        return $this->driver->query($expression);
    }

    /**
     *
     * @param string $expression
     * @return array
     */
    function queryRow($expression) {
        return $this->driver->queryRow($expression);
    }

    /**
     *
     * @param string $expression
     * @return string
     */
    function queryFirst($expression) {
        return $this->driver->queryFirst($expression);
    }

    /**
     *
     * @param string $expression
     * @return array
     */
    function queryArray($expression) {
        return $this->driver->queryArray($expression);
    }
}