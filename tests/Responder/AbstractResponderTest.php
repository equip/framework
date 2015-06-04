<?php
namespace SparkTests;

use Aura\Payload\Payload;
use Spark\Application;
use PHPUnit_Framework_TestCase as TestCase;

class AbstractResponderTest extends TestCase {

    protected $request, $response;

    public function setUp()
    {
        $this->request = Application::boot()->getInjector()->make('Psr\Http\Message\ServerRequestInterface');
        $this->response = Application::boot()->getInjector()->make('Psr\Http\Message\ResponseInterface');
    }

    public function statusCodeProvider()
    {
        return [
            ['unknown', 500],
            [null, 204],
            [Payload::ACCEPTED, 202],
            [Payload::AUTHENTICATED, 200],
            [Payload::AUTHORIZED, 200],
            [Payload::CREATED, 201],
            [Payload::DELETED, 204],
            [Payload::ERROR, 500],
            [Payload::FAILURE, 400],
            [Payload::FOUND, 200],
            [Payload::NOT_ACCEPTED, 406],
            [Payload::NOT_AUTHENTICATED, 400],
            [Payload::NOT_AUTHORIZED, 403],
            [Payload::NOT_CREATED, 400],
            [Payload::NOT_DELETED, 400],
            [Payload::NOT_FOUND, 404],
            [Payload::NOT_UPDATED, 400],
            [Payload::NOT_VALID, 422],
            [Payload::PROCESSING, 202],
            [Payload::SUCCESS, 200],
            [Payload::UPDATED, 303],
            [Payload::VALID, 200]
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

        $responderClass = $this->getMockForAbstractClass('Spark\Responder\AbstractResponder');

        $responder = new $responderClass;
        $result = $responder($this->request, $this->response, $payload);
        $this->assertEquals($expected, $result->getStatusCode());
    }
}