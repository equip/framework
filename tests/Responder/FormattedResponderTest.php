<?php

namespace SparkTests\Responder;

use Spark\Payload;
use Spark\Responder\FormattedResponder;

use Negotiation\Negotiator;
use Zend\Diactoros\Response;

class FormattedResponderTest extends \PHPUnit_Framework_TestCase
{
    private $responder;

    public function setUp()
    {
        $negotiator = new Negotiator;

        $resolver = $this->getMockBuilder('Spark\Resolver\ResolverInterface')->getMock();
        $resolver->method('__invoke')
                 ->will($this->returnCallback(function ($spec) {
                     return new $spec;
                 }));

        $this->responder = new FormattedResponder($negotiator, $resolver);
    }

    public function testFormatters()
    {
        $formatters = $this->responder->getFormatters();

        $this->assertArrayHasKey('Spark\Formatter\JsonFormatter', $formatters);

        unset($formatters['Spark\Formatter\JsonFormatter']);

        $formatters = $this->responder->withFormatters($formatters)->getFormatters();

        $this->assertArrayNotHasKey('Spark\Formatter\JsonFormatter', $formatters);

        // Append another one with high quality
        $formatters['Spark\Formatter\FakeFormatter'] = 1.0;

        $formatters = $this->responder->withFormatters($formatters)->getFormatters();
        $sortedcopy = $formatters;

        arsort($sortedcopy);

        $this->assertArrayHasKey('Spark\Formatter\FakeFormatter', $formatters);
        $this->assertSame($formatters, $sortedcopy);
    }

    public function testResponse()
    {
        $request = $this->getMockBuilder('Psr\Http\Message\ServerRequestInterface')->getMock();
        $request->method('getHeader')
                ->with('Accept')
                ->willReturn(['application/json']);

        $response = new Response;
        $payload  = (new Payload)
            ->withStatus(Payload::OK)
            ->withOutput(['test' => 'test']);

        $response = call_user_func($this->responder, $request, $response, $payload);

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('Content-Type'));
        $this->assertEquals('{"test":"test"}', (string) $response->getBody());
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
