<?php

namespace EquipTests\Formatter;

use EquipTests\Configuration\ConfigurationTestCase;
use Equip\Configuration\AurynConfiguration;
use Equip\Exception\FormatterException;
use Equip\Formatter\FormatterInterface;
use Equip\Formatter\JsonFormatter;
use Equip\Formatter\NegotiatedFormatter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class NegotiatedFormatterTest extends ConfigurationTestCase
{
    /**
     * @var NegotiatedFormatter
     */
    private $formatter;

    protected function getConfigurations()
    {
        return [
            new AurynConfiguration,
        ];
    }

    public function setUp()
    {
        parent::setUp();

        $this->formatter = $this->injector->make(NegotiatedFormatter::class);
    }

    public function testFormatters()
    {
        $formatters = $this->formatter->toArray();

        $this->assertArrayHasKey(JsonFormatter::class, $formatters);

        unset($formatters[JsonFormatter::class]);

        $formatters = $this->formatter->withValues($formatters)->toArray();

        $this->assertArrayNotHasKey(JsonFormatter::class, $formatters);

        // Append another one with high priority
        $formatters[JsonFormatter::class] = 1.0;

        $formatters = $this->formatter->withValues($formatters)->toArray();
        $sortedcopy = $formatters;
    }

    public function testSorting()
    {
        $a = $this->getMockBuilder(FormatterInterface::class)
            ->setMockClassName('FooFormatter')
            ->getMock();

        $b = $this->getMockBuilder(FormatterInterface::class)
            ->setMockClassName('BarFormatter')
            ->getMock();

        $values = [
            get_class($a) => 0.5,
            get_class($b) => 1.0,
        ];

        $formatter = $this->formatter->withValues($values);
        $formatters = $formatter->toArray();

        $this->assertNotSame($values, $formatters);

        arsort($values);

        $this->assertSame($values, $formatters);
    }

    public function testInvalidformatter()
    {
        $this->setExpectedExceptionRegExp(
            FormatterException::class,
            '/Formatter class .* must implement .*FormatterInterface/i'
        );

        $this->formatter->withValue(get_class($this), 1.0);
    }

    public function testInvalidformatterQuality()
    {
        $this->setExpectedExceptionRegExp(
            FormatterException::class,
            '/No quality have been set for the .*/i'
        );

        $this->formatter->withValue(JsonFormatter::class, false);
    }

    public function testResponse()
    {
        $request = new ServerRequest;
        $request = $request->withHeader('Accept', 'application/json');
        $response = new Response;

        $content = [
            'test' => 'test',
        ];

        $response = $this->formatter->apply($request, $response, $content);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(['application/json'], $response->getHeader('Content-Type'));
        $this->assertEquals('{"test":"test"}', (string) $response->getBody());
    }
}
