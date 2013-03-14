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

class ResponsePart extends Object {
    const ETAG_LIFETIME = 3600;

    /**
     * @var string
     */
    static private $etag = null;

    /**
     * @var DateTime
     */
    static private $lastModified = null;

    /**
     * @var \DOMDocument
     */
    private $document;

    /**
     * @var string
     */
    private $relativePath;

    /**
     * @var array
     */
    private $attributes = array();

    /**
     * @static
     * @param $file_name
     * @param boolean $etag
     * @param array $attributes
     * @param boolean $update_include_path
     * @return \DOMDocument
     */
    static function evaluate($file_name, $etag = false, $attributes = array(), $update_include_path = false) {
        $part = new ResponsePart($file_name, $etag, $attributes, $update_include_path);
        return $part->getDocument();
    }

    /**
     * @param $file_name
     * @param $etag
     * @param $attributes
     * @param $update_include_path
     * @throws ResponseException
     * @throws \Exception
     */
    private function __construct($file_name, $etag, $attributes, $update_include_path) {
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
        // Add to the include path the path to the template
        if ($update_include_path && strpos($this->relativePath, get_include_path()) === false) {
            set_include_path(realpath(dirname($file_name)).PATH_SEPARATOR.get_include_path());
        }
        $this->attributes = $attributes;
        try {
            $response = $this->getController()->getResponse();
            ob_start();
            require $file_name;
            // Trimming the source before feeding it to the XML parser helps bad formed documents
            $source = ob_get_clean();
            if ($source != '') {
                // ETag
                if ($etag && self::$etag == null) {
                    self::$etag = md5(serialize($this->getSession()->getACL()).$source);
                    self::$lastModified = DateTime::Now();
                    header('Etag: '.self::$etag);
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s', self::$lastModified->toUnixTimestamp()).' GMT');
                    $if_modified_since = new DateTime(strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']));
                    if (self::$etag == $_SERVER['HTTP_IF_NONE_MATCH'] && !self::$lastModified->greater($if_modified_since->addInterval(self::ETAG_LIFETIME.' seconds'))) {
                        header("HTTP/1.0 304 Not Modified");
                        exit();
                    }
                }
                libxml_use_internal_errors(true);
                $this->document = new \DOMDocument();
                $this->document->preserveWhiteSpace = false;
                $this->document->resolveExternals = false;
                $this->document->loadHTML($source);
                $errors = libxml_get_errors();
                foreach ($errors as $error) {
                    // Ignore warnings and recoverable errors, throw an exception if anything else
                    if (!in_array($error->level, array(LIBXML_ERR_WARNING, LIBXML_ERR_ERROR))) {
                        $exception = new ResponseException(trim($error->message).' ('.$error->level.'/'.$error->code.')');
                        $i = 1; foreach (explode("\n", htmlspecialchars($source)) as $line) {
                            $lc = sprintf('%03d', $i);
                            $exception->addDebugInfo('<div '.(($i++ == $error->line) ? 'style="color: red;"' : '').'>'.$lc.' '.$line.'</div>');
                        }
                        throw $exception;
                    }
                }
                libxml_clear_errors();
                $context = new \DOMXPath($this->document);
                $q = $context->query('//form');
                if ($q instanceof \DOMNodeList) foreach ($q as $node) {
                    $components = Form::getComponents($node->getAttribute('name'));
                    $class_stack = array();
                    if (is_array($components)) foreach ($components as $component) {
                        $class = get_class($component);
                        if (method_exists($component, 'javascript') && !in_array($class, $class_stack)) {
                            $script = $this->document->createElement('script');
                            $script->setAttribute('type', 'text/javascript');
                            $script->appendChild($this->document->createTextNode("\n".trim($component->javascript())."\n"));
                            $node->parentNode->insertBefore($script, $node);
                            $class_stack[] = $class;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            ob_get_clean();
            throw $e;
        }
    }

    /**
     * @return \DOMDocument
     */
    function getDocument() {
        return $this->document;
    }

    /**
     * @return string
     */
    function getRelativePath() {
        return $this->relativePath;
    }

    /**
     * @param string $uri
     * @param null $language
     * @return string
     */
    function routerResolve($uri, $language = null) {
        return $this->getController()->getRouter()->resolve($uri, $language);
    }

    /**
     * @param string $text
     * @return string
     */
    function _($text) {
        return $this->getTranslator()->_($text);
    }

    /**
     * @param string $string
     * @return string
     */
    function escapeString($string) {
        return $this->getFormatter()->escapeString($string);
    }

    /**
     * @param string $text
     * @return string
     */
    function formatPlainText($text) {
        return $this->getFormatter()->formatPlainText($text);
    }

    /**
     * @param string $text
     * @return string
     */
    function formatMarkItUpText($text) {
        return $this->getFormatter()->formatMarkItUpText($text);
    }

    /**
     * @param float $number
     * @return string
     */
    function formatNumber($number) {
        return $this->getFormatter()->formatNumber($number);
    }

    /**
     * @param DateTime $date
     * @return string
     */
    function formatDate(DateTime $date) {
        return $this->getFormatter()->formatDate($date);
    }

    /**
     * @param DateTime $date
     * @param string $time_zone
     * @return string
     */
    function formatDateWithTimeZone(DateTime $date, $time_zone) {
        return $this->getFormatter()->formatDateWithTimeZone($date, $time_zone);
    }

    /**
     * @param DateTime $date
     * @return string
     */
    function formatTime(DateTime $date) {
        return $this->getFormatter()->formatTime($date);
    }

    /**
     * @param DateTime $date
     * @return string
     */
    function formatDateAndTime(DateTime $date) {
        return $this->getFormatter()->formatDateAndTime($date);
    }

    /**
     * @param DateTime $date
     * @param string $time_zone
     * @return string
     */
    function formatDateAndTimeWithTimeZone(DateTime $date, $time_zone) {
        return $this->getFormatter()->formatDateAndTimeWithTimeZone($date, $time_zone);
    }

    /**
     * @return array
     */
    function getAttributes() {
        return $this->attributes;
    }

    /**
     * @param $key
     * @param string $default
     * @return string
     */
    function getAttribute($key, $default = '') {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        } else {
            return $default;
        }
    }

    /**
     * @param boolean $with_descriptions
     * @return array
     */
    function getAvailableLanguages($with_descriptions = false) {
        if ($with_descriptions) {
            $iso_languages = LocaleISO::getLanguages();
            $available_languages = array();
            foreach ($this->getLocale()->getTranslator()->getDriver()->getAvailableLanguages() as $language) {
                $available_languages[$language] = $iso_languages[$language];
            }
            return $available_languages;
        } else {
            return $this->getLocale()->getTranslator()->getDriver()->getAvailableLanguages();
        }
    }
}