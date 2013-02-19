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
            'field-id' => null
        ), array_shift($arguments));
        $field_id = ($parameters['field-id'] !== null) ? $parameters['field-id'] : $field_name;
        $selected = array();
        $remaining = array();
        if ($options instanceof ObjectIterator) {
            foreach ($options as $object) {
                $key = $object->getId();
                $value = method_exists($object, '__toString') ? $object->__toString() : (method_exists($object, 'getDefaultDescription') ? $object->getDefaultDescription() : $object->getDescription());
                if (is_array($field_value) && in_array($key, $field_value)) {
                    $selected[] = '<p style="margin: 0px; line-height: 18px; background-color: #FFFFCC;">'.$form->selectCheckbox($attribute, $key).' '.htmlspecialchars($value).'</p>';
                } else {
                    $remaining[] = '<p style="margin: 0px; line-height: 18px;">'.$form->selectCheckbox($attribute, $key).' '.htmlspecialchars($value).'</p>';
                }
            }
        } elseif (is_array($options)) {
            foreach ($options as $k1 => $v1) {
                if (is_array($v1)) {
                    $t1 = true; $t2 = true;
                    foreach ($v1 as $k2 => $v2) {
                        if (is_array($field_value) && in_array($k2, $field_value)) {
                            if ($t1) { $t1 = false; $selected[] = '<p style="font-weight: bold; margin: 0px; background-color: #FFFFCC;">'.htmlspecialchars($k1).'</p>'; }
                            $selected[] = '<p style="margin: 0px; line-height: 18px; background-color: #FFFFCC;">'.$form->selectCheckbox($attribute, $k2).' '.htmlspecialchars($v2).'</p>';
                        } else {
                            if ($t2) { $t2 = false; $remaining[] = '<p style="font-weight: bold; margin: 0px;">'.htmlspecialchars($k1).'</p>'; }
                            $remaining[] = '<p style="margin: 0px; line-height: 18px;">'.$form->selectCheckbox($attribute, $k2).' '.htmlspecialchars($v2).'</p>';
                        }
                    }
                } else {
                    if (is_array($field_value) && in_array($k1, $field_value)) {
                        $selected[] = '<p style="margin: 0px; line-height: 18px; background-color: #FFFFCC;">'.$form->selectCheckbox($attribute, $k1).' '.htmlspecialchars($v1).'</p>';
                    } else {
                        $remaining[] = '<p style="margin: 0px; line-height: 18px;">'.$form->selectCheckbox($attribute, $k1).' '.htmlspecialchars($v1).'</p>';
                    }
                }
            }
        }
        ob_start(); ?>
        <div style="margin-bottom: 10px;">
            <div id="<?=$field_id?>" data-field-name="<?=$field_name?>" style="border: 1px solid #D4D0C8; background-color: white; padding: 4px; width:390px; overflow: auto;">
                <? /* <?=$form->selectCheckbox($attribute)?> <?=$this->getTranslator()->_('Check, uncheck all')?> */ ?>
                <?=implode('', $selected)?>
                <?=implode('', $remaining)?>
            </div>
            <? $notice = $form->getFieldNotices($attribute); if ($notice): ?>
                <span class="error"><?=$notice->getMessage()?></span>
            <? endif; ?>
        </div>
        <?php return ob_get_clean();
    }
}
