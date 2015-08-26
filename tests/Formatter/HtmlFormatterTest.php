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
        $this->assertEquals('text/html', (new HtmlFormatter)->type());
    }

    public function testBody()
    {
        $payload = (new Payload)->withOutput([
            'header' => 'header',
            'body'   => 'body',
            'footer' => 'footer',
        ]);

        $body = (new HtmlFormatter)->body($payload);

        $this->assertEquals("header\nbody\nfooter", $body);
    }
}
