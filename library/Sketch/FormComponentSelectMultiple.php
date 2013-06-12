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

class FormComponentSelectMultiple extends FormComponent {
    function javascript() {
        $form_name = $this->getForm()->getFormName();
        ob_start(); ?>
        <?=$form_name?>Stack("<?=$form_name?>SelectMultipleSubmit()");var <?=$form_name?>SelectMultipleArray=new Array();function <?=$form_name?>SelectMultipleSubmit(){for(var b=0;b<<?=$form_name?>SelectMultipleArray.length;b++){select=document.getElementById(<?=$form_name?>SelectMultipleArray[b]);for(var a=0;a<select.options.length;a++){select.options[a].selected=true}}}function <?=$form_name?>SelectMultipleMove(e,d,b){$(document.getElementById(e)).find('option:selected').appendTo(document.getElementById(d));}function <?=$form_name?>SelectMultipleSortUp(a){var a=document.getElementById(a);var f=a.options;var d=-1;for(var b=0;b<a.options.length;b++){j=b-1;if(f[b].selected){if(j>d){var e=new Option(f[b].text,f[b].value,f[b].defaultSelected,f[b].selected);var c=new Option(f[j].text,f[j].value,f[j].defaultSelected,f[j].selected);f[b]=c;f[j]=e}else{d=b}}}}function <?=$form_name?>SelectMultipleSortDown(a){var a=document.getElementById(a);var f=a.options;var d=f.length;for(var b=(f.length-1);b>-1;b=b-1){j=b+1;if(f[b].selected){if(j<d){var e=new Option(f[b].text,f[b].value,f[b].defaultSelected,f[b].selected);var c=new Option(f[j].text,f[j].value,f[j].defaultSelected,f[j].selected);f[b]=c;f[j]=e}else{d=b}}}};
        <?php return ob_get_clean();
    }

    function saveHTML() {
        $form = $this->getForm();
        $arguments = $this->getArguments();
        $options = array_shift($arguments);
        $attribute = array_shift($arguments);
        $parameters = $this->extend(array(
            'show-sort-options' => false,
            'size' => 5,
            'to' => array('id' => null, 'class' => 'select-multiple-to', 'style' => ''),
            'controls' => array('id' => null, 'class' => 'select-multiple-controls', 'style' => ''),
            'left' => array('id' => null, 'class' => 'select-multiple-left btn btn-mini', 'style' => null),
            'left-label' => '<i class="icon-double-angle-up"></i>',
            'up' => array('id' => null, 'class' => 'select-multiple-up btn btn-mini', 'style' => null),
            'up-label' => '<i class="icon-angle-up"></i>',
            'down' => array('id' => null, 'class' => 'select-multiple-down btn btn-mini', 'style' => null),
            'down-label' => '<i class="icon-double-down"></i>',
            'right' => array('id' => null, 'class' => 'select-multiple-right btn btn-mini', 'style' => null),
            'right-label' => '<i class="icon-double-angle-down"></i>',
            'from' => array('id' => null, 'class' => 'select-multiple-from', 'style' => ''),
        ), array_shift($arguments));
        $form_name = $form->getFormName();
        $field_name = $form->getFieldName($attribute);
        $field_value = $form->getFieldValue($attribute);
        $from = $to = array();
        if ($options instanceof ObjectIterator) {
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
            foreach ($options as $key => $object) {
                if (is_object($object)) {
                    $value = method_exists($object, '__toString') ? $object->__toString() : (method_exists($object, 'getDefaultDescription') ? $object->getDefaultDescription() : $object->getDescription());
                    $value_styles = method_exists($object, '__toStringStyles') ? $object->__toStringStyles() : '';
                } else {
                    $value = $object;
                }
                if (is_array($field_value) && in_array($key, $field_value)) {
                    $to[array_search($key, $field_value)] = '<option value="'.htmlspecialchars($key).'" selected="selected" class="'.$value_styles.' select-option">'.htmlspecialchars($value).'</option>';
                } else {
                    $from[] = '<option value="'.htmlspecialchars($key).'" class="'.$value_styles.' select-option">'.htmlspecialchars($value).'</option>';
                }
            }
        }
        ksort($to);
        $ignore_field_name = str_replace('[attributes]', '[ignore]', $field_name);
        ob_start(); ?>
        <script type="text/javascript">
            <?=$form_name?>SelectMultipleArray[<?=$form_name?>SelectMultipleArray.length] = '<?=$field_name?>';
        </script>
        <select id="<?=$field_name?>" name="<?=$field_name?>[]" multiple="multiple" size="<?=$parameters['size']?>"<?=$parameters['to']?>><?=implode($to)?></select>
        <div<?=$parameters['controls']?>>
            <a href="#" onclick="<?=$form_name?>SelectMultipleMove('<?=$ignore_field_name?>', '<?=$field_name?>', false); return false;"<?=$parameters['left']?>><?=$parameters['left-label']?></a>
            <? if ($parameters['show-sort-options']): ?>
                <a href="#" onclick="<?=$form_name?>SelectMultipleSortUp('<?=$field_name?>'); return false;"<?=$parameters['up']?>><?=$parameters['up-label']?></a>
                <a href="#" onclick="<?=$form_name?>SelectMultipleSortDown('<?=$field_name?>'); return false;"<?=$parameters['down']?>><?=$parameters['down-label']?></a>
            <? endif; ?>
            <a href="#" onclick="<?=$form_name?>SelectMultipleMove('<?=$field_name?>', '<?=$ignore_field_name?>', true); return false;"<?=$parameters['right']?>><?=$parameters['right-label']?></a>
        </div>
        <select id="<?=$ignore_field_name?>" name="<?=$ignore_field_name?>[]" multiple="multiple" size="<?=$parameters['size']?>"<?=$parameters['from']?>><?=implode($from)?></select>
        <?php return ob_get_clean();
    }
}