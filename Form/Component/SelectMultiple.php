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
 * SketchFormComponentSelectMultiple
 *
 * @package Components
 */
class SketchFormComponentSelectMultiple extends SketchFormComponent {
    function javascript() {
        $form_name = $this->getForm()->getFormName();
        ob_start(); ?>
        <?=$form_name?>PrepareSubmitStack[<?=$form_name?>PrepareSubmitStack.length] = '<?=$form_name?>SelectMultipleSubmit()';

        var <?=$form_name?>SelectMultipleArray = new Array();

        function <?=$form_name?>SelectMultipleSubmit() {
            for (var i = 0; i < <?=$form_name?>SelectMultipleArray.length; i++) {
                select = document.getElementById(<?=$form_name?>SelectMultipleArray[i]);
                for (var j = 0; j < select.options.length; j++) {
                    select.options[j].selected = true;
                }
            }
        }

        function <?=$form_name?>SelectMultipleMove(from, to, sort) {
            var from = document.getElementById(from);
            var to = document.getElementById(to);
            var o = new Array();
            for (var i = 0; i < to.options.length; i++) {
                o[o.length] = new Option(to.options[i].text, to.options[i].value);
            }
            var i = 0; do {
                if (from.options[i].selected) {
                    o[o.length] = new Option(from.options[i].text, from.options[i].value, from.options[i].defaultSelected, from.options[i].selected);
                    from.options[from.selectedIndex] = null;
                } else i++;
            } while (i < from.options.length);
            if (sort) o.sort(function (a,b) { if ((a.text + "") < (b.text + "")) return -1; if ((a.text + "") > (b.text + "")) return 1; return 0; });
            for (var i = 0; i < o.length; i++) to.options[i] = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
        }

        function <?=$form_name?>SelectMultipleSortUp(select) {
            var select = document.getElementById(select);
            var o = select.options;
            var s = -1;
            for (var i = 0; i < select.options.length; i++) {
                j = i - 1; if (o[i].selected) {
                    if (j > s) {
                        var t1 = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
                        var t2 = new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);
                        o[i] = t2; o[j] = t1;
                    } else s = i;
                }
            }
        }

        function <?=$form_name?>SelectMultipleSortDown(select) {
            var select = document.getElementById(select);
            var o = select.options;
            var s = o.length;
            for (var i = (o.length - 1); i > -1; i = i - 1) {
                j = i + 1; if (o[i].selected) {
                    if (j < s) {
                        var t1 = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
                        var t2 = new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);
                        o[i] = t2; o[j] = t1;
                    } else s = i;
                }
            }
        }
        <?php return ob_get_clean();
    }

    function saveHTML() {
        $arguments = $this->getArguments();
        $options = array_shift($arguments);
        $attribute = array_shift($arguments);
        $parameters = array_shift($arguments);
        $form_name = $this->getForm()->getFormName();
        $action = $this->getForm()->getAction();
        $field_name = $this->getForm()->getFieldName($attribute);
        $ignore_field_name = str_replace('[attributes]', '[ignore]', $field_name);
        $field_value = $this->getForm()->getFieldValue($attribute);
        $parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="select-multiple"')));
        $from = $to = array(); if (is_array($options)) foreach ($options as $key => $value) {
            if (is_array($field_value) && in_array($key, $field_value)) {
                $to[array_search($key, $field_value)] = '<option value="'.htmlspecialchars($key).'" selected="selected" class="select-option selected">'.htmlspecialchars($value).'</option>';
            } else {
                $from[] = '<option value="'.htmlspecialchars($key).'" class="select-option">'.htmlspecialchars($value).'</option>';
            }
        } ksort($to);
        ob_start(); ?>
        <script language="JavaScript">
            // <!CDATA[[
                <?=$form_name?>SelectMultipleArray[<?=$form_name?>SelectMultipleArray.length] = '<?=$field_name?>';
            // ]]>
        </script>
        <table border="0" cellspacing="0" cellpadding="0" <?=(($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="select-multiple"')))?>>
            <tr>
                <td class="select-multiple-td select">
                    <select id="<?=$ignore_field_name?>" name="<?=$ignore_field_name?>[]" multiple="multiple" size="5" class="select-multiple-select left"><?=implode($from)?></select>
                </td>
                <td class="select-multiple-td control">
                    <input type="button" value="" onclick="<?=$form_name?>SelectMultipleMove('<?=$ignore_field_name?>', '<?=$field_name?>', false)" class="select-multiple-button right" /><br />
                    <input type="button" value="" onclick="<?=$form_name?>SelectMultipleMove('<?=$field_name?>', '<?=$ignore_field_name?>', true)" class="select-multiple-button left" />
                </td>
                <td class="select-multiple-td select">
                    <select id="<?=$field_name?>" name="<?=$field_name?>[]" multiple="multiple" size="5" class="select-multiple-select right"><?=implode($to)?></select>
                </td>
                <td class="select-multiple-td control">
                    <input type="button" value="" onclick="<?=$form_name?>SelectMultipleSortUp('<?=$field_name?>')" class="select-multiple-button up" /><br />
                    <input type="button" value="" onclick="<?=$form_name?>SelectMultipleSortDown('<?=$field_name?>')" class="select-multiple-button down" />
                </td>
            </tr>
        </table>
        <?php return ob_get_clean();
    }
}