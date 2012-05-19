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
        $translator = $this->getTranslator();
        $parameters = $this->extend(array(
            'show-first' => false,
            'show-previous' => true,
            'show-next' => true,
            'show-last' => false,
            'show-size' => false,
            'first-label' => $translator->_('First'),
            'previous-label' => $translator->_('Previous'),
            'next-label' => $translator->_('Next'),
            'last-label' => $translator->_('Next'),
            'selected' => array('id' => null, 'class' => 'selected', 'style' => null),
            'pager' => array('id' => null, 'class' => 'pager', 'style' => null),
            'first' => array('id' => null, 'class' => 'first', 'style' => null),
            'previous' => array('id' => null, 'class' => 'previous', 'style' => null),
            'next' => array('id' => null, 'class' => 'next', 'style' => null),
            'last' => array('id' => null, 'class' => 'last', 'style' => null),
            'size' => array('id' => null, 'class' => 'size', 'style' => null)
        ), $parameters);
        $first = ($parameters['show-first'] && $from > 1) ? '<li'.$parameters['first'].'>'.$this->commandLink(new SketchFormCommand('offset', 0), null, $parameters['first-label']).'</li>' : '';
        $previous = ($parameters['show-previous'] && $current_page > 1) ? '<li'.$parameters['previous'].'>'.$this->commandLink(new SketchFormCommand('offset', $current_offset - $limit), null, $parameters['previous-label']).'</li>' : '';
        $size = ($parameters['show-size']) ? "<li".$parameters['size']."\">of $last_page</li>" : '';
        $next = ($parameters['show-next'] !== false && $current_page < $last_page) ? '<li'.$parameters['next'].'>'.$this->commandLink(new SketchFormCommand('offset', $current_offset + $limit), null, $parameters['next-label']).'</li>' : '';
        $last = ($parameters['show-last'] && $last_page > 10 && $current_page < ($last_page - 5)) ? '<li'.$parameters['last'].'>'.$this->commandLink(new SketchFormCommand('offset', ($last_page - 1) * $limit), null, $parameters['last-label']).'</li>' : '';
        $o = array();
        for ($i = $from; $i <= $to; $i++) {
            $o[] = (($offset = ($i - 1) * $limit) == $current_offset) ? '<li'.$parameters['selected'].'>'.$i.'</li>' : '<li>'.$this->commandLink(new SketchFormCommand('offset', $offset), null, $i).'</li>';
        }
        return '<ul'.$parameters['pager'].'>'.trim(implode(' ', array('first' => $first, 'previous' => $previous) + $o + array('size' => $size, 'next' => $next, 'last' => $last))).'</ul>';
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