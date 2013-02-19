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

class SketchRouterRewrite extends SketchRouter {
    /**
     *
     * @param $uri
     * @param null $language
     * @return string
     * @throws Exception
     */
    function resolve($uri, $language = null) {
        if ($language == null) {
            $language = $this->getLocale()->getLanguage();
        }
        // If relative path
        if (substr($uri, 0, 1) != DIRECTORY_SEPARATOR) {
            list($resolved_uri) = explode('?', $this->getRequest()->getResolvedURI());
            $base = rtrim(dirname($resolved_uri), DIRECTORY_SEPARATOR);
        } else {
            $base = '';
        }
        foreach ($this->getContext()->query('//rewrite/rule') as $r) {
            $t1 = ($r->getAttribute('uri') == $uri);
            $t2 = in_array($language, array_map('trim', explode(',', $r->getAttribute('language'))));
            if ($t1 && $t2) {
                return $base.$r->getCharacterData();
            }
        }
        throw new Exception(sprintf('Could not resolve route for %s (%s).', $uri, $language));
    }

    /**
     *
     * @return string
     */
    function getView() {
        $application = $this->getApplication();
        $redirect_url = str_replace($application->getURI(), '', $_SERVER['REDIRECT_URL']);
        // Set language if present
        if (preg_match('/^\/(\w{2})\/[\w\.-]+$/', $redirect_url, $matches)) {
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
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        exit($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        // throw new Exception(sprintf('No route found for %s.', $redirect_url));
    }
}