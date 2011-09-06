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

require_once 'Sketch/Form/Component.php';

/**
 * SketchFormComponentInputFile
 *
 * @package Components
 */
class SketchFormComponentInputFile extends SketchFormComponent {
    function saveHTML() {
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $parameters = array_shift($arguments);
        $form_name = $this->getForm()->getFormName();
        $field_name = str_replace('[attributes]', "[$form_name]", $this->getForm()->getFieldName($attribute));
        $parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="input-file"')));
        if (method_exists($this->getForm()->getInstance(), 'getFolder')) {
            $folder = $this->getForm()->getInstance()->getFolder();
            $descriptor = $folder->getDescriptor(($preview != null) ? $preview : $attribute);
            $info = null; if ($descriptor instanceof SketchResourceFolderDescriptor) {
                $info = $this->getForm()->commandLink(new SketchFormCommand('removeDescriptor', $attribute), null, $this->getTranslator()->_('Remove')).' '.htmlspecialchars($descriptor->getSourceFileName()).', '.$descriptor->getFormattedFileSize().', '.$descriptor->getFileType().'<br />';
            } return $info.'<input type="file" name="'.$field_name.'" '.$parameters.' />';
        } else {
            return '<input type="file" name="'.$field_name.'" '.$parameters.' />';
        }
    }
}