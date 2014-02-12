<?php
require_once 'vendor/autoload.php';

class ApplicationTest extends PHPUnit_Framework_TestCase {
    function testGetInstance() {
        $application = \Sketch\Application\Application::getInstance();
        $this->assertInstanceOf('\Sketch\Application\Application', $application);
    }

    function testStart() {
        $application = \Sketch\Application\Application::getInstance();
        $application->load(dirname(__FILE__), true, true);
        $this->assertInstanceOf('\Sketch\ResourceContext', $application->getContext());
    }
}
