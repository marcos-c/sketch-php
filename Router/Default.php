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

require_once 'Sketch/Router.php';

/**
 * SketchRouterDefault
 *
 * @package Sketch
 */
class SketchRouterDefault extends SketchRouter {
    /**
     *
     * @param string $uri
     * @return string
     */
    function resolve($uri) {
        // If relative path
        if (substr($uri, 0, 1) != DIRECTORY_SEPARATOR) {
            list($resolved_uri) = explode('?', $this->getRequest()->getResolvedURI());
            $base = rtrim(dirname($resolved_uri), DIRECTORY_SEPARATOR);
            $uri = $base.DIRECTORY_SEPARATOR.$uri;
        }
        return $uri;
    }

    /**
     *
     * @return string
     */
    function getView() {
        list($request_uri) = explode('?', $this->getRequest()->getResolvedURI());
        preg_match('/[\w\.-]+\.php$/', $request_uri, $matches);
        return $matches[0];
    }
}