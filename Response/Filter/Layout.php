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

require_once 'Sketch/Response/Filter.php';

/**
 * LayoutResponseFilter
 *
 * @package Sketch
 */
class LayoutResponseFilter extends SketchResponseFilter {
    /**
     *
     * @param DOMDocument $layout
     * @param DOMElement $append
     * @param DOMElement $old
     */
    private function append(DOMDocument $layout, DOMElement $append, DOMElement $old) {
        if ($append->hasChildNodes()) foreach ($append->childNodes as $node) {
            $old->appendChild($layout->importNode($node, true));
        }
    }

    /**
     *
     * @param DOMDocument $layout
     * @param DOMElement $replace
     * @param DOMElement $old
     */
    private function replaceElement(DOMDocument $layout, DOMElement $replace, DOMElement $old) {
        $new = $layout->createElement($old->tagName);
        if ($old->hasAttributes()) foreach ($old->attributes as $attribute) {
            $new->setAttribute($attribute->name, $attribute->value);
        }
        if ($replace->hasAttributes()) foreach ($replace->attributes as $attribute) {
            if ($attribute->name == 'tag') continue;
            $new->setAttribute($attribute->name, $attribute->value);
        }
        if ($replace->hasChildNodes()) foreach ($replace->childNodes as $node) {
            $new->appendChild($layout->importNode($node, true));
        }
        $old->parentNode->replaceChild($new, $old);
    }

    /**
     *
     * @param DOMDocument $layout
     * @param DOMElement $replace
     * @param DOMElement $old
     */
    private function replaceElementNs(DOMDocument $layout, DOMElement $replace, DOMElement $old) {
        $new = $layout->createElementNs('http://www.w3.org/1999/xhtml', $old->tagName);
        if ($old->hasAttributes()) foreach ($old->attributes as $attribute) {
            $new->setAttribute($attribute->name, $attribute->value);
        }
        if ($replace->hasAttributes()) foreach ($replace->attributes as $attribute) {
            if ($attribute->name == 'tag') continue;
            $new->setAttribute($attribute->name, $attribute->value);
        }
        if ($replace->hasChildNodes()) foreach ($replace->childNodes as $node) {
            $new->appendChild($layout->importNode($node, true));
        }
        $old->parentNode->replaceChild($new, $old);
    }

    /**
     *
     * @param SketchResourceXML $resource
     */
    function apply(SketchResourceXML $resource) {
        $layout_path = null;
        foreach ($resource->query("//layout[@for]") as $layout) {
            $for = $layout->getAttribute('for');
            if (strpos($this->getRequest()->getResolvedURI(), $for) !== false) {
                $layout_path = $layout->getCharacterData();
            }
        }
        $response = $this->getResponse();
        $context = new DOMXPath($response->getDocument());
        if ($response->isXHTML()) {
            $context->registerNamespace('s', 'http://kunyomi.com/sketch/layout');
            $q = $context->query('//s:template');
            if ($q instanceof DOMNodeList) foreach ($q as $template) {
                if ($template->hasAttribute('layout')) {
                    $layout = SketchResponsePart::evaluate($layout_path.DIRECTORY_SEPARATOR.$template->getAttribute('layout'));
                    $layout_context = new DOMXPath($layout);
                    $layout_context->registerNamespace('h', 'http://www.w3.org/1999/xhtml');
                    $r = $context->query('//s:append[@tag]');
                    if ($r instanceof DOMNodeList) foreach ($r as $append) {
                        $tag = $append->getAttribute('tag');
                        foreach ($layout_context->query("//h:$tag") as $old) {
                            $this->append($layout, $append, $old);
                        }
                    }
                    $r = $context->query('//s:append[@id]');
                    if ($r instanceof DOMNodeList) foreach ($r as $append) {
                        $id = $append->getAttribute('id');
                        foreach ($layout_context->query("//*[@id='$id']") as $old) {
                            $this->append($layout, $append, $old);
                        }
                    }
                    $r = $context->query('//s:replace[@tag]');
                    if ($r instanceof DOMNodeList) foreach ($r as $replace) {
                        $tag = $replace->getAttribute('tag');
                        $s = $layout_context->query("//h:$tag");
                        if ($s instanceof DOMNodeList) foreach ($s as $old) {
                            $this->replaceElementNs($layout, $replace, $old);
                        }
                    }
                    $r = $context->query('//s:replace[@id]');
                    if ($r instanceof DOMNodeList) foreach ($r as $replace) {
                        $id = $replace->getAttribute('id');
                        $s = $layout_context->query("//*[@id='$id']");
                        if ($s instanceof DOMNodeList) foreach ($s as $old) {
                            $this->replaceElementNs($layout, $replace, $old);
                        }
                    }
                    $response->setDocument($layout);
                }
            }
        } else {
            $q = $context->query('//template');
            if ($q instanceof DOMNodeList) foreach ($q as $template) {
                if ($template->hasAttribute('layout')) {
                    $layout = SketchResponsePart::evaluate($layout_path.DIRECTORY_SEPARATOR.$template->getAttribute('layout'));
                    $layout_context = new DOMXPath($layout);
                    $r = $context->query('//append[@tag]');
                    if ($r instanceof DOMNodeList) foreach ($r as $append) {
                        $tag = $append->getAttribute('tag');
                        foreach ($layout_context->query("//$tag") as $old) {
                            $this->append($layout, $append, $old);
                        }
                    }
                    $r = $context->query('//append[@id]');
                    if ($r instanceof DOMNodeList) foreach ($r as $append) {
                        $id = $append->getAttribute('id');
                        foreach ($layout_context->query("//*[@id='$id']") as $old) {
                            $this->append($layout, $append, $old);
                        }
                    }
                    $r = $context->query('//replace[@tag]');
                    if ($r instanceof DOMNodeList) foreach ($r as $replace) {
                        $tag = $replace->getAttribute('tag');
                        $s = $layout_context->query("//$tag");
                        if ($s instanceof DOMNodeList) foreach ($s as $old) {
                            $this->replaceElement($layout, $replace, $old);
                        }
                    }
                    $r = $context->query('//replace[@id]');
                    if ($r instanceof DOMNodeList) foreach ($r as $replace) {
                        $id = $replace->getAttribute('id');
                        $s = $layout_context->query("//*[@id='$id']");
                        if ($s instanceof DOMNodeList) foreach ($s as $old) {
                            $this->replaceElement($layout, $replace, $old);
                        }
                    }
                    $response->setDocument($layout);
                }
            }
        }
    }
}