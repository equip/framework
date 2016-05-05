<?php

namespace EquipTests\Formatter;

use Equip\Formatter\HtmlFormatter;
use Equip\Payload;
use Lukasoppermann\Httpstatus\Httpstatus;
use PHPUnit_Framework_TestCase as TestCase;

class HtmlFormatterTest extends TestCase
{
    public function testAccepts()
    {
        $this->assertEquals(['text/html'], HtmlFormatter::accepts());
    }

    public function testType()
    {
        $formatter = $this->getMockForAbstractClass(HtmlFormatter::class, [new Httpstatus]);
        $this->assertEquals('text/html', $formatter->type());
    }
}
