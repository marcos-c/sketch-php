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

require_once 'Sketch/Form/Component.php';

/**
 * SketchFormComponentInputHidden
 *
 * @package Components
 */
class SketchFormComponentInputHidden extends SketchFormComponent {
    function saveHTML() {
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $parameters = array_shift($arguments);
        $parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="input-hidden"')));
        $field_name = $this->getForm()->getFieldName($attribute);
        $field_value = $this->getForm()->getFieldValue($attribute);
        if ($field_value instanceof SketchDateTime) {
            $field_value = $field_value->toString('Y-m-d');
        }
        return '<input type="hidden" name="'.$field_name.'" value="'.$field_value.'" '.$parameters.' />';
    }
}