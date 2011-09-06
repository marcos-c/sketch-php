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

require_once 'Sketch/Application/Notice.php';

/**
 * SketchFormNotice
 *
 * @package Sketch
 */
class SketchFormNotice extends SketchApplicationNotice {
    /**
     *
     * @var string
     */
    private $fieldName = null;

    /**
     *
     * @param string $field_name
     * @param string $message
     */
    function __construct($field_name, $message) {
        $this->setFieldName($field_name);
        $this->setMessage($message);
        $this->setNoticeType(A_NOTICE);
    }

    /**
     *
     * @return string
     */
    function getFieldName() {
        return $this->fieldName;
    }

    /**
     *
     * @param string $field_name
     */
    function setFieldName($field_name) {
        $this->fieldName = $field_name;
    }
}