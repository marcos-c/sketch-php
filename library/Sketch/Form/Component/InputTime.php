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

use Sketch\Core\DateTime;

/**
 * Time form input component
 *
 * @package Sketch\Form\Component
 */
class InputTime extends Component {
    function saveHTML() {
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $parameters = $this->extend(array(
            'disabled' => false,
            'null' => false,
            'null-text' => '...',
            'daylight-hours-only' => false,
            'working-hours-only' => false,
            'from-hour' => 0,
            'till-hour' => 23,
            'minutes-step' => 1,
            '24-hours' => false,
            'span' => array('id' => null, 'class' => 'input-time', 'style' => null),
            'input-time' => array('id' => null, 'class' => 'input-time', 'style' => null),
            'input-time-hour' => array('id' => null, 'class' => 'input-time-hour', 'style' => null),
            'input-time-minute' => array('id' => null, 'class' => 'input-time-minute', 'style' => null)
        ), array_shift($arguments));
        $field_name = $this->getForm()->getFieldName($attribute);
        $field_value = $this->getForm()->getFieldValue($attribute);
        if ($field_value instanceof DateTime) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            list($year, $month, $day, $hour, $minute) = $field_value->toArray();
        }
        $disabled = ($parameters['disabled'] !== false) ? ' disabled="disabled"' : '';
        if ($parameters['daylight-hours-only'] !== false) {
            $parameters['from-hour'] = 5;
            $parameters['till-hour'] = 19;
        } else if ($parameters['working-hours-only'] !== false) {
            $parameters['from-hour'] = 9;
            $parameters['till-hour'] = 21;
        }
        if ($parameters['24-hours']) {
            $hours = array();
            for ($i = $parameters['from-hour']; $i <= $parameters['till-hour']; $i++) {
                $hours[$i] = $i;
            }
            $options = ($parameters['null']) ? array(null => $parameters['null-text']) : array();
            foreach ($hours as $key => $value) {
                foreach (range(0, 59, $parameters['minutes-step']) as $i) {
                    $new_key = sprintf('%02d:%02d', $key, $i);
                    $options[$new_key] = htmlspecialchars($new_key);
                }
            }
        } else {
            $hours = array();
            for ($i = $parameters['from-hour']; $i <= $parameters['till-hour']; $i++) {
                if ($i == 0) {
                    $hours[$i] = "12 am";
                } else {
                    $hours[$i] = ($i >= 12) ? (($i > 12) ? $i - 12 : $i).' pm' : "$i am";
                }
            }
            $options = ($parameters['null']) ? array(null => $parameters['null-text']) : array();
            foreach ($hours as $key => $value) {
                list($key_hour, $period) = explode(' ', $value);
                foreach (range(0, 59, $parameters['minutes-step']) as $i) {
                    $new_key = sprintf('%02d:%02d', $key, $i);
                    $options[$new_key] = sprintf('%d:%02d %s', $key_hour, $i, $period);
                }
            }
        }
        ob_start(); ?>
        <span <?=$parameters['span']?>>
            <select name="<?=$field_name?>" <?=$parameters['input-time'].$disabled?>>
                <? foreach ($options as $key => $value): ?>
                    <option value="<?=htmlspecialchars($key)?>" <?=(($field_value instanceof DateTime && sprintf('%02s:%02s', $hour, $minute) == $key) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=$value?></option>
                <? endforeach; ?>
            </select>
        </span>
        <?php return ob_get_clean();
    }
}