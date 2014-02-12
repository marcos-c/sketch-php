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

require_once 'Sketch/Object/View.php';

/**
 * SketchObjectList
 */
abstract class SketchObjectList extends SketchObjectView {
    /** @var string */
    private $orderBy;

    /** @var integer */
    private $limit;

    /** @var integer */
    private $offset;

    /**
     * Get order by
     *
     * @return string
     */
    final function getOrderBy() {
        if ($this->orderBy == null && $this->getUseSessionObject()) {
            $this->orderBy = $this->getSessionObjectAttribute('order_by', $this->orderBy);
        }
        return $this->orderBy;
    }

    /**
     * Set order by
     *
     * @param $order_by
     * @return void
     */
    final function setOrderBy($order_by) {
        $this->orderBy = $order_by;
        if ($this->getUseSessionObject()) {
            $this->setSessionObjectAttribute('order_by', $order_by);
        }
    }

    /**
     * Set default order by
     *
     * @param $order_by
     * @return void
     */
    final function setDefaultOrderBy($order_by) {
        if ($this->getOrderBy() == null) {
            $this->setOrderBy($order_by);
        }
    }

    /**
     * Set order by action
     *
     * @param SketchFormView $form
     * @param null|array $parameters
     * @return bool
     */
    final function setOrderByAction(SketchFormView $form, $parameters = null) {
        $connection = $this->getConnection();
        if (is_array($parameters) && array_key_exists('order_by', $parameters)) {
            $form->setFieldValue('orderBy', $connection->escapeString($parameters['order_by']));
        } elseif (is_string($parameters)) {
            $form->setFieldValue('orderBy', $connection->escapeString($parameters));
        }
        return true;
    }

    /**
     * Shorter alias for set order by action
     *
     * @param SketchFormList $form
     * @param $parameters
     * @return bool
     */
    final function orderBy(SketchFormList $form, $parameters) {
        return $this->setOrderByAction($form, $parameters);
    }

    abstract function getSize();

    /**
     * Get limit
     *
     * @param bool $default
     * @return bool|int
     */
    final function getLimit($default = false) {
        if ($this->limit == null && $this->getUseSessionObject()) {
            $this->limit = $this->getSessionObjectAttribute('limit', $this->limit);
        }
        return ($this->limit > 0) ? $this->limit : $default;
    }

    /**
     * Set limit
     *
     * @param $limit
     * @return void
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
     * Set default limit
     *
     * @param $limit
     * @return void
     */
    final function setDefaultLimit($limit) {
        if ($this->getLimit() == 0) {
            $this->setLimit($limit);
        }
    }

    /**
     * Get offset
     *
     * @param bool $default
     * @return bool|int
     */
    final function getOffset($default = false) {
        if ($this->offset == null && $this->getUseSessionObject()) {
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
     * Set offset
     *
     * @param $offset
     * @return void
     */
    final function setOffset($offset) {
        $this->offset = $offset;
        if ($this->getUseSessionObject()) {
            $this->setSessionObjectAttribute('offset', $offset);
        }
    }

    /**
     * Set offset action
     *
     * @param SketchFormList $form
     * @param $parameters
     * @return bool
     */
    final function setOffsetAction(SketchFormList $form, $parameters) {
        if (is_array($parameters) && array_key_exists('offset', $parameters)) {
            $form->setFieldValue('offset', intval($parameters['offset']));
        } elseif (is_string($parameters)) {
            $form->setFieldValue('offset', intval($parameters));
        } elseif (is_int($parameters)) {
            $form->setFieldValue('offset', $parameters);
        }
        return true;
    }

    /**
     * Shorter alias for set order by action
     *
     * @param SketchFormList $form
     * @param $parameters
     * @return bool
     */
    final function offset(SketchFormList $form, $parameters) {
        return $this->setOffsetAction($form, $parameters);
    }

    /**
     * Clear offset action
     *
     * @param SketchFormView $form
     * @return bool
     */
    final function clearOffsetAction(SketchFormView $form) {
        $form->setFieldValue('offset', 0);
        return true;
    }
}
