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

require_once 'Sketch/Object.php';

/**
 * SketchFormCommand
 */
class SketchFormCommand extends SketchObject {
    /** @var null|string */
    private $command = null;

    /** @var array */
    private $parameters = array();

    /** @var bool */
    private $targetForError = false;

    /**
     * Constructor
     *
     * @param null $command
     */
    final function __construct($command = null) {
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

    /**
     * Get command
     *
     * @return null|string
     */
    function getCommand() {
        return $this->command;
    }

    /**
     * Set command
     *
     * @param $command
     * @return void
     */
    function setCommand($command) {
        $this->command = trim($command);
    }

    /**
     * Get parameters
     *
     * @return array
     */
    function getParameters() {
        return $this->parameters;
    }

    /**
     * Set parameters
     *
     * @throws Exception
     * @param $parameters
     * @return void
     */
    function setParameters($parameters) {
        if (is_array($parameters)) {
            for ($i = 0; $i < count($parameters); $i++) {
                if ($parameters[$i] instanceof FormView) throw new Exception($this->getTranslator()->_("Can't pass form as parameters."));
            } $this->parameters = $parameters;
        }
    }

    /**
     * Get target for error
     *
     * @return bool
     */
    function getTargetForError() {
        return $this->targetForError;
    }
}
