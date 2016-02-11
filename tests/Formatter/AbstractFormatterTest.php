<?php

namespace EquipTests\Formatter;

use Equip\Formatter\AbstractFormatter;
use Equip\Payload;
use Lukasoppermann\Httpstatus\Httpstatus;

class AbstractFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function statusCodeProvider()
    {
        return [
            [Payload::STATUS_OK, 200],
            [Payload::STATUS_CREATED, 201],
            [Payload::STATUS_ACCEPTED, 202],
            [Payload::STATUS_NO_CONTENT, 204],
            [Payload::STATUS_MOVED_PERMANENTLY, 301],
            [Payload::STATUS_FOUND, 302],
            [Payload::STATUS_NOT_MODIFIED, 304],
            [Payload::STATUS_BAD_REQUEST, 400],
            [Payload::STATUS_UNAUTHORIZED, 401],
            [Payload::STATUS_FORBIDDEN, 403],
            [Payload::STATUS_NOT_FOUND, 404],

            // Legacy results
            // @todo Remove these in 2.0
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

        $formatter = $this->getMockForAbstractClass(AbstractFormatter::class, [new Httpstatus]);

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
