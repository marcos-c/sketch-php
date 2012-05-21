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
require_once 'Sketch/Mail/Address/Exception.php';

 /**
 * SketchMailAddress
 */
class SketchMailAddress extends SketchObject {
    /** @var null|string */
    var $emailAddress = null;

    /** @var null|string */
    var $contactName = null;

    /**
     * Constructor
     *
     * @throws SketchMailAddressException
     * @param $email_address
     * @param null $contact_name
     */
    function  __construct($email_address, $contact_name = null) {
        $this->setEmailAddress($email_address);
        if ($this->isValid()) {
            $this->setContactName($contact_name);
        } else throw new SketchMailAddressException($this->getTranslator()->_('Error validating email address'));
    }

    /**
     * Get email address
     *
     * @return null|string
     */
    function getEmailAddress() {
        return $this->emailAddress;
    }

    /**
     * Set email address
     *
     * @param $email_address
     * @return void
     */
    function setEmailAddress($email_address) {
        $this->emailAddress = trim($email_address);
    }

    /**
     * Get contact name
     *
     * @return null|string
     */
    function getContactName() {
        return $this->contactName;
    }

    /**
     * Set contact name
     *
     * @param $contact_name
     * @return void
     */
    function setContactName($contact_name) {
        $this->contactName = trim($contact_name);
    }

    /**
     * To string
     *
     * @return null|string
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
     * Is valid
     *
     * @return int
     */
    function isValid() {
        return preg_match('/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i', $this->emailAddress);
    }
}