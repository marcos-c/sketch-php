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

require_once 'Sketch/Object/View.php';

/**
 * SketchObjectList
 *
 * @package Sketch
 */
abstract class SketchObjectList extends SketchObjectView {
    /**
     *
     * @var string
     */
    private $orderBy;

    /**
     *
     * @var integer
     */
    private $limit;

    /**
     *
     * @var integer
     */
    private $offset;

    /**
     *
     * @param SketchFormList $form
     * @param string $parameters
     * @return boolean
     */
    final function orderBy(SketchFormList $form, $parameters) {
        $connection = $this->getConnection();
        $this->setOrderBy($connection->escapeString($parameters));
        return true;
    }

    /**
     *
     * @return string
     */
    final function getOrderBy() {
        if ($this->getUseSessionObject()) {
            $this->orderBy = $this->getSessionObjectAttribute('order_by', $this->orderBy);
        }
        return $this->orderBy;
    }

    /**
     *
     * @param <type> string
     */
    final function setOrderBy($order_by) {
        $this->orderBy = $order_by;
        if ($this->getUseSessionObject()) {
            $this->setSessionObjectAttribute('order_by', $order_by);
        }
    }

    /**
     *
     * @param SketchFormView $form
     * @param mixed $parameters
     * @return boolean
     */
    final function setOrderByAction(SketchFormView $form, $parameters = null) {
        if (is_array($parameters)) {
            if (array_key_exists('order_by', $parameters)) {
                $form->setFieldValue('orderBy', $parameters['order_by']);
            }
        }
        return true;
    }

    /**
     *
     * @param string $order_by
     */
    final function setDefaultOrderBy($order_by) {
        if ($this->getOrderBy() == null) {
            $this->setOrderBy($order_by);
        }
    }

    abstract function getSize();

    /**
     *
     * @return integer
     */
    final function getLimit($default = false) {
        if ($this->getUseSessionObject()) {
            $this->limit = $this->getSessionObjectAttribute('limit', $this->limit);
        }
        return ($this->limit > 0) ? $this->limit : $default;
    }

    /**
     *
     * @param integer $limit
     */
    final function setLimit($limit) {
        $this->limit = $limit;
        if ($this->getUseSessionObject()) {
            $session = $this->getSession();
            $data = $session->getAttribute('__list');
            $t1 = is_array($data) && array_key_exists($this->getViewName(), $data);
            $t2 = $t1 && array_key_exists('limit', $data[$this->getViewName()]);
            if (!$t2 || $limit != $data[$this->getViewName()]['limit']) {
                $data[$this->getViewName()]['limit'] = $limit;
                $session->setAttribute('__list', $data);
                $this->setOffset(0);
            }
        }
    }

    /**
     *
     * @param integer $limit
     */
    final function setDefaultLimit($limit) {
        if ($this->getLimit() == 0) {
            $this->setLimit($limit);
        }
    }

    /**
     *
     * @param SketchFormList $form
     * @param integer $parameters
     * @return integer
     */
    final function offset(SketchFormList $form, $parameters) {
        $this->setOffset(intval($parameters));
        return true;
    }

    /**
     *
     * @return integer
     */
    final function getOffset($default = false) {
        if ($this->getUseSessionObject()) {
            $this->offset = $this->getSessionObjectAttribute('offset', $this->offset);
        }
        if ($this->offset >= $this->getSize()) {
            $this->setOffset(0);
            return 0;
        } else {
            return ($this->offset > 0) ? $this->offset : $default;
        }
    }

    /**
     *
     * @param integer $offset
     */
    final function setOffset($offset) {
        $this->offset = $offset;
        if ($this->getUseSessionObject()) {
            $this->setSessionObjectAttribute('offset', $offset);
        }
    }

    /**
     *
     * @param SketchFormView $form
     * @return boolean
     */
    final function clearOffsetAction(SketchFormView $form) {
        $form->setFieldValue('offset', 0);
        return true;
    }
}