<?php

namespace EquipTests;

use Equip\Payload;

class PayloadTest extends \PHPUnit_Framework_TestCase
{
    public function testStatus()
    {
        $load = new Payload;
        $copy = $load->withStatus(Payload::STATUS_OK);

        $this->assertNull($load->getStatus());
        $this->assertSame(Payload::STATUS_OK, $copy->getStatus());
    }

    public function testInput()
    {
        $input = [
            'test' => true,
        ];

        $load = new Payload;
        $copy = $load->withInput($input);

        $this->assertNull($load->getInput());
        $this->assertSame($input, $copy->getInput());
    }

    public function testMessages()
    {
        $messages = [
            'username' => 'not found',
            'password' => 'invalid',
        ];

        $load = new Payload;
        $copy = $load->withMessages($messages);

        $this->assertNull($load->getMessages());
        $this->assertSame($messages, $copy->getMessages());
    }

    public function testOutput()
    {
        $output = [
            'collection' => [],
        ];

        $load = new Payload;
        $copy = $load->withOutput($output);

        $this->assertNull($load->getOutput());
        $this->assertSame($output, $copy->getOutput());
    }
}
