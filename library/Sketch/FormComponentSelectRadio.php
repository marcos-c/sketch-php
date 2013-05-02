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

class FormComponentSelectRadio extends FormComponent {
    function saveHTML() {
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $reference = array_shift($arguments);
        $parameters = array_shift($arguments);
        $field_name = $this->getForm()->getFieldName($attribute);
        $field_value = $this->getForm()->getFieldValue($attribute);
        $parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="select-radio"')));
        $checked = ($reference == $field_value) ? ' checked="checked"' : '';
        return '<input type="radio" name="'.$field_name.'" value="'.$reference.'" '.$parameters.$checked.' />';
    }
}