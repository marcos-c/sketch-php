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

class FormComponentSelectCheckbox extends FormComponent {
    function javascript() {
        $form_name = $this->getForm()->getFormName();
        ob_start(); ?>
        function <?=$form_name?>SelectCheckboxAll(header, field_name) {
            var form = document.forms['<?=$form_name?>'];
            for (i = 0; i < form.elements.length; i++) {
                if (form.elements[i].name != undefined) {
                    if (form.elements[i].name.substr(0, field_name.length) == field_name) {
                        form.elements[i].checked = (header.checked) ? true : false;
                    }
                }
            }
        }
        <?php return ob_get_clean();
    }

    function saveHTML() {
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $reference = array_shift($arguments);
        $parameters = array_shift($arguments);
        $field_name = $this->getForm()->getFieldName($attribute);
        $parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="select-checkbox"')));
        if ($reference == null) {
            $form_name = $this->getForm()->getFormName();
            $ignore_field_name = str_replace('[attributes]', '[ignore]', $field_name);
            return '<input type="checkbox" name="'.$ignore_field_name.'" onclick="'.$form_name.'SelectCheckboxAll(this, \''.$field_name.'\')" '.$parameters.' />';
        } else {
            $field_value = $this->getForm()->getFieldValue($attribute);
            $checked = (is_array($field_value) && in_array($reference, $field_value)) ? ' checked="checked"' : '';
            return '<input type="checkbox" name="'.$field_name.'['.$reference.']" value="'.$reference.'" '.$parameters.$checked.' />';
        }
    }
}