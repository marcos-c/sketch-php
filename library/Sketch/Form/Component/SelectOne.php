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

use Sketch\Core\ObjectIterator;

/**
 * Select one option form component (select)
 *
 * @package Sketch\Form\Component
 */
class SelectOne extends Component {
    function saveHTML() {
        $arguments = $this->getArguments();
        $options = array_shift($arguments);
        $attribute = array_shift($arguments);
        $parameters = array_shift($arguments);
        $field_name = $this->getForm()->getFieldName($attribute);
        $field_value = $this->getForm()->getFieldValue($attribute);
        $parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="select-one"')));
        ob_start(); ?>
        <select name="<?=$field_name?>" <?=$parameters?>>
            <? if ($options instanceof ObjectIterator): ?>
                <? foreach ($options as $object):
                    $key = $object->getId();
                    $value = method_exists($object, '__toString') ? $object->__toString() : (method_exists($object, 'getDefaultDescription') ? $object->getDefaultDescription() : $object->getDescription()); ?>
                    <option value="<?=htmlspecialchars($key)?>" <?=(($field_value == $key) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=htmlspecialchars($value)?></option>
                <? endforeach; ?>
            <? elseif (is_array($options)): ?>
                <? foreach ($options as $k1 => $v1): ?>
                    <? if (is_array($v1)): ?>
                        <optgroup label="<?=$k1?>">
                            <? foreach ($v1 as $k2 => $v2): ?>
                                <option value="<?=htmlspecialchars($k2)?>" <?=(($field_value == $k2) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=htmlspecialchars($v2)?></option>
                            <? endforeach; ?>
                        </optgroup>
                    <? else: ?>
                        <option value="<?=htmlspecialchars($k1)?>" <?=(($field_value == $k1) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=htmlspecialchars(is_object($v1) ? $v1->__toString() : $v1)?></option>
                    <? endif; ?>
                <? endforeach; ?>
            <? endif; ?>
        </select>
        <?php return ob_get_clean();
    }
}