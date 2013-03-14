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

class ResponseFilterTemplate extends ResponseFilter {
    /**
     *
     * @param \DOMDocument $template
     * @param \DOMElement $append
     * @param \DOMElement $old
     */
    private function append(\DOMDocument $template, \DOMElement $append, \DOMElement $old) {
        if ($append->hasChildNodes()) foreach ($append->childNodes as $node) {
            $old->appendChild($template->importNode($node, true));
        }
    }

    /**
     *
     * @param \DOMDocument $template
     * @param \DOMElement $replace
     * @param \DOMElement $old
     */
    private function replaceElement(\DOMDocument $template, \DOMElement $replace, \DOMElement $old) {
        $new = $template->createElement($old->tagName);
        if ($old->hasAttributes()) foreach ($old->attributes as $attribute) {
            $new->setAttribute($attribute->name, $attribute->value);
        }
        if ($replace->hasAttributes()) foreach ($replace->attributes as $attribute) {
            if ($attribute->name == 'tag') continue;
            $new->setAttribute($attribute->name, $attribute->value);
        }
        if ($replace->hasChildNodes()) foreach ($replace->childNodes as $node) {
            $new->appendChild($template->importNode($node, true));
        }
        $old->parentNode->replaceChild($new, $old);
    }

    /**
     *
     * @param ResourceXML $resource
     * @param $uri
     * @throws \Exception
     */
    function applyForURI(ResourceXML $resource, $uri) {
        $template_path = null;
        $attributes = array();
        if ($this->getContext()->getLayerName() != 'installer') {
            foreach ($resource->query("//template[@for][@class][@source]") as $template) {
                $for = $template->getAttribute('for');
                if (strpos($uri, $for) !== false) {
                    $context = $this->getContext();
                    $class = $template->getAttribute('class');
                    if (class_exists($class)) {
                        $reflection = new ReflectionMethod($class, 'getCurrentlySelectedTemplate');
                        $instance = $reflection->invoke(null);
                        /** @var $instance ObjectView */
                        $attributes = $instance->getAttributes();
                        $template_path = $instance->getPath();
                    } else {
                        throw new \Exception(sprintf($context->getTranslator()->_("Can't instantiate class %s"), $class));
                    }
                }
            }
        }
        foreach ($resource->query("//template[@for][not(@class)]") as $template) {
            $for = $template->getAttribute('for');
            if (strpos($uri, $for) !== false) {
                $template_path = $template->getCharacterData();
            }
        }
        if ($this->getSession()->getAttribute('template_for') != '' && $this->getSession()->getAttribute('template_path') != '') {
            $for = $this->getSession()->getAttribute('template_for');
            if (strpos($uri, $for) !== false) {
                $template_path = $this->getSession()->getAttribute('template_path');
            }
        }
        $response = $this->getResponse();
        $context = new \DOMXPath($response->getDocument());
        $q = $context->query('//template');
        if ($q instanceof \DOMNodeList) foreach ($q as $template) {
            if ($template->hasAttribute('src')) {
                $template = ResponsePart::evaluate($template_path.DIRECTORY_SEPARATOR.$template->getAttribute('src'), false, $attributes, true);
                $template_context = new \DOMXPath($template);
                $r = $context->query('//append[@tag]');
                if ($r instanceof \DOMNodeList) foreach ($r as $append) {
                    $tag = $append->getAttribute('tag');
                    foreach ($template_context->query("//$tag") as $old) {
                        $this->append($template, $append, $old);
                    }
                }
                $r = $context->query('//append[@id]');
                if ($r instanceof \DOMNodeList) foreach ($r as $append) {
                    $id = $append->getAttribute('id');
                    foreach ($template_context->query("//*[@id='$id']") as $old) {
                        $this->append($template, $append, $old);
                    }
                }
                $r = $context->query('//replace[@tag]');
                if ($r instanceof \DOMNodeList) foreach ($r as $replace) {
                    $tag = $replace->getAttribute('tag');
                    $s = $template_context->query("//$tag");
                    if ($s instanceof \DOMNodeList) foreach ($s as $old) {
                        $this->replaceElement($template, $replace, $old);
                    }
                }
                $r = $context->query('//replace[@id]');
                if ($r instanceof \DOMNodeList) foreach ($r as $replace) {
                    $id = $replace->getAttribute('id');
                    $s = $template_context->query("//*[@id='$id']");
                    if ($s instanceof \DOMNodeList) foreach ($s as $old) {
                        $this->replaceElement($template, $replace, $old);
                    }
                }
                $response->setDocument($template);
            }
        }
    }

    /**
     *
     * @param ResourceXML $resource
     */
    function apply(ResourceXML $resource) {
        $this->applyForURI($resource, $this->getRequest()->getResolvedURI());
    }
}