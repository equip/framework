<?php
namespace SparkTests\Responder;

use Spark\Payload;
use Spark\Responder\JsonResponder;

class JsonResponderTest extends AbstractResponderTest
{
    public function testAccepts()
    {
        $this->assertEquals(['application/json'], (new JsonResponder)->accepts());
    }

    public function testResponseBody()
    {
        $payload = (new Payload)->withOutput([
                'success' => true,
            ]);

        $response = $this->getResponse(new JsonResponder, $payload);

        $this->assertContains('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals('{"success":true}', (string) $response->getBody());
    }
}
