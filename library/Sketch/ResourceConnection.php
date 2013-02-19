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

class ResourceConnection extends Resource {
    /**
     * @var ConnectionDriver
     */
    private $driver;

    function  __construct(ConnectionDriver $driver) {
        $this->setDriver($driver);
    }

    /**
     * @return ConnectionDriver
     */
    function getDriver() {
        return $this->driver;
    }

    /**
     * @param ConnectionDriver $driver
     */
    function setDriver(ConnectionDriver $driver) {
        $this->driver = $driver;
    }

    /**
     * @param string $default
     * @return string
     */
    function getTablePrefix($default = null) {
        return $this->driver->getTablePrefix($default);
    }

    /**
     * @param mixed $do_not_show
     * @return array
     */
    function getTables($do_not_show) {
        return $this->driver->getTables($do_not_show);
    }

    /**
     * @param string $table_name
     * @return array
     */
    function getTableDefinition($table_name) {
        return $this->driver->getTableDefinition($table_name);
    }

    /**
     * @param string $string
     * @return string
     */
    function escapeString($string) {
        return $this->driver->escapeString($string);
    }

    /**
     * @param string $string
     * @param $encoding
     * @return string
     */
    function toASCII($string, $encoding) {
        setlocale(LC_CTYPE, $encoding);
        return strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', strtolower($this->driver->escapeString($string))));
    }

    /**
     * @param string $expression
     * @return ObjectIterator
     */
    function executeQuery($expression) {
        return $this->driver->executeQuery($expression);
    }

    /**
     * @param string $expression
     * @return boolean
     */
    function executeUpdate($expression) {
        return $this->driver->executeUpdate($expression);
    }

    /**
     * @return boolean
     */
    function beginTransaction() {
        return $this->driver->beginTransaction();
    }

    /**
     * @return boolean
     */
    function commitTransaction() {
        return $this->driver->commitTransaction();
    }

    /**
     * @return boolean
     */
    function rollbackTransaction() {
        return $this->driver->rollbackTransaction();
    }

    /**
     * @param string $expression
     * @return ObjectIterator
     */
    function query($expression) {
        return $this->driver->query($expression);
    }

    /**
     * @param string $expression
     * @return array
     */
    function queryRow($expression) {
        return $this->driver->queryRow($expression);
    }

    /**
     * @param string $expression
     * @return string
     */
    function queryFirst($expression) {
        return $this->driver->queryFirst($expression);
    }

    /**
     * @param string $expression
     * @return array
     */
    function queryArray($expression) {
        return $this->driver->queryArray($expression);
    }

    /**
     * @param string $attribute
     * @return boolean
     */
    function supports($attribute = null) {
        return $this->getDriver()->supports($attribute);
    }
}