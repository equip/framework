<?php

namespace EquipTests\Formatter;

use Equip\Payload;
use Equip\Formatter\HtmlFormatter;

class HtmlFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testAccepts()
    {
        $this->assertEquals(['text/html'], HtmlFormatter::accepts());
    }

    public function testType()
    {
        $formatter = $this->getMockForAbstractClass(HtmlFormatter::class);
        $this->assertEquals('text/html', $formatter->type());
    }
}
