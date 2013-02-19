<?php
require_once 'vendor/autoload.php';

class ApplicationTest extends PHPUnit_Framework_TestCase {
    function testGetInstance() {
        $application = \Sketch\Application::getInstance();
        $this->assertInstanceOf('\Sketch\Application', $application);
    }

    function testStart() {
        $application = \Sketch\Application::getInstance();
        $application->load(dirname(__FILE__), true);
        $this->assertInstanceOf('\Sketch\ResourceContext', $application->getContext());
    }
}
