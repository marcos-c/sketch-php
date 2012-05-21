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

require_once 'Sketch/Object/List.php';

/**
 * SketchObjectSearchableList
 *
 * @package Sketch
 */
abstract class SketchObjectSearchableList extends SketchObjectList {
    /**
     *
     * @var string
     */
    private $searchText;

    /**
     *
     * @var array 
     */
    private $filters;

    /**
     *
     * @return string
     */
    function getSearchText() {
        if ($this->getUseSessionObject()) {
            $this->searchText = $this->getSessionObjectAttribute('search_text', $this->searchText);
        }
        return $this->searchText;
    }

    /**
     *
     * @param string $search_text
     */
    function setSearchText($search_text) {
        $this->searchText = $search_text;
        if ($this->getUseSessionObject()) {
            $this->setSessionObjectAttribute('search_text', $search_text);
        }
    }

    /**
     *
     * @return array
     */
    function getFilters() {
        return $this->filters;
    }

    /**
     * 
     * @param array $filters
     * @return void
     */
    function setFilters(array $filters) {
        $this->filters = $filters;
    }
}