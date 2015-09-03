<?php

namespace SparkTests\Responder;

use Spark\Formatter\HtmlFormatter;
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

    public function testResponse()
    {
        $request = $this->getMockBuilder('Psr\Http\Message\ServerRequestInterface')->getMock();
        $request->method('getHeader')
                ->with('Accept')
                ->willReturn(['text/html']);

        $response = new Response;
        $payload  = (new Payload)
            ->withStatus(Payload::OK)
            ->withOutput(['test' => 'test']);

        $response = call_user_func($this->responder, $request, $response, $payload);

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['text/html'], $response->getHeader('Content-Type'));
        $this->assertEquals('test', (string) $response->getBody());
    }
}
