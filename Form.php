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


class SketchForm extends SketchObject {
    /**
     * @var array
     */
    static private $components = array();

    /**
     * @param string $form_name
     * @return array
     */
    static function getComponents($form_name) {
        if (array_key_exists($form_name, self::$components)) {
            return self::$components[$form_name];
        } else return false;
    }

    /**
     * @param string $form_name
     * @param array $components
     */
    static function setComponents($form_name, array $components) {
        self::$components[$form_name] = $components;
    }

    /**
     * @param string $form_name
     * @param SketchFormComponent $component
     */
    static function addComponent($form_name, SketchFormComponent $component) {
        self::$components[$form_name][] = $component;
    }

    /**
     * @return SketchFormCommand
     */
    static function Command() {
        $a = func_get_args();
        $cp = ($a[0] != null) ? $a[0] : 'null';
        $parameters = array_slice($a, 1);
        for ($i = 0; $i < count($parameters); $i++) {
            $cp .= ', $parameters['.$i.']';
        }
        return eval("return new SketchFormCommand($cp);");
    }

    /**
     * @return SketchFormCommandPropagate
     */
    static function Propagate() {
        $a = func_get_args();
        $cp = ($a[0] != null) ? $a[0] : 'null';
        $parameters = array_slice($a, 1);
        for ($i = 0; $i < count($parameters); $i++) {
            $cp .= ', $parameters['.$i.']';
        }
        return eval("return new SketchFormCommandPropagate($cp);");
    }

    /**
     * @param SketchObjectView $data_object
     * @param null $view_id
     * @throws Exception
     * @return mixed
     */
    static function Factory(SketchObjectView $data_object, $view_id = null) {
        $application = SketchApplication::getInstance();
        $translator = $application->getLocale()->getTranslator();
        // Check that the data object view has a valid id
        if ($view_id != null) {
            $data_object->setViewId($view_id);
        } elseif (!$data_object->getViewId()) {
            // If the data object doesn't have a valid id then generate one
            $backtrace = debug_backtrace();
            $trace = array_shift($backtrace);
            if (array_key_exists('class', $trace)) {
                if ($trace['class'] == 'SketchForm' && $trace['function'] == 'Factory') {
                    $data_object->setViewId(md5(serialize($trace)));
                } else throw new Exception(sprintf($translator->_('Can\'t generate a valid view id for class %s'), get_class($data_object)));
            } else throw new Exception(sprintf($translator->_('Can\'t generate a valid view id for class %s'), get_class($data_object)));
        }
        // Use a substr of the view name (md5) as form name
        $form_name = '__'.substr($data_object -> getViewName(), 0, 8);
        // Allow session based attributes
        $data_object->setUseSessionObject(true);
        // Factory a sketch form object
        if ($data_object instanceof SketchObjectList) {
            return new SketchFormList(clone($data_object), $form_name);
        } else {
            return new SketchFormView(clone($data_object), $form_name);
        }
    }
}