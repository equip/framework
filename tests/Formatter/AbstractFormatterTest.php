<?php

namespace EquipTests\Formatter;

use Equip\Formatter\AbstractFormatter;
use Equip\Payload;
use PHPUnit_Framework_TestCase as TestCase;

class AbstractFormatterTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testAccepts()
    {
        AbstractFormatter::accepts('');
    }
}
