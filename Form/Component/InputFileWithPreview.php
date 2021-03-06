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
 * SketchFormComponentInputFileWithPreview
 *
 * @package Components
 */
class SketchFormComponentInputFileWithPreview extends SketchFormComponent {
    function saveHTML() {
        $arguments = $this->getArguments();
        $attribute = array_shift($arguments);
        $preview_attribute = array_shift($arguments);
        $parameters = array_shift($arguments);
        $folder = $this->getForm()->getInstance()->getFolder();
        $descriptor = $folder->getDescriptor(($preview_attribute != null) ? $preview_attribute : $attribute);
        $form_name = $this->getForm()->getFormName();
        $field_name = str_replace('[attributes]', "[$form_name]", $this->getForm()->getFieldName($attribute));
        $parameters = (($parameters != null && strpos(" $parameters", 'class="')) ? $parameters : implode(' ', array($parameters, 'class="input-file"')));
        $preview = null;
        if ($descriptor instanceof SketchResourceFolderDescriptor && $descriptor->isImage()) {
            $file_name = $folder->getResourcePath().$descriptor->getFileName();
            $width = $descriptor->getImageWidth();
            $height = $descriptor->getImageHeight();
            $preview = '<img src="'.$file_name.'" width="'.$width.'" height="'.$height.'" border="0" /><br />'.$this->getForm()->commandLink(new SketchFormCommand('removeDescriptor', $attribute), null, $this->getTranslator()->_('Remove')).' '.htmlspecialchars($descriptor->getSourceFileName()).', '.$descriptor->getFormattedFileSize().', '.$descriptor->getFileType().'<br />';
        }
        return $preview.'<input type="file" name="'.$field_name.'" '.$parameters.' />';
    }
}
