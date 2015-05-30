<?php
namespace SparkTests;

use Aura\Payload\Payload;
use PHPUnit_Framework_TestCase as TestCase;
use Spark\Responder\HtmlResponder;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class HtmlResponderTest extends TestCase {

    public function testAccepts()
    {
        $this->assertEquals(['text/html'], HtmlResponder::accepts());
    }

    public function testResponseBody()
    {
        $request = ServerRequestFactory::fromGlobals();
        $payload = (new Payload)
            ->setStatus(Payload::FOUND)
            ->setOutput(["success" => true]);

        $responder = new HtmlResponder;
        $response = $responder($request, new Response(), $payload);

        $this->assertEquals('{&quot;success&quot;:true}', (string)$response->getBody());

        $payload->setOutput("Test html!");
        $response = $responder($request, new Response(), $payload);
        $this->assertEquals('Test html!', (string)$response->getBody());

    }
}