<?php

namespace SparkTests\Responder;

use League\Plates\Engine;
use Spark\Payload;
use Spark\Responder\PlatesResponder;

class PlatesResponderTest extends AbstractResponderTest
{
    protected $templates;

    public function setUp()
    {
        $this->templates = new Engine(__DIR__ . '/../_templates');
    }

    public function testAccepts()
    {
        $this->assertEquals(['text/html'], (new PlatesResponder($this->templates))->accepts());
    }

    public function testResponse()
    {
        $payload = (new Payload)->withOutput([
                'template' => 'test',
                'header'   => 'header',
                'body'     => 'body',
                'footer'   => 'footer',
            ]);

        $response = $this->getResponse(new PlatesResponder($this->templates), $payload);

        $this->assertContains('text/html', $response->getHeader('Content-Type'));
        $this->assertEquals("<h1>header</h1>\n<p>body</p>\n<span>footer</span>\n", (string) $response->getBody());
    }
}
