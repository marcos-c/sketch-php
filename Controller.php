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

require_once 'Sketch/Object.php';
require_once 'Sketch/Router.php';
require_once 'Sketch/Response.php';
require_once 'Sketch/Response/Filter.php';

/**
 * SketchController
 *
 * @package Sketch
 */
class SketchController extends SketchObject {
    /**
     *
     * @var SketchRouter
     */
    private $router;

    /**
     *
     * @return SketchResponse
     */
    function getResponse() {
        return $this->response;
    }

    /**
     *
     * @param SketchResponse $response
     */
    function setResponse(SketchResponse $response) {
        $this->response = $response;
        $this->response->setDocument(SketchResponsePart::evaluate($this->getRouter()->getView(), true));
    }

    /**
     *
     * @param SketchResourceContext $context
     */
    function setResponseFilters(SketchResourceContext $context) {
        $extensions = $context->query("//extension[@type='SketchResponseFilter']");
        foreach ($extensions as $extension) {
            $class = $extension->getAttribute('class');
            $source = $extension->getAttribute('source');
            if (SketchUtils::Readable("Sketch/Response/Filter/$source")) {
                require_once "Sketch/Response/Filter/$source";
                if (class_exists($class)) {
                    eval('$instance = new '.$class.'($this->getResponse());');
                    if ($instance instanceof SketchResponseFilter) {
                        $instance->apply($extension);
                    } else throw new Exception(sprinf($this->getTranslator()->_("Filter %s does not extend or implement SketchResponseFilter"), $class));
                } else throw new Exception(sprintf($this->getTranslator()->_("Can't instantiate class %s"), $class));
            } else throw new Exception(sprintf($this->getTranslator()->_("File %s can't be found"), $source));
        }
    }

    /**
     *
     * @return SketchRouter
     */
    function getRouter() {
        return $this->router;
    }

    /**
     *
     * @param SketchRouter $router
     */
    function setRouter(SketchRouter $router) {
        $this->router = $router;
    }

    /**
     *
     * @param string $location
     */
    function forward($location = null) {
        if ($this->getRequest()->isJSON()) {
            $response = new SketchResponseJSON();
            $response->forwardLocation = $location;
            print SketchUtils::encodeJSON($response);
            exit();
        } else {
            if (headers_sent()) {
                throw new Exception($this->getTranslator()->_("Headers already sent"));
            } else {
                $request = $this->getRequest();
                if ($request->getOnForwardReturn()) {
                    print $request->getOnForwardReturn();
                } else {
                    $server_name = $request->getServerName();
                    $server_port = $this->getRequest()->getServerPort();
                    if ($server_port == 443) {
                        if ($location != null) {
                            // If relative path
                            if (substr($location, 0, 1) != DIRECTORY_SEPARATOR) {
                                $base = rtrim(dirname($this->getRequest()->getResolvedURI()), DIRECTORY_SEPARATOR);
                                $location = $base.DIRECTORY_SEPARATOR.$location;
                            }
                            header("Location: https://$server_name".$location, true, 303);
                        } else {
                            header("Location: https://$server_name".$request->getURI(), true, 303);
                        }
                    } else {
                        $server_port = ($server_port != 80) ? ":$server_port" : "";
                        if ($location != null) {
                            // If relative path
                            if (substr($location, 0, 1) != DIRECTORY_SEPARATOR) {
                                $base = rtrim(dirname($this->getRequest()->getResolvedURI()), DIRECTORY_SEPARATOR);
                                $location = $base.DIRECTORY_SEPARATOR.$location;
                            }
                            header("Location: http://$server_name$server_port".$location, true, 303);
                        } else {
                            header("Location: http://$server_name$server_port".$request->getURI(), true, 303);
                        }
                    }
                }
                exit();
            }
        }
    }
}