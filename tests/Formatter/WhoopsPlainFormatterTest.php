<?php

namespace EquipTests\Formatter;

use Equip\Adr\PayloadInterface;
use Equip\Formatter\WhoopsPlainFormatter;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;
use Whoops\Handler\PlainTextHandler as PlainHandler;
use Whoops\Run as Whoops;

class WhoopsPlainFormatterTest extends TestCase
{
    /**
     * @var WhoopsPlainFormatter
     */
    private $formatter;

    protected function setUp()
    {
        $whoops = new Whoops;
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);

        $handler = new PlainHandler;
        $handler->addTraceToOutput(false);

        $this->formatter = new WhoopsPlainFormatter(
            $whoops,
            $handler
        );
    }

    public function testAccepts()
    {
        $this->assertEquals(['text/plain'], WhoopsPlainFormatter::accepts());
    }

    public function testType()
    {
        $this->assertEquals('text/plain', $this->formatter->type());
    }

    public function testResponse()
    {
        list($line, $exception) = [__LINE__, new RuntimeException('test message')];
        $output = compact('exception');

        $payload = $this->createMock(PayloadInterface::class);
        $payload->expects($this->any())->method('getOutput')->willReturn($output);

        $body = $this->formatter->body($payload);

        $this->assertEquals(sprintf(
                "%s: %s in file %s on line %d\n",
                get_class($exception),
                'test message',
                __FILE__,
                $line
            ),
            $body
        );
    }
}
