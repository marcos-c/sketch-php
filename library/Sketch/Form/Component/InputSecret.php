<?php
/**
 * This file is part of the Sketch library
 *
 * @author Marcos Cooper <marcos@releasepad.com>
 * @version 3.0
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

namespace Sketch\Form\Component;

/**
 * Secret form input component
 *
 * @package Sketch\Form\Component
 */
class InputSecret extends Component {
    function saveHTML() {
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $parameters = array_shift($arguments);
        $default = array_shift($arguments);
        $field_name = $this -> getForm() -> getFieldName($attribute);
        $field_value = $this -> getForm() -> getFieldValue($attribute);
        if ($field_value == null && $default != null) {
            $ignore_parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="input-secret-disabled"')));
            $parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="input-secret"')));
            $span_field_name = str_replace('[attributes]', '', $field_name);
            $ignore_field_name = str_replace('[attributes]', '[ignore]', $field_name);
            $span_ignore_field_name = str_replace('[attributes]', '', $ignore_field_name);
            return '<span id="'.$span_ignore_field_name.'"><input type="text" name="'.$ignore_field_name.'" value="'.$default.'" '.$ignore_parameters.' onfocus="document.getElementById(\''.$span_ignore_field_name.'\').style.display = \'none\'; document.getElementById(\''.$span_field_name.'\').style.display = \'inline\'; document.getElementById(\''.$field_name.'\').focus();"  /></span><span id="'.$span_field_name.'" style="display: none"><input type="password" id="'.$field_name.'" name="'.$field_name.'" value="" '.$parameters.' /></span>';
        } else {
            $parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="input-secret"')));
            return '<input type="password" name="'.$field_name.'" value="" '.$parameters.' />';
        }
    }
}