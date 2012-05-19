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
 * SketchFormComponentInputFileWithUploadify
 *
 * @package Components
 */
class SketchFormComponentInputFileWithUploadify extends SketchFormComponent {
    function saveHTML() {
        $arguments = $this->getArguments();
        $uri = array_shift($arguments);
        $command = array_shift($arguments);
        $attribute = array_shift($arguments);
        $form_name = $this->getForm()->getFormName();
        $field_name = str_replace('[attributes]', "[$form_name]", $this->getForm()->getFieldName($attribute));
        $field_id = substr(md5(uniqid()), 0, 8);
        $size_limit = ini_get('upload_max_filesize');
        if (preg_match('/\d+K/', $size_limit)) {
            $size_limit = intval($size_limit) * 1024;
        } elseif (preg_match('/\d+M/', $size_limit)) {
            $size_limit = intval($size_limit) * 1048576;
        } elseif (preg_match('/\d+G/', $size_limit)) {
            $size_limit = intval($size_limit) * 1073741824;
        }
        $on_complete = method_exists($this->getForm()->getInstance(), 'partialUpdateAction') ? 'partialUpdateAction' : 'updateAction';
        ob_start(); ?>
            <input type="file" id="<?=$field_id?>" name="<?=$field_name?>" />
            <script type="text/javascript">
                jQuery(function(){
                    jQuery('#<?=$field_id?>').uploadify({
                        uploader:'/js/uploadify.swf',
                        script:'<?=$this->getForm()->resolveInstanceParameters($uri)?>',
                        scriptData:{'<?=$form_name?>[command]':'<?=$this->getForm()->encodeCommand($command)?>','<?=$this->getSession()->getName()?>':'<?=$this->getSession()->getId()?>'},
                        fileDataName:'<?=$field_name?>',
                        auto:true,
                        multi:false,
                        buttonImg:'/i/browse-files.png',
                        wmode:'transparent',
                        width:106,
                        height:24,
                        sizeLimit:<?=$size_limit?>,
                        onComplete:function(event, id, fileObj, response, data){if (response != 1){console.log(response);}else{<?=$this->getForm()->command($on_complete)?>}}
                    });
                });
            </script>
        <?php return ob_get_clean();
    }
}