<?php

namespace SparkTests\Responder;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Payload;
use Spark\Responder\RedirectResponder;
use Zend\Diactoros\Response;

class RedirectResponderTest extends TestCase
{
    /**
     * @var RedirectResponder
     */
    private $responder;

    public function setUp()
    {
        $this->responder = new RedirectResponder;
    }

    public function testRedirect()
    {
        $payload = new Payload;
        $payload = $payload->withMessages([
            'redirect' => '/',
        ]);

        $request = $this->getMockBuilder('Psr\Http\Message\ServerRequestInterface')->getMock();
        $response = new Response;

        $response = call_user_func($this->responder, $request, $response, $payload);

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeaderLine('Location'));

        $payload = new Payload;
        $payload = $payload->withMessages([
            'status' => 301,
            'redirect' => '/',
        ]);

        $response = call_user_func($this->responder, $request, $response, $payload);

        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testEmptyPayload()
    {
        $payload = new Payload;
        $request = $this->getMockBuilder('Psr\Http\Message\ServerRequestInterface')->getMock();
        $response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $returned = call_user_func($this->responder, $request, $response, $payload);
        $this->assertSame($returned, $response);
    }
}
