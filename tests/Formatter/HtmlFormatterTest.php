<?php

namespace SparkTests\Formatter;

use Spark\Payload;
use Spark\Formatter\HtmlFormatter;

class HtmlFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testAccepts()
    {
        $this->assertEquals(['text/html'], HtmlFormatter::accepts());
    }

    public function testType()
    {
        $formatter = $this->getMockForAbstractClass(HtmlFormatter::class);
        $this->assertEquals('text/html', $formatter->type());
    }
}
