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

namespace Sketch;

class ResourceFactory {
    /**
     * @var string
     */
    static private $defaultContext = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<context name=\"sketch\" layer=\"default\">\n\t<layer name=\"default\"></layer>\n</context>";

    /**
     * @param ResourceContext $context
     * @throws ResourceConnectionException
     * @throws \Exception
     * @return ResourceConnection
     */
    static function getConnection(ResourceContext $context) {
        $driver = $context->queryFirst("//driver[@type='ResourceConnectionDriver']");
        if ($driver) {
            $type = "Sketch\\".$driver->getAttribute('type');
            $class = $driver->getAttribute('class');
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                $instance = $reflection->newInstance($driver);
                if ($instance instanceof $type) {
                    /** @var $instance ResourceConnectionDriver */
                    return new ResourceConnection($instance);
                } else {
                    throw new \Exception(sprintf($context->getTranslator()->_s("Driver %s does not extend or implement %s"), $class, $type));
                }
            } else {
                throw new \Exception(sprintf($context->getTranslator()->_s("Can't instantiate class %s"), $class));
            }
        } else {
            throw new ResourceConnectionException($context->getTranslator()->_s("No driver configuration in context"));
        }
    }

    /**
     * @param string $file
     * @throws \Exception
     * @return ResourceContext
     */
    static function getContext($file) {
        libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->resolveExternals = false;
        try {
            $document->loadXML(file_get_contents($file, FILE_USE_INCLUDE_PATH));
        } catch (\Exception $e) {
            $document->loadXML(self::$defaultContext);
        }
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            throw new \Exception($error->message);
        } libxml_clear_errors();
        return new ResourceContext($document);
    }

    static function getFolder($table, $parent_id) {
        return new ResourceFolder($table, $parent_id);
    }

    /**
     * @param string $file
     * @throws \Exception
     * @return ResourceXML
     */
    static function getXML($file) {
        libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->resolveExternals = true;
        $document->loadXML(file_get_contents($file, FILE_USE_INCLUDE_PATH));
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            throw new \Exception($error->message);
        } libxml_clear_errors();
        return new ResourceXML($document);
    }
}