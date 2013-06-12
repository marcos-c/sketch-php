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

class Calendar extends Object {
    /**
     * @var array
     */
    private $monthDescriptions;

    /**
     * @var array
     */
    private $monthShortDescriptions;

    /**
     * @var array
     */
    private $dayDescriptions;

    /**
     * @var array
     */
    private $dayShortDescriptions;

    /**
     * @var array
     */
    private $dayOneLetterDescriptions;

    /**
     * @var integer
     */
    private $month;

    /**
     * @var integer
     */
    private $year;

    /**
     * @param integer $month
     * @param integer $year
     */
    function __construct($month, $year) {
        $translator = $this->getLocale()->getTranslator();
        $this->monthDescriptions = array(1 => $translator->_s('January'), $translator->_s('February'), $translator->_s('March'),
        $translator->_s('April'), $translator->_s('May'), $translator->_s('June'), $translator->_s('July'), $translator->_s('August'),
        $translator->_s('September'), $translator->_s('October'), $translator->_s('November'), $translator->_s('December'));
        $this->monthShortDescriptions = array(1 => $translator->_s('Jan'), $translator->_s('Feb'), $translator->_s('Mar'),
        $translator->_s('Apr'), $translator->_s('May'), $translator->_s('Jun'), $translator->_s('Jul'), $translator->_s('Aug'),
        $translator->_s('Sep'), $translator->_s('Oct'), $translator->_s('Nov'), $translator->_s('Dec'));
        $this->dayDescriptions = array(1 => $translator->_s('Monday'), $translator->_s('Tuesday'), $translator->_s('Wednesday'),
        $translator->_s('Thursday'), $translator->_s('Friday'), $translator->_s('Saturday'), $translator->_s('Sunday'));
        $this->dayShortDescriptions = array(1 => $translator->_s('Mon'), $translator->_s('Tue'), $translator->_s('Wed'),
        $translator->_s('Thu'), $translator->_s('Fri'), $translator->_s('Sat'), $translator->_s('Sun'));
        $this->dayOneLetterDescriptions = array(1 => $translator->_xs('M', 'Monday'), $translator->_xs('T', 'Tuesday'), $translator->_xs('W', 'Wednesday'),
        $translator->_xs('T', 'Thursday'), $translator->_xs('F', 'Friday'), $translator->_xs('S', 'Saturday'), $translator->_xs('S', 'Sunday'));
        $this->month = intval($month);
        $this->year = intval($year);
    }

    /**
     * @return integer
     */
    function getMonth() {
        return $this->month;
    }

    /**
     * @return integer
     */
    function getYear() {
        return $this->year;
    }

    /**
     * @return array
     */
    function getMonthDescriptions() {
        return $this->monthDescriptions;
    }

    /**
     * @return string
     */
    function getMonthDescription() {
        return $this->monthDescriptions[$this->month];
    }

    /**
     * @return array
     */
    function getMonthShortDescriptions() {
        return $this->monthShortDescriptions;
    }

    /**
     * @return string
     */
    function getMonthShortDescription() {
        return $this->monthShortDescriptions[$this->month];
    }

    /**
     * @return array
     */
    function getDayDescriptions() {
        return $this->dayDescriptions;
    }

    /**
     * @return array
     */
    function getDayShortDescriptions() {
        return $this->dayShortDescriptions;
    }

    /**
     * @return array
     */
    function getDayOneLetterDescriptions() {
        return $this->dayOneLetterDescriptions;
    }

    /**
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