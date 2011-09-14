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
require_once 'Sketch/Form/Iterator.php';

/**
 * SketchFormView
 *
 * @method string inputCheckbox() inputCheckbox($attribute, $parameters = null, $true = 't', $checked = false) Input checkbox
 * @method string inputDate() inputDate($attribute, $parameters = null) Input date
 * @method string inputFile() inputFile($attribute, $parameters = null) Input file
 * @method string inputFileWithPreview($attribute, $preview_attribute = null, $parameters = null) inputFileWithPreview() Input file with preview
 * @method string inputFileWithUploadify() inputFileWithUploadify($uri, $command, $attribute, $on_complete = 'updateAction') Input file with uploadify
 * @method string inputHidden() inputHidden($attribute, $parameters = null) Input hidden
 * @method string inputNights() inputNights($attribute, $parameters = null) Input nights
 * @method string inputSecret() inputSecret($attribute, $parameters = null, $default = null) Input secret
 * @method string inputText() inputText($attribute, $parameters = null, $default = null) Input text
 * @method string inputTextArea() inputTextArea($attribute, $parameters = null) Input text area
 * @method string inputTime() inputTime($attribute, $parameters = null) Input time
 * @method string selectCheckbox() selectCheckbox($attribute, $reference = null, $parameters = null) Select checkbox
 * @method string selectMultiple() selectMultiple($options, $attribute, $parameters = null) Select multiple
 * @method string selectOne() selectOne($options, $attribute, $parameters) Select one
 * @method string selectOneRadio() selectOneRadio($options, $attribute, $parameters) Select one radio
 * @method string selectRadio() selectRadio($attribute, $reference, $parameters = null) Select radio
 * @throws Exception|SketchResponsePartStopParseException
 */
class SketchFormView extends SketchObject {
    /** @var bool */
    private static $executeCommand = true;

    /** @var \SketchObjectView */
    protected $fromInstance;

    /** @var \SketchObjectView */
    protected $instance;

    /** @var string */
    protected $formName;

    /** @var string */
    protected $action;

    /** @var bool */
    protected $javascript = false;

    /** @var null */
    protected $defaultCommand = null;

    /**
     * Call form components
     *
     * @throws Exception
     * @param $name
     * @param $arguments
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
     * Constructor
     *
     * @param SketchObjectView $data_view
     * @param $form_name
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
     * Resolve instance parameters
     *
     * @param $location
     * @return mixed|string
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
     * Forward requests
     *
     * @throws SketchResponsePartStopParseException
     * @param $location
     * @param null $attributes
     * @param bool $set_and_clear
     * @return void
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
            if ($location != null) {
                $this->getApplication()->getController()->getResponse()->forward = $location;
                throw new SketchResponsePartStopParseException();
            } else {
                $this->getApplication()->getController()->getResponse()->forward = "";
            }
        } else {
            $this->getController()->forward($location);
        }
    }

    /**
     * Execute commands
     *
     * @throws Exception
     * @param SketchFormCommand $command
     * @param $target
     * @return void
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
     * Encode command
     *
     * @param $mixed
     * @return SketchFormCommand|string
     */
    function encodeCommand($mixed) {
        if ($mixed != null) {
            $mixed = ($mixed instanceof SketchFormCommand) ? $mixed : new SketchFormCommand($mixed);
            switch (get_class($mixed)) {
                case 'SketchFormCommandPropagate': $alias = 'sfcp'; break;
                case 'SketchFormCommand': $alias = 'sfc'; break;
            }
            return $alias.':'.$mixed->getCommand().':'.base64_encode(serialize($mixed->getParameters())).':'.($mixed->getTargetForError() ? '1': '0');
        } else {
            return $mixed;
        }
    }

    /**
     * Decode command
     *
     * @param $mixed
     * @return SketchFormCommand|SketchFormCommandPropagate
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
     * Encode location
     *
     * @param $mixed
     * @return string
     */
    private function encodeLocation($mixed) {
        if ($mixed != null) {
            return base64_encode(serialize($mixed));
        } else return $mixed;
    }

    /**
     * Decode location
     *
     * @param $mixed
     * @return mixed
     */
    private function decodeLocation($mixed) {
        if ($mixed != null) {
            return unserialize(base64_decode($mixed));
        } else return $mixed;
    }

    /**
     * Decode attribute path expression
     *
     * @param $ape
     * @return array
     */
    private function decodeAttributePathExpression($ape) {
        return ($ape != null) ? explode('.', strtolower($ape)) : array();
    }

    /**
     * Get form name
     *
     * @return string
     */
    function getFormName() {
        return $this->formName;
    }

    /**
     * Get form action
     *
     * @return string
     */
    function getAction() {
        return $this->action;
    }

    /**
     * Set form action
     *
     * @param $action
     * @return void
     */
    function setAction($action) {
        $this->action = $this->resolveInstanceParameters($action);
    }

    /**
     * Get instance before POST, GET
     *
     * @return SketchObjectView
     */
    function getFromInstance() {
        return $this->fromInstance;
    }

    /**
     * Get instance after POST, GET
     *
     * @return SketchObjectView
     */
    function getInstance() {
        return $this->instance;
    }

    /**
     * Get field name
     *
     * @param $attribute
     * @return string
     */
    function getFieldName($attribute) {
        return $this->getFormName().'[attributes]['.base64_encode($attribute).']';
    }

    /**
     * Get field value
     *
     * Field is defined using a attribute path expression.
     *
     * @throws Exception
     * @param $ape
     * @return null|SketchObjectView
     */
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

    /**
     * Set field value
     *
     * Field is defined using a attribute path expression.
     *
     * @throws Exception
     * @param $ape
     * @param $value
     * @return void
     */
    function setFieldValue($ape, $value) {
        $path = $this->decodeAttributePathExpression($ape);
        $set = array_pop($path);
        $instance = $this->getFieldValue(implode('.', $path));
        if ($value instanceof SketchResourceFolderDescriptor && method_exists($instance, "addDescriptor")) {
            // addDescriptor has to be called after the command is executed
        } else {
            if (preg_match('/(\w+)\[([\w-|]+)\]/i', $set, $matches)) {
                $set = $matches[1];
                $tmp = $this->getFieldValue(implode('.', array_merge($path, array($set))));
                $tmp[$matches[2]] = $value;
                $value = $tmp;
            }
            if (method_exists($instance, "set${set}")) {
                eval('$instance->set'.$set.'($value);');
                // Make sure that we have the same value inside form attributes
                $this->form['attributes'][base64_encode($ape)] = $value;
            } else {
                throw new Exception(sprintf("Can't set %1\$s field for %2\$s", $set, get_class($instance)));
            }
        }
    }

    /**
     * Get field notices
     *
     * @param $attribute
     * @return bool
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

    /**
     * Get instance iterator
     *
     * @throws Exception
     * @param $attribute
     * @return SketchFormIterator
     */
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

    /**
     * Open form
     *
     * @param null $parameters
     * @param null $force_command
     * @return string
     */
    function openForm($parameters = null, $force_command = null) {
        $this->javascript = true;
        $form_name = $this->getFormName();
        $action = $this->getAction();
        $force_command = $this->encodeCommand($force_command);
        $parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="open-form"')));
        return "<script type=\"text/javascript\"><!--\n(function($) { var stack = new Array(); ${form_name}Stack = function(method) { stack[stack.length] = method; }; ${form_name}Command = function(command, location) { $(\"input[name='${form_name}[command]']\").val(command); $(\"input[name='${form_name}[location]']\").val(location); $(\"form[name='${form_name}']\").submit(); return false; }; $(\"form[name='${form_name}']\").ready(function() { $(\"form[name='${form_name}']\").submit(function() { for (var i = 0; i < stack.length; i++) { eval(stack[i]); } }); }); })(jQuery);\n// --></script><form name=\"${form_name}\" action=\"${action}\" method=\"post\" enctype=\"multipart/form-data\" ${parameters}><input type=\"hidden\" id=\"${form_name}[command]\" name=\"${form_name}[command]\" value=\"${force_command}\" /><input type=\"hidden\" id=\"${form_name}[location]\" name=\"${form_name}[location]\" />\n";
    }

    /**
     * Close form
     *
     * @return string
     */
    function closeForm() {
        return ($this->defaultCommand != null) ? '<input type="submit" onclick="'.$this->defaultCommand.'" style="display: none"/></form>' : '</form>';
    }

    /**
     * Set default location
     *
     * @param $command
     * @param null $location
     * @return void
     */
    function setDefaultCommand($command, $location = null) {
        $form_name = $this->getFormName();
        $command = $this->encodeCommand($command);
        $location = $this->encodeLocation($location);
        $this->defaultCommand = "${form_name}Command('${command}', '${location}')";
    }

    /**
     * Returns JavaScript to send a command
     *
     * @param null $command
     * @param null $location
     * @return string
     */
    function command($command = null, $location = null) {
        $form_name = $this->getFormName();
        $command = $this->encodeCommand($command);
        $location = $this->encodeLocation($location);
        return $form_name.'Command(\''.$command.'\', \''.$location.'\');';
    }

    /**
     * Returns HTML and JavaScript to add a command button
     *
     * @param $command
     * @param null $location
     * @param null $label
     * @param null $parameters
     * @return string
     */
    function commandButton($command, $location = null, $label = null, $parameters = null) {
        $label = ($label != null) ? $label : $command;
        return '<span><input type="button" value="'.$label.'" onclick="return '.$this->command($command, $location).'" '.trim(!strpos(" $parameters", 'class="') ? "$parameters class=\"command-button\"" : $parameters).' /></span>';
    }

    /**
     * Returns HTML and JavaScript to add a command link
     *
     * @param $command
     * @param null $location
     * @param null $label
     * @param null $parameters
     * @return string
     */
    function commandLink($command, $location = null, $label = null, $parameters = null) {
        $label = ($label != null) ? $label : $command;
        $action = $this->getAction();
        return '<a href="'.$action.'" onclick="return '.$this->command($command, $location).'" '.trim(!strpos(" $parameters", 'class="') ? "$parameters class=\"command-link\"" : $parameters).'>'.$label.'</a>';
    }

    /**
     * Returns HTML and JavaScript to add a command link with confirmation
     *
     * @param $command
     * @param null $location
     * @param null $label
     * @param null $confirmation_message
     * @param null $parameters
     * @return string
     */
    function commandLinkWithConfirmation($command, $location = null, $label = null, $confirmation_message = null, $parameters = null) {
        $label = ($label != null) ? $label : $command;
        return '<a href="'.$this->getAction().'" onclick="if (confirm(\''.$confirmation_message.'\')) { return '.$this->command($command, $location).'; } else { return false; }" '.trim(!strpos(" $parameters", 'class="') ? "$parameters class=\"command-link\"" : $parameters).'>'.$label.'</a>';
    }

    /**
     * Returns HTML and JavaScript to add a command link that submits to another window
     *
     * @param $command
     * @param null $location
     * @param null $label
     * @param null $parameters
     * @return string
     */
    function commandLinkNewWindow($command, $location = null, $label = null, $parameters = null) {
        $form_name = $this->getFormName();
        $label = ($label != null) ? $label : $command;
        return '<a href="'.$this->getAction().'" onclick="jQuery(\'form[name=\\\''.$form_name.'\\\']\').attr(\'target\', \'_blank\'); '.$this->command($command, $location).'; jQuery(\'form[name=\\\''.$form_name.'\\\']\').attr(\'target\', \'_self\'); return false;" '.trim(!strpos(" $parameters", 'class="') ? "$parameters class=\"command-link\"" : $parameters).'>'.$label.'</a>';
    }

    /**
     * Returns routed link
     *
     * @param null $location
     * @param null $label
     * @param null $parameters
     * @return string
     */
    function link($location = null, $label = null, $parameters = null) {
        $location = $this->resolveInstanceParameters($location);
        return '<a href="'.$location.'" '.trim(!strpos(" $parameters", 'class="') ? "$parameters class=\"link\"" : $parameters).'>'.$label.'</a>';
    }

    /**
     * Returns a routed location
     *
     * @param $location
     * @return mixed|string
     */
    function resolveLocation($location) {
        return $this->resolveInstanceParameters($location);
    }
}
