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
require_once 'Sketch/Mail/Address/Exception.php';

 /**
 * SketchMailAddress
 *
 * @package Sketch
 */
class SketchMailAddress extends SketchObject {
    /**
     *
     * @var string
     */
    var $emailAddress = null;

    /**
     *
     * @var string
     */
    var $contactName = null;

    /**
     *
     * @param string $email_address
     * @param string $contact_name
     */
    function  __construct($email_address, $contact_name = null) {
        $this->setEmailAddress($email_address);
        if ($this->isValid()) {
            $this->setContactName($contact_name);
        } else throw new SketchMailAddressException($this->getTranslator()->_('Error validating email address'));
    }

    /**
     *
     * @return string
     */
    function getEmailAddress() {
        return $this->emailAddress;
    }

    /**
     *
     * @param string $email_address
     */
    function setEmailAddress($email_address) {
        $this->emailAddress = trim($email_address);
    }

    /**
     *
     * @return string
     */
    function getContactName() {
        return $this->contactName;
    }

    /**
     *
     * @param string $contact_name
     */
    function setContactName($contact_name) {
        $this->contactName = trim($contact_name);
    }

    /**
     *
     * @return string
     */
    function toString() {
        if ($this->isValid()) {
            $email_address = $this->getEmailAddress();
            $contact_name = $this->getContactName();
            if ($contact_name != null) {
                return "\"$contact_name\" <$email_address>";
            } else return $email_address;
        } else return null;
    }

    /**
     *
     * @return boolean
     */
    function isValid() {
        return preg_match('/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i', $this->emailAddress);
    }
}