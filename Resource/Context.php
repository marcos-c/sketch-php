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

class SketchResourceContext extends SketchResourceXML {
    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $version = '1.0';

    /**
     *
     * @var string
     */
    private $locale;

    /**
     *
     * @var string
     */
    private $layerName;

    /**
     *
     * @param DOMDocument $document
     */
    function __construct(DOMDocument $document) {
        $context = $document->getElementsByTagName('context')->item(0);
        if ($context instanceof DOMElement) {
            $this->layerName = $context->getAttribute('layer');
            if ($this->layerName != null) {
                $this->document = new DOMDocument();
                $this->document->preserveWhiteSpace = false;
                $this->document->resolveExternals = false;
                $root = $this->document->createElement('context');
                if ($document->documentElement->hasAttributes()) {
                    foreach ($document->documentElement->attributes as $attribute) {
                        if ($attribute->name == 'name') {
                            $this->setName($attribute->value);
                            $root->setAttribute($attribute->name, $attribute->value);
                        } else if ($attribute->name == 'version') {
                            $this->setVersion($attribute->value);
                            $root->setAttribute($attribute->name, $attribute->value);
                        } else if ($attribute->name == 'layer') {
                            $this->setLayerName($attribute->value);
                        } else {
                            $root->setAttribute($attribute->name, $attribute->value);
                        }
                    }
                }
                $xpath_context = new DOMXPath($document);
                $q = $xpath_context->query('//layer[contains(@name, \''.$this->layerName.'\')]');
                if ($q instanceof DOMNodeList) foreach ($q as $layer) {
                    if ($layer->hasChildNodes()) foreach ($layer->childNodes as $node) {
                        $root->appendChild($this->document->importNode($node, true));
                    }
                }
                $this->document->appendChild($root);
            } else {
                $this->document = $document;
            }
        } else {
            throw new Exception("Fatal error! Can't read context");
        }
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
     * @param string $name
     */
    function setName($name) {
        $this->name = $name;
        $context = $this->document->getElementsByTagName('context')->item(0);
        if ($context instanceof DOMElement) {
            $context->setAttribute('name', $name);
        }
    }

    /**
     *
     * @return string
     */
    function getVersion() {
        return $this->version;
    }

    /**
     *
     * @param string $version
     */
    function setVersion($version) {
        $this->version = $version;
        $context = $this->document->getElementsByTagName('context')->item(0);
        if ($context instanceof DOMElement) {
            $context->setAttribute('version', $version);
        }
    }

    /**
     *
     * @return string
     */
    function getLocale() {
        return $this->locale;
    }

    /**
     *
     * @param string $locale
     */
    function setLocale($locale) {
        $this->locale = $locale;
        $context = $this->document->getElementsByTagName('context')->item(0);
        if ($context instanceof DOMElement) {
            $context->setAttribute('locale', $locale);
        }
    }

    /**
     *
     * @return string
     */
    function getLayerName() {
        return $this->layerName;
    }

    /**
     *
     * @param string $layer_name
     */
    function setLayerName($layer_name) {
        $this->layerName = $layer_name;
    }

    /**
     *
     * @param string $type
     * @param string $class
     * @param string $source
     * @param array $parameters
     */
    function addDriver($reference, $type, $class, $source, array $parameters = null) {
        $context = $this->document->getElementsByTagName('context')->item(0);
        $driver = $this->document->createElement('driver');
        if ($reference != null) {
            $driver->setAttribute('reference', $reference);
        }
        $driver->setAttribute('type', $type);
        $driver->setAttribute('class', $class);
        $driver->setAttribute('source', $source);
        if (is_array($parameters)) foreach ($parameters as $value) {
            $parameter = $this->document->createDocumentFragment();
            $parameter->appendXML($value);
            $driver->appendChild($parameter);
        }
        if ($context->hasChildNodes()) {
            $context->insertBefore($driver, $context->childNodes->item(0));
        } else {
            $context->appendChild($driver);
        }
    }

    /**
     *
     * @param string $type
     * @param string $class
     * @param string $source
     * @param array $parameters
     */
    function addExtension($type, $class, $source, $excludes = null, array $parameters = null) {
        $context = $this->document->getElementsByTagName('context')->item(0);
        $extension = $this->document->createElement('extension');
        $extension->setAttribute('type', $type);
        $extension->setAttribute('class', $class);
        $extension->setAttribute('source', $source);
        if ($excludes != null) {
            $extension->setAttribute('excludes', $excludes);
        }
        if (is_array($parameters)) foreach ($parameters as $value) {
            $parameter = $this->document->createDocumentFragment();
            $parameter->appendXML($value);
            $extension->appendChild($parameter);
        }
        $context->appendChild($extension);
    }

    /**
     *
     * @return string
     */
    function  __toString() {
        $this->document->formatOutput = true;
        return $this->document->saveXML();
    }

    /**
     *
     * @param string $for
     * @param string $name
     */
    function getParametersFor($for, $name) {
        $parameters = array();
        $xpath_context = new DOMXPath($this->document);
        foreach ($xpath_context->query("//parameters[@for='${for}'][@name='${name}']") as $layer) {
            if ($layer->hasChildNodes()) foreach ($layer->childNodes as $n1) {
                $i = 0; if ($n1->hasChildNodes()) foreach ($n1->childNodes as $n2) {
                    /* @var $n2 DOMElement */
                    if ($n2->nodeName == '#text' || $n2->nodeName == '#cdata-section') {
                        $parameters[$n1->nodeName] = $n2->textContent;
                    } else if ($n2->hasChildNodes()) foreach ($n2->childNodes as $n3) {
                        /* @var $n3 DOMElement */
                        if ($n3->nodeName == '#text' || $n3->nodeName == '#cdata-section') {
                            $parameters[$n1->nodeName][$n2->nodeName] = $n3->textContent;
                        }
                    } else if ($n2->hasAttributes()) {
                        $attributes = array('type' => $n2->nodeName);
                        foreach ($n2->attributes as $attribute) {
                            /* @var $attribute DOMAttribute */
                            $attributes[$attribute->name] = $attribute->value;
                        }
                        if (array_key_exists('reference', $attributes)) {
                            $reference = $attributes['reference'];
                            unset($attributes['reference']);
                            $parameters[$n1->nodeName][$reference] = $attributes;
                        } else {
                            $parameters[$n1->nodeName][$i] = $attributes;
                        }
                    } $i++;
                }
            }
        }
        return $parameters;
    }
}