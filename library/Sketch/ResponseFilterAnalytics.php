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

class ResponseFilterAnalytics extends ResponseFilter {
    /**
     * @param ResourceXML $resource
     * @throws \Exception
     */
    function apply(ResourceXML $resource) {
        $set_account = $resource->queryCharacterData('//set-account');
        $set_domain_name = $resource->queryCharacterData('//set-domain-name');
        $set_allow_linker = $resource->queryCharacterData('//set-allow-linker');
        $script = "var _gaq = _gaq || [];\n_gaq.push(['_setAccount', '${set_account}']);\n";
        if ($set_domain_name != '') {
            $script .= "_gaq.push(['_setDomainName', '${set_domain_name}']);\n";
            if ($set_allow_linker == 'true') {
                $script .= "_gaq.push(['_setAllowLinker', true]);\n";
            }
        }
        $script .= "_gaq.push(['_trackPageview']);\n";
        $r = $resource->query('//add-transaction-handler');
        foreach ($r as $transaction_handler) {
            $class = $transaction_handler->getAttribute('class');
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                if ($reflection->implementsInterface('AnalyticsResponseFilterTransactionHandlerInterface')) {
                    $script .= $reflection->newInstance()->execute();
                } else {
                    throw new \Exception(sprintf($this->getTranslator()->_("Handler %s does not implement AnalyticsResponseFilterTransactionHandlerInterface"), $class));
                }
            } else {
                throw new \Exception(sprintf($this->getTranslator()->_("Can't instantiate class %s"), $class));
            }
        }
        $script .= "\n(function() {\nvar ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\nga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\nvar s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n})();";
        $document = $this->getResponse()->getDocument();
        $context = new \DOMXPath($document);
        $q = $context->query('//body');
        if ($q instanceof \DOMNodeList) foreach ($q as $node) {
            $element = $document->createElement('script');
            $element->setAttribute('type', 'text/javascript');
            $element->appendChild($document->createTextNode("\n".trim($script)."\n"));
            $node->appendChild($element);
        }
    }
}