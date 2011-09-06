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
 * DebugResponseFilter
 *
 * @package Sketch
 */
class DebugResponseFilter extends SketchResponseFilter {
    /**
     *
     * @param SketchResourceXML $resource 
     */
    function apply(SketchResourceXML $resource) {
        $document = $this->getResponse()->getDocument();
        if ($this->getResponse()->isXHTML()) {
            $context = new DOMXPath($document);
            $context->registerNamespace('d', 'http://kunyomi.com/sketch/debug');
            $q = $context->query('//d:debug');
            if ($q instanceof DOMNodeList) foreach ($q as $node) {
                $fragment = $document->createDocumentFragment();
                $fragment->appendXML('<pre>'.str_replace('SketchApplication Object', 'SketchApplication Object('.ceil(memory_get_usage() / 1024).'kb)', print_r(SketchApplication::getInstance(), true)).'</pre>');
                $node->parentNode->replaceChild($fragment, $node);
            }
        } else {
            $context = new DOMXPath($document);
            $q = $context->query('//debug');
            if ($q instanceof DOMNodeList) foreach ($q as $node) {
                $fragment = $document->createDocumentFragment();
                $fragment->appendXML('<pre>'.str_replace('SketchApplication Object', 'SketchApplication Object('.ceil(memory_get_usage() / 1024).'kb)', print_r(SketchApplication::getInstance(), true)).'</pre>');
                $node->parentNode->replaceChild($fragment, $node);
            }
        }
    }
}