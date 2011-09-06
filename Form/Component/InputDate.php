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
 * SketchFormComponentInputDate
 *
 * @package Components
 */
class SketchFormComponentInputDate extends SketchFormComponent {
    /**
     *
     * @return string
     */
    function javascript() {
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $form_name = $this->getForm()->getFormName();
        ob_start(); ?>
        function <?=$form_name?>UpdateDays(h){var a=document.forms.<?=$form_name?>;var g=a[h+"[day]"];var e=a[h+"[year_month]"].value.substr(4,2)-1;var f=a[h+"[year_month]"].value.substr(0,4);if(f!=0&&e>=0){var c=new Date();var k=1;var l=0;if(l==0){var b;l=31;do{b=new Date(f,e,l--)}while(e<b.getMonth())}if(e==c.getMonth()&&f==c.getFullYear()){k=(k>(c.getDate()))?k:c.getDate()}var d=g.value;while(g.options.length){g.options[0]=null}for(i=k;i<l+2;i++){option=new Option(((i>9)?i:"0"+i),i,false,false);g.options[j=g.length]=option;if(i==d){g.selectedIndex=j}}}else{while(g.options.length){g.options[0]=null}g.options[0]=new Option("...",null,false,false)}}function <?=$form_name?>UpdateDate(c,d){var g=document.forms.<?=$form_name?>;var h=d.getMonth()>8?String(d.getMonth()+1):"0"+String(d.getMonth()+1);var f=String(d.getFullYear())+h;var a=g[c+"[year_month]"];for(e=0;e<a.length;e++){if(a.options[e].value==f){a.options[e].selected=true;break}}<?=$form_name?>UpdateDays(c);var b=g[c+"[day]"];for(var e=0;e<b.length;e++){if(b.options[e].value==d.getDate()){b.options[e].selected=true;break}}}function <?=$form_name?>UpdateNights(d,c,e,a,f){var b=document.forms.<?=$form_name?>;var g=new Date(b[d+"[year_month]"].value.substr(0,4),b[d+"[year_month]"].value.substr(4,2)-1,b[d+"[day]"].value);var h=new Date(b[c+"[year_month]"].value.substr(0,4),b[c+"[year_month]"].value.substr(4,2)-1,b[c+"[day]"].value);var k=Math.round((h-g)/86400000);b[e].value=k;<?=$form_name?>OnNightsChange(d,c,e,a,f)}function <?=$form_name?>OnDayChange(m,f,e,g,d,a,l){if(m==f){<?=$form_name?>OnNightsChange(f,e,g,a,l)}else{if(g!=null){<?=$form_name?>UpdateNights(f,e,g,a,l)}else{var b=document.forms.<?=$form_name?>;var k=Number(b[m+"[day]"].value);var h=b[m+"[year_month]"];var c=new Date(h.value.substr(0,4),h.value.substr(4,2)-1,k);jQuery("#"+d).val(c.getFullYear()+"-"+(c.getMonth()+1)+"-"+c.getDate())}}}function <?=$form_name?>OnMonthChange(a,e,b,d,g,f,c){<?=$form_name?>UpdateDays(a);<?=$form_name?>OnDayChange(a,e,b,d,g,f,c)}function <?=$form_name?>OnNightsChange(f,d,g,b,k){var c=document.forms.<?=$form_name?>;if(c[g].value<1){c[g].value=1}if(c[g].value>90){c[g].value=90}var a=Number(c[f+"[day]"].value);var l=a+Number(c[g].value);var h=c[f+"[year_month]"];var e=new Date(h.value.substr(0,4),h.value.substr(4,2)-1,a);var m=new Date(h.value.substr(0,4),h.value.substr(4,2)-1,l);<?=$form_name?>UpdateDate(d,m);jQuery("#"+b).val(e.getFullYear()+"-"+(e.getMonth()+1)+"-"+e.getDate());jQuery("#"+k).val(m.getFullYear()+"-"+(m.getMonth()+1)+"-"+m.getDate())}function <?=$form_name?>OnCalendarChange(a,e,b,d,h,g,c){var f=jQuery("#"+h).val().split("-");jQuery(":input[name='"+a+"[year_month]']").val(f[0]+f[1]);<?=$form_name?>UpdateDays(a);jQuery(":input[name='"+a+"[day]']").val(f[2]);<?=$form_name?>OnDayChange(a,e,b,d,h,g,c)};
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
        if ($field_value instanceof SketchDateTime) list($year, $month, $day) = $field_value->toArray();
        elseif (!$parameters['null']) list($year, $month, $day) = SketchDateTime::Today()->toArray();
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
        <select id="<?=$field_name?>[day]" name="<?=$field_name?>[day]" onchange="<?=$form_name?>OnDayChange('<?=$field_name?>', <?=$from_field_name?>, <?=$to_field_name?>, <?=$nights_field_name?>, '<?=$calendar_field_id?>', <?=$from_calendar_field_id?>, <?=$to_calendar_field_id?>)"<?=$parameters['input-date-day'].$disabled?>>
            <? foreach ($days as $key => $value): ?>
                <option value="<?=htmlspecialchars($key)?>" <?=(($day == $key) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=htmlspecialchars($value)?></option>
            <? endforeach; ?>
        </select>
        <?php $day_selector = ob_get_clean();
        ob_start(); ?>
        <input type="hidden" name="<?=$field_name?>[day]" value="1" />
        <?php $day_hidden = ob_get_clean();
        ob_start(); ?>
        <select name="<?=$field_name?>[year_month]" onchange="<?=$form_name?>OnMonthChange('<?=$field_name?>', <?=$from_field_name?>, <?=$to_field_name?>, <?=$nights_field_name?>, '<?=$calendar_field_id?>', <?=$from_calendar_field_id?>, <?=$to_calendar_field_id?>)"<?=$parameters['input-date-year-month'].$disabled?>>
            <? foreach ($months as $key => $value): ?>
                <option value="<?=htmlspecialchars($key)?>" <?=(($year_month == $key) ? 'selected="selected" class="select-option selected"' : 'class="select-option"')?>><?=htmlspecialchars($value)?></option>
            <? endforeach; ?>
        </select>
        <?php $year_month_selector = ob_get_clean();
        if ($parameters['calendar']) {
            ob_start(); ?>
                    <? if ($parameters['input-date-calendar']): ?><span<?=$parameters['input-date-calendar']?>><? endif; ?>
                        <input type="hidden" value="<?=$year?>-<?=$month?>-<?=$day?>" onchange="<?=$form->getFormName()?>OnCalendarChange('<?=$field_name?>', <?=$from_field_name?>, <?=$to_field_name?>, <?=$nights_field_name?>, '<?=$calendar_field_id?>', <?=$from_calendar_field_id?>, <?=$to_calendar_field_id?>);" id="<?=$calendar_field_id?>" />
                        <script type="text/javascript">
                            jQuery(document).ready(function($) {
                                $('#<?=$calendar_field_id?>').datepicker({firstDay: 1, minDate: new Date(<?=$from_year?>, <?=$from_month - 1?>, <?=$from_day?>), maxDate: new Date(<?=$last_year?>, <?=$last_month - 1?>, <?=$last_day?>), dayNamesMin: ['<?=$this->getTranslator()->_('Sun')?>', '<?=$this->getTranslator()->_('Mon')?>', '<?=$this->getTranslator()->_('Tue')?>', '<?=$this->getTranslator()->_('Wed')?>', '<?=$this->getTranslator()->_('Thu')?>', '<?=$this->getTranslator()->_('Fri')?>', '<?=$this->getTranslator()->_('Sat')?>'], monthNames: ['<?=$this->getTranslator()->_('January')?>', '<?=$this->getTranslator()->_('February')?>', '<?=$this->getTranslator()->_('March')?>', '<?=$this->getTranslator()->_('April')?>', '<?=$this->getTranslator()->_('May')?>', '<?=$this->getTranslator()->_('June')?>', '<?=$this->getTranslator()->_('July')?>', '<?=$this->getTranslator()->_('August')?>', '<?=$this->getTranslator()->_('September')?>', '<?=$this->getTranslator()->_('October')?>', '<?=$this->getTranslator()->_('November')?>', '<?=$this->getTranslator()->_('December')?>'], dateFormat: 'yy-mm-dd', showOn: 'button', buttonText: '<?=$this->getTranslator()->_('Calendar')?>'});
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