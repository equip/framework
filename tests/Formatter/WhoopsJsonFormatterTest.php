<?php

namespace EquipTests\Formatter;

use Equip\Adr\PayloadInterface;
use Equip\Formatter\WhoopsJsonFormatter;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;
use Whoops\Handler\JsonResponseHandler as JsonHandler;
use Whoops\Run as Whoops;

class WhoopsJsonFormatterTest extends TestCase
{
    /**
     * @var WhoopsJsonFormatter
     */
    private $formatter;

    protected function setUp()
    {
        $handler = new JsonHandler;
        $handler->addTraceToOutput(false);

        $whoops = new Whoops;
        $whoops->allowQuit(false);

        $this->formatter = new WhoopsJsonFormatter(
            $whoops,
            $handler
        );
    }

    public function testAccepts()
    {
        $accepts = [
            'application/json',
            'application/javascript',
            'application/ld+json',
            'application/vnd.api+json',
            'application/vnd.geo+json',
        ];

        $this->assertEquals($accepts, WhoopsJsonFormatter::accepts());
    }

    public function testType()
    {
        $this->assertEquals('application/json', $this->formatter->type());
    }

    public function testResponse()
    {
        $output = [
            'exception' => new RuntimeException
        ];

        $payload = $this->createMock(PayloadInterface::class);
        $payload->expects($this->any())->method('getOutput')->willReturn($output);

        ob_start();
        $this->formatter->body($payload);
        json_decode(ob_get_clean(), true);

        // Check that the json response is parse-able:
        $this->assertEquals(json_last_error(), JSON_ERROR_NONE);
    }
}
