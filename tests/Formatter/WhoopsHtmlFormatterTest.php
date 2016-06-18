<?php

namespace EquipTests\Formatter;

use Equip\Adr\PayloadInterface;
use Equip\Formatter\WhoopsHtmlFormatter;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;
use Whoops\Handler\PrettyPageHandler as HtmlHandler;
use Whoops\Run as Whoops;

class WhoopsHtmlFormatterTest extends TestCase
{
    /**
     * @var WhoopsHtmlFormatter
     */
    private $formatter;

    protected function setUp()
    {
        $this->formatter = new WhoopsHtmlFormatter(
            new Whoops,
            new HtmlHandler
        );
    }

    public function testAccepts()
    {
        $this->assertEquals(['text/html'], WhoopsHtmlFormatter::accepts());
    }

    public function testType()
    {
        $this->assertEquals('text/html', $this->formatter->type());
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
        ob_get_clean();

        // Reached the end without errors
        $this->assertTrue(true);
    }
}
