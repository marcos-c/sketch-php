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

class FormComponentInputDateSimple extends FormComponent {
    /**
     * @return string
     */
    function javascript() {
        $form_name = $this->getForm()->getFormName();
        ob_start(); ?>
        function <?=$form_name?>UpdateDate(input, date, calendar_input) {
            var form = document.forms.<?=$form_name?>;
            var month = date.getMonth() > 8 ? String(date.getMonth() + 1) : '0' + String(date.getMonth() + 1);
            var selectedValue = String(date.getFullYear()) + month;
            var year_month = form[input + '[year_month]'];
            year_month.value = selectedValue;
            var monthNames = ['<?=$this->getTranslator()->_s('Jan')?>', '<?=$this->getTranslator()->_s('Feb')?>', '<?=$this->getTranslator()->_s('Mar')?>', '<?=$this->getTranslator()->_s('Apr')?>', '<?=$this->getTranslator()->_s('May')?>', '<?=$this->getTranslator()->_s('Jun')?>', '<?=$this->getTranslator()->_s('Jul')?>', '<?=$this->getTranslator()->_s('Aug')?>', '<?=$this->getTranslator()->_s('Sep')?>', '<?=$this->getTranslator()->_s('Oct')?>', '<?=$this->getTranslator()->_s('Nov')?>', '<?=$this->getTranslator()->_s('Dec')?>'];
            $('#_' + calendar_input).html(monthNames[date.getMonth()] + ' ' + String(date.getFullYear()));
            var day = form[input + '[day]'];
            day.value = date.getDate();
        }

        function <?=$form_name?>UpdateNights(from_input, to_input, nights_input, from_calendar_input, to_calendar_input) {
            var form = document.forms.<?=$form_name?>;
            var from = new Date(form[from_input + '[year_month]'].value.substr(0, 4), form[from_input + '[year_month]'].value.substr(4, 2) - 1, form[from_input + '[day]'].value);
            var to = new Date(form[to_input + '[year_month]'].value.substr(0, 4), form[to_input + '[year_month]'].value.substr(4, 2) - 1, form[to_input + '[day]'].value);
            var nights = Math.round((to - from) / 86400000);
            form[nights_input].value = nights;
            <?=$form_name?>OnNightsChange(from_input, to_input, nights_input, from_calendar_input, to_calendar_input);
        }

        function <?=$form_name?>OnDayChange(increment, input, from_input, to_input, nights_input, calendar_input, from_calendar_input, to_calendar_input) {
            var form = document.forms.<?=$form_name?>;
            var day = form[input + '[day]'];
            if (isNaN(parseInt(day.value))) {
            } else {
                var year_month = form[input + '[year_month]'];
                var month = year_month.value.substr(4, 2) - 1;
                var year = year_month.value.substr(0, 4);
                if (increment != 0) {
                    var date = new Date(year, month, parseInt(day.value) + increment);
                    day.value = date.getDate();
                    month = date.getMonth() > 8 ? String(date.getMonth() + 1) : '0' + String(date.getMonth() + 1);
                    year_month.value = String(date.getFullYear()) + month;
                }
                if (input == from_input) {
                    <?=$form_name?>OnNightsChange(from_input, to_input, nights_input, from_calendar_input, to_calendar_input);
                } else if (nights_input != null) {
                    <?=$form_name?>UpdateNights(from_input, to_input, nights_input, from_calendar_input, to_calendar_input);
                } else {
                    var date = new Date(year, month, Number());
                    $('#' + calendar_input).val(date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate());
                }
            }
        }

        function <?=$form_name?>OnNightsChange(from_input, to_input, nights_input, from_calendar_input, to_calendar_input) {
            var form = document.forms.<?=$form_name?>;
            if (form[nights_input].value < 1) form[nights_input].value = 1;
            if (form[nights_input].value > 90) form[nights_input].value = 90;
            var from_day = Number(form[from_input + '[day]'].value);
            var to_day = from_day + Number(form[nights_input].value);
            var year_month = form[from_input + '[year_month]'];
            var from_date = new Date(year_month.value.substr(0, 4), year_month.value.substr(4, 2) - 1, from_day);
            var to_date = new Date(year_month.value.substr(0, 4), year_month.value.substr(4, 2) - 1, to_day);
            <?=$form_name?>UpdateDate(from_input, from_date, from_calendar_input);
            <?=$form_name?>UpdateDate(to_input, to_date, to_calendar_input);
            $('#' + from_calendar_input).val(from_date.getFullYear() + '-' + (from_date.getMonth() + 1) + '-' + from_date.getDate());
            $('#' + to_calendar_input).val(to_date.getFullYear() + '-' + (to_date.getMonth() + 1) + '-' + to_date.getDate());
        }

        function <?=$form_name?>OnCalendarChange(input, from_input, to_input, nights_input, calendar_input, from_calendar_input, to_calendar_input) {
            var new_date = jQuery('#' + calendar_input).val().split('-');
            jQuery(':input[name=\'' + input + '[year_month]\']').val(new_date[0] + new_date[1]);
            jQuery(':input[name=\'' + input + '[day]\']').val(new_date[2]);
            <?=$form_name?>OnDayChange(0, input, from_input, to_input, nights_input, calendar_input, from_calendar_input, to_calendar_input);
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
            'from_current_date' => false,
            'from_attribute' => null,
            'to_attribute' => null,
            'nights_attribute' => null,
            'calendar' => false,
            'span' => array('id' => null, 'class' => 'input-date', 'style' => null),
            'input-date-day' => array('id' => null, 'class' => 'input-date-day', 'style' => null),
            'input-date-year-month-count' => 252,
            'input-date-year-month' => array('id' => null, 'class' => 'input-date-year-month', 'style' => null),
            'input-date-calendar' => array('id' => null, 'class' => 'input-date-calendar', 'style' => null)
        ), array_shift($arguments));
        $form_name = $form->getFormName();
        $field_name = $form->getFieldName($attribute);
        $calendar_field_id = md5($field_name);
        if ($parameters['from_attribute'] != null) {
            $from_field_name = "'".$form->getFieldName($parameters['from_attribute'])."'";
            $from_calendar_field_id = "'".md5($form->getFieldName($parameters['from_attribute']))."'";
            $to_field_name = "'".$field_name."'";
            $to_calendar_field_id = "'".md5($field_name)."'";
        } elseif ($parameters['to_attribute'] != null) {
            $from_field_name = "'".$field_name."'";
            $from_calendar_field_id = "'".md5($field_name)."'";
            $to_field_name = "'".$form->getFieldName($parameters['to_attribute'])."'";
            $to_calendar_field_id = "'".md5($form->getFieldName($parameters['to_attribute']))."'";
        } else {
            $from_field_name = 'null';
            $from_calendar_field_id = 'null';
            $to_field_name = 'null';
            $to_calendar_field_id = 'null';
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
        $month_names = array($this->getTranslator()->_s('Jan'), $this->getTranslator()->_s('Feb'), $this->getTranslator()->_s('Mar'), $this->getTranslator()->_s('Apr'), $this->getTranslator()->_s('May'), $this->getTranslator()->_s('Jun'), $this->getTranslator()->_s('Jul'), $this->getTranslator()->_s('Aug'), $this->getTranslator()->_s('Sep'), $this->getTranslator()->_s('Oct'), $this->getTranslator()->_s('Nov'), $this->getTranslator()->_s('Dec'));
        ob_start(); ?>
        <input type="button" class="input-date-day-substract" value="&lt;" onclick="<?=$form_name?>OnDayChange(-1, '<?=$field_name?>', <?=$from_field_name?>, <?=$to_field_name?>, <?=$nights_field_name?>, '<?=$calendar_field_id?>', <?=$from_calendar_field_id?>, <?=$to_calendar_field_id?>)" />
        <input type="text" id="<?=$field_name?>[day]" name="<?=$field_name?>[day]" value="<?=$day?>" onchange="<?=$form_name?>OnDayChange(0, '<?=$field_name?>', <?=$from_field_name?>, <?=$to_field_name?>, <?=$nights_field_name?>, '<?=$calendar_field_id?>', <?=$from_calendar_field_id?>, <?=$to_calendar_field_id?>)"<?=$parameters['input-date-day'].$disabled?> />
        <input type="button" class="input-date-day-add" value="&gt;" onclick="<?=$form_name?>OnDayChange(1, '<?=$field_name?>', <?=$from_field_name?>, <?=$to_field_name?>, <?=$nights_field_name?>, '<?=$calendar_field_id?>', <?=$from_calendar_field_id?>, <?=$to_calendar_field_id?>)" />
        <?php $day_selector = ob_get_clean();
        ob_start(); ?>
        <input type="hidden" name="<?=$field_name?>[year_month]" value="<?=$year_month?>" />
        <?php $year_month_selector = ob_get_clean();
        if ($parameters['calendar']) {
            ob_start(); ?>
            <? if ($parameters['input-date-calendar']): ?><span<?=$parameters['input-date-calendar']?>><? endif; ?>
                <input type="hidden" value="<?=$year?>-<?=$month?>-<?=$day?>" onchange="<?=$form->getFormName()?>OnCalendarChange('<?=$field_name?>', <?=$from_field_name?>, <?=$to_field_name?>, <?=$nights_field_name?>, '<?=$calendar_field_id?>', <?=$from_calendar_field_id?>, <?=$to_calendar_field_id?>);" id="<?=$calendar_field_id?>" />
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        $('#<?=$calendar_field_id?>').datepicker({firstDay: 1, minDate: new Date(<?=$from_year?>, <?=$from_month - 1?>, <?=$from_day?>), maxDate: new Date(<?=$last_year?>, <?=$last_month - 1?>, <?=$last_day?>), dayNamesMin: ['<?=$this->getTranslator()->_s('Sun')?>', '<?=$this->getTranslator()->_s('Mon')?>', '<?=$this->getTranslator()->_s('Tue')?>', '<?=$this->getTranslator()->_s('Wed')?>', '<?=$this->getTranslator()->_s('Thu')?>', '<?=$this->getTranslator()->_s('Fri')?>', '<?=$this->getTranslator()->_s('Sat')?>'], monthNames: ['<?=$this->getTranslator()->_s('January')?>', '<?=$this->getTranslator()->_s('February')?>', '<?=$this->getTranslator()->_s('March')?>', '<?=$this->getTranslator()->_s('April')?>', '<?=$this->getTranslator()->_s('May')?>', '<?=$this->getTranslator()->_s('June')?>', '<?=$this->getTranslator()->_s('July')?>', '<?=$this->getTranslator()->_s('August')?>', '<?=$this->getTranslator()->_s('September')?>', '<?=$this->getTranslator()->_s('October')?>', '<?=$this->getTranslator()->_s('November')?>', '<?=$this->getTranslator()->_s('December')?>'], dateFormat: 'yy-mm-dd', showOn: 'button', buttonText: '<?=$this->getTranslator()->_s('Calendar')?>'});
                    });
                </script>
            <? if ($parameters['input-date-calendar']): ?></span><? endif; ?>
            <?php $calendar = ob_get_clean();
        } else {
            $calendar = '';
        }
        if ($parameters['span']) {
            return '<span'.$parameters['span'].'>'.$day_selector.$year_month_selector.'</span><span id="_'.$calendar_field_id.'" class="input-date-month-year">'.$month_names[$month - 1].' '.$year.'</span>'.$calendar;
        } else {
            return $day_selector.$year_month_selector.'<span id="_'.$calendar_field_id.'" class="input-date-month-year">'.$month_names[$month - 1].' '.$year.'</span>'.$calendar;
        }
    }
}