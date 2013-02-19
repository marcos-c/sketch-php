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

class DateTimeIterator extends Object implements Iterator {
    /**
     * @var DateTime
     */
    private $fromDate;

    /**
     * @var DateTime
     */
    private $toDate;

    /**
     * @var DateTime
     */
    private $currentDate;

    /**
     * @param DateTime $from_date
     * @param DateTime $to_date
     */
    function __construct(DateTime $from_date, DateTime $to_date) {
        $this->setFromDate($from_date);
        $this->setCurrentDate($from_date);
        $this->setToDate($to_date);
    }

    /**
     * @param DateTime $fromDate
     */
    public function setFromDate($fromDate) {
        $this->fromDate = $fromDate;
    }

    /**
     * @return DateTime
     */
    public function getFromDate() {
        return $this->fromDate;
    }

    /**
     * @param DateTime $toDate
     */
    public function setToDate($toDate) {
        $this->toDate = $toDate;
    }

    /**
     * @return DateTime
     */
    public function getToDate() {
        return $this->toDate;
    }

    /**
     * @param DateTime $currentDate
     */
    public function setCurrentDate($currentDate) {
        $this->currentDate = $currentDate;
    }

    /**
     * @return DateTime
     */
    public function getCurrentDate() {
        return $this->currentDate;
    }

    /**
     * @return DateTime
     */
    public function current() {
        return $this->getCurrentDate();
    }

    public function next() {
        $this->setCurrentDate($this->getCurrentDate()->addInterval('1 day'));
    }

    /**
     * @return DateTime
     */
    public function key() {
        return $this->getCurrentDate();
    }

    /**
     * @return boolean
     */
    public function valid() {
        return !$this->getCurrentDate()->greater($this->getToDate());
    }

    public function rewind() {
        $this->setCurrentDate($this->getFromDate());
    }
}