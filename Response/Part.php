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

require_once 'Sketch/Object.php';
require_once 'Sketch/Utils.php';
require_once 'Sketch/Response/Exception.php';
require_once 'Sketch/Response/Part/StopParseException.php';

define('ETAG_LIFETIME', 3600);

/**
 * SketchResponsePart
 */
class SketchResponsePart extends SketchObject {
    /** @var null|string */
    static private $etag = null;

    /** @var null|\SketchDateTime */
    static private $lastModified = null;

    /** @var \DOMDocument */
    private $document;

    /** @var mixed */
    private $relativePath;

    /**
     * Evaluate the response part
     *
     * @static
     * @param $file_name
     * @param bool $etag
     * @return DOMDocument
     */
    static function evaluate($file_name, $etag = false) {
        $part = new SketchResponsePart($file_name, $etag);
        return $part->getDocument();
    }

    /**
     * Check and make sure that the encoding for the source file is UTF-8
     *
     * @throws Exception
     * @param $source
     * @return string
     */
    private function encode($source) {
        if (function_exists('mb_detect_encoding')) {
            switch (mb_detect_encoding($source, 'UTF-8, ISO-8859-1')) {
                case 'UTF-8': return $source;
                case 'ISO-8859-1': return iconv('ISO-8859-1', 'UTF-8', $source);
            }
        } else {
            $list = array('UTF-8', 'ISO-8859-1');
            foreach ($list as $item) {
                $sample = iconv($item, $item, $source);
                if (md5($sample) == md5($source)) {
                    switch ($item) {
                        case 'UTF-8': return $source;
                        case 'ISO-8859-1': return iconv('ISO-8859-1', 'UTF-8', $source);
                    }
                }
            }
        }
        throw new Exception($this->getTranslator()->_('Wrong ENCODING. Sketch recomends UTF-8 but it can sort of work around ISO-8859-1, please provide a valid ENCODING'));
    }

    /**
     * Constructor
     *
     * @throws Exception|SketchResponseException|SketchResponsePartStopParseException
     * @param $file_name
     * @param $etag
     */
    private function __construct($file_name, $etag) {
        $document_root = $this->getApplication()->getRequest()->getDocumentRoot();
        if (substr($file_name, 0, 1) == '/') {
            list($request_uri) = explode('?', $this->getRequest()->getResolvedURI());
            $t1 = dirname($request_uri);
            $path = '';
            while (strpos(" $file_name", " $t1") === false) {
                $t1 = dirname($t1);
                $path .= '../';
            }
            if ($t1 == '/') {
                $file_name = $path.preg_replace("/^\//", '', $file_name);
            } else {
                $t1 = preg_quote($t1, '/');
                $file_name = $path.preg_replace("/^$t1\//", '', $file_name);
            }
        }
        $this->relativePath = str_replace($document_root, '', realpath(dirname($file_name)));
        if (SketchUtils::Readable($file_name)) {
            try {
                $response = $this->getController()->getResponse();
                ob_start();
                try {
                    require $file_name;
                    // Trimming the source before feeding it to the XML parser helps bad formed documents
                    $source = $response->getForceEncoding() ? $this->encode(trim(ob_get_clean())) : trim(ob_get_clean());
                    if ($source != '') {
                        // ETag
                        if ($etag && self::$etag == null) {
                            self::$etag = md5(serialize($this->getSession()->getACL()).$source);
                            self::$lastModified = SketchDateTime::Now();
                            header('Etag: '.self::$etag);
                            header('Last-Modified: '.gmdate('D, d M Y H:i:s', self::$lastModified->toUnixTimestamp()).' GMT');
                            $if_modified_since = new SketchDateTime(strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']));
                            if (self::$etag == $_SERVER['HTTP_IF_NONE_MATCH'] && !self::$lastModified->greater($if_modified_since->addInterval(ETAG_LIFETIME.' seconds'))) {
                                header("HTTP/1.0 304 Not Modified");
                                exit();
                            }
                        }
                        libxml_use_internal_errors(true);
                        $this->document = new DOMDocument();
                        $this->document->preserveWhiteSpace = false;
                        $this->document->resolveExternals = false;
                        if ($response->isXHTML()) {
                            $i = 0; do {
                                $this->document->loadXML($source);
                                $errors = libxml_get_errors();
                                if (count($errors) > 0) {
                                    $source_lines = explode("\n", $source);
                                    foreach ($errors as $error) {
                                        // Ignore warnings and recoverable errors, throw an exception if anything else
                                        if (!in_array($error->level, array(LIBXML_ERR_WARNING, LIBXML_ERR_ERROR))) {
                                            // Try and fix XML_ERR_ENTITYREF_SEMICOL_MISSING and XML_ERR_NAME_REQUIRED before giving up
                                            if (in_array($error->code, array(XML_ERR_ENTITYREF_SEMICOL_MISSING, XML_ERR_NAME_REQUIRED))) {
                                                $source_lines[$error->line - 1] = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/', '&amp;', $source_lines[$error->line - 1]);
                                            } else {
                                                $exception = new SketchResponseException(trim($error->message).' ('.$error->level.'/'.$error->code.')');
                                                $j = 1; foreach ($source_lines as $line) {
                                                    $lc = sprintf('%03d', $j);
                                                    $exception->addDebugInfo('<div '.(($j++ == $error->line) ? 'style="color: red;"' : '').'>'.$lc.' '.htmlentities($line).'</div>');
                                                }
                                                throw $exception;
                                            }
                                        }
                                    }
                                    $source = implode("\n", $source_lines);
                                    libxml_clear_errors();
                                } else break;
                            } while ($i++ < 4);
                            $context = new DOMXPath($this->document);
                            $context->registerNamespace('h', 'http://www.w3.org/1999/xhtml');
                            $q = $context->query('//h:form');
                            if ($q instanceof DOMNodeList) foreach ($q as $node) {
                                $components = SketchForm::getComponents($node->getAttribute('name'));
                                $class_stack = array();
                                if (is_array($components)) foreach ($components as $component) {
                                    $class = get_class($component);
                                    if (method_exists($component, 'javascript') && !in_array($class, $class_stack)) {
                                        $script = $this->document->createElementNs('http://www.w3.org/1999/xhtml', 'script');
                                        $script->setAttribute('type', 'text/javascript');
                                        $script->appendChild($this->document->createTextNode("\n//"));
                                        $script->appendChild($this->document->createCDATASection("\n".trim($component->javascript())."\n//"));
                                        $node->parentNode->insertBefore($script, $node);
                                        $class_stack[] = $class;
                                    }
                                }
                            }
                        } else {
                            $this->document->loadHTML($source);
                            $errors = libxml_get_errors();
                            foreach ($errors as $error) {
                                // Ignore warnings and recoverable errors, throw an exception if anything else
                                if (!in_array($error->level, array(LIBXML_ERR_WARNING, LIBXML_ERR_ERROR))) {
                                    $exception = new SketchResponseException(trim($error->message).' ('.$error->level.'/'.$error->code.')');
                                    $i = 1; foreach (explode("\n", htmlspecialchars($source)) as $line) {
                                        $lc = sprintf('%03d', $i);
                                        $exception->addDebugInfo('<div '.(($i++ == $error->line) ? 'style="color: red;"' : '').'>'.$lc.' '.$line.'</div>');
                                    }
                                    throw $exception;
                                }
                            }
                            libxml_clear_errors();
                            $context = new DOMXPath($this->document);
                            $q = $context->query('//form');
                            if ($q instanceof DOMNodeList) foreach ($q as $node) {
                                $components = SketchForm::getComponents($node->getAttribute('name'));
                                $class_stack = array();
                                if (is_array($components)) foreach ($components as $component) {
                                    $class = get_class($component);
                                    if (method_exists($component, 'javascript') && !in_array($class, $class_stack)) {
                                        $script = $this->document->createElement('script');
                                        $script->setAttribute('type', 'text/javascript');
                                        $script->appendChild($this->document->createTextNode("\n<!--\n".trim($component->javascript())."\n// -->"));
                                        $node->parentNode->insertBefore($script, $node);
                                        $class_stack[] = $class;
                                    }
                                }
                            }
                        }
                    }
                } catch (SketchResponsePartStopParseException $e) {
                    $this->document = new DOMDocument();
                }
            } catch (Exception $e) {
                ob_get_clean();
                throw $e;
            }
        } else throw new Exception();
    }

    /**
     * Get document
     *
     * @return DOMDocument
     */
    function getDocument() {
        return $this->document;
    }

    /**
     * Get relative path
     *
     * @return mixed
     */
    function getRelativePath() {
        return $this->relativePath;
    }

    /**
     * Resolve a universal resource locator using the router
     *
     * @param string $uri
     * @return string
     */
    function routerResolve($uri) {
        return $this->getController()->getRouter()->resolve($uri);
    }

    /**
     * Translate text using the current locale
     *
     * @param string $text
     * @return string
     */
    function _($text) {
        return $this->getTranslator()->_($text);
    }

    /**
     * Escape a text string using the current formatter
     *
     * @param string $string
     * @return string
     */
    function escapeString($string) {
        return $this->getFormatter()->escapeString($string);
    }

    /**
     * Escape a plant text using the current formatter
     *
     * @param string $text
     * @return string
     */
    function formatPlainText($text) {
        return $this->getFormatter()->formatPlainText($text);
    }

    /**
     * Format a number using the current formatter
     *
     * @param float $number
     * @return string
     */
    function formatNumber($number) {
        return $this->getFormatter()->formatNumber($number);
    }

    /**
     * Format a date using the current formatter
     *
     * @param SketchDateTime $date
     * @return string
     */
    function formatDate(SketchDateTime $date) {
        return $this->getFormatter()->formatDate($date);
    }

    /**
     * Format a date using the current formatter with the provided time zone
     *
     * @param SketchDateTime $date
     * @param $time_zone
     * @return string
     */
    function formatDateWithTimeZone(SketchDateTime $date, $time_zone) {
        return $this->getFormatter()->formatDateWithTimeZone($date, $time_zone);
    }

    /**
     * Format a time using the current formatter
     *
     * @param SketchDateTime $date
     * @return string
     */
    function formatTime(SketchDateTime $date) {
        return $this->getFormatter()->formatTime($date);
    }

    /**
     * Format a date and time using the current formatter
     *
     * @param SketchDateTime $date
     * @return string
     */
    function formatDateAndTime(SketchDateTime $date) {
        return $this->getFormatter()->formatDateAndTime($date);
    }

    /**
     * Format date and time using the current formatter with the provided time zone
     *
     * @param SketchDateTime $date
     * @param string $time_zone
     * @return string
     */
    function formatDateAndTimeWithTimeZone(SketchDateTime $date, $time_zone) {
        return $this->getFormatter()->formatDateAndTimeWithTimeZone($date, $time_zone);
    }
}