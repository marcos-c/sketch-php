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

/**
 * SketchResourceFolderDescriptor
 *
 * @package Sketch
 */
class SketchResourceFolderDescriptor extends SketchResource {
    /**
     *
     * @var integer
     */
    private $id;

    /**
     *
     * @var integer
     */
    private $parentId;

    /**
     *
     * @var string
     */
    private $reference;

    /**
     *
     * @var string
     */
    private $fileName;

    /**
     *
     * @var string;
     */
    private $sourceFileName;

    /**
     *
     * @var string
     */
    private $fileType;

    /**
     *
     * @var integer
     */
    private $fileSize;

    /**
     *
     * @var integer
     */
    private $imageWidth;

    /**
     *
     * @var integer
     */
    private $imageHeight;

    /**
     *
     * @param array $parameters
     */
    function __construct($parameters = null) {
        $this->setId($parameters['id']);
        $this->setParentId($parameters['parent_id']);
        $this->setReference($parameters['reference']);
        $this->setFileName($parameters['file_name']);
        $this->setSourceFileName($parameters['source_file_name']);
        $this->setFileType($parameters['file_type']);
        $this->setFileSize($parameters['file_size']);
        $this->setImageWidth($parameters['image_width']);
        $this->setImageHeight($parameters['image_height']);
    }

    /**
     *
     * @return boolean
     */
    function isImage() {
        return in_array($this->getFileType(), array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png'));
    }

    /**
     *
     * @return integer
     */
    function getId() {
        return $this->id;
    }

    /**
     *
     * @param integer $id
     */
    function setId($id) {
        $this->id = intval($id);
    }

    /**
     *
     * @return integer
     */
    function getParentId() {
        return $this->parentId;
    }

    /**
     *
     * @param integer $parent_id
     */
    function setParentId($parent_id) {
        $this->parentId = intval($parent_id);
    }

    /**
     *
     * @return string
     */
    function getReference() {
        return $this->reference;
    }

    /**
     *
     * @param string $reference
     */
    function setReference($reference) {
        $this->reference = trim($reference);
    }

    /**
     *
     * @return string
     */
    function getFileName() {
        return $this->fileName;
    }

    /**
     *
     * @param string $name
     */
    function setFileName($name) {
        $this->fileName = trim($name);
    }

    /**
     *
     * @return string
     */
    function getSourceFileName() {
        return $this->sourceFileName;
    }

    /**
     *
     * @param string $source_name
     */
    function setSourceFileName($source_name) {
        $this->sourceFileName = trim($source_name);
    }

    /**
     *
     * @return string
     */
    function getFileExtension() {
        return substr(strrchr($this->sourceFileName, '.'), 1);
    }

    /**
     *
     * @return string
     */
    function getFileType() {
        return $this->fileType;
    }

    /**
     *
     * @param string $type
     */
    function setFileType($type) {
        $this->fileType = trim($type);
    }

    /**
     *
     * @return integer
     */
    function getFileSize() {
        return $this->fileSize;
    }

    /**
     *
     * @param integer $size
     */
    function setFileSize($size) {
        $this->fileSize = intval($size);
    }

    /**
     *
     * @return string
     */
    function getFormattedFileSize() {
        return round($this->fileSize / 1024)."Kb";
    }

    /**
     *
     * @return integer
     */
    function getImageWidth() {
        return $this->imageWidth;
    }

    /**
     *
     * @param integer $image_width
     */
    function setImageWidth($image_width) {
        $this->imageWidth = intval($image_width);
    }

    /**
     *
     * @return integer
     */
    function getImageHeight() {
        return $this->imageHeight;
    }

    /**
     *
     * @param integer $image_height
     */
    function setImageHeight($image_height) {
        $this->imageHeight = intval($image_height);
    }
}