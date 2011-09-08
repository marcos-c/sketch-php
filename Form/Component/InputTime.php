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
 * SketchFormComponentInputTime
 *
 * @package Components
 */
class SketchFormComponentInputTime extends SketchFormComponent {
    function saveHTML() {
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $parameters = $this->extend(array(
            'disabled' => false,
            'null' => false,
            'null-text' => '...',
            'daylight-hours-only' => false,
            'minutes-step' => 1,
            'span' => array('id' => null, 'class' => 'input-time', 'style' => null),
            'input-time' => array('id' => null, 'class' => 'input-time', 'style' => null),
            'input-time-hour' => array('id' => null, 'class' => 'input-time-hour', 'style' => null),
            'input-time-minute' => array('id' => null, 'class' => 'input-time-minute', 'style' => null)
        ), array_shift($arguments));
        $field_name = $this->getForm()->getFieldName($attribute);
        $field_value = $this->getForm()->getFieldValue($attribute);
        if ($field_value instanceof SketchDateTime) list($year, $month, $day, $hour, $minute) = $field_value->toArray();
        $disabled = ($parameters['disabled'] !== false) ? ' disabled="disabled"' : '';
        if ($parameters['daylight-hours-only'] !== false) {
            $hours = array(5 => '5 am', 6 => '6 am', 7 => '7 am', 8 => '8 am', 9 => '9 am', 10 => '10 am', 11 => '11 am', 12 => '12 pm', 13 => '1 pm', 14 => '2 pm', 15 => '3 pm', 16 => '4 pm', 17 => '5 pm', 18 => '6 pm', 19 => '7 pm');
        } else {
            $hours = array(1 => '1 am', 2 => '2 am', 3 => '3 am', 4 => '4 am', 5 => '5 am', 6 => '6 am', 7 => '7 am', 8 => '8 am', 9 => '9 am', 10 => '10 am', 11 => '11 am', 12 => '12 pm', 13 => '1 pm', 14 => '2 pm', 15 => '3 pm', 16 => '4 pm', 17 => '5 pm', 18 => '6 pm', 19 => '7 pm', 20 => '8 pm', 21 => '9 pm', 22 => '10 pm', 23 => '11 pm', 0 => '12 pm');
        }
        $options = ($parameters['null']) ? array(null => $parameters['null-text']) : array();
        foreach ($hours as $key => $value) {
            list($key_hour, $period) = explode(' ', $value);
            foreach (range(0, 59, $parameters['minutes-step']) as $i) {
                $new_key = sprintf('%02s', $key).':'.sprintf('%02s', $i);
                $options[$new_key] = htmlspecialchars(sprintf('%s:%02s %s', $key_hour, $i, $period));
            }
        }
        ob_start(); ?>
        <span <?=$parameters['span']?>>
            <select name="<?=$field_name?>" <?=$parameters['input-time'].$disabled?>>
                <? foreach ($options as $key => $value): ?>
                    <option value="<?=htmlspecialchars($key)?>" <?=(($field_value instanceof SketchDateTime && !$field_value->isNull() && sprintf('%02s:%02s', $hour, $minute) == $key) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=$value?></option>
                <? endforeach; ?>
            </select>
        </span>
        <?php return ob_get_clean();
    }
}