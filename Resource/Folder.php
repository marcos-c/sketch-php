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

require_once 'Sketch/Resource.php';
require_once 'Sketch/Resource/Folder/Descriptor.php';
require_once 'Sketch/Resource/Folder/Descriptor/List.php';

define('FORCE_GEOMETRY', 1);
define('FILL_GEOMETRY', 2);
define('CLIP_GEOMETRY', 3);
define('FOLDER_MD5_SIZE', 20);

/**
 * SketchResourceFolder
 *
 * @package Sketch
 */
class SketchResourceFolder extends SketchResource {
    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $uri;

    /**
     *
     * @var integer
     */
    private $parentId;

    /**
     *
     * @var array
     */
    private $descriptors;

    /**
     *
     * @var array
     */
    private $geometry;

    /**
     *
     * @param string $name
     * @param integer $parent_id
     */
    function __construct($name, $parent_id) {
        $connection = $this->getConnection();
        $prefix = $connection->getTablePrefix();
        if ($prefix != null) {
            $name = "${prefix}_${name}";
        }
        $this->name = $name;
        $this->uri = $this->getApplication()->getURI().'/resources/'.$name.'/';
        $this->parentId = intval($parent_id);
        $this->descriptors = array();
        $this->geometry = null;
        if ($parent_id = $this->getParentId()) {
            $table = $this->getName();
            foreach ($connection->executeQuery("SELECT * FROM $table WHERE parent_id = $parent_id ORDER BY source_file_name") as $current) {
                $current['table_name'] = $this->name;
                $this->descriptors[$current['reference']] = new SketchResourceFolderDescriptor($current);
            }
        }
    }

    /**
     *
     * @return string
     * @deprecated
     */
    function getResourceName() {
        return $this->getName();
    }

    /**
     *
     * @return string
     */
    function getName() {
        return $this->name;
    }

    /**
     *
     * @return string
     * @deprecated
     */
    function getResourcePath() {
        return $this->getURI();
    }

    /**
     *
     * @return string
     */
    function getURI() {
        return $this->uri;
    }
    
    /**
     *
     * @return string
     * @deprecated
     */
    function getDocumentResourcePath() {
        return $this->getDocumentRoot();
    }

    /**
     *
     * @return string
     */
    function getDocumentRoot() {
        // Can't rely on $_SERVER['DOCUMENT_ROOT'] because it doesn't return what you would
        // expect on all situations (symbolic links, server configuration, etc.)
        $server_document_root = str_replace($_SERVER['SCRIPT_NAME'], '', realpath(basename($_SERVER['SCRIPT_NAME'])));
        if (!is_dir($server_document_root.$this->getURI())) {
            mkdir($server_document_root.$this->getURI());
        }
        return $server_document_root.$this->getURI();
    }

    /**
     *
     * @return integer
     */
    function getParentId() {
        return ($this->parentId > 0) ? $this->parentId : false;
    }

    /**
     *
     * @param string $reference
     * @return SketchResourceFolderDescriptor
     */
    function getDescriptor($reference) {
        return (array_key_exists($reference, $this->descriptors) && $this->descriptors[$reference] instanceof SketchResourceFolderDescriptor) ? $this->descriptors[$reference] : false;
    }

    /**
     *
     * @param $source_file_name
     * @param string $extra
     * @param string $default
     * @return string
     */
    function getDescriptorFromSourceFileName($source_file_name, $extra = '', $default = '') {
        foreach ($this->getDescriptors() as $descriptor) {
            if ($descriptor->getSourceFileName() == $source_file_name) {
                return $descriptor;
            }
        }
        return $default;
    }

    /**
     *
     * @return array
     */
    function getDescriptors() {
        return $this->descriptors;
    }

    /**
     *
     * @return SketchResourceFolderDescriptorList
     */
    function getDescriptorList() {
        return new SketchResourceFolderDescriptorList($this->descriptors);
    }

    /**
     *
     * @param string $reference
     * @param string $extra
     * @return string
     */
    function getDescriptorHTML($reference, $extra = null) {
        if ($descriptor = $this->getDescriptor($reference)) {
            $uri = $this->getURI().$descriptor->getFileName();
            if ($descriptor->isImage()) {
                $width = $descriptor->getImageWidth();
                $height = $descriptor->getImageHeight();
                return '<img src="'.$uri.'" width="'.$width.'" height="'.$height.'" border="0"'.(($extra != null) ? " $extra" : "").' />';
            } else {
                $source_name = $descriptor->getSourceFileName();
                return '<a href="'.$uri.'">'.$source_name.'</a>';
            }
        } else {
            return false;
        }
    }

    function exportDescriptor($reference, $force_download = false) {
        ob_end_clean();
        $descriptor = $this->getDescriptor($reference);
        $file = $this->getDocumentRoot().$descriptor->getFileName();
        if (ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 0);
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime($file)).' GMT');
        header('Cache-Control: private', false);
        header('Content-Type: '.$descriptor->getFileType());
        if ($descriptor->isImage() && !$force_download) {
            header('Content-Disposition: inline; filename="'.$descriptor->getSourceFileName().'"');
        } else {
            header('Content-Disposition: attachment; filename="'.$descriptor->getSourceFileName().'"');
        }
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.filesize($file));
        header('Connection: close');
        readfile($file);
        exit();
    }

    /**
     *
     * @param SketchResourceFolderDescriptor $descriptor
     */
    function addDescriptor($descriptor) {
        if (($descriptor instanceof SketchResourceFolderDescriptor) && is_readable($descriptor->getFileName())) {
            $application = $this->getApplication();
            $test = true; if ($descriptor->isImage()) {
                if (!$this->imlibSave($descriptor)) {
                    if (!$this->gdSave($descriptor)) {
                        $test = false;
                    }
                }
            } else {
                if (!$this->documentSave($descriptor)) {
                    $test = false;
                }
            }
            if ($test) {
                $connection = $this->getConnection();
                $table_name = $this->getName();
                $parent_id = $this->getParentId();
                $reference = $descriptor->getReference();
                $file_name = $descriptor->getFileName();
                $source_file_name = $descriptor->getSourceFileName();
                $file_type = $descriptor->getFileType();
                $file_size = $descriptor->getFileSize();
                $image_width = $descriptor->getImageWidth();
                $image_height = $descriptor->getImageHeight();
                $clip_x = $descriptor->getClipX() ? $descriptor->getClipX() : 0;
                $clip_y = $descriptor->getClipY() ? $descriptor->getClipY() : 0;
                if (array_key_exists($descriptor->getReference(), $this->descriptors)) {
                    $test = $connection->executeUpdate("UPDATE $table_name SET file_name = '$file_name', source_file_name = '$source_file_name', file_type = '$file_type', file_size = $file_size, image_width = $image_width, image_height = $image_height, clip_x = $clip_x, clip_y = $clip_y WHERE parent_id = $parent_id AND reference = '$reference'");
                    if ($test) {
                        $application->addNotice(new SketchApplicationNotice(sprintf($this->getTranslator()->_("Descriptor <b>%s</b> (%s) has been updated"), $reference, $file_type)));
                    }
                } else {
                    if ($connection->supports('nextval')) {
                        $descriptor->setId($connection->queryFirst("SELECT nextval('${table_name}_id_seq')"));
                        $test = $connection->executeUpdate(sprintf("INSERT INTO ${table_name} (id, parent_id, reference, file_name, source_file_name, file_type, file_size, image_width, image_height, clip_x, clip_y) VALUES (%d, $parent_id, '$reference', '$file_name', '$source_file_name', '$file_type', $file_size, $image_width, $image_height, $clip_x, $clip_y)", $descriptor->getId()));
                    } else {
                        $test = $connection->executeUpdate("INSERT INTO $table_name (parent_id, reference, file_name, source_file_name, file_type, file_size, image_width, image_height, clip_x, clip_y) VALUES ($parent_id, '$reference', '$file_name', '$source_file_name', '$file_type', $file_size, $image_width, $image_height, $clip_x, $clip_y)");
                        if ($test) {
                            $descriptor->setId($connection->queryFirst("SELECT LAST_INSERT_ID()"));
                        }
                    }
                    if ($test) {
                        $application->addNotice(new SketchApplicationNotice(sprintf($this->getTranslator()->_("Descriptor <b>%s</b> (%s) was added to folder"), $reference, $file_type)));
                    }
                }
            }
            if ($test) {
                $this->descriptors[$reference] = $descriptor;
                $this->clean();
            } else {
                $application->addNotice(new SketchApplicationNotice(sprintf($this->getTranslator()->_("Descriptor <b>%s</b> (%s) couldn't be added to folder"), $descriptor->getReference(), $descriptor->getFileType())));
            }
        }
    }

    /**
     *
     * @param SketchResourceFolderDescriptor $descriptor
     * @return boolean
     */
    function updateDescriptor(SketchResourceFolderDescriptor $descriptor) {
        $connection = $this->getConnection();
        $descriptor_id = $descriptor->getId(0);
        $table_name = $descriptor->getTableName();
        $data = array();
        foreach ($descriptor->getData() as $key => $value) {
            list($key, $language) = explode('|', $key);
            $data[$language][$key] = $value;
        }
        $test = true;
        try {
            foreach ($data as $language => $r) {
                foreach ($r as $key => $value) {
                    switch ($key) {
                        case 'caption':
                            $caption = $connection->escapeString($value);
                            break;
                        case 'tags':
                            $tags = $connection->escapeString($value);
                            break;
                    }
                }
                $test = $test && $connection->executeUpdate("REPLACE INTO ${table_name}_data (descriptor_id, `language`, caption, tags) VALUES ($descriptor_id, '$language', '$caption', '$tags')");
            }
        } catch (Exception $e) {}
        return $test;
    }

    /**
     *
     * @param $reference
     * @return bool
     * @throws Exception
     */
    function removeDescriptor($reference) {
        $connection = $this->getConnection();
        $table = $this->getName();
        $parent_id = $this->getParentId();
        if ($descriptor = $this->getDescriptor($reference)) {
            unset($this->descriptors[$reference]);
            try {
                unlink($this->getDocumentRoot().$descriptor->getFileName());
            } catch (Exception $e) {
                if (strstr($e->getMessage(), 'No such file or directory') === false) {
                    throw $e;
                }
            }
            return $connection->executeUpdate("DELETE FROM $table WHERE file_name = '".$descriptor->getFileName()."'");
        }
    }

    private function clean() {
        $connection = $this->getConnection();
        $path = $this->getDocumentRoot();
        $files = array(); if (is_dir($path)) {
            if ($dh = opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    if (filetype($path.$file) == 'file') {
                        $files[] = $file;
                    }
                }
                closedir($dh);
            }
        }
        $table = $this->getName();
        $descriptors = array();
        foreach ($connection->executeQuery("SELECT * FROM $table") as $current) {
            $current['table_name'] = $this->getName();
            $descriptor = new SketchResourceFolderDescriptor($current);
            $descriptors[] = $descriptor->getFileName();
        }
        foreach (array_diff($files, $descriptors) as $remove) {
            unlink($this->getDocumentRoot().$remove);
        }
    }

    /**
     *
     * @return array|false
     */
    function getOutputGeometry() {
        return (is_array($this->geometry)) ? $this->geometry : false;
    }

    /**
     *
     * @param integer $width
     * @param integer $height
     * @param integer $model
     */
    function setOutputGeometry($width, $height, $model = null) {
        $this->geometry['width'] = intval($width);
        $this->geometry['height'] = intval($height);
        $this->geometry['model'] = $model;
    }

    /**
     *
     * @param SketchResourceFolderDescriptor $descriptor
     * @return boolean
     */
    function documentSave($descriptor) {
        if (array_key_exists($descriptor->getReference(), $this->descriptors) && $descriptor->getFileExtension() == $this->descriptors[$descriptor->getReference()]->getFileExtension()) {
            $file_name = $this->descriptors[$descriptor->getReference()]->getFileName();
        } else {
            $file_name = 'f'.substr(md5(uniqid(rand())), 0, FOLDER_MD5_SIZE).'.'.$descriptor->getFileExtension();
        }
        if ($result = copy($descriptor->getFileName(), $this->getDocumentRoot().$file_name)) {
            $descriptor->setFileName($file_name);
        }
        return $result;
    }

    /**
     *
     * @param SketchResourceFolderDescriptor $descriptor
     * @return boolean
     */
    function imlibSave($descriptor) {
        return false;
    }

    /**
     *
     * @param SketchResourceFolderDescriptor $descriptor
     * @return boolean
     */
    function gdSave($descriptor) {
        if (array_key_exists($descriptor->getReference(), $this->descriptors) && $descriptor->getFileExtension() == $this->descriptors[$descriptor->getReference()]->getFileExtension()) {
            $file_name = $this->descriptors[$descriptor->getReference()]->getFileName();
        } else {
            $file_name = 'f'.substr(md5(uniqid(rand())), 0, FOLDER_MD5_SIZE).'.'.$descriptor->getFileExtension();
        }
        list($width, $height) = getimagesize($descriptor->getFileName());
        if ($geometry = $this->getOutputGeometry()) {
            if ($geometry['width'] == null || $geometry['width'] > $width) {
                $geometry['width'] = $width;
            }
            if ($geometry['height'] == null || $geometry['height'] > $height) {
                $geometry['height'] = $height;
            }
            $src_x = 0;
            $src_y = 0;
            $output_width = $geometry['width'];
            $output_height = $geometry['height'];
            if ($geometry['model'] == FORCE_GEOMETRY) {
            } else if ($geometry['model'] == FILL_GEOMETRY) {
                if ($width != $geometry['width'] || $height != $geometry['height']) {
                    if ($width > $height) {
                        $aux_width = round($geometry['height'] * $width / $height);
                        if ($aux_width < $geometry['width']) {
                            $output_height = round($geometry['width'] * $height / $width);
                        } else {
                            $output_width = $aux_width;
                        }
                    } else {
                        $aux_height = round($geometry['width'] * $height / $width);
                        if ($aux_height < $geometry['height']) {
                            $output_width = round($geometry['height'] * $width / $height);
                        } else {
                            $output_height = $aux_height;
                        }
                    }
                } else $geometry = null;
            } else if ($geometry['model'] == CLIP_GEOMETRY) {
                if ($width != $geometry['width'] || $height != $geometry['height']) {
                    if ($width > $height) {
                        $aux_width = round($geometry['height'] * $width / $height);
                        if ($aux_width < $geometry['width']) {
                            $aux_height = round($geometry['width'] * $height / $width);
                            $src_y = round($descriptor->getClipY() * $height / $aux_height);
                            $height = round($output_height * $height / $aux_height);
                        } else {
                            $src_x = round($descriptor->getClipX() * $width / $aux_width);
                            $width = round($output_width * $width / $aux_width);
                        }
                    } else {
                        $aux_height = round($geometry['width'] * $height / $width);
                        if ($aux_height < $geometry['height']) {
                            $aux_width = round($geometry['height'] * $width / $height);
                            $src_x = round($descriptor->getClipX() * $width / $aux_width);
                            $width = round($output_width * $width / $aux_width);
                        } else {
                            $src_y = round($descriptor->getClipY() * $height / $aux_height);
                            $height = round($output_height * $height / $aux_height);
                        }
                    }
                } else $geometry = null;
            } else {
                $in_factor = $width / $height;
                $out_factor = $geometry['width'] / $geometry['height'];
                $zoom_factor = ($geometry['width'] / $width) + ($geometry['height'] / $height);
                if ($in_factor > $out_factor) {
                    $output_height = round($geometry['width'] / $in_factor);
                } else if ($in_factor < $out_factor) {
                    $output_width = round($geometry['height'] * $in_factor);
                } else if ($zoom_factor == 2) {
                    $output_width = $output_height = null;
                }
            }
            if ($output_width > 0 && $output_height > 0) {
                if (in_array($descriptor->getFileType(), array('image/jpeg', 'image/pjpeg'))) {
                    $src = imagecreatefromjpeg($descriptor->getFileName());
                    if (function_exists('imagecreatetruecolor')) {
                        $dst = imagecreatetruecolor($output_width, $output_height);
                    } else {
                        $dst = imagecreate($output_width, $output_height);
                    }
                    if (function_exists('imagecopyresampled')) {
                        imagecopyresampled($dst, $src, 0, 0, $src_x, $src_y, $output_width, $output_height, $width, $height);
                    } else {
                        imagecopyresized($dst, $src, 0, 0, $src_x, $src_y, $output_width, $output_height, $width, $height);
                    }
                    if (imagejpeg($dst, $this->getDocumentRoot().$file_name, 90)) {
                        $descriptor->setFileName($file_name);
                        $descriptor->setImageWidth($output_width);
                        $descriptor->setImageHeight($output_height);
                        $descriptor->setFileSize(filesize($this->getDocumentRoot().$file_name));
                        return true;
                    } else {
                        return false;
                    }
                } else if ($descriptor->getFileType() == 'image/png') {
                    $src = imagecreatefrompng($descriptor->getFileName());
                    if (function_exists('imagecreatetruecolor')) {
                        $dst = imagecreatetruecolor($output_width, $output_height);
                    } else {
                        $dst = imagecreate($output_width, $output_height);
                    }
                    if (function_exists('imagecopyresampled')) {
                        imagecopyresampled($dst, $src, 0, 0, 0, 0, $output_width, $output_height, $width, $height);
                    } else {
                        imagecopyresized($dst, $src, 0, 0, 0, 0, $output_width, $output_height, $width, $height);
                    }
                    if (imagepng($dst, $this->getDocumentRoot().$file_name, 9)) {
                        $descriptor->setFileName($file_name);
                        $descriptor->setImageWidth($output_width);
                        $descriptor->setImageHeight($output_height);
                        $descriptor->setFileSize(filesize($this->getDocumentRoot().$file_name));
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
        if ($result = copy($descriptor->getFileName(), $this->getDocumentRoot().$file_name)) {
            $descriptor->setFileName($file_name);
            $descriptor->setImageWidth($width);
            $descriptor->setImageHeight($height);
        }
        return $result;
    }
}