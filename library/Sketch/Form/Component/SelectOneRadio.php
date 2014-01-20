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
 * Select one option form component (radio)
 *
 * @package Sketch\Form\Component
 */
class SelectOneRadio extends Component {
    function saveHTML() {
        $form = $this->getForm();
        $arguments = $this->getArguments();
        $options = array_shift($arguments);
        $attribute = array_shift($arguments);
        $field_name = $form->getFieldName($attribute);
        $field_value = $form->getFieldValue($attribute);
        if (is_bool($field_value) || !$field_value) {
            $field_value = $field_value ? 't' : 'f';
        }
        $parameters = $this->extend(array(
            'ul' => array('id' => null, 'class' => 'select-one-radio', 'style' => null),
            'li' => array('id' => null, 'class' => 'select-one-radio-li', 'style' => null),
            'label' => array('id' => null, 'class' => 'select-one-radio-li', 'style' => null, 'for' => null),
            'input' => array('id' => null, 'class' => 'select-one-radio-input', 'style' => null)
        ), array_shift($arguments));
        ob_start(); ?>
        <? if (is_array($options)): ?>
            <ul <?=$parameters['ul']?>>
                <? foreach ($options as $key => $value):
                    if (is_array($value)) {
                        $add = $value[1];
                        $value = $value[0];
                    }
                    if ($arguments['label']['for'] != null && $arguments['input']['id'] != null): ?>
                        <li <?=$parameters['li']?>><label <?=$parameters['label']?>><input type="radio" name="<?=$field_name?>" value="<?=$key?>" <?=(($field_value == $key) ? 'checked="checked"' : '')?><?=$parameters['input']?> /> <?=$value?></label><?=$add?></li>
                    <? elseif ($arguments['label']['for'] != null):
                        $id = sprintf(' id="%s"', $arguments['input']['label']); ?>
                        <li <?=$parameters['li']?>><label <?=$parameters['label']?>><input<?=$id?> type="radio" name="<?=$field_name?>" value="<?=$key?>" <?=(($field_value == $key) ? 'checked="checked"' : '')?><?=$parameters['input']?> /> <?=$value?></label><?=$add?></li>
                    <? elseif ($arguments['label']['id'] != null):
                        $for = sprintf(' for="%s"', $arguments['input']['id']); ?>
                        <li <?=$parameters['li']?>><label<?=$for?> <?=$parameters['label']?>><input type="radio" name="<?=$field_name?>" value="<?=$key?>" <?=(($field_value == $key) ? 'checked="checked"' : '')?><?=$parameters['input']?> /> <?=$value?></label><?=$add?></li>
                    <? else:
                        $md5 = substr(md5($field_name.$key), 8);
                        $id = sprintf(' id="%s"', $md5);
                        $for = sprintf(' for="%s"', $md5); ?>
                        <li <?=$parameters['li']?>><label<?=$for?> <?=$parameters['label']?>><input<?=$id?> type="radio" name="<?=$field_name?>" value="<?=$key?>" <?=(($field_value == $key) ? 'checked="checked"' : '')?><?=$parameters['input']?> /> <?=$value?></label><?=$add?></li>
                    <? endif; ?>
                <? endforeach; ?>
            </ul>
        <? endif; ?>
        <?php return ob_get_clean();
    }
}