<?php

namespace EquipTests\Responder;

use Equip\Adr\Status;
use Equip\Payload;
use Equip\Responder\RedirectResponder;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        $payload = (new Payload())->withSetting('redirect', '/');

        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $response = new Response;

        $response = call_user_func($this->responder, $request, $response, $payload);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('/', $response->getHeaderLine('Location'));
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
