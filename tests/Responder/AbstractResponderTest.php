<?php

namespace SparkTests\Responder;

use Spark\Payload;
use Spark\Responder\AbstractResponder;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class AbstractResponderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Execute a responder and get the response.
     *
     * @param  AbstractResponder $responder
     * @param  Payload $payload
     * @return Response
     */
    protected function getResponse(AbstractResponder $responder, Payload $payload)
    {
        $response = $responder(new ServerRequest, new Response, $payload);

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);

        return $response;
    }

    public function statusCodeProvider()
    {
        return [
            [Payload::OK, 200],
            [Payload::ERROR, 500],
            [Payload::INVALID, 400],
            [Payload::UNKNOWN, 520],
        ];
    }

    /**
     * @dataProvider statusCodeProvider
     */
    public function testStatusCode($status, $expected)
    {
        $payload = (new Payload)->withStatus($status);

        $responder = $this->getMockForAbstractClass(AbstractResponder::class);
        $responder->method('type')->willReturn('text/plain');
        $responder->method('body')->willReturn('test body');

        $response = $this->getResponse($responder, $payload);

        $this->assertEquals($expected, $response->getStatusCode());
        $this->assertContains('text/plain', $response->getHeader('Content-Type'));
        $this->assertEquals('test body', (string) $response->getBody());
    }
}
