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

class FormComponentSelectMultipleCheckbox extends FormComponent {
    /**
     * @return integer
     */
    function getVersion() {
        return 2;
    }

    function saveHTML() {
        $form = $this->getForm();
        $arguments = $this->getArguments();
        $options = array_shift($arguments);
        $attribute = array_shift($arguments);
        $field_name = $form->getFieldName($attribute);
        $field_value = $form->getFieldValue($attribute);
        $parameters = $this->extend(array(
            'container' => array('id' => $field_name, 'class' => 'select-multiple-checkbox-container', 'style' => null),
            'container-selected' => array('id' => $field_name, 'class' => 'select-multiple-checkbox-container-selected', 'style' => null),
            'optgroup' => array('id' => null, 'class' => 'select-multiple-checkbox-optgroup', 'style' => null),
            'optgroup-selected' => array('id' => null, 'class' => 'select-multiple-checkbox-optgroup selected', 'style' => null),
            'option' => array('id' => null, 'class' => 'select-multiple-checkbox-option', 'style' => null),
            'option-selected' => array('id' => null, 'class' => 'select-multiple-checkbox-option selected', 'style' => null)
        ), array_shift($arguments));
        $selected = array();
        $remaining = array();
        if ($options instanceof ObjectIterator) {
            foreach ($options as $object) {
                $key = $object->getId();
                $value = method_exists($object, '__toString') ? $object->__toString() : (method_exists($object, 'getDefaultDescription') ? $object->getDefaultDescription() : $object->getDescription());
                if (is_array($field_value) && in_array($key, $field_value)) {
                    $selected[] = '<label '.$parameters['options-selected'].'>'.$form->selectCheckbox($attribute, $key).' '.htmlspecialchars($value).'</label>';
                } else {
                    $remaining[] = '<label '.$parameters['option'].'>'.$form->selectCheckbox($attribute, $key).' '.htmlspecialchars($value).'</label>';
                }
            }
        } elseif (is_array($options)) {
            foreach ($options as $k1 => $v1) {
                if (is_array($v1)) {
                    $t1 = true; $t2 = true;
                    foreach ($v1 as $k2 => $v2) {
                        if (is_array($field_value) && in_array($k2, $field_value)) {
                            if ($t1) { $t1 = false; $selected[] = '<label '.$parameters['optgroup-selected'].'>'.htmlspecialchars($k1).'</label>'; }
                            $selected[] = '<label '.$parameters['option-selected'].'>'.$form->selectCheckbox($attribute, $k2).' '.htmlspecialchars($v2).'</label>';
                        } else {
                            if ($t2) { $t2 = false; $remaining[] = '<label '.$parameters['optgroup'].'>'.htmlspecialchars($k1).'</label>'; }
                            $remaining[] = '<label '.$parameters['option'].'>'.$form->selectCheckbox($attribute, $k2).' '.htmlspecialchars($v2).'</label>';
                        }
                    }
                } else {
                    if (is_array($field_value) && in_array($k1, $field_value)) {
                        $selected[] = '<label '.$parameters['option-selected'].'>'.$form->selectCheckbox($attribute, $k1).' '.htmlspecialchars($v1).'</label>';
                    } else {
                        $remaining[] = '<label '.$parameters['option'].'>'.$form->selectCheckbox($attribute, $k1).' '.htmlspecialchars($v1).'</label>';
                    }
                }
            }
        }
        ob_start(); ?>
        <div <?=$parameters['container']?>">
            <? if (count($selected) > 0): ?>
                <div <?=$parameters['container-selected']?>">
                    <?=implode('', $selected)?>
                </div>
            <? endif; ?>
            <?=implode('', $remaining)?>
        </div>
        <? $notice = $form->getFieldNotices($attribute); if ($notice): ?>
            <span class="help-block"><?=$notice->getMessage()?></span>
        <? endif; ?>
        <?php return ob_get_clean();
    }
}
