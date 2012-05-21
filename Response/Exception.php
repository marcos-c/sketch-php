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

/**
 * SketchResponseExtension
 */
class SketchResponseException extends Exception {
    /** @var array */
    private $debugInfo = array();

    /** @var array */
    private $stack = array();

    /**
     * Get debug info
     *
     * @return array
     */
    function getDebugInfo() {
        return $this->debugInfo;
    }

    /**
     * Add debug info
     *
     * @param $debug_info
     * @return void
     */
    function addDebugInfo($debug_info) {
        $this->debugInfo[] = $debug_info;
    }

    /**
     * Get stack
     *
     * @return array
     */
    function getStack() {
        return $this->stack;
    }

    /**
     * Add to stack
     *
     * @param Exception $exception
     * @return void
     */
    function addToStack(Exception $exception) {
        $this->stack[] = $exception;
    }
}