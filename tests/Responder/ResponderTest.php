<?php
namespace SparkTests;

use Aura\Payload\Payload;
use PHPUnit_Framework_TestCase as TestCase;
use Spark\Application;
use Spark\Responder\Responder;

class ResponderTest extends TestCase {

    protected $request, $response;

    public function setUp()
    {
        $this->request = Application::boot()->getInjector()->make('Psr\Http\Message\ServerRequestInterface');
        $this->response = Application::boot()->getInjector()->make('Psr\Http\Message\ResponseInterface');
    }

    public function testAccepts()
    {
        $this->assertEquals(['application/json'], Responder::accepts());
    }

    public function statusCodeProvider()
    {
        return [
            ['unknown', 500],
            [null, 204],
            [Payload::ACCEPTED, 202],
            [Payload::CREATED, 201],
            [Payload::DELETED, 204],
            [Payload::ERROR, 500],
            [Payload::FAILURE, 400],
            [Payload::FOUND, 200],
            [Payload::NOT_AUTHENTICATED, 400],
            [Payload::NOT_AUTHORIZED, 403],
            [Payload::NOT_FOUND, 404],
            [Payload::NOT_VALID, 422],
            [Payload::PROCESSING, 203],
            [Payload::SUCCESS, 200],
            [Payload::UPDATED, 303],
        ];
    }

    /**
     * @dataProvider statusCodeProvider
     */
    public function testStatusCode($status, $expected)
    {
        $payload = null;
        if ($status) {
            $payload = new Payload;
            $payload->setStatus($status);
        }

        $responder = new Responder;
        $result = $responder($this->request, $this->response, $payload);
        $this->assertEquals($expected, $result->getStatusCode());
    }
}