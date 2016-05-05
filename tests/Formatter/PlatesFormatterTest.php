<?php

namespace EquipTests\Formatter;

use Equip\Formatter\PlatesFormatter;
use Equip\Payload;
use League\Plates\Engine;
use Lukasoppermann\Httpstatus\Httpstatus;
use PHPUnit_Framework_TestCase as TestCase;

class PlatesFormatterTest extends TestCase
{
    /**
     * @var Engine
     */
    protected $templates;

    /**
     * @var PlatesFormatter
     */
    private $formatter;

    public function setUp()
    {
        if (!class_exists('League\Plates\Engine')) {
            $this->markTestSkipped('Plates is not installed');
        }

        $this->templates = new Engine(__DIR__ . '/../_templates');
        $this->formatter = new PlatesFormatter(
            $this->templates,
            new HttpStatus
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
        $payload = (new Payload)->withOutput([
                'template' => 'test',
                'header'   => 'header',
                'body'     => 'body',
                'footer'   => 'footer',
            ]);

        $body = (string) $this->formatter->body($payload);

        $this->assertEquals("<h1>header</h1>\n<p>body</p>\n<span>footer</span>\n", $body);
    }
}
