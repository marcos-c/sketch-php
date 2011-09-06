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

require_once 'Sketch/Form/View.php';

/**
 * SketchFormList
 *
 * @package Sketch
 */
class SketchFormList extends SketchFormView {
    function requiresPager($size) {
        $limit = $this->instance->getLimit();
        return ($limit != 0 && $size > $limit);
    }

    function getPager($size, $parameters = null) {
        $form_name = $this->getFormName();
        $limit = $this->getInstance()->getLimit();
        $current_offset = $this->getInstance()->getOffset();
        $last_page = ceil($size / $limit);
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
        $default_parameters = array(
            'pager-option' => 'pager-option',
            'pager-option-selected' => 'pager-option-selected',
            'pager-option-first-label' => 'First',
            'pager-option-first' => 'pager-option-first',
            'pager-option-previous-label' => 'Previous',
            'pager-option-previous' => 'pager-option-previous',
            'pager-option-next-label' => 'Next',
            'pager-option-next' => 'pager-option-next',
            'pager-option-last-label' => 'Next',
            'pager-option-last' => 'pager-option-last',
            'pager-option-size' => 'pager-option-size'
        );
        $parameters = (is_array($parameters)) ? array_merge($default_parameters, $parameters) : $default_parameters;
        $first = ($parameters['pager-option-first'] !== false && $from > 1) ? $this->commandLink(new SketchFormCommand('offset', 0), null, $parameters['pager-option-first-label'], 'class="'.$parameters['pager-option-first'].'"') : '';
        $previous = ($parameters['pager-option-previous'] !== false && $current_page > 1) ? $this->commandLink(new SketchFormCommand('offset', $current_offset - $limit), null, $parameters['pager-option-previous-label'], 'class="'.$parameters['pager-option-previous'].'"') : '';
        $of_size = ($parameters['pager-option-size'] !== false) ? "<span class=\"".$parameters['pager-option-size']."\">of $last_page</span>" : '';
        $next = ($parameters['pager-option-next'] !== false && $current_page < $last_page) ? $this->commandLink(new SketchFormCommand('offset', $current_offset + $limit), null, $parameters['pager-option-next-label'], 'class="'.$parameters['pager-option-next'].'"') : '';
        $last = ($parameters['pager-option-last'] !== false && $last_page > 10 && $current_page < ($last_page - 5)) ? $this->commandLink(new SketchFormCommand('offset', ($last_page - 1) * $limit), null, $parameters['pager-option-last-label'], 'class="'.$parameters['pager-option-last'].'"') : '';
        $o = array(); for ($i = $from; $i <= $to; $i++) {
            $o[] = (($offset = ($i - 1) * $limit) == $current_offset) ? '<span class="'.$parameters['pager-option-selected'].'">'.$i.'</span>' : $this->commandLink(new SketchFormCommand('offset', $offset), null, '<span class="'.$parameters['pager-option'].'">'.$i.'</span>');
        } return trim(implode(' ', array('first' => $first, 'previous' => $previous) + $o + array('size' => $of_size, 'next' => $next, 'last' => $last)));
    }

    function getLimitedPager() {
        $form_name = $this->getFormName();
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
        $first = ($from > 1) ? $this->commandLink(new SketchFormCommand('offset', 0), null, 'First') : null;
        $previous = ($current_page > 1) ? $this->commandLink(new SketchFormCommand('offset', $current_offset - $limit), null, 'Previous') : null;
        $of_size = "<span class=\"pager-option size\">of $last_page</span>";
        $next = ($current_page < $last_page) ? $this->commandLink(new SketchFormCommand('offset', $current_offset + $limit), null, 'Next') : null;
        $last = ($last_page > 10 && $current_page < ($last_page - 5)) ? $this->commandLink(new SketchFormCommand('offset', ($last_page - 1) * $limit), null, 'Last') : null;
        $o = array(); for ($i = $from; $i <= $to; $i++) {
            $o[] = (($offset = ($i - 1) * $limit) == $current_offset) ? '<span class="pager-option selected">'.$i.'</span>' : $this->commandLink(new SketchFormCommand('offset', $offset), null, '<span class="pager-option">'.$i.'</span>');
        } return trim(implode(' ', array('first' => $first, 'previous' => $previous) + $o + array('size' => $of_size, 'next' => $next, 'last' => $last, 'limited' => $limited)));
    }

    function getRangedPager() {
        $form_name = $this->getFormName();
        $size = $this->instance->getSize();
        $limit = $this->instance->getLimit();
        $offset = $this->instance->getOffset();
        $o = array(); for ($i = 0; $i < $size; $i += $limit) {
            $j = ((($j = $i + $limit) < $size) ? $j : $size) - 1;
            $o[$i] = ($i == $offset) ? '[ '.($i + 1).'-'.($j + 1).' ]' : '[ '.$this->commandLink(new SketchFormCommand('offset', $i), null, ($i + 1).'-'.($j + 1)).' ]';
        } return implode(' ', $o);
    }
}