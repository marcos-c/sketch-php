<?php
/**
 * This file is part of the Sketch library
 *
 * @author Marcos Cooper <marcos@releasepad.com>
 * @version 3.0
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

namespace Sketch\Resource;

use Sketch\Resource\Database\Database;
use Sketch\Resource\Database\Driver\Driver;
use Sketch\Resource\Folder\Folder;

/**
 * Resource factory class
 *
 * @package Sketch\Resource
 */
class Factory {
    /**
     * @var string
     */
    static private $defaultContext = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<context name=\"sketch\" layer=\"default\">\n\t<layer name=\"default\"></layer>\n</context>";

    /**
     * @param Context $context
     * @throws \Exception
     * @return Database
     */
    static function getConnection(Context $context) {
        $driver = $context->queryFirst("//driver[@type='ResourceConnectionDriver']");
        if ($driver) {
            $class = $driver->getAttribute('class');
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                if ($reflection->isSubclassOf('Sketch\ResourceConnectionDriver')) {
                    /** @var Driver $instance */
                    $instance = $reflection->newInstance($driver);
                    return new Database($instance);
                } else {
                    throw new \Exception(sprintf($context->getTranslator()->_s("Driver %s does not extend or implement ResourceConnectionDriver"), $class));
                }
            } else {
                throw new \Exception(sprintf($context->getTranslator()->_s("Can't instantiate class %s"), $class));
            }
        } else {
            throw new \Exception($context->getTranslator()->_s("No driver configuration in context"));
        }
    }

    /**
     * @param string $file
     * @throws \Exception
     * @return Context
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
        return new Context($document);
    }

    static function getFolder($table, $parent_id) {
        return new Folder($table, $parent_id);
    }

    /**
     * @param string $file
     * @throws \Exception
     * @return XML
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
        return new XML($document);
    }
}