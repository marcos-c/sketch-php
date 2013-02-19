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

class SketchDateTime extends SketchObject {
    /**
     *
     * @var integer
     */
    private $dateTime = null;

    /**
     *
     * @var array
     */
    private $dateTimeArray = null;

    /**
     *
     * @return SketchDateTime
     */
    static function Now() {
        return new SketchDateTime(time());
    }

    /**
     *
     * @return SketchDateTime
     */
    static function Today() {
        return new SketchDateTime(self::Now()->toString('Y-m-d'));
    }

    /**
     *
     * @return array
     */
    static function getTimeZoneIdentifiers() {
        $time_zones = array();
        foreach (timezone_identifiers_list() as $s) {
            $r = explode('/', $s);
            if (in_array($r[0], array('GMT', 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'))) {
                $time_zones[$s] = $s;
            }
        }
        ksort($time_zones);
        return $time_zones;
    }

    /**
     *
     * @param mixed $date_time
     */
    function __construct($date_time = null) {
        if ($date_time instanceof SketchDateTime) {
            $this->dateTime = $date_time->dateTime;
            $this->dateTimeArray = $date_time->dateTimeArray;
        } else {
            if (is_array($date_time)) {
                if (array_key_exists('year', $date_time) && array_key_exists('month', $date_time) && array_key_exists('day', $date_time) && array_key_exists('hour', $date_time) && array_key_exists('minute', $date_time) && array_key_exists('seconds', $date_time)) {
                    $date_time = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $date_time['year'], $date_time['month'], $date_time['day'], $date_time['hour'], $date_time['minute'], $date_time['seconds']);
                } else if (array_key_exists('year', $date_time) && array_key_exists('month', $date_time) && array_key_exists('day', $date_time) && array_key_exists('hour', $date_time) && array_key_exists('minute', $date_time)) {
                    $date_time = sprintf('%04d-%02d-%02d %02d:%02d:00', $date_time['year'], $date_time['month'], $date_time['day'], $date_time['hour'], $date_time['minute']);
                } else if (array_key_exists('year', $date_time) && array_key_exists('month', $date_time) && array_key_exists('day', $date_time)) {
                    $date_time = sprintf('%04d-%02d-%02d 00:00:00', $date_time['year'], $date_time['month'], $date_time['day']);
                } else if (array_key_exists('year_month', $date_time) && array_key_exists('day', $date_time)) {
                    $date_time = sprintf('%04d-%02d-%02d 00:00:00', intval(substr($date_time['year_month'], 0, 4)), intval(substr($date_time['year_month'], 4)), $date_time['day']);
                } else if (array_key_exists('hour', $date_time) && array_key_exists('minute', $date_time)) {
                    $date_time = sprintf('1970-01-01 %02d:%02d', $date_time['hour'], $date_time['minute']);
                } else {
                    throw new SketchDateTimeException(print_r($date_time, true));
                }
            } else if (preg_match('/^\d+$/', $date_time)) {
                $date_time = date('Y-m-d H:i:s', $date_time);
            }
            if (preg_match('/^(\d{2}):(\d{2})(?::(\d{2}))?$/', $date_time)) {
                $date_time = "1970-01-01 $date_time";
            }
            if (preg_match('/^((?:19|20)\d{2})-(\d{1,2})-(\d{1,2})(?: (\d{2}):(\d{2}))?(?::(\d{2}))?/', $date_time, $matches)) {
                if (checkdate($matches[2], $matches[3], $matches[1])) {
                    $this->dateTime = strtotime("$date_time GMT");
                    $this->dateTimeArray = array(0, 0, 0, 0, 0, 0);
                    $count = count($matches) - 1;
                    for ($i = 0; $i < $count; $i++) {
                        $this->dateTimeArray[$i] = intval($matches[$i + 1]);
                    }
                }
            }
        }
    }

    /**
     *
     * @return string
     */
    function __toString() {
        return $this->toString();
    }

    /**
     *
     * @return boolean
     */
    function isNull() {
        $test = true; if (is_array($this->dateTimeArray)) {
            foreach ($this->dateTimeArray as $value) {
                $test = $test && (intval($value) == 0);
            }
        } return $test;
    }

    /**
     *
     * @return boolean
     */
    function isValid() {
        return (!$this->isNull() && $this->dateTime != null);
    }

    /**
     *
     * @param SketchDateTime $date_time
     * @return boolean
     */
    function greater(SketchDateTime $date_time) {
        if ($date_time instanceof SketchDateTime) {
            return $this->dateTime > $date_time->dateTime;
        } else return false;
    }

    /**
     * 
     * @param SketchDateTime $from_date_time
     * @param SketchDateTime $to_date_time
     * @return boolean
     */
    function between(SketchDateTime $from_date_time, SketchDateTime $to_date_time) {
        return $this->dateTime >= $from_date_time->dateTime && $this->dateTime <= $to_date_time->dateTime;
    }

    /**
     *
     * @param string $format
     * @return string
     */
    function toString($format = 'Y-m-d H:i:s T') {
        if ($this->dateTime != null) {
            return gmdate($format, $this->dateTime);
        } else return null;
    }

    /**
     *
     * @return array
     */
    function toArray() {
        return $this->dateTimeArray;
    }

    /**
     *
     * @return integer
     */
    function getYear() {
        return $this->dateTimeArray[0];
    }

    /**
     *
     * @return integer
     */
    function getMonth() {
        return $this->dateTimeArray[1];
    }

    /**
     *
     * @return integer
     */
    function getDay() {
        return $this->dateTimeArray[2];
    }

    /**
     *
     * @return integer
     */
    function getLastDay() {
        return date('t', $this->dateTime);
    }

    /**
     *
     * @return integer
     */
    function getDayOfTheWeek() {
        $day_of_the_week = date('w', $this->dateTime);
        return $day_of_the_week == 0 ? 7 : $day_of_the_week;
    }

    /**
     *
     * @return integer
     */
    function toUnixTimestamp() {
        return $this->dateTime;
    }

    /**
     *
     * @param string $interval
     * @return SketchDateTime
     */
    function addInterval($interval) {
        if (preg_match('/^-?\d+ second(s)?$/', $interval)) {
            return new SketchDateTime(strtotime($interval, $this->dateTime));
        } else if (preg_match('/^-?\d+ minute(s)?$/', $interval)) {
            return new SketchDateTime(strtotime($interval, $this->dateTime));
        } else if (preg_match('/^-?\d+ hour(s)?$/', $interval)) {
            return new SketchDateTime(strtotime($interval, $this->dateTime));
        } else if (preg_match('/^-?\d+ day(s)?$/', $interval)) {
            return new SketchDateTime(strtotime($interval, $this->dateTime));
        } else if (preg_match('/^-?\d+ month(s)?$/', $interval)) {
            return new SketchDateTime(strtotime($interval, $this->dateTime));
        } else if (preg_match('/^-?\d+ week(s)?$/', $interval)) {
            return new SketchDateTime(strtotime($interval, $this->dateTime));
        } else if (preg_match('/^next month$/', $interval)) {
            return new SketchDateTime(strtotime($interval, $this->dateTime));
        } else if (preg_match('/^last month$/', $interval)) {
            return new SketchDateTime(strtotime($interval, $this->dateTime));
        }
    }

    /**
     *
     * @param SketchDateTime $date_time
     * @return integer
     */
    function substract(SketchDateTime $date_time) {
        $from = new SketchDateTime($this->toString('Y-m-d'));
        $to = new SketchDateTime($date_time->toString('Y-m-d'));
        return (($from->toUnixTimestamp() - $to->toUnixTimestamp()) / 86400);
    }

    /**
     *
     * @param SketchDateTime $date_time
     * @return boolean
     */
    function equals(SketchDateTime $date_time) {
        return ($this->toUnixTimestamp() == $date_time->toUnixTimestamp());
    }
}