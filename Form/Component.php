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

abstract class SketchFormComponent extends SketchObject {
    /**
     *
     * @var SketchFormView
     */
    private $form = null;

    /**
     *
     * @var array
     */
    private $arguments = null;

    /**
     *
     * @param SketchFormView $form
     * @param array $arguments
     */
    function __construct(SketchFormView $form, $arguments) {
        $this->setForm($form);
        $this->setArguments($arguments);
    }

    
    abstract function saveHTML();

    /**
     *
     * @return SketchFormView
     */
    function getForm() {
        return $this->form;
    }

    /**
     *
     * @param SketchFormView $form 
     */
    protected function setForm(SketchFormView $form) {
        $this->form = $form;
    }

    /**
     *
     * @return array
     */
    protected function getArguments() {
        return $this->arguments;
    }

    /**
     *
     * @param array $arguments 
     */
    protected function setArguments(array $arguments) {
        $this->arguments = $arguments;
    }
}