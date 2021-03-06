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
 * SketchRouterRewrite
 *
 * @package Sketch
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
            if ($r->getAttribute('match') == 'preg') {
                $pattern = sprintf('@%s@', str_replace('\\\\\\\\1', '(\w+)', preg_quote($r->getAttribute('uri'))));
                $t2 = in_array($language, array_map('trim', explode(',', $r->getAttribute('language'))));
                if (preg_match($pattern, $uri, $matches) && $t2) {
                    $replacement = str_replace('\\\\1', '$1', $r->getCharacterData());
                    return preg_replace($pattern, $replacement, $uri);
                }
            } else {
                $t1 = ($r->getAttribute('uri') == $uri);
                $t2 = in_array($language, array_map('trim', explode(',', $r->getAttribute('language'))));
                if ($t1 && $t2) {
                    return $base.$r->getCharacterData();
                }
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
        if (preg_match('/^\/(\w{2})\/[\w\/\.-]+$/', $redirect_url, $matches)) {
            $application->setLocale(new SketchLocale($matches[1]));
            $not_found = sprintf("/^\/%s\/404.*/", $application->getLocale()->getLanguage());
        } else {
            $not_found = "/404.*/";
        }
        $not_found_parameters = null;
        $not_found_request_uri = null;
        foreach ($this->getContext()->query('//rewrite/rule') as $r) {
            if ($r->getAttribute('match') == 'preg') {
                $pattern = sprintf('@%s@', str_replace('\\\\\\\\1', '(\w+)', preg_quote($r->getCharacterData())));
                if (preg_match($pattern, $redirect_url, $matches)) {
                    $replacement = str_replace('\\\\1', '$1', $r->getAttribute('uri'));
                    list($request_uri, $parameters) = array_map('trim', explode('?', preg_replace($pattern, $replacement, $redirect_url)));
                    foreach (explode('&', $parameters) as $t) {
                        list($key, $value) = array_map('trim', explode('=', $t));
                        $this->getRequest()->setAttribute($key, $value);
                    }
                    return $request_uri;
                }
            } else {
                // Rule URIs can have parameters
                list($request_uri, $parameters) = array_map('trim', explode('?', $r->getAttribute('uri')));
                if ($r->getCharacterData() == $redirect_url) {
                    foreach (explode('&', $parameters) as $t) {
                        list($key, $value) = array_map('trim', explode('=', $t));
                        $this->getRequest()->setAttribute($key, $value);
                    }
                    return $request_uri;
                } else if (preg_match($not_found, $r->getCharacterData())) {
                    $not_found_request_uri = $request_uri;
                    $not_found_parameters = $parameters;
                }
            }
        }
        if ($not_found_request_uri) {
            foreach (explode('&', $not_found_parameters) as $t) {
                list($key, $value) = array_map('trim', explode('=', $t));
                $this->getRequest()->setAttribute($key, $value);
            }
            return $not_found_request_uri;
        } else {
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
            exit($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        }
    }
}