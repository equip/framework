<?php

namespace EquipTests\Formatter;

use Equip\Payload;
use Equip\Formatter\HtmlFormatter;
use Lukasoppermann\Httpstatus\Httpstatus;

class HtmlFormatterTest extends \PHPUnit_Framework_TestCase
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
