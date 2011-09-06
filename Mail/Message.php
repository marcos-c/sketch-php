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

define('MESSAGE_TO', 1);
define('MESSAGE_CC', 2);
define('MESSAGE_BCC', 4);

require_once 'Sketch/Object.php';
require_once 'Sketch/Mail/Address.php';

 /**
 * SketchMailMessage
 *
 * @package Sketch
 */
class SketchMailMessage extends SketchObject {
    /**
     *
     * @var string
     */
    var $subject;

    /**
     *
     * @var string
     */
    var $boundary;

    /**
     *
     * @var string
     */
    var $plainContent;

    /**
     *
     * @var string
     */
    var $htmlContent;

    /**
     *
     * @var string
     */
    var $replyTo;

    /**
     *
     * @var array
     */
    var $from = array();

    /**
     *
     * @var array
     */
    var $recipient = array();

    /**
     *
     * @return boolean
     */
    function isValid() {
        return ($this->getReplyToHeader() && $this->getFromHeader());
    }

    /**
     *
     * @return string
     */
    function getEndOfLine() {
        if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
            return "\r\n";
        } else if (strtoupper(substr(PHP_OS, 0, 3) == 'MAC')) {
            return "\r";
        } else {
            return "\n";
        }
    }

    /**
     *
     * @return string
     */
    function getSubject() {
        return $this->subject;
    }

    /**
     *
     * @param string $subject
     */
    function setSubject($subject) {
        $this->subject = trim($subject);
    }

    /**
     *
     * @return string
     */
    function getBoundaryKey() {
        if ($this->boundary == null) {
            $this->boundary = md5(time()).rand(1000, 9999);
        } return $this->boundary;
    }

    /**
     *
     * @return string
     */
    function getContent() {
        $eol = $this->getEndOfLine();
        $content = "This is a multi-part message in MIME format.".$eol.$eol;
        $content .= "--".$this->getBoundaryKey().$eol;
        $content .= "Content-Type: text/plain; charset=utf-8".$eol;
        $content .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
        $content .= $this->plainContent.$eol.$eol;
        $content .= "--".$this->getBoundaryKey().$eol;
        $content .= "Content-Type: text/html; charset=utf-8".$eol;
        $content .= "Content-Transfer-Encoding: 8-bit".$eol.$eol;
        $content .= $this->htmlContent.$eol.$eol;
        $content .= "--".$this->getBoundaryKey()."--";
        return $content;
    }

    /**
     *
     * @param string $plain_content
     */
    private function setPlainContent($plain_content) {
        $this->plainContent = $plain_content;
    }

    /**
     *
     * @param string $html_content
     */
    private function setHtmlContent($html_content) {
        $this->htmlContent = $html_content;
    }

    /**
     *
     * @param string $html_content
     */
    function setContent($html_content) {
        $this->setHtmlContent($html_content);
        $eol = $this->getEndOfLine();
        $plain_content = eregi_replace("<br(.{0,2})>", $eol, $html_content);
        $plain_content = eregi_replace("</p>", $eol, $plain_content);
        $tmp = array(); foreach (explode($eol, strip_tags($plain_content)) as $line) {
            if (($line = trim($line)) != null) $tmp[] = $line;
        } $this->setPlainContent("[ Esta versión del mensaje esta simplificada, el mensaje original debería ser mostrado como HTML ]".$eol.$eol.implode($eol, $tmp));
    }

    /**
     *
     * @return string
     */
    function getHeaders() {
        $eol = $this->getEndOfLine();
        $headers = 'Reply-To: '.$this->getReplyToHeader().$eol;
        $headers .= 'From: '.$this->getFromHeader().$eol;
        $cc = $this->getRecipientHeader(MESSAGE_CC);
        if ($cc) $headers .= 'Cc: '.$cc.$eol;
        $bcc = $this->getRecipientHeader(MESSAGE_BCC);
        if ($bcc) $headers .= 'Bcc: '.$bcc.$eol;
        $headers .= 'Message-ID: <'.mktime().'.'.md5(rand(1000, 9999)).'@'.$_SERVER['SERVER_NAME'].'>'.$eol;
        $headers .= 'Date: '.date('r').$eol;
        $headers .= 'Sender-IP: '.$_SERVER['REMOTE_ADDR'].$eol;
        $headers .= 'X-Mailser: Sketch Mime Library'.$eol;
        $headers .= 'Mime-Version: 1.0'.$eol;
        $headers .= "Content-Type: multipart/alternative;$eol\tboundary=\"".$this->getBoundaryKey()."\"";
        return $headers;
    }

    /**
     *
     * @return string
     */
    function getReplyToHeader() {
        if ($this->replyTo->isValid()) {
            return $this->replyTo->toString();
        } else {
            $this->getApplication()->addNotice(new ApplicationNotice("El email ".$this->replyTo->toString()." no es un email válido como remitente"));
            return false;
        }
    }

    /**
     *
     * @param string $address
     */
    function setReplyTo($address) {
        $this->replyTo = $address;
    }

    /**
     *
     * @return string
     */
    function getFromHeader() {
        $from_header = array(); foreach ($this->from as $address) {
            if ($address->isValid()) {
                $from_header[] = $address->toString();
            } else {
                $this->getApplication()->addNotice(new ApplicationNotice("El email ".$address->toString()." no es un email válido como remitente"));
            }
        }
        if (count($from_header) > 0) {
            return implode(', ', $from_header);
        } else return false;
    }

    /**
     *
     * @param SketchMailAddress $address
     */
    function setFrom(SketchMailAddress $address) {
        $this->from = array($address);
    }

    /**
     *
     * @param SketchMailAddress $address
     */
    function addFrom(SketchMailAddress $address) {
        $this->from[] = $address;
    }

    /**
     *
     * @param integer $type
     * @return string
     */
    function getRecipientHeader($type) {
        if (is_array($this->recipient[$type])) {
            $recipient_header = array();
            foreach ($this->recipient[$type] as $type => $address) {
                if ($address->isValid()) {
                    $recipient_header[] = $address->toString();
                } else {
                    $this->getApplication()->addNotice(new ApplicationNotice("El email ".$address->toString()." no es un email válido como destinatario"));
                }
            }
            if (count($recipient_header) > 0) {
                return implode(', ', $recipient_header);
            }
        } return false;
    }

    /**
     *
     * @param integer $type
     * @param SketchMailAddress $address
     */
    function addRecipient($type, SketchMailAddress $address) {
        if (!is_array($this->recipient[$type])) {
            $this->recipient[$type] = array($address);
        } else {
            $this->recipient[$type][] = $address;
        }
    }

    function clearRecipients($type) {
        unset($this->recipient[$type]);
    }
}