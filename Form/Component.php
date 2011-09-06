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
 * SketchFormComponent
 *
 * @package Sketch
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
    final function __construct(SketchFormView $form, $arguments) {
        $this->setForm($form);
        $this->setArguments($arguments);
    }

    
    abstract function saveHTML();

    /**
     *
     * @return SketchFormView
     */
    final protected function getForm() {
        return $this->form;
    }

    /**
     *
     * @param SketchFormView $form 
     */
    final protected function setForm(SketchFormView $form) {
        $this->form = $form;
    }

    /**
     *
     * @return array
     */
    final protected function getArguments() {
        return $this->arguments;
    }

    /**
     *
     * @param array $arguments 
     */
    final protected function setArguments(array $arguments) {
        $this->arguments = $arguments;
    }

    /**
     *
     * @param array
     * @return array
     */
    final protected function extend() {
        $o = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            $t = func_get_arg($i);
            if (is_array($t)) {
                foreach ($t as $k1 => $v1) {
                    if (is_array($v1)) {
                        foreach ($v1 as $k2 => $v2) {
                            $o[$k1][$k2] = $v2;
                        }
                    } else {
                        $o[$k1] = $v1;
                    }
                }
            }
        }
        foreach ($o as $key => $value) {
            if (is_array($value)) {
                $final_value = "";
                foreach ($value as $key2 => $value2) {
                    if ($value2 != null) {
                        $final_value .= " $key2=\"$value2\"";
                    }
                }
                $o[$key] = $final_value;
            }
        }
        return $o;
    }
}