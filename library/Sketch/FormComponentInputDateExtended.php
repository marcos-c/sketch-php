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

class FormComponentInputDateExtended extends FormComponent {
    /**
     * @return string
     */
    function javascript() {
        $form_name = $this->getForm()->getFormName();        
        ob_start(); ?>
        function <?=$form_name?>UpdateDays(input) {
            var form = document.forms['<?=$form_name?>'];
            var day = form[input + '[day]'];
            var month = form[input + '[year_month]'].value.substr(4, 2) - 1;
            var year = form[input + '[year_month]'].value.substr(0, 4);
            if (year != 0 && month >= 0) {
                // 28 to 31 days
                var td = new Date();
                var from = 1;
                var value = 0;
                if (value == 0) {
                    var value = 31; do {
                        var date = new Date(year, month, value--);
                    } while (month < date.getMonth());
                }
                // Check if from stamp is greater than today + release
                if (month == td.getMonth() && year == td.getFullYear()) {
                    from = (from > (td.getDate())) ? from : td.getDate();
                }
                // Update the selector
                var selectedValue = day.value;
                while (day.options.length) day.options[0] = null;
                for (i = from; i < value + 2; i++) {
                    option = new Option(((i > 9) ? i : '0' + i), i, false, false);
                    day.options[j = day.length] = option;
                    if (i == selectedValue) day.selectedIndex = j;
                }
            } else {
                while (day.options.length) day.options[0] = null;
                day.options[0] = new Option('...', null, false, false);
            }
        }

        function <?=$form_name?>UpdateDate(input, date) {
            var form = document.forms['<?=$form_name?>'];
            var month = date.getMonth() > 8 ? String(date.getMonth() + 1) : '0' + String(date.getMonth() + 1);
            var selectedValue = String(date.getFullYear()) + month;
            var year_month = form[input + '[year_month]'];
            for (i = 0; i < year_month.length; i++) {
                if (year_month.options[i].value == selectedValue) {
                    year_month.options[i].selected = true;
                    break;
                }
            } <?=$form_name?>UpdateDays(input);
            var day = form[input + '[day]'];
            for (var i = 0; i < day.length; i++) {
                if (day.options[i].value == date.getDate()) {
                    day.options[i].selected = true;
                    break;
                }
            }
        }

        function <?=$form_name?>UpdateNights(from_input, to_input, nights_input) {
            var form = document.forms['<?=$form_name?>'];
            var from = new Date(form[from_input + '[year_month]'].value.substr(0, 4), form[from_input + '[year_month]'].value.substr(4, 2) - 1, form[from_input + '[day]'].value);
            var to = new Date(form[to_input + '[year_month]'].value.substr(0, 4), form[to_input + '[year_month]'].value.substr(4, 2) - 1, form[to_input + '[day]'].value);
            var nights = Math.round((to - from) / 86400000);
            if (nights < 1) nights = 1; if (nights > 31) nights = 31;
            form[nights_input].value = nights;
            <?=$form_name?>OnNightsChange(from_input, to_input, nights_input);
        }

        function <?=$form_name?>OnDayChange(input, from_input, to_input, nights_input) {
            var form = document.forms['<?=$form_name?>'];
            if (input == from_input) {
                <?=$form_name?>OnNightsChange(from_input, to_input, nights_input);
                // Check if from date is valid
                var day = Number(form[to_input + '[day]'].value) - Number(form[nights_input].value);
                var year_month = form[to_input + '[year_month]'];
                var month = year_month.value.substr(4, 2) - 1;
                var year = year_month.value.substr(0, 4);
                var from = new Date(year, month, day);
                <?=$form_name?>UpdateDate(from_input, from);
            } else if (nights_input != null) {
                <?=$form_name?>UpdateNights(from_input, to_input, nights_input);
            }
        }

        function <?=$form_name?>OnMonthChange(input, from_input, to_input, nights_input) {
            <?=$form_name?>UpdateDays(input);
            if (input == from_input) {
                <?=$form_name?>OnNightsChange(from_input, to_input, nights_input);
            } else if (nights_input != null) {
                <?=$form_name?>UpdateNights(from_input, to_input, nights_input);
            }
        }

        function <?=$form_name?>OnDateChange(input, from_input, to_input, nights_input, calendar_input) {
            var new_date = jQuery('#' + calendar_input).val().split('-');
            jQuery(':input[name=\'' + input + '[year_month]\']').val(new_date[0] + new_date[1]);
            <?=$form_name?>UpdateDays(input);
            jQuery(':input[name=\'' + input + '[day]\']').val(new_date[2]);
            if (input == from_input) {
                <?=$form_name?>OnNightsChange(from_input, to_input, nights_input);
            } else if (nights_input != null) {
                <?=$form_name?>UpdateNights(from_input, to_input, nights_input);
            }
        }

        function <?=$form_name?>OnNightsChange(from_input, to_input, nights_input) {
            var form = document.forms['<?=$form_name?>'];
            if (form[nights_input].value < 1) form[nights_input].value = 1;
            if (form[nights_input].value > 90) form[nights_input].value = 90;
            var day = Number(form[from_input + '[day]'].value) + Number(form[nights_input].value);
            var year_month = form[from_input + '[year_month]'];
            var month = year_month.value.substr(4, 2) - 1;
            var year = year_month.value.substr(0, 4);
            var to = new Date(year, month, day);
            <?=$form_name?>UpdateDate(to_input, to);
        }
        <?php return ob_get_clean();
    }

    /**
     * @return string
     */
    function saveHTML() {
        $form = $this->getForm();
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $parameters = $this->extend(array(
            'disabled' => false,
            'null' => false,
            'show_day_selector' => true,
            'from_current_date' => false,
            'from_attribute' => null,
            'to_attribute' => null,
            'nights_attribute' => null,
            'calendar' => false,
            'span' => array('id' => null, 'class' => 'input-date', 'style' => null),
            'input-date-day' => array('id' => null, 'class' => 'input-date-day', 'style' => null),
            'input-date-year-month-count' => 252,
            'input-date-year-month' => array('id' => null, 'class' => 'input-date-year-month', 'style' => null),
            'input-date-calendar' => array('id' => null, 'class' => 'input-date-calendar', 'style' => null),
            'onchange' => null
        ), array_shift($arguments));
        $form_name = $form->getFormName();
        $field_name = $form->getFieldName($attribute);
        $ignore_field_name = str_replace('[attributes]', '[ignore]', $field_name);
        $ignore_field_id = md5($ignore_field_name);
        if ($parameters['from_attribute'] != null) {
            $from_field_name = "'".$form->getFieldName($parameters['from_attribute'])."'";
            $to_field_name = "'".$field_name."'";
        } elseif ($parameters['to_attribute'] != null) {
            $from_field_name = "'".$field_name."'";
            $to_field_name = "'".$form->getFieldName($parameters['to_attribute'])."'";
        } else {
            $from_field_name = 'null';
            $to_field_name = 'null';
        }
        $nights_field_name = ($parameters['nights_attribute'] != null) ? "'".$form->getFieldName($parameters['nights_attribute'])."'" : 'null';
        $field_value = $form->getFieldValue($attribute);
        if ($field_value instanceof DateTime) list($year, $month, $day) = $field_value->toArray();
        elseif (!$parameters['null']) list($year, $month, $day) = DateTime::Today()->toArray();
        $year_month = sprintf('%04d%02d', $year, $month);
        $disabled = ($parameters['disabled'] !== false) ? ' disabled="disabled"' : '';
        if ($parameters['from_current_date']) {
            $from_year = intval(date('Y'));
            $from_month = intval(date('m', mktime(0, 0, 0, date('m'), date('d'), date('Y'))));
            $from_year_month = sprintf('%04d%02d', $from_year, $from_month);
            $from_day = ($from_year_month == $year_month) ? intval(date('d')) : 1;
            if ($from_day > date('t')) $from_day = 1;
            $count = 24;
        } else {
            $from_year = intval(date('Y')) - floor($parameters['input-date-year-month-count'] / 24);
            $from_month = $from_day = 1;
            $count = $parameters['input-date-year-month-count'];
        }
        $stamp = mktime(12, 0, 0, $count, 15, $from_year);
        $last_year = date('Y', $stamp);
        $last_month = date('m', $stamp);
        $last_day = date('t', $stamp);
        if ($parameters['null']) {
            $months = array('...'); $month_days = array('000000' => 0);
        } else {
            $months = array(); $month_days = array();
        }
        for ($i = $from_month; $i <= $count; $i++) {
            $stamp = mktime(12, 0, 0, $i, 15, $from_year);
            $syear = date('Y', $stamp);
            $month_days[$syear.date('m', $stamp)] = date('t', $stamp);
            $months[$syear.date('m', $stamp)] = date('m', $stamp).' - '.$syear;
        }
        if ($month_days[$year_month] > 0) {
            for ($i = $from_day; $i <= $month_days[$year_month]; $i++) $days[$i] = sprintf('%02d', $i);
        } else {
            $days[$i] = '...';
        }
        ob_start(); ?>
        <select id="<?=$field_name?>[day]" name="<?=$field_name?>[day]" onchange="<? if ($parameters['onchange'] != null): ?><?=$parameters['onchange']?><? else: ?><?=$form_name?>OnDayChange('<?=$field_name?>', <?=$from_field_name?>, <?=$to_field_name?>, <?=$nights_field_name?>);<? endif; ?>"<?=$parameters['input-date-day'].$disabled?>>
            <? foreach ($days as $key => $value): ?>
                <option value="<?=htmlspecialchars($key)?>" <?=(($day == $key) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=htmlspecialchars($value)?></option>
            <? endforeach; ?>
        </select>
        <?php $day_selector = ob_get_clean();
        ob_start(); ?>
        <input type="hidden" name="<?=$field_name?>[day]" value="1" />
        <?php $day_hidden = ob_get_clean();
        ob_start(); ?>
        <select name="<?=$field_name?>[year_month]" onchange="<? if ($parameters['onchange'] != null): ?><?=$parameters['onchange']?><? else: ?><?=$form_name?>OnMonthChange('<?=$field_name?>', <?=$from_field_name?>, <?=$to_field_name?>, <?=$nights_field_name?>);<? endif; ?>"<?=$parameters['input-date-year-month'].$disabled?>>
            <? foreach ($months as $key => $value): ?>
                <option value="<?=htmlspecialchars($key)?>" <?=(($year_month == $key) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=htmlspecialchars($value)?></option>
            <? endforeach; ?>
        </select>
        <?php $year_month_selector = ob_get_clean();
        if ($parameters['calendar']) {
            ob_start(); ?>
                    <? if ($parameters['input-date-calendar']): ?><span<?=$parameters['input-date-calendar']?>><? endif; ?>
                        <input type="hidden" value="<?=$year?>-<?=$month?>-<?=$day?>" onchange="<?=$form->getFormName()?>OnDateChange('<?=$field_name?>', <?=$from_field_name?>, <?=$to_field_name?>, <?=$nights_field_name?>, '<?=$ignore_field_id?>');" id="<?=$ignore_field_id?>" />
                        <script type="text/javascript">
                            jQuery(document).ready(function($) {
                                $(':input[name=\'<?=$field_name?>[day]\']').change(function () {
                                    var year_month = $(':input[name=\'<?=$field_name?>[year_month]\']').val();
                                    var date = year_month.substr(0, 4) + '-' + year_month.substr(4) + '-' + $(':input[name=\'<?=$field_name?>[day]\']').val();
                                    if (date != $('#<?=$ignore_field_id?>').val()) {
                                        $('#<?=$ignore_field_id?>').val(date);
                                        $(':input[name=\'<?=$field_name?>[day]\']').change();
                                    }
                                });
                                $(':input[name=\'<?=$field_name?>[year_month]\']').change(function () {
                                    var year_month = $(':input[name=\'<?=$field_name?>[year_month]\']').val();
                                    var date = year_month.substr(0, 4) + '-' + year_month.substr(4) + '-' + $(':input[name=\'<?=$field_name?>[day]\']').val();
                                    if (date != $('#<?=$ignore_field_id?>').val()) {
                                        $('#<?=$ignore_field_id?>').val(date);
                                        $(':input[name=\'<?=$field_name?>[day]\']').change();
                                    }
                                });
                                $('#<?=$ignore_field_id?>').datepicker({firstDay: 1, minDate: new Date(<?=$from_year?>, <?=$from_month - 1?>, <?=$from_day?>), maxDate: new Date(<?=$last_year?>, <?=$last_month - 1?>, <?=$last_day?>), dayNamesMin: ['<?=$this->getTranslator()->_s('Sun')?>', '<?=$this->getTranslator()->_s('Mon')?>', '<?=$this->getTranslator()->_s('Tue')?>', '<?=$this->getTranslator()->_s('Wed')?>', '<?=$this->getTranslator()->_s('Thu')?>', '<?=$this->getTranslator()->_s('Fri')?>', '<?=$this->getTranslator()->_s('Sat')?>'], monthNames: ['<?=$this->getTranslator()->_s('January')?>', '<?=$this->getTranslator()->_s('February')?>', '<?=$this->getTranslator()->_s('March')?>', '<?=$this->getTranslator()->_s('April')?>', '<?=$this->getTranslator()->_s('May')?>', '<?=$this->getTranslator()->_s('June')?>', '<?=$this->getTranslator()->_s('July')?>', '<?=$this->getTranslator()->_s('August')?>', '<?=$this->getTranslator()->_s('September')?>', '<?=$this->getTranslator()->_s('October')?>', '<?=$this->getTranslator()->_s('November')?>', '<?=$this->getTranslator()->_s('December')?>'], dateFormat: 'yy-mm-dd', showOn: 'button', buttonText: '<?=$this->getTranslator()->_s('Calendar')?>'});
                            });
                        </script>
                    <? if ($parameters['input-date-calendar']): ?></span><? endif; ?>
            <?php $calendar = ob_get_clean();
        } else {
            $calendar = '';
        }
        if ($parameters['span']) {
            return '<span'.$parameters['span'].'>'.($parameters['show_day_selector'] ? $day_selector : $day_hidden).$year_month_selector.'</span>'.$calendar;
        } else {
            return ($parameters['show_day_selector'] ? $day_selector : $day_hidden).$year_month_selector.$calendar;
        }
    }
}