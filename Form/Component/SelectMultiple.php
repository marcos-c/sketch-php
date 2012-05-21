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
 * SketchFormComponentSelectMultiple
 */
class SketchFormComponentSelectMultiple extends SketchFormComponent {
    /**
     * JavaScript
     *
     * @return string
     */
    function javascript() {
        $form_name = $this->getForm()->getFormName();
        ob_start(); ?>
        <?=$form_name?>Stack('<?=$form_name?>SelectMultipleSubmit()');
        var <?=$form_name?>SelectMultipleArray = new Array();
        function <?=$form_name?>SelectMultipleSubmit() {
            var i, j;
            for (i = 0; i < <?=$form_name?>SelectMultipleArray.length; i++) {
                select = document.getElementById(<?=$form_name?>SelectMultipleArray[i]);
                for (j = 0; j < select.options.length; j++) {
                    select.options[j].selected = true;
                }
            }
        }
        function <?=$form_name?>SelectMultipleMove(from, to, sort, callback) {
            var from_element = document.getElementById(from), to_element = document.getElementById(to), o = new Array(), i;
            if (from_element.options.length > 0) {
                for (i = 0; i < to_element.options.length; i++) {
                    o[o.length] = new Option(to_element.options[i].text, to_element.options[i].value);
                }
                i = 0; do {
                    if (from_element.options[i].selected) {
                        o[o.length] = new Option(from_element.options[i].text, from_element.options[i].value, from_element.options[i].defaultSelected, from_element.options[i].selected);
                        from_element.options[from_element.selectedIndex] = null;
                    } else i++;
                } while (i < from_element.options.length);
                if (sort) o.sort(function (a,b) { if ((a.text + "") < (b.text + "")) return -1; if ((a.text + "") > (b.text + "")) return 1; return 0; });
                for (i = 0; i < o.length; i++) to_element.options[i] = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
            }
            if (arguments.length == 4) {
                callback.call(from_element);
            }
        }
        function <?=$form_name?>SelectMultipleSortUp(select) {
            var select_element = document.getElementById(select), o = select_element.options, s = -1, i, j, t1, t2;
            for (i = 0; i < select_element.options.length; i++) {
                j = i - 1; if (o[i].selected) {
                    if (j > s) {
                        t1 = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
                        t2 = new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);
                        o[i] = t2; o[j] = t1;
                    } else s = i;
                }
            }
        }
        function <?=$form_name?>SelectMultipleSortDown(select) {
            var select_element = document.getElementById(select), o = select_element.options, s = o.length, i, t1, t2;
            for (i = (o.length - 1); i > -1; i = i - 1) {
                j = i + 1; if (o[i].selected) {
                    if (j < s) {
                        t1 = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
                        t2 = new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);
                        o[i] = t2; o[j] = t1;
                    } else s = i;
                }
            }
        }
        <?php return ob_get_clean();
    }

    /**
     * Save HTML
     *
     * @return string
     */
    function saveHTML() {
        $form = $this->getForm();
        $arguments = $this->getArguments();
        $options = array_shift($arguments);
        $attribute = array_shift($arguments);
        $parameters = $this->extend(array(
            'show-sort-options' => false,
            'size' => 5,
            'field-id' => null,
            'ignore-field-id' => null,
            'on-select-multiple-move' => null,
            'to' => array('id' => null, 'class' => 'select-multiple-to', 'style' => 'float: left;'),
            'controls' => array('id' => null, 'class' => 'select-multiple-controls', 'style' => 'float: left; margin-right: 4px;'),
            'left' => array('id' => null, 'class' => 'select-multiple-left', 'style' => null),
            'left-label' => $this->getTranslator()->_('Left'),
            'up' => array('id' => null, 'class' => 'select-multiple-up', 'style' => null),
            'up-label' => $this->getTranslator()->_('Up'),
            'down' => array('id' => null, 'class' => 'select-multiple-down', 'style' => null),
            'down-label' => $this->getTranslator()->_('Down'),
            'right' => array('id' => null, 'class' => 'select-multiple-right', 'style' => null),
            'right-label' => $this->getTranslator()->_('Right'),
            'from' => array('id' => null, 'class' => 'select-multiple-from', 'style' => 'float: left;'),
        ), array_shift($arguments));
        $form_name = $form->getFormName();
        $field_name = $form->getFieldName($attribute);
        $field_value = $form->getFieldValue($attribute);
        $from = $to = array();
        if ($options instanceof SketchObjectIterator) {
            foreach ($options as $object) {
                $key = $object->getId();
                $value = method_exists($object, '__toString') ? $object->__toString() : (method_exists($object, 'getDefaultDescription') ? $object->getDefaultDescription() : $object->getDescription());
                $value_styles = method_exists($object, '__toStringStyles') ? $object->__toStringStyles() : '';
                if (is_array($field_value) && in_array($key, $field_value)) {
                    $to[array_search($key, $field_value)] = '<option value="'.htmlspecialchars($key).'" selected="selected" class="'.$value_styles.' select-option selected">'.htmlspecialchars($value).'</option>';
                } else {
                    $from[] = '<option value="'.htmlspecialchars($key).'" class="'.$value_styles.' select-option">'.htmlspecialchars($value).'</option>';
                }
            }
        } elseif (is_array($options)) {
            foreach ($options as $key => $value) {
                if (is_array($field_value) && in_array($key, $field_value)) {
                    if ($value instanceof SketchObject) {
                        $value = method_exists($value, '__toString') ? $value->__toString() : (method_exists($value, 'getDefaultDescription') ? $value->getDefaultDescription() : $value->getDescription());
                        $to[array_search($key, $field_value)] = '<option value="'.htmlspecialchars($key).'" selected="selected" class="select-option selected">'.htmlspecialchars($value).'</option>';
                    } else {
                        $to[array_search($key, $field_value)] = '<option value="'.htmlspecialchars($key).'" selected="selected" class="select-option selected">'.htmlspecialchars($value).'</option>';
                    }
                } else {
                    if ($value instanceof SketchObject) {
                        $value = method_exists($value, '__toString') ? $value->__toString() : (method_exists($value, 'getDefaultDescription') ? $value->getDefaultDescription() : $value->getDescription());
                        $from[] = '<option value="'.htmlspecialchars($key).'" class="select-option">'.htmlspecialchars($value).'</option>';
                    } else {
                        $from[] = '<option value="'.htmlspecialchars($key).'" class="select-option">'.htmlspecialchars($value).'</option>';
                    }
                }
            }
        }
        ksort($to);
        $field_id = ($parameters['field-id']) ? $parameters['field-id'] : $field_name;
        $ignore_field_name = str_replace('[attributes]', '[ignore]', $field_name);
        $ignore_field_id = ($parameters['ignore-field-id'] != null) ? $parameters['ignore-field-id'] : $ignore_field_name;
        ob_start(); ?>
        <script type="text/javascript">
            <?=$form_name?>SelectMultipleArray[<?=$form_name?>SelectMultipleArray.length] = '<?=$field_id?>';
        </script>
        <select id="<?=$field_id?>" name="<?=$field_name?>[]" multiple="multiple" size="<?=$parameters['size']?>"<?=$parameters['to']?>><?=implode($to)?></select>
        <div<?=$parameters['controls']?>>
            <a href="#" onclick="<?=$form_name?>SelectMultipleMove('<?=$ignore_field_id?>', '<?=$field_id?>', false<?=$parameters['on-select-multiple-move'] != '' ? ', '.$parameters['on-select-multiple-move'] : ''?>); return false;"<?=$parameters['left']?>><?=$parameters['left-label']?></a>
            <? if ($parameters['show-sort-options']): ?>
                <a href="#" onclick="<?=$form_name?>SelectMultipleSortUp('<?=$field_id?>'); return false;"<?=$parameters['up']?>><?=$parameters['up-label']?></a>
                <a href="#" onclick="<?=$form_name?>SelectMultipleSortDown('<?=$field_id?>'); return false;"<?=$parameters['down']?>><?=$parameters['down-label']?></a>
            <? endif; ?>
            <a href="#" onclick="<?=$form_name?>SelectMultipleMove('<?=$field_id?>', '<?=$ignore_field_id?>', true<?=$parameters['on-select-multiple-move'] != '' ? ', '.$parameters['on-select-multiple-move'] : ''?>); return false;"<?=$parameters['right']?>><?=$parameters['right-label']?></a>
        </div>
        <select id="<?=$ignore_field_id?>" name="<?=$ignore_field_id?>[]" multiple="multiple" size="<?=$parameters['size']?>"<?=$parameters['from']?>><?=implode($from)?></select>
        <?php return ob_get_clean();
    }
}
