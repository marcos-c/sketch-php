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

require_once 'Sketch/Form/Component.php';

/**
 * SketchFormComponentInputNights
 */
class SketchFormComponentInputNights extends SketchFormComponent {
    /**
     * Save HTML
     *
     * @return string
     */
    function saveHTML() {
        $form = $this->getForm();
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $parameters = $this->extend(array(
            'disabled' => false,
            'from_attribute' => null,
            'to_attribute' => null,
            'span' => array('id' => null, 'class' => 'input-date', 'style' => null),
            'input-nights' => array('id' => null, 'class' => 'input-nights', 'style' => null)
        ), array_shift($arguments));
        $form_name = $form->getFormName();
        $from_field_name = $form->getFieldName($parameters['from_attribute']);
        $from_calendar_field_id = md5($form->getFieldName($parameters['from_attribute']));
        $to_field_name = $form->getFieldName($parameters['to_attribute']);
        $to_calendar_field_id = md5($form->getFieldName($parameters['to_attribute']));
        $nights_field_name = $form->getFieldName($attribute);
        $nights_field_value = $form->getFieldValue($attribute);
        return '<span'.$parameters['span'].'><input type="text" id="'.$nights_field_name.'" name="'.$nights_field_name.'" value="'.$nights_field_value.'" onchange="'.$form_name.'OnNightsChange(\''.$from_field_name.'\', \''.$to_field_name.'\', \''.$nights_field_name.'\', \''.$from_calendar_field_id.'\', \''.$to_calendar_field_id.'\')"'.$parameters['input-nights'].' /></span>';
    }
}
