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
        <?=$form_name?>Stack("<?=$form_name?>SelectMultipleSubmit()");var <?=$form_name?>SelectMultipleArray=new Array();function <?=$form_name?>SelectMultipleSubmit(){var b,a;for(b=0;b<<?=$form_name?>SelectMultipleArray.length;b++){select=document.getElementById(<?=$form_name?>SelectMultipleArray[b]);for(a=0;a<select.options.length;a++){select.options[a].selected=true}}}function <?=$form_name?>SelectMultipleMove(h,g,c,f){var a=document.getElementById(h),e=document.getElementById(g),d=new Array(),b;if(a.options.length>0){for(b=0;b<e.options.length;b++){d[d.length]=new Option(e.options[b].text,e.options[b].value)}b=0;do{if(a.options[b].selected){d[d.length]=new Option(a.options[b].text,a.options[b].value,a.options[b].defaultSelected,a.options[b].selected);a.options[a.selectedIndex]=null}else{b++}}while(b<a.options.length);if(c){d.sort(function(k,i){if((k.text+"")<(i.text+"")){return -1}if((k.text+"")>(i.text+"")){return 1}return 0})}for(b=0;b<d.length;b++){e.options[b]=new Option(d[b].text,d[b].value,d[b].defaultSelected,d[b].selected)}}if(arguments.length==4){f.call(a)}}function <?=$form_name?>SelectMultipleSortUp(b){var a=document.getElementById(b),h=a.options,f=-1,d,c,g,e;for(d=0;d<a.options.length;d++){c=d-1;if(h[d].selected){if(c>f){g=new Option(h[d].text,h[d].value,h[d].defaultSelected,h[d].selected);e=new Option(h[c].text,h[c].value,h[c].defaultSelected,h[c].selected);h[d]=e;h[c]=g}else{f=d}}}}function <?=$form_name?>SelectMultipleSortDown(b){var a=document.getElementById(b),g=a.options,e=g.length,c,f,d;for(c=(g.length-1);c>-1;c=c-1){j=c+1;if(g[c].selected){if(j<e){f=new Option(g[c].text,g[c].value,g[c].defaultSelected,g[c].selected);d=new Option(g[j].text,g[j].value,g[j].defaultSelected,g[j].selected);g[c]=d;g[j]=f}else{e=c}}}};
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
                if (is_array($field_value) && in_array($key, $field_value)) {
                    $to[array_search($key, $field_value)] = '<option value="'.htmlspecialchars($key).'" selected="selected" class="select-option selected">'.htmlspecialchars($value).'</option>';
                } else {
                    $from[] = '<option value="'.htmlspecialchars($key).'" class="select-option">'.htmlspecialchars($value).'</option>';
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
