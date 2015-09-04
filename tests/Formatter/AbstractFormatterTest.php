<?php

namespace SparkTests\Formatter;

use Spark\Payload;
use Spark\Formatter\AbstractFormatter;

class AbstractFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function statusCodeProvider()
    {
        return [
            [Payload::OK, 200],
            [Payload::ERROR, 500],
            [Payload::INVALID, 400],
            [Payload::UNKNOWN, 520],
        ];
    }

    /**
     * @dataProvider statusCodeProvider
     */
    public function testStatusCode($status, $expected)
    {
        $payload = (new Payload)->withStatus($status);

        $formatter = $this->getMockForAbstractClass(AbstractFormatter::class);

        $this->assertEquals($expected, $formatter->status($payload));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAccepts()
    {
        AbstractFormatter::accepts('');
    }
}
