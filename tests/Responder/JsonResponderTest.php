<?php
namespace SparkTests;

use Aura\Payload\Payload;
use PHPUnit_Framework_TestCase as TestCase;
use Spark\Responder\JsonResponder;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class JsonResponderTest extends TestCase {

    public function testAccepts()
    {
        $this->assertEquals(['application/json'], JsonResponder::accepts());
    }

    public function testResponseBody()
    {
        $request = ServerRequestFactory::fromGlobals();
        $payload = (new Payload)
            ->setStatus(Payload::FOUND)
            ->setOutput(["success" => true]);

        $responder = new JsonResponder;
        $response = $responder($request, new Response(), $payload);

        $this->assertEquals('{"success":true}', (string)$response->getBody());

    }
}