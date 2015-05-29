<?php
namespace SparkTests;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Responder\JsonResponder;

class JsonResponderTest extends TestCase {

    public function testAccepts()
    {
        $this->assertEquals(['application/json'], JsonResponder::accepts());
    }
}