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
 * SketchFormCommand
 *
 * @package Sketch
 */
class SketchFormCommand extends SketchObject {
    private $command = null;

    private $parameters = array();

    private $targetForError = false;

    final function __construct() {
        $a = func_get_args();
        if (array_key_exists(0, $a)) {
            if (is_array($a[0])) {
                $this->setCommand($a[0][0]);
                $this->targetForError = $a[0][1];
            } else {
                $this->setCommand($a[0]);
            }
            $this->setParameters(array_slice($a, 1));
        }
    }

    function getCommand() {
        return $this->command;
    }

    function setCommand($command) {
        $this->command = trim($command);
    }

    function getParameters() {
        return $this->parameters;
    }

    function setParameters($parameters) {
        if (is_array($parameters)) {
            for ($i = 0; $i < count($parameters); $i++) {
                if ($parameters[$i] instanceof FormView) throw new Exception($this->getTranslator()->_("Can't pass form as parameters."));
            } $this->parameters = $parameters;
        }
    }

    function getTargetForError() {
        return $this->targetForError;
    }
}
