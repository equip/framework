<?php

namespace EquipTests\Formatter;

use Equip\Adr\PayloadInterface;
use Equip\Formatter\JsonFormatter;
use PHPUnit_Framework_TestCase as TestCase;

class JsonFormatterTest extends TestCase
{
    /**
     * @var JsonFormatter
     */
    private $formatter;

    protected function setUp()
    {
        $this->formatter = new JsonFormatter();
    }

    public function testAccepts()
    {
        $accepts = [
            'application/json',
            'application/javascript',
            'application/ld+json',
            'application/vnd.api+json',
            'application/vnd.geo+json',
        ];

        $this->assertEquals($accepts, JsonFormatter::accepts());
    }

    public function testType()
    {
        $this->assertEquals('application/json', $this->formatter->type());
    }

    public function testBody()
    {
        $output = [
            'success' => true
        ];

        $payload = $this->createMock(PayloadInterface::class);
        $payload->expects($this->any())->method('getOutput')->willReturn($output);

        $body = $this->formatter->body($payload);

        $this->assertEquals('{"success":true}', $body);
    }
}
