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

require_once 'Sketch/Utils.php';
require_once 'Sketch/Resource/Connection.php';
require_once 'Sketch/Resource/Context.php';
require_once 'Sketch/Resource/Folder.php';
require_once 'Sketch/Resource/XML.php';

/**
 * SketchResourceFactory
 */
class SketchResourceFactory {
    /** @var string */
    static private $defaultContext = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<context name=\"sketch\" layer=\"default\">\n\t<layer name=\"default\" />\n</context>";

    /**
     * Get connection
     *
     * @static
     * @throws Exception
     * @param SketchResourceContext $context
     * @return SketchResourceConnection
     */
    static function getConnection(SketchResourceContext $context) {
        $driver = $context->queryFirst("//driver[@type='SketchConnectionDriver']");
        if ($driver) {
            $type = $driver->getAttribute('type');
            $class = $driver->getAttribute('class');
            $source = $driver->getAttribute('source');
            if (SketchUtils::Readable("Sketch/Resource/Connection/Driver/$source")) {
                require_once "Sketch/Resource/Connection/Driver/$source";
                if (class_exists($class)) {
                    eval('$instance = new '.$class.'($driver);');
                    if ($instance instanceof $type) {
                        return new SketchResourceConnection($instance);
                    } else throw new Exception(sprinf($context->getTranslator()->_("Driver %s does not extend or implement %s"), $class, $type));
                } else throw new Exception(sprintf($context->getTranslator()->_("Can't instantiate class %s"), $class));
            } else throw new Exception(sprintf($context->getTranslator()->_("File %s can't be found"), $source));
        }
    }

    /**
     * Get context
     *
     * @static
     * @throws Exception
     * @param $file
     * @return SketchResourceContext
     */
    static function getContext($file) {
        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->resolveExternals = false;
        try {
            $document->loadXML(file_get_contents($file, FILE_USE_INCLUDE_PATH));
        } catch (Exception $e) {
            $document->loadXML(self::$defaultContext);
        }
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            throw new Exception($error->message);
        } libxml_clear_errors();
        return new SketchResourceContext($document);
    }

    /**
     * Get folder
     *
     * @static
     * @param $table
     * @param $parent_id
     * @return SketchResourceFolder
     */
    static function getFolder($table, $parent_id) {
        return new SketchResourceFolder($table, $parent_id);
    }

    /**
     * Get XML
     *
     * @static
     * @throws Exception
     * @param $file
     * @return SketchResourceXML
     */
    static function getXML($file) {
        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->resolveExternals = true;
        $document->loadXML(file_get_contents($file, FILE_USE_INCLUDE_PATH));
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            throw new Exception($error->message);
        } libxml_clear_errors();
        return new SketchResourceXML($document);
    }
}