<?php

namespace SparkTests\Responder;

use Spark\Payload;
use Spark\Responder\HtmlResponder;

class HtmlResponderTest extends AbstractResponderTest
{
    public function testAccepts()
    {
        $this->assertEquals(['text/html'], (new HtmlResponder)->accepts());
    }

    public function testResponse()
    {
        $payload = (new Payload)->withOutput([
                'header' => 'header',
                'body'   => 'body',
                'footer' => 'footer',
            ]);

        $response = $this->getResponse(new HtmlResponder, $payload);

        $this->assertContains('text/html', $response->getHeader('Content-Type'));
        $this->assertEquals("header\nbody\nfooter", (string) $response->getBody());
    }
}
