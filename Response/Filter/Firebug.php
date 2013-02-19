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

class FirebugResponseFilter extends SketchResponseFilter {
    /**
     * @param string $message
     * @return string
     */
    private function formatMessage($message) {
        $message = str_replace('&', '&amp;', $message);
        return addcslashes($message, "'\n\r");
    }

    /**
     * @param SketchResourceXML $resource
     */
    function apply(SketchResourceXML $resource) {
        $debug_level = $resource->queryCharacterData('//debug-level', 1);
        $script = "\tif (typeof window.console != 'undefined') { ";
        switch ($debug_level) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 5:
                // Session
                $message = $this->formatMessage(print_r($this->getSession(), true));
                $script .= "\t\tconsole.log('$message'); ";
            /** @noinspection PhpMissingBreakStatementInspection */
            case 4:
                // Request
                $message = $this->formatMessage(print_r($this->getRequest(), true));
                $script .= "\t\tconsole.log('$message'); ";
            /** @noinspection PhpMissingBreakStatementInspection */
            case 3:
                // Logged messages
                foreach ($this->getLogger()->getMessages() as $message) {
                    $message = $this->formatMessage($message);
                    $script .= "\t\tconsole.log('$message'); ";
                }
            /** @noinspection PhpMissingBreakStatementInspection */
            case 2:
                // Notices
                foreach ($this->getApplication()->getNotices() as $notice) {
                    $notice = $this->formatMessage($notice);
                    $script .= "\t\tconsole.log('$notice'); ";
                }
            case 1:
                // PHP and LIBXML versions
                $php_version = phpversion();
                $script .= "\t\tconsole.log('PHP Version: $php_version'); ";
                $libxml_version = LIBXML_DOTTED_VERSION;
                $script .= "\t\tconsole.log('LIBXML Version: $libxml_version'); ";
                // Memory usage and response time
                $memory_usage = ceil(memory_get_usage() / 1024);
                $script .= "\t\tconsole.log('Memory usage: ".$memory_usage."kb'); ";
                $response_time = number_format(microtime(true) - $this->getApplication()->getStartTime(), 3);
                $script .= "\t\tconsole.log('Response time: ".$response_time."seg'); ";
                break;
        }
        $script .= "\t}";
        $document = $this->getResponse()->getDocument();
        $context = new DOMXPath($document);
        if ($this->getResponse()->isXHTML()) {
            $context->registerNamespace('h', 'http://www.w3.org/1999/xhtml');
            $q = $context->query('//h:head');
            if ($q instanceof DOMNodeList) foreach ($q as $node) {
                $element = $document->createElementNs('http://www.w3.org/1999/xhtml', 'script');
                $element->setAttribute('type', 'text/javascript');
                $element->appendChild($document->createTextNode("\n//"));
                $element->appendChild($document->createCDATASection("\n".trim($script)."\n//"));
                $node->appendChild($element);
            }
        } else {
            $q = $context->query('//head');
            if ($q instanceof DOMNodeList) foreach ($q as $node) {
                $element = $document->createElement('script');
                $element->setAttribute('type', 'text/javascript');
                $element->appendChild($document->createTextNode("\n".trim($script)."\n"));
                $node->appendChild($element);
            }
        }
    }
}