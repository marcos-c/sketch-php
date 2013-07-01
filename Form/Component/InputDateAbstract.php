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
 * SketchFormComponentInputDateAbstract
 *
 * @package Components
 */
abstract class SketchFormComponentInputDateAbstract extends SketchFormComponent {
    /**
     * @param $parameters
     * @param $form
     * @param $attribute
     * @return array
     */
    protected function resolve($parameters, $form, $attribute) {
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
            $days = array('...');
            $months = array('0000' => array('00' => '...'));
            $years = array('0000' => '...');
            $year_months = array('000000' => '...');
        } else {
            $days = array();
            $months = array();
            $years = array();
            $year_months = array();
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
            return array($parameters, $year, $month, $day, $year_month, $from_year, $from_month, $from_day, $to_year, $to_month, $to_day, $days, $months, $years, $year_months, $year_month_days);
        } else {
            $days[0] = '...';
            return array($parameters, $year, $month, $day, $year_month, $from_year, $from_month, $from_day, $to_year, $to_month, $to_day, $days, $months, $years, $year_months, $year_month_days);
        }
    }
}