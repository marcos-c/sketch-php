<?php
/**
 * This file is part of the Sketch Framework
 * (http://code.google.com/p/sketch-framework/)
 *
 * Copyright (C) 2011 Marcos Albaladejo Cooper
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

require_once 'Sketch/Object.php';
require_once 'Sketch/DateTime/Exception.php';

/**
 * SketchDateTime
 */
class SketchDateTime extends SketchObject {
    /** @var \DateTime|null */
    private $dateTime = null;

    /** @var string|null */
    private $dateTimeString = null;

    /** @var array|null */
    private $dateTimeArray = null;

    /**
     * Return the current date and time
     *
     * @static
     * @return SketchDateTime
     */
    static function Now() {
        return new SketchDateTime(time());
    }

    /**
     * Return the current date
     *
     * @static
     * @return SketchDateTime
     */
    static function Today() {
        return new SketchDateTime(self::Now()->toString('Y-m-d'));
    }

    /**
     * Get available time zone identifiers
     *
     * @static
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
     * Constructor
     *
     * @param null $date_time
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
                }
            } else if (preg_match('/^\d+$/', $date_time)) {
                $date_time = date('Y-m-d H:i:s', $date_time);
            }
            if (preg_match('/^(\d{2}):(\d{2})(?::(\d{2}))?$/', $date_time)) {
                $date_time = "1970-01-01 $date_time";
            }
            if (preg_match('/^((?:19|20)\d{2})-(\d{1,2})-(\d{1,2})(?: (\d{2}):(\d{2}))?(?::(\d{2}))?/', $date_time, $matches)) {
                if (checkdate($matches[2], $matches[3], $matches[1])) {
                    $this->dateTime = new DateTime($date_time, new DateTimeZone('GMT'));
                    $this->dateTimeArray = array(
                        $this->dateTime->format('Y'),
                        $this->dateTime->format('n'),
                        $this->dateTime->format('j'),
                        $this->dateTime->format('G'),
                        intval($this->dateTime->format('i')),
                        intval($this->dateTime->format('s')),
                    );
                }
            }
        }
    }

    /**
     * Return the instance as a string
     *
     * @return string
     */
    function __toString() {
        return $this->toString();
    }

    /**
     * Serialize
     *
     * @return array
     */
    public function __sleep(){
        if ($this->dateTime instanceof DateTime) {
            $this->dateTimeString = $this->dateTime->format('c');
        }
        return array('dateTimeString', 'dateTimeArray');
    }

    /**
     * Unserialize
     *
     * @return void
     */
    public function __wakeup() {
        if ($this->dateTimeString != null) {
            $this->dateTime = new DateTime($this->dateTimeString);
        }
    }

    /**
     * Check if the instance is null
     *
     * @return bool
     */
    function isNull() {
        $test = true; if (is_array($this->dateTimeArray)) {
            foreach ($this->dateTimeArray as $value) {
                $test = $test && (intval($value) == 0);
            }
        } return $test;
    }

    /**
     * Check if the instance is valid
     *
     * @return bool
     */
    function isValid() {
        return (!$this->isNull() && $this->dateTime != null);
    }

    /**
     * Check if the instance is greater than another date
     *
     * @param SketchDateTime $date_time
     * @return bool
     */
    function greater(SketchDateTime $date_time) {
        if ($date_time instanceof SketchDateTime) {
            return $this->toUnixTimestamp() > $date_time->toUnixTimestamp();
        } else return false;
    }

    /**
     * Returned the formatted date
     *
     * @param string $format
     * @return null|string
     */
    function toString($format = 'Y-m-d H:i:s T') {
        if ($this->dateTime != null) {
            return $this->dateTime->format($format);
        } else return null;
    }

    /**
     * Return the instance as an array
     *
     * @return array|null
     */
    function toArray() {
        return $this->dateTimeArray;
    }

    /**
     * Get the instance year
     *
     * @return integer
     */
    function getYear() {
        return $this->dateTimeArray[0];
    }

    /**
     * Return the instance month
     *
     * @return integer
     */
    function getMonth() {
        return $this->dateTimeArray[1];
    }

    /**
     * Return the instance day
     *
     * @return integer
     */
    function getDay() {
        return $this->dateTimeArray[2];
    }

    /**
     * Return the instances last day of the month
     *
     * @return null|string
     */
    function getLastDay() {
        return $this->toString('t');
    }

    /**
     * Return the instance as a unix timestamp
     *
     * @return null|string
     */
    function toUnixTimestamp() {
        return $this->toString('U');
    }

    /**
     * Add an interval to the instance
     *
     * @param $interval
     * @return SketchDateTime
     */
    function addInterval($interval) {
        if ($this->dateTime instanceof DateTime) {
            $clone = clone $this->dateTime;
            if ($this->dateTime != null) {
                if (preg_match('/^-?\d+ second(s)?$/', $interval) ||
                    preg_match('/^-?\d+ minute(s)?$/', $interval) ||
                    preg_match('/^-?\d+ hour(s)?$/', $interval) ||
                    preg_match('/^-?\d+ day(s)?$/', $interval) ||
                    preg_match('/^-?\d+ month(s)?$/', $interval) ||
                    preg_match('/^-?\d+ week(s)?$/', $interval) ||
                    preg_match('/^next month$/', $interval) ||
                    preg_match('/^last month$/', $interval)) {
                    $clone->modify($interval);
                }
            }
            return new SketchDateTime($clone->format('Y-m-d H:i:s'));
        } else {
            return new SketchDateTime();
        }
    }

    /**
     * Substract from the instance another date
     *
     * @param SketchDateTime $date_time
     * @return float
     */
    function substract(SketchDateTime $date_time) {
        $from = new SketchDateTime($this->toString('Y-m-d'));
        $to = new SketchDateTime($date_time->toString('Y-m-d'));
        return (($from->toUnixTimestamp() - $to->toUnixTimestamp()) / 86400);
    }

    /**
     * Check if the instance is equal to another date
     *
     * @param SketchDateTime $date_time
     * @return bool
     */
    function equals(SketchDateTime $date_time) {
        return ($this->toUnixTimestamp() == $date_time->toUnixTimestamp());
    }
}