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
require_once 'Sketch/Form/Iterator.php';

/**
 * SketchFormView
 *
 * @package Sketch
 */
class SketchFormView extends SketchObject {
    /**
     *
     * @var boolean
     */
    private static $executeCommand = true;

    /**
     *
     * @var SketchObjectView
     */
    protected $fromInstance;

    /**
     *
     * @var SketchObjectView
     */
    protected $instance;

    /**
     *
     * @var string
     */
    protected $formName;

    /**
     *
     * @var string
     */
    protected $action;

    /**
     *
     * @var boolean
     */
    protected $javascript = false;

    /**
     *
     * @var SketchFormCommand
     */
    protected $defaultCommand = null;

    /**
     *
     * @param string $name
     * @param array $arguments
     * @return string
     */
    function __call($name, $arguments) {
        $split = preg_split('/([A-Z][a-z0-9_.]*)/', $name, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $form_name = $this->getFormName();
        foreach ($split as $key => $value) {
            $split[$key] = ucfirst(strtolower($value));
        }
        require_once 'Sketch/Form/Component/'.implode('', $split).'.php';
        if (class_exists('SketchFormComponent'.$name)) {
            $component = eval('return new SketchFormComponent'.$name.'($this, $arguments);');
            SketchForm::addComponent($form_name, $component);
            return trim($component->saveHTML());
        } else throw new Exception();
    }

    /**
     *
     * @param SketchObjectView $data_view
     * @param string $form_name
     */
    final function __construct(SketchObjectView $data_view, $form_name) {
        $this->fromInstance = clone($data_view);
        $this->instance = $data_view;
        $this->formName = $form_name;
        $this->action = $this->getRequest()->getURI();
        // Update instance before calling action commands
        $this->form = $this->getRequest()->getAttribute($this->getFormName());
        $session = $this->getSession();
        $session_form = $session->getAttribute('__form');
        if (is_array($session_form) && !(is_array($this->form) && array_key_exists('attributes', $this->form) && is_array($this->form['attributes']))) {
            if (array_key_exists($this->getFormName(), $session_form)) {
                $this->form['attributes'] = $session_form[$this->getFormName()];
                if ($session_form['set_and_clear']) {
                    $session->setAttribute('__form', null);
                }
            }
        }
        if (is_array($this->form) && array_key_exists('attributes', $this->form) && is_array($this->form['attributes'])) {
            foreach ($this->form['attributes'] as $attribute => $value) {
                $attribute = base64_decode($attribute);
                $this->setFieldValue($attribute, $value);
            }
        }
        // Call action commands
        $command = (is_array($this->form) && array_key_exists('command', $this->form)) ? $this->decodeCommand($this->form['command']) : null;
        $target = (is_array($this->form) && array_key_exists('command', $this->form)) ? $this->decodeLocation($this->form['location']) : null;
        if ($command instanceof SketchFormCommand && $command->getCommand() != null) {
            if (self::$executeCommand) {
                self::$executeCommand = false;
                $this->executeCommand($command, $target);
            }
        } else {
            $location = is_array($target) ? $target[true] : trim($target);
            if ($location != null) {
                $this->requestForward($location, ($command instanceof SketchFormCommandPropagate && is_array($this->form) && array_key_exists('attributes', $this->form)) ? $this->form['attributes'] : null);
            }
        }
    }

    /**
     *
     * @param string $location
     * @return string
     */
    public function resolveInstanceParameters($location) {
        // Add dinamic parameters to location
        $explode = explode('?', $location);
        $location = array_shift($explode);
        $url_parameters = explode('&', array_shift($explode));
        $add = array();
        if (is_array($url_parameters)) foreach ($url_parameters as $parameter) {
            if ($parameter != null && !strpos($parameter, '=')) {
                $field = null; foreach (explode('_', $parameter) as $value) {
                    $field .= ucfirst($value);
                }
                $add[] = $parameter.'='.urlencode($this->getFieldValue(strtolower(substr($field, 0, 1)).substr($field, 1)));
            } else if ($parameter != null) {
                $add[] = $parameter;
            }
        }
        if (count($add) > 0) $location = $location.'?'.implode('&', $add);
        return $location;
    }

    /**
     *
     * @param string $location
     * @param array $attributes
     * @param boolean $set_and_clear
     */
    private function requestForward($location, $attributes = null, $set_and_clear = false) {
        // Add notices, log and attributes to session
        $session = $this->getSession();
        $session->setAttribute('__notices', $this->getApplication()->getNotices(false));
        $session->setAttribute('__log', $this->getLogger()->getMessages());
        if (is_array($attributes)) {
            // Update all attributes before propagation because the action class could have modified them
            foreach ($attributes as $attribute => $value) {
                if ($value instanceof SketchResourceFolderDescriptor) {
                } else {
                    $attributes[$attribute] = $this->getFieldValue(base64_decode($attribute));
                }
            }
            $session_attributes = $session->getAttribute('__form');
            $session_attributes[$this->getFormName()] = $attributes;
            $session_attributes['action'] = $this->getAction();
            $session_attributes['set_and_clear'] = ($set_and_clear) ? true : false;
            $session->setAttribute('__form', $session_attributes);
        }
        if ($location != null) {
            $location = $this->resolveInstanceParameters($location);
        }
        // If it's a JSON request then add the forward to the response object
        if ($this->getRequest()->isJSON()) {
            $this->getApplication()->getController()->getResponse()->forward = ($location != null) ? $location : "";
        } else {
            $this->getController()->forward($location);
        }
    }

    /**
     *
     * @param mixed $command
     * @param string $target
     */
    function executeCommand(SketchFormCommand $command, $target) {
        if ($command instanceof SketchFormCommand && $command->getCommand() != null) {
            if (method_exists($this->instance, $command->getCommand())) {
                $parameters = $command->getParameters();
                $cp = null; for ($i = 0; $i < count($parameters); $i++) $cp .= ', $parameters['.$i.']';
                $result = eval('return $this->instance->'.$command->getCommand().'($this'.$cp.');');
                // Check result
                if ($result === true || is_string($result)) {
                    $location = (is_array($target)) ? $target[$result] : (($result) ? trim($target) : null);
                    if ($location != null || $target == null) {
                        // Update instance after calling action commands
                        if (method_exists($this->instance, "addDescriptor") && (is_array($this->form) && array_key_exists('attributes', $this->form) && is_array($this->form['attributes']))) {
                            foreach ($this->form['attributes'] as $attribute => $value) {
                                if ($value instanceof SketchResourceFolderDescriptor) {
                                    $value->setTableName($this->getInstance()->getFolder()->getName());
                                    eval('$this->instance->addDescriptor($value);');
                                }
                            }
                        }
                        if ($result == $command->getTargetForError()) {
                            $this->requestForward($location, is_array($this->form) && array_key_exists('attributes', $this->form) ? $this->form['attributes'] : null, ($command instanceof SketchFormCommandPropagate) ? false : true);
                        } else {
                            $this->requestForward($location, ($command instanceof SketchFormCommandPropagate && is_array($this->form) && array_key_exists('attributes', $this->form)) ? $this->form['attributes'] : null);
                        }
                    } else {
                        throw new Exception(sprintf($this->getTranslator()->_("Method %s returned a non valid location target."), $command->getCommand()));
                    }
                } else if ($result === false) {
                    $this->requestForward(null, is_array($this->form) && array_key_exists('attributes', $this->form) ? $this->form['attributes'] : null, ($command instanceof SketchFormCommandPropagate) ? false : true);
                } else {
                    throw new Exception(sprintf($this->getTranslator()->_("Method %s returned a non valid location target."), $command->getCommand()));
                }
            } else {
                throw new Exception(sprintf($this->getTranslator()->_("Method %s doesn't exist."), $command->getCommand()));
            }
        } else {
            $location = is_array($target) ? $target[true] : trim($target);
            if ($location != null) {
                $this->requestForward($location, ($command instanceof SketchFormCommandPropagate && is_array($this->form) && array_key_exists('attributes', $this->form)) ? $this->form['attributes'] : null);
            }
        }
    }

    /**
     *
     * @param string $mixed
     * @return string
     */
    function encodeCommand($mixed) {
        if ($mixed != null) {
            $mixed = ($mixed instanceof SketchFormCommand) ? $mixed : new SketchFormCommand($mixed);
            switch (get_class($mixed)) {
                case 'SketchFormCommandPropagate': $alias = 'sfcp'; break;
                case 'SketchFormCommand': $alias = 'sfc'; break;
            }
            return $alias.':'.$mixed->getCommand().':'.base64_encode(serialize($mixed->getParameters())).':'.($mixed->getTargetForError() ? $mixed->getTargetForError() : '0');
        } else {
            return $mixed;
        }
    }

    /**
     *
     * @param string $mixed
     * @return string
     */
    private function decodeCommand($mixed) {
        if ($mixed != null) {
            $explode = explode(':', $mixed);
            switch ($explode[0]) {
                case 'sfcp': $command = new SketchFormCommandPropagate(array($explode[1], $explode[3])); break;
                case 'sfc': $command = new SketchFormCommand(array($explode[1], $explode[3])); break;
            }
            $command->setParameters(unserialize(base64_decode($explode[2])));
            return $command;
        } else {
            return $mixed;
        }
    }

    /**
     *
     * @param string $mixed
     * @return string
     */
    private function encodeLocation($mixed) {
        if ($mixed != null) {
            return base64_encode(serialize($mixed));
        } else return $mixed;
    }

    private function decodeLocation($mixed) {
        if ($mixed != null) {
            return unserialize(base64_decode($mixed));
        } else return $mixed;
    }

    private function decodeAttributePathExpression($ape) {
        return ($ape != null) ? explode('.', strtolower($ape)) : array();
    }

    function getFormName() {
        return $this->formName;
    }

    function getAction() {
        return $this->action;
    }

    function setAction($action) {
        $this->action = $this->resolveInstanceParameters($action);
    }

    function getFromInstance() {
        return $this->fromInstance;
    }

    function getInstance() {
        return $this->instance;
    }

    function getFieldName($attribute) {
        return $this->getFormName().'[attributes]['.base64_encode($attribute).']';
    }

    function getFieldValue($ape) {
        $path = $this->decodeAttributePathExpression($ape);
        $instance = $this->instance;
        foreach ($path as $attribute) {
            if (preg_match('/(\w+)\[([\w-|]+)\]/i', $attribute, $matches)) {
                $attribute = $matches[1];
                $key = $matches[2];
            } else {
                $key = null;
            }
            if (method_exists($instance, "get${attribute}")) {
                $instance = eval('return $instance->get'.$attribute.'();');
                if ($key != null && is_array($instance)) $instance = (array_key_exists($key, $instance)) ? $instance[$key] : null;
            } else {
                throw new Exception(sprintf($this->getTranslator()->_("Can't get %1\$s field for %2\$s"), $attribute, get_class($instance)));
            }
        } return $instance;
    }

    function setFieldValue($ape, $original_value) {
        $path = $this->decodeAttributePathExpression($ape);
        $set = array_pop($path);
        $instance = $this->getFieldValue(implode('.', $path));
        // if (!($instance instanceof SketchObjectView)) $instance = $this->instance;
        if ($original_value instanceof SketchResourceFolderDescriptor && method_exists($instance, "addDescriptor")) {
            // addDescriptor has to be called after the command is executed
        } else {
            if (preg_match('/(\w+)\[([\w-|]+)\]/i', $set, $matches)) {
                $set = $matches[1];
                $tmp = $this->getFieldValue(implode('.', array_merge($path, array($set))));
                $tmp[$matches[2]] = $original_value;
                $value = $tmp;
            } else {
                $value = $original_value;
            }
            if ($instance instanceof SketchObjectView || $instance instanceof SketchResourceFolderDescriptor) {
                if (method_exists($instance, "set${set}")) {
                    eval('$instance->set'.$set.'($value);');
                    // Update the form attribute
                    $key = base64_encode($ape);
                    $this->form['attributes'][$key] = $original_value;
                    // Update the propagated session attribute if it exists
                    $session = $this->getSession();
                    $session_attributes = $session->getAttribute('__form');
                    if (is_array($session_attributes) &&
                        array_key_exists($this->getFormName(), $session_attributes) &&
                        array_key_exists($key, $session_attributes[$this->getFormName()])) {
                        $session_attributes[$this->getFormName()][$key] = $original_value;
                        $session->setAttribute('__form', $session_attributes);
                    }
                } else {
                    throw new Exception(sprintf("Can't set %1\$s field for %2\$s", $set, get_class($instance)));
                }
            }
        }
    }

    /**
     *
     * @param string $attribute
     * @return SketchFormNotice
     */
    function getFieldNotices($attribute) {
        foreach ($this->getApplication()->getNotices(false) as $notice) {
            if ($notice instanceof SketchFormNotice) {
                if ($this->getFieldName($attribute) == $notice->getFieldName()) {
                    return $notice;
                }
            }
        }
        return false;
    }

    function getIterator($attribute) {
        $attribute = strtolower($attribute);
        $object = eval('return $this->instance->get'.$attribute.'();');
        if (method_exists($object, "getiterator")) {
            $iterator = $object->getIterator();
        } else if ($object instanceof Iterator) {
            $iterator = $object;
        } else throw new Exception($this->getTranslator()->_("Field doesn't return a valid iterator object."));
        return new SketchFormIterator($iterator);
    }

    function openForm($parameters = null) {
        $this->javascript = true;
        $form_name = $this->getFormName();
        $action = $this->getAction();
        $parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="open-form"')));
        return "<script type=\"text/javascript\">\n(function($) { var stack = new Array(); ${form_name}Stack = function(method) { stack[stack.length] = method; }; ${form_name}Command = function(command, location) { $(\"input[name='${form_name}[command]']\").val(command); $(\"input[name='${form_name}[location]']\").val(location); $(\"form[name='${form_name}']\").submit(); return false; }; $(\"form[name='${form_name}']\").ready(function() { $(\"form[name='${form_name}']\").submit(function() { for (var i = 0; i < stack.length; i++) { eval(stack[i]); } }); }); })(jQuery);\n</script><form name=\"${form_name}\" action=\"${action}\" method=\"post\" enctype=\"multipart/form-data\" ${parameters}><input type=\"hidden\" id=\"${form_name}[command]\" name=\"${form_name}[command]\" /><input type=\"hidden\" id=\"${form_name}[location]\" name=\"${form_name}[location]\" />\n";
    }

    function closeForm() {
        return ($this->defaultCommand != null) ? '<input type="submit" onclick="'.$this->defaultCommand.'" style="display: none"/></form>' : '</form>';
    }

    function setDefaultCommand($command, $location = null) {
        $form_name = $this->getFormName();
        $command = $this->encodeCommand($command);
        $location = $this->encodeLocation($location);
        $this->defaultCommand = "${form_name}Command('${command}', '${location}')";
    }

    function command($command = null, $location = null) {
        $form_name = $this->getFormName();
        $command = $this->encodeCommand($command);
        $location = $this->encodeLocation($location);
        return $form_name.'Command(\''.$command.'\', \''.$location.'\');';
    }

    function commandButton($command, $location = null, $label = null, $parameters = null) {
        $label = ($label != null) ? $label : $command;
        return '<button type="button" onclick="return '.$this->command($command, $location).'" '.trim(!strpos(" $parameters", 'class="') ? "$parameters class=\"command-button\"" : $parameters).'>'.$label.'</button>';
    }

    function commandLink($command, $location = null, $label = null, $parameters = null) {
        $label = ($label != null) ? $label : $command;
        $action = $this->getAction();
        return '<a href="'.$action.'" onclick="return '.$this->command($command, $location).'" '.trim(!strpos(" $parameters", 'class="') ? "$parameters class=\"command-link\"" : $parameters).'>'.$label.'</a>';
    }

    function commandLinkWithConfirmation($command, $location = null, $label = null, $confirmation_message = null, $parameters = null) {
        $label = ($label != null) ? $label : $command;
        return '<a href="'.$this->getAction().'" onclick="if (confirm(\''.$confirmation_message.'\')) { return '.$this->command($command, $location).'; } else { return false; }" '.trim(!strpos(" $parameters", 'class="') ? "$parameters class=\"command-link\"" : $parameters).'>'.$label.'</a>';
    }

    function commandLinkNewWindow($command, $location = null, $label = null, $parameters = null) {
        $form_name = $this->getFormName();
        $label = ($label != null) ? $label : $command;
        return '<a href="'.$this->getAction().'" onclick="jQuery(\'form[name=\\\''.$form_name.'\\\']\').attr(\'target\', \'_blank\'); '.$this->command($command, $location).'; jQuery(\'form[name=\\\''.$form_name.'\\\']\').attr(\'target\', \'_self\'); return false;" '.trim(!strpos(" $parameters", 'class="') ? "$parameters class=\"command-link\"" : $parameters).'>'.$label.'</a>';
    }

    function link($location = null, $label = null, $parameters = null) {
        $location = $this->resolveInstanceParameters($location);
        return '<a href="'.$location.'" '.trim(!strpos(" $parameters", 'class="') ? "$parameters class=\"link\"" : $parameters).'>'.$label.'</a>';
    }

    function resolveLocation($location) {
        return $this->resolveInstanceParameters($location);
    }
}