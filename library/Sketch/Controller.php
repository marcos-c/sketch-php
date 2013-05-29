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

class Controller extends Object {
    /**
     * @var Router
     */
    private $router;

    /**
     * @var Response
     */
    private $response;

    /**
     * @return Response
     */
    function getResponse() {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    function setResponse(Response $response) {
        $this->response = $response;
        $this->response->setDocument(ResponsePart::evaluate($this->getRouter()->getView(), false));
    }

    /**
     * @param ResourceContext $context
     * @throws \Exception
     */
    function setResponseFilters(ResourceContext $context) {
        $extensions = $context->query("//extension[@type='SketchResponseFilter']");
        foreach ($extensions as $extension) {
            $class = $extension->getAttribute('class');
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                $instance = $reflection->newInstance($this->getResponse());
                if ($instance instanceof ResponseFilter) {
                    $instance->apply($extension);
                } else {
                    throw new \Exception(sprintf($this->getTranslator()->_s("Filter %s does not extend or implement SketchResponseFilter"), $class));
                }
            } else {
                throw new \Exception(sprintf($this->getTranslator()->_s("Can't instantiate class %s"), $class));
            }
        }
    }

    /**
     * @return Router
     */
    function getRouter() {
        return $this->router;
    }

    /**
     * @param Router $router
     */
    function setRouter(Router $router) {
        $this->router = $router;
    }

    /**
     * @param string $location
     * @param boolean $https
     * @throws \Exception
     */
    function forward($location = null, $https = false) {
        if ($this->getRequest()->isJSON()) {
            $response = new ResponseJSON();
            $response->forwardLocation = $location;
            print json_encode($response);
            exit();
        } else {
            if (headers_sent()) {
                throw new \Exception($this->getTranslator()->_s("Headers already sent"));
            } else {
                $request = $this->getRequest();
                if ($request->getOnForwardReturn()) {
                    print $request->getOnForwardReturn();
                } else {
                    $server_name = $request->getServerName();
                    $server_port = $this->getRequest()->getServerPort();
                    if ($server_port == 443 || $https) {
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