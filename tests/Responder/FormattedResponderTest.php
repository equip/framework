<?php

namespace EquipTests\Responder;

use Negotiation\Negotiator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Equip\Configuration\AurynConfiguration;
use Equip\Payload;
use Equip\Responder\FormattedResponder;
use Equip\Formatter\JsonFormatter;
use EquipTests\Configuration\ConfigurationTestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class FormattedResponderTest extends ConfigurationTestCase
{
    /**
     * @var FormattedResponder
     */
    private $responder;

    protected function getConfigurations()
    {
        return [
            new AurynConfiguration,
        ];
    }

    public function setUp()
    {
        parent::setUp();

        $this->responder = $this->injector->make(FormattedResponder::class);
    }

    public function testFormatters()
    {
        $formatters = $this->responder->toArray();

        $this->assertArrayHasKey(JsonFormatter::class, $formatters);

        unset($formatters[JsonFormatter::class]);

        $formatters = $this->responder->withData($formatters)->toArray();

        $this->assertArrayNotHasKey(JsonFormatter::class, $formatters);

        // Append another one with high quality
        $formatters[JsonFormatter::class] = 1.0;

        $formatters = $this->responder->withData($formatters)->toArray();
        $sortedcopy = $formatters;
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /formatters .* must implement .*AbstractFormatter/i
     */
    public function testInvalidResponder()
    {
        $responder = $this->responder->withValue(get_class($this), 1.0);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /formatters .* must have a quality/i
     */
    public function testInvalidResponderQuality()
    {
        $responder = $this->responder->withValue(JsonFormatter::class, false);
    }

    public function testResponse()
    {
        $request = new ServerRequest;
        $request = $request->withHeader('Accept', 'application/json');

        $response = new Response;

        $payload = new Payload;
        $payload = $payload
            ->withStatus(Payload::OK)
            ->withOutput(['test' => 'test']);

        $response = call_user_func($this->responder, $request, $response, $payload);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('Content-Type'));
        $this->assertEquals('{"test":"test"}', (string) $response->getBody());
    }

    public function testEmptyPayload()
    {
        $payload = new Payload;
        $request = $this->getMock(ServerRequestInterface::class);
        $response = $this->getMock(ResponseInterface::class);
        $returned = call_user_func($this->responder, $request, $response, $payload);
        $this->assertSame($returned, $response);
    }
}
