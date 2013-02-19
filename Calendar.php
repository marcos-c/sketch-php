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

class SketchCalendar extends SketchObject {
    /**
     *
     * @var array
     */
    private $monthDescriptions;

    /**
     *
     * @var array
     */
    private $monthShortDescriptions;

    /**
     *
     * @var array
     */
    private $dayDescriptions;

    /**
     *
     * @var array
     */
    private $dayShortDescriptions;

    /**
     *
     * @var array
     */
    private $dayOneLetterDescriptions;

    /**
     *
     * @var integer
     */
    private $month;

    /**
     *
     * @var integer
     */
    private $year;

    /**
     *
     * @param integer $month
     * @param integer $year
     */
    function __construct($month, $year) {
        $translator = $this->getLocale()->getTranslator();
        $this->monthDescriptions = array(1 => $translator->_('January'), $translator->_('February'), $translator->_('March'),
        $translator->_('April'), $translator->_('May'), $translator->_('June'), $translator->_('July'), $translator->_('August'),
        $translator->_('September'), $translator->_('October'), $translator->_('November'), $translator->_('December'));
        $this->monthShortDescriptions = array(1 => $translator->_('Jan'), $translator->_('Feb'), $translator->_('Mar'),
        $translator->_('Apr'), $translator->_('May'), $translator->_('Jun'), $translator->_('Jul'), $translator->_('Aug'),
        $translator->_('Sep'), $translator->_('Oct'), $translator->_('Nov'), $translator->_('Dec'));
        $this->dayDescriptions = array(1 => $translator->_('Monday'), $translator->_('Tuesday'), $translator->_('Wednesday'),
        $translator->_('Thursday'), $translator->_('Friday'), $translator->_('Saturday'), $translator->_('Sunday'));
        $this->dayShortDescriptions = array(1 => $translator->_('Mon'), $translator->_('Tue'), $translator->_('Wed'),
        $translator->_('Thu'), $translator->_('Fri'), $translator->_('Sat'), $translator->_('Sun'));
        $this->dayOneLetterDescriptions = array(1 => $translator->_('M'), $translator->_('T'), $translator->_('W'),
        $translator->_('T'), $translator->_('F'), $translator->_('S'), $translator->_('S'));
        $this->month = intval($month);
        $this->year = intval($year);
    }

    /**
     *
     * @return integer
     */
    function getMonth() {
        return $this->month;
    }

    /**
     *
     * @return integer
     */
    function getYear() {
        return $this->year;
    }

    /**
     *
     * @return array
     */
    function getMonthDescriptions() {
        return $this->monthDescriptions;
    }

    /**
     *
     * @return string
     */
    function getMonthDescription() {
        return $this->monthDescriptions[$this->month];
    }

    /**
     *
     * @return array
     */
    function getMonthShortDescriptions() {
        return $this->monthShortDescriptions;
    }

    /**
     *
     * @return string
     */
    function getMonthShortDescription() {
        return $this->monthShortDescriptions[$this->month];
    }

    /**
     *
     * @return array
     */
    function getDayDescriptions() {
        return $this->dayDescriptions;
    }

    /**
     *
     * @return array
     */
    function getDayShortDescriptions() {
        return $this->dayShortDescriptions;
    }

    /**
     *
     * @return array
     */
    function getDayOneLetterDescriptions() {
        return $this->dayOneLetterDescriptions;
    }

    /**
     *
     * @return array
     */
    function output() {
        $days = date("j", mktime(0, 0, 0, $this->month + 1, 1, $this->year) - 1);
        $first = date("w", mktime(0, 0, 0, $this->month, 1, $this->year));
        $first_week_day = ($first == 0) ? 7 : $first;
        $output = array();
        for ($i = 1; $i <= 42; $i++) {
            $day_index = $i - $first_week_day + 1;
            $output[$i]['value'] = ($i < $first_week_day || $day_index > $days) ? '&nbsp;' : $day_index;
            $output[$i]['control'] = (!($i % 7) && $i < 42) ? 'end-of-row' : null;
        }
        return $output;
    }
}