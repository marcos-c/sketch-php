<?php
/**
 * This file is part of the Sketch Framework
 * (http://code.google.com/p/sketch-framework/)
 *
 * Copyright (c) 2011 Marcos Cooper | http://marcoscooper.com
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
 * SketchFormComponentInputDate
 *
 * @package Components
 */
class SketchFormComponentInputDate extends SketchFormComponent {
    private function getDaySelector($field_name, $parameters, $days, $day) {
        $disabled = ($parameters['disabled'] !== false) ? ' disabled="disabled"' : '';
        ob_start(); ?>
        <select id="<?=$field_name?>[day]" name="<?=$field_name?>[day]"<?=$parameters['input-date-day'].($parameters['onchange'] != null ? 'onchange="'.$parameters['onchange'].'"' : '').$disabled?>>
            <? foreach ($days as $key => $value): ?>
                <option value="<?=htmlspecialchars($key)?>" <?=(($day == $key) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=htmlspecialchars($value)?></option>
            <? endforeach; ?>
        </select>
        <?php return ob_get_clean();
    }

    private function getMonthSelector($field_name, $parameters, $months, $month) {
        $disabled = ($parameters['disabled'] !== false) ? ' disabled="disabled"' : '';
        ob_start(); ?>
        <select name="<?=$field_name?>[month]"<?=$parameters['input-date-month'].($parameters['onchange'] != null ? 'onchange="'.$parameters['onchange'].'"' : '').$disabled?>>
            <? foreach ($months as $key => $value): ?>
                <option value="<?=htmlspecialchars($key)?>" <?=(($month == $key) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=htmlspecialchars($value)?></option>
            <? endforeach; ?>
        </select>
        <?php return ob_get_clean();
    }

    private function getYearSelector($field_name, $parameters, $years, $year) {
        $disabled = ($parameters['disabled'] !== false) ? ' disabled="disabled"' : '';
        ob_start(); ?>
        <select name="<?=$field_name?>[year]"<?=$parameters['input-date-year'].($parameters['onchange'] != null ? 'onchange="'.$parameters['onchange'].'"' : '').$disabled?>>
            <? foreach ($years as $key => $value): ?>
                <option value="<?=htmlspecialchars($key)?>" <?=(($year == $key) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=htmlspecialchars($value)?></option>
            <? endforeach; ?>
        </select>
        <?php return ob_get_clean();
    }

    private function getYearMonthSelector($field_name, $parameters, $year_months, $year_month) {
        $disabled = ($parameters['disabled'] !== false) ? ' disabled="disabled"' : '';
        ob_start(); ?>
        <select name="<?=$field_name?>[year_month]"<?=$parameters['input-date-year-month'].($parameters['onchange'] != null ? 'onchange="'.$parameters['onchange'].'"' : '').$disabled?>>
            <? foreach ($year_months as $key => $value): ?>
                <option value="<?=htmlspecialchars($key)?>" <?=(($year_month == $key) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=htmlspecialchars($value)?></option>
            <? endforeach; ?>
        </select>
        <?php return ob_get_clean();
    }

    private function getCalendarAndOrJavascript($calendar_field_id, $field_name, $parameters, $year, $month, $day, $from_year, $from_month, $from_day, $to_year, $to_month, $to_day, $months, $year_month_days) {
        ob_start(); ?>
            <? if ($parameters['calendar']): ?>
                <? if ($parameters['input-date-calendar']): ?><span<?=$parameters['input-date-calendar']?>><? endif; ?>
                    <input type="hidden" value="<?=$year?>-<?=$month?>-<?=$day?>" id="<?=$calendar_field_id?>" />
                    <script type="text/javascript">
                        //<![CDATA[
                            jQuery(function($){
                                var months = <?=SketchUtils::encodeJSON($months)?>;
                                var year_month_days = <?=SketchUtils::encodeJSON($year_month_days)?>;
                                var calendar = $('#<?=$calendar_field_id?>');
                                calendar.datepicker({firstDay: 1, minDate: new Date(<?=$from_year?>, <?=$from_month - 1?>, <?=$from_day?>), maxDate: new Date(<?=$to_year?>, <?=$to_month - 1?>, <?=$to_day?>), dayNamesMin: ['<?=$this->getTranslator()->_('Sun')?>', '<?=$this->getTranslator()->_('Mon')?>', '<?=$this->getTranslator()->_('Tue')?>', '<?=$this->getTranslator()->_('Wed')?>', '<?=$this->getTranslator()->_('Thu')?>', '<?=$this->getTranslator()->_('Fri')?>', '<?=$this->getTranslator()->_('Sat')?>'], monthNames: ['<?=$this->getTranslator()->_('January')?>', '<?=$this->getTranslator()->_('February')?>', '<?=$this->getTranslator()->_('March')?>', '<?=$this->getTranslator()->_('April')?>', '<?=$this->getTranslator()->_('May')?>', '<?=$this->getTranslator()->_('June')?>', '<?=$this->getTranslator()->_('July')?>', '<?=$this->getTranslator()->_('August')?>', '<?=$this->getTranslator()->_('September')?>', '<?=$this->getTranslator()->_('October')?>', '<?=$this->getTranslator()->_('November')?>', '<?=$this->getTranslator()->_('December')?>'], dateFormat: 'yy-mm-dd', showOn: 'button', buttonText: '<?=$this->getTranslator()->_('Calendar')?>'});
                                var day = $(':input[name="<?=$field_name?>[day]"]');
                                <? if (in_array($parameters['format'], array('mY', 'd-mY'))): ?>
                                    var year_month = $(':input[name="<?=$field_name?>[year_month]"]');
                                    day.change(function(e){
                                        e.stopPropagation();
                                        calendar.val(year_month.val().substr(0, 4) + '-' + year_month.val().substr(4, 2) + '-' + ((day.val() > 9) ? day.val() : '0' + day.val()));
                                    });
                                    year_month.change(function(){
                                        var selected_day = day.val();
                                        if (day.is(":visible")) {
                                            day[0].options.length = 0;
                                            if (year_month_days[year_month.val()] == undefined) {
                                                day[0].options[j = day[0].options.length] = new Option('...', 0, false, false);
                                            } else {
                                                var i, j; for (i = year_month_days[year_month.val()][0]; i <= year_month_days[year_month.val()][1]; i++) {
                                                    day[0].options[j = day[0].options.length] = new Option(((i > 9) ? i : '0' + i), i, false, i == selected_day);
                                                }
                                            }
                                        }
                                        day.change();
                                    });
                                    year_month.change();
                                    calendar.change(function(){
                                        var new_date = calendar.val().split('-');
                                        year_month.val(new_date[0] + new_date[1]);
                                        if (day.is(":visible")) {
                                            day[0].options.length = 0;
                                            if (year_month_days[year_month.val()] == undefined) {
                                                day[0].options[j = day[0].options.length] = new Option('...', 0, false, false);
                                            } else {
                                                var i, j; for (i = year_month_days[year_month.val()][0]; i <= year_month_days[year_month.val()][1]; i++) {
                                                    day[0].options[j = day[0].options.length] = new Option(((i > 9) ? i : '0' + i), i, false, i == parseInt(new_date[2]));
                                                }
                                            }
                                        } else {
                                            day.val(parseInt(new_date[2]));
                                        }
                                    });
                                <? elseif (in_array($parameters['format'], array('m-Y', 'Y-m', 'd-m-Y', 'M-d-Y', 'Y-m-d'))): ?>
                                    var month = $(':input[name="<?=$field_name?>[month]"]');
                                    var year = $(':input[name="<?=$field_name?>[year]"]');
                                    day.change(function(){
                                        calendar.val(year.val() + '-' + ((month.val() > 9) ? month.val() : '0' + month.val()) + '-' + ((day.val() > 9) ? day.val() : '0' + day.val()));
                                    });
                                    month.change(function(){
                                        var selected_year_month = year.val() + ((month.val() > 9) ? month.val() : '0' + month.val());
                                        var selected_day = day.val();
                                        if (day.is(":visible")) {
                                            day[0].options.length = 0;
                                            if (year_month_days[selected_year_month] == undefined) {
                                                day[0].options[j = day[0].options.length] = new Option('...', 0, false, false);
                                            } else {
                                                var i, j; for (i = year_month_days[selected_year_month][0]; i <= year_month_days[selected_year_month][1]; i++) {
                                                    day[0].options[j = day[0].options.length] = new Option(((i > 9) ? i : '0' + i), i, false, i == selected_day);
                                                }
                                            }
                                        }
                                        day.change();
                                    });
                                    year.change(function(){
                                        var selected_month = month.val();
                                        month[0].options.length = 0;
                                        if (months[year.val()] == undefined) {
                                            month[0].options[month[0].options.length] = new Option('...', 0, false, false);
                                        } else {
                                            for (var i in months[year.val()]) {
                                                month[0].options[month[0].options.length] = new Option(months[year.val()][i], i, false, i == selected_month);
                                            }
                                        }
                                        month.change();
                                    });
                                    year.change();
                                    calendar.change(function(){
                                        var new_date = calendar.val().split('-');
                                        year.val(new_date[0]);
                                        var selected_month = month.val();
                                        month[0].options.length = 0;
                                        if (months[year.val()] == undefined) {
                                            month[0].options[month[0].options.length] = new Option('...', 0, false, false);
                                        } else {
                                            for (var i in months[year.val()]) {
                                                month[0].options[month[0].options.length] = new Option(months[year.val()][i], i, false, i == selected_month);
                                            }
                                        }
                                        month.val(parseInt(new_date[1]));
                                        var selected_year_month = year.val() + ((month.val() > 9) ? month.val() : '0' + month.val());
                                        if (day.is(":visible")) {
                                            day[0].options.length = 0;
                                            if (year_month_days[selected_year_month] == undefined) {
                                                day[0].options[j = day[0].options.length] = new Option('...', 0, false, false);
                                            } else {
                                                var i, j; for (i = year_month_days[selected_year_month][0]; i <= year_month_days[selected_year_month][1]; i++) {
                                                    day[0].options[j = day[0].options.length] = new Option(((i > 9) ? i : '0' + i), i, false, i == parseInt(new_date[2]));
                                                }
                                            }
                                        } else {
                                            day.val(parseInt(new_date[2]));
                                        }
                                    });
                                <? endif; ?>
                            });
                        //]]>
                    </script>
                <? if ($parameters['input-date-calendar']): ?></span><? endif; ?>
            <? else: ?>
                <script type="text/javascript">
                    //<![CDATA[
                        jQuery(function($){
                            var months = <?=SketchUtils::encodeJSON($months)?>;
                            var year_month_days = <?=SketchUtils::encodeJSON($year_month_days)?>;
                            var day = $(':input[name="<?=$field_name?>[day]"]');
                            <? if (in_array($parameters['format'], array('mY', 'd-mY'))): ?>
                                var year_month = $(':input[name="<?=$field_name?>[year_month]"]');
                                year_month.change(function(){
                                    var selected_day = day.val();
                                    if (day.is(":visible")) {
                                        day[0].options.length = 0;
                                        if (year_month_days[year_month.val()] == undefined) {
                                            day[0].options[j = day[0].options.length] = new Option('...', 0, false, false);
                                        } else {
                                            var i, j; for (i = year_month_days[year_month.val()][0]; i <= year_month_days[year_month.val()][1]; i++) {
                                                day[0].options[j = day[0].options.length] = new Option(((i > 9) ? i : '0' + i), i, false, i == selected_day);
                                            }
                                        }
                                    }
                                });
                                year_month.change();
                            <? elseif (in_array($parameters['format'], array('m-Y', 'Y-m', 'd-m-Y', 'Y-m-d', 'M-d-Y'))): ?>
                                var month = $(':input[name="<?=$field_name?>[month]"]');
                                var year = $(':input[name="<?=$field_name?>[year]"]');
                                month.change(function(){
                                    var selected_year_month = year.val() + ((month.val() > 9) ? month.val() : '0' + month.val());
                                    var selected_day = day.val();
                                    if (day.is(":visible")) {
                                        day[0].options.length = 0;
                                        if (year_month_days[selected_year_month] == undefined) {
                                            day[0].options[j = day[0].options.length] = new Option('...', 0, false, false);
                                        } else {
                                            var i, j; for (i = year_month_days[selected_year_month][0]; i <= year_month_days[selected_year_month][1]; i++) {
                                                day[0].options[j = day[0].options.length] = new Option(((i > 9) ? i : '0' + i), i, false, i == selected_day);
                                            }
                                        }
                                    }
                                });
                                year.change(function(){
                                    var selected_month = month.val();
                                    month[0].options.length = 0;
                                    for (var i in months[year.val()]) {
                                        month[0].options[month[0].options.length] = new Option(months[year.val()][i], i, false, i == selected_month);
                                    }
                                    month.change();
                                });
                                year.change();
                            <? endif; ?>
                        });
                    //]]>
                </script>
            <? endif; ?>
        <?php return ob_get_clean();
    }

    /**
     *
     * @return string
     */
    function saveHTML() {
        $form = $this->getForm();
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $parameters = $this->extend(array(
            'disabled' => false,
            'null' => false,
            'format' => 'd-m-Y',
            'inverse' => false,
            'month_count' => 35,
            'from_current_date' => false,
            'calendar' => true,
            'input-date-day' => array('id' => null, 'class' => 'input-date-day', 'style' => null),
            'input-date-month' => array('id' => null, 'class' => 'input-date-month', 'style' => null),
            'input-date-year' => array('id' => null, 'class' => 'input-date-year', 'style' => null),
            'input-date-year-month' => array('id' => null, 'class' => 'input-date-year-month', 'style' => null),
            'input-date-calendar' => array('id' => null, 'class' => 'input-date-calendar', 'style' => null),
            'onchange' => null
        ), array_shift($arguments));
        $form_name = $form->getFormName();
        // Field names
        $field_name = $form->getFieldName($attribute);
        $calendar_field_id = md5($field_name);
        // From and to dates
        if ($parameters['from_current_date']) {
            $parameters['from_date'] = SketchDateTime::Today();
        }
        if ($parameters['to_current_date']) {
            $parameters['to_date'] = SketchDateTime::Today();
            $parameters['from_date'] = $parameters['to_date']->addInterval('-'.$parameters['month_count'].' months');
        } else {
            if (!($parameters['from_date'] instanceof SketchDateTime)) {
                $month_count = ceil($parameters['month_count'] / 2);
                $parameters['from_date'] = SketchDateTime::Today()->addInterval('-'.$month_count.' months');
            }
            if (!($parameters['to_date'] instanceof SketchDateTime)) {
                $month_count = intval($parameters['month_count']);
                $parameters['to_date'] = $parameters['from_date']->addInterval($month_count.' months');
            }
        }
        // Field value
        $field_value = $form->getFieldValue($attribute);
        if ($field_value instanceof SketchDateTime) {
            list($year, $month, $day) = $field_value->toArray();
        } else if (is_array($field_value)) {
            $year = $field_value['year'];
            $month = $field_value['month'];
            $day = $field_value['day'];
        } else if ($parameters['null'] == false) {
            list($year, $month, $day) = $parameters['from_date']->toArray();
        }
        $year_month = sprintf('%04d%02d', $year, $month);
        $year = sprintf('%04d', $year);
        $from_year = $parameters['from_date']->getYear();
        $from_month = $parameters['from_date']->getMonth();
        $from_day = $parameters['from_date']->getDay();
        $to_year = $parameters['to_date']->getYear();
        $to_month = $parameters['to_date']->getMonth();
        $to_day = $parameters['to_date']->getDay();
        if ($parameters['null']) {
            $days = array('...'); $months = array('0000' => array('00' => '...')); $years = array('0000' => '...'); $year_months = array('000000' => '...');
        } else {
            $days = array(); $months = array(); $years = array(); $year_months = array();
        }
        $year_month_days = array();
        $date = new SketchDateTime($parameters['from_date']->toString('Y-m-1'));
        $month_names = SketchLocaleISO::getMonthNames();
        do {
            $years[$date->getYear()] = $date->getYear();
            $months[$date->getYear()][intval($date->getMonth())] = ($parameters['format'] == 'M-d-Y') ? $month_names[intval($date->getMonth())] : sprintf('%02d', $date->getMonth());
            $key = sprintf('%04d%02d', $date->getYear(), $date->getMonth());
            $year_months[$key] = sprintf('%02d - %04d', $date->getMonth(), $date->getYear());
            $year_month_days[$key] = array($date->equals($parameters['from_date']) ? $date->getDay() : 1, $date->getLastDay());
            $date = $date->addInterval('1 month');
        } while ($parameters['to_date']->equals($date) || $parameters['to_date']->greater($date));
        $year_month_days[$key][1] = $parameters['to_date']->getDay();
        if (is_array($year_month_days[$year_month])) {
            for ($i = $year_month_days[$year_month][0]; $i <= $year_month_days[$year_month][1]; $i++) $days[$i] = sprintf('%02d', $i);
        } else {
            $days[0] = '...';
        }
        $calendar = $this->getCalendarAndOrJavascript($calendar_field_id, $field_name, $parameters, $year, $month, $day, $from_year, $from_month, $from_day, $to_year, $to_month, $to_day, $months, $year_month_days);
        if (in_array($parameters['format'], array('mY', 'd-mY'))) {
            $year_month_selector = $this->getYearMonthSelector($field_name, $parameters, $year_months, $year_month);
            if ($parameters['format'] == 'd-mY') {
                return $this->getDaySelector($field_name, $parameters, $days, $day).$year_month_selector.$calendar;
            } else {
                return '<input type="hidden" name="'.$field_name.'[day]" value="1" />'.$year_month_selector.$calendar;
            }
        } elseif (in_array($parameters['format'], array('m-Y', 'Y-m', 'd-m-Y', 'Y-m-d', 'M-d-Y'))) {
            $month_selector = $this->getMonthSelector($field_name, $parameters, $months[$year], $month);
            if ($parameters['inverse']) $years = array_reverse($years, true);
            $year_selector = $this->getYearSelector($field_name, $parameters, $years, $year);
            if ($parameters['format'] == 'd-m-Y') {
                return $this->getDaySelector($field_name, $parameters, $days, $day).$month_selector.$year_selector.$calendar;
            } elseif ($parameters['format'] == 'M-d-Y') {
                return $month_selector.$this->getDaySelector($field_name, $parameters, $days, $day).$year_selector.$calendar;
            } elseif ($parameters['format'] == 'Y-m-d') {
                return $year_selector.$month_selector.$this->getDaySelector($field_name, $parameters, $days, $day).$calendar;
            } elseif ($parameters['format'] == 'Y-m') {
                return $year_selector.$month_selector.'<input type="hidden" name="'.$field_name.'[day]" value="1" />'.$calendar;
            } else {
                return $month_selector.'<input type="hidden" name="'.$field_name.'[day]" value="1" />'.$year_selector.$calendar;
            }
        } else {
            throw new Exception(sprintf($this->getTranslator()->_('%s is not a supported format for SketchFormComponentInputDate'), $parameters['format']));
        }
    }
}