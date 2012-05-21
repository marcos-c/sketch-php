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

require_once 'Sketch/Resource.php';

/**
 * SketchResourceConnection
 */
class SketchResourceConnection extends SketchResource {
    /** @var SketchConnectionDriver */
    private $driver;

    /**
     * Constructor
     *
     * @param SketchConnectionDriver $driver
     */
    function  __construct(SketchConnectionDriver $driver) {
        $this->setDriver($driver);
    }

    /**
     * Get driver
     *
     * @return SketchConnectionDriver
     */
    function getDriver() {
        return $this->driver;
    }

    /**
     * Set driver
     *
     * @param SketchConnectionDriver $driver
     * @return void
     */
    function setDriver(SketchConnectionDriver $driver) {
        $this->driver = $driver;
    }

    /**
     * Get table prefix
     *
     * @param null $default
     * @return string
     */
    function getTablePrefix($default = null) {
        return $this->driver->getTablePrefix($default);
    }

    /**
     * Get tables
     *
     * @param $do_not_show
     * @return void
     */
    function getTables($do_not_show) {
        return $this->driver->getTables($do_not_show);
    }

    /**
     * Get table definition
     *
     * @param $table_name
     * @return void
     */
    function getTableDefinition($table_name) {
        return $this->driver->getTableDefinition($table_name);
    }

    /**
     * Escape string
     *
     * @param $string
     * @return void
     */
    function escapeString($string) {
        return $this->driver->escapeString($string);
    }

    /**
     * To ASCII
     *
     * @param $string
     * @param $encoding
     * @return string
     */
    function toASCII($string, $encoding) {
        setlocale(LC_CTYPE, $encoding);
        return strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', strtolower($this->driver->escapeString($string))));
    }

    /**
     * Execute query
     *
     * @param $expression
     * @return SketchResourceConnectionResultSet
     */
    function executeQuery($expression) {
        return $this->driver->executeQuery($expression);
    }

    /**
     * Execute update
     *
     * @param $expression
     * @return bool
     */
    function executeUpdate($expression) {
        return $this->driver->executeUpdate($expression);
    }

    /**
     * Begin transaction
     *
     * @return bool
     */
    function beginTransaction() {
        return $this->driver->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * @return bool
     */
    function commitTransaction() {
        return $this->driver->commitTransaction();
    }

    /**
     * Rollback transaction
     *
     * @return bool
     */
    function rollbackTransaction() {
        return $this->driver->rollbackTransaction();
    }

    /**
     * Shorter alias for executeQuery
     *
     * @param $expression
     * @return void
     */
    function query($expression) {
        return $this->driver->query($expression);
    }

    /**
     * Query row
     *
     * @param $expression
     * @return array
     */
    function queryRow($expression) {
        return $this->driver->queryRow($expression);
    }

    /**
     * Query first
     *
     * @param $expression
     * @return bool|mixed
     */
    function queryFirst($expression) {
        return $this->driver->queryFirst($expression);
    }

    /**
     * Query array
     *
     * @param $expression
     * @return array
     */
    function queryArray($expression) {
        return $this->driver->queryArray($expression);
    }

    /**
     * Supports
     *
     * @param null $attribute
     * @return bool
     */
    function supports($attribute = null) {
        return $this->getDriver()->supports($attribute);
    }
}