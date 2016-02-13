<?php

namespace EquipTests\Formatter;

use Equip\Payload;
use Equip\Formatter\JsonFormatter;
use Lukasoppermann\Httpstatus\Httpstatus;

class JsonFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JsonFormatter
     */
    private $formatter;

    protected function setUp()
    {
        $this->formatter = new JsonFormatter(new Httpstatus);
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
        $payload = (new Payload)->withOutput([
            'success' => true,
        ]);

        $body = $this->formatter->body($payload);

        $this->assertEquals('{"success":true}', $body);
    }
}

