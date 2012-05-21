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

require_once 'Sketch/Router.php';

/**
 * SketchRouterRewrite
 */
class SketchRouterRewrite extends SketchRouter {
    /**
     * Resolve
     *
     * @throws Exception
     * @param $uri
     * @return string
     */
    function resolve($uri) {
        // If relative path
        if (substr($uri, 0, 1) != DIRECTORY_SEPARATOR) {
            $base = rtrim(dirname($this->getRequest()->getResolvedURI()), DIRECTORY_SEPARATOR);
        } else {
            $base = '';
        }
        foreach ($this->getContext()->query('//rewrite/rule') as $r) {
            $t1 = ($r->getAttribute('uri') == $uri);
            $t2 = in_array($this->getLocale()->getLanguage(), array_map('trim', explode(',', $r->getAttribute('language'))));
            if ($t1 && $t2) {
                return $base.$r->getCharacterData();
            }
        }
        throw new Exception(sprintf('Could not resolve route for %s (%s).', $uri, $this->getLocale()->getLanguage()));
    }

    /**
     * Get view
     *
     * @return string
     */
    function getView() {
        $application = $this->getApplication();
        $redirect_url = str_replace($application->getURI(), '', $_SERVER['REDIRECT_URL']);
        // Set language if present
        if (preg_match('/^\/(\w{2})\/[\w\.]+$/', $redirect_url, $matches)) {
            $application->setLocale(new SketchLocale($matches[1]));
        }
        foreach ($this->getContext()->query('//rewrite/rule') as $r) {
            if ($r->getCharacterData() == $redirect_url) {
                // Rule URIs can have parameters
                list($request_uri, $parameters) = array_map('trim', explode('?', $r->getAttribute('uri')));
                foreach (explode('&', $parameters) as $t) {
                    list($key, $value) = array_map('trim', explode('=', $t));
                    $this->getRequest()->setAttribute($key, $value);
                }
                return $request_uri;
            }
        }
        throw new Exception(sprintf('No route found for %s.', $redirect_url));
    }
}