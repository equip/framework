<?php
namespace SparkTests;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Responder\HtmlResponder;

class HtmlResponderTest extends TestCase {

    public function testAccepts()
    {
        $this->assertEquals(['text/html'], HtmlResponder::accepts());
    }
}