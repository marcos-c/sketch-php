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

abstract class SketchObjectIterator extends SketchObject implements Iterator {
    protected $result = null;

    protected $size = null;

    private $key = 0;

    final function __construct($result = null, $size = null) {
        $this->result = $result;
        $this->size = ($size != null) ? intval($size) : null;
    }

    final function size() {
        return ($this->size != null) ? $this->size : $this->rows();
    }

    abstract function rows();

    abstract function fetch($key);

    /**
     *
     * @return mixed
     */
    final function current() {
        if ($this->key < $this->rows()) {
            return $this->fetch($this->key);
        } else return false;
    }

    /**
     *
     * @return scalar
     */
    final function key() {
        return $this->key;
    }

    final function next() {
        $this->key++;
    }

    final function rewind() {
        $this->key = 0;
    }

    /**
     *
     * @return boolean
     */
    final function valid() {
        return ($this->current() !== false);
    }

    /**
     *
     * @param bool $full
     * @return array
     */
    final function toArray($full = true) {
        $output = array();
        $copy_of_this = clone $this;
        $copy_of_this->rewind();
        foreach ($copy_of_this as $record) {
            if ($record instanceof SketchObjectView) {
                if ($full) {
                    $output[$record->getViewId()] = $record;
                } else {
                    $output[$record->getViewId()] = method_exists($record, '__toString') ? $record->__toString() : (method_exists($record, 'getDefaultDescription') ? $record->getDefaultDescription() : $record->getDescription());
                }
            } else {
                $output[] = $record;
            }
        }
        return $output;
    }
}