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

require_once 'Sketch/Object.php';
require_once 'Sketch/Object/EmptyIterator.php';
require_once 'Sketch/Object/ArrayIterator.php';

/**
 * SketchObjectIterator
 */
abstract class SketchObjectIterator extends SketchObject implements Iterator {
    /** @var null|resource */
    protected $result = null;

    /** @var int|null */
    protected $size = null;

    /** @var int */
    private $key = 0;

    /**
     * Constructor
     *
     * @param null $result
     * @param null $size
     */
    final function __construct($result = null, $size = null) {
        $this->result = $result;
        $this->size = ($size != null) ? intval($size) : null;
    }

    /**
     * Size
     *
     * @return int|null|void
     */
    final function size() {
        return ($this->size != null) ? $this->size : $this->rows();
    }

    /**
     * Rows
     *
     * @abstract
     * @return void
     */
    abstract function rows();

    /**
     * Fetch
     *
     * @abstract
     * @param $key
     * @return void
     */
    abstract function fetch($key);

    /**
     * Current
     *
     * @return mixed
     */
    final function current() {
        if ($this->key < $this->rows()) {
            return $this->fetch($this->key);
        } else return false;
    }

    /**
     * Key
     *
     * @return int
     */
    final function key() {
        return $this->key;
    }

    /**
     * Next
     *
     * @return void
     */
    final function next() {
        $this->key++;
    }

    /**
     * Rewind
     *
     * @return void
     */
    final function rewind() {
        $this->key = 0;
    }

    /**
     * Valid
     *
     * @return bool
     */
    final function valid() {
        return ($this->current() !== false);
    }

    /**
     * To array
     *
     * @return array
     */
    final function toArray() {
        $output = array();
        $copy_of_this = clone $this;
        $copy_of_this->rewind();
        foreach ($copy_of_this as $record) {
            if ($record instanceof SketchObjectView) {
                $output[$record->getViewId()] = $record;
            } else {
                $output[] = $record;
            }
        }
        return $output;
    }
}