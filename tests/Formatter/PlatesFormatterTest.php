<?php

namespace EquipTests\Formatter;

use League\Plates\Engine;
use Equip\Payload;
use Equip\Formatter\PlatesFormatter;

class PlatesFormatterTest extends \PHPUnit_Framework_TestCase
{
    protected $templates;

    public function setUp()
    {
        if (!class_exists('League\Plates\Engine')) {
            $this->markTestSkipped('Plates is not installed');
        }

        $this->templates = new Engine(__DIR__ . '/../_templates');
    }

    public function testAccepts()
    {
        $this->assertEquals(['text/html'], PlatesFormatter::accepts());
    }

    public function testType()
    {
        $this->assertEquals('text/html', (new PlatesFormatter($this->templates))->type());
    }

    public function testResponse()
    {
        $payload = (new Payload)->withOutput([
                'template' => 'test',
                'header'   => 'header',
                'body'     => 'body',
                'footer'   => 'footer',
            ]);

        $body = (string) (new PlatesFormatter($this->templates))->body($payload);

        $this->assertEquals("<h1>header</h1>\n<p>body</p>\n<span>footer</span>\n", $body);
    }
}
