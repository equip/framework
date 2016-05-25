<?php

namespace EquipTests;

use Equip\Payload;
use PHPUnit_Framework_TestCase as TestCase;

class PayloadTest extends TestCase
{
    public function testStatus()
    {
        $load = new Payload;
        $copy = $load->withStatus(Payload::STATUS_OK);

        $this->assertEmpty($load->getStatus());
        $this->assertSame(Payload::STATUS_OK, $copy->getStatus());
    }

    public function testInput()
    {
        $input = [
            'test' => true
        ];

        $load = new Payload;
        $copy = $load->withInput($input);

        $this->assertEmpty($load->getInput());
        $this->assertSame($input, $copy->getInput());
    }

    public function testMessages()
    {
        $messages = [
            'username' => 'not found',
            'password' => 'invalid'
        ];

        $load = new Payload;
        $copy = $load->withMessages($messages);

        $this->assertEmpty($load->getMessages());
        $this->assertSame($messages, $copy->getMessages());
    }

    public function testOutput()
    {
        $output = [
            'collection' => []
        ];

        $load = new Payload;
        $copy = $load->withOutput($output);

        $this->assertEmpty($load->getOutput());
        $this->assertSame($output, $copy->getOutput());
    }

    public function testSettings()
    {
        $name = 'template';
        $value = 'index';

        $load = new Payload;
        $copy = $load->withSetting($name, $value);

        $this->assertEmpty($load->getSettings());
        $this->assertSame($value, $copy->getSetting($name));
        $this->assertSame([$name => $value], $copy->getSettings());

        $empty = $copy->withoutSetting($name);

        $this->assertFalse($empty->getSetting($name));
        $this->assertEmpty($empty->getSettings());
    }
}
