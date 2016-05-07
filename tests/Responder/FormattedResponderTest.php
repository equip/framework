<?php

namespace EquipTests\Responder;

use EquipTests\Configuration\ConfigurationTestCase;
use Equip\Configuration\AurynConfiguration;
use Equip\Formatter\AbstractFormatter;
use Equip\Formatter\JsonFormatter;
use Equip\Payload;
use Equip\Responder\FormattedResponder;
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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

        $formatters = $this->responder->withValues($formatters)->toArray();

        $this->assertArrayNotHasKey(JsonFormatter::class, $formatters);

        // Append another one with high quality
        $formatters[JsonFormatter::class] = 1.0;

        $formatters = $this->responder->withValues($formatters)->toArray();
        $sortedcopy = $formatters;
    }

    public function testSorting()
    {
        $a = $this->getMockBuilder(AbstractFormatter::class)
            ->setMockClassName('FooFormatter')
            ->getMockForAbstractClass();

        $b = $this->getMockBuilder(AbstractFormatter::class)
            ->setMockClassName('BarFormatter')
            ->getMockForAbstractClass();

        $values = [
            get_class($a) => 0.5,
            get_class($b) => 1.0,
        ];

        $responder = $this->responder->withValues($values);
        $formatters = $responder->toArray();

        $this->assertNotSame($values, $formatters);

        arsort($values);

        $this->assertSame($values, $formatters);
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
            ->withOutput(['test' => 'test']);

        $response = call_user_func($this->responder, $request, $response, $payload);

        $this->assertInstanceOf(ResponseInterface::class, $response);
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
