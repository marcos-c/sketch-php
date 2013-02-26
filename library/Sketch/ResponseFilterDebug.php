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

class ResponseFilterDebug extends ResponseFilter {
    /**
     * @param ResourceXML $resource
     */
    function apply(ResourceXML $resource) {
        $document = $this->getResponse()->getDocument();
        $context = new \DOMXPath($document);
        $q = $context->query('//debug');
        if ($q instanceof \DOMNodeList) foreach ($q as $node) {
            $fragment = $document->createDocumentFragment();
            $fragment->appendXML('<pre>'.str_replace('SketchApplication Object', 'SketchApplication Object('.ceil(memory_get_usage() / 1024).'kb)', print_r(Application::getInstance(), true)).'</pre>');
            $node->parentNode->replaceChild($fragment, $node);
        }
    }
}