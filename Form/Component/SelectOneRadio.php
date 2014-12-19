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
 * SketchFormComponentSelectOneRadio
 *
 * @package Components
 */
class SketchFormComponentSelectOneRadio extends SketchFormComponent {
    function saveHTML() {
        $form = $this->getForm();
        $arguments = $this->getArguments();
        $options = array_shift($arguments);
        $attribute = array_shift($arguments);
        $parameters = $this->extend(array(
            'template' => null,
            'wrapper' => true,
            'columns' => 3,
            'input' => array('id' => null, 'class' => null, 'style' => null),
            'left-div' => array('id' => null, 'class' => null, 'style' => null),
            'div' => array('id' => null, 'class' => null, 'style' => null),
            'right-div' => array('id' => null, 'class' => null, 'style' => null),
            'end-div' => array('id' => null, 'class' => null, 'style' => null),
            'left-label' => array('id' => null, 'class' => null, 'style' => null),
            'label' => array('id' => null, 'class' => null, 'style' => null),
            'right-label' => array('id' => null, 'class' => null, 'style' => null),
            'end-label' => array('id' => null, 'class' => null, 'style' => null),
        ), array_shift($arguments));
        $field_name = $form->getFieldName($attribute);
        $field_value = $form->getFieldValue($attribute);
        ob_start(); ?>
        <? if (is_array($options)):
            $i = 1; ?>
            <? if ($parameters['template'] != null):
                foreach ($options as $key => $value):
                    $input = '<input type="radio" name="'.$field_name.'" value="'.$key.'" '.(($field_value == $key) ? 'checked="checked"' : '').$parameters['input'].' />';
                    if (is_array($value)):
                        list($p1, $p2, $p3, $p4, $p5) = $value; ?>
                        <?=sprintf($parameters['template'], $input, $p1, $p2, $p3, $p4, $p5)?>
                    <? else: ?>
                        <?=sprintf($parameters['template'], $input, $value)?>
                    <? endif; ?>
                <? endforeach; ?>
            <? elseif ($parameters['wrapper']): ?>
                <? foreach ($options as $key => $value): ?>
                    <? if ($i  == count($options) || !($i++ % $parameters['columns'])): ?>
                        <div<?=$parameters['right-div'] != null ? $parameters['right-div'] : $parameters['div']?>>
                            <label<?=$parameters['right-label'] != null ? $parameters['right-label'] : $parameters['label']?>><?=$value?></label>
                            <p><input type="radio" name="<?=$field_name?>" value="<?=$key?>" <?=(($field_value == $key) ? 'checked="checked"' : '')?><?=$parameters['input']?> /></p>
                        </div>
                    <? elseif (!($i % $parameters['columns'])): ?>
                        <div<?=$parameters['div']?>>
                            <label<?=$parameters['label']?>><?=$value?></label>
                            <p><input type="radio" name="<?=$field_name?>" value="<?=$key?>" <?=(($field_value == $key) ? 'checked="checked"' : '')?><?=$parameters['input']?> /></p>
                        </div>
                    <? else: ?>
                        <div<?=$parameters['left-div'] != null ? $parameters['left-div'] : $parameters['div']?>>
                            <label<?=$parameters['left-label'] != null ? $parameters['left-label'] : $parameters['div']?>><?=$value?></label>
                            <p><input type="radio" name="<?=$field_name?>" value="<?=$key?>" <?=(($field_value == $key) ? 'checked="checked"' : '')?><?=$parameters['input']?> /></p>
                        </div>
                    <? endif; ?>
                <? endforeach; ?>
            <? else: ?>
                <? foreach ($options as $key => $value): ?>
                    <span style="display:inline-block; margin-bottom: 4px;"><input type="radio" name="<?=$field_name?>" value="<?=$key?>" <?=(($field_value == $key) ? 'checked="checked"' : '')?><?=$parameters['input']?> />
                    <?=$value?></span><br />
                <? endforeach; ?>
            <? endif; ?>
        <? endif; ?>
        <?php return ob_get_clean();
    }
}