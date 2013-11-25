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

/**
 * SketchResponseExtension
 *
 * @package Sketch
 */
class SketchResponseException extends Exception {
    /**
     *
     * @var array
     */
    private $debugInfo = array();

    /**
     *
     * @var array
     */
    private $stack = array();

    /**
     *
     * @return array
     */
    function getDebugInfo() {
        return $this->debugInfo;
    }

    /**
     *
     * @param mixed $debug_info
     */
    function addDebugInfo($debug_info) {
        $this->debugInfo[] = $debug_info;
    }

    /**
     *
     * @return array
     */
    function getStack() {
        return $this->stack;
    }

    /**
     *
     * @param Exception $exception 
     */
    function addToStack(Exception $exception) {
        $this->stack[] = $exception;
    }
}