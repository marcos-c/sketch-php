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

require_once 'Sketch/Object.php';

/**
 * SketchDateTimeIterator
 */
class SketchDateTimeIterator extends SketchObject implements Iterator {
    /**
     * @var SketchDateTime
     */
    private $fromDate;

    /**
     * @var SketchDateTime
     */
    private $toDate;

    /**
     * @var SketchDateTime
     */
    private $currentDate;

    /**
     * @param SketchDateTime $from_date
     * @param SketchDateTime $to_date
     */
    function __construct(SketchDateTime $from_date, SketchDateTime $to_date) {
        $this->setFromDate($from_date);
        $this->setCurrentDate($from_date);
        $this->setToDate($to_date);
    }

    /**
     * @param \SketchDateTime $fromDate
     */
    public function setFromDate($fromDate) {
        $this->fromDate = $fromDate;
    }

    /**
     * @return \SketchDateTime
     */
    public function getFromDate() {
        return $this->fromDate;
    }

    /**
     * @param \SketchDateTime $toDate
     */
    public function setToDate($toDate) {
        $this->toDate = $toDate;
    }

    /**
     * @return \SketchDateTime
     */
    public function getToDate() {
        return $this->toDate;
    }

    /**
     * @param \SketchDateTime $currentDate
     */
    public function setCurrentDate($currentDate) {
        $this->currentDate = $currentDate;
    }

    /**
     * @return \SketchDateTime
     */
    public function getCurrentDate() {
        return $this->currentDate;
    }

    /**
     * @return \SketchDateTime
     */
    public function current() {
        return $this->getCurrentDate();
    }

    public function next() {
        $this->setCurrentDate($this->getCurrentDate()->addInterval('1 day'));
    }

    /**
     * @return SketchDateTime
     */
    public function key() {
        return $this->getCurrentDate();
    }

    /**
     * @return bool
     */
    public function valid() {
        return !$this->getCurrentDate()->greater($this->getToDate());
    }

    public function rewind() {
        $this->setCurrentDate($this->getFromDate());
    }
}