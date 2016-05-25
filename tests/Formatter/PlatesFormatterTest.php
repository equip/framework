<?php

namespace EquipTests\Formatter;

use Equip\Adr\PayloadInterface;
use Equip\Formatter\PlatesFormatter;
use League\Plates\Engine;
use PHPUnit_Framework_TestCase as TestCase;

class PlatesFormatterTest extends TestCase
{
    /**
     * @var PlatesFormatter
     */
    private $formatter;

    public function setUp()
    {
        if (!class_exists(Engine::class)) {
            $this->markTestSkipped('Plates is not installed');
        }

        $this->formatter = new PlatesFormatter(
            new Engine(__DIR__ . '/../_templates')
        );
    }

    public function testAccepts()
    {
        $this->assertEquals(['text/html'], PlatesFormatter::accepts());
    }

    public function testType()
    {
        $this->assertEquals('text/html', $this->formatter->type());
    }

    public function testResponse()
    {
        $template = 'index';
        $output = [
            'header' => 'header',
            'body'   => 'body',
            'footer' => 'footer'
        ];

        $payload = $this->getMock(PayloadInterface::class);

        $payload->expects($this->any())
            ->method('getOutput')
            ->willReturn($output);

        $payload->expects($this->any())
            ->method('getSetting')
            ->willReturn($template);

        $body = (string) $this->formatter->body($payload);

        $this->assertEquals("<h1>header</h1>\n<p>body</p>\n<span>footer</span>\n", $body);
    }
}
