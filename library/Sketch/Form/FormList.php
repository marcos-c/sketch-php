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

namespace Sketch\Form;

/**
 * Form list class
 *
 * @package Sketch\Form
 */
class FormList extends FormView {
    function requiresPager($size) {
        $limit = $this->instance->getLimit();
        return ($limit != 0 && $size > $limit);
    }

    function getPager($size, $parameters = null) {
        $limit = $this->getInstance()->getLimit();
        $current_offset = $this->getInstance()->getOffset();
        if ($limit > 0) {
            $last_page = ceil($size / $limit);
            $current_page = intval($current_offset / $limit) + 1;
        } else {
            $last_page = 1;
            $current_page = 1;
        }
        if ($current_page > 5 && $current_page < ($last_page - 5)) {
            $from = $current_page - 5;
            $to = $current_page + 5;
        } else if ($current_page <= 5) {
            $from = 1;
            $to = (($to = $from + 9) < $last_page) ? $to : $last_page;
        } else if ($current_page >= ($last_page - 5)) {
            $to = $last_page;
            $from = (($from = $to - 9) > 1) ? $from : 1;
        }
        $translator = $this->getTranslator();
        $parameters = $this->extend(array(
            'show' => 'first,previous,pages,of_pages,next,last,page_size',
            'page_size_label' => $translator->_s('showing %s per page'),
            'first-label' => $translator->_s('First'),
            'previous-label' => $translator->_s('Previous'),
            'next-label' => $translator->_s('Next'),
            'last-label' => $translator->_s('Next'),
            'selected' => array('id' => null, 'class' => 'selected', 'style' => null),
            'pager' => array('id' => null, 'class' => 'pager', 'style' => null),
            'first' => array('id' => null, 'class' => 'first', 'style' => null),
            'previous' => array('id' => null, 'class' => 'previous', 'style' => null),
            'next' => array('id' => null, 'class' => 'next', 'style' => null),
            'last' => array('id' => null, 'class' => 'last', 'style' => null),
            'size' => array('id' => null, 'class' => 'size', 'style' => null)
        ), $parameters);
        $show = array_map('trim', explode(',', $parameters['show']));
        $current = in_array('current', $show) ? '<li>'.intval($current_offset + 1).' - '.(($current_page == $last_page) ? $size : intval($current_offset + $limit)).'</li>' : '';
        $of_rows = in_array('of_rows', $show) ? '<li'.$parameters['rows'].'>'.sprintf($translator->_s('of %s'), $size).'</li>' : '';
        $pages = array();
        for ($i = $from; $i <= $to; $i++) {
            $pages[] = (($offset = ($i - 1) * $limit) == $current_offset) ? '<li'.$parameters['selected'].'>'.$i.'</li>' : '<li>'.$this->commandLink(new FormCommand('offset', $offset), null, '<span>'.$i.'</span>').'</li>';
        }
        $of_pages = (in_array('of_pages', $show)) ? "<li".$parameters['size']."\">of $last_page</li>" : '';
        $first = (in_array('first', $show) && $from > 1) ? '<li'.$parameters['first'].'>'.$this->commandLink(new FormCommand('offset', 0), null, $parameters['first-label']).'</li>' : '';
        $previous = (in_array('previous', $show) && $current_page > 1) ? '<li'.$parameters['previous'].'>'.$this->commandLink(new FormCommand('offset', $current_offset - $limit), null, $parameters['previous-label']).'</li>' : '';
        $next = (in_array('next', $show) !== false && $current_page < $last_page) ? '<li'.$parameters['next'].'>'.$this->commandLink(new FormCommand('offset', $current_offset + $limit), null, $parameters['next-label']).'</li>' : '';
        $last = (in_array('last', $show) && $last_page > 10 && $current_page < ($last_page - 5)) ? '<li'.$parameters['last'].'>'.$this->commandLink(new FormCommand('offset', ($last_page - 1) * $limit), null, $parameters['last-label']).'</li>' : '';
        $page_size = (in_array('page_size', $show) && $size > 10) ? '<li'.$parameters['page_size'].'>'.sprintf($parameters['page_size_label'], $this->selectOne(array(10 => 10, 25 => 25, 50 => 50), 'limit', 'onchange="'.$this->command(new FormCommandPropagate(), null).'"')).'</li>' : '';
        $output = array();
        foreach ($show as $key) {
            switch ($key) {
                case 'current': $output[$key] = $current; break;
                case 'of_rows': $output[$key] = $of_rows; break;
                case 'pages': $output[$key] = implode(' ', $pages); break;
                case 'of_pages': $output[$key] = $of_pages; break;
                case 'first': $output[$key] = $first; break;
                case 'previous': $output[$key] = $previous; break;
                case 'next': $output[$key] = $next; break;
                case 'last': $output[$key] = $last; break;
                case 'page_size': $output[$key] = $page_size; break;
            }
        }
        return '<ul'.$parameters['pager'].'>'.trim(implode(' ', $output)).'</ul>';
    }

    function getLimitedPager() {
        $size = $this->getInstance()->getSize();
        $limit = $this->getInstance()->getLimit();
        $current_offset = $this->getInstance()->getOffset();
        $last_page = intval($size / $limit) + 1;
        $limited = false; if ($size > 100) {
            $limited = "<br /><div style=\"padding-top: 4px;\"><span class=\"pager-option size\">Too many search results, please limit your search</span></div>";
            $last_page = 100 / $limit;
        }
        $current_page = intval($current_offset / $limit) + 1;
        if ($current_page > 5 && $current_page < ($last_page - 5)) {
            $from = $current_page - 5;
            $to = $current_page + 5;
        } else if ($current_page <= 5) {
            $from = 1;
            $to = (($to = $from + 9) < $last_page) ? $to : $last_page;
        } else if ($current_page >= ($last_page - 5)) {
            $to = $last_page;
            $from = (($from = $to - 9) > 1) ? $from : 1;
        }
        $first = ($from > 1) ? $this->commandLink(new FormCommand('offset', 0), null, 'First') : null;
        $previous = ($current_page > 1) ? $this->commandLink(new FormCommand('offset', $current_offset - $limit), null, 'Previous') : null;
        $of_size = "<span class=\"pager-option size\">of $last_page</span>";
        $next = ($current_page < $last_page) ? $this->commandLink(new FormCommand('offset', $current_offset + $limit), null, 'Next') : null;
        $last = ($last_page > 10 && $current_page < ($last_page - 5)) ? $this->commandLink(new FormCommand('offset', ($last_page - 1) * $limit), null, 'Last') : null;
        $o = array(); for ($i = $from; $i <= $to; $i++) {
            $o[] = (($offset = ($i - 1) * $limit) == $current_offset) ? '<span class="pager-option selected">'.$i.'</span>' : $this->commandLink(new FormCommand('offset', $offset), null, '<span class="pager-option">'.$i.'</span>');
        } return trim(implode(' ', array('first' => $first, 'previous' => $previous) + $o + array('size' => $of_size, 'next' => $next, 'last' => $last, 'limited' => $limited)));
    }

    function getRangedPager() {
        $size = $this->instance->getSize();
        $limit = $this->instance->getLimit();
        $offset = $this->instance->getOffset();
        $o = array(); for ($i = 0; $i < $size; $i += $limit) {
            $j = ((($j = $i + $limit) < $size) ? $j : $size) - 1;
            $o[$i] = ($i == $offset) ? '[ '.($i + 1).'-'.($j + 1).' ]' : '[ '.$this->commandLink(new FormCommand('offset', $i), null, ($i + 1).'-'.($j + 1)).' ]';
        } return implode(' ', $o);
    }

    function getCurrentPage($size) {
        $limit = $this->getInstance()->getLimit();
        $current_offset = $this->getInstance()->getOffset();
        if ($limit > 0) {
            $last_page = ceil($size / $limit);
            $current_page = intval($current_offset / $limit) + 1;
        } else {
            $last_page = 1;
            $current_page = 1;
        }
        if ($current_page > 5 && $current_page < ($last_page - 5)) {
            $from = $current_page - 5;
            $to = $current_page + 5;
        } else if ($current_page <= 5) {
            $from = 1;
            $to = (($to = $from + 9) < $last_page) ? $to : $last_page;
        } else if ($current_page >= ($last_page - 5)) {
            $to = $last_page;
            $from = (($from = $to - 9) > 1) ? $from : 1;
        }
        $translator = $this->getTranslator();
        return sprintf($translator->_s('Page %d of %d'), $current_page, $last_page);
    }

    function getBootstrapPager($size, $parameters = null) {
        $limit = $this->getInstance()->getLimit();
        $current_offset = $this->getInstance()->getOffset();
        if ($limit > 0) {
            $last_page = ceil($size / $limit);
            $current_page = intval($current_offset / $limit) + 1;
        } else {
            $last_page = 1;
            $current_page = 1;
        }
        if ($current_page > 5 && $current_page < ($last_page - 5)) {
            $from = $current_page - 5;
            $to = $current_page + 5;
        } else if ($current_page <= 5) {
            $from = 1;
            $to = (($to = $from + 9) < $last_page) ? $to : $last_page;
        } else if ($current_page >= ($last_page - 5)) {
            $to = $last_page;
            $from = (($from = $to - 9) > 1) ? $from : 1;
        }
        $translator = $this->getTranslator();
        $parameters = $this->extend(array(
            'show' => 'first,previous,pages,next,last',
            'page_size_label' => $translator->_s('showing %s per page'),
            'first-label' => $translator->_s('First'),
            'previous-label' => $translator->_s('Previous'),
            'next-label' => $translator->_s('Next'),
            'last-label' => $translator->_s('Next'),
            'selected' => array('id' => null, 'class' => 'active', 'style' => null),
            'pager' => array('id' => null, 'class' => 'pager', 'style' => null),
            'first' => array('id' => null, 'class' => 'first', 'style' => null),
            'previous' => array('id' => null, 'class' => 'previous', 'style' => null),
            'next' => array('id' => null, 'class' => 'next', 'style' => null),
            'last' => array('id' => null, 'class' => 'last', 'style' => null),
            'size' => array('id' => null, 'class' => 'size', 'style' => null)
        ), $parameters);
        $show = array_map('trim', explode(',', $parameters['show']));
        $current = in_array('current', $show) ? '<li>'.intval($current_offset + 1).' - '.(($current_page == $last_page) ? $size : intval($current_offset + $limit)).'</li>' : '';
        $of_rows = in_array('of_rows', $show) ? '<li'.$parameters['rows'].'>'.sprintf($translator->_s('of %s'), $size).'</li>' : '';
        $pages = array();
        for ($i = $from; $i <= $to; $i++) {
            $pages[] = (($offset = ($i - 1) * $limit) == $current_offset) ? '<li'.$parameters['selected'].'><span>'.$i.'</span></li>' : '<li>'.$this->commandLink(new FormCommand('offset', $offset), null, $i).'</li>';
        }
        $of_pages = (in_array('of_pages', $show)) ? "<li".$parameters['size']."\">of $last_page</li>" : '';
        $first = (in_array('first', $show) && $from > 1) ? '<li'.$parameters['first'].'>'.$this->commandLink(new FormCommand('offset', 0), null, $parameters['first-label']).'</li>' : '';
        $previous = (in_array('previous', $show) && $current_page > 1) ? '<li'.$parameters['previous'].'>'.$this->commandLink(new FormCommand('offset', $current_offset - $limit), null, $parameters['previous-label']).'</li>' : '';
        $next = (in_array('next', $show) !== false && $current_page < $last_page) ? '<li'.$parameters['next'].'>'.$this->commandLink(new FormCommand('offset', $current_offset + $limit), null, $parameters['next-label']).'</li>' : '';
        $last = (in_array('last', $show) && $last_page > 10 && $current_page < ($last_page - 5)) ? '<li'.$parameters['last'].'>'.$this->commandLink(new FormCommand('offset', ($last_page - 1) * $limit), null, $parameters['last-label']).'</li>' : '';
        $page_size = (in_array('page_size', $show) && $size > 10) ? '<li'.$parameters['page_size'].'>'.sprintf($parameters['page_size_label'], $this->selectOne(array(10 => 10, 25 => 25, 50 => 50), 'limit', 'onchange="'.$this->command(new FormCommandPropagate(), null).'"')).'</li>' : '';
        $output = array();
        foreach ($show as $key) {
            switch ($key) {
                case 'current': $output[$key] = $current; break;
                case 'of_rows': $output[$key] = $of_rows; break;
                case 'pages': $output[$key] = implode(' ', $pages); break;
                case 'of_pages': $output[$key] = $of_pages; break;
                case 'first': $output[$key] = $first; break;
                case 'previous': $output[$key] = $previous; break;
                case 'next': $output[$key] = $next; break;
                case 'last': $output[$key] = $last; break;
                case 'page_size': $output[$key] = $page_size; break;
            }
        }
        return '<ul'.$parameters['pager'].'>'.trim(implode(' ', $output)).'</ul>';
    }
}