<?php

namespace EquipTests\Formatter;

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
        $this->assertEquals(['application/json'], JsonFormatter::accepts());
    }

    public function testType()
    {
        $this->assertEquals('application/json', $this->formatter->type());
    }

    public function testBody()
    {
        $content = [
            'success' => true,
        ];

        $body = $this->formatter->format($content);

        $this->assertEquals('{"success":true}', $body);
    }
}
