<?php

namespace EquipTests\Responder;

use Equip\Payload;
use Equip\Responder\StatusResponder;
use Lukasoppermann\Httpstatus\Httpstatus;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class StatusResponderTest extends TestCase
{
    /**
     * @var StatusResponder
     */
    private $responder;

    public function setUp()
    {
        $this->responder = new StatusResponder(
            new Httpstatus
        );
    }

    public function testStatus()
    {
        $payload = new Payload;
        $payload = $payload->withStatus(Payload::STATUS_OK);

        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $response = new Response;

        $response = call_user_func($this->responder, $request, $response, $payload);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEmptyPayload()
    {
        $payload = new Payload;
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $returned = call_user_func($this->responder, $request, $response, $payload);
        $this->assertSame($returned, $response);
    }
}
