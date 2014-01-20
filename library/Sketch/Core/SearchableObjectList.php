<?php
/**
 * This file is part of the Sketch library
 *
 * @author Marcos Cooper <marcos@releasepad.com>
 * @version 3.0
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

namespace Sketch\Core;

/**
 * Core searchable object list definition
 *
 * @package Sketch\Core
 */
abstract class SearchableObjectList extends ObjectList {
    /**
     * @var string
     */
    private $searchText;

    /**
     * @var array
     */
    private $searchFilters;

    /**
     * @return string
     */
    function getSearchText() {
        if ($this->searchText == null && $this->getUseSessionObject()) {
            $this->searchText = $this->getSessionObjectAttribute('search_text', $this->searchText);
        }
        return $this->searchText;
    }

    /**
     * @param string $search_text
     */
    function setSearchText($search_text) {
        $this->searchText = $search_text;
        if ($this->getUseSessionObject()) {
            $this->setSessionObjectAttribute('search_text', $search_text);
        }
    }

    /**
     * @return array
     */
    function getSearchFilters() {
        if ($this->searchFilters == null && $this->getUseSessionObject()) {
            $this->searchFilters = $this->getSessionObjectAttribute('search_filters', $this->searchFilters);
        }
        return $this->searchFilters;
    }

    /**
     * @param array $search_filters
     * @return void
     */
    function setSearchFilters(array $search_filters) {
        $this->searchFilters = $search_filters;
        if ($this->getUseSessionObject()) {
            $this->setSessionObjectAttribute('search_filters', $search_filters);
        }
    }
}