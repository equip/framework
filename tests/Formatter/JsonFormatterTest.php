<?php

namespace EquipTests\Formatter;

use Equip\Payload;
use Equip\Formatter\JsonFormatter;

class JsonFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testAccepts()
    {
        $this->assertEquals(['application/json'], JsonFormatter::accepts());
    }

    public function testType()
    {
        $this->assertEquals('application/json', (new JsonFormatter)->type());
    }

    public function testBody()
    {
        $payload = (new Payload)->withOutput([
            'success' => true,
        ]);

        $body = (new JsonFormatter)->body($payload);

        $this->assertEquals('{"success":true}', $body);
    }
}

